<?php

namespace MyCp\PartnerBundle\Controller;

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
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $tourOperator = $em->getRepository("PartnerBundle:paTourOperator")->findOneBy(array("tourOperator" => $user->getUserId()));
        $travelAgency = $tourOperator->getTravelAgency();

        $cartItems = $em->getRepository("PartnerBundle:paReservation")->getCartItems($travelAgency);

        return $this->render('PartnerBundle:Dashboard:cart.html.twig', array(
            "items" => $cartItems
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

        $list = $em->getRepository('PartnerBundle:paReservation')->getDetailsByIds($ownReservationIds);
        $payments = array();

        foreach($list as $item)
        {
            $touristFee = $em->getRepository("mycpBundle:serviceFee")->calculateTouristServiceFeeByGeneralReservation($item["gen_res_id"], $timer);
            $commission = $item["totalInSite"]*$item["commission"]/100;

            $reservations = $em->getRepository("mycpBundle:generalReservation")->getReservationCart($item["gen_res_id"], $ownReservationIds);
            $fixedFee = 0;

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

        $response = $this->renderView('PartnerBundle:Cart:selected_to_pay.html.twig', array(
            'items' => $list,
            'payments' => $payments
        ));

        return new JsonResponse([
            'success' => true,
            'html' => $response,
            'message' => ""

        ]);
    }

    public function payNowAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        /*$user = $this->getUser();
        $tourOperator = $em->getRepository("PartnerBundle:paTourOperator")->findOneBy(array("tourOperator" => $user->getUserId()));
        $travelAgency = $tourOperator->getTravelAgency();*/

        $roomsToPay = $request->get("roomsToPay");
        $extraData = $request->get("extraData");

        //Guardar hora de llegada y actualizar nombre del cliente
        foreach($extraData as $data)
        {
            $generalReservation = $em->getRepository("mycpBundle:generalReservation")->find($data["genResId"]);
            $reservation = $em->getRepository("PartnerBundle:paReservation")->find($data["idReservation"]);

            if($data["arrivalTime"] != "") {
                $generalReservation->setGenResArrivalHour($data["arrivalTime"]);
                $em->persist($generalReservation);

                $timeExplode = explode(":", $data["arrivalTime"]);
                $time = new \DateTime();
                $time->setTime($timeExplode[0], $timeExplode[1], 0);

                $reservation->setArrivalHour($time);
                $em->persist($reservation);
            }

            if($data["clientName"] != "") {
                $client = $reservation->getClient();

                $client->setFullname($data["clientName"]);
                $em->persist($client);
            }

            $em->flush();
        }



        return new JsonResponse([
            'success' => true,
            'url' => "",
            'message' => ""

        ]);
    }

}
