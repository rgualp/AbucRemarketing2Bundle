<?php

namespace MyCp\FrontEndBundle\Service;

use Abuc\RemarketingBundle\Event\FixedDateJobEvent;
use Doctrine\Common\Persistence\ObjectManager;
use MyCp\FrontEndBundle\Helpers\PaymentHelper;
use MyCp\mycpBundle\Entity\booking;
use MyCp\mycpBundle\Entity\cancelPayment;
use MyCp\mycpBundle\Entity\failure;
use MyCp\mycpBundle\Entity\generalReservation;
use MyCp\mycpBundle\Entity\ownershipReservation;
use MyCp\mycpBundle\Entity\payment;
use MyCp\mycpBundle\Entity\pendingPayown;
use MyCp\mycpBundle\Entity\notification;
use MyCp\mycpBundle\Entity\pendingPaytourist;
use MyCp\mycpBundle\Helpers\SyncStatuses;
use MyCp\PartnerBundle\Entity\paPendingPaymentAccommodation;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

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

    /*
     * Triple room charge
     * @var float
     */
    private $tripleRoomCharge;

    public function __construct(ObjectManager $em, $serviceChargeInCuc, $voucherDirectoryPath, $tripleRoomCharge)
    {
        $this->em = $em;
        $this->serviceChargeInCuc = (float)$serviceChargeInCuc;
        $this->tripleRoomCharge = (float)$tripleRoomCharge;

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
    public function calculateBookingDetailsPartner($bookingId,$user)
    {
        $serviceChargeInCuc = 0;

        $timeService = $this->get('Time');
        $em = $this->em;
        $booking = $this->getBooking($bookingId);
        $payment = $this->getPaymentByBooking($booking);
        $user = $this->getUserByBooking($booking);
        //$userTourist = $em->getRepository('mycpBundle:userTourist')->findOneBy(array('user_tourist_user' => $user->getUserId()));
        $userLocale = strtolower($user->getUserLanguage()->getLangCode());

        $currency = $payment->getCurrency();
        $currencySymbol = $currency->getCurrSymbol();
        $currencyRate = $currency->getCurrCucChange();
        $touristTaxTotal = 0;

        $nights = array();
        $rooms = array();
        $commissions = array();
        $ownResRooms = array();
        $payments = array();
        $ownResDistinct = $em
            ->getRepository('mycpBundle:ownershipReservation')
            ->getByBooking($bookingId);

        foreach ($ownResDistinct as $own_r) {
            $ownResRooms[$own_r["id"]] = $em
                ->getRepository('mycpBundle:ownershipReservation')
                ->getRoomsByAccomodation($bookingId, $own_r["id"]);

            $ownCommission = $own_r["commission_percent"];
            $ownReservations = $em
                ->getRepository('mycpBundle:ownershipReservation')
                ->getByBookingAndOwnership($bookingId,$own_r["id"]);
            $totalPrice = 0;
            $totalPercentPrice = 0;
            $totalNights = 0;

            foreach ($ownReservations as $own) {
                $array_dates = $timeService->datesBetween(
                    $own->getOwnResReservationFromDate()->getTimestamp(),
                    $own->getOwnResReservationToDate()->getTimestamp()
                );
                $totalPrice += \MyCp\FrontEndBundle\Helpers\ReservationHelper::getTotalPrice($em, $timeService, $own, $this->tripleRoomCharge);

                $totalNights += $timeService->nights($own->getOwnResReservationFromDate()->format("Y-m-d"), $own->getOwnResReservationToDate()->format("Y-m-d"));

            }

            if($serviceChargeInCuc == 0)
            {
                $serviceChargeInCuc = $own_r["fixedFee"];
            }
            else if($serviceChargeInCuc != $own_r["fixedFee"] && $own_r["currentFee"])
                $serviceChargeInCuc = $own_r["fixedFee"];

            $totalPercentPrice += $totalPrice * $ownCommission / 100;
            $totalRooms = count($ownReservations);
            $tax = $em->getRepository("mycpBundle:serviceFee")->calculateTouristServiceFee($totalRooms, ($totalNights/$totalRooms), $totalPrice / $totalNights * $totalRooms, $own_r["service_fee"]);

            $touristTaxTotal += $totalPrice * $tax;


            $payments[$own_r["id"]] = array(
                'total_price' => $totalPrice * $currencyRate,
                'prepayment' => $totalPercentPrice * $currencyRate,
                'touristTax' => $totalPrice * $tax * $currencyRate,
                'pay_at_service_cuc' => $totalPrice - $totalPercentPrice,
                'pay_at_service' => ($totalPrice - $totalPercentPrice) * $currencyRate
            );
        }

        $ownReservations = $em
            ->getRepository('mycpBundle:ownershipReservation')
            ->findBy(array('own_res_reservation_booking' => $bookingId, 'own_res_status' => ownershipReservation::STATUS_RESERVED));

        $totalPrice = 0;
        $totalPercentPrice = 0;

        foreach ($ownReservations as $own) {
            $array_dates = $timeService->datesBetween(
                $own->getOwnResReservationFromDate()->getTimestamp(),
                $own->getOwnResReservationToDate()->getTimestamp()
            );
            //array_push($nights, count($array_dates) - 1);
            $nights[$own->getOwnResId()] = count($array_dates) - 1;
            array_push($rooms, $em->getRepository('mycpBundle:room')->find($own->getOwnResSelectedRoomId()));
            $partialPrice = \MyCp\FrontEndBundle\Helpers\ReservationHelper::getTotalPrice($em, $timeService, $own, $this->tripleRoomCharge);
            $totalPrice += $partialPrice;
            $commission = $own->getOwnResGenResId()->GetGenResOwnId()->getOwnCommissionPercent();
            $totalPercentPrice += $partialPrice * $commission / 100;

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
        $touristTaxTotal = $touristTaxTotal * $currencyRate;
        $totalPrepayment = $serviceChargeTotal + $prepaymentAccommodations + $touristTaxTotal;
        $totalPrepaymentInCuc = $totalPrepayment / $currencyRate;
        $totalServicingPrice = ($totalPrice - $totalPercentPrice) * $currencyRate;

        $totalPriceToPayAtServiceInCUC = $totalPrice - $totalPercentPrice;

        return array(
            'user_locale' => $userLocale,
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
            'total_price_to_pay_at_service_in_cuc' => $totalPriceToPayAtServiceInCUC,
            'tourist_tax_total' => $touristTaxTotal
        );
    }
    /**
     * @param $bookingId
     * @return array
     */
    public function calculateBookingDetails($bookingId)
    {
        $serviceChargeInCuc = 0;

        $timeService = $this->get('Time');
        $em = $this->em;
        $booking = $this->getBooking($bookingId);
        $payment = $this->getPaymentByBooking($booking);
        $user = $this->getUserByBooking($booking);
        if($user->getRoles()[0] == "ROLE_CLIENT_PARTNER"){
            $userLocale = strtolower($user->getUserLanguage()->getLangCode());
        }
        else{
            $userTourist = $em->getRepository('mycpBundle:userTourist')->findOneBy(array('user_tourist_user' => $user->getUserId()));
            $userLocale = strtolower($userTourist->getUserTouristLanguage()->getLangCode());
        }

        $currency = $payment->getCurrency();
        $currencySymbol = $currency->getCurrSymbol();
        $currencyRate = $currency->getCurrCucChange();
        $touristTaxTotal = 0;

        $nights = array();
        $rooms = array();
        $commissions = array();
        $ownResRooms = array();
        $payments = array();
        $ownResDistinct = $em
            ->getRepository('mycpBundle:ownershipReservation')
            ->getByBooking($bookingId);

        foreach ($ownResDistinct as $own_r) {
            $ownResRooms[$own_r["id"]] = $em
                ->getRepository('mycpBundle:ownershipReservation')
                ->getRoomsByAccomodation($bookingId, $own_r["id"]);

            $ownCommission = $own_r["commission_percent"];
            $ownReservations = $em
                ->getRepository('mycpBundle:ownershipReservation')
                ->getByBookingAndOwnership($bookingId,$own_r["id"]);
            $totalPrice = 0;
            $totalPercentPrice = 0;
            $totalNights = 0;

            $tempNights = 0;

            $tempGenResId = $ownReservations[0]->getOwnResGenResId()->getGenResId();
            $tempTotalRooms = 0;
            $tempPrice = 0;

            foreach ($ownReservations as $own) {

                $array_dates = $timeService->datesBetween(
                    $own->getOwnResReservationFromDate()->getTimestamp(),
                    $own->getOwnResReservationToDate()->getTimestamp()
                );
                $totalPrice += \MyCp\FrontEndBundle\Helpers\ReservationHelper::getTotalPrice($em, $timeService, $own, $this->tripleRoomCharge);

                if($tempGenResId == $own->getOwnResGenResId()->getGenResId()) {
                    $tempNights += $timeService->nights($own->getOwnResReservationFromDate()->format("Y-m-d"), $own->getOwnResReservationToDate()->format("Y-m-d"));
                    $tempTotalRooms++;
                    $tempPrice += \MyCp\FrontEndBundle\Helpers\ReservationHelper::getTotalPrice($em, $timeService, $own, $this->tripleRoomCharge);

                }
                else{
                    $totalNights += ($tempNights / $tempTotalRooms);
                    $tempGenResId = $own->getOwnResGenResId()->getGenResId();

                    $tax = $em->getRepository("mycpBundle:serviceFee")->calculateTouristServiceFee($tempTotalRooms, $tempNights / $tempTotalRooms, $tempPrice / $tempTotalRooms, $own_r["service_fee"]);
                    $touristTaxTotal += $tempPrice * $tax;

                    $tempNights = $timeService->nights($own->getOwnResReservationFromDate()->format("Y-m-d"), $own->getOwnResReservationToDate()->format("Y-m-d"));
                    $tempTotalRooms = 1;
                    $tempPrice = \MyCp\FrontEndBundle\Helpers\ReservationHelper::getTotalPrice($em, $timeService, $own, $this->tripleRoomCharge);
                }
            }

            $tax = $em->getRepository("mycpBundle:serviceFee")->calculateTouristServiceFee($tempTotalRooms, $tempNights / $tempTotalRooms, $tempPrice / $tempTotalRooms, $own_r["service_fee"]);
            $touristTaxTotal += $tempPrice * $tax;

            if($serviceChargeInCuc == 0)
            {
                $serviceChargeInCuc = $own_r["fixedFee"];
            }
            else if($serviceChargeInCuc != $own_r["fixedFee"] && $own_r["currentFee"])
                $serviceChargeInCuc = $own_r["fixedFee"];

            $totalPercentPrice += $totalPrice * $ownCommission / 100;
            /*$totalRooms = count($ownReservations);
            $tax = $em->getRepository("mycpBundle:serviceFee")->calculateTouristServiceFee($totalRooms, $totalNights, $totalPrice / $totalNights, $own_r["service_fee"]);

            $touristTaxTotal += $totalPrice * $tax;*/

            $payments[$own_r["id"]] = array(
                'total_price' => $totalPrice * $currencyRate,
                'prepayment' => $totalPercentPrice * $currencyRate,
                'touristTax' => $totalPrice * $tax * $currencyRate,
                'pay_at_service_cuc' => $totalPrice - $totalPercentPrice,
                'pay_at_service' => ($totalPrice - $totalPercentPrice) * $currencyRate
            );
        }

        $ownReservations = $em
            ->getRepository('mycpBundle:ownershipReservation')
            ->findBy(array('own_res_reservation_booking' => $bookingId, 'own_res_status' => ownershipReservation::STATUS_RESERVED));

        $totalPrice = 0;
        $totalPercentPrice = 0;

        foreach ($ownReservations as $own) {
            $array_dates = $timeService->datesBetween(
                    $own->getOwnResReservationFromDate()->getTimestamp(),
                    $own->getOwnResReservationToDate()->getTimestamp()
                );
            //array_push($nights, count($array_dates) - 1);
            $nights[$own->getOwnResId()] = count($array_dates) - 1;
            array_push($rooms, $em->getRepository('mycpBundle:room')->find($own->getOwnResSelectedRoomId()));
            $partialPrice = \MyCp\FrontEndBundle\Helpers\ReservationHelper::getTotalPrice($em, $timeService, $own, $this->tripleRoomCharge);
            $totalPrice += $partialPrice;
            $commission = $own->getOwnResGenResId()->GetGenResOwnId()->getOwnCommissionPercent();
            $totalPercentPrice += $partialPrice * $commission / 100;

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
        $touristTaxTotal = $touristTaxTotal * $currencyRate;
        $totalPrepayment = $serviceChargeTotal + $prepaymentAccommodations + $touristTaxTotal;
        $totalPrepaymentInCuc = $totalPrepayment / $currencyRate;
        $totalServicingPrice = ($totalPrice - $totalPercentPrice) * $currencyRate;

        $totalPriceToPayAtServiceInCUC = $totalPrice - $totalPercentPrice;

        return array(
            'user_locale' => $userLocale,
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
            'total_price_to_pay_at_service_in_cuc' => $totalPriceToPayAtServiceInCUC,
            'tourist_tax_total' => $touristTaxTotal
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
     * @param $bookingId
     * @param $user
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getPrintableBookingConfirmationResponsePartner($bookingId,$user){
        $em = $this->em;
        $repository = $em->getRepository('mycpBundle:generalReservation');
        $paginator = $repository->getReservationsPartner($user->getUserId(),generalReservation::STATUS_RESERVED,array('booking_code'=>$bookingId),0,1000);
        $booking = $em->getRepository('mycpBundle:booking')->find($bookingId);
        $result=array_merge($this->calculateBookingDetailsPartner($bookingId,$user),array('data'=>$paginator['data'],'bookingId'=>$bookingId,'user'=>$user,'own_res'=>$paginator['data'],'booking'=>$booking,'user_locale'=> strtolower($user->getUserLanguage()->getLangCode()),'user_currency'=>$user->getUserCurrency()));
        return $this->render('PartnerBundle:Voucher:voucherReservation.html.twig',$result);
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

        $notificationService = $this->container->get("mycp.notification.service");
        $generalReservations = $this->em->getRepository('mycpBundle:generalReservation')->getReservationsByBookin($bookingId);
        foreach ($generalReservations as $generalReservation) {
            $notificationService->sendConfirmPaymentSMSNotification($generalReservation);
        }

        $ownershipReservations = $this->getOwnershipReservations($bookingId);
        foreach ($ownershipReservations as $own) {
            $this->updateICal($own->getOwnResSelectedRoomId());
        }

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
    public function postProcessBookingPaymentPartner(payment $payment)
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
        $this->processPaymentEmailsPartner($booking, $paymentPending);
        $this->setPaymentStatusProcessed($payment);

        $notificationService = $this->container->get("mycp.notification.service");
        $generalReservations = $this->em->getRepository('mycpBundle:generalReservation')->getReservationsByBookin($bookingId);
        foreach ($generalReservations as $generalReservation) {
            $notificationService->sendConfirmPaymentSMSNotification($generalReservation);
        }

        $ownershipReservations = $this->getOwnershipReservations($bookingId);
        $toPayAtService = 0;

        if(count($ownershipReservations)){
            $generalReservation = $ownershipReservations[0]->getOwnResGenResId();
            $generalReservationId = $generalReservation->getGenResId();
            $user = $ownershipReservations[0]->getOwnResGenResId()->getGenResUserId();
            $tourOperator = $this->em->getRepository("PartnerBundle:paTourOperator")->findOneBy(array("tourOperator" => $user->getUserId()));
            $travelAgency = $tourOperator->getTravelAgency();

            $pendingPaymentStatusPending = $this->em->getRepository("mycpBundle:nomenclator")->findOneBy(array("nom_name" => "pendingPayment_pending_status", "nom_category" => "paymentPendingStatus"));
            $completePaymentType= $this->em->getRepository("mycpBundle:nomenclator")->findOneBy(array("nom_name" => "complete_payment", "nom_category" => "paymentPendingType"));

            foreach ($ownershipReservations as $own) {
                $this->updateICal($own->getOwnResSelectedRoomId());

                if($booking->getCompletePayment()) {
                    $accommodation = $own->getOwnResGenResId()->getGenResOwnId();
                    $toPayAtService += $own->getOwnResTotalInSite() * $accommodation->getOwnCommissionPercent() / 100;

                    if ($own->getOwnResGenResId()->getGenResId() != $generalReservationId) {
                        $toDate = $own->getOwnResGenResId()->getGenResToDate();
                        $new_date = strtotime('+3 day', strtotime($toDate));
                        $payDate = new \DateTime();
                        $payDate->setTimestamp($new_date);

                        $generalReservationId = $own->getOwnResGenResId()->getGenResId();
                        $pendingPayment = new paPendingPaymentAccommodation();
                        $pendingPayment->setAmount($toPayAtService);
                        $pendingPayment->setAgency($travelAgency);
                        $pendingPayment->setBooking($booking);
                        $pendingPayment->setCreatedDate(new \DateTime());
                        $pendingPayment->setReservation($own->getOwnResGenResId());
                        $pendingPayment->setPayDate($payDate);
                        $pendingPayment->setStatus($pendingPaymentStatusPending);
                        $pendingPayment->setType($completePaymentType);

                        $this->em->persist($pendingPayment);
                        $this->em->flush();

                        $toPayAtService = 0;
                    }
                }
            }
        }

    }

    /**
     * Returns the full voucher file path of a booking according to the
     * corresponding booking ID and null in case no booking was found.
     *
     * @param $bookingId
     * @return null|string
     */
    public function getVoucherFilePathByBookingId($bookingId)
    {
        $booking = $this->em->getRepository('mycpBundle:booking')->find($bookingId);

        if (empty($booking)) {
            return null;
        }

        $user = $this->getUserFromBooking($booking);
        $userId = $user->getUserId();

        return $this->getVoucherFilePathByUserIdAndBookingId($userId, $bookingId);
    }

    /**
     * Creates the booking voucher for a booking with ID $bookingID and
     * returns the full file path to the voucher and null if it could
     * not be created. In case the voucher already exists the file path is returned.
     *
     * @param $bookingId
     * @return null|string
     */
    public function createBookingVoucherIfNotExisting($bookingId, $replaceExistingVoucher = false)
    {
        $response = $this->getPrintableBookingConfirmationResponse($bookingId);
        $pdfFilePath = $this->getVoucherFilePathByBookingId($bookingId);

        /*var_dump($response);
        die;*/

        if (file_exists($pdfFilePath)) {
            if($replaceExistingVoucher)
            {
                unlink($pdfFilePath);
            }
            else
            {
                return $pdfFilePath;
            }
        }

        $pdfService = $this->get('front_end.services.pdf');
        $success = $pdfService->storeHtmlAsPdf($response, $pdfFilePath);

        if (!$success) {
            // PDF could not be stored, so ignore attachment for now
            $pdfFilePath = null;
        }

        return $pdfFilePath;
    }
    public function createBookingVoucherIfNotExistingPartner($bookingId, $user,$replaceExistingVoucher = false)
    {
        $response = $this->getPrintableBookingConfirmationResponsePartner($bookingId,$user);
       // dump($response);die;
        $pdfFilePath = $this->getVoucherFilePathByBookingId($bookingId);

        if (file_exists($pdfFilePath)) {
            if($replaceExistingVoucher)
            {
                unlink($pdfFilePath);
            }
            else
            {
                return $pdfFilePath;
            }
        }

        $pdfService = $this->get('front_end.services.pdf');
        $success = $pdfService->storeHtmlAsPdf($response, $pdfFilePath);

        if (!$success) {
            // PDF could not be stored, so ignore attachment for now
            $pdfFilePath = null;
        }

        return $pdfFilePath;
    }

    /**
     * Creates the booking voucher for a booking with ID $bookingID and
     * returns the full file path to the voucher and null if it could
     * not be created. In case the voucher already deletes it and create a new one.
     * This is use in create new offer from a payed reservation case.
     *
     * @param $bookingId
     * @return null|string
     */
    public function createBookingVoucher($bookingId)
    {
        $response = $this->getPrintableBookingConfirmationResponse($bookingId);
        $pdfFilePath = $this->getVoucherFilePathByBookingId($bookingId);

        if (file_exists($pdfFilePath)) {
            unlink($pdfFilePath);
        }

        $pdfService = $this->get('front_end.services.pdf');
        $success = $pdfService->storeHtmlAsPdf($response, $pdfFilePath);

        if (!$success) {
            // PDF could not be stored, so ignore attachment for now
            $pdfFilePath = null;
        }

        return $pdfFilePath;
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
    public function processPaymentEmails(booking $booking, $paymentPending)
    {
        $bookingId = $booking->getBookingId();
        $user = $this->getUserFromBooking($booking);
        $userId = $user->getUserId();
        $userTourist = $this->getUserTourist($userId, $bookingId);
        $ownershipReservations = $this->getOwnershipReservations($bookingId);
        $rooms = $this->getRoomsFromReservations($ownershipReservations);

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
            $array_dates = $timeService->datesBetween(
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

        $pdfFilePath = $this->createBookingVoucherIfNotExisting($bookingId);

        // Send email to customer
        $emailService = $this->get('Email');

        $userLocale = strtolower($userTourist->getUserTouristLanguage()->getLangCode());
        $body = $this->render('FrontEndBundle:mails:boletin.html.twig', array(
            'bookId' => $bookingId,
            'user' => $user,
            'reservations' => $ownershipReservations,
            'photos' => $arrayPhotos,
            'nights' => $arrayNights,
            'user_locale' => $userLocale,
            'user_currency' => ($userTourist != null) ? $userTourist->getUserTouristCurrency() : null,
            'reservationStatus' => (count($ownershipReservations) > 0) ? $ownershipReservations[0]->getOwnResGenResId()->getGenResStatus() : generalReservation::STATUS_NONE
        ));

        $locale = $this->get('translator');
        $subject = $locale->trans('PAYMENT_CONFIRMATION', array(), "messages", $userLocale);

        $logger = $this->get('logger');
        $userEmail = trim($user->getUserEmail());

        try {
            $emailService->sendEmail(
                $subject,
                'send@mycasaparticular.com',
                $subject . ' - MyCasaParticular.com',
                $userEmail,
                $body,
                $pdfFilePath
            );

           /*$emailService->sendEmail(
                $subject,
                'send@mycasaparticular.com',
                $subject . ' - MyCasaParticular.com',
                "luiseduardo@hds.li",
                $body,
                $pdfFilePath
            );*/

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
                    'payment_pending' => $paymentPending,
                    'rooms' => $rooms,
                    'booking' => $bookingId,
                    'payedAmount' => $booking->getPayedAmount()
                )
            );

            try {
               /* $emailService->sendEmail(
                    'Confirmación de pago',
                    'no-reply@mycasaparticular.com',
                    'MyCasaParticular.com',
                    'reservation@mycasaparticular.com',
                    $bodyRes
                );*/

                $emailService->sendEmail(
                    'Confirmación de pago',
                    'no-reply@mycasaparticular.com',
                    'MyCasaParticular.com',
                    'confirmacion@mycasaparticular.com',
                    $bodyRes
                );

                $logger->info('Successfully sent email to reservation team. Booking ID: ' . $bookingId);
            } catch (\Exception $e) {
                $logger->error('EMAIL: Could not send Email to reservation team. Booking ID: ' . $bookingId);
                $logger->error($e->getMessage());
            }
        }

        // send email to accommodation owner
        foreach ($arrayOwnershipReservationByHouse as $key => $owns) {

            $fromToTravel = $this->em->getRepository('mycpBundle:ownershipReservation')->getFromToDestinationCliente($key,$user->getUserId(), date_format($owns[0]->getOwnResReservationFromDate(), 'Y-m-d'), date_format($owns[0]->getOwnResReservationToDate(), 'Y-m-d'));
            $houseFrom = null;
            if (array_key_exists('from', $fromToTravel)){
                $houseFrom = $this->em->getRepository('mycpBundle:ownership')->findOneBy(array('own_id' => $fromToTravel['from']));
            }
            $houseTo = null;
            if (array_key_exists('to', $fromToTravel)){
                $houseTo = $this->em->getRepository('mycpBundle:ownership')->findOneBy(array('own_id' => $fromToTravel['to']));
            }

            $bodyOwner = $this->render(
                'FrontEndBundle:mails:email_house_confirmation.html.twig',
                array(
                    'user' => $user,
                    'user_tourist' => array($userTourist),
                    'reservations' => $owns,
                    'nights' => $arrayNightsByOwnershipReservation,
                    'rooms' => $rooms,
                    'booking' => $bookingId,
                    'houseFrom' => $houseFrom,
                    'houseTo' => $houseTo
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
                    $emailService->sendEmail(
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

            //Suscripción al evento para feedback
            $dispatcher = $this->get('event_dispatcher');
            $eventData = new \MyCp\mycpBundle\JobData\GeneralReservationJobData($owns[0]->getOwnResGenResId());
            $dispatcher->dispatch('mycp.event.feedback', new FixedDateJobEvent($owns[0]->getOwnResGenResId()->getGenResToDate(),$eventData));
        }
    }

    /**
     * @param booking $booking
     * @param $paymentPending
     */
    public function processPaymentEmailsPartner(booking $booking, $paymentPending)
    {
        $bookingId = $booking->getBookingId();
        $user = $this->getUserFromBooking($booking);
        $userId = $user->getUserId();

        //$userTourist = $this->getUserTourist($userId, $bookingId);
        $ownershipReservations = $this->getOwnershipReservations($bookingId);
        $rooms = $this->getRoomsFromReservations($ownershipReservations);

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

            $array_dates = $timeService->datesBetween(
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

        $pdfFilePath = $this->createBookingVoucherIfNotExistingPartner($bookingId,$user);

        // Send email to customer
        $emailService = $this->get('Email');

        $userLocale = strtolower($user->getUserLanguage()->getLangCode());
        $body = $this->render('PartnerBundle:Mail:voucher.html.twig', array(
            'user_locale' => $userLocale,
            'user_currency' => $user->getUserCurrency(),
            'user'=>$user
        ));

        $locale = $this->get('translator');
        $subject = $locale->trans('PAYMENT_CONFIRMATION', array(), "messages", $userLocale);

        $logger = $this->get('logger');
        $userEmail = trim($user->getUserEmail());

        try {
            $emailService->sendEmail(
                $subject,
                'send@mycasaparticular.com',
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
        /*foreach ($arrayOwnershipReservationByHouse as $owns) {
            $bodyRes = $this->render(
                'PartnerBundle:Mail:rt_payment_confirmation.html.twig',
                array(
                    'user' => $user,
                    'user_tourist' => $user,
                    'reservations' => $owns,
                    'nights' => $arrayNightsByOwnershipReservation,
                    'payment_pending' => $paymentPending,
                    'rooms' => $rooms,
                    'booking' => $bookingId
                )
            );

            try {
                $emailService->sendEmail(
                    'Confirmación de pago',
                    'no-reply@mycasaparticular.com',
                    'MyCasaParticular.com',
                    'confirmacion@mycasaparticular.com',
                    $bodyRes
                );

                $logger->info('Successfully sent email to reservation team. Booking ID: ' . $bookingId);
            } catch (\Exception $e) {
                $logger->error('EMAIL: Could not send Email to reservation team. Booking ID: ' . $bookingId);
                $logger->error($e->getMessage());
            }
        }*/

        // send email to accommodation owner
       /* foreach ($arrayOwnershipReservationByHouse as $owns) {
            $bodyOwner = $this->render(
                'PartnerBundle:Mail:email_house_confirmation.html.twig',
                array(
                    'user' => $user,
                    'user_tourist' => $user,
                    'reservations' => $owns,
                    'nights' => $arrayNightsByOwnershipReservation,
                    'rooms' => $rooms,
                    'booking' => $bookingId
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
                    $emailService->sendEmail(
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

            //Suscripción al evento para feedback
            $dispatcher = $this->get('event_dispatcher');
            $eventData = new \MyCp\mycpBundle\JobData\GeneralReservationJobData($owns[0]->getOwnResGenResId());
            $dispatcher->dispatch('mycp.event.feedback', new FixedDateJobEvent($owns[0]->getOwnResGenResId()->getGenResToDate(),$eventData));
        }*/
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
                $this->em->flush();

                $ownership = $generalReservation->getGenResOwnId();
                $this->em->getRepository("mycpBundle:ownership")->updateRanking($ownership);

            }

            $this->em->persist($own);
        }

        $this->em->flush();
    }

    private function updateICal($roomId) {
        try {
            $calendarService = $this->get('mycp.service.calendar');
            $room = $this->em->getRepository("mycpBundle:room")->find($roomId);
            $calendarService->createICalForRoom($room->getRoomId(), $room->getRoomCode());
            return "Se actualizó satisfactoriamente el fichero .ics asociado a esta habitación.";
        } catch (\Exception $e) {
            return "Ha ocurrido un error mientras se actualizaba el fichero .ics de la habitación. Error: " . $e->getMessage();
        }
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
            ->findBy(array('own_res_reservation_booking' => $bookingId), array("own_res_gen_res_id" => "ASC"));
        return $ownershipReservations;
    }

    /**
     * Returns the voucher file path of a booking according to the user ID and the booking ID.
     *
     * @param $userId
     * @param $bookingId
     * @return string
     */
    private function getVoucherFilePathByUserIdAndBookingId($userId, $bookingId)
    {
        $pdfName = 'voucher' . $userId . '_' . $bookingId;
        return $this->getVoucherPdfFilePath($pdfName);
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

    private function getRoomsFromReservations($ownershipReservations)
    {
        $rooms = array();

        foreach($ownershipReservations as $reservation)
        {
            $room = $this->em->getRepository('mycpBundle:room')->find($reservation->getOwnResSelectedRoomId());

            $rooms[$reservation->getOwnResId()] = $room;
        }

        return $rooms;
    }

    /**
     * @param array $reservations_ids
     * @param int $type   De tipo 1 es una cancelación de tipo propietario
     * @param $cancel_date      Fecha de cancelación
     * @param string $reason    Motivo
     * @return JsonResponse
     */
    public function cancelReservations($reservations_ids=array(),$type=1,$cancel_date,$reason='',$give_tourist=true,$by_system=false) {

        $notificationService = $this->container->get("mycp.notification.service");
        if(count($reservations_ids)) {
            //Servicios
            $templatingService = $this->container->get('templating');
            $emailService = $this->container->get('mycp.service.email_manager');
            $service_time = $this->get('time');
            //Variables
            $rooms = array();
            $total_nights = array();

            //Obtener datos de los repositorios
            $onReservation = $this->em->getRepository('mycpBundle:ownershipReservation')->find($reservations_ids[0]);
            $booking = $onReservation->getOwnResReservationBooking();
            $idBooking = $booking->getBookingId();

            $min_date = $this->em->getRepository('mycpBundle:ownershipReservation')->getBookingById($idBooking);
            $payment = $this->em->getRepository('mycpBundle:payment')->findOneBy(array("booking" => $idBooking));
            $user_tourist = $this->em->getRepository('mycpBundle:userTourist')->findOneBy(array('user_tourist_user' => $payment->getBooking()->getBookingUserId()));

            $min_date_arrive = \MyCp\mycpBundle\Helpers\Dates::createFromString($min_date[0]['arrivalDate'], '-', 1);
            $date_cancel_payment = \MyCp\mycpBundle\Helpers\Dates::createDateFromString($cancel_date, '/', 1);

            //if($date_cancel_payment<$min_date_arrive){
            //Se calcula la diferencia entre las fechas de cancelación y la mínima reserva
            $day = $service_time->diffInDays($min_date_arrive->format("Y-m-d"), $date_cancel_payment->format("Y-m-d"));
            //Se crea el objeto de cancelación
            $obj = new cancelPayment();
            $generalReserv = $onReservation->getOwnResGenResId();
            $flag = false;
            $failure_flag = false;
            if(count($booking->getBookingOwnReservations()) == count($reservations_ids))
                $flag = true;

            //Change status reservations
            if(count($reservations_ids)) {
                foreach ($reservations_ids as $genResId) {
                    $reservation = $this->em->getRepository('mycpBundle:ownershipReservation')->find($genResId);
                    $reservation->setOwnResStatus(ownershipReservation::STATUS_CANCELLED);
                    $this->em->persist($reservation);
                    $obj->addOwnershipReservation($reservation);
                }
            }
            //Set booking save relations
            $obj->setBooking($booking);
            if($by_system) {
                $user = $this->em->getRepository('mycpBundle:generalReservation')->getUserByOwnershipReservations($reservations_ids[0]);
                $obj->setUser($this->em->getRepository('mycpBundle:user')->find($user[0]['user_id']));
            }
            else
                //Set user save relations
                $obj->setUser($this->getUser());

            $obj->setType($this->em->getRepository('mycpBundle:cancelType')->find($type));
            $obj->setCancelDate(\MyCp\mycpBundle\Helpers\Dates::createDateFromString($cancel_date, '/', 1));
            $obj->setGiveTourist($give_tourist);
            $this->em->persist($obj);
            //$this->em->flush();

            if($type == 1)//Si el tipo de cancelación es de propietario
            {
                $price_tourist = $this->calculateTourist($reservations_ids, true);
                if(count($booking->getBookingOwnReservations()) == count($reservations_ids)) {     //es que las cancelo todas
                    $total_price = ($price_tourist['price'] + $price_tourist['fixed']) * $payment->getCurrentCucChangeRate();
                }
                else {
                    $total_price = ($price_tourist['price']) * $payment->getCurrentCucChangeRate();
                }
                //Se registra un Pago Pendiente a Turista
                $pending_tourist = new pendingPaytourist();
                $pending_tourist->setCancelId($obj);
                $pending_tourist->setPayAmount($total_price);
                $pending_tourist->setUserTourist($user_tourist);
                if($by_system) {
                    $user = $this->em->getRepository('mycpBundle:generalReservation')->getUserByOwnershipReservations($reservations_ids[0]);
                    $pending_tourist->setUser($this->em->getRepository('mycpBundle:user')->find($user[0]['user_id']));
                }
                else
                    $pending_tourist->setUser($this->getUser());
                $pending_tourist->setRegisterDate(new \DateTime(date('Y-m-d')));

                $date_pay = \MyCp\mycpBundle\Helpers\Dates::createDateFromString($cancel_date, '/', 1);
                $date = $service_time->add("+1 days", $date_pay->format('Y/m/d'), "Y/m/d");
                $pending_tourist->setPaymentDate(\MyCp\mycpBundle\Helpers\Dates::createFromString($date, '/', 1));

                $pending_tourist->setType($this->em->getRepository('mycpBundle:nomenclator')->findOneBy(array("nom_name" => 'pendingPayment_pending_status')));
                $this->em->persist($pending_tourist);

                //Se penaliza la casa en el ranking
                if(count($reservations_ids)) {   //Debo de recorrer cada una de las habitaciones para de ellas sacar las casas
                    $array_id_ownership = array();
                    foreach ($reservations_ids as $genResId) {
                        $ownershipReservation = $this->em->getRepository('mycpBundle:ownershipReservation')->find($genResId);
                        if(!in_array($ownershipReservation->getOwnResGenResId()->getGenResOwnId()->getOwnId(), $array_id_ownership)) {
                            $failure = $this->em->getRepository('mycpBundle:failure')->findBy(array("reservation" => $ownershipReservation->getOwnResGenResId()->getGenResId()));
                            if(count($failure) == 0) {
                                //Registro un fallo de tipo propietario
                                $failure_own = new failure();
                                $failure_own->setUser($this->getUser());
                                $failure_own->setAccommodation($ownershipReservation->getOwnResGenResId()->getGenResOwnId());
                                $failure_own->setReservation($ownershipReservation->getOwnResGenResId());
                                $failure_own->setType($this->em->getRepository('mycpBundle:nomenclator')->findOneBy(array("nom_name" => 'accommodation_failure')));
                                $failure_own->setDescription($reason);
                                $failure_own->setCreationDate(new \DateTime());
                                $this->em->persist($failure_own);
                            }
                            //Adiciono el id de la casa al arreglo de casas
                            $array_id_ownership[] = $ownershipReservation->getOwnResGenResId()->getGenResOwnId()->getOwnId();
                        }
                    }
                }
            }
            if($type == 2)//Si el tipo de cancelación  es de turista
            {

                if($day > 7 || ($day <= 7 && $day >= 3) && $date_cancel_payment < $min_date_arrive) {  //Antes  de los 7 días de llegada del turista:
                    $price_tourist = ($day > 7) ? $this->calculateTourist($reservations_ids, false) : $this->calculateTourist($reservations_ids, true);
                    if(count($booking->getBookingOwnReservations()) == count($reservations_ids)) {
                        $total_price = $price_tourist['price'] + $price_tourist['fixed'];
                    }
                    else {
                        $total_price = ($price_tourist['price']);
                    }

                    //Se registra un Pago Pendiente a Turista
                    $pending_tourist = new pendingPaytourist();
                    $pending_tourist->setCancelId($obj);
                    if($day > 7)
                        $pending_tourist->setPayAmount($total_price * $payment->getCurrentCucChangeRate());
                    if($day <= 7 && $day >= 3) {
                        $pending_tourist->setPayAmount(($total_price * 0.5) * $payment->getCurrentCucChangeRate());
                    }


                    $pending_tourist->setUserTourist($user_tourist);
                    $pending_tourist->setUser($this->getUser());
                    $pending_tourist->setRegisterDate(new \DateTime(date('Y-m-d')));

                    $date_pay = \MyCp\mycpBundle\Helpers\Dates::createDateFromString($cancel_date, '/', 1);
                    $date = $service_time->add("+1 days", $date_pay->format('Y/m/d'), "Y/m/d");
                    $pending_tourist->setPaymentDate(\MyCp\mycpBundle\Helpers\Dates::createFromString($date, '/', 1));

                    $pending_tourist->setType($this->em->getRepository('mycpBundle:nomenclator')->findOneBy(array("nom_name" => 'pendingPayment_pending_status')));
                    $this->em->persist($pending_tourist);

                    //Array $ownershipReservation para mandar el correo
                    $ownershipReservations = array();
                    //Se de da putos en el ranking a la casa
                    if(count($reservations_ids)) {   //Debo de recorrer cada una de las habitaciones para de ellas sacar las casas
                        $array_id_ownership = array();
                        foreach ($reservations_ids as $genResId) {
                            $ownershipReservation = $this->em->getRepository('mycpBundle:ownershipReservation')->find($genResId);
                            if(!in_array($ownershipReservation->getOwnResGenResId()->getGenResOwnId()->getOwnId(), $array_id_ownership)) {
                                $failure = $this->em->getRepository('mycpBundle:failure')->findBy(array("reservation" => $ownershipReservation->getOwnResGenResId()->getGenResId()));
                                if(count($failure) == 0) {
                                    //Registro un fallo de tipo turista
                                    $failure_tourist = new failure();
                                    $failure_tourist->setUser($this->getUser());
                                    $failure_tourist->setAccommodation($ownershipReservation->getOwnResGenResId()->getGenResOwnId());
                                    $failure_tourist->setReservation($ownershipReservation->getOwnResGenResId());
                                    $failure_tourist->setType($this->em->getRepository('mycpBundle:nomenclator')->findOneBy(array("nom_name" => 'tourist_failure')));
                                    $failure_tourist->setDescription($reason);
                                    $failure_tourist->setCreationDate(new \DateTime());
                                    $this->em->persist($failure_tourist);
                                    $failure_flag = true;
                                }

                                //Se envia un sms al prpietario
                                $notificationService->sendSMSReservationsCancel($ownershipReservation);

                                //Adiciono el id de la casa al arreglo de casas
                                $array_id_ownership[] = $ownershipReservation->getOwnResGenResId()->getGenResOwnId()->getOwnId();
                                //Adiciono al arreglo de reservaciones
                                $ownershipReservations[] = $ownershipReservation;
                                //Adiciono las rooms de esa casa
                                array_push($rooms, $this->em->getRepository('mycpBundle:room')->find($ownershipReservation->getOwnResSelectedRoomId()));
                                $temp_total_nights = 0;
                                $nights = $service_time->nights($ownershipReservation->getOwnResReservationFromDate()->getTimestamp(), $ownershipReservation->getOwnResReservationToDate()->getTimestamp());
                                $temp_total_nights += $nights;
                                array_push($total_nights, $temp_total_nights);
                            }

                        }
                    }

                }
                else if($day <= 7) {   //Despues de los 7 días antes de la fecha de llegada

                    if(count($reservations_ids)) {   //Debo de recorrer cada una de las habitaciones para de ellas sacar las casas
                        $array_id_ownership = array();
                        foreach ($reservations_ids as $genResId) {
                            $ownershipReservation = $this->em->getRepository('mycpBundle:ownershipReservation')->find($genResId);
                            if($day <= 2)
                                $price = $this->calculatePriceOwnOtherCase($ownershipReservation->getOwnResReservationFromDate(), $ownershipReservation->getOwnResReservationToDate(), $ownershipReservation->getOwnResTotalInSite(), $ownershipReservation->getOwnResGenResId()->getGenResOwnId()->getOwnCommissionPercent());
                            else
                                $price = $this->calculatePriceOwn($ownershipReservation->getOwnResReservationFromDate(), $ownershipReservation->getOwnResReservationToDate(), $ownershipReservation->getOwnResTotalInSite(), $ownershipReservation->getOwnResGenResId()->getGenResOwnId()->getOwnCommissionPercent());
                            if(!array_key_exists($ownershipReservation->getOwnResGenResId()->getGenResOwnId()->getOwnId(), $array_id_ownership)) {
                                $failure = $this->em->getRepository('mycpBundle:failure')->findBy(array("reservation" => $ownershipReservation->getOwnResGenResId()->getGenResId()));
                                if(count($failure) == 0) {
                                    //Se le da puntos positivos en el Ranking a la casa
                                    //Registro un fallo de tipo turista
                                    if(!$failure_flag) {
                                        $failure_tourist = new failure();
                                        $failure_tourist->setUser($this->getUser());
                                        $failure_tourist->setAccommodation($ownershipReservation->getOwnResGenResId()->getGenResOwnId());
                                        $failure_tourist->setReservation($ownershipReservation->getOwnResGenResId());
                                        $failure_tourist->setType($this->em->getRepository('mycpBundle:nomenclator')->findOneBy(array("nom_name" => 'tourist_failure')));
                                        $failure_tourist->setDescription($reason);
                                        $failure_tourist->setCreationDate(new \DateTime());
                                        $this->em->persist($failure_tourist);
                                    }
                                }
                                //Adiciono el id de la casa al arreglo de casas
                                $array_id_ownership[$ownershipReservation->getOwnResGenResId()->getGenResOwnId()->getOwnId()] = array('idown' => $ownershipReservation->getOwnResGenResId()->getGenResOwnId()->getOwnId(), 'price' => $price, 'ownershipReservations' => array($ownershipReservation), 'arrival_date' => $ownershipReservation->getOwnResReservationFromDate());
                            }
                            else {
                                $array_id_ownership[$ownershipReservation->getOwnResGenResId()->getGenResOwnId()->getOwnId()]['price'] = $array_id_ownership[$ownershipReservation->getOwnResGenResId()->getGenResOwnId()->getOwnId()]['price'] + $price;
                                $array_id_ownership[$ownershipReservation->getOwnResGenResId()->getGenResOwnId()->getOwnId()]['ownershipReservations'][] = $ownershipReservation;
                            }
                        }

                        foreach ($array_id_ownership as $item) {
                            $ownership = $this->em->getRepository('mycpBundle:ownership')->find($item['idown']);
                            //Se registra un Pago Pendiente a Propietario
                            if($item['price'] > 0) {
                                $pending_own = new pendingPayown();
                                $pending_own->setCancelId($obj);
                                $pending_own->setPayAmount($item['price']);
                                $pending_own->setUserCasa($ownership);
                                $pending_own->setType($this->em->getRepository('mycpBundle:nomenclator')->findOneBy(array("nom_name" => 'pendingPayment_pending_status')));
                                $pending_own->setUser($this->getUser());
                                $pending_own->setRegisterDate(new \DateTime(date('Y-m-d')));
                                $dateRangeFrom = $service_time->add("+3 days", $item['arrival_date']->format('Y/m/d'), "Y/m/d");
                                $pending_own->setPaymentDate(\MyCp\mycpBundle\Helpers\Dates::createFromString($dateRangeFrom, '/', 1));
                                $this->em->persist($pending_own);

                                //Se envia un sms al prpietario
                                $notificationService->sendSMSReservationsCancel($ownershipReservation, $item['price']);
                            }
                        }
                    }
                }
                //}
                //Change status reservations
                if(count($reservations_ids)) {
                    foreach ($reservations_ids as $genResId) {
                        $reservation = $this->em->getRepository('mycpBundle:ownershipReservation')->find($genResId);
                        $reservation->setOwnResStatus(ownershipReservation::STATUS_CANCELLED);
                        $this->em->persist($reservation);
                        $obj->addOwnershipReservation($reservation);
                    }
                }
                //}

                if(count($booking->getBookingOwnReservations()) == count($obj->getOwnreservations()))
                    $flag = true;
                if($flag)
                    $generalReserv->setGenResStatus(generalReservation::STATUS_CANCELLED);
                else
                    $generalReserv->setGenResStatus(generalReservation::STATUS_PARTIAL_CANCELLED);
                $this->em->persist($generalReserv);
                $this->em->flush();
                return array('success' => true, 'message' => 'Se ha cancelado satisfactoriamente');
            }
            else
                return array('success' => false, 'message' => 'Debe de seleccionar algún CAS a cancelar');
        }
    }

    /**
     * @param $reservations_ids
     * @return array
     */
    public function calculateTourist($reservations_ids,$sum_tax){
        $service_time = $this->get('time');
        $price=0;
        $fixed=0;
        if(count($reservations_ids)){
            foreach($reservations_ids as $genResId){
                $ownershipReservation=$this->em->getRepository('mycpBundle:ownershipReservation')->find($genResId);
                $generalReservation = $ownershipReservation->getOwnResGenResId();
                if($fixed==0)
                    $fixed=$generalReservation->getServiceFee()->getFixedFee();
                $price =$price+ $this->em->getRepository('mycpBundle:ownershipReservation')->cancelReservationByTourist($this->em->getRepository('mycpBundle:ownershipReservation')->find($genResId),$service_time,$sum_tax);
            }
        }
        return array('price'=>$price,'fixed'=>$fixed);

    }

    /**
     * @param $from
     * @param $to
     * @param $price_total_in_site
     * @param $commission_percent
     * @return float
     */
    public function calculatePriceOwn($from,$to,$price_total_in_site,$commission_percent){
        $service_time = $this->get('time');
        $day=$service_time->diffInDays($to->format("Y-m-d"), $from->format("Y-m-d"));
        $price=0;
        if($day>3 && $day<6){
            $price=($price_total_in_site/$day)*(1-$commission_percent/100)*0.5;
        }
        if($day>=6){
            $price=($price_total_in_site/$day)*(1-$commission_percent/100);
        }
        return $price;
    }
    /**
     * @param $from
     * @param $to
     * @param $price_total_in_site
     * @param $commission_percent
     * @return float
     */
    public function calculatePriceOwnOtherCase($from,$to,$price_total_in_site,$commission_percent){
        $service_time = $this->get('time');
        $day=$service_time->diffInDays($to->format("Y-m-d"), $from->format("Y-m-d"));
        $price=0;
        if($day>0 && $day<3){
            $price=($price_total_in_site/$day)*(1-$commission_percent/100)*0.5;
        }
        if($day>=3){
            $price=($price_total_in_site/$day)*(1-$commission_percent/100);
        }
        return $price;
    }

    /**
     * @param $subject
     * @param $body
     * @param $from
     * @param $to
     */
    public function sendMailCancelPayment($subject,$body,$from,$to){
        //Servicios
        $emailService = $this->container->get('mycp.service.email_manager');
        $emailService->sendEmail($from, $subject,  $body, $to);
    }

}
