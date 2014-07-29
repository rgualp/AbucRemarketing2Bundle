<?php

namespace MyCp\FrontEndBundle\Service;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class BookingService extends Controller
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * Service charge in CUC
     * @var float
     */
    private $serviceChargeInCuc;

    public function __construct(EntityManager $em, $serviceChargeInCuc)
    {
        $this->em = $em;
        $this->serviceChargeInCuc = (float)$serviceChargeInCuc;
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
        $payment = $this->getPayment($booking);
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
    private function getPayment($booking)
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
