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
}
