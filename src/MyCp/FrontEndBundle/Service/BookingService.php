<?php

namespace MyCp\FrontEndBundle\Service;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManager;
use MyCp\FrontEndBundle\Helpers\PaymentHelper;
use MyCp\mycpBundle\Entity\booking;
use MyCp\mycpBundle\Entity\payment;
use MyCp\mycpBundle\Helpers\SyncStatuses;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use MyCp\mycpBundle\Entity\generalReservation;
use MyCp\mycpBundle\Entity\ownershipReservation;

class BookingService extends Controller
{
    /**
     * @var string
     */
    private $voucherDirectoryPath;

    /**
     * @var ObjectManager
     */
    private $em;

    /**
     * Service charge in CUC
     * @var float
     */
    private $serviceChargeInCuc;

    public function __construct(ObjectManager $em, $serviceChargeInCuc, $voucherDirectoryPath)
    {
        $this->em = $em;
        $this->serviceChargeInCuc = (float)$serviceChargeInCuc;

        if (!is_dir($voucherDirectoryPath)) {
            throw new \InvalidArgumentException('Invalid directory given: ' . $voucherDirectoryPath);
        }

        if (DIRECTORY_SEPARATOR != substr($voucherDirectoryPath, -1)) {
            $voucherDirectoryPath .= DIRECTORY_SEPARATOR;
        }

        $this->voucherDirectoryPath = $voucherDirectoryPath;
    }

    /**
     * @param $bookingId
     * @return array
     */
    public function calculateBookingDetails($bookingId)
    {
        $serviceChargeInCuc = $this->serviceChargeInCuc;

        $timeService = $this->get('Time');
        $em = $this->em;
        $booking = $this->getBooking($bookingId);
        $payment = $this->getPaymentByBooking($booking);
        $user = $this->getUserByBooking($booking);

        $currency = $payment->getCurrency();
        $currencySymbol = $currency->getCurrSymbol();
        $currencyRate = $currency->getCurrCucChange();

        $nights = array();
        $rooms = array();
        $commissions = array();
        $ownResRooms = array();
        $payments = array();
        $ownResDistinct = $em
            ->getRepository('mycpBundle:ownershipReservation')
            ->get_by_id_booking($bookingId);

        foreach ($ownResDistinct as $own_r) {
            $ownResRooms[$own_r["id"]] = $em
                ->getRepository('mycpBundle:ownershipReservation')
                ->get_rooms_by_accomodation($bookingId, $own_r["id"]);

            $ownCommission = $own_r["commission_percent"];
            $ownReservations = $em
                ->getRepository('mycpBundle:ownershipReservation')
                ->get_reservations_by_booking_and_ownership($bookingId,$own_r["id"]);
            $totalPrice = 0;
            $totalPercentPrice = 0;

            foreach ($ownReservations as $own) {
                $array_dates = $timeService->dates_between(
                        $own->getOwnResReservationFromDate()->getTimestamp(),
                        $own->getOwnResReservationToDate()->getTimestamp()
                    );
                $totalPrice += $own->getOwnResNightPrice() * (count($array_dates) - 1);
                $totalPercentPrice +=
                    $own->getOwnResNightPrice() * (count($array_dates) - 1) * $ownCommission / 100;
            }

            $payments[$own_r["id"]] = array(
                'total_price' => $totalPrice * $currencyRate,
                'prepayment' => $totalPercentPrice * $currencyRate,
                'pay_at_service_cuc' => $totalPrice - $totalPercentPrice,
                'pay_at_service' => ($totalPrice - $totalPercentPrice) * $currencyRate
            );
        }

        $ownReservations = $em
            ->getRepository('mycpBundle:ownershipReservation')
            ->findBy(array('own_res_reservation_booking' => $bookingId));

        $totalPrice = 0;
        $totalPercentPrice = 0;

        foreach ($ownReservations as $own) {
            $array_dates = $timeService->dates_between(
                    $own->getOwnResReservationFromDate()->getTimestamp(),
                    $own->getOwnResReservationToDate()->getTimestamp()
                );
            array_push($nights, count($array_dates) - 1);
            array_push($rooms, $em->getRepository('mycpBundle:room')->find($own->getOwnResSelectedRoomId()));
            $totalPrice += $own->getOwnResNightPrice() * (count($array_dates) - 1);
            $commission = $own->getOwnResGenResId()->GetGenResOwnId()->getOwnCommissionPercent();
            $totalPercentPrice += $own->getOwnResNightPrice() * (count($array_dates) - 1) * $commission / 100;
            $insert = 1;

            foreach ($commissions as $com) {
                if ($com == $commission) {
                    $insert = 0;
                    break;
                }
            }

            if ($insert == 1) {
                array_push($commissions, $commission);
            }
        }

        $accommodationServiceCharge = $totalPrice * $currencyRate;
        $prepaymentAccommodations = $totalPercentPrice * $currencyRate;
        $serviceChargeTotal = $serviceChargeInCuc * $currencyRate;
        $totalPrepayment = $serviceChargeTotal + $prepaymentAccommodations;
        $totalPrepaymentInCuc = $totalPrepayment / $currencyRate;
        $totalServicingPrice = ($totalPrice - $totalPercentPrice) * $currencyRate;

        $totalPriceToPayAtServiceInCUC = $totalPrice - $totalPercentPrice;

        return array(
            'own_res' => $ownResDistinct,
            'own_res_rooms' => $ownResRooms,
            'own_res_payments' => $payments,
            'user' => $user,
            'booking' => $booking,
            'nights' => $nights,
            'rooms' => $rooms,
            'commissions' => $commissions,
            'currency_symbol' => $currencySymbol,
            'currency_rate' => $currencyRate,
            'accommodations_service_charge' => $accommodationServiceCharge,
            'prepayment_accommodations' => $prepaymentAccommodations,
            'service_charge_total' => $serviceChargeTotal,
            'total_prepayment' => $totalPrepayment,
            'total_prepayment_cuc' => $totalPrepaymentInCuc,
            'total_servicing_price' => $totalServicingPrice,
            'total_price_to_pay_at_service_in_cuc' => $totalPriceToPayAtServiceInCUC
        );
    }

