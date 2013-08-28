<?php

namespace MyCp\frontEndBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

/* use Symfony\Component\HttpFoundation\Request;
  use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
  use Symfony\Component\Security\Core\SecurityContext; */

class ownershipController extends Controller {

    public function detailsAction($owner_id, Request $request) {

        $em = $this->getDoctrine()->getEntityManager();

        $ownership = $em->getRepository('mycpBundle:ownership')->find($owner_id);
        $general_reservations = $em->getRepository('mycpBundle:generalReservation')->findBy(array('gen_res_own_id'=>$owner_id));
        $reservations=array();
        foreach($general_reservations as $gen_res)
        {
            $own_reservations=$em->getRepository('mycpBundle:ownershipReservation')->findBy(array('own_res_gen_res_id'=>$gen_res->getGenResId()));
            foreach($own_reservations as $own_res)
            {
                array_push($reservations,$own_res);
            }
        }


        $locale = $this->get('translator')->getLocale();
        $ownership_description = $em->getRepository('mycpBundle:ownershipDescriptionLang')->findOneBy(array(
            'odl_id_lang' => $em->getRepository('mycpBundle:lang')->findOneBy(array('lang_code' => $locale)),
            'odl_ownership' => $owner_id
        ));

        $similar_houses = $em->getRepository('mycpBundle:ownership')->getByCategory($ownership->getOwnCategory(), 5, $owner_id);
        $total_similar_houses = count($em->getRepository('mycpBundle:ownership')->getByCategory($ownership->getOwnCategory(), null, $owner_id));
        $photos_similar_houses = $this->getArrayFotos($similar_houses);
        $comments = $em->getRepository('mycpBundle:comment')->get_comments($owner_id);

        $rooms = $em->getRepository('mycpBundle:room')->findBy(array('room_ownership' => $owner_id));
        $friends = array();

        $own_photos = $em->getRepository('mycpBundle:ownership')->getPhotos($owner_id);
        $own_photo_descriptions = $em->getRepository('mycpBundle:ownership')->getPhotoDescription($own_photos, $locale);

        $start_timestamp = mktime(0,0,0,date('m'),date('d'),date('Y'));
        $end_timestamp = strtotime ('+6 day') ;
        $post = $request->request->getIterator()->getArrayCopy();

        if($this->getRequest()->getMethod()=='POST')
        {
            $reservation_filter_date_from=$post['reservation_filter_date_from'];
            $reservation_filter_date_from=explode('/',$reservation_filter_date_from);
            $start_timestamp = mktime(0,0,0,$reservation_filter_date_from[1],$reservation_filter_date_from[0],$reservation_filter_date_from[2]);

            $reservation_filter_date_to=$post['reservation_filter_date_to'];
            $reservation_filter_date_to=explode('/',$reservation_filter_date_to);
            $end_timestamp = mktime(0,0,0,$reservation_filter_date_to[1],$reservation_filter_date_to[0],$reservation_filter_date_to[2]);
        }

        $service_time= $this->get('Time');
        $array_dates=$service_time->dates_between($start_timestamp,$end_timestamp);


        $array_no_available=array();
        $no_available_days=array();

        $service_time= $this->get('time');
        $array_prices=array();
        $prices_dates=array();

        foreach($rooms as $room)
        {
            foreach($reservations as $reservation)
            {

                if($reservation->getOwnResSelectedRoomId() == $room->getRoomId())
                {

                    if($start_timestamp <= $reservation->getOwnResReservationFromDate()->getTimestamp() &&
                        $end_timestamp >= $reservation->getOwnResReservationToDate()->getTimestamp())
                    {

                        $array_no_available[$room->getRoomId()]=$room->getRoomId();
                    }

                    if($start_timestamp >= $reservation->getOwnResReservationFromDate()->getTimestamp() &&
                        $start_timestamp <= $reservation->getOwnResReservationToDate()->getTimestamp() &&
                        $end_timestamp >= $reservation->getOwnResReservationToDate()->getTimestamp())
                    {

                        $array_no_available[$room->getRoomId()]=$room->getRoomId();
                    }

                    if($start_timestamp <= $reservation->getOwnResReservationFromDate()->getTimestamp() &&
                        $end_timestamp <= $reservation->getOwnResReservationToDate()->getTimestamp() &&
                        $end_timestamp >= $reservation->getOwnResReservationFromDate()->getTimestamp())
                    {

                        $array_no_available[$room->getRoomId()]=$room->getRoomId();
                    }

                    if($start_timestamp >= $reservation->getOwnResReservationFromDate()->getTimestamp() &&
                        $end_timestamp <= $reservation->getOwnResReservationToDate()->getTimestamp())
                    {

                        $array_no_available[$room->getRoomId()]=$room->getRoomId();
                    }

                    $array_numbers_check=array();
                    $cont_numbers=1;
                    foreach($array_dates as $date)
                    {

                        if($date >=$reservation->getOwnResReservationFromDate()->getTimestamp() &&  $date <=$reservation->getOwnResReservationToDate()->getTimestamp())
                        {
                            array_push($array_numbers_check,$cont_numbers);
                        }
                        $cont_numbers++;
                    }
                    array_push($no_available_days,array(
                        $room->getRoomId()=>$room->getRoomId(),
                        'check'=>$array_numbers_check
                    ));

                }

            }
            $total_price_room=0;
            $prices_dates_temp=array();
            for($a=0; $a < count($array_dates)-1; $a++)
            {

                $season=$service_time->season_by_date($array_dates[$a]);
                if($season =='top')
                {
                    $total_price_room += $room->getRoomPriceUpTo();
                    array_push($prices_dates_temp,$room->getRoomPriceUpTo());

                }
                else
                {
                    $total_price_room += $room->getRoomPriceDownTo();
                    array_push($prices_dates_temp,$room->getRoomPriceDownTo());
                }
                //var_dump($season);
            }
            array_push($array_prices,$total_price_room);
            array_push($prices_dates,$prices_dates_temp);
        }

        $no_available_days_ready=array();
        foreach($no_available_days as $item)
        {
            $keys=array_keys($item);
            if(!isset($no_available_days_ready[$item[$keys[0]]]))
                $no_available_days_ready[$item[$keys[0]]]=array();
            $no_available_days_ready[$item[$keys[0]]]=array_merge($no_available_days_ready[$item[$keys[0]]],$item['check']);

        }
        //var_dump($no_available_days_ready);
        //exit();


        $array_dates_keys=array();
        $count=1;
        foreach ($array_dates as $date)
        {
            $array_dates_keys[$count]=array('day_number'=>date('d', $date),'day_name'=>date('D', $date));
            $count++;
        }
        if($this->getRequest()->getMethod()=='POST')
        {}
        else
            array_pop($array_dates_keys);
        //var_dump($prices_dates); exit();
        return $this->render('frontEndBundle:ownership:ownershipDetails.html.twig', array(
            'array_prices'=>$array_prices,
            'prices_dates'=>$prices_dates,
            'ownership' => $ownership,
            'description' => $ownership_description,
            'similar_houses' => $similar_houses,
            'total_similar_houses' => $total_similar_houses,
            'photos_similar_houses' => $photos_similar_houses,
            'comments' => $comments,
            'friends' => $friends,
            'show_comments_and_friends' => count($comments) + count($friends),
            'rooms' => $rooms,
            'gallery_photos' => $own_photos,
            'gallery_photo_descriptions' => $own_photo_descriptions,
            'is_in_favories' => $this->is_ownership_in_cookie($owner_id),
            'array_dates'=>$array_dates_keys,
            'post'=>$post,
            'reservations'=>$array_no_available,
            'no_available_days'=>$no_available_days_ready
        ));
    }

