<?php

namespace MyCp\PartnerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

class DashboardController extends Controller
{
    public function indexAction()
    {
        return new JsonResponse([
            'success' => true,
            'html' => $this->renderView('PartnerBundle:Dashboard:requests.html.twig', array('apis' => 'asdasd', 'form'=>'dsdsd')),
            'msg' => 'Vista del listado de apis']);
    }

    /**
     * @param $ids
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function pageDetailAction($ids, Request $request) {
//
//        $em = $this->getDoctrine()->getManager();
//        $user_ids = $em->getRepository('mycpBundle:user')->getIds($this);
//        $locale = $this->get('translator')->getLocale();
//
//
//        $own_name = str_replace('-', ' ', $own_name);
//        $own_name = str_replace('  ', '-', $own_name);
//        //$own_name = str_replace("nn", "ñ", $own_name);
//
//        $ownership_array = $em->getRepository('mycpBundle:ownership')->getDetails($own_name, $locale, $user_ids["user_id"], $user_ids["session_id"]);
//        if ($ownership_array == null) {
//            throw $this->createNotFoundException();
//        }
//
//        $langs_array = array();
//        if ($ownership_array['english'] == 1)
//            $langs_array[] = $this->get('translator')->trans("LANG_ENGLISH");
//
//        if ($ownership_array['french'] == 1)
//            $langs_array[] = $this->get('translator')->trans("LANG_FRENCH");
//
//        if ($ownership_array['german'] == 1)
//            $langs_array[] = $this->get('translator')->trans("LANG_GERMAN");
//
//        if ($ownership_array['italian'] == 1)
//            $langs_array[] = $this->get('translator')->trans("LANG_ITALIAN");
//
//        $languages = $this->get('translator')->trans('LANG_SPANISH');
//
//        foreach ($langs_array as $lang)
//            $languages .= ", " . $lang;
//
//        $owner_id = $ownership_array['own_id'];
//        $reservations = $em->getRepository('mycpBundle:generalReservation')->getReservationsByIdAccommodation($owner_id);
//
//        // $similar_houses = $em->getRepository('mycpBundle:ownership')->getByCategory($ownership_array['category'], null, $owner_id, $user_ids["user_id"], $user_ids["session_id"]);
//        // $total_similar_houses = count($similar_houses);
//
//        $paginator = $this->get('ideup.simple_paginator');
//        $items_per_page = 5;
//        $paginator->setItemsPerPage($items_per_page);
//        $total_comments = $em->getRepository('mycpBundle:comment')->getByOwnership($owner_id);
//        $comments = $paginator->paginate($total_comments)->getResult();
//        $page = 1;
//        if (isset($_GET['page']))
//            $page = $_GET['page'];
//
//        $rooms = $em->getRepository('mycpBundle:room')->findBy(array('room_ownership' => $owner_id, 'room_active' => true));
//        $friends = array();
//        $own_photos = $em->getRepository('mycpBundle:ownership')->getPhotosAndDescription($owner_id, $locale);
//
//        $session = $this->get('session');
//        $post = $request->request->getIterator()->getArrayCopy();
//        $start_date = (isset($post['top_reservation_filter_date_from'])) ? ($post['top_reservation_filter_date_from']) : (($session->get('search_arrival_date') != null) ? $session->get('search_arrival_date') : 'now');
//        $end_date = (isset($post['top_reservation_filter_date_to'])) ? ($post['top_reservation_filter_date_to']) : (($session->get('search_departure_date') != null) ? $session->get('search_departure_date') : '+2 day');
//
//        $start_timestamp = strtotime($start_date);
//        $end_timestamp = strtotime($end_date);
//
//
//        if (isset($post['top_reservation_filter_date_from'])) {
//            $post['reservation_filter_date_from'] = $post['top_reservation_filter_date_from'];
//            $post['reservation_filter_date_to'] = $post['top_reservation_filter_date_to'];
//        }
//
//        if ($this->getRequest()->getMethod() == 'POST') {
//            if (isset($post['reservation_filter_date_from']) && isset($post['reservation_filter_date_to'])) {
//                $reservation_filter_date_from = $post['reservation_filter_date_from'];
//                $reservation_filter_date_from = explode('/', $reservation_filter_date_from);
//                $start_timestamp = mktime(0, 0, 0, $reservation_filter_date_from[1], $reservation_filter_date_from[0], $reservation_filter_date_from[2]);
//
//                $reservation_filter_date_to = $post['reservation_filter_date_to'];
//                $reservation_filter_date_to = explode('/', $reservation_filter_date_to);
//                $end_timestamp = mktime(0, 0, 0, $reservation_filter_date_to[1], $reservation_filter_date_to[0], $reservation_filter_date_to[2]);
//            }
//        } else {
//
//        }
//
//        $service_time = $this->get('Time');
//        $array_dates = $service_time->datesBetween($start_timestamp, $end_timestamp);
//
//        $array_no_available = array();
//        $no_available_days = array();
//
//        $array_prices = array();
//        $prices_dates = array();
//
//        foreach ($rooms as $room) {
//            foreach ($reservations as $reservation) {
//
//                if ($reservation->getOwnResSelectedRoomId() == $room->getRoomId()) {
//
//                    if ($start_timestamp <= $reservation->getOwnResReservationFromDate()->getTimestamp() &&
//                        $end_timestamp >= $reservation->getOwnResReservationToDate()->getTimestamp() && $reservation->getOwnResStatus() == ownershipReservation::STATUS_RESERVED) {
//
//                        $array_no_available[$room->getRoomId()] = $room->getRoomId();
//                    }
//
//                    if ($start_timestamp >= $reservation->getOwnResReservationFromDate()->getTimestamp() &&
//                        $start_timestamp <= $reservation->getOwnResReservationToDate()->getTimestamp() &&
//                        $end_timestamp >= $reservation->getOwnResReservationToDate()->getTimestamp() && $reservation->getOwnResStatus() == ownershipReservation::STATUS_RESERVED) {
//
//                        $array_no_available[$room->getRoomId()] = $room->getRoomId();
//                    }
//
//                    if ($start_timestamp <= $reservation->getOwnResReservationFromDate()->getTimestamp() &&
//                        $end_timestamp <= $reservation->getOwnResReservationToDate()->getTimestamp() &&
//                        $end_timestamp >= $reservation->getOwnResReservationFromDate()->getTimestamp() && $reservation->getOwnResStatus() == ownershipReservation::STATUS_RESERVED) {
//
//                        $array_no_available[$room->getRoomId()] = $room->getRoomId();
//                    }
//
//                    if ($start_timestamp >= $reservation->getOwnResReservationFromDate()->getTimestamp() &&
//                        $end_timestamp <= $reservation->getOwnResReservationToDate()->getTimestamp() && $reservation->getOwnResStatus() == ownershipReservation::STATUS_RESERVED) {
//
//                        $array_no_available[$room->getRoomId()] = $room->getRoomId();
//                    }
//
//                    $array_numbers_check = array();
//                    $cont_numbers = 1;
//                    foreach ($array_dates as $date) {
//
//                        if ($date >= $reservation->getOwnResReservationFromDate()->getTimestamp() && $date <= $reservation->getOwnResReservationToDate()->getTimestamp() && $reservation->getOwnResStatus() == ownershipReservation::STATUS_RESERVED) {
//                            array_push($array_numbers_check, $cont_numbers);
//                        }
//                        $cont_numbers++;
//                    }
//                    array_push($no_available_days, array(
//                        $room->getRoomId() => $room->getRoomId(),
//                        'check' => $array_numbers_check
//                    ));
//                }
//            }
//            $total_price_room = 0;
//            $prices_dates_temp = array();
//            $x = 1;
//            /* if ($request->getMethod() != 'POST') {
//              //$x = 2;
//              } */
//            $seasons = $em->getRepository("mycpBundle:season")->getSeasons($start_date, $end_date, $ownership_array['des_id']);
//            for ($a = 0; $a < count($array_dates) - $x; $a++) {
//
//                $season_type = $service_time->seasonTypeByDate($seasons, $array_dates[$a]);
//                $roomPrice = $room->getPriceBySeasonType($season_type);
//                $total_price_room += $roomPrice;
//                array_push($prices_dates_temp, $roomPrice);
//            }
//            array_push($array_prices, $total_price_room);
//            array_push($prices_dates, $prices_dates_temp);
//        }
//        $no_available_days_ready = array();
//        foreach ($no_available_days as $item) {
//            $keys = array_keys($item);
//            if (!isset($no_available_days_ready[$item[$keys[0]]]))
//                $no_available_days_ready[$item[$keys[0]]] = array();
//            $no_available_days_ready[$item[$keys[0]]] = array_merge($no_available_days_ready[$item[$keys[0]]], $item['check']);
//        }
//
//        $array_dates_keys = array();
//        $count = 1;
//        foreach ($array_dates as $date) {
//            $array_dates_keys[$count] = array('day_number' => date('d', $date), 'day_name' => date('D', $date));
//            $count++;
//        }
//        if ($this->getRequest()->getMethod() != 'POST') {
//            // array_pop($array_dates_keys);
//        }
//
//        $flag_room = 0;
//        $price_subtotal = 0;
//        $do_operation = true;
//        $available_rooms = array();
//        $avail_array_prices = array();
//        foreach ($rooms as $room_2) {
//            foreach ($array_no_available as $no_avail) {
//                if ($room_2->getRoomId() == $no_avail) {
//                    $do_operation = false;
//                }
//            }
//            if ($do_operation == true) {
//                $price_subtotal+=$array_prices[$flag_room];
//                array_push($available_rooms, $room_2->getRoomId());
//                array_push($avail_array_prices, $array_prices[$flag_room]);
//            }
//            $do_operation = true;
//            $flag_room++;
//        }
//        //exit();
//
//        /* YANET */
//        $em->getRepository('mycpBundle:userHistory')->insert(true, $owner_id, $user_ids);
//
//        $real_category = "";
//        if ($ownership_array['category'] == 'Económica')
//            $real_category = 'economy';
//        else if ($ownership_array['category'] == 'Rango medio')
//            $real_category = 'mid_range';
//        else if ($ownership_array['category'] == 'Premium')
//            $real_category = 'premium';
//
//        $brief_description = Utils::removeNewlines($ownership_array['brief_description']);
//
//        $currentServiceFee = $em->getRepository("mycpBundle:serviceFee")->getCurrent();
//
//        return $this->render('FrontEndBundle:ownership:ownershipDetails.html.twig', array(
//            'avail_array_prices' => $avail_array_prices,
//            'available_rooms' => $available_rooms,
//            'price_subtotal' => $price_subtotal,
//            'avail_array_prices' => $avail_array_prices,
//            'array_prices' => $array_prices,
//            'prices_dates' => $prices_dates,
//            'ownership' => $ownership_array,
//            'description' => $ownership_array['description'],
//            'brief_description' => $brief_description,
//            'automaticTranslation' => $ownership_array['autotomaticTranslation'],
//            //'similar_houses' => array_slice($similar_houses, 0, 5),
//            //'total_similar_houses' => $total_similar_houses,
//            'comments' => $comments,
//            'friends' => $friends,
//            'show_comments_and_friends' => count($total_comments) + count($friends),
//            'rooms' => $rooms,
//            'gallery_photos' => $own_photos,
//            'is_in_favorite' => $ownership_array['is_in_favorites'],
//            'array_dates' => $array_dates_keys,
//            'post' => $post,
//            'reservations' => $array_no_available,
//            'no_available_days' => $no_available_days_ready,
//            'comments_items_per_page' => $items_per_page,
//            'comments_total_items' => $paginator->getTotalItems(),
//            'comments_current_page' => $page,
//            'can_comment' => $em->getRepository("mycpBundle:comment")->canComment($user_ids["user_id"], $owner_id),
//            'can_public_comment' => $em->getRepository("mycpBundle:comment")->canPublicComment($user_ids["user_id"], $owner_id),
//            'locale' => $locale,
//            'real_category' => $real_category,
//            'languages' => $languages,
//            'keywords' => $ownership_array['keywords'],
//            'locale' => $locale,
//            'currentServiceFee' => $currentServiceFee
//        ));

        return $this->render('@Partner/Dashboard/accomodation_detail.html.twig');
    }


}
