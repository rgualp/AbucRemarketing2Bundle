<?php

namespace MyCp\frontEndBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\SecurityContext;

class PublicController extends Controller {

    public function home_pageAction() {
        return $this->redirect($this->generateUrl('frontend_welcome'));
    }


    public function welcomeAction() {
        $em = $this->getDoctrine()->getEntityManager();
        $session = $this->getRequest()->getSession();
        $glogal_locale = $this->get('translator')->getLocale();
        
        $provinces = $em->getRepository('mycpBundle:province')->findAll();
        $slide_folder = rand(1, 1);
        
        $paginator = $this->get('ideup.simple_paginator');
        $items_per_page = 4 * ($session->get("top_rated_show_rows") != null ? $session->get("top_rated_show_rows") : 2);
        $paginator->setItemsPerPage($items_per_page);
        $own_top20_list = $paginator->paginate($em->getRepository('mycpBundle:ownership')->top20($glogal_locale))->getResult();
        
        $response = $this->render('frontEndBundle:public:home.html.twig', array(
            'locale' => $glogal_locale,
            'provinces' => $provinces,
            'slide_folder' => $slide_folder,
            'own_top20_list' => $own_top20_list
        ));
        
        return $response;
    }

    public function loginAction() {
        //$this->getRequest()->setLocale('en');
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

    public function home_carrouselAction() {
        $em = $this->getDoctrine()->getEntityManager();
        $user_ids = $em->getRepository('mycpBundle:user')->user_ids($this);

        $popular_destinations_list = $em->getRepository('mycpBundle:destination')->get_popular_destination(12, $user_ids['user_id'], $user_ids['session_id']);
        $last_added = $em->getRepository('mycpBundle:ownership')->lastAdded(12, $user_ids['user_id'], $user_ids['session_id']);
        $offers_list = array();
        $economic_own_list = $em->getRepository('mycpBundle:ownership')->getByCategory('Económica', 12,null, $user_ids['user_id'], $user_ids['session_id']);
        $medium_own_list = $em->getRepository('mycpBundle:ownership')->getByCategory('Rango medio', 12,null, $user_ids['user_id'], $user_ids['session_id']);
        $premium_own_list = $em->getRepository('mycpBundle:ownership')->getByCategory('Premium', 12,null, $user_ids['user_id'], $user_ids['session_id']);

        return $this->render('frontEndBundle:public:homeCarousel.html.twig', array(
                    'popular_places' => $popular_destinations_list,
                    'last_added' => $last_added,
                    'offers' => $offers_list,
                    'economic_own_list' => $economic_own_list,
                    'medium_own_list' => $medium_own_list,
                    'premium_own_list' => $premium_own_list
        ));
    }

    public function recommend2FriendAction() {
        $request = $this->getRequest();
        $email_from = $request->get('email_from');
        $email_type = $request->get('email_type');
        $name_from = $request->get('name_from');
        $email_to = $request->get('email_to');
        $em = $this->getDoctrine()->getEntityManager();
        $service_email = $this->get('Email');
        switch ($email_type) {
            case 'recommend_general':
                $result = $service_email->recommend2Friend($email_from, $name_from, $email_to);
                break;
            case 'recommend_property':
                $property_id = $request->get('dest_prop_id');
                $property = $em->getRepository('mycpBundle:ownership')->find($property_id);
                $result = $service_email->recommendProperty2Friend($email_from, $name_from, $email_to, $property);
                break;
            case 'recommend_destiny':
                $destiny_id = $request->get('dest_prop_id');
                $destination = $em->getRepository('mycpBundle:destination')->find($destiny_id);
                $result = $service_email->recommendDestiny2Friend($email_from, $name_from, $email_to, $destination);
                break;
        }

        return new Response($result ? "ok" : "error");
    }
    
    public function get_main_menu_destinationsAction()
    {
        $em = $this->getDoctrine()->getEntityManager();
        $destinations = $em->getRepository('mycpBundle:destination')->get_for_main_menu();
        return $this->render('frontEndBundle:utils:mainMenuDestinationItems.html.twig', array(
              'destinations'=>$destinations  
        ));
    }
    
    public function get_main_menu_accomodationsAction()
    {
        $em = $this->getDoctrine()->getEntityManager();
        $provinces = $em->getRepository('mycpBundle:province')->get_for_main_menu();
        return $this->render('frontEndBundle:utils:mainMenuAccomodationItems.html.twig', array(
              'provinces'=>$provinces  
        ));
    }
    
    public function get_main_menu_mycasatripAction()
    {
        $em = $this->getDoctrine()->getEntityManager();
        $user = $this->get('security.context')->getToken()->getUser();
        $notifications = ($user != null && $user != "anon.") ? $em->getRepository('mycpBundle:ownershipReservation')->get_for_main_menu($user->getUserId()) : array();
                
        return $this->render('frontEndBundle:utils:mainMenuMyCasaTripItems.html.twig', array(
              'notifications'=>($user != null && $user != "anon.") ?$notifications[0]['available']: 0  
        ));
    }
}
