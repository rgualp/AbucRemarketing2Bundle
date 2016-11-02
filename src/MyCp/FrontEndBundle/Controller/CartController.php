<?php

namespace MyCp\FrontEndBundle\Controller;

use MyCp\mycpBundle\Entity\room;
use MyCp\mycpBundle\Entity\season;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use MyCp\mycpBundle\Entity\generalReservation;
use MyCp\mycpBundle\Entity\cart;
use Abuc\RemarketingBundle\Event\JobEvent;
use MyCp\mycpBundle\Entity\ownershipReservation;
use Symfony\Component\HttpFoundation\JsonResponse;

class CartController extends Controller {

    /**
     * @return Response
     */
    public function countCartItemsAction() {
        $em = $this->getDoctrine()->getManager();
        $user_ids = $em->getRepository('mycpBundle:user')->getIds($this);
        $countItems = $em->getRepository('mycpBundle:cart')->countItems($user_ids);
        return $this->render('FrontEndBundle:cart:cartCountItems.html.twig', array(
                    'count' => $countItems
        ));
    }

    /**
     * @return Response
     */
    public function countCestatItemsAction() {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        // disponibles Mayores que (hoy - 30) días
        $date = \date('Y-m-j');
        $new_date = strtotime('-60 hours', strtotime($date));
        $new_date = \date('Y-m-j', $new_date);
        $string_sql = "AND gre.gen_res_status_date > '$new_date'";
        $status_string = 'ownre.own_res_status =' . ownershipReservation::STATUS_AVAILABLE;
        $list = ($user!='')?$em->getRepository('mycpBundle:ownershipReservation')->findByUserAndStatus($user->getUserId(), $status_string, $string_sql):array();

        return $this->render('FrontEndBundle:cart:cestaCountItems.html.twig', array(
                'count' => count($list)
            ));
    }


