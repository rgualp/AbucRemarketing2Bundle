<?php

namespace MyCp\FrontEndBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use MyCp\mycpBundle\Entity\ownershipReservation;

class MycasatripController extends Controller {

    public function homeAction() {
        /* $user = ﻿$this->getUser();
          $em = $this->getDoctrine()->getManager();
          $session_id = $em->getRepository('mycpBundle:user')->getSessionId($this);

          if ($user != null && $user != "anon." && $session_id != null && $session_id != "")
          $em->getRepository('mycpBundle:favorite')->set_to_user($user->getUserId(), $session_id); */

        return $this->redirect($this->generateUrl('frontend_mycasatrip_pending'));
    }

    public function pendingAction($order_by, Request $request) {
        $user = $this->getUser();
        $em = $this->getDoctrine()->getManager();

        // pendientes Mayores hoy - 30 días
        $date = \date('Y-m-j');
        $new_date = strtotime('-30 day', strtotime($date));
        $new_date = \date('Y-m-j', $new_date);
        $string_sql = "AND gre.gen_res_date > '$new_date'";
        $status_string = 'ownre.own_res_status = ' . ownershipReservation::STATUS_PENDING;

        if ($this->getRequest()->getMethod() == 'POST') {
            $order_by = $request->get('mct_change_order');
        }
        $string_sql.=$this->getOrderBy($order_by);

        $paginator = $this->get('ideup.simple_paginator');
        $items_per_page = 15;
        $paginator->setItemsPerPage($items_per_page);
        $list = $em->getRepository('mycpBundle:ownershipReservation')->findByUserAndStatus($user->getUserId(), $status_string, $string_sql);
        $res_pending = $paginator->paginate($list)->getResult();
        $page = 1;
        if (isset($_GET['page']))
            $page = $_GET['page'];



        $mobileDetector = $this->get('mobile_detect.mobile_detector');


        if ($mobileDetector->isMobile()){
            $array_photos = array();
            foreach ($list as $pend) {
                $photo = $em->getRepository('mycpBundle:ownership')->getOwnershipPhoto($pend['own_res_gen_res_id']['gen_res_own_id']['own_id']);
                array_push($array_photos, $photo);
            }
            return $this->render('MyCpMobileFrontendBundle:mycasatrip:pending.html.twig', array(
                'res_pending' => $list,
                'total_items' => $paginator->getTotalItems(),
                'photos' => $array_photos,

            ));
        }else{
            $array_photos = array();
            foreach ($res_pending as $pend) {
                $photo = $em->getRepository('mycpBundle:ownership')->getOwnershipPhoto($pend['own_res_gen_res_id']['gen_res_own_id']['own_id']);
                array_push($array_photos, $photo);
            }
            return $this->render('FrontEndBundle:mycasatrip:pending.html.twig', array(
                'res_pending' => $res_pending,
                'order_by' => $order_by,
                'photos' => $array_photos,
                'items_per_page' => $items_per_page,
                'total_items' => $paginator->getTotalItems(),
                'current_page' => $page
            ));
        }

    }