    /**
     * Returns a printable rendered view of the reservation confirmation.
     *
     * @param $bookingId
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getPrintableBookingConfirmationResponse($bookingId)
    {
        return $this->render('FrontEndBundle:reservation:boucherReservation.html.twig',
            $this->calculateBookingDetails($bookingId));
    }

    /**
     * Returns the rendered view of the reservation confirmation.
     *
     * @param $bookingId
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getBookingConfirmationResponse($bookingId)
    {
        return $this->render('FrontEndBundle:reservation:confirmReservation.html.twig',
            $this->calculateBookingDetails($bookingId));
    }

    /**
     * Executes the post-processing of a booking's payment.
     *
     * This includes sending out emails to the respective stakeholders
     * and setting the statuses of corresponding reservations.
     *
     * @param payment $payment
     * @throws \LogicException
     */
    public function postProcessBookingPayment(payment $payment)
    {
        $booking = $payment->getBooking();
        $bookingId = $booking->getBookingId();
        $status = $payment->getStatus();

        if ($status !== PaymentHelper::STATUS_PENDING
            && $status !== PaymentHelper::STATUS_SUCCESS) {
            return;
        }

        $paymentPending = $status == PaymentHelper::STATUS_PENDING;
        $this->updateReservationStatuses($bookingId, $status);
        $this->processPaymentEmails($booking, $paymentPending);
        $this->setPaymentStatusProcessed($payment);
    }

