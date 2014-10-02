<?php

namespace MyCp\FrontEndBundle\Controller;

use MyCp\FrontEndBundle\Helpers\Utils;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class DestinationController extends Controller {

    public function getBigMapAction()
    {
        $em = $this->getDoctrine()->getManager();
        $locale=$this->get('translator')->getLocale();
        $lang= $em->getRepository('mycpBundle:lang')->findBy(array('lang_code'=>$locale));
        $destinations=$em->getRepository('mycpBundle:destination')->findBy(array('des_active'=>1));
        $categories_lang = $em->getRepository('mycpBundle:destinationCategoryLang')->findBy(array('des_cat_id_lang'=>$lang[0]->getLangId()));
        //var_dump($categories_lang);exit();
        return $this->render('FrontEndBundle:public:map.html.twig',array(
            'destinations_map'=>$destinations,
            'des_categories_lang'=>$categories_lang));
    }

    public function get_map_by_provinceAction()
    {
        $em = $this->getDoctrine()->getManager();
        $dest_location = $em->getRepository('mycpBundle:destinationLocation')->findAll();
        return $this->render('FrontEndBundle:destination:destinationByProvince.html.twig', array(
            'locations_destinations' => $dest_location
        ));
    }

    public function popular_listAction() {
        $em = $this->getDoctrine()->getManager();
        $locale = $this->get('translator')->getLocale();
        $users_id = $em->getRepository('mycpBundle:user')->user_ids($this);
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
        $users_id = $em->getRepository('mycpBundle:user')->user_ids($this);
        $locale = $this->get('translator')->getLocale();
        $original_destination_name = $destination_name;
        $destination_name=str_replace('-',' ',$destination_name);
        $destination= $em->getRepository('mycpBundle:destination')->findOneBy(array('des_name'=>$destination_name));
        if($destination==null)
        {
            throw $this->createNotFoundException();
        }
        $destination_array = $em->getRepository('mycpBundle:destination')->getDestination($destination->getDesId(),$locale);
        if($destination_array==null || count($destination_array) == 0)
        {
            throw $this->createNotFoundException();
        }

        $photos = $em->getRepository('mycpBundle:destination')->getPhotos($destination->getDesId(),$locale);

        $location_municipality_id = $destination_array['municipality_id'];
        $location_province_id = $destination_array['province_id'];

        $popular_destinations_list = $em->getRepository('mycpBundle:destination')->getPopularDestinations(5, $users_id["user_id"], $users_id["session_id"]);

        $popular_destinations_for_url = array();
        foreach ($popular_destinations_list as $dest)
            $popular_destinations_for_url[$dest['des_id']] = Utils::urlNormalize($dest['des_name']);


        $other_destinations_in_municipality = $em->getRepository('mycpBundle:destination')->filter($locale,$location_municipality_id, $location_province_id, $destination->getDesId(), null, 5);
        $other_destinations_in_municipality_for_url = array();
        foreach ($other_destinations_in_municipality as $dest)
            $other_destinations_in_municipality_for_url[$dest['desid']] = Utils::urlNormalize($dest['desname']);

        $other_destinations_in_province = $em->getRepository('mycpBundle:destination')->filter($locale,null, $location_province_id, $destination->getDesId(), null, 5);
        $other_destinations_in_province_for_url = array();
        foreach ($other_destinations_in_province as $dest)
            $other_destinations_in_province_for_url[$dest['desid']] = Utils::urlNormalize($dest['desname']);

        $provinces = $em->getRepository("mycpBundle:province")->findAll();
        $provinces_for_url = array();
        foreach ($provinces as $prov)
            $provinces_for_url[$prov->getProvId()] = Utils::urlNormalize($prov->getProvName());

        $view = $session->get('search_view_results_destination');
        $paginator = $this->get('ideup.simple_paginator');
        $items_per_page = ($view != null) ? ($view != 'PHOTOS' ? 5 : 9) : 5;;
        $paginator->setItemsPerPage($items_per_page);
        $list = $em->getRepository('mycpBundle:destination')->getAccommodationsNear($destination->getDesId(), null,null, $users_id['user_id'], $users_id['session_id']);
        $owns_nearby = $paginator->paginate($list)->getResult();

        $em->getRepository('mycpBundle:userHistory')->insert(false, $destination->getDesId(), $users_id);

        $own_ids = "0";
        foreach ($list as $own)
            $own_ids .= "," . $own['own_id'];
        $session->set('own_ids', $own_ids);

        return $this->render('FrontEndBundle:destination:destinationDetails.html.twig', array(
                    'destination' => $destination_array[0],
                    'is_in_favorite' => $em->getRepository('mycpBundle:favorite')->is_in_favorite($destination->getDesId(), false, $users_id["user_id"], $users_id["session_id"]),
                    'autocomplete_text_list' => $em->getRepository('mycpBundle:ownership')->autocomplete_text_list(),
                    'locale' => $this->get('translator')->getLocale(),
                    'location' => $destination_array['municipality_name'].' / '.$destination_array['province_name'],
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
                    'total_items' => $paginator->getTotalItems(),
                    'destination_name' => $original_destination_name,
                    'data_view' => (($view == null) ? 'LIST' : $view),
                    'popular_destinations_for_url' =>$popular_destinations_for_url,
                    'other_destinations_in_municipality_for_url' => $other_destinations_in_municipality_for_url,
                    'other_destinations_in_province_for_url' => $other_destinations_in_province_for_url,
                    'provinces_for_url' => $provinces_for_url,
                    'keyword_description'=>$destination_array['keyword_description'],
                    'keyword'=>$destination_array['keywords']
        ));
    }

    public function owns_nearby_callbackAction($destination_name,$destination_id) {
        $request = $this->getRequest();
        $session = $request->getSession();
        $em = $this->getDoctrine()->getManager();
        $users_id = $em->getRepository('mycpBundle:user')->user_ids($this);
        $show_rows = $request->request->get('show_rows');

        if ($show_rows != null)
            $session->set('destination_details_show_rows', $show_rows);
        else if ($session->get("destination_details_show_rows") == null)
            $session->set('destination_details_show_rows', 3);

        $view = $session->get('search_view_results_destination');

        $paginator = $this->get('ideup.simple_paginator');
        $items_per_page = ($view != null) ? ($view != 'PHOTOS' ? 5 : 9) : 5;
        $paginator->setItemsPerPage($items_per_page);
        $owns_nearby = $paginator->paginate($em->getRepository('mycpBundle:destination')->getAccommodationsNear($destination_id, null,null, $users_id['user_id'], $users_id['session_id']))->getResult();


        $response = $this->renderView('FrontEndBundle:destination:detailsOwnsNearByDestination.html.twig', array(
            'owns_nearby' => $owns_nearby,
            'destination_name' => $destination_name,
            'data_view' => (($view == null) ? 'LIST' : $view),
            'items_per_page' => $items_per_page,
            'total_items' => $paginator->getTotalItems()
        ));

        return new Response($response, 200);
    }

    public function byprovinceAction()
    {
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
        if (isset($_GET['page']))
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

    public function search_change_view_resultsAction($destination_name,$destination_id) {
        $request = $this->getRequest();
        $session = $request->getSession();
        $em = $this->getDoctrine()->getManager();
        $users_id = $em->getRepository('mycpBundle:user')->user_ids($this);

        $view = $request->request->get('view');
        $session->set('search_view_results_destination', $view);

        if ($session->get("destination_details_show_rows") == null)
            $session->set('destination_details_show_rows', 3);

        $paginator = $this->get('ideup.simple_paginator');
        //$items_per_page = $session->get("destination_details_show_rows");
        $items_per_page = ($view != null) ? ($view != 'PHOTOS' ? 5 : 6) : 5;
        $paginator->setItemsPerPage($items_per_page);
        $owns_nearby = $paginator->paginate($em->getRepository('mycpBundle:destination')->getAccommodationsNear($destination_id, null,null, $users_id['user_id'], $users_id['session_id']))->getResult();

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
