<?php

namespace MyCp\FrontendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class mycasatripController extends Controller {

    public function homeAction()
    {
        return $this->render('frontEndBundle:mycasatrip:home.html.twig');
    }

    public function reservations_pendingAction($order_by,Request $request)
    {
        $user = $this->get('security.context')->getToken()->getUser();
        $em=$this->getDoctrine()->getManager();

        // pendientes Mayores hoy - 30 días
        $date = \date('Y-m-j');
        $new_date = strtotime ( '-30 day' , strtotime ( $date ) ) ;
        $new_date = \date ( 'Y-m-j' , $new_date );
        $string_sql="AND gre.gen_res_date > '$new_date'";
        $status_string='gre.gen_res_status =0';
        $order_by_string='';

        if($this->getRequest()->getMethod()=='POST')
        {
            $order_by=$request->get('mct_change_order');
        }

        $string_sql.=$this->get_order_by_sql($order_by);

        $res_pending = $em->getRepository('mycpBundle:generalReservation')->find_by_user_and_status($user->getUserId(),$status_string, $string_sql);
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

        //var_dump(new \dateTime()); exit();

        return $this->render('frontEndBundle:mycasatrip:pending.html.twig',array(
            'res_pending'=>$res_pending,
            'own_reservations'=>$own_reservations,
            'own_rooms'=>$own_rooms,
            'order_by'=>$order_by
        ));
    }

    public function reservations_availableAction()
    {
        $user = $this->get('security.context')->getToken()->getUser();
        $em=$this->getDoctrine()->getManager();

        // disponibles Mayores que (hoy - 30) días
        $date = \date('Y-m-j');
        $new_date = strtotime ( '-60 hours' , strtotime ( $date ) ) ;
        $new_date = \date ( 'Y-m-j' , $new_date );
        $string_sql="AND gre.gen_res_date > '$new_date'";

        $status_string='gre.gen_res_status =1';
        $res_available = $em->getRepository('mycpBundle:generalReservation')->find_by_user_and_status($user->getUserId(),$status_string,$string_sql);
        //var_dump($res_available); exit();
        $own_reservations=array();
        $own_rooms=array();
        $own_res_count_nights=array();
        $service_time= $this->get('Time');
        foreach($res_available as $res)
        {
            $own_re=$em->getRepository('mycpBundle:ownershipReservation')->findBy(array('own_res_gen_res_id'=>$res[0]['gen_res_id']));
            array_push($own_reservations,$own_re);
            $start_timestamp=$res[0]['gen_res_from_date']->getTimestamp();
            $end_timestamp=$res[0]['gen_res_to_date']->getTimestamp();
            $array_dates=$service_time->dates_between($start_timestamp,$end_timestamp);
            array_push($own_res_count_nights,count($array_dates)-1);
        }
        //var_dump($own_res_count_nights);
        //exit();

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
            'count_nights'=>$own_res_count_nights,
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
        return $this->render('frontEndBundle:mycasatrip:reservation.html.twig');


    }

    function history_reservation_consultAction()
    {
        $user = $this->get('security.context')->getToken()->getUser();
        $em=$this->getDoctrine()->getManager();

        // disponibles Mayores que (hoy - 30) días
        $date = \date('Y-m-j');
        $new_date = strtotime ( '-30 day' , strtotime ( $date ) ) ;
        $new_date = \date ( 'Y-m-j' , $new_date );
        //var_dump($new_date); exit();
        $string_sql="AND gre.gen_res_date < '$new_date'";

        $status_string="gre.gen_res_status = 0";
        $reservations = $em->getRepository('mycpBundle:generalReservation')->find_by_user_and_status($user->getUserId(),$status_string,$string_sql);

        $own_reservations=array();
        $own_rooms=array();
        $own_res_count_nights=array();
        $service_time= $this->get('Time');
        foreach($reservations as $res)
        {
            $own_re=$em->getRepository('mycpBundle:ownershipReservation')->findBy(array('own_res_gen_res_id'=>$res[0]['gen_res_id']));
            array_push($own_reservations,$own_re);
            $start_timestamp=$res[0]['gen_res_from_date']->getTimestamp();
            $end_timestamp=$res[0]['gen_res_to_date']->getTimestamp();
            $array_dates=$service_time->dates_between($start_timestamp,$end_timestamp);
            array_push($own_res_count_nights,count($array_dates)-1);
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

        return $this->render('frontEndBundle:mycasatrip:history_consult.html.twig',array(
            'count_nights'=>$own_res_count_nights,
            'reservations'=>$reservations,
            'own_reservations'=>$own_reservations,
            'own_rooms'=>$own_rooms
        ));
    }

    function get_menu_countAction($menu_selected)
    {
        $user = $this->get('security.context')->getToken()->getUser();
        $em=$this->getDoctrine()->getManager();
        $counts = $em->getRepository('mycpBundle:generalReservation')->find_count_for_menu($user->getUserId());
        //var_dump($counts); exit();
        return $this->render('frontEndBundle:mycasatrip:menu.html.twig', array('menu'=>$menu_selected,'counts'=>$counts[0]));
    }

    function get_order_by_sql($order_by)
    {
        $order_by_string='';
        switch($order_by)
        {
            case 0:
                $order_by_string=' ORDER BY gre.gen_res_date DESC';
                break;
            case 1:
                $order_by_string=' ORDER BY gre.gen_res_id ASC';
                break;
            case 2:
                $order_by_string=' ORDER BY gre.gen_res_total_price_in_site ASC';
                break;
        }
        return $order_by_string;
    }

    

}