    /**
     * Processes the payment emails by sending them out to
     *  - the customer
     *  - the reservation team
     *  - the accommodation owner(s)
     *
     * @param booking $booking
     * @param $paymentPending
     */
    private function processPaymentEmails(booking $booking, $paymentPending)
    {
        $bookingId = $booking->getBookingId();
        $user = $this->getUserFromBooking($booking);
        $userId = $user->getUserId();
        $userTourist = $this->getUserTourist($userId, $bookingId);
        $ownershipReservations = $this->getOwnershipReservations($bookingId);

        $arrayPhotos = array();
        $arrayNights = array();
        $arrayNightsByOwnershipReservation = array();
        $arrayHouses = array();
        $arrayHousesIds = array();
        $arrayOwnershipReservationByHouse = array();
        $timeService = $this->get('time');

        $cont = 0;

        foreach ($ownershipReservations as $own) {
            $rootOwn = $own->getOwnResGenResId()->getGenResOwnId();
            $rootOwnId = $rootOwn->getOwnId();

            $photos = $this->em
                ->getRepository('mycpBundle:ownership')
                ->getPhotos($rootOwnId);

            array_push($arrayPhotos, $photos);
            $array_dates = $timeService->dates_between(
                $own->getOwnResReservationFromDate()->getTimestamp(),
                $own->getOwnResReservationToDate()->getTimestamp()
            );
            array_push($arrayNights, count($array_dates) - 1);
            $arrayNightsByOwnershipReservation[$own->getOwnResId()] = count($array_dates) - 1;

            $insert = true;
            foreach ($arrayHousesIds as $item) {
                if ($rootOwnId == $item) {
                    $insert = false;
                }
            }

            if ($insert) {
                array_push($arrayHousesIds, $rootOwnId);
                array_push($arrayHouses, $rootOwn);
            }

            if (isset($arrayOwnershipReservationByHouse[$rootOwnId])) {
                $temp_array = $arrayOwnershipReservationByHouse[$rootOwnId];
            } else {
                $temp_array = array();
            }

            array_push($temp_array, $own);
            $arrayOwnershipReservationByHouse[$rootOwnId] = $temp_array;
            $cont++;
        }

        $response = $this->getPrintableBookingConfirmationResponse($bookingId);

        $pdfName = 'voucher' . $userId . '_' . $bookingId;
        $pdfFilePath = $this->getVoucherPdfFilePath($pdfName);

        $pdfService = $this->get('front_end.services.pdf');
        $success = $pdfService->storeHtmlAsPdf($response, $pdfFilePath);

        if (!$success) {
            // PDF could not be stored, so ignore attachment for now
            $pdfFilePath = null;
        }

        // Send email to customer
        $emailService = $this->get('Email');

        $userLocale = strtolower($userTourist->getUserTouristLanguage()->getLangCode());
        $body = $this->render('FrontEndBundle:mails:email_offer_available.html.twig', array(
            'booking' => $bookingId,
            'user' => $user,
            'reservations' => $ownershipReservations,
            'photos' => $arrayPhotos,
            'nights' => $arrayNights,
            'user_locale' => $userLocale
        ));

        $locale = $this->get('translator');
        $subject = $locale->trans('PAYMENT_CONFIRMATION', array(), "messages", $userLocale);

        $logger = $this->get('logger');
        $userEmail = trim($user->getUserEmail());

        try {
            $emailService->send_email(
                $subject,
                'reservation1@mycasaparticular.com',
                $subject . ' - MyCasaParticular.com',
                $userEmail,
                $body,
                $pdfFilePath
            );

            $logger->info('Successfully sent email to user ' . $userEmail . ', PDF path : ' .
                (isset($pdfFilePath) ? $pdfFilePath : '<empty>'));
        } catch (\Exception $e) {
            $logger->error(sprintf(
                    'EMAIL: Could not send Email to User. Booking ID: %s, Email: %s',
                    $bookingId, $userEmail));
            $logger->error($e->getMessage());
        }

        // send email to reservation team
        foreach ($arrayOwnershipReservationByHouse as $owns) {
            $bodyRes = $this->render(
                'FrontEndBundle:mails:rt_payment_confirmation.html.twig',
                array(
                    'user' => $user,
                    'user_tourist' => array($userTourist),
                    'reservations' => $owns,
                    'nights' => $arrayNightsByOwnershipReservation,
                    'payment_pending' => $paymentPending
                )
            );

            try {
                $emailService->send_email(
                    'Confirmación de pago',
                    'no-reply@mycasaparticular.com',
                    'MyCasaParticular.com',
                    'reservation@mycasaparticular.com',
                    $bodyRes
                );

                $logger->info('Successfully sent email to reservation team. Booking ID: ' . $bookingId);
            } catch (\Exception $e) {
                $logger->error('EMAIL: Could not send Email to reservation team. Booking ID: ' . $bookingId);
                $logger->error($e->getMessage());
            }
        }

        // send email to accommodation owner
        foreach ($arrayOwnershipReservationByHouse as $owns) {
            $bodyOwner = $this->render(
                'FrontEndBundle:mails:email_house_confirmation.html.twig',
                array(
                    'user' => $user,
                    'user_tourist' => array($userTourist),
                    'reservations' => $owns,
                    'nights' => $arrayNightsByOwnershipReservation
                )
            );

            $ownerEmail = $owns[0]->getOwnResGenResId()->getGenResOwnId()->getOwnEmail1();
            $ownerEmail = trim($ownerEmail);

            if (empty($ownerEmail)) {
                $logger->warning('EMAIL: Could not send Email to Casa Owner as the Email address is empty. Booking ID: ' .
                    $bookingId . '. General Reservation ID: ' .
                    $owns[0]->getOwnResGenResId()->getGenResId() . '.');
            } else {
                try {
                    $emailService->send_email(
                        'Confirmación de reserva',
                        'no-reply@mycasaparticular.com',
                        'MyCasaParticular.com',
                        $ownerEmail,
                        $bodyOwner
                    );

                    $logger->info('Successfully sent email to Casa Owner. Booking ID: ' .
                        $bookingId . ', Email: ' . $ownerEmail);
                } catch (\Exception $e) {
                    $logger->error('EMAIL: Could not send Email to Casa Owner. Booking ID: ' .
                        $bookingId . '. General Reservation ID: ' .
                        $owns[0]->getOwnResGenResId()->getGenResId() . '. Email: ' . $ownerEmail);
                    $logger->error($e->getMessage());
                }
            }
        }
    }

