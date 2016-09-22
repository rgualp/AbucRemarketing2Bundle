<?php

namespace MyCp\PartnerBundle\Controller;

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
}