    public function last_added_listAction() {
        $em = $this->getDoctrine()->getEntityManager();
        $last_added_own_list = $em->getRepository('mycpBundle:ownership')->lastAdded();

        $last_added_own_photos = $this->getArrayFotos($last_added_own_list);
        $last_added_own_rooms = $this->getArrayRooms($last_added_own_list);

        return $this->render('frontEndBundle:ownership:lastAddedOwnership.html.twig', array(
                    'list' => $last_added_own_list,
                    'photos' => $last_added_own_photos,
                    'rooms' => $last_added_own_rooms
                ));
    }

    public function categoryAction($category) {
        $em = $this->getDoctrine()->getEntityManager();
        $real_category = $category;

        if ($category == 'economy')
            $real_category = 'Económica';
        else if ($category == 'rango_medio')
            $real_category = 'Rango medio';
        else if ($category == 'premium')
            $real_category = 'Premium';

        $list = $em->getRepository('mycpBundle:ownership')->getByCategory($real_category);

        $photos = $this->getArrayFotos($list);
        $rooms = $this->getArrayRooms($list);

        return $this->render('frontEndBundle:ownership:categoryListOwnership.html.twig', array(
            'category' => $category,
            'title' => str_replace(' ', '_', $category),
            'list' => $list,
            'photos' => $photos,
            'rooms' => $rooms
        ));
    }

