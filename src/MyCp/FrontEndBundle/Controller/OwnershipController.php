<?php

namespace MyCp\FrontEndBundle\Controller;

use MyCp\FrontEndBundle\Helpers\Utils;
use MyCp\mycpBundle\Entity\bookingModality;
use MyCp\mycpBundle\Entity\ownership;
use MyCp\mycpBundle\Entity\ownershipReservation;
use MyCp\mycpBundle\Helpers\OrderByHelper;
use MyCp\mycpBundle\Helpers\SearchUtils;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;

class OwnershipController extends Controller
{

    public function getReservationCalendarAction(Request $request)
    {
        $from = $request->get('from');
        $to = $request->get('to');
        $fromBackend = $request->get('backend');
        $timer = $this->get("Time");
        $fromBackend = ($fromBackend != "" && $fromBackend);

        $reservation_from = explode('/', $from);
        $dateFrom = new \DateTime();
        $start_timestamp = mktime(0, 0, 0, $reservation_from[1], $reservation_from[0], $reservation_from[2]);
        $dateFrom->setTimestamp($start_timestamp);

        $reservation_to = explode('/', $to);
        $dateTo = new \DateTime();
        $end_timestamp = mktime(0, 0, 0, $reservation_to[1], $reservation_to[0], $reservation_to[2]);
        $dateTo->setTimestamp($end_timestamp);
        $owner_id = $request->get('own_id');

        $nights = $timer->nights($dateFrom->getTimestamp(), $dateTo->getTimestamp());
        $dateTo->setTimestamp(strtotime("-1 day", $end_timestamp));
        /* if($dateFrom==$dateTo){
             $dateTo->setTimestamp(strtotime("+1 day", $end_timestamp));
         }*/
        if (!$owner_id) {
            return $this->redirect($this->generateUrl('frontend_search_ownership'));
            //throw $this->createNotFoundException();
        }

        $em = $this->getDoctrine()->getManager();

        $ownership = $em->getRepository('mycpBundle:ownership')->find($owner_id);
        $bookingModality = $ownership->getBookingModality();

        $completeReservationPrice = 0;
        if ($bookingModality != null and $bookingModality->getBookingModality()->getName() == bookingModality::COMPLETE_RESERVATION_BOOKING)
            $completeReservationPrice = $bookingModality->getPrice();

        /*$general_reservations = $em->getRepository('mycpBundle:generalReservation')->findBy(array('gen_res_own_id' => $owner_id));
        $reservations = array();
        foreach ($general_reservations as $gen_res) {
            $own_reservations = $em->getRepository('mycpBundle:ownershipReservation')->getReservationReservedByGeneralAndDate($gen_res->getGenResId(), $dateFrom, $dateTo);//findBy(array('own_res_gen_res_id' => $gen_res->getGenResId()));
            foreach ($own_reservations as $own_res) {
                array_push($reservations, $own_res);
            }
        }*/

        $roomsAux = $em->getRepository('mycpBundle:room')->findBy(array('room_ownership' => $owner_id, 'room_active' => true), array("room_num" => "ASC"));

        $rooms = ($completeReservationPrice > 0) ? array($roomsAux[0]) : $roomsAux;
        $service_time = $this->get('Time');
        $array_dates = $service_time->datesBetween($start_timestamp, $end_timestamp);

        $array_no_available = array();
        $no_available_days = array();
        $no_available_days_ready = array();
        $array_prices = array();
        $prices_dates = array();


        foreach ($rooms as $room) {
            $temp_local = array();
            $unavailable_room = $em->getRepository('mycpBundle:unavailabilityDetails')->getRoomDetailsByRoomAndDates($room->getRoomId(), $dateFrom, $dateTo);
            $flag = 0;
            if ($unavailable_room) {
                foreach ($unavailable_room as $ur) {
                    $unavailable_days = $service_time->datesBetween($ur->getUdFromDate()->getTimestamp(), $ur->getUdToDate()->getTimestamp());
                    // unavailable details
                    $array_no_available[$room->getRoomId()] = $room->getRoomId();
                    $flag = 1;
                    /*if ($start_timestamp <= $ur->getUdFromDate()->getTimestamp() &&
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
                    }*/
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

            /*dump($room->getRoomId());
            dump($dateFrom);
            dump($dateTo); die;*/
            $reservations = $em->getRepository('mycpBundle:ownershipReservation')->getReservationReservedByRoomAndDateForCalendar($room->getRoomId(), $dateFrom, $dateTo);
            //var_dump("Habitacion id ". $room->getRoomId(). ": REservaciones " .count($reservations). ". Desde: ".date("d-m-Y",$dateFrom->getTimestamp()). ". Hasta: ".date("d-m-Y",$dateTo->getTimestamp())."<br/>");
            foreach ($reservations as $reservation) {
                $reservationStartDate = $reservation->getOwnResReservationFromDate()->getTimestamp();
                $reservationEndDate = $reservation->getOwnResReservationToDate()->getTimestamp();
                $date = new \DateTime();
                $date->setTimestamp(strtotime("-1 day", $reservationEndDate));
                $reservationEndDate = $date->getTimestamp();

                $array_no_available[$room->getRoomId()] = $room->getRoomId();

                /*if ($start_timestamp <= $reservationStartDate && $end_timestamp >= $reservationEndDate) {

                    $array_no_available[$room->getRoomId()] = $room->getRoomId();
                }

                if ($start_timestamp >= $reservationStartDate && $start_timestamp <= $reservationEndDate &&
                        $end_timestamp >= $reservationEndDate) {

                    $array_no_available[$room->getRoomId()] = $room->getRoomId();
                }

                if ($start_timestamp <= $reservationStartDate && $end_timestamp <= $reservationEndDate &&
                        $end_timestamp >= $reservationStartDate) {

                    $array_no_available[$room->getRoomId()] = $room->getRoomId();
                }

                if ($start_timestamp >= $reservationStartDate && $end_timestamp <= $reservationEndDate) {

                    $array_no_available[$room->getRoomId()] = $room->getRoomId();
                }*/

                $array_numbers_check = array();
                $cont_numbers = 1;
                foreach ((array)$array_dates as $date) {

                    if ($date >= $reservationStartDate && $date <= $reservationEndDate && $date != $dateTo->getTimestamp()) {
                        array_push($array_numbers_check, $cont_numbers);
                    }
                    $cont_numbers++;
                }
                array_push($no_available_days, array(
                    $room->getRoomId() => $room->getRoomId(),
                    'check' => $array_numbers_check
                ));
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
                $roomPrice = ($completeReservationPrice > 0) ? $completeReservationPrice : $room->getPriceBySeasonType($season_type);

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
                $price_subtotal += $array_prices[$flag_room];
                array_push($available_rooms, $room_2->getRoomId());
                array_push($avail_array_prices, $array_prices[$flag_room]);
            }
            $do_operation = true;
            $flag_room++;
        }
        $tripleChargeRoom = $this->container->getParameter('configuration.triple.room.charge');
        $mobileDetector = $this->get('mobile_detect.mobile_detector');

        if ($mobileDetector->isMobile()) {
            return $this->render('MyCpMobileFrontendBundle:ownership:ownershipReservationCalendar.html.twig', array(
                'array_dates' => $array_dates_keys,
                'rooms' => $rooms,
                'array_prices' => $array_prices,
                'ownership' => $ownership,
                'no_available_days' => $no_available_days_ready,
                'prices_dates' => $prices_dates,
                'reservations' => $array_no_available,
                'fromBackend' => $fromBackend,
                'nights' => $nights,
                'tripleChargeRoom' => $tripleChargeRoom,
                'isCompletePayment' => ($completeReservationPrice > 0),
                'completeReservationPrice' => $completeReservationPrice
            ));
        } else {
            //$no_available_days_ready[351]=array(11,12,13,14,15,21,22);
            return $this->render('FrontEndBundle:ownership:ownershipReservationCalendar.html.twig', array(
                'array_dates' => $array_dates_keys,
                'rooms' => $rooms,
                'array_prices' => $array_prices,
                'ownership' => $ownership,
                'no_available_days' => $no_available_days_ready,
                'prices_dates' => $prices_dates,
                'reservations' => $array_no_available,
                'fromBackend' => $fromBackend,
                'nights' => $nights,
                'tripleChargeRoom' => $tripleChargeRoom,
                'isCompletePayment' => ($completeReservationPrice > 0),
                'completeReservationPrice' => $completeReservationPrice
            ));
        }
    }

    public function ownDetailsDirectAction($own_code)
    {
        // There are so many browserconfig.xml requests from stupid IE6 that we check for it
        // here to avoid Exceptions in the log files
        if (strpos($own_code, 'browserconfig.xml') !== false) {
            return new Response('not found', 404);
        }

        if ($own_code != "welcome") {
            $em = $this->getDoctrine()->getManager();
            $ownership = $em->getRepository('mycpBundle:ownership')->findOneBy(array('own_mcp_code' => $own_code));
            if ($ownership && $ownership->getOwnStatus()->getStatusId() == \MyCp\mycpBundle\Entity\ownershipStatus::STATUS_ACTIVE) {
                $own_name = Utils::urlNormalize($ownership->getOwnName());
                return $this->redirect($this->generateUrl('frontend_details_ownership', array('own_name' => $own_name)));
            } else
                throw $this->createNotFoundException();
        } else
            return $this->redirect($this->generateUrl('frontend-welcome', array('_locale' => "en")));
    }

    public function simpleAction($mycp_code)
    {
        // There are so many browserconfig.xml requests from stupid IE6 that we check for it
        // here to avoid Exceptions in the log files
        if (strpos($mycp_code, 'browserconfig.xml') !== false) {
            return new Response('not found', 404);
        }

        $em = $this->getDoctrine()->getManager();
        $locale = $this->get('translator')->getLocale();
        //$own_name = Utils::urlNormalize($ownership->getOwnName());
        $ownership_array = $em->getRepository('mycpBundle:ownership')->getDetailsByCode($mycp_code, strtoupper($locale), true);

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

            $brief_description = Utils::removeNewlines($ownership_array['brief_description']);

            foreach ($langs_array as $lang)
                $languages .= ", " . $lang;
            return $this->render('FrontEndBundle:ownership:ownershipSimpleDetails.html.twig', array( //'FrontEndBundle:ownership:test.html.twig'
                'ownership' => $ownership_array,
                'description' => $ownership_array['description'],
                'brief_description' => $brief_description,
                'automaticTranslation' => $ownership_array['autotomaticTranslation'],
                'rooms' => $rooms,
                'gallery_photos' => $own_photos,
                'locale' => $locale,
                'real_category' => $real_category,
                'languages' => $languages,
                'keywords' => $ownership_array['keywords']
            ));
        } else
            throw $this->createNotFoundException();
    }

    public function detailsAction($own_name, Request $request)
    {
        $own_name = Utils::convert_text($own_name);
        $em = $this->getDoctrine()->getManager();
        $user_ids = $em->getRepository('mycpBundle:user')->getIds($this);
        $locale = $this->get('translator')->getLocale();

        $own_name = str_replace('-', ' ', $own_name);
        $own_name = str_replace('  ', '-', $own_name);

        $ownership_array = $em->getRepository('mycpBundle:ownership')->getDetails($own_name, $locale, $user_ids["user_id"], $user_ids["session_id"]);

        if ($ownership_array['own_id'] == null) {
            return $this->redirect($this->generateUrl('frontend_search_ownership'));
        }

        $existNonNullValue = false;
        foreach (array_keys($ownership_array) as $key) {
            if ($ownership_array[$key] != null) {
                $existNonNullValue = true;
                break;
            }
        }

        if (!$existNonNullValue) {
            return $this->redirect($this->generateUrl('frontend_search_ownership'));
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
        $reservations = $em->getRepository('mycpBundle:generalReservation')->getReservationsByIdAccommodation($owner_id);

        $ownership = $em->getRepository('mycpBundle:ownership')->findOneBy(array('own_id' => $owner_id));

        $em->getRepository('mycpBundle:ownership')->registerVisit($owner_id);

        if ($ownership) {
            if ($ownership->getCountVisits() == null) {
                $ownership->setCountVisits(1);
            } else {
                $count = (int)$ownership->getCountVisits();
                $ownership->setCountVisits($count + 1);
            }
            $em->persist($ownership);
            $em->flush();
        }
        // $similar_houses = $em->getRepository('mycpBundle:ownership')->getByCategory($ownership_array['category'], null, $owner_id, $user_ids["user_id"], $user_ids["session_id"]);
        // $total_similar_houses = count($similar_houses);

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
        $start_date = (isset($post['top_reservation_filter_date_from'])) ? ($post['top_reservation_filter_date_from']) : (($session->get('search_arrival_date') != null && $session->get('search_arrival_date') instanceof \DateTime) ? $session->get('search_arrival_date') : 'now');
        $end_date = (isset($post['top_reservation_filter_date_to'])) ? ($post['top_reservation_filter_date_to']) : (($session->get('search_departure_date') != null && $session->get('search_departure_date') instanceof \DateTime) ? $session->get('search_departure_date') : '+2 day');

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

        $array_prices = array();
        $prices_dates = array();

        foreach ($rooms as $room) {
            foreach ($reservations as $reservation) {

                if ($reservation->getOwnResSelectedRoomId() == $room->getRoomId()) {

                    if ($start_timestamp <= $reservation->getOwnResReservationFromDate()->getTimestamp() &&
                        $end_timestamp >= $reservation->getOwnResReservationToDate()->getTimestamp() && $reservation->getOwnResStatus() == ownershipReservation::STATUS_RESERVED
                    ) {

                        $array_no_available[$room->getRoomId()] = $room->getRoomId();
                    }

                    if ($start_timestamp >= $reservation->getOwnResReservationFromDate()->getTimestamp() &&
                        $start_timestamp <= $reservation->getOwnResReservationToDate()->getTimestamp() &&
                        $end_timestamp >= $reservation->getOwnResReservationToDate()->getTimestamp() && $reservation->getOwnResStatus() == ownershipReservation::STATUS_RESERVED
                    ) {

                        $array_no_available[$room->getRoomId()] = $room->getRoomId();
                    }

                    if ($start_timestamp <= $reservation->getOwnResReservationFromDate()->getTimestamp() &&
                        $end_timestamp <= $reservation->getOwnResReservationToDate()->getTimestamp() &&
                        $end_timestamp >= $reservation->getOwnResReservationFromDate()->getTimestamp() && $reservation->getOwnResStatus() == ownershipReservation::STATUS_RESERVED
                    ) {

                        $array_no_available[$room->getRoomId()] = $room->getRoomId();
                    }

                    if ($start_timestamp >= $reservation->getOwnResReservationFromDate()->getTimestamp() &&
                        $end_timestamp <= $reservation->getOwnResReservationToDate()->getTimestamp() && $reservation->getOwnResStatus() == ownershipReservation::STATUS_RESERVED
                    ) {

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
                $price_subtotal += $array_prices[$flag_room];
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

        $brief_description = Utils::removeNewlines($ownership_array['brief_description']);

        $currentServiceFee = $em->getRepository("mycpBundle:serviceFee")->getCurrent();
        $allLanguages = $em->getRepository("mycpBundle:lang")->findBy(array('lang_active' => 1));

        $langCountry = array(
            'en' => 'en-US',
            'es' => 'es-ES',
            'de' => 'de-DE',
            'it' => 'it-IT',
            'fr' => 'fr-FR'
        );

        $mobileDetector = $this->get('mobile_detect.mobile_detector');
        if ($mobileDetector->isMobile()) {
            return $this->render('MyCpMobileFrontendBundle:ownership:ownershipDetails.html.twig', array(
                'avail_array_prices' => $avail_array_prices,
                'available_rooms' => $available_rooms,
                'price_subtotal' => $price_subtotal,
                'avail_array_prices' => $avail_array_prices,
                'array_prices' => $array_prices,
                'prices_dates' => $prices_dates,
                'ownership' => $ownership_array,
                'description' => $ownership_array['description'],
                'brief_description' => $brief_description,
                'automaticTranslation' => $ownership_array['autotomaticTranslation'],
                //'similar_houses' => array_slice($similar_houses, 0, 5),
                //'total_similar_houses' => $total_similar_houses,
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
                'can_comment' => $em->getRepository("mycpBundle:comment")->canComment($user_ids["user_id"], $owner_id),
                'can_public_comment' => $em->getRepository("mycpBundle:comment")->canPublicComment($user_ids["user_id"], $owner_id),
                'locale' => $locale,
                'real_category' => $real_category,
                'languages' => $languages,
                'keywords' => $ownership_array['keywords'],
                'locale' => $locale,
                'currentServiceFee' => $currentServiceFee,
                'lastPage' => $paginator->getLastPage(),
                'allLanguages' => $allLanguages,
                'langCountry' => $langCountry
            ));
        } else {

            return $this->render('FrontEndBundle:ownership:ownershipDetails.html.twig', array(
                'avail_array_prices' => $avail_array_prices,
                'available_rooms' => $available_rooms,
                'price_subtotal' => $price_subtotal,
                'avail_array_prices' => $avail_array_prices,
                'array_prices' => $array_prices,
                'prices_dates' => $prices_dates,
                'ownership' => $ownership_array,
                'description' => $ownership_array['description'],
                'brief_description' => $brief_description,
                'automaticTranslation' => $ownership_array['autotomaticTranslation'],
                //'similar_houses' => array_slice($similar_houses, 0, 5),
                //'total_similar_houses' => $total_similar_houses,
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
                'can_comment' => $em->getRepository("mycpBundle:comment")->canComment($user_ids["user_id"], $owner_id),
                'can_public_comment' => $em->getRepository("mycpBundle:comment")->canPublicComment($user_ids["user_id"], $owner_id),
                'locale' => $locale,
                'real_category' => $real_category,
                'languages' => $languages,
                'keywords' => $ownership_array['keywords'],
                'currentServiceFee' => $currentServiceFee,
                'lastPage' => $paginator->getLastPage(),
                'allLanguages' => $allLanguages,
                'langCountry' => $langCountry
            ));
        }
    }

    public function lastAddedListAction()
    {
        $em = $this->getDoctrine()->getManager();
        $user_ids = $em->getRepository('mycpBundle:user')->getIds($this);

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
            'current_page' => $page,
            'lastPage' => $paginator->getLastPage()
        ));
    }

    public function categoryAction($category)
    {
        $em = $this->getDoctrine()->getManager();
        $user_ids = $em->getRepository('mycpBundle:user')->getIds($this);

        $real_category = $category;

        if ($category == 'economy')
            $real_category = 'Económica';
        else if ($category == 'mid_range')
            $real_category = 'Rango medio';
        else if ($category == 'premium')
            $real_category = 'Premium';
        else
            return $this->redirect($this->generateUrl('frontend-welcome'));


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
            'current_page' => $page,
            'lastPage' => $paginator->getLastPage()
        ));
    }

    public function searchAction(Request $request, $text = null, $arriving_date = null, $departure_date = null, $guests = 0, $rooms = 0, $inmediate = "null", $order_price = 'null', $order_comments = 'null', $order_books = 'null')
    {
        $em = $this->getDoctrine()->getManager();
        $session = $this->get('request')->getSession();

        if ($session->get('search_order') == null || $session->get('search_order') == '')
            $session->set('search_order', OrderByHelper::DEFAULT_ORDER_BY);
        $rooms = ($rooms == "undefined") ? 1 : $rooms;

        $session->set('search_arrival_date', null);
        $session->set('search_departure_date', null);

        if ($session->get('inmediate') != null) {
            $inmediate = $session->get('inmediate');
        }
        $session->set('inmediate', $inmediate);
        $today = new \DateTime();
        $arrTransProv = array(
            'la-habana' => 'La Habana',
            'havana' => 'La Habana',
            'havanna' => 'La Habana',
            'havana' => 'La Habana',
            'lavana' => 'La Habana',
            'isla-de-la-juventud' => 'Isla de la Juventud',
            'isle-of-youth' => 'Isla de la Juventud',
            'insel-der-jugend' => 'Isla de la Juventud',
            'ile-de-la-jeunesse' => 'Isla de la Juventud',
            'isola-della-gioventu' => 'Isla de la Juventud',
        );

        $search_text = ($text != null && $text != '' && $text != $this->get('translator')->trans('PLACE_WATERMARK')) ? Utils::getTextFromNormalized($text) : null;

        if (array_key_exists($text, $arrTransProv)) {
            $search_text = $arrTransProv[$text];
        }

        $provinces = $em->getRepository('mycpBundle:province')->findOneBy(array('prov_name' => $search_text));
        $province_name = 'no';

        if ($provinces) {
            $province_name = $provinces->getProvName();
        }

        $search_guests = ($guests != null && $guests != '' && $guests != $this->get('translator')->trans('GUEST_WATERMARK')) ? $guests : "1";
        $search_rooms = ($rooms != null && $rooms != '' && $rooms != $this->get('translator')->trans('ROOM_WATERMARK')) ? $rooms : "1";
        $arrival = ($request->get('arrival') != null && $request->get('arrival') != "" && $request->get('arrival') != "null") ? $request->get('arrival') : $today->format('d-m-Y');

        $departure = null;
        if ($request->get('departure') != null && $request->get('departure') != "" && $request->get('departure') != "null")
            $departure = $request->get('departure');
        else if ($arrival != null) {
            $arrivalDateTime = \DateTime::createFromFormat("d-m-Y", $arrival);
            $departure = date_add($arrivalDateTime, date_interval_create_from_date_string("2 days"))->format('d-m-Y');
        } else
            $departure = date_add($today, date_interval_create_from_date_string("2 days"))->format('d-m-Y');

        $check_filters = $session->get("filter_array");
        $room_filter = $session->get("filter_room");

        $session->set("filter_array", $check_filters);
        $session->set("filter_room", $room_filter);
        $list = $em->getRepository('mycpBundle:ownership')->search($this, $search_text, $arrival, $departure, $search_guests, $search_rooms, $session->get('search_order'), $room_filter, $check_filters, $inmediate);
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
        $session->set('search_view_results', 'PHOTOS');
        $results = $em->getRepository('mycpBundle:ownership')->getSearchNumbers();

        $categories_own_list = $results["categories"];
        $types_own_list = $results["types"];
        $prices_own_list = $results["prices"];
        $statistics_own_list = $em->getRepository('mycpBundle:ownership')->getSearchStatistics();
        $awards = $em->getRepository('mycpBundle:award')->findAll();
        $mobileDetector = $this->get('mobile_detect.mobile_detector');
        if ($mobileDetector->isMobile()){
            return $this->render('@MyCpMobileFrontend/accommodations/accomodationDetails.html.twig', array(
                'inmediate' => $inmediate,
                'search_text' => $search_text,
                'search_guests' => $search_guests,
                'search_arrival_date' => $arrival,
                'search_departure_date' => $departure,
                'search_rooms' => $search_rooms,
                'owns_categories' => $categories_own_list,
                'owns_types' => $types_own_list,
                'owns_prices' => $prices_own_list,
                'order' => $session->get('search_order'),
                'view_results' => $session->get('search_view_results'),
                'own_statistics' => $statistics_own_list,
                'locale' => $this->get('translator')->getLocale(),
                'list' => $result_list,
                'items_per_page' => $items_per_page,
                'total_items' => $paginator->getTotalItems(),
                'cant_pages' => $paginator->getLastPage(),
                'current_page' => $page,
                'check_filters' => $check_filters,
                'show_paginator' => true,
                'awards' => $awards,
                "lastPage" => $paginator->getLastPage(),
                "inmediate" => $inmediate,
                "province_name" => $province_name
            ));
        }
        else{
        if ($check_filters != null)
            return $this->render('FrontEndBundle:ownership:searchOwnershipv2.html.twig', array(
                'inmediate' => $inmediate,
                'search_text' => $search_text,
                'search_guests' => $search_guests,
                'search_arrival_date' => $arrival,
                'search_departure_date' => $departure,
                'search_rooms' => $search_rooms,
                'owns_categories' => $categories_own_list,
                'owns_types' => $types_own_list,
                'owns_prices' => $prices_own_list,
                'order' => $session->get('search_order'),
                'view_results' => $session->get('search_view_results'),
                'own_statistics' => $statistics_own_list,
                'locale' => $this->get('translator')->getLocale(),
                'list' => $result_list,
                'items_per_page' => $items_per_page,
                'total_items' => $paginator->getTotalItems(),
                'cant_pages' => $paginator->getLastPage(),
                'current_page' => $page,
                'check_filters' => $check_filters,
                'show_paginator' => true,
                'awards' => $awards,
                "lastPage" => $paginator->getLastPage(),
                "inmediate" => $inmediate,
                "province_name" => $province_name
            ));
        else
            return $this->render('FrontEndBundle:ownership:searchOwnershipv2.html.twig', array(
                'inmediate' => $inmediate,
                'search_text' => $search_text,
                'search_guests' => $search_guests,
                'search_arrival_date' => $arrival,
                'search_departure_date' => $departure,
                'search_rooms' => $search_rooms,
                'owns_categories' => $categories_own_list,
                'owns_types' => $types_own_list,
                'owns_prices' => $prices_own_list,
                'order' => $session->get('search_order'),
                'view_results' => $session->get('search_view_results'),
                'own_statistics' => $statistics_own_list,
                'locale' => $this->get('translator')->getLocale(),
                'autocomplete_text_list' => $em->getRepository('mycpBundle:ownership')->autocompleteTextList(),
                'list' => $result_list,
                'items_per_page' => $items_per_page,
                'total_items' => $paginator->getTotalItems(),
                'cant_pages' => $paginator->getMaxPagerItems(),
                'current_page' => $page,
                'show_paginator' => true,
                'awards' => $awards,
                "lastPage" => $paginator->getLastPage(),
                "inmediate" => $inmediate,
                "province_name" => $province_name
            ));}
    }

    public function searchOrderResultsAction()
    {

        $request = $this->getRequest();
        $session = $request->getSession();
        $em = $this->getDoctrine()->getManager();

        if ($session->get('search_view_results') == null)
            $session->set('search_view_results', 'PHOTOS');

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

    public function searchChangeViewResultsAction()
    {
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

    public function researchAction()
    {
        $request = $this->getRequest();
        $session = $request->getSession();
        $page = 1;
        if (isset($_GET['page']))
            $page = $_GET['page'];

        $items_per_page = 20;
        if ($page == 1) {
            $em = $this->getDoctrine()->getManager();

            if ($session->get('search_order') == null || $session->get('search_order') == '')
                $session->set('search_order', OrderByHelper::DEFAULT_ORDER_BY);

            if ($session->get('search_view_results') == null || $session->get('search_view_results') == '')
                $session->set('search_view_results', 'PHOTOS');

            $guests = $request->request->get('guests');
            $rooms = $request->request->get('rooms');
            $arriving_date = $request->request->get('arrival');
            $departure_date = $request->request->get('departure');

            $session->set('search_arrival_date', null);
            $session->set('search_departure_date', null);
            $today = new \DateTime();

            $search_guests = ($guests != null && $guests != '' && $guests != $this->get('translator')->trans('GUEST_WATERMARK')) ? $guests : "1";
            $search_rooms = ($rooms != null && $rooms != '' && $rooms != $this->get('translator')->trans('ROOM_WATERMARK')) ? $rooms : "1";
            $search_arrival_date = ($arriving_date != null && $arriving_date != '' && $arriving_date != $this->get('translator')->trans('ARRIVAL_WATERMARK')) ? $arriving_date : $today->format('Y-m-d');

            $search_departure_date = null;
            if ($departure_date != null && $departure_date != "" && $departure_date != "null" && $departure_date != $this->get('translator')->trans('DEPARTURE_WATERMARK'))
                $search_departure_date = $departure_date;
            else if ($search_arrival_date != null) {
                $arrivalDateTime = \DateTime::createFromFormat("Y-m-d", $search_arrival_date);
                $search_departure_date = date_add($arrivalDateTime, date_interval_create_from_date_string("2 days"))->format('d-m-Y');
            } else
                $search_departure_date = date_add($today, date_interval_create_from_date_string("2 days"))->format('d-m-Y');

            $text = $request->request->get('text');

            $text = ($text == "") ? "null" : $text;

            $session->set('search_text', $text);
            $session->set('search_arrival_date', $search_arrival_date);
            $session->set('search_departure_date', $search_departure_date);
            $session->set('search_guests', $search_guests);
            $session->set('search_rooms', $search_rooms);
            $check_filters['own_category'] = $request->request->get('own_category');

            $orderPrice = $request->request->get('order_price');
            $orderComments = $request->request->get('order_comments');
            $orderBooks = $request->request->get('order_books');

            $check_filters = array();

            $check_filters['own_update_avaliable'] = $request->request->get('own_update_avaliable');
            $check_filters['own_reservation_type'] = $request->request->get('own_reservation_type');
            $check_filters['own_award'] = $request->request->get('own_award');
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
            $check_filters['own_inmediate_booking'] = ($request->request->get('own_inmediate_booking') == 'true' || $request->request->get('own_inmediate_booking') == '1') ? true : false;

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

            $inmediate = ($request->request->get('own_inmediate_booking2') == 'true' || $request->request->get('own_inmediate_booking2') == '1') ? 1 : null;
            $session->set('inmediate', $inmediate);
            $results_list = $em->getRepository('mycpBundle:ownership')->search($this, $session->get('search_text'), $session->get('search_arrival_date'), $session->get('search_departure_date'), $session->get('search_guests'), $session->get('search_rooms'), $session->get('search_order'), $room_filter, $check_filters, $inmediate);
            $own_ids = "0";
            foreach ($results_list as $own)
                $own_ids .= "," . $own['own_id'];

            $session->set('own_ids', $own_ids);

            $paginator = $this->get('ideup.simple_paginator');
            $paginator->setItemsPerPage($items_per_page);
            $list = $paginator->paginate($results_list)->getResult();

            $total_items = $paginator->getTotalItems();
            $cant_pages = $paginator->getLastPage();


            $session->set('list_result', $list);
            $session->set('total_items', $total_items);
            $session->set('cant_pages', $cant_pages);
        } else {
            $list = $session->get('list_result');
            $total_items = $session->get('total_items');
            $cant_pages = $session->get('cant_pages');
        }

        $mobileDetector = $this->get('mobile_detect.mobile_detector');
        if ($mobileDetector->isMobile()){
            $response = $this->renderView('@MyCpMobileFrontend/destination/cards.html.twig', array(
                'list' => $list,
                'items_per_page' => $items_per_page,
                'total_items' => $total_items,
                'cant_pages' => $cant_pages,
                'current_page' => $page,
                'list_preffix' => 'search',
                'show_paginator' => true
            ));
        }
        else {
            if ($session->get('search_view_results') != null && $session->get('search_view_results') == 'LIST')
                $response = $this->renderView('FrontEndBundle:ownership:searchListOwnership.html.twig', array(
                    'list' => $list,
                    'items_per_page' => $items_per_page,
                    'total_items' => $total_items,
                    'cant_pages' => $cant_pages,
                    'current_page' => $page,
                    'list_preffix' => 'search',
                    'show_paginator' => true
                ));
            else if ($session->get('search_view_results') != null && $session->get('search_view_results') == 'PHOTOS')
                $response = $this->renderView('FrontEndBundle:ownership:searchMosaicOwnershipv2.html.twig', array(
                    'list' => $list,
                    'cant_pages' => $cant_pages,
                    'items_per_page' => $items_per_page,
                    'total_items' => $total_items,
                    'current_page' => $page,
                    'list_preffix' => 'search',
                    'show_paginator' => true
                ));
            else if ($session->get('search_view_results') != null && $session->get('search_view_results') == 'MAP')
                $response = $this->renderView('FrontEndBundle:ownership:searchMapOwnership.html.twig', array(
                    'list' => $list,
                    'items_per_page' => $items_per_page,
                    'total_items' => $total_items,
                    'cant_pages' => $cant_pages,
                    'current_page' => $page,
                    'list_preffix' => 'search',
                    'show_paginator' => true
                ));

        }
        return new JsonResponse(array('html' => $response, 'cant_pages' => $cant_pages));

    }

    public function filterAction()
    {
        $request = $this->getRequest();
        $session = $request->getSession();
        $em = $this->getDoctrine()->getManager();

        if ($session->get('search_view_results') == null || $session->get('search_view_results') == '')
//            $session->set('search_view_results', 'LIST');
            $session->set('search_view_results', 'PHOTOS');

        $check_filters = array();
        $check_filters['own_reservation_type'] = $request->request->get('own_reservation_type');
        $check_filters['own_award'] = $request->request->get('own_award');
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
        $check_filters['own_inmediate_booking'] = ($request->request->get('own_inmediate_booking') == 'true' || $request->request->get('own_inmediate_booking') == '1') ? true : false;

        $inmediate = ($request->request->get('own_inmediate_booking2') == 'true' || $request->request->get('own_inmediate_booking2') == '1') ? 1 : null;

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
        $items_per_page = 20;
        $paginator->setItemsPerPage($items_per_page);
        $orderPrice = $request->request->get('order_price');
        $orderComments = $request->request->get('order_comments');
        $orderBooks = $request->request->get('order_books');
//        $orderBy='';
//        if($orderPrice!=''||$orderComments!=''||$orderBooks!='')
//        {
//            $orderBy=' ORDER BY';
//            if($orderPrice!='')
//                $orderBy.= ' o.own_minimum_price '.$orderPrice;
//            if($orderComments!=''){
//                if($orderPrice!='')
//                    $orderBy.=',';
//                $orderBy.= ' o.own_comments_total '.$orderComments;
//            }
//            if($orderBooks!=''){
//                if($orderPrice!=''||$orderComments!='')
//                    $orderBy.=',';
//                $orderBy.= ' count_reservations '.$orderBooks;
//            }
//            $orderBy.=', o.own_ranking DESC';
//
//        }
        $list = $paginator->paginate($em->getRepository('mycpBundle:ownership')->search($this, $session->get('search_text'), $session->get('search_arrival_date'), $session->get('search_departure_date'), $session->get('search_guests'), $session->get('search_rooms'), $session->get('search_order') ? $session->get('search_order') : null, $room_filter, $check_filters, $inmediate))->getResult();
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
            $response = $this->renderView('FrontEndBundle:ownership:searchMosaicOwnershipv2.html.twig', array(
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

    public function getFiltersStatisticsAction()
    {
        $request = $this->getRequest();
        $session = $request->getSession();
        $em = $this->getDoctrine()->getManager();
        $own_ids = $session->get('own_ids');

        $statisics = $em->getRepository('mycpBundle:ownership')->getSearchStatistics();

        $check_filters = array();

        //$check_filters['own_reservation_type'] = $request->request->get('own_reservation_type');
        $check_filters['own_category'] = $request->request->get('own_category');
        $check_filters['own_award'] = $request->request->get('own_award');
        $check_filters['own_inmediate_booking'] = $request->request->get('own_inmediate_booking');
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

        $results = $em->getRepository('mycpBundle:ownership')->getSearchNumbers();
        $categories_own_list = $results["categories"];//$em->getRepository('mycpBundle:ownership')->getOwnsCategories($own_ids);
        $types_own_list = $results["types"];//$em->getRepository('mycpBundle:ownership')->getOwnsTypes($own_ids);
        $prices_own_list = $results["prices"];//$em->getRepository('mycpBundle:ownership')->getOwnsPrices($own_ids);

        $response = $this->renderView('FrontEndBundle:ownership:filters.html.twig', array(
            'own_statistics' => $statisics,
            'check_filters' => $check_filters,
            'owns_categories' => $categories_own_list,
            'owns_types' => $types_own_list,
            'owns_prices' => $prices_own_list,
        ));

        return new Response($response, 200);
    }

    public function mapMarkersListAction()
    {
        $request = $this->getRequest();
        $session = $request->getSession();
        $em = $this->getDoctrine()->getManager();
        $own_ids = $session->get('own_ids');
        $list = $em->getRepository('mycpBundle:ownership')->getListByIds($own_ids);
        if (is_array($list)) {
            $result = array();

            foreach ($list as $own) {
                $prize = ($own->getOwnMinimumPrice()) * ($session->get('curr_rate') == null ? 1 : $session->get('curr_rate'));

                if ($own->getCompleteReservationMode()) {
                    $prize = ($own->getBookingModality()->getPrice()) * ($session->get('curr_rate') == null ? 1 : $session->get('curr_rate'));
                }

                $result[] = array('latitude' => $own->getOwnGeolocateX(),
                    'longitude' => $own->getOwnGeolocateY(),
                    'title' => $own->getOwnName(),
                    'content' => $this->get('translator')->trans('FROM_PRICES') . ($session->get("curr_symbol") != null ? " " . $session->get('curr_symbol') . " " : " $ ") . $prize . " " . strtolower($this->get('translator')->trans("BYNIGHTS_PRICES")),
                    'image' => $this->container->get('templating.helper.assets')->getUrl('uploads/ownershipImages/thumbnails/' . $em->getRepository('mycpBundle:ownership')->getOwnershipPhoto($own->getOwnId())), //$this->get_ownership_photo($own->getOwnId()),
                    'id' => $own->getOwnId(),
                    'url' => $this->generateUrl('frontend_details_ownership', array('own_name' => $own->getOwnName())),
                    'destination' => ($own->getOwnDestination() != null) ? array('geolocate_x' => $own->getOwnDestination()->getDesGeolocateX(), 'geolocate_y' => $own->getOwnDestination()->getDesGeolocateY()) : null);
            }
        }

        return new Response(json_encode($result), 200);
    }

    public function updateRatingAction($ownid)
    {
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

    function addFavoriteOwnershipAction($id_ownership)
    {
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
                $string_favorites .= $id_ownership . '*';
            }
        } else
            $string_favorites = $id_ownership . '*';


        setcookie("mycp_favorites_ownerships", $string_favorites, time() + 3600);

        //var_dump($_COOKIE["mycp_favorites_ownerships"]);
        return $this->redirect($this->generateUrl('frontend_details_ownership', array('ownerid' => $id_ownership)));
    }

    public function mapDetailsOwnershipAction($ownGeolocateX, $ownGeolocateY, $ownName, $description, $image)
    {
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

    public function mapResizedAction($in_searcher = 'true', $destination_name = null)
    {
        $request = $this->getRequest();
        $session = $request->getSession();
        $em = $this->getDoctrine()->getManager();
        $markers_id_list = $request->request->get('own_ids');
        $users_id = $em->getRepository('mycpBundle:user')->getIds($this);

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

    public function votedBestListAction()
    {
        $em = $this->getDoctrine()->getManager();

        $session = $this->getRequest()->getSession();
        $session->set('search_order', OrderByHelper::SEARCHER_BEST_VALUED);

        $list = $em->getRepository('mycpBundle:ownership')->search($this, null, null, null, 1, 1, $session->get('search_order'), false, null, 0, null, null, false);
        $paginator = $this->get('ideup.simple_paginator');
        $items_per_page = 20;
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
            $session->set('search_view_results', 'PHOTOS');

        $results = $em->getRepository('mycpBundle:ownership')->getSearchNumbers();
        $categories_own_list = $results["categories"];//$em->getRepository('mycpBundle:ownership')->getOwnsCategories($own_ids);
        $types_own_list = $results["types"];//$em->getRepository('mycpBundle:ownership')->getOwnsTypes($own_ids);
        $prices_own_list = $results["prices"];//$em->getRepository('mycpBundle:ownership')->getOwnsPrices($own_ids);
        $statistics_own_list = $em->getRepository('mycpBundle:ownership')->getSearchStatistics();
        $awards = $em->getRepository('mycpBundle:award')->findAll();
        return $this->render('FrontEndBundle:ownership:searchOwnershipv2.html.twig', array(
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
            'autocomplete_text_list' => $em->getRepository('mycpBundle:ownership')->autocompleteTextList(),
            'items_per_page' => $items_per_page,
            'total_items' => $paginator->getTotalItems(),
            'current_page' => $page,
            'list_preffix' => 'voted_best',
            'awards' => $awards,
            'cant_pages' => $items_per_page,
            'lastPage' => $paginator->getLastPage()
        ));
    }

    public function typeListAction($type)
    {
        $em = $this->getDoctrine()->getManager();

        $session = $this->getRequest()->getSession();
        $session->set('search_order', OrderByHelper::SEARCHER_BEST_VALUED);

        $filters = array();

        switch ($type) {
            case "villa-con-piscina":
            case "pool-villa":
            case "villa-mit-swimmbad":
                $type = "villa-con-piscina";
                break;
            case "apartamento":
            case "apartament":
            case "wohnung":
                $type = "apartamento";
                break;
            case "propiedad-completa":
            case "full-property":
            case "ganze-unterkunft":
                $type = "propiedad-completa";
                break;
        }

        $filters['own_type'] = array(str_replace("-", " ", ucfirst($type)));
        //dump($filters); die;
        $list = $em->getRepository('mycpBundle:ownership')->search($this, null, null, null, '1', '1', $session->get('search_order'), false, $filters, 0, null, null, false);
        $paginator = $this->get('ideup.simple_paginator');
        $items_per_page = 20;
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
            $session->set('search_view_results', 'PHOTOS');

        $results = $em->getRepository('mycpBundle:ownership')->getSearchNumbers();
        $categories_own_list = $results["categories"];//$em->getRepository('mycpBundle:ownership')->getOwnsCategories($own_ids);
        $types_own_list = $results["types"];//$em->getRepository('mycpBundle:ownership')->getOwnsTypes($own_ids);
        $prices_own_list = $results["prices"];//$em->getRepository('mycpBundle:ownership')->getOwnsPrices($own_ids);
        $statistics_own_list = $em->getRepository('mycpBundle:ownership')->getSearchStatistics();

        $check_filters = array();
        $check_filters['own_reservation_type'] = null;
        $check_filters['own_award'] = null;
        $check_filters['own_category'] = null;
        $check_filters['own_type'] = array(ucfirst($type));
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
        $awards = $em->getRepository('mycpBundle:award')->findAll();
        $page = 1;
        if (isset($_GET['page']))
            $page = $_GET['page'];

        return $this->render('FrontEndBundle:ownership:searchOwnershipv2.html.twig', array(
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
            'autocomplete_text_list' => $em->getRepository('mycpBundle:ownership')->autocompleteTextList(),
            'check_filters' => $check_filters,
            'list_preffix' => 'search',
            'list' => $search_results_list,
            'awards' => $awards,
            'current_page' => $page,
            'cant_pages' => $paginator->getLastPage()
        ));
    }

    public function topRatedCallbackAction()
    {
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

        $statistics = $em->getRepository("mycpBundle:ownership")->top20Statistics();

        $category = $session->get("top_rated_category");
        $paginator = $this->get('ideup.simple_paginator');
        $items_per_page = 9;
        $paginator->setItemsPerPage($items_per_page);

        $page = 1;
        $page = $request->request->get('page');
        //$_GET['page'] = $page;

        if ($page == 1) {
            $list = $em->getRepository('mycpBundle:ownership')->top20($locale, ((strtolower($category) != "todos") ? $category : null));
            $top_rated_list = $list->getResult();
            $session->set("top_rated_list", $top_rated_list);
        } else {
            $top_rated_list = $session->get("top_rated_list");
        }
        $own_top20_list = $paginator->paginate($top_rated_list)->getResult();
        $total_item = $paginator->getTotalItems();

        $response = $this->renderView('FrontEndBundle:ownership:homeTopRatedList.html.twig', array(
            'own_top20_list' => $own_top20_list,
            'top_rated_items_per_page' => $items_per_page,
            'top_rated_total_items' => $total_item,
            'current_page' => $page,
            'premium_total' => $statistics['premium_total'],
            'midrange_total' => $statistics['midrange_total'],
            'economic_total' => $statistics['economic_total']
        ));

        return new Response($response, 200);
    }

    public function topRatedChangeCategoryCallbackAction()
    {
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

        $statistics = $em->getRepository("mycpBundle:ownership")->top20Statistics();
        $paginator = $this->get('ideup.simple_paginator');
        $items_per_page = 4 * $session->get("top_rated_show_rows");
        $paginator->setItemsPerPage($items_per_page);

        $list = $em->getRepository('mycpBundle:ownership')->top20($locale, ((strtolower($category) != "todos") ? $category : null));
        $own_top20_list = $paginator->paginate($list)->getResult();
        $page = 1;
        if (isset($_GET['page']))
            $page = $_GET['page'];

        $response = $this->renderView('FrontEndBundle:ownership:homeTopRatedList.html.twig', array(
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

    public function orangeSearchBarAction()
    {
        $em = $this->getDoctrine()->getManager();
        $session = $this->getRequest()->getSession();

        return $this->render('FrontEndBundle:ownership:orangeSearchBar.html.twig', array(
            'locale' => $this->get('translator')->getLocale(),
            'autocomplete_text_list' => $em->getRepository('mycpBundle:ownership')->autocompleteTextList(),
            'arrival_date' => $session->get("search_arrival_date"),
            'departure_date' => $session->get("search_departure_date")
        ));
    }

    public function autoCompleteTextListAction()
    {
        $em = $this->getDoctrine()->getManager();
        $text = $em->getRepository('mycpBundle:ownership')->autocompleteTextList();

        return new JsonResponse(array('autocompletetext' => $text));
    }

    public function ownersPhotosAction($ownership_id, $photo)
    {
        if ($photo == null) {
            $em = $this->getDoctrine()->getManager();
            $photo = $em->getRepository('mycpBundle:userCasa')->getOwnersPhotos($ownership_id);
        }

        return $this->render('FrontEndBundle:ownership:ownersPhotosOwnership.html.twig', array(
            'owner_photo' => $photo
        ));
    }

    public function lastOwnsVisitedAction($exclude_ownership_id = null)
    {
        $em = $this->getDoctrine()->getManager();
        $user_ids = $em->getRepository('mycpBundle:user')->getIds($this);
        $history_owns = $em->getRepository('mycpBundle:userHistory')->getListEntity($user_ids, true, 10, $exclude_ownership_id);
        $history_owns_photos = $em->getRepository('mycpBundle:ownership')->getPhotosArrayFromArray($history_owns, "ownId");

        return $this->render('FrontEndBundle:ownership:historyOwnership.html.twig', array(
            'history_list' => $history_owns,
            'photos' => $history_owns_photos
        ));
    }

    public function nearByDestinationsAction($municipality_id, $province_id)
    {
        $em = $this->getDoctrine()->getManager();
        $users_id = $em->getRepository('mycpBundle:user')->getIds($this);

        $destinations = $em->getRepository('mycpBundle:destination')->filter($municipality_id, $province_id, null, null, 3);

        if (count($destinations) < 3)
            $destinations = $em->getRepository('mycpBundle:destination')->getPopularDestinations(3, $users_id["user_id"], $users_id["session_id"], $this->get('translator')->getLocale());

        return $this->render('FrontEndBundle:ownership:nearByDestinationsOwnership.html.twig', array(
            'destinations' => $destinations
        ));
    }

    public function getCarrouselLastAddedCallbackAction()
    {
        $request = $this->getRequest();
        $em = $this->getDoctrine()->getManager();
        $user_ids = $em->getRepository('mycpBundle:user')->getIds($this);
        $last_added = $em->getRepository('mycpBundle:ownership')->lastAdded(12, $user_ids['user_id'], $user_ids['session_id']);

        $response = $this->renderView('FrontEndBundle:public:homeCarrouselAccommodationsList.html.twig', array(
            'list' => $last_added,
            'list_preffix' => "lastAdded",
            "moreUrl" => $this->generateUrl("frontend_last_added_ownership"),
            "sliderId" => "th-last-carousel"
        ));

        return new Response($response, 200);
    }

    public function getCarrouselByCategoryCallbackAction()
    {
        $request = $this->getRequest();
        $em = $this->getDoctrine()->getManager();
        $user_ids = $em->getRepository('mycpBundle:user')->getIds($this);
        $category = $request->get("category");
        $elementId = $request->get("elementId");
        $realCategory = $request->get("realCategory");

        $list = $em->getRepository('mycpBundle:ownership')->getByCategory($category, 12, null, $user_ids['user_id'], $user_ids['session_id']);

        $response = $this->renderView('FrontEndBundle:public:homeCarrouselAccommodationsList.html.twig', array(
            'list' => $list,
            'list_preffix' => $elementId,
            "moreUrl" => $this->generateUrl("frontend_category_ownership", array("category" => $realCategory)),
            "sliderId" => "th-" . $elementId . "-carousel"
        ));

        return new Response($response, 200);
    }

    public function getThumbnailForSearcherAction($photo, $title)
    {
        list($width, $height) = getimagesize(realpath("uploads/ownershipImages/" . $photo));

        return $this->render('FrontEndBundle:ownership:searchImage.html.twig', array(
            'id' => uniqid('photo-'),
            'title' => $title,
            'photo' => $photo,
            'taller' => ($height > $width)
        ));
    }

    /**
     * @return Response
     */
    public function showModalOwnerShipCalendarAction()
    {
        $request = $this->getRequest();
        $em = $this->getDoctrine()->getManager();
        $ownership = $em->getRepository('mycpBundle:ownership')->find($request->get('idOwn'));
        $session = $this->getRequest()->getSession();

        $bookingModality = $ownership->getBookingModality();

        $hasCompleteReservation = ($bookingModality != null and $bookingModality->getBookingModality()->getName() == bookingModality::COMPLETE_RESERVATION_BOOKING);

        $locale = $this->get('translator')->getLocale();
        $currentServiceFee = $em->getRepository("mycpBundle:serviceFee")->getCurrent();
        $ownership_array = array();
        $ownership_array['own_id'] = $ownership->getOwnId();
        $ownership_array['ownname'] = $ownership->getOwnName();
        $ownership_array['OwnInmediateBooking2'] = $ownership->isOwnInmediateBooking2();
        $mobileDetector = $this->get('mobile_detect.mobile_detector');
        if ($mobileDetector->isMobile()) {
            return $this->render('MyCpMobileFrontendBundle:ownership:calendar.html.twig', array('ownership' => $ownership_array, 'locale' => $locale, 'currentServiceFee' => $currentServiceFee, "hasCompleteReservation" => $hasCompleteReservation));
        } else {
            return $this->render('FrontEndBundle:ownership:modal_ownership_calendar.html.twig', array('ownership' => $ownership_array, 'locale' => $locale, 'currentServiceFee' => $currentServiceFee, "hasCompleteReservation" => $hasCompleteReservation));
        }
    }

    /**
     * @param Request $request
     */
    public function searchByDestinationAction(Request $request)
    {
        $prov_array = $request->request->get('prov_array');
        $em = $this->getDoctrine()->getManager();
        $user_ids = $em->getRepository('mycpBundle:user')->getIds($this);
        $user_id = $user_ids['user_id'];
        $session_id = $user_ids['session_id'];
        $where = "";
        if (count($prov_array)) {
            $i = 0;
            foreach ($prov_array as $item) {
                if ($i == 0)
                    $where .= "prov.prov_id=$item";
                else
                    $where .= " OR prov.prov_id=$item ";
                $i++;
            }
        }
        $query_string = SearchUtils::getBasicQuery(false, $user_id, $session_id);
        $query_string = $query_string['query'];
        if ($where != '')
            $query_string .= " AND o.own_inmediate_booking_2=1 AND $where ";
        else
            $query_string .= " AND o.own_inmediate_booking_2=1 ";
        $owns_id = "0";
        $reservations = SearchUtils::ownNotAvailable($em);
        foreach ($reservations as $res)
            $owns_id .= "," . $res["own_id"];
        $query_string = $query_string . " AND o.own_id NOT IN ($owns_id)";
        $query = $em->createQuery($query_string);
        if ($session_id != null) {
            $query->setParameter('session_id', $session_id);
        }
        if ($user_id != null) {
            $query->setParameter('user_id', $user_id);
        }
        $result = $query->setFirstResult(0)->setMaxResults(6)->getResult();

        $response = $this->renderView('FrontEndBundle:destination:listOwnerShipMap.html.twig', array(
            'list' => $result,
        ));

        return new Response($response, 200);
    }

}
