<?php

namespace MyCp\FrontendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class mycasatripController extends Controller {

    public function homeAction()
    {
        return $this->render('frontEndBundle:mycasatrip:home.html.twig');
    }

    public function reservations_pendingAction()
    {
        $user = $this->get('security.context')->getToken()->getUser();
        $em=$this->getDoctrine()->getManager();
        $res_pending = $em->getRepository('mycpBundle:generalReservation')->find_pending_by_user($user->getUserId(),0);
        $own_reservations=array();
        $own_rooms=array();
        foreach($res_pending as $res)
        {
            $own_re=$em->getRepository('mycpBundle:ownershipReservation')->findBy(array('own_res_gen_res_id'=>$res[0]['gen_res_id']));
            array_push($own_reservations,$own_re);
        }


        foreach($own_reservations as $own_re)
        {
            $temp_array=array();
            foreach($own_re as $own_re_items)
            {
                $own_room=$em->getRepository('mycpBundle:room')->find($own_re_items->getOwnResSelectedRoomId());
                array_push($temp_array,$own_room);
            }
            array_push($own_rooms,$temp_array);
        }
        //var_dump($own_rooms);
        //var_dump($own_reservations);
        //exit();
        return $this->render('frontEndBundle:mycasatrip:pending.html.twig',array(
            'res_pending'=>$res_pending,
            'own_reservations'=>$own_reservations,
            'own_rooms'=>$own_rooms
        ));
    }

    public function reservations_availableAction()
    {
        $user = $this->get('security.context')->getToken()->getUser();
        $em=$this->getDoctrine()->getManager();
        $res_available = $em->getRepository('mycpBundle:generalReservation')->find_pending_by_user($user->getUserId(),1);
        $own_reservations=array();
        $own_rooms=array();
        foreach($res_available as $res)
        {
            $own_re=$em->getRepository('mycpBundle:ownershipReservation')->findBy(array('own_res_gen_res_id'=>$res[0]['gen_res_id']));
            array_push($own_reservations,$own_re);
        }


        foreach($own_reservations as $own_re)
        {
            $temp_array=array();
            foreach($own_re as $own_re_items)
            {
                $own_room=$em->getRepository('mycpBundle:room')->find($own_re_items->getOwnResSelectedRoomId());
                array_push($temp_array,$own_room);
            }
            array_push($own_rooms,$temp_array);
        }

        return $this->render('frontEndBundle:mycasatrip:available.html.twig',array(
            'res_available'=>$res_available,
            'own_reservations'=>$own_reservations,
            'own_rooms'=>$own_rooms
        ));
    }

    function reservation_reviewAction($id_reservation)
    {
        $user = $this->get('security.context')->getToken()->getUser();
        $em=$this->getDoctrine()->getManager();
        $gen_res = $em->getRepository('mycpBundle:generalReservation')->get_reservation_available_by_user($id_reservation,$user->getUserId());
        var_dump($gen_res); exit();
    }

    

}
