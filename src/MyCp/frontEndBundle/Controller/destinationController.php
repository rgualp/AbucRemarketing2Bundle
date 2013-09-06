<?php

namespace MyCp\frontEndBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;


class destinationController extends Controller
{
    /**
     * Este lo modifique
     */
    public function popular_listAction($municipality_id = null) { 
        $em = $this->getDoctrine()->getEntityManager();
        $locale = $this->get('translator')->getLocale();
        
        $paginator = $this->get('ideup.simple_paginator');
        $items_per_page = 15;
        $paginator->setItemsPerPage($items_per_page);
        
        $province_id = null;
        $municipalities = null;
        $mun_total_destinations = array();
        if($municipality_id == null)
            $popular_destinations_list=$paginator->paginate($em->getRepository('mycpBundle:destination')->get_popular_destination())->getResult();
        else
        {
            $province_id = $em->getRepository('mycpBundle:municipality')->find($municipality_id)->getMunProvId()->getProvId();
            $municipalities = $municipalities = $em->getRepository('mycpBundle:municipality')->findBy(array('mun_prov_id' => $province_id), array('mun_name' => "ASC"));
            $popular_destinations_list=$paginator->paginate($em->getRepository('mycpBundle:destination')->destination_filter($municipality_id))->getResult(); 
            
            $mun_total = 0;
        $total = 0;

        foreach ($municipalities as $mun) {
            $total = count($em->getRepository('mycpBundle:destinationLocation')->findBy(array('des_loc_municipality' => $mun->getMunId())));
            $mun_total_destinations[$mun->getMunId()] = $total;

            if ($total > 0)
                $mun_total = $mun_total + 1;
        }
        }
        $page=1;
        if(isset($_GET['page']))$page=$_GET['page'];
        
        $popular_destinations_photos_list = $em->getRepository('mycpBundle:destination')->get_destination_photos($popular_destinations_list);
        $popular_destinations_description_list = $em->getRepository('mycpBundle:destination')->get_destination_description($popular_destinations_list, $locale);
        $popular_destinations_location_list = $em->getRepository('mycpBundle:destination')->get_destination_location($popular_destinations_list);

        $provinces = $em->getRepository('mycpBundle:province')->findAll();

        $total_destination_provinces = array();
        $total_province = 0;

        foreach ($provinces as $prov) {
            $total_province = count($em->getRepository('mycpBundle:destination')->get_destination_by_location($prov->getProvId()));
            $total_destination_provinces[$prov->getProvId()] = $total_province;
        }


        return $this->render('frontEndBundle:destination:listDestination.html.twig', array(
            'popular_places' => $popular_destinations_list,
            'popular_places_photos' => $popular_destinations_photos_list,
            'popular_places_description' => $popular_destinations_description_list,
            'popular_places_location' => $popular_destinations_location_list,
            'provinces' => $provinces,
            'destination_provinces' => $total_destination_provinces,
            'items_per_page'=>$items_per_page,
            'total_items'=>$paginator->getTotalItems(),
            'current_page'=>$page,
            'municipality_id' => $municipality_id,
            'province_id' => $province_id,
            'municipalities' => $municipalities,
            'mun_total_destinations' => $mun_total_destinations
        ));
    }

