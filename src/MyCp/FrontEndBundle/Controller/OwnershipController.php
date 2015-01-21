<?php

namespace MyCp\FrontEndBundle\Controller;

use MyCp\mycpBundle\Entity\ownership;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use MyCp\FrontEndBundle\Helpers\Utils;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use MyCp\mycpBundle\Entity\ownershipReservation;
use MyCp\mycpBundle\Entity\season;

class OwnershipController extends Controller {

    public function get_reservation_calendarAction(Request $request) {
        $from = $request->get('from');
        $to = $request->get('to');
        
        $reservation_from = explode('/', $from);
        $dateFrom = new \DateTime();
        $start_timestamp = mktime(0, 0, 0, $reservation_from[1], $reservation_from[0], $reservation_from[2]);
        $dateFrom->setTimestamp($start_timestamp);

        $reservation_to = explode('/', $to);
        $dateTo = new \DateTime();
        $end_timestamp = mktime(0, 0, 0, $reservation_to[1], $reservation_to[0], $reservation_to[2]);
        $dateTo->setTimestamp($end_timestamp);
        $owner_id = $request->get('own_id');

        if (!$owner_id) {
            throw $this->createNotFoundException();
        }

        $em = $this->getDoctrine()->getManager();

        $ownership = $em->getRepository('mycpBundle:ownership')->find($owner_id);

        $general_reservations = $em->getRepository('mycpBundle:generalReservation')->findBy(array('gen_res_own_id' => $owner_id));
        $reservations = array();
        foreach ($general_reservations as $gen_res) {
            $own_reservations = $em->getRepository('mycpBundle:ownershipReservation')->getReservationReservedByGeneralAndDate($gen_res->getGenResId(),$dateFrom, $dateTo);//findBy(array('own_res_gen_res_id' => $gen_res->getGenResId()));
            foreach ($own_reservations as $own_res) {
                array_push($reservations, $own_res);
            }
        }
        

        $rooms = $em->getRepository('mycpBundle:room')->findBy(array('room_ownership' => $owner_id, 'room_active' => true));

        $service_time = $this->get('Time');
        $array_dates = $service_time->datesBetween($start_timestamp, $end_timestamp);

        $array_no_available = array();
        $no_available_days = array();
        $no_available_days_ready = array();
        $array_prices = array();
        $prices_dates = array();


        foreach ($rooms as $room) {
            $temp_local = array();
            $unavailable_room = $em->getRepository('mycpBundle:unavailabilityDetails')->getRoomDetails($room->getRoomId());
            $flag = 0;
            if ($unavailable_room) {
                foreach ($unavailable_room as $ur) {
                    $unavailable_days = $service_time->datesBetween($ur->getUdFromDate()->getTimestamp(), $ur->getUdToDate()->getTimestamp());
                    // unavailable details
                    if ($start_timestamp <= $ur->getUdFromDate()->getTimestamp() &&
                            $end_timestamp >= $ur->getUdToDate()->getTimestamp()) {
                        $array_no_available[$room->getRoomId()] = $room->getRoomId();
                        $flag = 1;
                    }

                    if ($start_timestamp >= $ur->getUdFromDate()->getTimestamp() &&
                            $start_timestamp <= $ur->getUdToDate()->getTimestamp() &&
                            $end_timestamp >= $ur->getUdToDate()->getTimestamp()) {
                        $array_no_available[$room->getRoomId()] = $room->getRoomId();
                        $flag = 1;
                    }

                    if ($start_timestamp <= $ur->getUdFromDate()->getTimestamp() &&
                            $end_timestamp <= $ur->getUdToDate()->getTimestamp() &&
                            $end_timestamp >= $ur->getUdFromDate()->getTimestamp()) {

                        $array_no_available[$room->getRoomId()] = $room->getRoomId();
                        $flag = 1;
                    }

                    if ($start_timestamp >= $ur->getUdFromDate()->getTimestamp() &&
                            $end_timestamp <= $ur->getUdToDate()->getTimestamp()) {
                        $array_no_available[$room->getRoomId()] = $room->getRoomId();
                        $flag = 1;
                    }
                    $temp = array();
                    foreach ($unavailable_days as $unav_date) {
                        for ($s = 0; $s < count($array_dates) - 1; $s++) {
                            if ($array_dates[$s] == $unav_date) {
                                array_push($temp, $s + 1);
                            }
                        }
                    }
                    if ($flag == 1) {
                        $temp_local = array_merge($temp_local, $temp);
                        $no_available_days_ready[$room->getRoomId()] = $temp_local;
                    }
                }
            }
            foreach ($reservations as $reservation) {

                if ($reservation->getOwnResSelectedRoomId() == $room->getRoomId()) {
                    $reservationStartDate = $reservation->getOwnResReservationFromDate()->getTimestamp();
                    $reservationEndDate = $reservation->getOwnResReservationToDate()->getTimestamp();
                    $date = new \DateTime();
                    $date->setTimestamp(strtotime("-1 day", $reservationEndDate));
                    $reservationEndDate = $date->getTimestamp();

                    if ($start_timestamp <= $reservationStartDate &&
                            $end_timestamp >= $reservationEndDate && $reservation->getOwnResStatus() == ownershipReservation::STATUS_RESERVED) {

                        $array_no_available[$room->getRoomId()] = $room->getRoomId();
                    }

                    if ($start_timestamp >= $reservationStartDate &&
                            $start_timestamp <= $reservationEndDate &&
                            $end_timestamp >= $reservationEndDate && $reservation->getOwnResStatus() == ownershipReservation::STATUS_RESERVED) {

                        $array_no_available[$room->getRoomId()] = $room->getRoomId();
                    }

                    if ($start_timestamp <= $reservationStartDate &&
                            $end_timestamp <= $reservationEndDate &&
                            $end_timestamp >= $reservationStartDate && $reservation->getOwnResStatus() == ownershipReservation::STATUS_RESERVED) {

                        $array_no_available[$room->getRoomId()] = $room->getRoomId();
                    }

                    if ($start_timestamp >= $reservationStartDate &&
                            $end_timestamp <= $reservationEndDate && $reservation->getOwnResStatus() == ownershipReservation::STATUS_RESERVED) {

                        $array_no_available[$room->getRoomId()] = $room->getRoomId();
                    }

                    $array_numbers_check = array();
                    $cont_numbers = 1;
                    foreach ($array_dates as $date) {

                        if ($date >= $reservationStartDate && $date <= $reservationEndDate && $reservation->getOwnResStatus() == ownershipReservation::STATUS_RESERVED) {
                            array_push($array_numbers_check, $cont_numbers);
                        }
                        $cont_numbers++;
                    }
                    array_push($no_available_days, array(
                        $room->getRoomId() => $room->getRoomId(),
                        'check' => $array_numbers_check
                    ));
                }
            }
            $total_price_room = 0;
            $prices_dates_temp = array();
            $x = 1;
            /* if ($request->getMethod() != 'POST') {
              //$x = 2;
              } */
            $destination_id = ($ownership->getOwnDestination() != null) ? $ownership->getOwnDestination()->getDesId() : null;
            $seasons = $em->getRepository("mycpBundle:season")->getSeasons($from, $to, $destination_id);
            for ($a = 0; $a < count($array_dates) - $x; $a++) {

                $season_type = $service_time->seasonTypeByDate($seasons, $array_dates[$a]);
                $roomPrice = $room->getPriceBySeasonType($season_type);

                $total_price_room += $roomPrice;
                array_push($prices_dates_temp, $roomPrice);
            }
            array_push($array_prices, $total_price_room);
            array_push($prices_dates, $prices_dates_temp);
        }


        foreach ($no_available_days as $item) {
            $keys = array_keys($item);
            if (!isset($no_available_days_ready[$item[$keys[0]]]))
                $no_available_days_ready[$item[$keys[0]]] = array();
            $no_available_days_ready[$item[$keys[0]]] = array_merge($no_available_days_ready[$item[$keys[0]]], $item['check']);
        }
        //var_dump($no_available_days);
        $array_dates_keys = array();
        $count = 1;
        foreach ($array_dates as $date) {
            $array_dates_keys[$count] = array('day_number' => date('d', $date), 'day_name' => date('D', $date));
            $count++;
        }
        $flag_room = 0;
        $price_subtotal = 0;
        $do_operation = true;
        $available_rooms = array();
        $avail_array_prices = array();
        foreach ($rooms as $room_2) {

            foreach ($array_no_available as $no_avail) {
                if ($room_2->getRoomId() == $no_avail) {
                    $do_operation = false;
                }
            }
            if ($do_operation == true) {
                $price_subtotal+=$array_prices[$flag_room];
                array_push($available_rooms, $room_2->getRoomId());
                array_push($avail_array_prices, $array_prices[$flag_room]);
            }
            $do_operation = true;
            $flag_room++;
        }
        //$no_available_days_ready[351]=array(11,12,13,14,15,21,22);
        return $this->render('FrontEndBundle:ownership:ownershipReservationCalendar.html.twig', array(
                    'array_dates' => $array_dates_keys,
                    'rooms' => $rooms,
                    'array_prices' => $array_prices,
                    'ownership' => $ownership,
                    'no_available_days' => $no_available_days_ready,
                    'prices_dates' => $prices_dates,
                    'reservations' => $array_no_available,
        ));
    }

