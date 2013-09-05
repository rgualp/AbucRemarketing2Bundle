<?php

namespace MyCp\frontEndBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class PublicController extends Controller {

    public function welcomeAction() {
        $em = $this->getDoctrine()->getEntityManager();
        //$province_list = $em->getRepository('mycpBundle:province')->findAll();
        $paginator = $this->get('ideup.simple_paginator');
        $items_per_page = 1;
        $paginator->setItemsPerPage($items_per_page);
        $own_top20_list = $paginator->paginate($em->getRepository('mycpBundle:ownership')->top20())->getResult();
        $page = 1;
        if (isset($_GET['page']))
            $page = $_GET['page'];

        $own_top20_photos = $em->getRepository('mycpBundle:ownership')->get_photos_array($own_top20_list);
        $own_top20_descriptions = array();

        $glogal_locale = $this->get('translator')->getLocale();

        foreach ($own_top20_list as $own) {

            $own_description = $em->getRepository('mycpBundle:ownershipDescriptionLang')->findOneBy(array(
                'odl_id_lang' => $em->getRepository('mycpBundle:lang')->findOneBy(array('lang_code' => $glogal_locale)),
                'odl_ownership' => $own->getOwnId()
                    ));

            $own_top20_descriptions[$own->getOwnId()] = substr($own_description, 0, 120) . ((strlen($own_description) > 120) ? "..." : "");
        }

        /**
         * Tabs bottom
         */
        $user_ids = $em->getRepository('mycpBundle:favorite')->user_ids($this);

        $popular_destinations_list = $em->getRepository('mycpBundle:destination')->get_popular_destination(4);
        $popular_destinations_photos = $em->getRepository('mycpBundle:destination')->get_destination_photos($popular_destinations_list);
        $popular_places_localization = $em->getRepository('mycpBundle:destination')->get_destination_location($popular_destinations_list);
        $popular_places_statistics = $em->getRepository('mycpBundle:destination')->get_destination_owns_statistics($popular_destinations_list);
        $popular_destination_favorites = $em->getRepository('mycpBundle:favorite')->is_in_favorite_array($popular_destinations_list, false,$user_ids['user_id'], $user_ids['session_id']);
        
        $last_added = $em->getRepository('mycpBundle:ownership')->lastAdded(4);
        $last_added_photos = $em->getRepository('mycpBundle:ownership')->get_photos_array($last_added);
        $last_added_favorites = $em->getRepository('mycpBundle:favorite')->is_in_favorite_array($last_added, true,$user_ids['user_id'], $user_ids['session_id']);

        $offers_list = array();

        $economic_own_list = $em->getRepository('mycpBundle:ownership')->getByCategory('Económica', 4);
        $economy_own_photos = $em->getRepository('mycpBundle:ownership')->get_photos_array($economic_own_list);
        $economic_own_favorites = $em->getRepository('mycpBundle:favorite')->is_in_favorite_array($economic_own_list, true,$user_ids['user_id'], $user_ids['session_id']);

        $medium_own_list = $em->getRepository('mycpBundle:ownership')->getByCategory('Rango medio', 4);
        $medium_own_photos = $em->getRepository('mycpBundle:ownership')->get_photos_array($medium_own_list);
        $medium_own_favorites = $em->getRepository('mycpBundle:favorite')->is_in_favorite_array($medium_own_list, true,$user_ids['user_id'], $user_ids['session_id']);

        $premium_own_list = $em->getRepository('mycpBundle:ownership')->getByCategory('Premium', 4);
        $premium_own_photos = $em->getRepository('mycpBundle:ownership')->get_photos_array($premium_own_list);
        $premium_own_favorites = $em->getRepository('mycpBundle:favorite')->is_in_favorite_array($premium_own_list, true,$user_ids['user_id'], $user_ids['session_id']);

        /**
         * Agregue esta parte - Yanet 
         */
        $provinces = $em->getRepository('mycpBundle:province')->findAll();

        return $this->render('frontEndBundle:public:home.html.twig', array(
                    //'province_list' => $province_list,
                    'own_top20_list' => $own_top20_list,
                    'own_top20_photos' => $own_top20_photos,
                    'own_top20_descriptions' => $own_top20_descriptions,
                    'popular_places' => $popular_destinations_list,
                    'popular_places_photos' => $popular_destinations_photos,
                    'popular_places_localization' => $popular_places_localization,
                    'popular_destination_favorites' => $popular_destination_favorites,
                    'last_added' => $last_added,
                    'last_added_photos' => $last_added_photos,
                    'last_added_favorites' => $last_added_favorites,
                    'offers' => $offers_list,
                    'economic_own_list' => $economic_own_list,
                    'economy_own_photos' => $economy_own_photos,
                    'economic_own_favorites' => $economic_own_favorites,
                    'medium_own_list' => $medium_own_list,
                    'medium_own_photos' => $medium_own_photos,
                    'medium_own_favorites' => $medium_own_favorites,
                    'premium_own_list' => $premium_own_list,
                    'premium_own_photos' => $premium_own_photos,
                    'premium_own_favorites' => $premium_own_favorites,
                    'locale' => $glogal_locale,
                    'autocomplete_text_list' => $this->autocomplete_text_list(),
                    'popular_places_statistics' => $popular_places_statistics,
                    'provinces' => $provinces,
                    'top_rated_items_per_page' => $items_per_page,
                    'top_rated_total_items' => $paginator->getTotalItems(),
                    'current_page' => $page,
                ));
    }

    public function loginAction() {
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
        $municipalities = $em->getRepository('mycpBundle:municipality')->get_municipalities();
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

//    private function user_ids() {
//        $user_id = null;
//        $session_id = null;
//        $user = $this->get('security.context')->getToken()->getUser();
//        
//        if ($user != null && $user != "anon.")
//            $user_id = $user->getUserId();
//
//        if ($user_id == null) {
//            if (isset($_COOKIE["mycp_user_session"]))
//                $session_id = $_COOKIE["mycp_user_session"];
//            else {
//                $session = $this->getRequest()->getSession();
//                $now = time();
//                $session_id = $session->getId()."_".$now;
//                setcookie("mycp_user_session", $session_id, time() + 3600);
//            }
//        }
//        
//        return array('user_id' => $user_id, 'session_id' => $session_id);
//    }

}
