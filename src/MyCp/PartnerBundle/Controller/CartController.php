<?php

namespace MyCp\PartnerBundle\Controller;

use MyCp\mycpBundle\Entity\booking;
use MyCp\mycpBundle\Entity\generalReservation;
use MyCp\mycpBundle\Entity\ownershipReservation;
use MyCp\mycpBundle\Form\restorePasswordUserType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use MyCp\PartnerBundle\Entity\paTravelAgency;
use MyCp\PartnerBundle\Entity\paTourOperator;

use MyCp\PartnerBundle\Form\paTravelAgencyType;
use Symfony\Component\HttpFoundation\JsonResponse;
use MyCp\mycpBundle\Entity\user;
use MyCp\mycpBundle\Entity\photo;

class CartController extends Controller
{
    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        /*identificadores de general reservation*/
        $ids_gr = $request->get('ids');
        $ids_gr = ($ids_gr != null) ? $ids_gr:array();

        $session = $request->getSession();
        $ids_from_session = $session->get("ri_ids");
        $ids_from_session = ($ids_from_session != null) ? $ids_from_session:array();

        $ids_gr = array_merge($ids_gr, $ids_from_session);
        $session->remove("ri_ids");

        $user = $this->getUser();
        $tourOperator = $em->getRepository("PartnerBundle:paTourOperator")->findOneBy(array("tourOperator" => $user->getUserId()));
        $travelAgency = $tourOperator->getTravelAgency();

        $cartItems = $em->getRepository("PartnerBundle:paReservation")->getCartItems($travelAgency, $ids_gr);

        $reservations = array();
        $reservationsIds = array();

        foreach($ids_gr as $idGenRes){
            $genRes = $em->getRepository("mycpBundle:generalReservation")->find($idGenRes);
            $details = $genRes->getTravelAgencyDetailReservations();
            $reservation = $details[0]->getReservation();

            if(!array_key_exists($reservation->getId(), $reservations)) {
                $reservations[$reservation->getId()] = array();
                $reservationsIds[] = $reservation->getId();
            }

            $reservations[$reservation->getId()][] = $idGenRes;
        }

        $details = array();

        foreach($reservationsIds as $reservationId)
        {
            $details[$reservationId] = $em->getRepository('PartnerBundle:paReservation')->getDetails($reservationId, $reservations[$reservationId]);
        }