    public function own_details_directAction($own_code) {
        // There are so many browserconfig.xml requests from stupid IE6 that we check for it
        // here to avoid Exceptions in the log files
        if (strpos($own_code, 'browserconfig.xml') !== false) {
            return new Response('not found', 404);
        }

        $em = $this->getDoctrine()->getManager();
        $ownership = $em->getRepository('mycpBundle:ownership')->findOneBy(array('own_mcp_code' => $own_code));
        if ($ownership && $ownership->getOwnStatus()->getStatusId() == \MyCp\mycpBundle\Entity\ownershipStatus::STATUS_ACTIVE) {
            $own_name = Utils::urlNormalize($ownership->getOwnName());
            return $this->redirect($this->generateUrl('frontend_details_ownership', array('own_name' => $own_name)));
        }
        else
            throw $this->createNotFoundException();
    }

    public function simpleAction($mycp_code) {
        // There are so many browserconfig.xml requests from stupid IE6 that we check for it
        // here to avoid Exceptions in the log files
        if (strpos($mycp_code, 'browserconfig.xml') !== false) {
            return new Response('not found', 404);
        }

        $em = $this->getDoctrine()->getManager();
        $locale = $this->get('translator')->getLocale();
        //$own_name = Utils::urlNormalize($ownership->getOwnName());
        $ownership_array = $em->getRepository('mycpBundle:ownership')->get_details_by_code($mycp_code, strtoupper($locale), true);
        if ($ownership_array && count($ownership_array) > 0) {
            $rooms = $em->getRepository('mycpBundle:room')->findBy(array('room_ownership' => $ownership_array['own_id'], 'room_active' => true));
            $own_photos = $em->getRepository('mycpBundle:ownership')->getPhotosAndDescription($ownership_array['own_id'], $locale);

            $real_category = "";
            if ($ownership_array['category'] == 'Económica')
                $real_category = 'economy';
            else if ($ownership_array['category'] == 'Rango medio')
                $real_category = 'mid_range';
            else if ($ownership_array['category'] == 'Premium')
                $real_category = 'premium';

            $langs_array = array();
            if ($ownership_array['english'] == 1)
                $langs_array[] = $this->get('translator')->trans("LANG_ENGLISH");

            if ($ownership_array['french'] == 1)
                $langs_array[] = $this->get('translator')->trans("LANG_FRENCH");

            if ($ownership_array['german'] == 1)
                $langs_array[] = $this->get('translator')->trans("LANG_GERMAN");

            if ($ownership_array['italian'] == 1)
                $langs_array[] = $this->get('translator')->trans("LANG_ITALIAN");

            $languages = $this->get('translator')->trans('LANG_SPANISH');

            foreach ($langs_array as $lang)
                $languages .= ", " . $lang;
            return $this->render('FrontEndBundle:ownership:ownershipSimpleDetails.html.twig', array(
                        'ownership' => $ownership_array,
                        'description' => $ownership_array['description'],
                        'brief_description' => $ownership_array['brief_description'],
                        'rooms' => $rooms,
                        'gallery_photos' => $own_photos,
                        'locale' => $locale,
                        'real_category' => $real_category,
                        'languages' => $languages,
                        'keywords' => $ownership_array['keywords']
            ));
        }
        else
            throw $this->createNotFoundException();
    }

