<?php

namespace MyCp\FrontendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class mycasatripController extends Controller {

    public function homeAction() {
        $user = $this->get('security.context')->getToken()->getUser();
        $em = $this->getDoctrine()->getManager();
        $session_id = $em->getRepository('mycpBundle:user')->get_session_id($this);

        if ($user != null && $user != "anon." && $session_id != null && $session_id != "")
            $em->getRepository('mycpBundle:favorite')->set_to_user($user->getUserId(), $session_id);

        return $this->render('frontEndBundle:mycasatrip:home.html.twig');
    }

    public function reservations_pendingAction($order_by, Request $request) {
        $user = $this->get('security.context')->getToken()->getUser();
        $em = $this->getDoctrine()->getManager();

        // pendientes Mayores hoy - 30 días
        $date = \date('Y-m-j');
        $new_date = strtotime('-30 day', strtotime($date));
        $new_date = \date('Y-m-j', $new_date);
        $string_sql = "AND gre.gen_res_date > '$new_date'";
        $status_string = 'ownre.own_res_status = 0';

        if ($this->getRequest()->getMethod() == 'POST') {
            $order_by = $request->get('mct_change_order');
        }
        $string_sql.=$this->get_order_by_sql($order_by);

        $res_pending = $em->getRepository('mycpBundle:ownershipReservation')->find_by_user_and_status($user->getUserId(), $status_string, $string_sql);
        $array_photos=array();
        foreach($res_pending as $pend)
        {
            $photo=$em->getRepository('mycpBundle:ownership')->get_ownership_photo($pend['own_res_gen_res_id']['gen_res_own_id']['own_id']);
            array_push($array_photos,$photo);
        }
        return $this->render('frontEndBundle:mycasatrip:pending.html.twig', array(
                    'res_pending' => $res_pending,
                    'order_by' => $order_by,
                    'photos'=>$array_photos
        ));
    }

    public function reservations_availableAction($order_by, Request $request) {
        $user = $this->get('security.context')->getToken()->getUser();
        $em = $this->getDoctrine()->getManager();

        // disponibles Mayores que (hoy - 30) días
        $date = \date('Y-m-j');
        $new_date = strtotime('-60 hours', strtotime($date));
        $new_date = \date('Y-m-j', $new_date);
        $string_sql = "AND gre.gen_res_date > '$new_date'";
        $status_string = 'ownre.own_res_status =1';

        if ($this->getRequest()->getMethod() == 'POST') {
            $order_by = $request->get('mct_change_order');
        }

        $string_sql.=$this->get_order_by_sql($order_by);

        $res_available = $em->getRepository('mycpBundle:ownershipReservation')->find_by_user_and_status($user->getUserId(), $status_string, $string_sql);

        $service_time=$this->get('time');
        $nights=array();
        $array_photos=array();
        foreach($res_available as $res)
        {
            $array_dates=$service_time->dates_between($res['own_res_reservation_from_date']->getTimestamp(),$res['own_res_reservation_to_date']->getTimestamp());
            array_push($nights,count($array_dates)-1);

            $photo=$em->getRepository('mycpBundle:ownership')->get_ownership_photo($res['own_res_gen_res_id']['gen_res_own_id']['own_id']);
            array_push($array_photos,$photo);
        }

        return $this->render('frontEndBundle:mycasatrip:available.html.twig', array(
                    'res_available' => $res_available,
                    'order_by'=>$order_by,
                    'nights'=>$nights,
                    'photos'=>$array_photos
        ));
    }
    
    public function update_favorites_statistics_callbackAction()
    {
        $request = $this->getRequest();
        $session = $request->getSession();

        $favorite_type = $request->request->get("favorite_type");
        
        if ($favorite_type == "ownership") {
            $total = $session->get('user_fav_own_count');
        } else if ($favorite_type == "destination") {
            $total = $session->get('user_fav_dest_count');
            
        }

        return new Response($total, 200);
    }

