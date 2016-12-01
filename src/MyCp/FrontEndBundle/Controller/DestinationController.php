<?php

namespace MyCp\FrontEndBundle\Controller;

use MyCp\FrontEndBundle\Helpers\Utils;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class DestinationController extends Controller {

    public function getBigMapAction() {
        $em = $this->getDoctrine()->getManager();
        $locale = $this->get('translator')->getLocale();
        $destinations = $em->getRepository('mycpBundle:destination')->getActiveForMap();
        $categories_lang = $em->getRepository('mycpBundle:destinationCategoryLang')->getForMap($locale);

        return $this->render('FrontEndBundle:public:map.html.twig', array(
            'destinations_map' => $destinations,
            'des_categories_lang' => $categories_lang));
    }

    public function getMapByProvinceAction() {
        $em = $this->getDoctrine()->getManager();
        $dest_location = $em->getRepository('mycpBundle:destinationLocation')->findAll();
        return $this->render('FrontEndBundle:destination:destinationByProvince.html.twig', array(
            'locations_destinations' => $dest_location
        ));
    }

    public function popularListAction() {
        $em = $this->getDoctrine()->getManager();
        $locale = $this->get('translator')->getLocale();
        $users_id = $em->getRepository('mycpBundle:user')->getIds($this);
        $dest_list = $em->getRepository('mycpBundle:destination')->getAllDestinations($locale, $users_id["user_id"], $users_id["session_id"]);

        return $this->render('FrontEndBundle:destination:listDestination.html.twig', array(
            'main_destinations' => array_slice($dest_list, 0, 6),
            'provinces' => $em->getRepository('mycpBundle:province')->findAll(),
            'all_destinations' => $dest_list
        ));
    }

    public function detailsAction($destination_name) {
        $request = $this->getRequest();
        $session = $request->getSession();
        $em = $this->getDoctrine()->getManager();
        $mobileDetector = $this->get('mobile_detect.mobile_detector');
        $users_id = $em->getRepository('mycpBundle:user')->getIds($this);
        $locale = $this->get('translator')->getLocale();
        $original_destination_name = $destination_name;
        $destination_name = str_replace('-', ' ', $destination_name);
        $destination = $em->getRepository('mycpBundle:destination')->findOneBy(array('des_name' => $destination_name));
        if($destination == null) {
            throw $this->createNotFoundException();
        }
        $destination_array = $em->getRepository('mycpBundle:destination')->getDestination($destination->getDesId(), $locale);
        if($destination_array == null || count($destination_array) == 0) {
            throw $this->createNotFoundException();
        }

        $photos = $em->getRepository('mycpBundle:destination')->getPhotos($destination->getDesId(), $locale);

        $location_municipality_id = $destination_array['municipality_id'];
        $location_province_id = $destination_array['province_id'];

        $popular_destinations_list = $em->getRepository('mycpBundle:destination')->getPopularDestinations(5, $users_id["user_id"], $users_id["session_id"]);

        $popular_destinations_for_url = array();
        foreach ($popular_destinations_list as $dest)
            $popular_destinations_for_url[$dest['des_id']] = Utils::urlNormalize($dest['des_name']);


        $other_destinations_in_municipality = $em->getRepository('mycpBundle:destination')->filter($locale, $location_municipality_id, $location_province_id, $destination->getDesId(), null, 5);
        $other_destinations_in_municipality_for_url = array();
        foreach ($other_destinations_in_municipality as $dest)
            $other_destinations_in_municipality_for_url[$dest['desid']] = Utils::urlNormalize($dest['desname']);

        $other_destinations_in_province = $em->getRepository('mycpBundle:destination')->filter($locale, null, $location_province_id, $destination->getDesId(), null, 5);
        $other_destinations_in_province_for_url = array();
        foreach ($other_destinations_in_province as $dest)
            $other_destinations_in_province_for_url[$dest['desid']] = Utils::urlNormalize($dest['desname']);

        $provinces = $em->getRepository("mycpBundle:province")->findAll();
        $provinces_for_url = array();
        foreach ($provinces as $prov)
            $provinces_for_url[$prov->getProvId()] = Utils::urlNormalize($prov->getProvName());


        if ($mobileDetector->isMobile()){
            $view = $session->get('search_view_results_destination');

            /**************************to pagin***********/
            $page = 1;
            if(isset($_GET['page']))
                $page = $_GET['page'];
            $items_per_page = 6;
            //$paginator = $this->get('ideup.simple_paginator');
            //$paginator->setItemsPerPage($items_per_page);

            $start = ($page - 1) * $items_per_page;
            $limit = $items_per_page;
            /**********************************************/

            $view = 'PHOTOS';
            $l = $em->getRepository('mycpBundle:destination')->getAccommodationsNear($destination->getDesId(), null, null, $users_id['user_id'], $users_id['session_id'], $start, $limit);
            $list = $l['results'];
            $owns_nearby = $list;//$paginator->paginate($list)->getResult();

            /**************************to pagin***********/
            $totalItems = $l['count'];

            $currentPage = $page;
            $firstPage = 1;
            $previousPage = $page - 1;
            $lastPage = (int)ceil((($totalItems > 0) ? $totalItems : 1) / $items_per_page);
            $minPage = ($currentPage == $firstPage) ? ($firstPage) : ($currentPage - 1);//($page > $offset) ? ($page - $offset) : ($firstPage);
            $maxPage = ($currentPage == $lastPage) ? ($lastPage) : ($currentPage + 1);//($minPage + $items_per_page > $lastPage) ? ($lastPage) : ($minPage + $items_per_page - 1);
            $nextPage = $currentPage + 1;
            $paginator = array(
                'firstPage'=>$firstPage,
                'previousPage'=>$previousPage,
                'minPage'=>$minPage,
                'lastPage'=>$lastPage,
                'maxPage'=>$maxPage,
                'currentPage'=>$currentPage,
                'nextPage'=>$nextPage
            );
            /**********************************************/

        }else{
            /**************************to pagin***********/

            //$paginator = $this->get('ideup.simple_paginator');
            //$paginator->setItemsPerPage($items_per_page);

            $start = null;
            $limit = null;
            /**********************************************/

            $view = 'PHOTOS';
            $list = $em->getRepository('mycpBundle:destination')->getAccommodationsNear($destination->getDesId(), null, null, $users_id['user_id'], $users_id['session_id'], $start, $limit);

            $items_per_page = 8;
            $paginator = $this->get('ideup.simple_paginator');
            $paginator->setItemsPerPage($items_per_page);
            $owns_nearby = $paginator->paginate($list)->getResult();
            $page = 1;
            if (isset($_GET['page']))
                $page = $_GET['page'];

            $results = $em->getRepository('mycpBundle:ownership')->getSearchNumbers();

            $categories_own_list = $results["categories"];//$em->getRepository('mycpBundle:ownership')->getOwnsCategories();
            $types_own_list = $results["types"];//$em->getRepository('mycpBundle:ownership')->getOwnsTypes();
            $prices_own_list = $results["prices"];//$em->getRepository('mycpBundle:ownership')->getOwnsPrices();
            $statistics_own_list = $em->getRepository('mycpBundle:ownership')->getSearchStatistics();
            $awards = $em->getRepository('mycpBundle:award')->findAll();

            $today = new \DateTime();
            $desName = $destination->getDesName();
            if ($desName === "La Habana Vieja"){
                $desName = "Habana Vieja";
            }
            $search_text = Utils::getTextFromNormalized($desName);
            $search_guests = "1";
            $search_rooms = "1";
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

            $session->set('search_text', $search_text);
            $session->set('search_arrival_date', $arrival);
            $session->set('search_departure_date', $departure);
            $session->set('search_guests', $search_guests);
            $session->set('search_rooms', $search_rooms);

        }

        $em->getRepository('mycpBundle:userHistory')->insert(false, $destination->getDesId(), $users_id);

        $own_ids = "0";
        foreach ($list as $own)
            $own_ids .= "," . $own['own_id'];
        $session->set('own_ids', $own_ids);



        if ($mobileDetector->isMobile()){
            return $this->render('MyCpMobileFrontendBundle:destination:destinationDetails.html.twig', array(
                'destination' => $destination_array[0],
                'is_in_favorite' => $em->getRepository('mycpBundle:favorite')->isInFavorite($destination->getDesId(), false, $users_id["user_id"], $users_id["session_id"]),
                'autocomplete_text_list' => $em->getRepository('mycpBundle:ownership')->autocompleteTextList(),
                'locale' => $this->get('translator')->getLocale(),
                'location' => $destination_array['municipality_name'] . ' / ' . $destination_array['province_name'],
                'location_municipality' => $destination_array['municipality_name'],
                'location_province' => $destination_array['province_name'],
                'location_municipality_id' => $destination_array['municipality_id'],
                'location_province_id' => $destination_array['province_id'],
                'gallery_photos' => $photos['photo_name'],
                'gallery_photo_descriptions' => $photos['photo_description'],
                'description' => $destination_array['desc_full'],
                'brief_description' => $destination_array['desc_brief'],
                'other_destinations_in_municipality' => $other_destinations_in_municipality,
                'total_other_destinations_in_municipality' => count($other_destinations_in_municipality),
                'other_destinations_in_province' => $other_destinations_in_province,
                'total_other_destinations_in_province' => count($other_destinations_in_province),
                'popular_list' => $popular_destinations_list,
                'provinces' => $provinces,
                'owns_nearby' => $owns_nearby,
                'items_per_page' => $items_per_page,
                'total_items' => $totalItems,
                'paginator'=>$paginator,
                'destination_name' => $original_destination_name,
                'data_view' => (($view == null) ? 'LIST' : $view),
                'popular_destinations_for_url' => $popular_destinations_for_url,
                'other_destinations_in_municipality_for_url' => $other_destinations_in_municipality_for_url,
                'other_destinations_in_province_for_url' => $other_destinations_in_province_for_url,
                'provinces_for_url' => $provinces_for_url,
                'keyword_description' => $destination_array['keyword_description'],
                'keyword' => $destination_array['keywords']
            ));
        }else{
            return $this->render('FrontEndBundle:destination:newDestinationDetails.html.twig', array(
                'destination' => $destination_array[0],
                'is_in_favorite' => $em->getRepository('mycpBundle:favorite')->isInFavorite($destination->getDesId(), false, $users_id["user_id"], $users_id["session_id"]),
                'autocomplete_text_list' => $em->getRepository('mycpBundle:ownership')->autocompleteTextList(),
                'locale' => $this->get('translator')->getLocale(),
                'location' => $destination_array['municipality_name'] . ' / ' . $destination_array['province_name'],
                'location_municipality' => $destination_array['municipality_name'],
                'location_province' => $destination_array['province_name'],
                'location_municipality_id' => $destination_array['municipality_id'],
                'location_province_id' => $destination_array['province_id'],
                'gallery_photos' => $photos['photo_name'],
                'gallery_photo_descriptions' => $photos['photo_description'],
                'description' => $destination_array['desc_full'],
                'brief_description' => $destination_array['desc_brief'],
                'other_destinations_in_municipality' => $other_destinations_in_municipality,
                'total_other_destinations_in_municipality' => count($other_destinations_in_municipality),
                'other_destinations_in_province' => $other_destinations_in_province,
                'total_other_destinations_in_province' => count($other_destinations_in_province),
                'popular_list' => $popular_destinations_list,
                'provinces' => $provinces,
                'items_per_page' => $items_per_page,
                'total_items' => $paginator->getTotalItems(),
                'paginator'=>$paginator,
                'destination_name' => $original_destination_name,
                'data_view' => (($view == null) ? 'LIST' : $view),
                'popular_destinations_for_url' => $popular_destinations_for_url,
                'other_destinations_in_municipality_for_url' => $other_destinations_in_municipality_for_url,
                'other_destinations_in_province_for_url' => $other_destinations_in_province_for_url,
                'provinces_for_url' => $provinces_for_url,
                'keyword_description' => $destination_array['keyword_description'],
                'keyword' => $destination_array['keywords'],
                'own_statistics' => $statistics_own_list,
                'owns_categories' => $categories_own_list,
                'owns_types' => $types_own_list,
                'owns_prices' => $prices_own_list,
                'awards'=>$awards,
                'current_page' => $page,
                'cant_pages' => $paginator->getLastPage(),
                'search_text' => $search_text,
                'search_guests' => $search_guests,
                'search_arrival_date' => $arrival,
                'search_departure_date' => $departure
            ));
        }



    }

    public function ownsNearbyCallbackAction($destination_name, $destination_id) {
        $request = $this->getRequest();
        $session = $request->getSession();
        $em = $this->getDoctrine()->getManager();
        $users_id = $em->getRepository('mycpBundle:user')->getIds($this);

        $paginator = $this->get('ideup.simple_paginator');
        $items_per_page = 8;
        $paginator->setItemsPerPage($items_per_page);
        $list = $paginator->paginate($em->getRepository('mycpBundle:destination')->getAccommodationsNear($destination_id, null, null, $users_id['user_id'], $users_id['session_id']))->getResult();

        $page = 1;
        if (isset($_GET['page']))
            $page = $_GET['page'];

        $own_ids = "0";
        foreach ($list as $own)
            $own_ids .= "," . $own['own_id'];
        $session->set('own_ids', $own_ids);

        $response = $this->renderView('FrontEndBundle:destination:detailsOwnsNearByDestination.html.twig', array(
            'owns_nearby' => $list,
            'destination_name' => $destination_name,
            'items_per_page' => $items_per_page,
            'total_items' => $paginator->getTotalItems(),
            'current_page' => $page,
            'cant_pages' => $paginator->getLastPage()
        ));

        return new Response($response, 200);
    }

    public function byProvinceAction() {
        $request = $this->getRequest();
        $em = $this->getDoctrine()->getManager();
        $province_name = $request->request->get('province_name');

        $list = $em->getRepository('mycpBundle:destination')->getByProvinceName($province_name);

        $response = $this->renderView('FrontEndBundle:destination:destinationsInProvince.html.twig', array(
            'list' => $list
        ));

        return new Response($response, 200);
    }

    public function filterAction() {
        $em = $this->getDoctrine()->getManager();
        $request = $this->getRequest();
        $prov_id = $request->request->get('province');
        $mun_id = $request->request->get('municipality');

        $paginator = $this->get('ideup.simple_paginator');
        $items_per_page = 15;
        $paginator->setItemsPerPage($items_per_page);
        $popular_places = $paginator->paginate($em->getRepository('mycpBundle:destination')->filter($mun_id, $prov_id))->getResult();
        $page = 1;
        if(isset($_GET['page']))
            $page = $_GET['page'];

        $popular_places_photos = $em->getRepository('mycpBundle:destination')->getAllPhotos($popular_places);
        $popular_places_description = $em->getRepository('mycpBundle:destination')->getDescription($popular_places, 'ES');
        $popular_places_location = $em->getRepository('mycpBundle:destination')->getLocation($popular_places);

        $response = $this->renderView('FrontEndBundle:destination:itemListDestination.html.twig', array(
            'popular_places' => $popular_places,
            'popular_places_photos' => $popular_places_photos,
            'popular_places_description' => $popular_places_description,
            'popular_places_location' => $popular_places_location,
            'items_per_page' => $items_per_page,
            'total_items' => $paginator->getTotalItems(),
            'current_page' => $page
        ));

        return new Response($response, 200);
    }

    public function searchChangeViewResultsAction($destination_name, $destination_id) {
        $request = $this->getRequest();
        $session = $request->getSession();
        $em = $this->getDoctrine()->getManager();
        $users_id = $em->getRepository('mycpBundle:user')->getIds($this);

        $view = $request->request->get('view');
        $session->set('search_view_results_destination', $view);

        if($session->get("destination_details_show_rows") == null)
            $session->set('destination_details_show_rows', 3);

        $paginator = $this->get('ideup.simple_paginator');
        //$items_per_page = $session->get("destination_details_show_rows");
        $items_per_page = ($view != null) ? ($view != 'PHOTOS' ? 5 : 6) : 5;
//        $items_per_page = 6;
        $view = ($view != null && $view == 'MAP') ? 'MAP' : 'PHOTOS';
        $paginator->setItemsPerPage($items_per_page);
        $owns_nearby = $paginator->paginate($em->getRepository('mycpBundle:destination')->getAccommodationsNear($destination_id, null, null, $users_id['user_id'], $users_id['session_id']))->getResult();

        $response = $this->renderView('FrontEndBundle:destination:detailsOwnsNearByDestination.html.twig', array(
            'owns_nearby' => $owns_nearby,
            'destination_name' => $destination_name,
            'data_view' => $view,
            'total_items' => $paginator->getTotalItems(),
            'items_per_page' => $items_per_page
        ));

        return new Response($response, 200);
    }

}