    public function detailsAction($own_name, Request $request) {

        $em = $this->getDoctrine()->getManager();
        $user_ids = $em->getRepository('mycpBundle:user')->user_ids($this);
        $locale = $this->get('translator')->getLocale();


        $own_name = str_replace('-', ' ', $own_name);
        $own_name = str_replace('  ', '-', $own_name);
        //$own_name = str_replace("nn", "ñ", $own_name);

        $ownership_array = $em->getRepository('mycpBundle:ownership')->get_details($own_name, $locale, $user_ids["user_id"], $user_ids["session_id"]);
        if ($ownership_array == null) {
            throw $this->createNotFoundException();
        }

        $langs_array = array();
        if ($ownership_array['english'] == 1)
            $langs_array[] = $this->get('translator')->trans("LANG_ENGLISH");

        if ($ownership_array['french'] == 1)
            $langs_array[] = $this->get('translator')->trans("LANG_FRENCH");

        if ($ownership_array['german'] == 1)
            $langs_array[] = $this->get('translator')->trans("LANG_GERMAN");

        if ($ownership_array['italian'] == 1)
            $langs_array[] = $this->get('translator')->trans("LANG_ITALIAN");

        $languages = $this->get('translator')->trans('LANG_SPANISH');

        foreach ($langs_array as $lang)
            $languages .= ", " . $lang;

        $owner_id = $ownership_array['own_id'];
        $general_reservations = $em->getRepository('mycpBundle:generalReservation')->findBy(array('gen_res_own_id' => $owner_id));
        $reservations = array();
        foreach ($general_reservations as $gen_res) {
            $own_reservations = $em->getRepository('mycpBundle:ownershipReservation')->findBy(array('own_res_gen_res_id' => $gen_res->getGenResId()));
            foreach ($own_reservations as $own_res) {
                array_push($reservations, $own_res);
            }
        }

        $similar_houses = $em->getRepository('mycpBundle:ownership')->getByCategory($ownership_array['category'], null, $owner_id, $user_ids["user_id"], $user_ids["session_id"]);
        $total_similar_houses = count($similar_houses);

        $paginator = $this->get('ideup.simple_paginator');
        $items_per_page = 5;
        $paginator->setItemsPerPage($items_per_page);
        $total_comments = $em->getRepository('mycpBundle:comment')->getByOwnership($owner_id);
        $comments = $paginator->paginate($total_comments)->getResult();
        $page = 1;
        if (isset($_GET['page']))
            $page = $_GET['page'];

        $rooms = $em->getRepository('mycpBundle:room')->findBy(array('room_ownership' => $owner_id, 'room_active' => true));
        $friends = array();
        $own_photos = $em->getRepository('mycpBundle:ownership')->getPhotosAndDescription($owner_id, $locale);

        $session = $this->get('session');
        $post = $request->request->getIterator()->getArrayCopy();
        $start_date = (isset($post['top_reservation_filter_date_from'])) ? ($post['top_reservation_filter_date_from']) : (($session->get('search_arrival_date') != null) ? $session->get('search_arrival_date') : 'now');
        $end_date = (isset($post['top_reservation_filter_date_to'])) ? ($post['top_reservation_filter_date_to']) : (($session->get('search_departure_date') != null) ? $session->get('search_departure_date') : '+2 day');

        $start_timestamp = strtotime($start_date);
        $end_timestamp = strtotime($end_date);


        if (isset($post['top_reservation_filter_date_from'])) {
            $post['reservation_filter_date_from'] = $post['top_reservation_filter_date_from'];
            $post['reservation_filter_date_to'] = $post['top_reservation_filter_date_to'];
        }

        if ($this->getRequest()->getMethod() == 'POST') {
            if (isset($post['reservation_filter_date_from']) && isset($post['reservation_filter_date_to'])) {
                $reservation_filter_date_from = $post['reservation_filter_date_from'];
                $reservation_filter_date_from = explode('/', $reservation_filter_date_from);
                $start_timestamp = mktime(0, 0, 0, $reservation_filter_date_from[1], $reservation_filter_date_from[0], $reservation_filter_date_from[2]);

                $reservation_filter_date_to = $post['reservation_filter_date_to'];
                $reservation_filter_date_to = explode('/', $reservation_filter_date_to);
                $end_timestamp = mktime(0, 0, 0, $reservation_filter_date_to[1], $reservation_filter_date_to[0], $reservation_filter_date_to[2]);
            }
        } else {

        }

        $service_time = $this->get('Time');
        $array_dates = $service_time->datesBetween($start_timestamp, $end_timestamp);

        $array_no_available = array();
        $no_available_days = array();

        $service_time = $this->get('time');
        $array_prices = array();
        $prices_dates = array();

        foreach ($rooms as $room) {
            foreach ($reservations as $reservation) {

                if ($reservation->getOwnResSelectedRoomId() == $room->getRoomId()) {

                    if ($start_timestamp <= $reservation->getOwnResReservationFromDate()->getTimestamp() &&
                            $end_timestamp >= $reservation->getOwnResReservationToDate()->getTimestamp() && $reservation->getOwnResStatus() == ownershipReservation::STATUS_RESERVED) {

                        $array_no_available[$room->getRoomId()] = $room->getRoomId();
                    }

                    if ($start_timestamp >= $reservation->getOwnResReservationFromDate()->getTimestamp() &&
                            $start_timestamp <= $reservation->getOwnResReservationToDate()->getTimestamp() &&
                            $end_timestamp >= $reservation->getOwnResReservationToDate()->getTimestamp() && $reservation->getOwnResStatus() == ownershipReservation::STATUS_RESERVED) {

                        $array_no_available[$room->getRoomId()] = $room->getRoomId();
                    }

                    if ($start_timestamp <= $reservation->getOwnResReservationFromDate()->getTimestamp() &&
                            $end_timestamp <= $reservation->getOwnResReservationToDate()->getTimestamp() &&
                            $end_timestamp >= $reservation->getOwnResReservationFromDate()->getTimestamp() && $reservation->getOwnResStatus() == ownershipReservation::STATUS_RESERVED) {

                        $array_no_available[$room->getRoomId()] = $room->getRoomId();
                    }

                    if ($start_timestamp >= $reservation->getOwnResReservationFromDate()->getTimestamp() &&
                            $end_timestamp <= $reservation->getOwnResReservationToDate()->getTimestamp() && $reservation->getOwnResStatus() == ownershipReservation::STATUS_RESERVED) {

                        $array_no_available[$room->getRoomId()] = $room->getRoomId();
                    }

                    $array_numbers_check = array();
                    $cont_numbers = 1;
                    foreach ($array_dates as $date) {

                        if ($date >= $reservation->getOwnResReservationFromDate()->getTimestamp() && $date <= $reservation->getOwnResReservationToDate()->getTimestamp() && $reservation->getOwnResStatus() == ownershipReservation::STATUS_RESERVED) {
                            array_push($array_numbers_check, $cont_numbers);
                        }
                        $cont_numbers++;
                    }
                    array_push($no_available_days, array(
                        $room->getRoomId() => $room->getRoomId(),
                        'check' => $array_numbers_check
                    ));
                }
            }
            $total_price_room = 0;
            $prices_dates_temp = array();
            $x = 1;
            /* if ($request->getMethod() != 'POST') {
              //$x = 2;
              } */
            $seasons = $em->getRepository("mycpBundle:season")->getSeasons($start_date, $end_date, $ownership_array['des_id']);
            for ($a = 0; $a < count($array_dates) - $x; $a++) {

                $season_type = $service_time->seasonTypeByDate($seasons, $array_dates[$a]);
                $roomPrice = $room->getPriceBySeasonType($season_type);
                $total_price_room += $roomPrice;
                array_push($prices_dates_temp, $roomPrice);
            }
            array_push($array_prices, $total_price_room);
            array_push($prices_dates, $prices_dates_temp);
        }
        $no_available_days_ready = array();
        foreach ($no_available_days as $item) {
            $keys = array_keys($item);
            if (!isset($no_available_days_ready[$item[$keys[0]]]))
                $no_available_days_ready[$item[$keys[0]]] = array();
            $no_available_days_ready[$item[$keys[0]]] = array_merge($no_available_days_ready[$item[$keys[0]]], $item['check']);
        }

        $array_dates_keys = array();
        $count = 1;
        foreach ($array_dates as $date) {
            $array_dates_keys[$count] = array('day_number' => date('d', $date), 'day_name' => date('D', $date));
            $count++;
        }
        if ($this->getRequest()->getMethod() != 'POST') {
            // array_pop($array_dates_keys);
        }

        $flag_room = 0;
        $price_subtotal = 0;
        $do_operation = true;
        $available_rooms = array();
        $avail_array_prices = array();
        foreach ($rooms as $room_2) {
            foreach ($array_no_available as $no_avail) {
                if ($room_2->getRoomId() == $no_avail) {
                    $do_operation = false;
                }
            }
            if ($do_operation == true) {
                $price_subtotal+=$array_prices[$flag_room];
                array_push($available_rooms, $room_2->getRoomId());
                array_push($avail_array_prices, $array_prices[$flag_room]);
            }
            $do_operation = true;
            $flag_room++;
        }
        //exit();

        /* YANET */
        $em->getRepository('mycpBundle:userHistory')->insert(true, $owner_id, $user_ids);

        $real_category = "";
        if ($ownership_array['category'] == 'Económica')
            $real_category = 'economy';
        else if ($ownership_array['category'] == 'Rango medio')
            $real_category = 'mid_range';
        else if ($ownership_array['category'] == 'Premium')
            $real_category = 'premium';
        return $this->render('FrontEndBundle:ownership:ownershipDetails.html.twig', array(
                    'avail_array_prices' => $avail_array_prices,
                    'available_rooms' => $available_rooms,
                    'price_subtotal' => $price_subtotal,
                    'avail_array_prices' => $avail_array_prices,
                    'array_prices' => $array_prices,
                    'prices_dates' => $prices_dates,
                    'ownership' => $ownership_array,
                    'description' => $ownership_array['description'],
                    'brief_description' => $ownership_array['brief_description'],
                    'similar_houses' => array_slice($similar_houses, 0, 5),
                    'total_similar_houses' => $total_similar_houses,
                    'comments' => $comments,
                    'friends' => $friends,
                    'show_comments_and_friends' => count($total_comments) + count($friends),
                    'rooms' => $rooms,
                    'gallery_photos' => $own_photos,
                    'is_in_favorite' => $ownership_array['is_in_favorites'],
                    'array_dates' => $array_dates_keys,
                    'post' => $post,
                    'reservations' => $array_no_available,
                    'no_available_days' => $no_available_days_ready,
                    'comments_items_per_page' => $items_per_page,
                    'comments_total_items' => $paginator->getTotalItems(),
                    'comments_current_page' => $page,
                    'can_comment' => ($this->getUser() != null), //$em->getRepository("mycpBundle:comment")->canComment($user_ids["user_id"], $owner_id),
                    'can_public_comment' => $em->getRepository("mycpBundle:comment")->canPublicComment($user_ids["user_id"], $owner_id),
                    'locale' => $locale,
                    'real_category' => $real_category,
                    'languages' => $languages,
                    'keywords' => $ownership_array['keywords']
        ));
    }

    public function last_added_listAction() {
        $em = $this->getDoctrine()->getManager();
        $user_ids = $em->getRepository('mycpBundle:user')->user_ids($this);

        $paginator = $this->get('ideup.simple_paginator');
        $items_per_page = 15;
        $paginator->setItemsPerPage($items_per_page);
        $last_added_own_list = $paginator->paginate($em->getRepository('mycpBundle:ownership')->lastAdded(null, $user_ids["user_id"], $user_ids["session_id"]))->getResult();
        $page = 1;
        if (isset($_GET['page']))
            $page = $_GET['page'];

        return $this->render('FrontEndBundle:ownership:lastAddedOwnership.html.twig', array(
                    'list' => $last_added_own_list,
                    'list_preffix' => 'last_added',
                    'items_per_page' => $items_per_page,
                    'total_items' => $paginator->getTotalItems(),
                    'current_page' => $page
        ));
    }

