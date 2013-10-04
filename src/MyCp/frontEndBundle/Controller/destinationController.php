<?php

namespace MyCp\frontEndBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class destinationController extends Controller {

    public function popular_listAction() {
        $em = $this->getDoctrine()->getEntityManager();
        $locale = $this->get('translator')->getLocale();
        $dest_list = $em->getRepository('mycpBundle:destination')->getAllDestinations($locale);

        return $this->render('frontEndBundle:destination:listDestination.html.twig', array(
                    'main_destinations' => array_slice($dest_list, 0, 6),
                    'provinces' => $em->getRepository('mycpBundle:province')->findAll(),
                    'all_destinations' => $dest_list
        ));
    }

    public function detailsAction($destination_name) {

        $em = $this->getDoctrine()->getEntityManager();
        $users_id = $em->getRepository('mycpBundle:user')->user_ids($this);
        $locale = $this->get('translator')->getLocale();
        $destination_name=str_replace('_',' ',$destination_name);
        $destination= $em->getRepository('mycpBundle:destination')->findOneBy(array('des_name'=>$destination_name));
        $destination_array = $em->getRepository('mycpBundle:destination')->get_destination($destination->getDesId(),$locale);
                
        $photos = $em->getRepository('mycpBundle:destination')->getPhotos($destination->getDesId(),$locale);

        $location_municipality_id = $destination_array['municipality_id'];
        $location_province_id = $destination_array['province_id'];

        $popular_destinations_list = $em->getRepository('mycpBundle:destination')->get_popular_destination(5, $users_id["user_id"], $users_id["session_id"]);
        $other_destinations_in_municipality = $em->getRepository('mycpBundle:destination')->destination_filter($location_municipality_id, $location_province_id, $destination->getDesId(), null, 5);
        $other_destinations_in_province = $em->getRepository('mycpBundle:destination')->destination_filter(null, $location_province_id, $destination->getDesId(), null, 5);
        
        $em->getRepository('mycpBundle:userHistory')->insert(false, $destination->getDesId(), $users_id);

        return $this->render('frontEndBundle:destination:destinationDetails.html.twig', array(
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
                    'provinces' => $em->getRepository("mycpBundle:province")->findAll()
        ));
    }

    public function owns_nearby_callbackAction($destination_id) {
        $request = $this->getRequest();
        $session = $request->getSession();
        $em = $this->getDoctrine()->getEntityManager();
        $users_id = $em->getRepository('mycpBundle:user')->user_ids($this);
        $show_rows = $request->request->get('show_rows');

        if ($show_rows != null)
            $session->set('destination_details_show_rows', $show_rows);
        else if ($session->get("destination_details_show_rows") == null)
            $session->set('destination_details_show_rows', 3);

        $location = $em->getRepository('mycpBundle:destinationLocation')->findOneBy(array('des_loc_destination' => $destination_id));

        $location_municipality = $location->getDesLocMunicipality();
        $location_province = $location->getDesLocProvince();

        $location_municipality_id = ($location_municipality != null) ? $location_municipality->getMunId() : null;
        $location_province_id = ($location_province != null) ? $location_province->getProvId() : null;

        $paginator = $this->get('ideup.simple_paginator');
        $items_per_page = 4 * $session->get("destination_details_show_rows");
        $paginator->setItemsPerPage($items_per_page);
        $owns_nearby = $paginator->paginate($em->getRepository('mycpBundle:destination')->ownsership_nearby_destination($location_municipality_id, $location_province_id, $items_per_page))->getResult();
        $page = 1;
        if (isset($_GET['page']))
            $page = $_GET['page'];

        $owns_nearby_photos = $em->getRepository('mycpBundle:ownership')->get_photos_array($owns_nearby);
        $owns_nearby_rooms = $em->getRepository('mycpBundle:ownership')->get_rooms_array($owns_nearby);
        $owns_nearby_is_in_favorities = $em->getRepository('mycpBundle:favorite')->is_in_favorite_array($owns_nearby, true, $users_id['user_id'], $users_id['session_id']);
        $owns_nearby_counts = $em->getRepository('mycpBundle:ownership')->get_counts_for_search($owns_nearby);

        $response = $this->renderView('frontEndBundle:destination:detailsOwnsNearByDestination.html.twig', array(
            'owns_nearby' => $owns_nearby,
            'owns_nearby_photos' => $owns_nearby_photos,
            'owns_nearby_rooms' => $owns_nearby_rooms,
            'owns_nearby_is_in_favorities' => $owns_nearby_is_in_favorities,
            'owns_nearby_counts' => $owns_nearby_counts,
            'top_rated_items_per_page' => $items_per_page,
            'top_rated_total_items' => $paginator->getTotalItems(),
            'current_page' => $page,
            'destination_id' => $destination_id
        ));

        return new Response($response, 200);
    }

    public function filterAction() {
        $em = $this->getDoctrine()->getEntityManager();
        $request = $this->getRequest();
        $prov_id = $request->request->get('province');
        $mun_id = $request->request->get('municipality');

        $paginator = $this->get('ideup.simple_paginator');
        $items_per_page = 15;
        $paginator->setItemsPerPage($items_per_page);
        $popular_places = $paginator->paginate($em->getRepository('mycpBundle:destination')->destination_filter($mun_id, $prov_id))->getResult();
        $page = 1;
        if (isset($_GET['page']))
            $page = $_GET['page'];

        $popular_places_photos = $em->getRepository('mycpBundle:destination')->get_destination_photos($popular_places);
        $popular_places_description = $em->getRepository('mycpBundle:destination')->get_destination_description($popular_places, 'ES');
        $popular_places_location = $em->getRepository('mycpBundle:destination')->get_destination_location($popular_places);

        $response = $this->renderView('frontEndBundle:destination:itemListDestination.html.twig', array(
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

}