    /**
     * Updates the statuses of the ownership and general reservations
     * according to the corresponding payment status.
     *
     * @param $bookingId
     * @param $paymentStatus
     */
    private function updateReservationStatuses($bookingId, $paymentStatus)
    {
        $ownershipReservations = $this->getOwnershipReservations($bookingId);

        foreach ($ownershipReservations as $own) {
            $own->setOwnResSyncSt(SyncStatuses::UPDATED);

            if ($paymentStatus == PaymentHelper::STATUS_PENDING
                || $paymentStatus == PaymentHelper::STATUS_SUCCESS) {
                $generalReservation = $own->getOwnResGenResId();
                $generalReservation->setGenResStatus(generalReservation::STATUS_RESERVED);
                $own->setOwnResStatus(ownershipReservation::STATUS_RESERVED);
                $this->em->persist($generalReservation);
            }

            $this->em->persist($own);
        }

        $this->em->flush();
    }

    /**
     * Set the payment status to STATUS_PROCESSED
     *
     * @param payment $payment
     */
    private function setPaymentStatusProcessed(payment $payment)
    {
        $payment->setStatus(PaymentHelper::STATUS_PROCESSED);
        $this->em->persist($payment);
        $this->em->flush();
    }

    /**
     * @param booking $booking
     * @return null|object
     * @throws \LogicException
     */
    private function getUserFromBooking(booking $booking)
    {
        $user = $this->em
            ->getRepository('mycpBundle:user')
            ->find($booking->getBookingUserId());

        if (empty($user)) {
            throw new \LogicException(
                sprintf('User with ID %s not found (Booking ID %s)',
                    $booking->getBookingUserId(), $booking->getBookingId()));
        }
        return $user;
    }

    /**
     * @param $userId
     * @param $bookingId
     * @return null|object
     * @throws \LogicException
     */
    private function getUserTourist($userId, $bookingId)
    {
        $userTourist = $this->em
            ->getRepository('mycpBundle:userTourist')
            ->findOneBy(array('user_tourist_user' => $userId));

        if (empty($userTourist)) {
            throw new \LogicException(
                sprintf('UserTourist for user with ID %s not found (Booking ID %s)',
                    $userId, $bookingId));
        }
        return $userTourist;
    }

    /**
     * @param $bookingId
     * @return array
     */
    private function getOwnershipReservations($bookingId)
    {
        $ownershipReservations = $this->em
            ->getRepository('mycpBundle:ownershipReservation')
            ->findBy(array('own_res_reservation_booking' => $bookingId));
        return $ownershipReservations;
    }

    /**
     * Returns the file path for a voucher.
     *
     * @param $pdfName
     * @return string
     */
    private function getVoucherPdfFilePath($pdfName)
    {
        $pdfFilePath = $this->voucherDirectoryPath . "$pdfName.pdf";
        return $pdfFilePath;
    }

    /**
     * @param $bookingId
     * @return mixed
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    private function getBooking($bookingId)
    {
        $booking = $this->em
            ->getRepository('mycpBundle:booking')
            ->findOneBy(array('booking_id' => $bookingId));

        if (empty($booking)) {
            throw $this->createNotFoundException('Booking not found for ID ' . $bookingId);
        }
        return $booking;
    }

    /**
     * @param $booking
     * @return mixed
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    private function getUserByBooking($booking)
    {
        $userId = $booking->getBookingUserId();
        $user = $this->em
            ->getRepository('mycpBundle:user')
            ->find($userId);

        if (empty($user)) {
            throw $this->createNotFoundException(
                'No user exists for confirmed booking with ID ' . $booking->getBookingId());
        }

        return $user;
    }

    /**
     * @param $booking
     * @return mixed
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    private function getPaymentByBooking($booking)
    {
        $payment = $this->em
            ->getRepository('mycpBundle:payment')
            ->findOneBy(array('booking' => $booking));

        if (empty($payment)) {
            throw $this->createNotFoundException('No payment exists for confirmed booking with ID ' . $booking->getBookingId());
        }

        return $payment;
    }

}
