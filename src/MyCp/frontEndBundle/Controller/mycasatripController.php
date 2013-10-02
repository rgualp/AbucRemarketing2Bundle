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
        return $this->render('frontEndBundle:mycasatrip:pending.html.twig', array(
                    'res_pending' => $res_pending,
                    'order_by' => $order_by
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
        foreach($res_available as $res)
        {
            $array_dates=$service_time->dates_between($res[0]['own_res_reservation_from_date']->getTimestamp(),$res[0]['own_res_reservation_to_date']->getTimestamp());
            array_push($nights,count($array_dates)-1);
        }

        return $this->render('frontEndBundle:mycasatrip:available.html.twig', array(
                    'res_available' => $res_available,
                    'order_by'=>$order_by,
                    'nights'=>$nights
        ));
    }

    public function reservations_reserveAction($order_by, Request $request) {
        $user = $this->get('security.context')->getToken()->getUser();
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
        foreach($res_available as $res)
        {
            $array_dates=$service_time->dates_between($res[0]['own_res_reservation_from_date']->getTimestamp(),$res[0]['own_res_reservation_to_date']->getTimestamp());
            array_push($nights,count($array_dates)-1);
        }
        return $this->render('frontEndBundle:mycasatrip:reserve.html.twig', array(
            'res_available' => $res_available,
            'order_by'=>$order_by,
            'nights'=>$nights
        ));
    }

    public function history_reservations_reserveAction($order_by, Request $request) {
        $user = $this->get('security.context')->getToken()->getUser();
        $em = $this->getDoctrine()->getManager();

        // reservados menores que (hoy - 30) días
        $date = \date('Y-m-j');
        $new_date = strtotime('-30 day', strtotime($date));
        $new_date = \date('Y-m-j', $new_date);
        $string_sql = "AND gre.gen_res_date < '$new_date'";
        $status_string = 'ownre.own_res_status =3';

        if ($this->getRequest()->getMethod() == 'POST') {
            $order_by = $request->get('mct_change_order');
        }

        $string_sql.=$this->get_order_by_sql($order_by);

        $res_available = $em->getRepository('mycpBundle:ownershipReservation')->find_by_user_and_status($user->getUserId(), $status_string, $string_sql);

        $service_time=$this->get('time');
        $nights=array();
        foreach($res_available as $res)
        {
            $array_dates=$service_time->dates_between($res[0]['own_res_reservation_from_date']->getTimestamp(),$res[0]['own_res_reservation_to_date']->getTimestamp());
            array_push($nights,count($array_dates)-1);
        }

        return $this->render('frontEndBundle:mycasatrip:history_reserve.html.twig', array(
            'res_available' => $res_available,
            'order_by'=>$order_by,
            'nights'=>$nights
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

        $res_available = $em->getRepository('mycpBundle:ownershipReservation')->find_by_user_and_status($user->getUserId(), $status_string, $string_sql);

        $service_time=$this->get('time');
        $nights=array();
        foreach($res_available as $res)
        {
            $array_dates=$service_time->dates_between($res[0]['own_res_reservation_from_date']->getTimestamp(),$res[0]['own_res_reservation_to_date']->getTimestamp());
            array_push($nights,count($array_dates)-1);
        }

        return $this->render('frontEndBundle:mycasatrip:payment.html.twig', array(
            'res_available' => $res_available,
            'order_by'=>$order_by,
            'nights'=>$nights
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

        return $this->render('frontEndBundle:mycasatrip:history_consult.html.twig', array(
            'res_contult' => $res_consult,
            'order_by'=>$order_by
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
            $favorite_own_ids = $em->getRepository('mycpBundle:favorite')->get_element_id_list(true, $user->getUserId(), null);

            $ownership_favorities = $em->getRepository('mycpBundle:ownership')->getListByIds($favorite_own_ids);
            $ownership_favorities_photos = $em->getRepository('mycpBundle:ownership')->get_photos_array($ownership_favorities);
            $ownership_favorities_rooms = $em->getRepository('mycpBundle:ownership')->get_rooms_array($ownership_favorities);

            $ownership_favorities_is_in = array();

            foreach ($ownership_favorities as $favorite) {
                $ownership_favorities_is_in[$favorite->getOwnId()] = true;
            }

            $counts_ownership = $em->getRepository('mycpBundle:ownership')->get_counts_for_search($ownership_favorities);

            return $this->render('frontEndBundle:mycasatrip:favorites.html.twig', array(
                        'ownership_favorities' => $ownership_favorities,
                        'ownership_favorities_photos' => $ownership_favorities_photos,
                        'ownership_favorities_rooms' => $ownership_favorities_rooms,
                        'ownership_favorities_is_in' => $ownership_favorities_is_in,
                        'favorite_type' => $favorite_type,
                        'counts_ownership' => $counts_ownership
            ));
        } else {
            $locale = $this->get('translator')->getLocale();
            $favorite_destination_ids = $em->getRepository('mycpBundle:favorite')->get_element_id_list(false, $user->getUserId(), null);

            $destination_favorities = $em->getRepository('mycpBundle:destination')->get_list_by_ids($favorite_destination_ids);
            $destination_favorities_photos = $em->getRepository('mycpBundle:destination')->get_destination_photos($destination_favorities);
            $destination_favorities_localization = $em->getRepository('mycpBundle:destination')->get_destination_location($destination_favorities);
            $destination_favorities_statistics = $em->getRepository('mycpBundle:destination')->get_destination_owns_statistics($destination_favorities);
            $destination_favorities_description = $em->getRepository('mycpBundle:destination')->get_destination_description($destination_favorities, $locale);

            $destination_favorities_is_in = array();

            foreach ($destination_favorities as $favorite) {
                $destination_favorities_is_in[$favorite->getDesId()] = true;
            }

            return $this->render('frontEndBundle:mycasatrip:favorites.html.twig', array(
                        'destination_favorities_is_in' => $destination_favorities_is_in,
                        'destination_favorities' => $destination_favorities,
                        'destination_favorities_photos' => $destination_favorities_photos,
                        'destination_favorities_localization' => $destination_favorities_localization,
                        'destination_favorities_statistics' => $destination_favorities_statistics,
                        'destination_favorities_description' => $destination_favorities_description,
                        'favorite_type' => $favorite_type
            ));
        }
    }

}