    public function categoryAction($category) {
        $em = $this->getDoctrine()->getManager();
        $user_ids = $em->getRepository('mycpBundle:user')->user_ids($this);

        $real_category = $category;

        if ($category == 'economy')
            $real_category = 'Económica';
        else if ($category == 'mid_range')
            $real_category = 'Rango medio';
        else if ($category == 'premium')
            $real_category = 'Premium';
        else
            return $this->redirect($this->generateUrl('frontend_welcome'));



        $paginator = $this->get('ideup.simple_paginator');
        $items_per_page = 15;
        $paginator->setItemsPerPage($items_per_page);
        $list = $paginator->paginate($em->getRepository('mycpBundle:ownership')->getByCategory($real_category, null, null, $user_ids['user_id'], $user_ids['session_id']))->getResult();
        $page = 1;
        if (isset($_GET['page']))
            $page = $_GET['page'];


        return $this->render('FrontEndBundle:ownership:categoryListOwnership.html.twig', array(
                    'category' => $category,
                    'title' => str_replace(' ', '-', $category),
                    'list' => $list,
                    'list_preffix' => 'category',
                    'items_per_page' => $items_per_page,
                    'total_items' => $paginator->getTotalItems(),
                    'current_page' => $page
        ));
    }

    public function searchAction(Request $request, $text = null, $arriving_date = null, $departure_date = null, $guests = 1, $rooms = 1) {

        $em = $this->getDoctrine()->getManager();
        $session = $this->getRequest()->getSession();

        if ($session->get('search_order') == null || $session->get('search_order') == '')
            $session->set('search_order', 'PRICE_LOW_HIGH');

        $rooms = ($rooms == "undefined") ? 1 : $rooms;


        $search_text = ($text != null && $text != '' && $text != $this->get('translator')->trans('PLACE_WATERMARK')) ? Utils::getTextFromNormalized($text) : null;
        $search_guests = ($guests != null && $guests != '' && $guests != $this->get('translator')->trans('GUEST_WATERMARK')) ? $guests : "1";
        $search_rooms = ($rooms != null && $rooms != '' && $rooms != $this->get('translator')->trans('ROOM_WATERMARK')) ? $rooms : "1";
        $arrival = ($request->get('arrival') != null && $request->get('arrival') != "" && $request->get('arrival') != "null") ? $request->get('arrival') : $session->get('search_arrival_date');
        $departure = ($request->get('departure') != null && $request->get('departure') != "" && $request->get('departure') != "null") ? $request->get('departure') : $session->get('search_departure_date');

        $today = new \DateTime();
        if ($arrival == null)
            $arrival = $today->format('d-m-Y');
        if ($departure == null)
            $departure = date_add($today, date_interval_create_from_date_string("2 days"))->format('d-m-Y');

        $check_filters = $session->get("filter_array");
        $room_filter = $session->get("filter_room");

        $session->set("filter_array", $check_filters);
        $session->set("filter_room", $room_filter);

        $list = $em->getRepository('mycpBundle:ownership')->search($this, $search_text, $arrival, $departure, $search_guests, $search_rooms, $session->get('search_order'), $room_filter, $check_filters);

        // <editor-fold defaultstate="collapsed" desc="Inside code was inserted into search method in ownershipRepository">
        //Marlon
        /* $owns_list = array();

          foreach($list as $tmp){
          //die(var_dump($tmp));
          $own = new ownership();
          $own = $tmp;
          $rooms = $em->getRepository('mycpBundle:room')->findBy(array('room_ownership' => $own['own_id']));

          $real = count($rooms);

          foreach( $rooms as $room){
          $own_reservations = $em->getRepository('mycpBundle:ownershipReservation')->findBy(
          array(
          'own_res_selected_room_id' => $room->getRoomId()
          )
          );
          foreach( $own_reservations as $res ){
          $from = $res->getOwnResReservationFromDate();
          $end = $res->getOwnResReservationToDate();

          $arr = new \DateTime($arrival);
          $dep = new \DateTime($departure);

          //die(var_dump($from, $end, $arr, $dep));

          if ( ! ( $dep < $from || $arr > $end)){
          $real--;
          break;
          }

          }
          }

          if ( $real > 0 )
          $owns_list[] = $own;

          }

          $list = $owns_list; */
        // End Marlon
        // </editor-fold>

        $paginator = $this->get('ideup.simple_paginator');
        $items_per_page = 15;
        $paginator->setItemsPerPage($items_per_page);
        $result_list = $paginator->paginate($list)->getResult();
        $page = 1;
        if (isset($_GET['page']))
            $page = $_GET['page'];


        $session->set('search_text', $search_text);
        $session->set('search_arrival_date', $arrival);
        $session->set('search_departure_date', $departure);
        $session->set('search_guests', $search_guests);
        $session->set('search_rooms', $search_rooms);

        $own_ids = "0";
        foreach ($list as $own)
            $own_ids .= "," . $own['own_id'];
        $session->set('own_ids', $own_ids);

        if ($session->get('search_view_results') == null || $session->get('search_view_results') == '')
            $session->set('search_view_results', 'LIST');

        $categories_own_list = $em->getRepository('mycpBundle:ownership')->getOwnsCategories();
        $types_own_list = $em->getRepository('mycpBundle:ownership')->getOwnsTypes();
        $prices_own_list = $em->getRepository('mycpBundle:ownership')->getOwnsPrices();
        $statistics_own_list = $em->getRepository('mycpBundle:ownership')->getSearchStatistics();

        if ($check_filters != null)
            return $this->render('FrontEndBundle:ownership:searchOwnership.html.twig', array(
                        'search_text' => $search_text,
                        'search_guests' => $search_guests,
                        'search_arrival_date' => $arrival,
                        'search_departure_date' => $departure,
                        'owns_categories' => $categories_own_list,
                        'owns_types' => $types_own_list,
                        'owns_prices' => $prices_own_list,
                        'order' => $session->get('search_order'),
                        'view_results' => $session->get('search_view_results'),
                        'own_statistics' => $statistics_own_list,
                        'locale' => $this->get('translator')->getLocale(),
                        'autocomplete_text_list' => $em->getRepository('mycpBundle:ownership')->autocomplete_text_list(),
                        'list' => $result_list,
                        'items_per_page' => $items_per_page,
                        'total_items' => $paginator->getTotalItems(),
                        'current_page' => $page,
                        'check_filters' => $check_filters,
                        'show_paginator' => true
            ));
        else
            return $this->render('FrontEndBundle:ownership:searchOwnership.html.twig', array(
                        'search_text' => $search_text,
                        'search_guests' => $search_guests,
                        'search_arrival_date' => $arrival,
                        'search_departure_date' => $departure,
                        'owns_categories' => $categories_own_list,
                        'owns_types' => $types_own_list,
                        'owns_prices' => $prices_own_list,
                        'order' => $session->get('search_order'),
                        'view_results' => $session->get('search_view_results'),
                        'own_statistics' => $statistics_own_list,
                        'locale' => $this->get('translator')->getLocale(),
                        'autocomplete_text_list' => $em->getRepository('mycpBundle:ownership')->autocomplete_text_list(),
                        'list' => $result_list,
                        'items_per_page' => $items_per_page,
                        'total_items' => $paginator->getTotalItems(),
                        'current_page' => $page,
                        'show_paginator' => true
            ));
    }

    public function search_order_resultsAction() {

        $request = $this->getRequest();
        $session = $request->getSession();
        $em = $this->getDoctrine()->getManager();

        if ($session->get('search_view_results') == null)
            $session->set('search_view_results', 'LIST');

        if ($request->getMethod() == 'POST') {
            $order = $request->request->get('order');
            $session->set('search_order', $order);

            $check_filters = $session->get("filter_array");
            $room_filter = $session->get("filter_room");

            $session->set("filter_array", $check_filters);
            $session->set("filter_room", $room_filter);

            $list = $em->getRepository('mycpBundle:ownership')->search($this, $session->get('search_text'), $session->get('search_arrival_date'), $session->get('search_departure_date'), $session->get('search_guests'), $session->get('search_rooms'), $session->get('search_order'), $room_filter, $check_filters);
            $paginator = $this->get('ideup.simple_paginator');
            $items_per_page = 15;
            $paginator->setItemsPerPage($items_per_page);
            $result_list = $paginator->paginate($list)->getResult();
            $page = 1;
            if (isset($_GET['page']))
                $page = $_GET['page'];

            $own_ids = "0";
            foreach ($list as $own)
                $own_ids .= "," . $own['own_id'];
            $session->set('own_ids', $own_ids);

            if ($session->get('search_view_results') != null && $session->get('search_view_results') == 'LIST') {
                $response = $this->renderView('FrontEndBundle:ownership:searchListOwnership.html.twig', array(
                    'list' => $result_list,
                    'items_per_page' => $items_per_page,
                    'total_items' => $paginator->getTotalItems(),
                    'current_page' => $page,
                    'list_preffix' => 'search',
                    'show_paginator' => true
                ));
            } elseif ($session->get('search_view_results') != null && $session->get('search_view_results') == 'PHOTOS') {
                $response = $this->renderView('FrontEndBundle:ownership:searchMosaicOwnership.html.twig', array(
                    'list' => $result_list,
                    'items_per_page' => $items_per_page,
                    'total_items' => $paginator->getTotalItems(),
                    'current_page' => $page,
                    'list_preffix' => 'search',
                    'show_paginator' => true
                ));
            } elseif ($session->get('search_view_results') != null && $session->get('search_view_results') == 'MAP') {
                $response = $this->renderView('FrontEndBundle:ownership:searchMapOwnership.html.twig', array(
                    'list' => $result_list,
                    'items_per_page' => $items_per_page,
                    'total_items' => $paginator->getTotalItems(),
                    'current_page' => $page,
                    'list_preffix' => 'search',
                    'show_paginator' => true
                ));
            } else { // guarantee that a response is always returned
                $response = $this->renderView('FrontEndBundle:ownership:searchListOwnership.html.twig', array(
                    'list' => $result_list,
                    'items_per_page' => $items_per_page,
                    'total_items' => $paginator->getTotalItems(),
                    'current_page' => $page,
                    'list_preffix' => 'search',
                    'show_paginator' => true
                ));
            }

            return new Response($response, 200);
        }

        throw new MethodNotAllowedHttpException(array('POST'));
    }