    public function availableAction($order_by, Request $request,$flag = null) {
        $user = $this->getUser();
        $em = $this->getDoctrine()->getManager();

        // disponibles Mayores que (hoy - 30) días
        $date = \date('Y-m-j');
        //$new_date = strtotime('-48 hours', strtotime($date));
        //$new_date = \date('Y-m-j', $new_date);
        //$string_sql = "AND gre.gen_res_status_date > '$new_date'";
        $string_sql = "AND gre.gen_res_from_date >= '$date'";
        $status_string = 'ownre.own_res_status =' . ownershipReservation::STATUS_AVAILABLE;

        if ($this->getRequest()->getMethod() == 'POST') {
            $order_by = $request->get('mct_change_order');
        }

        $string_sql.=$this->getOrderBy($order_by);

        $paginator = $this->get('ideup.simple_paginator');
        if($flag=='1')
            $items_per_page = 5;
        else
            $items_per_page = 15;
        $paginator->setItemsPerPage($items_per_page);
        $list = ($user!='')?$em->getRepository('mycpBundle:ownershipReservation')->findByUserAndStatus($user->getUserId(), $status_string, $string_sql):array();
        $res_available = $paginator->paginate($list)->getResult();
        $page = 1;
        if (isset($_GET['page']))
            $page = $_GET['page'];

        $service_time = $this->get('time');
        $nights = array();
        $array_photos = array();
        foreach ($res_available as $res) {
            $totalNights = $service_time->nights($res['own_res_reservation_from_date']->getTimestamp(), $res['own_res_reservation_to_date']->getTimestamp());
            array_push($nights, $totalNights);

            $photo = $em->getRepository('mycpBundle:ownership')->getOwnershipPhoto($res['own_res_gen_res_id']['gen_res_own_id']['own_id']);
            array_push($array_photos, $photo);
        }

        $mobileDetector = $this->get('mobile_detect.mobile_detector');


        if ($mobileDetector->isMobile()){
            if($flag=='1'){
                return $this->render('MyCpMobileFrontendBundle:cart:navBarCesta.html.twig', array(
                    'res_available' => $res_available,
                    'order_by' => $order_by,
                    'nights' => $nights,
                    'photos' => $array_photos,
                    'items_per_page' => $items_per_page,
                    'total_items' => $paginator->getTotalItems(),
                    'current_page' => $page
                ));
            }
            else{
                return $this->render('MyCpMobileFrontendBundle:mycasatrip:available.html.twig', array(
                    'res_available' => $res_available,
                    'order_by' => $order_by,
                    'nights' => $nights,
                    'photos' => $array_photos,
                    'items_per_page' => $items_per_page,
                    'total_items' => $paginator->getTotalItems(),
                    'current_page' => $page
                ));
            }
        }else{
            if($flag=='1'){
                return $this->render('FrontEndBundle:cart:navBarCesta.html.twig', array(
                    'res_available' => $res_available,
                    'order_by' => $order_by,
                    'nights' => $nights,
                    'photos' => $array_photos,
                    'items_per_page' => $items_per_page,
                    'total_items' => $paginator->getTotalItems(),
                    'current_page' => $page
                ));
            }
            else{
                return $this->render('FrontEndBundle:mycasatrip:available.html.twig', array(
                    'res_available' => $res_available,
                    'order_by' => $order_by,
                    'nights' => $nights,
                    'photos' => $array_photos,
                    'items_per_page' => $items_per_page,
                    'total_items' => $paginator->getTotalItems(),
                    'current_page' => $page
                ));
            }
        }



    }

    public function updateFavoritesStatisticsCallbackAction() {
        $request = $this->getRequest();
        $session = $request->getSession();

        $favorite_type = $request->request->get("favorite_type");

        if ($favorite_type == "ownership") {
            $total = $session->get('user_fav_own_count');
        } else if ($favorite_type == "destination") {
            $total = $session->get('user_fav_dest_count');
        }
        else
            $total = 0;

        return new Response($total, 200);
    }

    public function reserveAction($order_by, Request $request) {
        /* $user = $this->getUser();
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

          $string_sql.=$this->getOrderBy($order_by);

          $res_available = $em->getRepository('mycpBundle:ownershipReservation')->findByUserAndStatus($user->getUserId(), $status_string, $string_sql);

          $service_time=$this->get('time');
          $nights=array();
          $array_photos=array();
          foreach($res_available as $res)
          {
          $totalNights=$service_time->nights($res['own_res_reservation_from_date']->getTimestamp(),$res['own_res_reservation_to_date']->getTimestamp());
          array_push($nights,$totalNights);
          $photo=$em->getRepository('mycpBundle:ownership')->getOwnershipPhoto($res['own_res_gen_res_id']['gen_res_own_id']['own_id']);
          array_push($array_photos,$photo);
          }
          return $this->render('FrontEndBundle:mycasatrip:reserve.html.twig', array(
          'res_available' => $res_available,
          'order_by'=>$order_by,
          'nights'=>$nights,
          'photos'=>$array_photos
          )); */
    }

