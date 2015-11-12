<?php

namespace MyCp\mycpBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class LodgingController extends Controller
{
    public function lodging_frontAction()
    {
        $user = $this->get('security.context')->getToken()->getUser();
        $user_id=$user->getUserId();
        $em=$this->getDoctrine()->getManager();
        $userCasa = $em->getRepository('mycpBundle:userCasa')->getByUser($user_id);
        if($user->getUserPhoto())
            $photo=$em->getRepository('mycpBundle:photo')->find($user->getUserPhoto()->getPhoId());
        else
            $photo=null;
        return $this->render('mycpBundle:lodging:welcome.html.twig', array('photo' => $photo, 'ownership' => $userCasa->getUserCasaOwnership()->getOwnId()));
    }

    public function getAccommodationNameAction()
    {
        $user = $this->get('security.context')->getToken()->getUser();
        $user_id=$user->getUserId();
        $em=$this->getDoctrine()->getManager();
        $userCasa = $em->getRepository('mycpBundle:userCasa')->getByUser($user_id);

        return $this->render('mycpBundle:utils:lodging_accommodation_name.html.twig', array(
            'ownership' => $userCasa->getUserCasaOwnership()
         ));
    }

    public function getRoomsMiniCalendarAction($ownership)
    {
        $em=$this->getDoctrine()->getManager();
        $rooms = $em->getRepository("mycpBundle:ownership")->getRoomsIdByOwnership($ownership);

        return $this->render('mycpBundle:utils:roomMiniCalendar.html.twig', array(
            'rooms' => $rooms
        ));
    }

    public function getNextReservationsAction($ownership, $maxResults){
        $em=$this->getDoctrine()->getManager();
        $list = $em->getRepository("mycpBundle:ownershipReservation")->getNextReservations($ownership, $maxResults);

        return $this->render('mycpBundle:reservation:nextReservationsLodging.html.twig', array(
            'list' => $list
        ));
    }

    public function getLastClientsAction($ownership, $maxResults){
        $em=$this->getDoctrine()->getManager();
        $list = $em->getRepository("mycpBundle:ownershipReservation")->getLastClients($ownership, $maxResults);

        return $this->render('mycpBundle:reservation:lastClientsLodging.html.twig', array(
            'list' => $list
        ));
    }

}