    public function searchAction($text = null, $arriving_date = null, $departure_date = null, $guests = 1, $rooms = 1) {
        $em = $this->getDoctrine()->getEntityManager();

        $session = $this->getRequest()->getSession();

        if ($session->get('search_order') == null || $session->get('search_order') == '')
            $session->set('search_order', 'PRICE_LOW_HIGH');

        $search_text = ($text != null && $text != '' && $text != $this->get('translator')->trans('PLACE_WATERMARK')) ? $text : null;
        $search_guests = ($guests != null && $guests != '' && $guests != $this->get('translator')->trans('GUEST_WATERMARK')) ? $guests : "1";
        $search_rooms = ($rooms != null && $rooms != '' && $rooms != $this->get('translator')->trans('ROOM_WATERMARK')) ? $rooms : "1";
        $arrival = ($arriving_date != null && $arriving_date != '' && $arriving_date != $this->get('translator')->trans('ARRIVAL_WATERMARK')) ? $arriving_date : null;
        $departure = ($departure_date != null && $departure_date != '' && $departure_date != $this->get('translator')->trans('DEPARTURE_WATERMARK')) ? $departure_date : null;

        $search_results_list = $em->getRepository('mycpBundle:ownership')->search($search_text, $arrival, $departure, $search_guests, $search_rooms, $session->get('search_order'));

        $session->set('search_text', $search_text);
        $session->set('search_arrival_date', $arrival);
        $session->set('search_departure_date', $departure);
        $session->set('search_guests', $search_guests);
        $session->set('search_rooms', $search_rooms);

        $own_ids = "0";

        foreach ($search_results_list as $own)
            $own_ids .= "," . $own->getOwnId();

        $session->set('own_ids', $own_ids);

        if ($session->get('search_view_results') == null || $session->get('search_view_results') == '')
            $session->set('search_view_results', 'LIST');

        $search_results_photos = $this->getArrayFotos($search_results_list);
        $search_results_rooms = $this->getArrayRooms($search_results_list);

        $categories_own_list = $em->getRepository('mycpBundle:ownership')->getOwnsCategories($own_ids);
        $types_own_list = $em->getRepository('mycpBundle:ownership')->getOwnsTypes($own_ids);
        $prices_own_list = $em->getRepository('mycpBundle:ownership')->getOwnsPrices($own_ids);
        $statistics_own_list = $em->getRepository('mycpBundle:ownership')->getSearchStatistics($search_results_list);

        return $this->render('frontEndBundle:ownership:searchOwnership.html.twig', array(
            'search_text' => $search_text,
            'search_guests' => $search_guests,
            'search_arrival_date' => $arrival,
            'search_departure_date' => $departure,
            'list' => $search_results_list,
            'photos' => $search_results_photos,
            'owns_categories' => $categories_own_list,
            'owns_types' => $types_own_list,
            'owns_prices' => $prices_own_list,
            'view_results' => $session->get('search_view_results'),
            'order' => $session->get('search_order'),
            'rooms' => $search_results_rooms,
            'own_statistics' => $statistics_own_list,
            'locale' => $this->get('translator')->getLocale(),
            'autocomplete_text_list' => $this->autocomplete_text_list()
        ));
    }

    public function search_order_resultsAction() {

        $request = $this->getRequest();
        $session = $request->getSession();
        $em = $this->getDoctrine()->getEntityManager();
        if ($request->getMethod() == 'POST') {
            $order = $request->request->get('order');
            $session->set('search_order', $order);
            $result_list = $em->getRepository('mycpBundle:ownership')->search($session->get('search_text'), $session->get('search_arrival_date'), $session->get('search_departure_date'), $session->get('search_guests'),$session->get('search_rooms'), $session->get('search_order'));

            //$own_ids = $session->get('own_ids');

            $photos_list = $this->getArrayFotos($result_list);
            $rooms_list = $this->getArrayRooms($result_list);

            if ($session->get('search_view_results') != null && $session->get('search_view_results') == 'LIST')
                $response = $this->renderView('frontEndBundle:ownership:searchListOwnership.html.twig', array(
                    'list' => $result_list,
                    'photos' => $photos_list,
                    'rooms' => $rooms_list
                ));
            else if ($session->get('search_view_results') != null && $session->get('search_view_results') == 'PHOTOS')
                $response = $this->renderView('frontEndBundle:ownership:searchMosaicOwnership.html.twig', array(
                    'list' => $result_list,
                    'photos' => $photos_list,
                    'rooms' => $rooms_list
                ));
            else if ($session->get('search_view_results') != null && $session->get('search_view_results') == 'MAP')
                $response = $this->renderView('frontEndBundle:ownership:searchMapOwnership.html.twig', array(
                    'list' => $result_list,
                    'photos' => $photos_list,
                    'rooms' => $rooms_list
                ));

            return new Response($response, 200);
        }
    }