    public function historyReserveAction($order_by, Request $request) {

        $user = $this->getUser();
        $em = $this->getDoctrine()->getManager();

        // reservados menores que (hoy - 30) días
        $date = \date('Y-m-j');
        $new_date = strtotime('-30 day', strtotime($date));
        $new_date = \date('Y-m-j', $new_date);
        $string_sql = "AND gre.gen_res_date < '$new_date'";
        $status_string = 'ownre.own_res_status =' . ownershipReservation::STATUS_RESERVED;

        if ($this->getRequest()->getMethod() == 'POST') {
            $order_by = $request->get('mct_change_order');
        }

        $string_sql.=$this->getOrderBy($order_by);

        $paginator = $this->get('ideup.simple_paginator');
        $items_per_page = 15;
        $paginator->setItemsPerPage($items_per_page);
        $list = $em->getRepository('mycpBundle:ownershipReservation')->findByUserAndStatus($user->getUserId(), $status_string, $string_sql);
        $res_available = $paginator->paginate($list)->getResult();
        $page = 1;
        if (isset($_GET['page']))
            $page = $_GET['page'];

        $service_time = $this->get('time');
        $nights = array();
        $array_photos = array();
        foreach ($res_available as $res) {
            $totalNights = $service_time->nights($res['own_res_reservation_from_date']->getTimestamp(), $res['own_res_reservation_to_date']->getTimestamp());
            array_push($nights, $totalNights);
            $photo = $em->getRepository('mycpBundle:ownership')->getOwnershipPhoto($res['own_res_gen_res_id']['gen_res_own_id']['own_id']);
            array_push($array_photos, $photo);
        }



        $mobileDetector = $this->get('mobile_detect.mobile_detector');


        if ($mobileDetector->isMobile()){
            return $this->render('MyCpMobileFrontendBundle:mycasatrip:history_reserve.html.twig', array(
                'res_available' => $res_available,
                'order_by' => $order_by,
                'nights' => $nights,
                'photos' => $array_photos,
                'items_per_page' => $items_per_page,
                'total_items' => $paginator->getTotalItems(),
                'current_page' => $page
            ));
        }else{
            return $this->render('FrontEndBundle:mycasatrip:history_reserve.html.twig', array(
                'res_available' => $res_available,
                'order_by' => $order_by,
                'nights' => $nights,
                'photos' => $array_photos,
                'items_per_page' => $items_per_page,
                'total_items' => $paginator->getTotalItems(),
                'current_page' => $page
            ));
        }
    }

    public function paymentsAction($order_by, Request $request) {
        $user = $this->getUser();
        $em = $this->getDoctrine()->getManager();

        $string_sql = "";
        $status_string = 'ownre.own_res_status =' . ownershipReservation::STATUS_RESERVED;

        if ($this->getRequest()->getMethod() == 'POST') {
            $order_by = $request->get('mct_change_order');
        }

        $string_sql.=$this->getOrderBy($order_by);

        $paginator = $this->get('ideup.simple_paginator');
        $items_per_page = 15;
        $paginator->setItemsPerPage($items_per_page);
        $list = $em->getRepository('mycpBundle:ownershipReservation')->findByUserAndStatus($user->getUserId(), $status_string, $string_sql);
        $res_payment = $paginator->paginate($list)->getResult();
        $page = 1;
        if (isset($_GET['page']))
            $page = $_GET['page'];

        $service_time = $this->get('time');
        $nights = array();
        $array_photos = array();
        foreach ($res_payment as $res) {
            $totalNights = $service_time->nights($res['own_res_reservation_from_date']->getTimestamp(), $res['own_res_reservation_to_date']->getTimestamp());
            array_push($nights, $totalNights);
            $photo = $em->getRepository('mycpBundle:ownership')->getOwnershipPhoto($res['own_res_gen_res_id']['gen_res_own_id']['own_id']);
            array_push($array_photos, $photo);
        }

        $mobileDetector = $this->get('mobile_detect.mobile_detector');


        if ($mobileDetector->isMobile()){
            return $this->render('MyCpMobileFrontendBundle:mycasatrip:payment.html.twig', array(
                'res_payment' => $res_payment,
                'order_by' => $order_by,
                'nights' => $nights,
                'photos' => $array_photos,
                'items_per_page' => $items_per_page,
                'total_items' => $paginator->getTotalItems(),
                'current_page' => $page
            ));
        }else{
            return $this->render('FrontEndBundle:mycasatrip:payment.html.twig', array(
                'res_payment' => $res_payment,
                'order_by' => $order_by,
                'nights' => $nights,
                'photos' => $array_photos,
                'items_per_page' => $items_per_page,
                'total_items' => $paginator->getTotalItems(),
                'current_page' => $page
            ));
        }
    }