    public function search_change_view_resultsAction() {
        $request = $this->getRequest();
        $session = $request->getSession();
        $em = $this->getDoctrine()->getManager();

        $view = $request->request->get('view');
        $session->set('search_view_results', $view);

        $check_filters = $session->get("filter_array");
        $room_filter = $session->get("filter_room");

        $session->set("filter_array", $check_filters);
        $session->set("filter_room", $room_filter);

        $results_list_without_paginate = $em->getRepository('mycpBundle:ownership')->search($this, $session->get('search_text'), $session->get('search_arrival_date'), $session->get('search_departure_date'), $session->get('search_guests'), $session->get('search_rooms'), $session->get('search_order'), $room_filter, $check_filters);

        $own_ids = "0";
        foreach ($results_list_without_paginate as $own)
            $own_ids .= "," . $own['own_id'];
        $session->set('own_ids', $own_ids);

        $paginator = $this->get('ideup.simple_paginator');
        $items_per_page = 15;
        $paginator->setItemsPerPage($items_per_page);
        $results_list = $paginator->paginate($results_list_without_paginate)->getResult();
        $page = 1;
        if (isset($_GET['page']))
            $page = $_GET['page'];


        if (($session->get('search_view_results') != null && $session->get('search_view_results') == 'LIST') || $session->get('search_view_results') == null) {
            $response = $this->renderView('FrontEndBundle:ownership:searchListOwnership.html.twig', array(
                'list' => $results_list,
                'items_per_page' => $items_per_page,
                'total_items' => $paginator->getTotalItems(),
                'current_page' => $page,
                'list_preffix' => 'search',
                'show_paginator' => true
            ));
        } else if ($session->get('search_view_results') != null && $session->get('search_view_results') == 'PHOTOS')
            $response = $this->renderView('FrontEndBundle:ownership:searchMosaicOwnership.html.twig', array(
                'list' => $results_list,
                'items_per_page' => $items_per_page,
                'total_items' => $paginator->getTotalItems(),
                'current_page' => $page,
                'list_preffix' => 'search',
                'show_paginator' => true
            ));
        else if ($session->get('search_view_results') != null && $session->get('search_view_results') == 'MAP')
            $response = $this->renderView('FrontEndBundle:ownership:searchMapOwnership.html.twig', array(
                'list' => $results_list,
                'items_per_page' => $items_per_page,
                'total_items' => $paginator->getTotalItems(),
                'current_page' => $page,
                'list_preffix' => 'search',
                'show_paginator' => true
            ));

        return new Response($response, 200);
    }

    public function researchAction() {
        $request = $this->getRequest();
        $session = $request->getSession();
        $em = $this->getDoctrine()->getManager();

        if ($session->get('search_order') == null || $session->get('search_order') == '')
            $session->set('search_order', 'BEST_VALUED');

        if ($session->get('search_view_results') == null || $session->get('search_view_results') == '')
            $session->set('search_view_results', 'LIST');


        if ($request->getMethod() == 'POST') {
            $guests = $request->request->get('guests');
            $rooms = $request->request->get('rooms');
            $arriving_date = $request->request->get('arrival');
            $departure_date = $request->request->get('departure');

            $search_guests = ($guests != null && $guests != '' && $guests != $this->get('translator')->trans('GUEST_WATERMARK')) ? $guests : "1";
            $search_rooms = ($rooms != null && $rooms != '' && $rooms != $this->get('translator')->trans('ROOM_WATERMARK')) ? $rooms : "1";
            $search_arrival_date = ($arriving_date != null && $arriving_date != '' && $arriving_date != $this->get('translator')->trans('ARRIVAL_WATERMARK')) ? $arriving_date : null;
            $search_departure_date = ($departure_date != null && $departure_date != '' && $departure_date != $this->get('translator')->trans('DEPARTURE_WATERMARK')) ? $departure_date : null;
            $text = $request->request->get('text');
            $text = ($text == "") ? "null" : $text;

            $session->set('search_text', $text);
            $session->set('search_arrival_date', $search_arrival_date);
            $session->set('search_departure_date', $search_departure_date);
            $session->set('search_guests', $search_guests);
            $session->set('search_rooms', $search_rooms);

            $check_filters = $session->get("filter_array");
            $room_filter = $session->get("filter_room");

            $session->set("filter_array", $check_filters);
            $session->set("filter_room", $room_filter);

            $results_list = $em->getRepository('mycpBundle:ownership')->search($this, $session->get('search_text'), $session->get('search_arrival_date'), $session->get('search_departure_date'), $session->get('search_guests'), $session->get('search_rooms'), $session->get('search_order'), $room_filter, $check_filters);

            $own_ids = "0";
            foreach ($results_list as $own)
                $own_ids .= "," . $own['own_id'];
            $session->set('own_ids', $own_ids);

            $paginator = $this->get('ideup.simple_paginator');
            $items_per_page = 15;
            $paginator->setItemsPerPage($items_per_page);
            $list = $paginator->paginate($results_list)->getResult();
            $page = 1;
            if (isset($_GET['page']))
                $page = $_GET['page'];

            if ($session->get('search_view_results') != null && $session->get('search_view_results') == 'LIST')
                $response = $this->renderView('FrontEndBundle:ownership:searchListOwnership.html.twig', array(
                    'list' => $list,
                    'items_per_page' => $items_per_page,
                    'total_items' => $paginator->getTotalItems(),
                    'current_page' => $page,
                    'list_preffix' => 'search',
                    'show_paginator' => true
                ));
            else if ($session->get('search_view_results') != null && $session->get('search_view_results') == 'PHOTOS')
                $response = $this->renderView('FrontEndBundle:ownership:searchMosaicOwnership.html.twig', array(
                    'list' => $list,
                    'items_per_page' => $items_per_page,
                    'total_items' => $paginator->getTotalItems(),
                    'current_page' => $page,
                    'list_preffix' => 'search',
                    'show_paginator' => true
                ));
            else if ($session->get('search_view_results') != null && $session->get('search_view_results') == 'MAP')
                $response = $this->renderView('FrontEndBundle:ownership:searchMapOwnership.html.twig', array(
                    'list' => $list,
                    'items_per_page' => $items_per_page,
                    'total_items' => $paginator->getTotalItems(),
                    'current_page' => $page,
                    'list_preffix' => 'search',
                    'show_paginator' => true
                ));

            return new Response($response, 200);
        }
    }

