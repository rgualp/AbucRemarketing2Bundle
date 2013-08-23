<?php

namespace MyCp\frontEndBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class PublicController extends Controller
{
    public function welcomeAction()
    {
        $em = $this->getDoctrine()->getEntityManager();
        //$province_list = $em->getRepository('mycpBundle:province')->findAll();
        $own_top20_list = $em->getRepository('mycpBundle:ownership')->top20();

        $own_top20_photos = array();
        $own_top20_descriptions = array();

        $glogal_locale = $this->get('translator')->getLocale();

        foreach ($own_top20_list as $own) {
            $own_photo = $em->getRepository('mycpBundle:ownershipPhoto')->findOneBy(array(
                'own_pho_own' => $own->getOwnId()));

            $own_description = $em->getRepository('mycpBundle:ownershipDescriptionLang')->findOneBy(array(
                'odl_id_lang' => $em->getRepository('mycpBundle:lang')->findOneBy(array('lang_code' => $glogal_locale)),
                'odl_ownership' => $own->getOwnId()
            ));

            $own_top20_descriptions[$own->getOwnId()] = substr($own_description, 0, 120) . ((strlen($own_description) > 120) ? "..." : "");


            if ($own_photo != null) {
                $photo_name = $own_photo->getOwnPhoPhoto()->getPhoName();


                if (file_exists(realpath("uploads/ownershipImages/" . $photo_name))) {
                    $own_top20_photos[$own->getOwnId()] = $photo_name;
                } else {
                    $own_top20_photos[$own->getOwnId()] = 'no_photo.png';
                }
            } else {
                $own_top20_photos[$own->getOwnId()] = 'no_photo.png';
            }
        }

        /**
         * Tabs bottom
         */
        $popular_destinations_list = $em->getRepository('mycpBundle:destination')->get_popular_destination(4);
        $popular_destinations_photos = $em->getRepository('mycpBundle:destination')->get_destination_photos($popular_destinations_list);
        $popular_places_localization = $em->getRepository('mycpBundle:destination')->get_destination_location($popular_destinations_list);
        $last_added = $em->getRepository('mycpBundle:ownership')->lastAdded(4);
        $last_added_photos = $this->getArrayFotos($last_added);

        $offers_list = array();

        $economic_own_list = $em->getRepository('mycpBundle:ownership')->getByCategory('Económica', 4);
        $economy_own_photos = $this->getArrayFotos($economic_own_list);

        $medium_own_list = $em->getRepository('mycpBundle:ownership')->getByCategory('Rango medio', 4);
        $medium_own_photos = $this->getArrayFotos($medium_own_list);

        $premium_own_list = $em->getRepository('mycpBundle:ownership')->getByCategory('Premium', 4);
        $premium_own_photos = $this->getArrayFotos($premium_own_list);

        return $this->render('frontEndBundle:public:home.html.twig', array(
            //'province_list' => $province_list,
            'own_top20_list' => $own_top20_list,
            'own_top20_photos' => $own_top20_photos,
            'own_top20_descriptions' => $own_top20_descriptions,
            'popular_places' => $popular_destinations_list,
            'popular_places_photos' => $popular_destinations_photos,
            'popular_places_localization' => $popular_places_localization,
            'last_added' => $last_added,
            'last_added_photos' => $last_added_photos,
            'offers' => $offers_list,
            'economic_own_list' => $economic_own_list,
            'economy_own_photos' => $economy_own_photos,
            'medium_own_list' => $medium_own_list,
            'medium_own_photos' => $medium_own_photos,
            'premium_own_list' => $premium_own_list,
            'premium_own_photos' => $premium_own_photos,
            'locale' => $glogal_locale,
             'autocomplete_text_list'  => $this->autocomplete_text_list()
        ));
    }

    function getArrayFotos($ownership_list) {
        $em = $this->getDoctrine()->getEntityManager();
        $photos = array();

        if (is_array($ownership_list)) {
            foreach ($ownership_list as $own) {
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

    public function loginAction()
    {
        $request = $this->getRequest();
        $session = $request->getSession();

        if ($request->attributes->has(SecurityContext::AUTHENTICATION_ERROR)) {
            $error = $request->attributes->get(SecurityContext::AUTHENTICATION_ERROR);
        } else {
            $error = $session->get(SecurityContext::AUTHENTICATION_ERROR);
            $session->remove(SecurityContext::AUTHENTICATION_ERROR);
        }
        return $this->render('frontEndBundle:public:login.html.twig', array(
            'last_username' => $session->get(SecurityContext::LAST_USERNAME),
            'error' => $error,
        ));
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
}