    function historyConsultAction($order_by, Request $request) {
        $user = $this->getUser();
        $em = $this->getDoctrine()->getManager();

        // pendientes menores que (hoy - 30) días
        $date = \date('Y-m-j');
        $new_date = strtotime('-30 day', strtotime($date));
        $new_date = \date('Y-m-j', $new_date);
        $string_sql = "AND gre.gen_res_date < '$new_date'";
        $status_string = 'ownre.own_res_status <>' . ownershipReservation::STATUS_RESERVED;

        if ($this->getRequest()->getMethod() == 'POST') {
            $order_by = $request->get('mct_change_order');
        }

        $string_sql.=$this->getOrderBy($order_by);

        $paginator = $this->get('ideup.simple_paginator');
        $items_per_page = 15;
        $paginator->setItemsPerPage($items_per_page);
        $list = $em->getRepository('mycpBundle:ownershipReservation')->findByUserAndStatus($user->getUserId(), $status_string, $string_sql);
        $res_consult = $paginator->paginate($list)->getResult();
        $page = 1;
        if (isset($_GET['page']))
            $page = $_GET['page'];

        $array_photos = array();

        foreach ($res_consult as $cons) {
            $photo = $em->getRepository('mycpBundle:ownership')->getOwnershipPhoto($cons['own_res_gen_res_id']['gen_res_own_id']['own_id']);
            array_push($array_photos, $photo);
        }

        $mobileDetector = $this->get('mobile_detect.mobile_detector');


        if ($mobileDetector->isMobile()){
            return $this->render('MyCpMobileFrontendBundle:mycasatrip:history_consult.html.twig', array(
                'res_contult' => $res_consult,
                'order_by' => $order_by,
                'photos' => $array_photos,
                'items_per_page' => $items_per_page,
                'total_items' => $paginator->getTotalItems(),
                'current_page' => $page
            ));
        }else{
            return $this->render('FrontEndBundle:mycasatrip:history_consult.html.twig', array(
                'res_contult' => $res_consult,
                'order_by' => $order_by,
                'photos' => $array_photos,
                'items_per_page' => $items_per_page,
                'total_items' => $paginator->getTotalItems(),
                'current_page' => $page
            ));
        }

    }

    function getMenuCountAction($menu_selected) {
        $user = $this->getUser();
        $em = $this->getDoctrine()->getManager();
        $counts = $em->getRepository('mycpBundle:ownershipReservation')->countForMenu($user->getUserId());
        //var_dump($counts); exit();
        $mobileDetector = $this->get('mobile_detect.mobile_detector');


        if ($mobileDetector->isMobile()){
            return $this->render('MyCpMobileFrontendBundle:mycasatrip:menu.html.twig', array('menu' => $menu_selected, 'counts' => $counts[0]));
        }else{
            return $this->render('FrontEndBundle:mycasatrip:menu.html.twig', array('menu' => $menu_selected, 'counts' => $counts[0]));
        }
    }