    public function search_change_view_resultsAction() {
        $request = $this->getRequest();
        $session = $request->getSession();
        $em = $this->getDoctrine()->getEntityManager();
        $view = $request->request->get('view');
        $session->set('search_view_results', $view);
        $own_ids = $session->get('own_ids');

        if ($own_ids != null && $own_ids != '' && $own_ids != 'null')
            $results_list = $em->getRepository('mycpBundle:ownership')->getListByIds($own_ids);
        else
            $results_list = $em->getRepository('mycpBundle:ownership')->search($session->get('search_text'), $session->get('search_arrival_date'), $session->get('search_departure_date'), $session->get('search_guests'),$session->get('search_rooms'), $session->get('search_order'));

        $photos_list = $this->getArrayFotos($results_list);
        $rooms_list = $this->getArrayRooms($results_list);

        if ($view != null && $view == 'LIST') {
            $response = $this->renderView('frontEndBundle:ownership:searchListOwnership.html.twig', array(
                'list' => $results_list,
                'photos' => $photos_list,
                'rooms' => $rooms_list
            ));
        } else if ($session->get('search_view_results') != null && $session->get('search_view_results') == 'PHOTOS')
            $response = $this->renderView('frontEndBundle:ownership:searchMosaicOwnership.html.twig', array(
                'list' => $results_list,
                'photos' => $photos_list,
                'rooms' => $rooms_list
            ));
        else if ($session->get('search_view_results') != null && $session->get('search_view_results') == 'MAP')
            $response = $this->renderView('frontEndBundle:ownership:searchMapOwnership.html.twig', array(
                'list' => $results_list,
                'photos' => $photos_list,
                'rooms' => $rooms_list
            ));

        return new Response($response, 200);
    }