    public function reservations_reserveAction($order_by, Request $request) {
       /* $user = $this->get('security.context')->getToken()->getUser();
        $em = $this->getDoctrine()->getManager();

        // reservados Mayores que (hoy - 30) días
        $date = \date('Y-m-j');
        $new_date = strtotime('-60 hours', strtotime($date));
        $new_date = \date('Y-m-j', $new_date);
        $string_sql = "AND gre.gen_res_date > '$new_date'";
        $status_string = 'ownre.own_res_status =2';

        if ($this->getRequest()->getMethod() == 'POST') {
            $order_by = $request->get('mct_change_order');
        }

        $string_sql.=$this->get_order_by_sql($order_by);

        $res_available = $em->getRepository('mycpBundle:ownershipReservation')->find_by_user_and_status($user->getUserId(), $status_string, $string_sql);

        $service_time=$this->get('time');
        $nights=array();
        $array_photos=array();
        foreach($res_available as $res)
        {
            $array_dates=$service_time->dates_between($res['own_res_reservation_from_date']->getTimestamp(),$res['own_res_reservation_to_date']->getTimestamp());
            array_push($nights,count($array_dates)-1);
            $photo=$em->getRepository('mycpBundle:ownership')->get_ownership_photo($res['own_res_gen_res_id']['gen_res_own_id']['own_id']);
            array_push($array_photos,$photo);
        }
        return $this->render('frontEndBundle:mycasatrip:reserve.html.twig', array(
            'res_available' => $res_available,
            'order_by'=>$order_by,
            'nights'=>$nights,
            'photos'=>$array_photos
        ));*/
    }

    public function history_reservations_reserveAction($order_by, Request $request) {

        $user = $this->get('security.context')->getToken()->getUser();
        $em = $this->getDoctrine()->getManager();

        // reservados menores que (hoy - 30) días
        $date = \date('Y-m-j');
        $new_date = strtotime('-30 day', strtotime($date));
        $new_date = \date('Y-m-j', $new_date);
        $string_sql = "AND gre.gen_res_date < '$new_date'";
        $status_string = 'ownre.own_res_status =2';

        if ($this->getRequest()->getMethod() == 'POST') {
            $order_by = $request->get('mct_change_order');
        }

        $string_sql.=$this->get_order_by_sql($order_by);

        $res_available = $em->getRepository('mycpBundle:ownershipReservation')->find_by_user_and_status($user->getUserId(), $status_string, $string_sql);

        $service_time=$this->get('time');
        $nights=array();
        $array_photos=array();
        foreach($res_available as $res)
        {
            $array_dates=$service_time->dates_between($res['own_res_reservation_from_date']->getTimestamp(),$res['own_res_reservation_to_date']->getTimestamp());
            array_push($nights,count($array_dates)-1);
            $photo=$em->getRepository('mycpBundle:ownership')->get_ownership_photo($res['own_res_gen_res_id']['gen_res_own_id']['own_id']);
            array_push($array_photos,$photo);
        }

        return $this->render('frontEndBundle:mycasatrip:history_reserve.html.twig', array(
            'res_available' => $res_available,
            'order_by'=>$order_by,
            'nights'=>$nights,
            'photos'=>$array_photos
        ));
    }

    public function reservations_paymentAction($order_by, Request $request) {
        $user = $this->get('security.context')->getToken()->getUser();
        $em = $this->getDoctrine()->getManager();

        $string_sql = "";
        $status_string = 'ownre.own_res_status =5';

        if ($this->getRequest()->getMethod() == 'POST') {
            $order_by = $request->get('mct_change_order');
        }

        $string_sql.=$this->get_order_by_sql($order_by);

        $res_payment = $em->getRepository('mycpBundle:ownershipReservation')->find_by_user_and_status($user->getUserId(), $status_string, $string_sql);

        $service_time=$this->get('time');
        $nights=array();
        $array_photos=array();
        foreach($res_payment as $res)
        {
            $array_dates=$service_time->dates_between($res['own_res_reservation_from_date']->getTimestamp(),$res['own_res_reservation_to_date']->getTimestamp());
            array_push($nights,count($array_dates)-1);
            $photo=$em->getRepository('mycpBundle:ownership')->get_ownership_photo($res['own_res_gen_res_id']['gen_res_own_id']['own_id']);
            array_push($array_photos,$photo);
        }

        return $this->render('frontEndBundle:mycasatrip:payment.html.twig', array(
            'res_payment' => $res_payment,
            'order_by'=>$order_by,
            'nights'=>$nights,
            'photos'=>$array_photos
        ));
    }