    function getOrderBy($order_by) {
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

    function favoritesDestinationsAction() {
        return $this->favoritesAction('destinations');
    }

    function favoritesAccomodationsAction() {
        return $this->favoritesAction('ownerships');
    }

    function favoritesAction($favorite_type) {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $request = $this->getRequest();
        $session = $request->getSession();
        $session->set('mycasatrip_favorite_type', $favorite_type);

        if ($favorite_type == "ownerships" || $favorite_type == "ownershipfav") {
            $favorite_ownerships_list = $em->getRepository('mycpBundle:favorite')->getFavoriteAccommodations($user->getUserId());

            $paginator = $this->get('ideup.simple_paginator');
            $items_per_page = 15;
            $paginator->setItemsPerPage($items_per_page);
            $ownership_favorities = $paginator->paginate($favorite_ownerships_list)->getResult();
            $page = 1;
            if (isset($_GET['page']))
                $page = $_GET['page'];

            $mobileDetector = $this->get('mobile_detect.mobile_detector');


            if ($mobileDetector->isMobile()){
                return $this->render('MyCpMobileFrontendBundle:mycasatrip:favorites.html.twig', array(
                    'ownership_favorities' => $ownership_favorities,
                    'favorite_type' => $favorite_type,
                    'items_per_page' => $items_per_page,
                    'total_items' => $paginator->getTotalItems(),
                    'current_page' => $page
                ));
            }else{
                return $this->render('FrontEndBundle:mycasatrip:favorites.html.twig', array(
                    'ownership_favorities' => $ownership_favorities,
                    'favorite_type' => $favorite_type,
                    'items_per_page' => $items_per_page,
                    'total_items' => $paginator->getTotalItems(),
                    'current_page' => $page
                ));
            }
        } else {
            $locale = $this->get('translator')->getLocale();
            $favorite_destination_list = $em->getRepository('mycpBundle:favorite')->getFavoriteDestinations($user->getUserId());

            $paginator = $this->get('ideup.simple_paginator');
            $items_per_page = 15;
            $paginator->setItemsPerPage($items_per_page);
            $destination_favorities = $paginator->paginate($favorite_destination_list)->getResult();
            $page = 1;
            if (isset($_GET['page']))
                $page = $_GET['page'];



            $mobileDetector = $this->get('mobile_detect.mobile_detector');

            if ($mobileDetector->isMobile()){
                return $this->render('MyCpMobileFrontendBundle:mycasatrip:favorites.html.twig', array(
                    'destination_favorities' => $destination_favorities,
                    'favorite_type' => $favorite_type,
                    'items_per_page' => $items_per_page,
                    'total_items' => $paginator->getTotalItems(),
                    'current_page' => $page
                ));
            }else{
                return $this->render('FrontEndBundle:mycasatrip:favorites.html.twig', array(
                    'destination_favorities' => $destination_favorities,
                    'favorite_type' => $favorite_type,
                    'items_per_page' => $items_per_page,
                    'total_items' => $paginator->getTotalItems(),
                    'current_page' => $page
                ));
            }
        }
    }

    function commentsAction($comment_type) {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();

        $paginator = $this->get('ideup.simple_paginator');
        $items_per_page = 15;
        $paginator->setItemsPerPage($items_per_page);
        $comments = $paginator->paginate($em->getRepository('mycpBundle:comment')->getByUser($user->getUserId()))->getResult();
        $page = 1;
        if (isset($_GET['page']))
            $page = $_GET['page'];

        $mobileDetector = $this->get('mobile_detect.mobile_detector');

        if ($mobileDetector->isMobile()){
            return $this->render('MyCpMobileFrontendBundle:mycasatrip:comments.html.twig', array(
                'comments' => $comments,
                'comment_type' => $comment_type,
                'items_per_page' => $items_per_page,
                'total_items' => $paginator->getTotalItems(),
                'current_page' => $page
            ));
        }else{
            return $this->render('FrontEndBundle:mycasatrip:comments.html.twig', array(
                'comments' => $comments,
                'comment_type' => $comment_type,
                'items_per_page' => $items_per_page,
                'total_items' => $paginator->getTotalItems(),
                'current_page' => $page
            ));
        }
    }

    public function cancelOfferAction($generalReservationId) {
        $user = $this->getUser();
        $em = $this->getDoctrine()->getManager();
        $service_time = $this->get('time');

        $generalReservation = $em
                ->getRepository('mycpBundle:generalReservation')
                ->getGeneralReservationById($generalReservationId);

        if ($user->getUserId() != $generalReservation->getGenResUserId()) {
            $message = $this->get('translator')->trans("NOT_ALLOW_OFFER");
            $this->get('session')->getFlashBag()->add('message', $message);

            return $this->redirect($this->generateUrl('frontend_mycasatrip_pending'));
        }

        if ($generalReservation->getGenResStatus() != \MyCp\mycpBundle\Entity\generalReservation::STATUS_PENDING &&
            $generalReservation->getGenResStatus() != \MyCp\mycpBundle\Entity\generalReservation::STATUS_AVAILABLE &&
            $generalReservation->getGenResStatus() != \MyCp\mycpBundle\Entity\generalReservation::STATUS_PARTIAL_AVAILABLE) {

            $message = $this->get('translator')->trans("NOT_ALLOW_STATUS_OFFER");
            $this->get('session')->getFlashBag()->add('message', $message);

            return $this->redirect($this->generateUrl('frontend_mycasatrip_pending'));
        }

        $ownershipReservations = $em
                ->getRepository('mycpBundle:generalReservation')
                ->getOwnershipReservations($generalReservation);

        $arrayPhotos = array();
        $arrayNights = array();

        $initialPayment = 0;

        foreach ($ownershipReservations as $ownershipReservation) {
            $photos = $em
                    ->getRepository('mycpBundle:ownership')
                    ->getPhotos(
                    // TODO: This line is very strange. Why take the ownId of the genRes of the ownRes?!
                    $ownershipReservation->getOwnResGenResId()->getGenResOwnId()->getOwnId()
            );

            if (!empty($photos)) {
                array_push($arrayPhotos, $photos);
            }

            $totalNights = $service_time
                    ->nights(
                    $ownershipReservation->getOwnResReservationFromDate()->getTimestamp(), $ownershipReservation->getOwnResReservationToDate()->getTimestamp()
            );

            array_push($arrayNights, $totalNights);

            $comission = $ownershipReservation->getOwnResGenResId()->getGenResOwnId()->getOwnCommissionPercent() / 100;
            //Initial down payment
            if ($ownershipReservation->getOwnResNightPrice() > 0)
                $initialPayment += $ownershipReservation->getOwnResNightPrice() * $totalNights * $comission;
            else
                $initialPayment += getOwnResTotalInSite() * $comission;
        }

        return $this->render('FrontEndBundle:mycasatrip:cancelOffer.html.twig', array(
                    'generalReservation' => $generalReservation,
                    'reservations' => $ownershipReservations,
                    'nights' => $arrayNights,
                    'photos' => $photos,
                    'initialPayment' => $initialPayment
        ));
    }

    public function cancelOfferCallbackAction($genResID) {
        $em = $this->getDoctrine()->getManager();

        $generalReservation = $em
                ->getRepository('mycpBundle:generalReservation')
                ->getGeneralReservationById($genResID);

        $ownershipReservations = $em
                ->getRepository('mycpBundle:generalReservation')
                ->getOwnershipReservations($generalReservation);

        foreach ($ownershipReservations as $ownRes) {
            $ownRes->setOwnResStatus(ownershipReservation::STATUS_CANCELLED);
            $em->persist($ownRes);
        }

        $generalReservation->setGenResStatus(\MyCp\mycpBundle\Entity\generalReservation::STATUS_CANCELLED);
        $em->persist($generalReservation);

        $em->flush();

        $message = $this->get('translator')->trans("CANCEL_OFFER_SUCESS")." ". $generalReservation->getCASId();
        $this->get('session')->getFlashBag()->add('message', $message);

        return $this->redirect($this->generateUrl('frontend_mycasatrip_pending'));
    }

    public function appsMycasatripAction(){
        return $this->render('FrontEndBundle:mycasatrip:app_index.html.twig', array());
    }

}