    public function filterAction() {
        $request = $this->getRequest();
        $session = $request->getSession();
        $em = $this->getDoctrine()->getManager();

        if ($session->get('search_view_results') == null || $session->get('search_view_results') == '')
            $session->set('search_view_results', 'LIST');

        $check_filters = array();
        $check_filters['own_reservation_type'] = $request->request->get('own_reservation_type');
        $check_filters['own_category'] = $request->request->get('own_category');
        $check_filters['own_type'] = $request->request->get('own_type');
        $check_filters['own_price'] = $request->request->get('own_price');
        $check_filters['own_price_from'] = $request->request->get('own_price_from');
        $check_filters['own_price_to'] = $request->request->get('own_price_to');
        $check_filters['own_rooms_number'] = $request->request->get('own_rooms_number');
        $check_filters['room_type'] = $request->request->get('room_type');
        $check_filters['own_beds_total'] = $request->request->get('own_beds_total');
        $check_filters['room_bathroom'] = $request->request->get('room_bathroom');
        $check_filters['room_windows_total'] = $request->request->get('room_windows_total');
        $check_filters['room_climatization'] = $request->request->get('room_climatization');
        $check_filters['room_audiovisuals'] = ($request->request->get('room_audiovisuals') == 'true' || $request->request->get('room_audiovisuals') == '1') ? true : false;
        $check_filters['room_kids'] = ($request->request->get('room_kids') == 'true' || $request->request->get('room_kids') == '1') ? true : false;
        $check_filters['room_smoker'] = ($request->request->get('room_smoker') == 'true' || $request->request->get('room_smoker') == '1') ? true : false;
        $check_filters['room_safe'] = ($request->request->get('room_safe') == 'true' || $request->request->get('room_safe') == '1') ? true : false;
        $check_filters['room_balcony'] = ($request->request->get('room_balcony') == 'true' || $request->request->get('room_balcony') == '1') ? true : false;
        $check_filters['room_terraza'] = ($request->request->get('room_terraza') == 'true' || $request->request->get('room_terraza') == '1') ? true : false;
        $check_filters['room_courtyard'] = ($request->request->get('room_courtyard') == 'true' || $request->request->get('room_courtyard') == '1') ? true : false;
        $check_filters['own_others_languages'] = $request->request->get('own_others_languages');
        $check_filters['own_others_included'] = $request->request->get('own_others_included');
        $check_filters['own_others_not_included'] = $request->request->get('own_others_not_included');
        $check_filters['own_others_pets'] = ($request->request->get('own_others_pets') == 'true' || $request->request->get('own_others_pets') == '1') ? true : false;
        $check_filters['own_others_internet'] = ($request->request->get('own_others_internet') == 'true' || $request->request->get('own_others_internet') == '1') ? true : false;

        $room_filter = ($check_filters['room_type'] != null ||
                $check_filters['room_bathroom'] != null ||
                $check_filters['room_climatization'] != null ||
                $check_filters['room_windows_total'] != null ||
                $check_filters['room_audiovisuals'] ||
                $check_filters['room_kids'] ||
                $check_filters['room_smoker'] ||
                $check_filters['room_safe'] ||
                $check_filters['room_balcony'] ||
                $check_filters['room_terraza'] ||
                $check_filters['room_courtyard'] ||
                $check_filters['own_beds_total']
                );

        $session->set("filter_array", $check_filters);
        $session->set("filter_room", $room_filter);

        $paginator = $this->get('ideup.simple_paginator');
        $items_per_page = 15;
        $paginator->setItemsPerPage($items_per_page);
        $list = $paginator->paginate($em->getRepository('mycpBundle:ownership')->search($this, $session->get('search_text'), $session->get('search_arrival_date'), $session->get('search_departure_date'), $session->get('search_guests'), $session->get('search_rooms'), $session->get('search_order'), $room_filter, $check_filters))->getResult();
        $page = 1;
        if (isset($_GET['page']))
            $page = $_GET['page'];

        $own_ids = "0";
        foreach ($list as $own)
            $own_ids .= "," . $own['own_id'];
        $session->set('own_ids', $own_ids);

        $view = $session->get('search_view_results');

        if ($view != null && $view == 'LIST')
            $response = $this->renderView('FrontEndBundle:ownership:searchListOwnership.html.twig', array(
                'list' => $list,
                'items_per_page' => $items_per_page,
                'total_items' => $paginator->getTotalItems(),
                'current_page' => $page,
                'list_preffix' => 'search',
                'show_paginator' => true
            ));
        else if ($view != null && $view == 'PHOTOS')
            $response = $this->renderView('FrontEndBundle:ownership:searchMosaicOwnership.html.twig', array(
                'list' => $list,
                'items_per_page' => $items_per_page,
                'total_items' => $paginator->getTotalItems(),
                'current_page' => $page,
                'list_preffix' => 'search',
                'show_paginator' => true
            ));
        else if ($view != null && $view == 'MAP')
            $response = $this->renderView('FrontEndBundle:ownership:searchMapOwnership.html.twig', array(
                'list' => $list,
                'items_per_page' => $items_per_page,
                'total_items' => $paginator->getTotalItems(),
                'current_page' => $page,
                'list_preffix' => 'search',
                'show_paginator' => true
            ));

        return new Response($response, 200);
    }

    public function get_filters_statisticsAction() {
        $request = $this->getRequest();
        $session = $request->getSession();
        $em = $this->getDoctrine()->getManager();
        $own_ids = $session->get('own_ids');

        $statisics = $em->getRepository('mycpBundle:ownership')->getSearchStatistics();

        $check_filters = array();

        //$check_filters['own_reservation_type'] = $request->request->get('own_reservation_type');
        $check_filters['own_category'] = $request->request->get('own_category');
        $check_filters['own_type'] = $request->request->get('own_type');
        $check_filters['own_price'] = $request->request->get('own_price');
        $check_filters['own_price_from'] = $request->request->get('own_price_from');
        $check_filters['own_price_to'] = $request->request->get('own_price_to');
        $check_filters['own_rooms_number'] = $request->request->get('own_rooms_number');
        $check_filters['room_type'] = $request->request->get('room_type');
        $check_filters['own_beds_total'] = $request->request->get('own_beds_total');
        $check_filters['room_bathroom'] = $request->request->get('room_bathroom');
        $check_filters['room_windows_total'] = $request->request->get('room_windows_total');
        $check_filters['room_climatization'] = $request->request->get('room_climatization');
        $check_filters['room_audiovisuals'] = ($request->request->get('room_audiovisuals') == 'true' || $request->request->get('room_audiovisuals') == '1') ? true : false;
        $check_filters['room_kids'] = ($request->request->get('room_kids') == 'true' || $request->request->get('room_kids') == '1') ? true : false;
        $check_filters['room_smoker'] = ($request->request->get('room_smoker') == 'true' || $request->request->get('room_smoker') == '1') ? true : false;
        $check_filters['room_safe'] = ($request->request->get('room_safe') == 'true' || $request->request->get('room_safe') == '1') ? true : false;
        $check_filters['room_balcony'] = ($request->request->get('room_balcony') == 'true' || $request->request->get('room_balcony') == '1') ? true : false;
        $check_filters['room_terraza'] = ($request->request->get('room_terraza') == 'true' || $request->request->get('room_terraza') == '1') ? true : false;
        $check_filters['room_courtyard'] = ($request->request->get('room_courtyard') == 'true' || $request->request->get('room_courtyard') == '1') ? true : false;
        $check_filters['own_others_languages'] = $request->request->get('own_others_languages');
        $check_filters['own_others_included'] = $request->request->get('own_others_included');
        $check_filters['own_others_not_included'] = $request->request->get('own_others_not_included');
        $check_filters['own_others_pets'] = ($request->request->get('own_others_pets') == 'true' || $request->request->get('own_others_pets') == '1') ? true : false;
        $check_filters['own_others_internet'] = ($request->request->get('own_others_internet') == 'true' || $request->request->get('own_others_internet') == '1') ? true : false;

        $categories_own_list = $em->getRepository('mycpBundle:ownership')->getOwnsCategories($own_ids);
        $types_own_list = $em->getRepository('mycpBundle:ownership')->getOwnsTypes($own_ids);
        $prices_own_list = $em->getRepository('mycpBundle:ownership')->getOwnsPrices($own_ids);

        $response = $this->renderView('FrontEndBundle:ownership:filters.html.twig', array(
            'own_statistics' => $statisics,
            'check_filters' => $check_filters,
            'owns_categories' => $categories_own_list,
            'owns_types' => $types_own_list,
            'owns_prices' => $prices_own_list,
        ));

        return new Response($response, 200);
    }