    public function researchAction() {
        $request = $this->getRequest();
        $session = $request->getSession();
        $em = $this->getDoctrine()->getEntityManager();

        if ($session->get('search_order') == null || $session->get('search_order') == '')
            $session->set('search_order', 'PRICE_LOW_HIGH');

        if ($session->get('search_view_results') == null || $session->get('search_view_results') == '')
            $session->set('search_view_results', 'LIST');

        //$municipalities = array();
        // $mun_text = "";

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

            $session->set('search_text', $text);
            $session->set('search_arrival_date', $search_arrival_date);
            $session->set('search_departure_date', $search_departure_date);
            $session->set('search_guests', $search_guests);
            $session->set('search_rooms', $search_rooms);

            $list = $em->getRepository('mycpBundle:ownership')->search($session->get('search_text'), $session->get('search_arrival_date'), $session->get('search_departure_date'), $session->get('search_guests'),$session->get('search_rooms'), $session->get('search_order'));

            $own_ids = "0";

            foreach ($list as $own)
                $own_ids .= "," . $own->getOwnId();

            $session->set('own_ids', $own_ids);

            $photos = $this->getArrayFotos($list);
            $rooms = $this->getArrayRooms($list);

            if ($session->get('search_view_results') != null && $session->get('search_view_results') == 'LIST')
                $response = $this->renderView('frontEndBundle:ownership:searchListOwnership.html.twig', array(
                    'list' => $list,
                    'photos' => $photos,
                    'rooms' => $rooms
                ));
            else if ($session->get('search_view_results') != null && $session->get('search_view_results') == 'PHOTOS')
                $response = $this->renderView('frontEndBundle:ownership:searchMosaicOwnership.html.twig', array(
                    'list' => $list,
                    'photos' => $photos,
                    'rooms' => $rooms
                ));
            else if ($session->get('search_view_results') != null && $session->get('search_view_results') == 'MAP')
                $response = $this->renderView('frontEndBundle:ownership:searchMapOwnership.html.twig', array(
                    'list' => $list,
                    'photos' => $photos,
                    'rooms' => $rooms
                ));

            return new Response($response, 200);
        }
    }

    public function filterAction() {
        $request = $this->getRequest();
        $session = $request->getSession();
        $em = $this->getDoctrine()->getEntityManager();

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
            $check_filters['room_courtyard']
        );

        $list = $em->getRepository('mycpBundle:ownership')->search($session->get('search_text'), $session->get('search_arrival_date'), $session->get('search_departure_date'), $session->get('search_guests'),$session->get('search_rooms'), $session->get('search_order'), $room_filter, $check_filters);

        $own_ids = "0";

        foreach ($list as $own)
            $own_ids .= "," . $own->getOwnId();

        $session->set('own_ids', $own_ids);
        $photos = $this->getArrayFotos($list);
        $rooms = $this->getArrayRooms($list);

        $view = $session->get('search_view_results');

        if ($view != null && $view == 'LIST')
            $response = $this->renderView('frontEndBundle:ownership:searchListOwnership.html.twig', array(
                'list' => $list,
                'photos' => $photos,
                'rooms' => $rooms
            ));
        else if ($view != null && $view == 'PHOTOS')
            $response = $this->renderView('frontEndBundle:ownership:searchMosaicOwnership.html.twig', array(
                'list' => $list,
                'photos' => $photos,
                'rooms' => $rooms
            ));
        else if ($view != null && $view == 'MAP')
            $response = $this->renderView('frontEndBundle:ownership:searchMapOwnership.html.twig', array(
                'list' => $list,
                'photos' => $photos,
                'rooms' => $rooms
            ));

        return new Response($response, 200);
    }

    public function get_filters_statisticsAction() {
        $request = $this->getRequest();
        $session = $request->getSession();
        $em = $this->getDoctrine()->getEntityManager();
        $own_ids = $session->get('own_ids');

        $statisics = $em->getRepository('mycpBundle:ownership')->getSearchStatisticsByIds($own_ids);

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

        $response = $this->renderView('frontEndBundle:ownership:filters.html.twig', array(
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
        $em = $this->getDoctrine()->getEntityManager();
        $own_ids = $session->get('own_ids');
        $curr_rate = $session->get('curr_rate');

        $list = $em->getRepository('mycpBundle:ownership')->getListByIds($own_ids);

        if (is_array($list)) {
            $result = array();
            
            foreach ($list as $own) {
                $prize = ($own->getOwnMinimumPrice()) * ($session->get('curr_rate') == null ? 1 : $session->get('curr_rate'));
                $result[] = array('latitude' => $own->getOwnGeolocateX(),
                    'longitude' => $own->getOwnGeolocateY(),
                    'title' => $own->getOwnName(),
                    'content' => $this->get('translator')->trans('FROM_PRICES') . ($session->get("curr_symbol") != null ? " " .$session->get('curr_symbol')." " : " $ ") . $prize . " " . strtolower($this->get('translator')->trans("BYNIGHTS_PRICES")),
                    'image' => $this->container->get('templating.helper.assets')->getUrl('uploads/ownershipImages/' . $this->get_ownership_photo($own->getOwnId()), null), //$this->get_ownership_photo($own->getOwnId()),
                    'id' => $own->getOwnId());
            }
        }

        return new Response(json_encode($result), 200);
    }

    public function update_ratingAction($ownid) {
        $em = $this->getDoctrine()->getEntityManager();
        $ownership = $em->getRepository('mycpBundle:ownership')->find($ownid);

        $response = $this->renderView('frontEndBundle:ownership:ownershipRating.html.twig', array(
            'ownership' => $ownership
                ));

        return new Response($response, 200);
    }

    function getArrayFotos($own_list) {
        $em = $this->getDoctrine()->getEntityManager();
        $photos = array();

        if (is_array($own_list)) {
            foreach ($own_list as $own) {
                $ownership_photo = $em->getRepository('mycpBundle:ownershipPhoto')->findOneBy(array(
                    'own_pho_own' => $own->getOwnId()));
                if ($ownership_photo != null) {
                    $photo_name = $ownership_photo->getOwnPhoPhoto()->getPhoName();


                    if (file_exists(realpath("uploads/ownershipImages/" . $photo_name))) {
                        $photos[$own->getOwnId()] = $photo_name;
                    } else {
                        $photos[$own->getOwnId()] = 'no_photo_square.gif';
                    }
                } else {
                    $photos[$own->getOwnId()] = 'no_photo_square.gif';
                }
            }
        }
        return $photos;
    }

    private function get_ownership_photo($own_id) {
        $em = $this->getDoctrine()->getEntityManager();
        $ownership_photo = $em->getRepository('mycpBundle:ownershipPhoto')->findOneBy(array(
            'own_pho_own' => $own_id));
        $photo = null;
        if ($ownership_photo != null) {
            $photo_name = $ownership_photo->getOwnPhoPhoto()->getPhoName();


            if (file_exists(realpath("uploads/ownershipImages/" . $photo_name))) {
                $photo = $photo_name;
            } else {
                $photo = 'no_photo_square.gif';
            }
        } else {
            $photo = 'no_photo_square.gif';
        }
        return $photo;
    }

    function getArrayRooms($own_list) {
        $em = $this->getDoctrine()->getEntityManager();
        $rooms = array();

        if (is_array($own_list)) {
            foreach ($own_list as $own) {

                $rooms[$own->getOwnId()] = count($em->getRepository('mycpBundle:room')->findBy(array('room_ownership' => $own->getOwnId())));
            }
        }

        return $rooms;
    }

    // Código de Ernesto
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

        var_dump($_COOKIE["mycp_favorites_ownerships"]);
        return $this->redirect($this->generateUrl('frontend_details_ownership', array('ownerid' => $id_ownership)));
    }

    // Fin Código de Ernesto

    public function map_details_ownershipAction($ownGeolocateX, $ownGeolocateY, $ownName, $description, $image) {
        $ownership = new \MyCp\mycpBundle\Entity\ownership();
        $ownership->setOwnName($ownName);
        $ownership->setOwnGeolocateX($ownGeolocateX);
        $ownership->setOwnGeolocateY($ownGeolocateY);

        return $this->render('frontEndBundle:ownership:ownershipDetailsMap.html.twig', array(
                    'ownership' => $ownership,
                    'description' => $description,
                    'image' => $image
                ));
    }

    public function map_resizedAction() {
        $request = $this->getRequest();
        $session = $request->getSession();
        $em = $this->getDoctrine()->getEntityManager();
        $markers_id_list = $request->request->get('own_ids');

        $own_ids = "0";

        if ($markers_id_list != null || count($markers_id_list)) {
            foreach ($markers_id_list as $id)
                $own_ids .= ", " . $id;
        }

        $session->set('own_ids', $own_ids);

        $list = $em->getRepository('mycpBundle:ownership')->getListByIds($own_ids);

        $photos = $this->getArrayFotos($list);
        $rooms = $this->getArrayRooms($list);

        $response = $this->renderView('frontEndBundle:ownership:searchListOwnership.html.twig', array(
            'list' => $list,
            'photos' => $photos,
            'rooms' => $rooms,
            'type' => 'map'
                ));

        return new Response($response, 200);
    }

    public function voted_best_listAction(){
        $em = $this->getDoctrine()->getEntityManager();

        $session = $this->getRequest()->getSession();
        $session->set('search_order', 'BEST_VALUED');
        $search_results_list = $em->getRepository('mycpBundle:ownership')->search(null, null, null, '1','1', $session->get('search_order'));

        $session->set('search_text', null);
        $session->set('search_arrival_date', null);
        $session->set('search_departure_date', null);
        $session->set('search_guests', '1');

        $own_ids = "0";

        foreach ($search_results_list as $own)
            $own_ids .= "," . $own->getOwnId();

        $session->set('own_ids', $own_ids);

        if ($session->get('search_view_results') == null || $session->get('search_view_results') == '')
            $session->set('search_view_results', 'LIST');

        $search_results_photos = $this->getArrayFotos($search_results_list);
        $search_results_rooms = $this->getArrayRooms($search_results_list);

        $categories_own_list = $em->getRepository('mycpBundle:ownership')->getOwnsCategories($own_ids);
        $types_own_list = $em->getRepository('mycpBundle:ownership')->getOwnsTypes($own_ids);
        $prices_own_list = $em->getRepository('mycpBundle:ownership')->getOwnsPrices($own_ids);
        $statistics_own_list = $em->getRepository('mycpBundle:ownership')->getSearchStatistics($search_results_list);

        return $this->render('frontEndBundle:ownership:searchOwnership.html.twig', array(
            'search_text' => null,
            'search_guests' => '1',
            'search_arrival_date' => null,
            'search_departure_date' => null,
            'list' => $search_results_list,
            'photos' => $search_results_photos,
            'owns_categories' => $categories_own_list,
            'owns_types' => $types_own_list,
            'owns_prices' => $prices_own_list,
            'view_results' => $session->get('search_view_results'),
            'order' => $session->get('search_order'),
            'rooms' => $search_results_rooms,
            'own_statistics' => $statistics_own_list,
            'locale' => $this->get('translator')->getLocale(),
            'autocomplete_text_list' => $this->autocomplete_text_list()
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

    private function autocomplete_text_list() {
        //$term = $request->get('term');
        $em = $this->getDoctrine()->getEntityManager();
        $provinces = $em->getRepository('mycpBundle:province')->getProvinces();
        $municipalities = $em->getRepository('mycpBundle:municipality')->getMunicipalities();
        $ownerships = $em->getRepository('mycpBundle:ownership')->getPublicOwnerships();

        $result = array();
        foreach ($provinces as $prov) {
            $result[] = $prov->getProvName();
        }

        foreach ($municipalities as $mun) {
            if (!array_search($mun->getMunName(), $result))
                $result[] = $mun->getMunName();
        }

        foreach ($ownerships as $own) {
            if (!array_search($own->getOwnName(), $result))
                $result[] = $own->getOwnName();

            if (!array_search($own->getOwnMcpCode(), $result))
                $result[] = $own->getOwnMcpCode();
        }

        return json_encode($result);
    }

    public static function dates_between($startdate, $enddate, $format = null) {

        (is_int($startdate)) ? 1 : $startdate = strtotime($startdate);
        (is_int($enddate)) ? 1 : $enddate = strtotime($enddate);

        if ($startdate > $enddate) {
            return false; //The end date is before start date
        }

        while ($startdate < $enddate) {
            $arr[] = ($format) ? date($format, $startdate) : $startdate;
            $startdate += 86400;
        }
        $arr[] = ($format) ? date($format, $enddate) : $enddate;

        return $arr;
    }

    public function type_listAction($type)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $session = $this->getRequest()->getSession();
        $session->set('search_order', 'BEST_VALUED');

        $filters = array();
        $filters['own_type'] = array($type);

        $search_results_list = $em->getRepository('mycpBundle:ownership')->search(null, null, null, '1','1', $session->get('search_order'),false,$filters);

        $session->set('search_text', null);
        $session->set('search_arrival_date', null);
        $session->set('search_departure_date', null);
        $session->set('search_guests', '1');

        $own_ids = "0";

        foreach ($search_results_list as $own)
            $own_ids .= "," . $own->getOwnId();

        $session->set('own_ids', $own_ids);

        if ($session->get('search_view_results') == null || $session->get('search_view_results') == '')
            $session->set('search_view_results', 'LIST');

        $search_results_photos = $this->getArrayFotos($search_results_list);
        $search_results_rooms = $this->getArrayRooms($search_results_list);

        $categories_own_list = $em->getRepository('mycpBundle:ownership')->getOwnsCategories($own_ids);
        $types_own_list = $em->getRepository('mycpBundle:ownership')->getOwnsTypes($own_ids);
        $prices_own_list = $em->getRepository('mycpBundle:ownership')->getOwnsPrices($own_ids);
        $statistics_own_list = $em->getRepository('mycpBundle:ownership')->getSearchStatistics($search_results_list);

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

        return $this->render('frontEndBundle:ownership:searchOwnership.html.twig', array(
            'search_text' => null,
            'search_guests' => '1',
            'search_arrival_date' => null,
            'search_departure_date' => null,
            'list' => $search_results_list,
            'photos' => $search_results_photos,
            'owns_categories' => $categories_own_list,
            'owns_types' => $types_own_list,
            'owns_prices' => $prices_own_list,
            'view_results' => $session->get('search_view_results'),
            'order' => $session->get('search_order'),
            'rooms' => $search_results_rooms,
            'own_statistics' => $statistics_own_list,
            'locale' => $this->get('translator')->getLocale(),
            'autocomplete_text_list' => $this->autocomplete_text_list(),
            'check_filters' => $check_filters
        ));
    }

}