    public function detailsAction($destination_id){
        $em = $this->getDoctrine()->getEntityManager();
        $destination = $em->getRepository('mycpBundle:destination')->find($destination_id);
        $currency_list = $em->getRepository('mycpBundle:currency')->findAll();
        $languages_list = $em->getRepository('mycpBundle:lang')->get_active_languages();
        $locale = $this->get('translator')->getLocale();
        $description = $em->getRepository('mycpBundle:destinationLang')->findOneBy(array(
            'des_lang_lang' => $em->getRepository('mycpBundle:lang')->findOneBy(array('lang_code' => $locale)),
            'des_lang_destination' => $destination_id
                ));

        $photos = $em->getRepository('mycpBundle:destination')->getPhotos($destination_id);
        $photo_descriptions = $em->getRepository('mycpBundle:destination')->getPhotoDescription($photos, $locale);

        $location = $em->getRepository('mycpBundle:destinationLocation')->findOneBy(array('des_loc_destination' => $destination_id));

        $location_municipality = $location->getDesLocMunicipality();
        $location_province = $location->getDesLocProvince();

        $location_municipality_id = ($location_municipality != null) ? $location_municipality->getMunId() : null;
        $location_province_id = ($location_province != null) ? $location_province->getProvId() : null;

        $other_destinations_in_municipality = $em->getRepository('mycpBundle:destination')->destination_filter($location_municipality_id, $location_province_id, $destination_id, null, 5);
        $other_destinations_in_province = $em->getRepository('mycpBundle:destination')->destination_filter(null, $location_province_id, $destination_id, $location_municipality_id, 5);
        $owns_nearby = $em->getRepository('mycpBundle:destination')->ownsership_nearby_destination($location_municipality_id, $location_province_id, 5);

        $owns_nearby_photos = $em->getRepository('mycpBundle:ownership')->get_photos_array($owns_nearby);
        return $this->render('frontEndBundle:destination:destinationDetails.html.twig', array(
                    'destination' => $destination,
                    'location' => $location,
                    'gallery_photos' => $photos,
                    'gallery_photo_descriptions' => $photo_descriptions,
                    'description' => ($description != null) ? $description->getDesLangDesc() : null,
                    'currency_list' => $currency_list,
                    'languages_list' => $languages_list,
                    'other_destinations_in_municipality' => $other_destinations_in_municipality,
                    'other_destinations_in_municipality_photos' => $em->getRepository('mycpBundle:destination')->get_destination_photos($other_destinations_in_municipality),
                    'other_destinations_in_municipality_description' => $em->getRepository('mycpBundle:destination')->get_destination_description($other_destinations_in_municipality, $locale),
                    'total_other_destinations_in_municipality' => count($other_destinations_in_municipality),
                    'other_destinations_in_province' => $other_destinations_in_province,
                    'other_destinations_in_province_photos' => $em->getRepository('mycpBundle:destination')->get_destination_photos($other_destinations_in_province),
                    'other_destinations_in_province_description' => $em->getRepository('mycpBundle:destination')->get_destination_description($other_destinations_in_province, $locale),
                    'total_other_destinations_in_province' => count($other_destinations_in_province),
                    'owns_nearby' => $owns_nearby,
                    'owns_nearby_photos' => $owns_nearby_photos
                ));
    }
    
    public function filterAction() {
        $em = $this->getDoctrine()->getEntityManager();
        $request = $this->getRequest();
        $prov_id = $request->request->get('province');
        $mun_id = $request->request->get('municipality');
        
        $paginator = $this->get('ideup.simple_paginator');
        $items_per_page = 15;
        $paginator->setItemsPerPage($items_per_page);
        $popular_places=$paginator->paginate($em->getRepository('mycpBundle:destination')->destination_filter($mun_id, $prov_id))->getResult(); 
        $page=1;
        if(isset($_GET['page']))$page=$_GET['page'];
        
        $popular_places_photos = $em->getRepository('mycpBundle:destination')->get_destination_photos($popular_places);
        $popular_places_description = $em->getRepository('mycpBundle:destination')->get_destination_description($popular_places, 'ES');
        $popular_places_location = $em->getRepository('mycpBundle:destination')->get_destination_location($popular_places);

        $response = $this->renderView('frontEndBundle:destination:itemListDestination.html.twig', array(
            'popular_places' => $popular_places,
            'popular_places_photos' => $popular_places_photos,
            'popular_places_description' => $popular_places_description,
            'popular_places_location' => $popular_places_location,
            'items_per_page'=>$items_per_page,
            'total_items'=>$paginator->getTotalItems(),
            'current_page'=>$page
                ));

        return new Response($response, 200);
    }
}