    public function map_markers_listAction() {
        $request = $this->getRequest();
        $session = $request->getSession();
        $em = $this->getDoctrine()->getManager();
        $own_ids = $session->get('own_ids');
        $list = $em->getRepository('mycpBundle:ownership')->getListByIds($own_ids);
        if (is_array($list)) {
            $result = array();

            foreach ($list as $own) {
                $prize = ($own->getOwnMinimumPrice()) * ($session->get('curr_rate') == null ? 1 : $session->get('curr_rate'));
                $result[] = array('latitude' => $own->getOwnGeolocateX(),
                    'longitude' => $own->getOwnGeolocateY(),
                    'title' => $own->getOwnName(),
                    'content' => $this->get('translator')->trans('FROM_PRICES') . ($session->get("curr_symbol") != null ? " " . $session->get('curr_symbol') . " " : " $ ") . $prize . " " . strtolower($this->get('translator')->trans("BYNIGHTS_PRICES")),
                    'image' => $this->container->get('templating.helper.assets')->getUrl('uploads/ownershipImages/thumbnails/' . $em->getRepository('mycpBundle:ownership')->get_ownership_photo($own->getOwnId())), //$this->get_ownership_photo($own->getOwnId()),
                    'id' => $own->getOwnId());
            }
        }

        return new Response(json_encode($result), 200);
    }

    public function update_ratingAction($ownid) {
        $request = $this->getRequest();
        $session = $request->getSession();
        $em = $this->getDoctrine()->getManager();
        $own_obj = $em->getRepository('mycpBundle:ownership')->find($ownid);
        $ownership = array('ownname' => $own_obj->getOwnName(),
            'rating' => $own_obj->getOwnRating(),
            'comments_total' => $own_obj->getOwnCommentsTotal());

        $response = $this->renderView('FrontEndBundle:ownership:ownershipRating.html.twig', array(
            'ownership' => $ownership
        ));

        if ($session->get('comments_cant') != null)
            $session->remove('comments_cant');

        return new Response($response, 200);
    }

    function add_favorite_ownershipAction($id_ownership) {
        $string_favorites = '';
        $ownership_in_cookies = false;

        if (isset($_COOKIE["mycp_favorites_ownerships"])) {
            $string_favorites = $_COOKIE["mycp_favorites_ownerships"];
            $array_ownerships = explode('*', $string_favorites);
            array_pop($array_ownerships);
            foreach ($array_ownerships as $ownership) {
                if ($ownership == $id_ownership) {
                    $ownership_in_cookies = true;
                    break;
                }
            }
            if ($ownership_in_cookies == false) {
                $string_favorites.=$id_ownership . '*';
            }
        }
        else
            $string_favorites = $id_ownership . '*';



        setcookie("mycp_favorites_ownerships", $string_favorites, time() + 3600);

        //var_dump($_COOKIE["mycp_favorites_ownerships"]);
        return $this->redirect($this->generateUrl('frontend_details_ownership', array('ownerid' => $id_ownership)));
    }

    public function map_details_ownershipAction($ownGeolocateX, $ownGeolocateY, $ownName, $description, $image) {
        $ownership = new ownership();
        $ownership->setOwnName($ownName);
        $ownership->setOwnGeolocateX($ownGeolocateX);
        $ownership->setOwnGeolocateY($ownGeolocateY);

        return $this->render('FrontEndBundle:ownership:ownershipDetailsMap.html.twig', array(
                    'ownership' => $ownership,
                    'description' => $description,
                    'image' => $image
        ));
    }

    public function map_resizedAction($in_searcher = 'true', $destination_name = null) {
        $request = $this->getRequest();
        $session = $request->getSession();
        $em = $this->getDoctrine()->getManager();
        $markers_id_list = $request->request->get('own_ids');
        $users_id = $em->getRepository('mycpBundle:user')->user_ids($this);

        $own_ids = "0";

        if ($markers_id_list != null || count($markers_id_list)) {
            foreach ($markers_id_list as $id)
                $own_ids .= ", " . $id;
        }

        $session->set('own_ids', $own_ids);

        $paginator = $this->get('ideup.simple_paginator');
        $items_per_page = ($in_searcher == 'false' && $destination_name != null && $destination_name != "") ? 5 : 15;
        $paginator->setItemsPerPage($items_per_page);
        $list = $em->getRepository('mycpBundle:ownership')->getCompleteListByIds($own_ids, $users_id["user_id"], $users_id["session_id"]);
        $list_paginated = $paginator->paginate($list)->getResult();
        $page = 1;
        if (isset($_GET['page']))
            $page = $_GET['page'];

        if ($in_searcher == 'false' && $destination_name != null && $destination_name != "" && $destination_name != 'null')
            $response = $this->renderView('FrontEndBundle:ownership:searchListOwnership.html.twig', array(
                'list' => $list_paginated,
                'items_per_page' => $items_per_page,
                'total_items' => $paginator->getTotalItems(),
                'current_page' => $page,
                'type' => 'map',
                'list_preffix' => 'map',
                'show_paginator' => true,
                'in_searcher' => $in_searcher,
                'destination_name' => $destination_name
            ));
        else
            $response = $this->renderView('FrontEndBundle:ownership:searchListOwnership.html.twig', array(
                'list' => $list_paginated,
                'items_per_page' => $items_per_page,
                'total_items' => $paginator->getTotalItems(),
                'current_page' => $page,
                'type' => 'map',
                'list_preffix' => 'map',
                'show_paginator' => true
            ));

        return new Response($response, 200);
    }

    public function voted_best_listAction() {
        $em = $this->getDoctrine()->getManager();

        $session = $this->getRequest()->getSession();
        $session->set('search_order', 'BEST_VALUED');

        $list = $em->getRepository('mycpBundle:ownership')->search($this, null, null, null, '1', '1', $session->get('search_order'));
        $paginator = $this->get('ideup.simple_paginator');
        $items_per_page = 15;
        $paginator->setItemsPerPage($items_per_page);
        $search_results_list = $paginator->paginate($list)->getResult();
        $page = 1;
        if (isset($_GET['page']))
            $page = $_GET['page'];

        $session->set('search_text', null);
        $session->set('search_arrival_date', null);
        $session->set('search_departure_date', null);
        $session->set('search_guests', '1');

        $own_ids = "0";
        foreach ($list as $own)
            $own_ids .= "," . $own['own_id'];
        $session->set('own_ids', $own_ids);

        if ($session->get('search_view_results') == null || $session->get('search_view_results') == '')
            $session->set('search_view_results', 'LIST');

        $categories_own_list = $em->getRepository('mycpBundle:ownership')->getOwnsCategories($own_ids);
        $types_own_list = $em->getRepository('mycpBundle:ownership')->getOwnsTypes($own_ids);
        $prices_own_list = $em->getRepository('mycpBundle:ownership')->getOwnsPrices($own_ids);
        $statistics_own_list = $em->getRepository('mycpBundle:ownership')->getSearchStatistics();

        return $this->render('FrontEndBundle:ownership:searchOwnership.html.twig', array(
                    'search_text' => null,
                    'search_guests' => '1',
                    'search_arrival_date' => null,
                    'search_departure_date' => null,
                    'list' => $search_results_list,
                    'owns_categories' => $categories_own_list,
                    'owns_types' => $types_own_list,
                    'owns_prices' => $prices_own_list,
                    'view_results' => $session->get('search_view_results'),
                    'order' => $session->get('search_order'),
                    'own_statistics' => $statistics_own_list,
                    'locale' => $this->get('translator')->getLocale(),
                    'autocomplete_text_list' => $em->getRepository('mycpBundle:ownership')->autocomplete_text_list(),
                    'items_per_page' => $items_per_page,
                    'total_items' => $paginator->getTotalItems(),
                    'current_page' => $page,
                    'list_preffix' => 'voted_best'
        ));
    }

    private function is_ownership_in_cookie($id_own) {
        if (isset($_COOKIE["mycp_favorites_ownerships"])) {
            $string_favorites = $_COOKIE["mycp_favorites_ownerships"];
            echo $string_favorites;
            $array_ownerships = explode('*', $string_favorites);
            array_pop($array_ownerships);
            foreach ($array_ownerships as $ownership) {

                if ($ownership == $id_own) {
                    return true;
                }
            }
        }
        return false;
    }

