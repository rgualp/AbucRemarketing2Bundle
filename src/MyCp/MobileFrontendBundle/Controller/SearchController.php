<?php

namespace MyCp\MobileFrontendBundle\Controller;

use MyCp\FrontEndBundle\Helpers\Utils;
use MyCp\mycpBundle\Helpers\OrderByHelper;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SearchController extends Controller
{
    public function orangeSearchBarAction() {
        $em = $this->getDoctrine()->getManager();
        $session = $this->getRequest()->getSession();

        return $this->render('MyCpMobileFrontendBundle:search:orangeSearchBar.html.twig', array(
            'locale' => $this->get('translator')->getLocale(),
            'autocomplete_text_list' => $em->getRepository('mycpBundle:ownership')->autocompleteTextList(),
            'arrival_date' => $session->get("search_arrival_date"),
            'departure_date' => $session->get("search_departure_date")
        ));
    }

    public function searchAction(Request $request, $text = null, $arriving_date = null, $departure_date = null, $guests = 1, $rooms = 1, $inmediate="null" ,$order_price='null', $order_comments='null', $order_books='null') {

        $em = $this->getDoctrine()->getManager();
        $session = $this->getRequest()->getSession();

        if ($session->get('search_order') == null || $session->get('search_order') == '')
            $session->set('search_order', OrderByHelper::SEARCHER_BEST_VALUED);
        $rooms = ($rooms == "undefined") ? 1 : $rooms;

        $session->set('search_arrival_date', null);
        $session->set('search_departure_date', null);
        $today = new \DateTime();
        $search_text = ($text != null && $text != '' && $text != $this->get('translator')->trans('PLACE_WATERMARK')) ? Utils::getTextFromNormalized($text) : null;
        $search_guests = ($guests != null && $guests != '' && $guests != $this->get('translator')->trans('GUEST_WATERMARK')) ? $guests : "1";
        $search_rooms = ($rooms != null && $rooms != '' && $rooms != $this->get('translator')->trans('ROOM_WATERMARK')) ? $rooms : "1";
        $arrival = ($request->get('arrival') != null && $request->get('arrival') != "" && $request->get('arrival') != "null") ? $request->get('arrival') : $today->format('d-m-Y');

        $departure = null;
        if($request->get('departure') != null && $request->get('departure') != "" && $request->get('departure') != "null")
            $departure = $request->get('departure');
        else if($arrival != null)
        {
            $arrivalDateTime = \DateTime::createFromFormat("d-m-Y",$arrival);
            $departure = date_add($arrivalDateTime, date_interval_create_from_date_string("2 days"))->format('d-m-Y');
        }
        else
            $departure = date_add($today, date_interval_create_from_date_string("2 days"))->format('d-m-Y');


        /*if ($arrival == null)
            $arrival = $today->format('d-m-Y');
        if ($departure == null)
            $departure = date_add($today, date_interval_create_from_date_string("2 days"))->format('d-m-Y');*/

        $check_filters = $session->get("filter_array");
        $room_filter = $session->get("filter_room");

        $session->set("filter_array", $check_filters);
        $session->set("filter_room", $room_filter);
        $list = $em->getRepository('mycpBundle:ownership')->search($this, $search_text, $arrival, $departure, $search_guests, $search_rooms, $session->get('search_order'), $room_filter, $check_filters, $inmediate);

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
        $items_per_page = 1;
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

//        if ($session->get('search_view_results') == null || $session->get('search_view_results') == '')
        $session->set('search_view_results', 'PHOTOS');

        $results = $em->getRepository('mycpBundle:ownership')->getSearchNumbers();

        $categories_own_list = $results["categories"];//$em->getRepository('mycpBundle:ownership')->getOwnsCategories();
        $types_own_list = $results["types"];//$em->getRepository('mycpBundle:ownership')->getOwnsTypes();
        $prices_own_list = $results["prices"];//$em->getRepository('mycpBundle:ownership')->getOwnsPrices();
        $statistics_own_list = $em->getRepository('mycpBundle:ownership')->getSearchStatistics();
        $awards = $em->getRepository('mycpBundle:award')->findAll();

        return $this->render('MyCpMobileFrontendBundle:search:searchResultOwnership.html.twig', array(
            'search_text' => $search_text,
            'inmediate' => $inmediate,
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
            'autocomplete_text_list' => $em->getRepository('mycpBundle:ownership')->autocompleteTextList(),
            'list' => $result_list,
            'items_per_page' => $items_per_page,
            'total_items' => $paginator->getTotalItems(),
            'current_page' => $page,
            'check_filters' => $check_filters,
            'show_paginator' => true,
            'awards'=>$awards
        ));
    }

    public function filterAction() {
        $request = $this->getRequest();
        $session = $request->getSession();
        $em = $this->getDoctrine()->getManager();
        if ($session->get('search_order') == null || $session->get('search_order') == '')
            $session->set('search_order', OrderByHelper::SEARCHER_BEST_VALUED);



            $guests = $request->request->get('guests');
            $rooms = $request->request->get('rooms');
            $arriving_date = $request->request->get('arrival');
            $departure_date = $request->request->get('departure');
            $inmediate = $request->request->get('inmediate');



            $session->set('search_arrival_date', null);
            $session->set('search_departure_date', null);
            $today = new \DateTime();

            $search_guests = ($guests != null && $guests != '' && $guests != $this->get('translator')->trans('GUEST_WATERMARK')) ? $guests : "1";
            $search_rooms = ($rooms != null && $rooms != '' && $rooms != $this->get('translator')->trans('ROOM_WATERMARK')) ? $rooms : "1";
            $search_arrival_date = ($arriving_date != null && $arriving_date != '' && $arriving_date != $this->get('translator')->trans('ARRIVAL_WATERMARK')) ? $arriving_date : $today->format('d-m-Y');

            $search_departure_date = null;
            if($departure_date != null && $departure_date != "" && $departure_date != "null" && $departure_date != $this->get('translator')->trans('DEPARTURE_WATERMARK'))
                $search_departure_date = $departure_date;
            else if($search_arrival_date != null)
            {
                $arrivalDateTime = \DateTime::createFromFormat("Y-m-d",$search_arrival_date);
                $search_departure_date = date_add($arrivalDateTime, date_interval_create_from_date_string("2 days"))->format('d-m-Y');
            }
            else
                $search_departure_date = date_add($today, date_interval_create_from_date_string("2 days"))->format('d-m-Y');

            $text = $request->request->get('text');
            $text = ($text == "") ? "null" : $text;

            $session->set('search_text', $text);
            $session->set('search_arrival_date', $search_arrival_date);
            $session->set('search_departure_date', $search_departure_date);
            $session->set('search_guests', $search_guests);
            $session->set('search_rooms', $search_rooms);



        $check_filters = array();
        $check_filters['own_reservation_type'] = $request->request->get('own_reservation_type');
        $check_filters['own_update_avaliable'] = $request->request->get('own_update_avaliable');
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

        $paginator = $this->get('ideup.simple_paginator');
        $items_per_page = 1;
        $paginator->setItemsPerPage($items_per_page);
        $orderPrice=$request->request->get('order_price');
        $orderComments=$request->request->get('order_comments');
        $orderBooks=$request->request->get('order_books');

        $list = $paginator->paginate($em->getRepository('mycpBundle:ownership')->search($this, $session->get('search_text'), $session->get('search_arrival_date'), $session->get('search_departure_date'), $session->get('search_guests'), $session->get('search_rooms'), $session->get('search_order')?$session->get('search_order'):null, $room_filter, $check_filters, $inmediate))->getResult();
        $page = 1;

        if ( $request->request->get('page') ){
            $page = $request->request->get('page');
            if ($page > $paginator->getTotalItems()){
                return new Response("", 200);
            }
        }

        $own_ids = "0";
        foreach ($list as $own)
            $own_ids .= "," . $own['own_id'];
        $session->set('own_ids', $own_ids);

        $response = $this->renderView('MyCpMobileFrontendBundle:ownership:searchItemMosaic.html.twig', array(
                'list' => $list,
                'items_per_page' => $items_per_page,
                'total_items' => $paginator->getTotalItems(),
                'current_page' => $page,
                'list_preffix' => 'search'
            ));

        return new Response($response, 200);
    }
}
