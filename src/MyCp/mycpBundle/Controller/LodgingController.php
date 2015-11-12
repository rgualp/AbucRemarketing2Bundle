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
        if($user->getUserPhoto())
            $photo=$em->getRepository('mycpBundle:photo')->find($user->getUserPhoto()->getPhoId());
        else
            $photo=null;
        return $this->render('mycpBundle:lodging:welcome.html.twig', array('photo' => $photo));
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

    public function getRoomsMiniCalendarAction()
    {
        $user = $this->get('security.context')->getToken()->getUser();
        $user_id=$user->getUserId();
        $em=$this->getDoctrine()->getManager();
        $userCasa = $em->getRepository('mycpBundle:userCasa')->getByUser($user_id);

        $rooms = $em->getRepository("mycpBundle:ownership")->getRoomsIdByOwnership($userCasa->getUserCasaOwnership()->getOwnId());

        return $this->render('mycpBundle:utils:roomMiniCalendar.html.twig', array(
            'rooms' => $rooms
        ));
    }

}