    public function type_listAction($type) {
        $em = $this->getDoctrine()->getManager();

        $session = $this->getRequest()->getSession();
        $session->set('search_order', 'BEST_VALUED');

        $filters = array();
        $filters['own_type'] = array(str_replace("-", " ", ucfirst($type)));

        $list = $em->getRepository('mycpBundle:ownership')->search($this, null, null, null, '1', '1', $session->get('search_order'), false, $filters);
        $paginator = $this->get('ideup.simple_paginator');
        $items_per_page = 15;
        $paginator->setItemsPerPage($items_per_page);
        $search_results_list = $paginator->paginate($list)->getResult();
        $page = 1;
        if (isset($_GET['page']))
            $page = $_GET['page'];

        $session->set('search_text', null);
        $session->set('search_arrival_date', null);
        $session->set('search_departure_date', null);
        $session->set('search_guests', '1');

        $own_ids = "0";

        foreach ($list as $own)
            $own_ids .= "," . $own['own_id'];

        $session->set('own_ids', $own_ids);

        if ($session->get('search_view_results') == null || $session->get('search_view_results') == '')
            $session->set('search_view_results', 'LIST');

        $categories_own_list = $em->getRepository('mycpBundle:ownership')->getOwnsCategories($own_ids);
        $types_own_list = $em->getRepository('mycpBundle:ownership')->getOwnsTypes($own_ids);
        $prices_own_list = $em->getRepository('mycpBundle:ownership')->getOwnsPrices($own_ids);
        $statistics_own_list = $em->getRepository('mycpBundle:ownership')->getSearchStatistics();

        $check_filters = array();
        $check_filters['own_reservation_type'] = null;
        $check_filters['own_category'] = null;
        $check_filters['own_type'] = array($type);
        $check_filters['own_price'] = null;
        $check_filters['own_price_from'] = null;
        $check_filters['own_price_to'] = null;
        $check_filters['own_rooms_number'] = null;
        $check_filters['room_type'] = null;
        $check_filters['own_beds_total'] = null;
        $check_filters['room_bathroom'] = null;
        $check_filters['room_windows_total'] = null;
        $check_filters['room_climatization'] = null;
        $check_filters['room_audiovisuals'] = false;
        $check_filters['room_kids'] = false;
        $check_filters['room_smoker'] = false;
        $check_filters['room_safe'] = false;
        $check_filters['room_balcony'] = false;
        $check_filters['room_terraza'] = false;
        $check_filters['room_courtyard'] = false;
        $check_filters['own_others_languages'] = null;
        $check_filters['own_others_included'] = null;
        $check_filters['own_others_not_included'] = null;
        $check_filters['own_others_pets'] = false;
        $check_filters['own_others_internet'] = false;

        return $this->render('FrontEndBundle:ownership:searchOwnership.html.twig', array(
                    'search_text' => null,
                    'search_guests' => '1',
                    'search_arrival_date' => null,
                    'search_departure_date' => null,
                    'owns_categories' => $categories_own_list,
                    'owns_types' => $types_own_list,
                    'owns_prices' => $prices_own_list,
                    'view_results' => $session->get('search_view_results'),
                    'order' => $session->get('search_order'),
                    'own_statistics' => $statistics_own_list,
                    'locale' => $this->get('translator')->getLocale(),
                    'autocomplete_text_list' => $em->getRepository('mycpBundle:ownership')->autocomplete_text_list(),
                    'check_filters' => $check_filters,
                    'list_preffix' => 'search',
                    'list' => $search_results_list
        ));
    }

    public function top_rated_callbackAction() {
        $request = $this->getRequest();
        $session = $request->getSession();
        $em = $this->getDoctrine()->getManager();
        $locale = $this->get('translator')->getLocale();
        $show_rows = $request->request->get('show_rows');

        if ($show_rows != null)
            $session->set('top_rated_show_rows', $show_rows);
        else if ($session->get("top_rated_show_rows") == null)
            $session->set('top_rated_show_rows', 2);

        if ($session->get("top_rated_category") == null)
            $session->set("top_rated_category", "todos");

        $statistics = $em->getRepository("mycpBundle:ownership")->top20_statistics();

        $category = $session->get("top_rated_category");
        $paginator = $this->get('ideup.simple_paginator');
        $items_per_page = 4 * $session->get("top_rated_show_rows");
        $paginator->setItemsPerPage($items_per_page);
        $list = $em->getRepository('mycpBundle:ownership')->top20($locale, ((strtolower($category) != "todos") ? $category : null));
        $own_top20_list = $paginator->paginate($list)->getResult();
        $page = 1;
        if (isset($_GET['page']))
            $page = $_GET['page'];

        $response = $this->renderView('FrontEndBundle:ownership:homeTopRatedOwnership.html.twig', array(
            'own_top20_list' => $own_top20_list,
            'top_rated_items_per_page' => $items_per_page,
            'top_rated_total_items' => $paginator->getTotalItems(),
            'current_page' => $page,
            'premium_total' => $statistics['premium_total'],
            'midrange_total' => $statistics['midrange_total'],
            'economic_total' => $statistics['economic_total']
        ));

        return new Response($response, 200);
    }

    public function top_rated_change_category_callbackAction() {
        $request = $this->getRequest();
        $session = $request->getSession();
        $em = $this->getDoctrine()->getManager();
        $locale = $this->get('translator')->getLocale();
        $category = $request->request->get('category');

        if ($category != null && $category != "")
            $session->set('top_rated_category', $category);
        else
            $session->set('top_rated_category', 'todos');

        if ($session->get("top_rated_show_rows") == null)
            $session->set("top_rated_show_rows", 2);

        $statistics = $em->getRepository("mycpBundle:ownership")->top20_statistics();
        $paginator = $this->get('ideup.simple_paginator');
        $items_per_page = 4 * $session->get("top_rated_show_rows");
        $paginator->setItemsPerPage($items_per_page);

        $list = $em->getRepository('mycpBundle:ownership')->top20($locale, ((strtolower($category) != "todos") ? $category : null));
        $own_top20_list = $paginator->paginate($list)->getResult();
        $page = 1;
        if (isset($_GET['page']))
            $page = $_GET['page'];

        $response = $this->renderView('FrontEndBundle:ownership:homeTopRatedOwnership.html.twig', array(
            'own_top20_list' => $own_top20_list,
            'top_rated_items_per_page' => $items_per_page,
            'top_rated_total_items' => $paginator->getTotalItems(),
            'current_page' => $page,
            'premium_total' => $statistics['premium_total'],
            'midrange_total' => $statistics['midrange_total'],
            'economic_total' => $statistics['economic_total']
        ));

        return new Response($response, 200);
    }

    public function orangeSearchBarAction() {
        $em = $this->getDoctrine()->getManager();
        $session = $this->getRequest()->getSession();

        return $this->render('FrontEndBundle:ownership:orangeSearchBar.html.twig', array(
                    'locale' => $this->get('translator')->getLocale(),
                    'autocomplete_text_list' => $em->getRepository('mycpBundle:ownership')->autocomplete_text_list(),
                    'arrival_date' => $session->get("search_arrival_date"),
                    'departure_date' => $session->get("search_departure_date")
        ));
    }

    public function owners_photosAction($ownership_id, $photo) {
        if ($photo == null) {
            $em = $this->getDoctrine()->getManager();
            $photo = $em->getRepository('mycpBundle:userCasa')->get_owners_photos($ownership_id);
        }

        return $this->render('FrontEndBundle:ownership:ownersPhotosOwnership.html.twig', array(
                    'owner_photo' => $photo
        ));
    }

    public function last_owns_visitedAction($exclude_ownership_id = null) {
        $em = $this->getDoctrine()->getManager();
        $user_ids = $em->getRepository('mycpBundle:user')->user_ids($this);
        $history_owns = $em->getRepository('mycpBundle:userHistory')->get_list_entity($user_ids, true, 10, $exclude_ownership_id);
        $history_owns_photos = $em->getRepository('mycpBundle:ownership')->get_photos_array($history_owns);

        return $this->render('FrontEndBundle:ownership:historyOwnership.html.twig', array(
                    'history_list' => $history_owns,
                    'photos' => $history_owns_photos
        ));
    }

    public function near_by_destinationsAction($municipality_id, $province_id) {
        $em = $this->getDoctrine()->getManager();
        $users_id = $em->getRepository('mycpBundle:user')->user_ids($this);

        $destinations = $em->getRepository('mycpBundle:destination')->filter($municipality_id, $province_id, null, null, 3);

        if (count($destinations) < 3)
            $destinations = $em->getRepository('mycpBundle:destination')->getPopularDestinations(3, $users_id["user_id"], $users_id["session_id"], $this->get('translator')->getLocale());

        return $this->render('FrontEndBundle:ownership:nearByDestinationsOwnership.html.twig', array(
                    'destinations' => $destinations
        ));
    }

}