    function history_reservation_consultAction($order_by, Request $request) {
        $user = $this->get('security.context')->getToken()->getUser();
        $em = $this->getDoctrine()->getManager();

        // pendientes menores que (hoy - 30) días
        $date = \date('Y-m-j');
        $new_date = strtotime('-30 day', strtotime($date));
        $new_date = \date('Y-m-j', $new_date);
        $string_sql = "AND gre.gen_res_date < '$new_date'";
        $status_string = 'ownre.own_res_status =0';

        if ($this->getRequest()->getMethod() == 'POST') {
            $order_by = $request->get('mct_change_order');
        }

        $string_sql.=$this->get_order_by_sql($order_by);

        $res_consult = $em->getRepository('mycpBundle:ownershipReservation')->find_by_user_and_status($user->getUserId(), $status_string, $string_sql);
        $array_photos=array();
        foreach($res_consult as $cons)
        {
            $photo=$em->getRepository('mycpBundle:ownership')->get_ownership_photo($cons['own_res_gen_res_id']['gen_res_own_id']['own_id']);
            array_push($array_photos,$photo);
        }

        return $this->render('frontEndBundle:mycasatrip:history_consult.html.twig', array(
            'res_contult' => $res_consult,
            'order_by'=>$order_by,
            'photos'=>$array_photos
        ));
    }

    function get_menu_countAction($menu_selected) {
        $user = $this->get('security.context')->getToken()->getUser();
        $em = $this->getDoctrine()->getManager();
        $counts = $em->getRepository('mycpBundle:ownershipReservation')->find_count_for_menu($user->getUserId());
        return $this->render('frontEndBundle:mycasatrip:menu.html.twig', array('menu' => $menu_selected, 'counts' => $counts[0]));
    }

    function get_order_by_sql($order_by) {
        $order_by_string = '';
        switch ($order_by) {
            case 0:
                $order_by_string = ' ORDER BY gre.gen_res_date DESC';
                break;
            case 1:
                $order_by_string = ' ORDER BY gre.gen_res_id ASC';
                break;
            case 2:
                $order_by_string = ' ORDER BY gre.gen_res_total_in_site ASC';
                break;
        }
        return $order_by_string;
    }

    /**
     * Yanet 
     */
    function favoritesAction($favorite_type) {
        $em = $this->getDoctrine()->getManager();
        $user = $this->get('security.context')->getToken()->getUser();
        $request = $this->getRequest();
        $session = $request->getSession();
        $session->set('mycasatrip_favorite_type', $favorite_type);

        if ($favorite_type == "ownerships") {
            $favorite_ownerships_list= $em->getRepository('mycpBundle:favorite')->get_favorite_ownerships($user->getUserId());
            
            $paginator = $this->get('ideup.simple_paginator');
            $items_per_page = 15;
            $paginator->setItemsPerPage($items_per_page);
            $ownership_favorities = $paginator->paginate($favorite_ownerships_list)->getResult();
            $page = 1;
            if (isset($_GET['page']))
                $page = $_GET['page'];
        
            return $this->render('frontEndBundle:mycasatrip:favorites.html.twig', array(
                        'ownership_favorities' => $ownership_favorities,
                        'favorite_type' => $favorite_type
            ));
        } else {
            $locale = $this->get('translator')->getLocale();
            $favorite_destination_list = $em->getRepository('mycpBundle:favorite')->get_favorite_destinations($user->getUserId());
            
            $paginator = $this->get('ideup.simple_paginator');
            $items_per_page = 15;
            $paginator->setItemsPerPage($items_per_page);
            $destination_favorities = $paginator->paginate($favorite_destination_list)->getResult();
            $page = 1;
            if (isset($_GET['page']))
                $page = $_GET['page'];

            return $this->render('frontEndBundle:mycasatrip:favorites.html.twig', array(
                        'destination_favorities' => $destination_favorities,
                        'favorite_type' => $favorite_type
            ));
        }
    }
    
    function commentsAction($comment_type)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->get('security.context')->getToken()->getUser();
                
        $paginator = $this->get('ideup.simple_paginator');
        $items_per_page = 15;
        $paginator->setItemsPerPage($items_per_page);
        $comments = $paginator->paginate($em->getRepository('mycpBundle:comment')->get_user_comments($user->getUserId()))->getResult();
        $page = 1;
        if (isset($_GET['page']))
            $page = $_GET['page'];
        
        return $this->render('frontEndBundle:mycasatrip:comments.html.twig', array(
                        'comments' => $comments,
                        'comment_type' => $comment_type,
                        'items_per_page' => $items_per_page,
                        'total_items' => $paginator->getTotalItems(),
            ));
    }

}
