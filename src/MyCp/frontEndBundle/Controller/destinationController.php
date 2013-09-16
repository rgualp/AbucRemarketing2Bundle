<?php

namespace MyCp\frontEndBundle\Controller;

use MyCp\frontEndBundle\Helpers\Utils;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class destinationController extends Controller {

    public function popular_listAction() {
        $em = $this->getDoctrine()->getEntityManager();
        $locale = $this->get('translator')->getLocale();
        $dest_list = $em->getRepository('mycpBundle:destination')->getAllDestinations($locale);
        
//        foreach($dest_list as $item)
//        Utils::debug($item->getFirstDestinationPhotoName());
        
        return $this->render('frontEndBundle:destination:listDestination.html.twig', array(
                    'destinations' => $dest_list
        ));
    }

    public function detailsAction($destination_id) {
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

        $owns_nearby_photos = $this->getArrayFotos($owns_nearby);
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

        $popular_places = $em->getRepository('mycpBundle:destination')->destination_filter($mun_id, $prov_id);
        $popular_places_photos = $em->getRepository('mycpBundle:destination')->get_destination_photos($popular_places);
        $popular_places_description = $em->getRepository('mycpBundle:destination')->get_destination_description($popular_places, 'ES');
        $popular_places_location = $em->getRepository('mycpBundle:destination')->get_destination_location($popular_places);

        $response = $this->renderView('frontEndBundle:destination:itemListDestination.html.twig', array(
            'popular_places' => $popular_places,
            'popular_places_photos' => $popular_places_photos,
            'popular_places_description' => $popular_places_description,
            'popular_places_location' => $popular_places_location
        ));

        return new Response($response, 200);
    }

    function getArrayFotos($listado_casas) {
        $em = $this->getDoctrine()->getEntityManager();
        $photos = array();

        if (is_array($listado_casas)) {
            foreach ($listado_casas as $own) {
                $ownership_photo = $em->getRepository('mycpBundle:ownershipPhoto')->findOneBy(array(
                    'own_pho_own' => $own->getOwnId()));
                if ($ownership_photo != null) {
                    $photo_name = $ownership_photo->getOwnPhoPhoto()->getPhoName();


                    if (file_exists(realpath("uploads/ownershipImages/" . $photo_name))) {
                        $photos[$own->getOwnId()] = $photo_name;
                    } else {
                        $photos[$own->getOwnId()] = 'no_photo.png';
                    }
                } else {
                    $photos[$own->getOwnId()] = 'no_photo.png';
                }
            }
        }
        return $photos;
    }

}