        return $this->render('PartnerBundle:Cart:cart.html.twig', array(
            "items" => $cartItems,
            "details" => $details,
            "disablePaymentButton" => (count($ids_gr) > 0)
        ));
    }

    public function showReservationsDetailsAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        /*$user = $this->getUser();
        $tourOperator = $em->getRepository("PartnerBundle:paTourOperator")->findOneBy(array("tourOperator" => $user->getUserId()));*/
        $reservationId = $request->get("idReservation");

        $list = $em->getRepository('PartnerBundle:paReservation')->getDetails($reservationId);

        $response = $this->renderView('PartnerBundle:Cart:cart_body.html.twig', array(
            'list' => $list
        ));

        return new JsonResponse([
            'success' => true,
            'html' => $response,
            'message' => ""

        ]);
    }

    public function deleteFromCartAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $tourOperator = $em->getRepository("PartnerBundle:paTourOperator")->findOneBy(array("tourOperator" => $user->getUserId()));
        $travelAgency = $tourOperator->getTravelAgency();

        $reservationId = $request->get("reservationId");
        $reservation = $em->getRepository("PartnerBundle:paReservation")->findOneBy(array("id" => $reservationId));

        foreach($reservation->getDetails() as $detail)
        {
            $generalReservation = $detail->getReservationDetail();
            $ownershipReservations = $generalReservation->getOwn_reservations();

            foreach($ownershipReservations as $ownRes)
            {
                $ownRes->setOwnResStatus(ownershipReservation::STATUS_CANCELLED);
                $em->persist($ownRes);
            }

            $generalReservation->setGenResStatus(generalReservation::STATUS_CANCELLED);
            $generalReservation->setGenResStatusDate(new \DateTime());
            $generalReservation->setCanceledBy($this->getUser());
            $em->persist($generalReservation);
        }

        $em->flush();

        $cartItems = $em->getRepository("PartnerBundle:paReservation")->getCartItems($travelAgency);
        $response = $this->renderView('PartnerBundle:Cart:cart_reservations.html.twig', array(
            'items' => $cartItems
        ));

        return new JsonResponse([
            'success' => true,
            'html' => $response,
            'message' => ""

        ]);
    }

    public function emptyCartAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $tourOperator = $em->getRepository("PartnerBundle:paTourOperator")->findOneBy(array("tourOperator" => $user->getUserId()));
        $travelAgency = $tourOperator->getTravelAgency();

        $reservations = $em->getRepository("PartnerBundle:paReservation")->getReservationsInCart($travelAgency);

        foreach($reservations as $reservation) {
            foreach ($reservation->getDetails() as $detail) {
                $generalReservation = $detail->getReservationDetail();
                $ownershipReservations = $generalReservation->getOwn_reservations();

                foreach ($ownershipReservations as $ownRes) {
                    $ownRes->setOwnResStatus(ownershipReservation::STATUS_CANCELLED);
                    $em->persist($ownRes);
                }

                $generalReservation->setGenResStatus(generalReservation::STATUS_CANCELLED);
                $generalReservation->setGenResStatusDate(new \DateTime());
                $generalReservation->setCanceledBy($user);
                $em->persist($generalReservation);
            }
        }

        $em->flush();

        return $this->redirect($this->generateUrl("partner_dashboard_cart"));
    }

    public function showReservationsToPayAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $ownReservationIds = $request->get("checkValues");
        $timer = $this->get("Time");
        $user = $this->getUser();
        $currentTourOperator = $em->getRepository("PartnerBundle:paTourOperator")->findOneBy(array("tourOperator" => $user->getUserId()));
        $currentTravelAgency = $currentTourOperator->getTravelAgency();
        $agencyPackage = $currentTravelAgency->getAgencyPackages()[0];
        $completePayment = $agencyPackage->getPackage()->getCompletePayment();

        $list = $em->getRepository('PartnerBundle:paReservation')->getDetailsByIds($ownReservationIds);
        $payments = array();

        $totalPrepayment = 0;
        $totalAccommodationPayment = 0;
        $totalServicesTax = 0;
        $totalPercentAccommodationPrepayment = 0;
        $totalAgencyCommission = 0;
        $totalOnlinePayment = 0;
        $totalTouristAgencyTax = 0;

        $totalTransferFee = 0;

        $currentServiceFee = $em->getRepository("mycpBundle:serviceFee")->getCurrent();
        $fixedFee = $currentServiceFee->getFixedFee();

        $itemsTotal = 0;

        foreach($list as $item)
        {
            $itemsTotal++;

            $touristFee = $em->getRepository("mycpBundle:serviceFee")->calculateTouristServiceFeeByGeneralReservation($item["gen_res_id"], $timer);
            $commission = $item["totalInSite"]*$item["commission"]/100;

            $totalTouristAgencyTax += $touristFee;

            $reservations = $em->getRepository("mycpBundle:generalReservation")->getReservationCart($item["gen_res_id"], $ownReservationIds);

            if(!$completePayment) {
                $totalPrepayment += $commission + $touristFee;
                $totalAccommodationPayment += $item["totalInSite"];
                $totalServicesTax += $touristFee;
                $totalPercentAccommodationPrepayment += $commission;

                $dinner = ($item["dinner"] != null) ? $item["dinner"] : 0;
                $breakfast = ($item["breakfast"] != null) ? $item["breakfast"] : 0;
                $totalPayment += $dinner + $breakfast;

                $payments[$item["gen_res_id"]] = array(
                    "totalPayment" => $item["totalInSite"] + $touristFee,
                    "totalPrepayment" => $commission + $touristFee,
                    "payAtAccommodation" => $item["totalInSite"] - $commission,
                    "touristFee" => $touristFee,
                    "commission" => $commission,
                    "commissionPercent" => $item["commission"],
                    "reservations" => $reservations,
                    "lodgingPrice" => $item["totalInSite"],
                    "fixedFee" => $fixedFee,
                    "taxFees" => $touristFee + $fixedFee
                );
            }
            else{ // agregar la tarfia fija a los calculos
                $subTotal = ($itemsTotal == count($list)) ? ($item["totalInSite"] + $touristFee + $fixedFee) : ($item["totalInSite"] + $touristFee);
                $addFixedFee = ($itemsTotal == count($list)) ? $fixedFee : 0;
                $transferFee =  0.1 *  $subTotal;
                $agencyCommission = $subTotal * $currentTravelAgency->getCommission() / 100;
                $totalPayment = $item["totalInSite"] + $touristFee + $transferFee + $addFixedFee;
                $dinner = ($item["dinner"] != null) ? $item["dinner"] : 0;
                $breakfast = ($item["breakfast"] != null) ? $item["breakfast"] : 0;
                $totalPayment += $dinner + $breakfast;

                $totalOnlinePayment += $totalPayment;

                $totalPrepayment += $totalPayment + $agencyCommission;

                $totalAccommodationPayment += $item["totalInSite"];
                $totalServicesTax += $touristFee + $transferFee;

                $totalTransferFee += $transferFee;
                $totalAgencyCommission += $agencyCommission;

                $payments[$item["gen_res_id"]] = array(
                    "totalPayment" => $totalPayment +  $agencyCommission,
                    "transferFee" => $transferFee,
                    "agencyCommission" => $agencyCommission,
                    "onlinePayment" => $totalPayment,
                    "agencyFee" => $touristFee,
                    "reservations" => $reservations,
                    "lodgingPrice" => $item["totalInSite"],
                    "agencyCommissionPercent" => $currentTravelAgency->getCommission(),
                    "fixedFee" => $fixedFee,
                    "taxFees" => $touristFee + $transferFee,
                );
            }
        }

        if(!$completePayment)
            $totalPrepayment += $currentServiceFee->getFixedFee();
        else
        {
            /*$totalPrepayment += 1.1*$currentServiceFee->getFixedFee();
            $totalTransferFee += 0.1*$currentServiceFee->getFixedFee();
            $totalAgencyCommission += $currentServiceFee->getFixedFee() * $currentTravelAgency->getCommission() / 100;*/
            $totalOnlinePayment = $totalPrepayment - $totalAgencyCommission;
        }

        $response = $this->renderView('PartnerBundle:Cart:selected_to_pay.html.twig', array(
            'items' => $list,
            'payments' => $payments,
            'completePayment' => $completePayment
        ));

        //$totalPayAtAccommodation = ($completePayment) ? $totalAccommodationPayment : $totalAccommodationPayment - $totalPercentAccommodationPrepayment;
        if(!$completePayment) {
            return new JsonResponse([
                'success' => true,
                'html' => $response,
                'message' => "",
                'completePayment' => $completePayment,
                'totalPrepayment' => $totalPrepayment,
                'totalPrepaymentTxt' => number_format($totalPrepayment * $user->getUserCurrency()->getCurrCucChange(), 2) . " " . $user->getUserCurrency()->getCurrSymbol(),
                'totalAccommodationPayment' => $totalAccommodationPayment,
                'totalAccommodationPaymentTxt' => number_format($totalAccommodationPayment * $user->getUserCurrency()->getCurrCucChange(), 2) . " " . $user->getUserCurrency()->getCurrSymbol(),
                'totalServiceTaxPayment' => $totalServicesTax,
                'totalServiceTaxPaymentTxt' => number_format($totalServicesTax * $user->getUserCurrency()->getCurrCucChange(), 2) . " " . $user->getUserCurrency()->getCurrSymbol(),
                'fixedTax' => $fixedFee,
                'fixedTaxTxt' => number_format($fixedFee * $user->getUserCurrency()->getCurrCucChange(), 2) . " " . $user->getUserCurrency()->getCurrSymbol(),
                'totalPayment' => $totalAccommodationPayment + $totalServicesTax + $fixedFee,
                'totalPaymentTxt' => number_format(($totalAccommodationPayment + $totalServicesTax + $fixedFee) * $user->getUserCurrency()->getCurrCucChange(), 2) . " " . $user->getUserCurrency()->getCurrSymbol(),
                'totalPercentAccommodationPrepayment' => $totalPercentAccommodationPrepayment,
                'totalPercentAccommodationPrepaymentTxt' => number_format($totalPercentAccommodationPrepayment * $user->getUserCurrency()->getCurrCucChange(), 2) . " " . $user->getUserCurrency()->getCurrSymbol(),
                'totalPayAtAccommodationPayment' => $totalAccommodationPayment - $totalPercentAccommodationPrepayment,
                'totalPayAtAccommodationPaymentTxt' => number_format(($totalAccommodationPayment - $totalPercentAccommodationPrepayment) * $user->getUserCurrency()->getCurrCucChange(), 2) . " " . $user->getUserCurrency()->getCurrSymbol(),
                "totalTouristAgencyTax" => $totalTouristAgencyTax

            ]);
        }
        else {
            return new JsonResponse([
                'success' => true,
                'html' => $response,
                'message' => "",
                'completePayment' => $completePayment,
                'totalPrepayment' => $totalOnlinePayment,
                'totalPrepaymentTxt' => number_format($totalOnlinePayment * $user->getUserCurrency()->getCurrCucChange(), 2) . " " . $user->getUserCurrency()->getCurrSymbol(),
                'totalAccommodationPayment' => $totalAccommodationPayment,
                'totalAccommodationPaymentTxt' => number_format($totalAccommodationPayment * $user->getUserCurrency()->getCurrCucChange(), 2) . " " . $user->getUserCurrency()->getCurrSymbol(),
                'totalServiceTaxPayment' => $totalServicesTax + 1.1 * $fixedFee,
                'totalServiceTaxPaymentTxt' => number_format(($totalServicesTax + 1.1 * $fixedFee) * $user->getUserCurrency()->getCurrCucChange(), 2) . " " . $user->getUserCurrency()->getCurrSymbol(),
                'fixedTax' => $fixedFee,
                'fixedTaxTxt' => number_format($fixedFee * $user->getUserCurrency()->getCurrCucChange(), 2) . " " . $user->getUserCurrency()->getCurrSymbol(),
                'totalPayment' => $totalPrepayment,
                'totalPaymentTxt' => number_format(($totalPrepayment) * $user->getUserCurrency()->getCurrCucChange(), 2) . " " . $user->getUserCurrency()->getCurrSymbol(),
                'totalTransferFeePayment' => $totalTransferFee,
                'totalTransferFeePaymentTxt' => number_format(($totalTransferFee) * $user->getUserCurrency()->getCurrCucChange(), 2) . " " . $user->getUserCurrency()->getCurrSymbol(),
                'totalAgencyCommission' => $totalAgencyCommission,
                'totalAgencyCommissionTxt' => number_format($totalAgencyCommission * $user->getUserCurrency()->getCurrCucChange(), 2) . " " . $user->getUserCurrency()->getCurrSymbol(),
                "totalTouristAgencyTax" => $totalTouristAgencyTax

            ]);
        }

    }

    public function payNowAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $currentTourOperator = $em->getRepository("PartnerBundle:paTourOperator")->findOneBy(array("tourOperator" => $user->getUserId()));
        $currentTravelAgency = $currentTourOperator->getTravelAgency();
        $agencyPackage = $currentTravelAgency->getAgencyPackages()[0];
        $completePayment = $agencyPackage->getPackage()->getCompletePayment();

        $roomsToPay = $request->get("roomsToPay");
        $roomsToPay = explode(",", $roomsToPay);
        $roomsToPay = array_unique($roomsToPay);
        $extraData = $request->get("extraData");
        $extraData = explode(",", $extraData);
        $extraData = array_unique($extraData);
        $cartPrepayment = $request->get("totalPrepaymentGeneralInput");
        $totalTouristAgencyTax = $request->get("totalTouristAgencyTax");
        //$completePayment = $request->get("completePayment");
        $paymentMethod = $request->get('payment_method');

        //Guardar hora de llegada y actualizar nombre del cliente
        foreach($extraData as $data)
        {
            $dataValues = explode("-", $data);
            $idReservation = $dataValues[0];
            $genResId = $dataValues[1];
            $arrivalHour = $request->get("arrivalHour_".$idReservation);
            $clientName = $request->get("clientName_".$idReservation);

            $generalReservation = $em->getRepository("mycpBundle:generalReservation")->find($genResId);
            $reservation = $em->getRepository("PartnerBundle:paReservation")->find($idReservation);

            if($arrivalHour != "") {
                $generalReservation->setGenResArrivalHour($arrivalHour);
                $em->persist($generalReservation);

                $timeExplode = explode(":", $arrivalHour);
                $time = new \DateTime();
                $time->setTime($timeExplode[0], $timeExplode[1], 0);

                $reservation->setArrivalHour($time);
                $em->persist($reservation);
            }

            if($clientName != "") {
                $client = $reservation->getClient();

                $client->setFullname($clientName);
                $em->persist($client);
            }

            $em->flush();
        }

        //Generate Booking
        $user = $this->getUser();
        $tourOperator = $em->getRepository("PartnerBundle:paTourOperator")->findOneBy(array("tourOperator" => $user->getUserId()));
        $travelAgency = $tourOperator->getTravelAgency();

        $currency = $user->getUserCurrency();

        $booking = new booking();
        $booking->setBookingCancelProtection(0);
        $booking->setBookingCurrency($currency);
        $prepayment = $cartPrepayment* $currency->getCurrCucChange();
        $booking->setBookingPrepay($prepayment);
        $booking->setBookingUserId($user->getUserId());
        $booking->setBookingUserDates($travelAgency->getName() . ', ' . $user->getUserEmail());
        $booking->setCompletePayment($completePayment);
        $booking->setTaxForService($totalTouristAgencyTax);
        $em->persist($booking);

        $total_pay_at_service = 0;

        foreach ($roomsToPay as $own_res) {
            $own = $em->getRepository('mycpBundle:ownershipReservation')->find($own_res);
            $own->setOwnResReservationBooking($booking);
            $em->persist($own);
            $total_pay_at_service += $own->getOwnResTotalInSite() * (1 - $generalReservation->getGenResOwnId()->getOwnCommissionPercent()/100);
        }

        $booking->setPayAtService($total_pay_at_service);
        $em->persist($booking);
        $em->flush();

        $bookingId = $booking->getBookingId();

        switch($paymentMethod){
            case "skrill": return $this->forward('PartnerBundle:Payment:skrillPayment', array('bookingId' => $bookingId));
            case "postfinance": return $this->forward('PartnerBundle:Payment:postFinancePayment', array('bookingId' => $bookingId, 'method' => "POSTFINANCE"));
//                    case "visa": return $this->forward('FrontEndBundle:Payment:postFinancePayment', array('bookingId' => $bookingId, 'method' => "VISA"));
//                    case "mastercard": return $this->forward('FrontEndBundle:Payment:postFinancePayment', array('bookingId' => $bookingId, 'method' => "MASTERCARD"));
            default: return $this->forward('PartnerBundle:Payment:skrillPayment', array('bookingId' => $bookingId));
        }
    }

    public function cartWithOpenReservationAction($idReservation, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $ids_gr = array($idReservation);

        $user = $this->getUser();
        $tourOperator = $em->getRepository("PartnerBundle:paTourOperator")->findOneBy(array("tourOperator" => $user->getUserId()));
        $travelAgency = $tourOperator->getTravelAgency();

        $cartItems = $em->getRepository("PartnerBundle:paReservation")->getCartItems($travelAgency, $ids_gr);

        $reservations = array();
        $reservationsIds = array();

        foreach($ids_gr as $idGenRes){
            $genRes = $em->getRepository("mycpBundle:generalReservation")->find($idGenRes);
            $details = $genRes->getTravelAgencyDetailReservations();
            $reservation = $details[0]->getReservation();

            if(!array_key_exists($reservation->getId(), $reservations)) {
                $reservations[$reservation->getId()] = array();
                $reservationsIds[] = $reservation->getId();
            }

            $reservations[$reservation->getId()][] = $idGenRes;
        }

        $details = array();

        foreach($reservationsIds as $reservationId)
        {
            $details[$reservationId] = $em->getRepository('PartnerBundle:paReservation')->getDetails($reservationId, $reservations[$reservationId]);
        }

        return $this->render('PartnerBundle:Cart:cart.html.twig', array(
            "items" => $cartItems,
            "details" => $details,
            "disablePaymentButton" => (count($ids_gr) > 0)
        ));
    }

}