    public function emptyCartAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $user_ids = $em->getRepository('mycpBundle:user')->getIds($this);
        $em->getRepository('mycpBundle:cart')->emptyCart($user_ids);
        $trans = $this->get('translator');
        $message = $trans->trans('CART_EMPTY_SUCCESSFUL');
        $this->get('session')->getFlashBag()->add('message_global_success', $message);
        return $this->redirect($this->generateUrl('frontend_view_cart'));
    }

    public function addToCartAction($id_ownership, Request $request) {
        $check_dispo=$request->get('check_dispo');
        $em = $this->getDoctrine()->getManager();
        if (!$request->get('data_reservation'))
            throw $this->createNotFoundException();
        $data = $request->get('data_reservation');
        //var_dump($data); exit();
        $data = explode('/', $data);

        $from_date = $data[0];
        $to_date = $data[1];
        $ids_rooms = $data[2];
        $count_guests = $data[3];
        $count_kids = $data[4];
        $count_kidsAge_1 = $data[5];
        $count_kidsAge_2 = $data[6];
        $count_kidsAge_3 = $data[7];

        //dump($data); die;

        $array_ids_rooms = explode('&', $ids_rooms);
        array_shift($array_ids_rooms);
        $array_count_guests = explode('&', $count_guests);
        array_shift($array_count_guests);
        $array_count_kids = explode('&', $count_kids);
        array_shift($array_count_kids);

        $array_count_kidsAge_1 = explode('&', $count_kidsAge_1);
        array_shift($array_count_kidsAge_1);

        $array_count_kidsAge_2 = explode('&', $count_kidsAge_2);
        array_shift($array_count_kidsAge_2);

        $array_count_kidsAge_3 = explode('&', $count_kidsAge_3);
        array_shift($array_count_kidsAge_3);

        $reservation_date_from = $from_date;
        $reservation_date_from = explode('&', $reservation_date_from);

        $start_timestamp = mktime(0, 0, 0, $reservation_date_from[1], $reservation_date_from[0], $reservation_date_from[2]);

        $reservation_date_to = $to_date;
        $reservation_date_to = explode('&', $reservation_date_to);
        $end_timestamp = mktime(0, 0, 0, $reservation_date_to[1], $reservation_date_to[0], $reservation_date_to[2]);

        $user_ids = $em->getRepository('mycpBundle:user')->getIds($this);
        $cartItems = $em->getRepository('mycpBundle:cart')->getCartItems($user_ids);
        if(isset($check_dispo) && $check_dispo!='' && ($check_dispo==1 || $check_dispo==2 ) ){
            $ownerShip=$em->getRepository('mycpBundle:generalReservation')->getOwnShipReserByUser($user_ids);
        }


        $showError = false;
        $showErrorOwnExist = false;
        $showErrorItem='';

        for ($a = 0; $a < count($array_ids_rooms); $a++) {
            $insert = 1;
            foreach ($cartItems as $item) {
                $cartDateFrom = $item->getCartDateFrom()->getTimestamp();
                $cartDateTo = $item->getCartDateTo()->getTimestamp();
                $date = new \DateTime();
                $date->setTimestamp(strtotime("-1 day", $cartDateTo));
                $cartDateTo = $date->getTimestamp();

                if (isset($array_count_guests[$a]) && isset($array_count_kids[$a]) &&
                        (($cartDateFrom <= $start_timestamp && $cartDateTo >= $start_timestamp) ||
                        ($cartDateFrom <= $end_timestamp && $cartDateTo >= $end_timestamp)) &&
                        $item->getCartRoom() == $array_ids_rooms[$a]
                ) {
                    $insert = 0;
                    $showError = 1;
                    $showErrorItem=$item;
                }
            }
            if(isset($check_dispo) && $check_dispo!='' && ($check_dispo==1 || $check_dispo==2 ) ){
                if(count($ownerShip)){
                    foreach ($ownerShip as $item){
                        $ownDateFrom = $item->getOwnResReservationFromDate()->getTimestamp();
                        $ownDateTo = $item->getOwnResReservationToDate()->getTimestamp();
                        $date = new \DateTime();
                        $date->setTimestamp(strtotime("-1 day", $ownDateTo));
                        $ownDateTo = $date->getTimestamp();
                        if ((($ownDateFrom <= $start_timestamp && $ownDateTo >= $start_timestamp) ||
                                ($ownDateFrom <= $end_timestamp && $ownDateTo >= $end_timestamp)) &&
                            $item->getOwnResSelectedRoomId() == $array_ids_rooms[$a]) {
                            $insert = 0;
                            $showError = 1;
                            $showErrorOwnExist = 1;
                        }

                    }
                }
            }
            if ($insert == 1) {
                $room = $em->getRepository('mycpBundle:room')->find($array_ids_rooms[$a]);

                if($room != null) {
                    $serviceFee = $em->getRepository("mycpBundle:serviceFee")->getCurrent();
                    $cart = new cart();
                    $fromDate = new \DateTime();
                    $fromDate->setTimestamp($start_timestamp);
                    $cart->setCartDateFrom($fromDate);

                    $toDate = new \DateTime();
                    $toDate->setTimestamp($end_timestamp);
                    $cart->setCartDateTo($toDate);
                    $cart->setCartRoom($room);
                    $cart->setServiceFee($serviceFee);

                    if (isset($array_count_guests[$a]))
                        $cart->setCartCountAdults($array_count_guests[$a]);
                    else
                        $cart->setCartCountAdults(1);

                    if (isset($array_count_kids[$a]))
                        $cart->setCartCountChildren($array_count_kids[$a]);
                    else
                        $cart->setCartCountChildren(0);

                    $kidsAge = array();

                    if (isset($array_count_kidsAge_1[$a]) && $array_count_kidsAge_1[$a] != -1)
                        $kidsAge["FirstKidAge"] = $array_count_kidsAge_1[$a];

                    if (isset($array_count_kidsAge_2[$a]) && $array_count_kidsAge_2[$a] != -1)
                        $kidsAge["SecondKidAge"] = $array_count_kidsAge_2[$a];

                    if (isset($array_count_kidsAge_3[$a]) && $array_count_kidsAge_3[$a] != -1)
                        $kidsAge["ThirdKidAge"] = $array_count_kidsAge_3[$a];

                    if (count($kidsAge))
                        $cart->setChildrenAges($kidsAge);

                    $cart->setCartCreatedDate(new \DateTime());
                    if ($user_ids["user_id"] != null) {
                        $user = $em->getRepository("mycpBundle:user")->find($user_ids["user_id"]);
                        $cart->setCartUser($user);
                    } else if ($user_ids["session_id"] != null)
                        $cart->setCartSessionId($user_ids["session_id"]);

                    $em->persist($cart);
                    $em->flush();
                    if ($user_ids["user_id"] != null || $user_ids["session_id"] != null) {
                        // inform listeners that a reservation was sent out
                        $dispatcher = $this->get('event_dispatcher');
                        $eventData = new \MyCp\mycpBundle\JobData\UserJobData($user_ids["user_id"], $user_ids["session_id"]);
                        $dispatcher->dispatch('mycp.event.cart.full', new JobEvent($eventData));
                    }
                }
            }
        }
        if(isset($check_dispo) && $check_dispo!='' && $check_dispo==1 && !$showErrorOwnExist){
                //Es que el usuario mando a consultar la disponibilidad
                $this->checkDispo(($showErrorItem!='')?$showErrorItem->getCartId():$cart->getCartId(),$request,false);
        }
        elseif(isset($check_dispo) && $check_dispo!='' && $check_dispo==2 && !$showErrorOwnExist){
            //Es que el usuario mando a hacer una reserva
            $this->checkDispo(($showErrorItem!='')?$showErrorItem->getCartId():$cart->getCartId(),$request,true);
        }
        else{
            if ( !$request->isXmlHttpRequest() ){
                $message = $this->get('translator')->trans("ADD_TO_CART_ERROR");
                $this->get('session')->getFlashBag()->add('message_global_error', $message);
            }
        }
        //If ajax
        if ( $request->isXmlHttpRequest() ) {
            if(isset($check_dispo) && $check_dispo=='' && !$showError){
                $data=$this->dataCart();
                $response =new Response($this->renderView('FrontEndBundle:cart:contentCart.html.twig', $data));
            }
            elseif(isset($check_dispo) && $check_dispo!='' && $check_dispo==1 && !$showErrorOwnExist){
                $response =new Response(1);
            }
            elseif(isset($check_dispo) && $check_dispo!='' && $check_dispo==2 && !$showErrorOwnExist){
                $data=$this->dataCesta();
                $response =new Response($this->renderView('FrontEndBundle:cart:contentCesta.html.twig', $data));
            }
            else
                $response =new Response(0);

            return $response;
        }
        else{
            return $this->redirect($this->generateUrl('frontend_view_cart'));
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function removeFromWhisListAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $cartItem = $em->getRepository('mycpBundle:cart')->find($request->get('data'));
        $em->remove($cartItem);
        $em->flush();
        $data=$this->dataCart();
        $response =new Response($this->renderView('FrontEndBundle:cart:contentCart.html.twig', $data));
        return $response;
    }
    public function removeFromCartAction($data, Request $request) {


        $em = $this->getDoctrine()->getManager();
        $array_data = explode('-', $data);
        $cartItem = $em->getRepository("mycpBundle:cart")->find($array_data[0]);

        if($cartItem != null) {

            $cartItemDateFrom = $cartItem->getCartDateFrom()->getTimestamp();
            $cartItemDateTo = $cartItem->getCartDateTo()->getTimestamp();
            $cartItemDateToBefore = strtotime("-1 day", $cartItemDateTo);
            $deleteToAfter = strtotime("+1 day", $array_data[1]);

            if ($cartItemDateFrom == $array_data[1]) {
                $date = new \DateTime();
                $date->setTimestamp(strtotime("+1 day", $cartItemDateFrom));
                $cartItem->setCartDateFrom($date);
            } else if ($cartItemDateTo == $deleteToAfter) {
                $dateTo = new \DateTime();
                $dateTo->setTimestamp($cartItemDateToBefore);
                $cartItem->setCartDateTo($dateTo);
            } else if ($array_data[1] < $cartItemDateTo && $array_data[1] > $cartItemDateFrom) {
                $cartItemNext = $cartItem->getClone();
                $date = new \DateTime();
                $date->setTimestamp($array_data[1]);
                $cartItem->setCartDateTo($date);

                $date = new \DateTime();
                $date->setTimestamp($deleteToAfter);
                $cartItemNext->setCartDateFrom($date);
                $em->persist($cartItemNext);
            }

            $cartItem->setCartCreatedDate(new \DateTime());
            $em->persist($cartItem);
            $em->flush();
            $user_ids = $em->getRepository('mycpBundle:user')->getIds($this);
            $cartItems = $em->getRepository('mycpBundle:cart')->getCartItems($user_ids);

            foreach ($cartItems as $item) {
                if ($item->getCartDateTo()->getTimestamp() <= $item->getCartDateFrom()->getTimestamp()) {
                    //delete cartItem
                    $em->remove($cartItem);
                }
            }
            $em->flush();

            $cartItems = $em->getRepository('mycpBundle:cart')->getCartItems($user_ids);
            if (count($cartItems) < 1) {
                return new Response('0');
            }
        }
        return $this->getCartBodyAction($request);
    }

    public function viewCartAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $mobileDetector = $this->get('mobile_detect.mobile_detector');

        /* $last_own = $request->getSession()->get('services_pre_reservation_last_own');
          if ($last_own)
          $ownership = $em->getRepository('mycpBundle:ownership')->find($last_own);
          else
          $ownership = 0; */

        $user_ids = $em->getRepository('mycpBundle:user')->getIds($this);
        $countItems = $em->getRepository('mycpBundle:cart')->countItems($user_ids);

        if ($mobileDetector->isMobile()){
            return $this->render('MyCpMobileFrontendBundle:cart:cart.html.twig', array(
                'countItems' => $countItems,
            ));
        }else{
            return $this->render('FrontEndBundle:cart:cart.html.twig', array(
                'countItems' => $countItems,
            ));
        }
    }

    /**
     * @return array
     */
    public function dataCesta(){
        $user = $this->getUser();
        $em = $this->getDoctrine()->getManager();

        // disponibles Mayores que (hoy - 30) días
        $date = \date('Y-m-j');
        $new_date = strtotime('-60 hours', strtotime($date));
        $new_date = \date('Y-m-j', $new_date);
        $string_sql = "AND gre.gen_res_status_date > '$new_date'";
        $status_string = 'ownre.own_res_status =' . ownershipReservation::STATUS_AVAILABLE;

        $paginator = $this->get('ideup.simple_paginator');
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
        return array(
            'res_available' => $res_available,
            'nights' => $nights,
            'photos' => $array_photos,
            'items_per_page' => $items_per_page,
            'total_items' => $paginator->getTotalItems(),
            'current_page' => $page
        );
    }
    /**
     * @return array
     */
    public function dataCart(){
        $em = $this->getDoctrine()->getManager();
        $user_ids = $em->getRepository('mycpBundle:user')->getIds($this);
        $cartItems = $em->getRepository('mycpBundle:cart')->getCartItems($user_ids);
        $service_time = $this->get('Time');

        $min_date = 0;
        $max_date = 0;
        $array_photos = array();
        foreach ($cartItems as $item) {
            if ($min_date == 0)
                $min_date = $item->getCartDateFrom()->getTimestamp();
            else if ($item->getCartDateFrom()->getTimestamp() < $min_date)
                $min_date = $item->getCartDateFrom()->getTimestamp();

            if ($max_date == 0)
                $max_date = $item->getCartDateTo()->getTimestamp();
            else if ($item->getCartDateTo()->getTimestamp() > $max_date)
                $max_date = $item->getCartDateTo()->getTimestamp();

            $photo = $em->getRepository('mycpBundle:ownership')->getOwnershipPhoto($item->getCartRoom()->getRoomOwnership()->getOwnId());
            array_push($array_photos, $photo);
        }


        $array_dates = $service_time->datesBetween($min_date, $max_date);
        $array_dates_string_day = array();
        $array_dates_string = array();
        $array_season = array();
        $array_clear_date = array();

        if ($array_dates) {
            $em = $this->getDoctrine()->getManager();
            foreach ($array_dates as $date) {
                array_push($array_dates_string, \date('/m/Y', $date));
                array_push($array_dates_string_day, \date('d', $date));

                $insert = 1;
                $array_season_temp = array();
                foreach ($cartItems as $item) {
                    $destination = $item->getCartRoom()->getRoomOwnership()->getOwnDestination();
                    $destination_id = isset($destination) ? $item->getCartRoom()->getRoomOwnership()->getOwnDestination()->getDesId() : null;
                    $seasons = $em->getRepository("mycpBundle:season")->getSeasons($min_date, $max_date, $destination_id);
                    $seasonTypes = $service_time->seasonByDate($seasons, $date);
                    array_push($array_season_temp, $seasonTypes);

                    if ($date >= $item->getCartDateFrom()->getTimestamp() && $date <= $item->getCartDateTo()->getTimestamp()) {
                        $insert = 0;
                    }

                }
                if ($insert == 1) {
                    $array_clear_date[$date] = 1;
                }

                $array_season[$date] = $array_season_temp;
            }

            $touristTax = $this->calculateTouristTax($cartItems, $em, $service_time);
        }

        $currentServiceFee = $em->getRepository("mycpBundle:serviceFee")->getCurrent();
        return array(
            'dates_string' => $array_dates_string,
            'dates_string_day' => $array_dates_string_day,
            'dates_timestamp' => $array_dates,
            'cartItems' => $cartItems,
            'array_season' => $array_season,
            'array_clear_date' => $array_clear_date,
            'currentServiceFee' => $currentServiceFee,
            'photos' => $array_photos,
            'touristTax' => $touristTax);
    }
    public function getCartBodyAction($flag) {
        $em = $this->getDoctrine()->getManager();
        $user_ids = $em->getRepository('mycpBundle:user')->getIds($this);
        $cartItems = $em->getRepository('mycpBundle:cart')->getCartItems($user_ids);
        $service_time = $this->get('Time');

        $min_date = 0;
        $max_date = 0;
        $array_photos = array();
        foreach ($cartItems as $item) {
            if ($min_date == 0)
                $min_date = $item->getCartDateFrom()->getTimestamp();
            else if ($item->getCartDateFrom()->getTimestamp() < $min_date)
                $min_date = $item->getCartDateFrom()->getTimestamp();

            if ($max_date == 0)
                $max_date = $item->getCartDateTo()->getTimestamp();
            else if ($item->getCartDateTo()->getTimestamp() > $max_date)
                $max_date = $item->getCartDateTo()->getTimestamp();

            $photo = $em->getRepository('mycpBundle:ownership')->getOwnershipPhoto($item->getCartRoom()->getRoomOwnership()->getOwnId());
            array_push($array_photos, $photo);
        }


        $array_dates = $service_time->datesBetween($min_date, $max_date);
        $array_dates_string_day = array();
        $array_dates_string = array();
        $array_season = array();
        $array_clear_date = array();

        if ($array_dates) {
            $em = $this->getDoctrine()->getManager();
            foreach ($array_dates as $date) {
                array_push($array_dates_string, \date('/m/Y', $date));
                array_push($array_dates_string_day, \date('d', $date));

                $insert = 1;
                $array_season_temp = array();
                foreach ($cartItems as $item) {
                    $destination = $item->getCartRoom()->getRoomOwnership()->getOwnDestination();
                    $destination_id = isset($destination) ? $item->getCartRoom()->getRoomOwnership()->getOwnDestination()->getDesId() : null;
                    $seasons = $em->getRepository("mycpBundle:season")->getSeasons($min_date, $max_date, $destination_id);
                    $seasonTypes = $service_time->seasonByDate($seasons, $date);
                    array_push($array_season_temp, $seasonTypes);

                    if ($date >= $item->getCartDateFrom()->getTimestamp() && $date <= $item->getCartDateTo()->getTimestamp()) {
                        $insert = 0;
                    }

                }
                if ($insert == 1) {
                    $array_clear_date[$date] = 1;
                }

                $array_season[$date] = $array_season_temp;
            }

            $touristTax = $this->calculateTouristTax($cartItems, $em, $service_time);
        }

        $currentServiceFee = $em->getRepository("mycpBundle:serviceFee")->getCurrent();

        $mobileDetector = $this->get('mobile_detect.mobile_detector');


        if ($mobileDetector->isMobile()){
            if($flag=='true')
                return $this->render('MyCpMobileFrontendBundle:cart:navBarCart.html.twig', array(
                    'dates_string' => $array_dates_string,
                    'dates_string_day' => $array_dates_string_day,
                    'dates_timestamp' => $array_dates,
                    'cartItems' => $cartItems,
                    'array_season' => $array_season,
                    'array_clear_date' => $array_clear_date,
                    'currentServiceFee' => $currentServiceFee,
                    'photos' => $array_photos,
                    'touristTax' => $touristTax));
            else
                return $this->render('MyCpMobileFrontendBundle:cart:bodyCart.html.twig', array(
                    'dates_string' => $array_dates_string,
                    'dates_string_day' => $array_dates_string_day,
                    'dates_timestamp' => $array_dates,
                    'cartItems' => $cartItems,
                    'array_season' => $array_season,
                    'array_clear_date' => $array_clear_date,
                    'currentServiceFee' => $currentServiceFee,
                    'touristTax' => $touristTax
                ));

        }else{
            if($flag=='true')
                return $this->render('FrontEndBundle:cart:navBarCart.html.twig', array(
                        'dates_string' => $array_dates_string,
                        'dates_string_day' => $array_dates_string_day,
                        'dates_timestamp' => $array_dates,
                        'cartItems' => $cartItems,
                        'array_season' => $array_season,
                        'array_clear_date' => $array_clear_date,
                        'currentServiceFee' => $currentServiceFee,
                        'photos' => $array_photos,
                        'touristTax' => $touristTax));
            else
                return $this->render('FrontEndBundle:cart:bodyCart.html.twig', array(
                    'dates_string' => $array_dates_string,
                    'dates_string_day' => $array_dates_string_day,
                    'dates_timestamp' => $array_dates,
                    'cartItems' => $cartItems,
                    'array_season' => $array_season,
                    'array_clear_date' => $array_clear_date,
                    'currentServiceFee' => $currentServiceFee,
                    'touristTax' => $touristTax
                ));
        }
    }

    private function calculateTouristTax($cartItems, $em, $service_time){
        $touristTax = 0;
        $accommodationId = (count($cartItems)) ? $cartItems[0]->getCartRoom()->getRoomOwnership()->getOwnId() : 0;
        $destination = (count($cartItems)) ? $cartItems[0]->getCartRoom()->getRoomOwnership()->getOwnDestination(): null;
        $destination_id = isset($destination) ? $destination->getDesId() : null;

        $nights = 0;
        $totalPrice = 0;
        $totalRooms = 0;
        foreach($cartItems as $item)
        {

               if($accommodationId != $item->getCartRoom()->getRoomOwnership()->getOwnId())
               {
                   $accommodationId = $item->getCartRoom()->getRoomOwnership()->getOwnId();
                   $destination = (count($cartItems)) ? $cartItems[0]->getCartRoom()->getRoomOwnership()->getOwnDestination(): null;
                   $destination_id = isset($destination) ? $destination->getDesId() : null;

                   $tax = $em->getRepository("mycpBundle:serviceFee")->calculateTouristServiceFee($totalRooms, $nights / $totalRooms, (($nights <= 1) ? $totalPrice : $totalPrice / $nights), $item->getServiceFee()->getId());
                   $touristTax += $tax * $totalPrice;

                   $nights = 0;
                   $totalPrice = 0;
                   $totalRooms = 0;
               }

               $totalRooms++;
               $roomNights = $service_time->nights($item->getCartDateFrom()->format("Y-m-d"), $item->getCartDateTo()->format("Y-m-d"));
               $nights += $roomNights;

               $interval = \DateInterval::createFromDateString('1 day');
               $period = new \DatePeriod($item->getCartDateFrom(), $interval, $item->getCartDateTo());

               foreach ( $period as $date ){
                   $seasons = $em->getRepository("mycpBundle:season")->getSeasons($item->getCartDateFrom()->format("Y-m-d"), $item->getCartDateTo()->format("Y-m-d"), $destination_id);
                   $seasonTypes = $service_time->seasonByDate($seasons, $date->format("Y-m-d"));

                   $tripleFee = ($item->getTripleRoomCharged()) ? $this->container->getParameter('configuration.triple.room.charge') : 0;
                   $price = ($item->getCartRoom()->getPriceBySeasonTypeString($seasonTypes) + $tripleFee);
                   $totalPrice += ($item->getCartRoom()->getPriceBySeasonTypeString($seasonTypes) + $tripleFee);
               }
        }
        if($totalRooms > 0){
            $tax = $em->getRepository("mycpBundle:serviceFee")->calculateTouristServiceFee($totalRooms, $nights / $totalRooms, (($nights <= 1) ? $totalPrice : $totalPrice / $nights), $item->getServiceFee()->getId());
            $touristTax += $tax * $totalPrice;
        }


        return $touristTax;

    }

    public function checkAvailabilitySubmitAction(Request $request) {
        $request->getSession()->set('message_cart', strip_tags($request->get('comment_cart')));
        return $this->redirect($this->generateUrl('frontend_check_availability_cart'));
    }

    /**
     * @param $id_car
     * @param $request
     * @return bool|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function checkDispo($id_car,$request,$inmediatily_booking){
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $reservations = array();
        $own_ids = array();
        $array_photos = array();
        $user_ids = $em->getRepository('mycpBundle:user')->getIds($this);
        $cartItem = $em->getRepository('mycpBundle:cart')->find($id_car);
        $cartItems[]=$cartItem;
        $min_date = null;
        $max_date = null;
        $generalReservations = array();

        if (count($cartItems) > 0) {
            $res_array = array();
            $own_visited = array();
            foreach ($cartItems as $item) {

                if ($min_date == null)
                    $min_date = $item->getCartDateFrom();
                else if ($item->getCartDateFrom() < $min_date)
                    $min_date = $item->getCartDateFrom();

                if ($max_date == null)
                    $max_date = $item->getCartDateTo();
                else if ($item->getCartDateTo() > $max_date)
                    $max_date = $item->getCartDateTo();

                $res_own_id = $item->getCartRoom()->getRoomOwnership()->getOwnId();

                $array_group_by_own_id = array();
                $flag = 1;
                foreach ($own_visited as $own) {
                    if ($own == $res_own_id) {
                        $flag = 0;
                    }
                }
                if ($flag == 1)
                    foreach ($cartItems as $item) {
                        if ($res_own_id == $item->getCartRoom()->getRoomOwnership()->getOwnId()) {
                            array_push($array_group_by_own_id, $item);
                        }
                    }
                array_push($res_array, $array_group_by_own_id);
                array_push($own_visited, $res_own_id);
            }
            $service_time = $this->get('Time');
            $nigths = array();
            foreach ($res_array as $resByOwn) {
                if (isset($resByOwn[0])) {
                    $ownership = $em->getRepository('mycpBundle:ownership')->find($resByOwn[0]->getCartRoom()->getRoomOwnership()->getOwnId());

                    $serviceFee = $em->getRepository("mycpBundle:serviceFee")->getCurrent();
                    $general_reservation = new generalReservation();
                    $general_reservation->setGenResUserId($user);
                    $general_reservation->setGenResDate(new \DateTime(date('Y-m-d')));
                    $general_reservation->setGenResStatusDate(new \DateTime(date('Y-m-d')));
                    $general_reservation->setGenResHour(date('G'));
                    if($inmediatily_booking)
                        $general_reservation->setGenResStatus(generalReservation::STATUS_AVAILABLE);
                    else
                        $general_reservation->setGenResStatus(generalReservation::STATUS_PENDING);
                    $general_reservation->setGenResFromDate($min_date);
                    $general_reservation->setGenResToDate($max_date);
                    $general_reservation->setGenResSaved(0);
                    $general_reservation->setGenResOwnId($ownership);
                    $general_reservation->setGenResDateHour(new \DateTime(date('H:i:s')));
                    $general_reservation->setServiceFee($serviceFee);


                    $total_price = 0;
                    $partial_total_price = array();
                    $destination_id = ($ownership->getOwnDestination() != null) ? $ownership->getOwnDestination()->getDesId() : null;
                    foreach ($resByOwn as $item) {
                        $triple_room_recharge = ($item->getTripleRoomCharged()) ? $this->container->getParameter('configuration.triple.room.charge') : 0;
                        $array_dates = $service_time->datesBetween($item->getCartDateFrom()->getTimestamp(), $item->getCartDateTo()->getTimestamp());
                        $temp_price = 0;
                        $seasons = $em->getRepository("mycpBundle:season")->getSeasons($item->getCartDateFrom()->getTimestamp(), $item->getCartDateTo()->getTimestamp(), $destination_id);

                        for ($a = 0; $a < count($array_dates) - 1; $a++) {
                            $seasonType = $service_time->seasonTypeByDate($seasons, $array_dates[$a]);
                            $roomPrice = $item->getCartRoom()->getPriceBySeasonType($seasonType);
                            $total_price += $roomPrice + $triple_room_recharge;
                            $temp_price += $roomPrice + $triple_room_recharge;
                        }
                        array_push($partial_total_price, $temp_price);
                    }
                    $general_reservation->setGenResTotalInSite($total_price);
                    $em->persist($general_reservation);

                    $arrayKidsAge = array();

                    $flag_1 = 0;
                    foreach ($resByOwn as $item) {
                        $ownership_reservation = $item->createReservation($general_reservation, $partial_total_price[$flag_1]);
                        if($inmediatily_booking)
                            $ownership_reservation->setOwnResStatus(ownershipReservation::STATUS_AVAILABLE);


                        array_push($reservations, $ownership_reservation);

                        $ownership_photo = $em->getRepository('mycpBundle:ownership')->getOwnershipPhoto($ownership_reservation->getOwnResGenResId()->getGenResOwnId()->getOwnId());
                        array_push($array_photos, $ownership_photo);

                        $nightsCount = $service_time->nights($ownership_reservation->getOwnResReservationFromDate()->getTimestamp(), $ownership_reservation->getOwnResReservationToDate()->getTimestamp());
                        array_push($nigths, $nightsCount);

                        if($item->getChildrenAges() != null)
                        {
                            $arrayKidsAge[$item->getCartRoom()->getRoomNum()] = $item->getChildrenAges();
                        }

                        $em->persist($ownership_reservation);
                        $em->flush();
                        array_push($own_ids, $ownership_reservation->getOwnResId());
                        $flag_1++;
                    }

                    //dump($arrayKidsAge); die;

                    $general_reservation->setChildrenAges($arrayKidsAge);
                    $em->flush();

                    //update generalReservation dates
                    $em->getRepository("mycpBundle:generalReservation")->updateDates($general_reservation);
                    array_push($generalReservations, $general_reservation->getGenResId());

                    if($general_reservation->getGenResOwnId()->getOwnInmediateBooking()){
                        $smsService = $this->get("mycp.notification.service");
                        $smsService->sendInmediateBookingSMSNotification($general_reservation);
                    }

                }
            }
        } else {
            return false;
        }
        $locale = $this->get('translator')->getLocale();
        // Enviando mail al cliente
        if(!$inmediatily_booking){
            $body = $this->render('FrontEndBundle:mails:email_check_available.html.twig', array(
                    'user' => $user,
                    'reservations' => $reservations,
                    'ids' => $own_ids,
                    'nigths' => $nigths,
                    'photos' => $array_photos,
                    'user_locale' => $locale
                ));

            $locale = $this->get('translator');
            $subject = $locale->trans('REQUEST_SENT');
            $service_email = $this->get('Email');
            $service_email->sendEmail(
                $subject, 'reservation@mycasaparticular.com', 'MyCasaParticular.com', $user->getUserEmail(), $body
            );
        }

        if(!$inmediatily_booking){
            //Enviando mail al reservation team
            foreach($generalReservations as $genResId){
                //Enviando correo a solicitud@mycasaparticular.com
                \MyCp\FrontEndBundle\Helpers\ReservationHelper::sendingEmailToReservationTeam($genResId, $em, $this, $service_email, $service_time, $request, 'solicitud@mycasaparticular.com', 'no-reply@mycasaparticular.com');
            }
        }
        $em->remove($cartItem);
        $em->flush();
        return true;
    }
    public function checkAvailabilityAction(Request $request) {

        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $reservations = array();
        $own_ids = array();
        $array_photos = array();
        $user_ids = $em->getRepository('mycpBundle:user')->getIds($this);
        $cartItems = $em->getRepository('mycpBundle:cart')->getCartItems($user_ids);
        $min_date = null;
        $max_date = null;
        $generalReservations = array();

        if (count($cartItems) > 0) {
            $res_array = array();
            $own_visited = array();
            foreach ($cartItems as $item) {

                if ($min_date == null)
                    $min_date = $item->getCartDateFrom();
                else if ($item->getCartDateFrom() < $min_date)
                    $min_date = $item->getCartDateFrom();

                if ($max_date == null)
                    $max_date = $item->getCartDateTo();
                else if ($item->getCartDateTo() > $max_date)
                    $max_date = $item->getCartDateTo();

                $res_own_id = $item->getCartRoom()->getRoomOwnership()->getOwnId();

                $array_group_by_own_id = array();
                $flag = 1;
                foreach ($own_visited as $own) {
                    if ($own == $res_own_id) {
                        $flag = 0;
                    }
                }
                if ($flag == 1)
                    foreach ($cartItems as $item) {
                        if ($res_own_id == $item->getCartRoom()->getRoomOwnership()->getOwnId()) {
                            array_push($array_group_by_own_id, $item);
                        }
                    }
                array_push($res_array, $array_group_by_own_id);
                array_push($own_visited, $res_own_id);
            }
            $service_time = $this->get('Time');
            $nigths = array();
            foreach ($res_array as $resByOwn) {
                if (isset($resByOwn[0])) {
                    $ownership = $em->getRepository('mycpBundle:ownership')->find($resByOwn[0]->getCartRoom()->getRoomOwnership()->getOwnId());

                    $serviceFee = $em->getRepository("mycpBundle:serviceFee")->getCurrent();
                    $general_reservation = new generalReservation();
                    $general_reservation->setGenResUserId($user);
                    $general_reservation->setGenResDate(new \DateTime(date('Y-m-d')));
                    $general_reservation->setGenResStatusDate(new \DateTime(date('Y-m-d')));
                    $general_reservation->setGenResHour(date('G'));
                    $general_reservation->setGenResStatus(generalReservation::STATUS_PENDING);
                    $general_reservation->setGenResFromDate($min_date);
                    $general_reservation->setGenResToDate($max_date);
                    $general_reservation->setGenResSaved(0);
                    $general_reservation->setGenResOwnId($ownership);
                    $general_reservation->setGenResDateHour(new \DateTime(date('H:i:s')));
                    $general_reservation->setServiceFee($serviceFee);


                    $total_price = 0;
                    $partial_total_price = array();
                    $destination_id = ($ownership->getOwnDestination() != null) ? $ownership->getOwnDestination()->getDesId() : null;
                    foreach ($resByOwn as $item) {
                        $triple_room_recharge = ($item->getTripleRoomCharged()) ? $this->container->getParameter('configuration.triple.room.charge') : 0;
                        $array_dates = $service_time->datesBetween($item->getCartDateFrom()->getTimestamp(), $item->getCartDateTo()->getTimestamp());
                        $temp_price = 0;
                        $seasons = $em->getRepository("mycpBundle:season")->getSeasons($item->getCartDateFrom()->getTimestamp(), $item->getCartDateTo()->getTimestamp(), $destination_id);

                        for ($a = 0; $a < count($array_dates) - 1; $a++) {
                            $seasonType = $service_time->seasonTypeByDate($seasons, $array_dates[$a]);
                            $roomPrice = $item->getCartRoom()->getPriceBySeasonType($seasonType);
                            $total_price += $roomPrice + $triple_room_recharge;
                            $temp_price += $roomPrice + $triple_room_recharge;
                        }
                        array_push($partial_total_price, $temp_price);
                    }
                    $general_reservation->setGenResTotalInSite($total_price);
                    $em->persist($general_reservation);

                    $arrayKidsAge = array();

                    $flag_1 = 0;
                    foreach ($resByOwn as $item) {
                        $ownership_reservation = $item->createReservation($general_reservation, $partial_total_price[$flag_1]);
                        array_push($reservations, $ownership_reservation);

                        $ownership_photo = $em->getRepository('mycpBundle:ownership')->getOwnershipPhoto($ownership_reservation->getOwnResGenResId()->getGenResOwnId()->getOwnId());
                        array_push($array_photos, $ownership_photo);

                        $nightsCount = $service_time->nights($ownership_reservation->getOwnResReservationFromDate()->getTimestamp(), $ownership_reservation->getOwnResReservationToDate()->getTimestamp());
                        array_push($nigths, $nightsCount);

                        if($item->getChildrenAges() != null)
                        {
                            $arrayKidsAge[$item->getCartRoom()->getRoomNum()] = $item->getChildrenAges();
                        }

                        $em->persist($ownership_reservation);
                        $em->flush();
                        array_push($own_ids, $ownership_reservation->getOwnResId());
                        $flag_1++;
                    }

                    //dump($arrayKidsAge); die;

                    $general_reservation->setChildrenAges($arrayKidsAge);
                    $em->flush();

                    //update generalReservation dates
                    $em->getRepository("mycpBundle:generalReservation")->updateDates($general_reservation);
                    array_push($generalReservations, $general_reservation->getGenResId());

                    if($general_reservation->getGenResOwnId()->getOwnInmediateBooking()){
                        $smsService = $this->get("mycp.notification.service");
                        $smsService->sendInmediateBookingSMSNotification($general_reservation);
                    }

                }
            }
        } else {
            return $this->redirect($this->generateUrl('frontend_view_cart'));
        }

        //Vaciar el carrito
        $em->getRepository("mycpBundle:cart")->emptyCart($user_ids);

        /*
         * Hallando otros ownerships en el mismo destino
         */
        $ownership = $em->getRepository('mycpBundle:ownership')->find($cartItems[0]->getCartRoom()->getRoomOwnership()->getOwnId());

        $owns_in_destination = $em->getRepository("mycpBundle:destination")->getRecommendableAccommodations($min_date, $max_date, $ownership->getOwnMinimumPrice(), $ownership->getOwnAddressMunicipality()->getMunId(), $ownership->getOwnAddressProvince()->getProvId(), 3, $ownership->getOwnId(), $user->getUserId());

        $locale = $this->get('translator')->getLocale();
        $destinations = $em->getRepository('mycpBundle:destination')->filter($locale, null, $ownership->getOwnAddressProvince()->getProvId(), null, $ownership->getOwnAddressMunicipality()->getMunId(), 3, $user->getUserId(), null);

        // Enviando mail al cliente
        $body = $this->render('FrontEndBundle:mails:email_check_available.html.twig', array(
            'user' => $user,
            'reservations' => $reservations,
            'ids' => $own_ids,
            'nigths' => $nigths,
            'photos' => $array_photos,
            'user_locale' => $locale
        ));

        $locale = $this->get('translator');
        $subject = $locale->trans('REQUEST_SENT');
        $service_email = $this->get('Email');
        $service_email->sendEmail(
                $subject, 'reservation@mycasaparticular.com', 'MyCasaParticular.com', $user->getUserEmail(), $body
        );

        //Enviando mail al reservation team
        foreach($generalReservations as $genResId)
        {
            //\MyCp\FrontEndBundle\Helpers\ReservationHelper::sendingEmailToReservationTeam($genResId, $em, $this, $service_email, $service_time, $request, 'reservation@mycasaparticular.com', 'no-reply@mycasaparticular.com');

            //Enviando correo a solicitud@mycasaparticular.com
            \MyCp\FrontEndBundle\Helpers\ReservationHelper::sendingEmailToReservationTeam($genResId, $em, $this, $service_email, $service_time, $request, 'solicitud@mycasaparticular.com', 'no-reply@mycasaparticular.com');
        }

        /*$user_tourist = $em->getRepository('mycpBundle:userTourist')->findOneBy(array('user_tourist_user' => $user->getUserId()));
        $array_own_res_home = array();
        foreach ($reservations as $gen) {
            $offset = $gen->getOwnResGenResId()->getGenResId();
            $temp = array();
            if (isset($array_own_res_home[$offset]))
                $temp = $array_own_res_home[$offset];
            array_push($temp, $gen);
            $array_own_res_home[$offset] = $temp;
        }
        //Enviando mail al reservation team
        $flag_3 = 0;
        foreach ($array_own_res_home as $own_array) {
            $temp_nigths = array_slice($nigths, $flag_3, count($own_array));
            $flag_3 = count($own_array);
            $body = $this->render('FrontEndBundle:mails:rt_email_check_available.html.twig', array(
                'user' => $user,
                'user_tourist' => $user_tourist,
                'reservations' => $own_array,
                'nigths' => $temp_nigths,
                'comment' => $request->getSession()->get('message_cart')
            ));

            $subject = "MyCasaParticular Reservas - " . strtoupper($user_tourist->getUserTouristLanguage()->getLangCode());

            $service_email->sendEmail(
                    $subject, 'no.reply@mycasaparticular.com', $subject, 'reservation@mycasaparticular.com', $body
            );
        }*/
        //exit();

        $mobileDetector = $this->get('mobile_detect.mobile_detector');


        if ($mobileDetector->isMobile()){
            return $this->render('MyCpMobileFrontendBundle:reservation:confirmReview.html.twig', array(
                "owns_in_destination" => $owns_in_destination,
                "other_destinations" => $destinations
            ));
        }else{
            return $this->render('FrontEndBundle:reservation:confirmReview.html.twig', array(
                "owns_in_destination" => $owns_in_destination,
                "other_destinations" => $destinations
            ));
        }

    }

}
