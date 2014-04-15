<?php

namespace MyCp\frontEndBundle\Controller;

use MyCp\frontEndBundle\Helpers\Utils;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\SecurityContext;

class PublicController extends Controller {

    public function home_pageAction() {
        return $this->redirect($this->generateUrl('frontend_welcome'));
    }

    public function get_meta_tagsAction()
    {
        $em = $this->getDoctrine()->getManager();
        $lang=$em->getRepository('mycpBundle:lang')->findOneBy(array('lang_code'=>$this->getRequest()->getLocale()));
        $metas=$em->getRepository('mycpBundle:metaLang')->findOneBy(array('meta_lang_lang'=>$lang));

        $response = $this->render('frontEndBundle:public:metas.html.twig', array(
            'metas'=>$metas
        ));

        return $response;
    }

    public function welcomeAction() {
        $em = $this->getDoctrine()->getManager();
        $session = $this->getRequest()->getSession();
        $glogal_locale = $this->get('translator')->getLocale();
        
        $provinces = $em->getRepository('mycpBundle:province')->findAll();
        $slide_folder = rand(1, 1);
        
        $paginator = $this->get('ideup.simple_paginator');
        $items_per_page = 4 * ($session->get("top_rated_show_rows") != null ? $session->get("top_rated_show_rows") : 2);
        $paginator->setItemsPerPage($items_per_page);
        $own_top20_list = $paginator->paginate($em->getRepository('mycpBundle:ownership')->top20($glogal_locale))->getResult();
        $statistics = $em->getRepository("mycpBundle:ownership")->top20_statistics();

        $response = $this->render('frontEndBundle:public:home.html.twig', array(
            'locale' => $glogal_locale,
            'provinces' => $provinces,
            'slide_folder' => $slide_folder,
            'own_top20_list' => $own_top20_list,
            'premium_total' => $statistics['premium_total'],
            'midrange_total' => $statistics['midrange_total'],
            'economic_total' => $statistics['economic_total']
        ));
        
        return $response;
    }

    public function loginAction() {
        //$this->getRequest()->setLocale('en');
        $request = $this->getRequest();
        $session = $request->getSession();
        $services=$request->getSession()->get('services_pre_reservation');
        if ($request->attributes->has(SecurityContext::AUTHENTICATION_ERROR)) {
            $error = $request->attributes->get(SecurityContext::AUTHENTICATION_ERROR);
        } else {
            $error = $session->get(SecurityContext::AUTHENTICATION_ERROR);
            $session->remove(SecurityContext::AUTHENTICATION_ERROR);
        }
        $request->getSession()->set('services_pre_reservation', $services);
        return $this->render('frontEndBundle:public:login.html.twig', array(
                    'last_username' => $session->get(SecurityContext::LAST_USERNAME),
                    'error' => $error,
        ));
    }

    public function home_carrouselAction() {
        $em = $this->getDoctrine()->getManager();
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
        $em = $this->getDoctrine()->getManager();
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
        $em = $this->getDoctrine()->getManager();
        $destinations = $em->getRepository('mycpBundle:destination')->get_for_main_menu();
        
         $for_url = array();
        
        foreach ($destinations as $prov)
            $for_url[$prov['des_id']] = Utils::url_normalize($prov['des_name']);

        return $this->render('frontEndBundle:utils:mainMenuDestinationItems.html.twig', array(
              'destinations'=>$destinations,
              'for_url' => $for_url
        ));
    }
    
    public function get_main_menu_accomodationsAction()
    {
        $em = $this->getDoctrine()->getManager();
        $provinces = $em->getRepository('mycpBundle:province')->get_for_main_menu();
        
        $for_url = array();
        
        foreach ($provinces as $prov)
            $for_url[$prov['prov_id']] = Utils::url_normalize($prov['prov_name']);
        
        return $this->render('frontEndBundle:utils:mainMenuAccomodationItems.html.twig', array(
              'provinces'=>$provinces,
              'for_url' => $for_url
        ));
    }
    
    public function get_main_menu_mycasatripAction()
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $notifications = ($user != null && $user != "anon.") ? $em->getRepository('mycpBundle:ownershipReservation')->get_for_main_menu($user->getUserId()) : array();
                
        return $this->render('frontEndBundle:utils:mainMenuMyCasaTripItems.html.twig', array(
              'notifications'=>($user != null && $user != "anon.") ?$notifications[0]['available']: 0  
        ));
    }

    public function site_mapAction()
    {
        $em = $this->getDoctrine()->getEntityManager();
        $urls = array();
        $hostname = $this->getRequest()->getHost();

        $languages=$em->getRepository('mycpBundle:lang')->findBy(array('lang_active'=>1));

        //houses
        $url_houses=array();
        $houses=$em->getRepository('mycpBundle:ownership')->findBy(array('own_status'=>1));
        foreach($languages as $lang) {
            $url = array(
                'loc' => $this->get('router')->generate('frontend_search_ownership',array('locale' => strtolower($lang->getLangCode()),'_locale' => strtolower($lang->getLangCode()))),
                'priority' => '0.8',
                'changefreq'=> 'monthly'
            );
            array_push($url_houses,$url);
            foreach($houses as $house)
            {
                $house_name=Utils::url_normalize($house->getOwnName());
                $url = array(
                    'loc' => $this->get('router')->generate('frontend_details_ownership',array('locale' => strtolower($lang->getLangCode()),'_locale' => strtolower($lang->getLangCode()),'own_name' => $house_name)),
                    'priority' => '1.0',
                    'changefreq'=> 'monthly'
                );

                array_push($url_houses,$url);
            }
        }

        //destinations
        $url_destinations=array();
        array_push($url_destinations,$url);
        $destinations=$em->getRepository('mycpBundle:destination')->findBy(array('des_active'=>1));
        foreach($languages as $lang) {
            $url = array(
                'loc' => $this->get('router')->generate('frontend_list_destinations',array('locale' => strtolower($lang->getLangCode()),'_locale' => strtolower($lang->getLangCode()))),
                'priority' => '0.8',
                'changefreq'=> 'monthly'
            );
            array_push($url_destinations,$url);
            foreach($destinations as $destination)
            {
                $destination_name=Utils::url_normalize($destination->getDesName());
                $url = array(
                    'loc' => $this->get('router')->generate('frontend_details_destination',array('locale' => strtolower($lang->getLangCode()),'_locale' => strtolower($lang->getLangCode()),'destination_name' => $destination_name)),
                    'priority' => '0.8',
                    'changefreq'=> 'monthly'
                );
                array_push($url_destinations,$url);
            }
        }

        // site pages
        $url_sites=array();
        foreach($languages as $lang) {
            $url = array(
                'loc' => $this->get('router')->generate('frontend_welcome',array('locale' => strtolower($lang->getLangCode()),'_locale' => strtolower($lang->getLangCode()))),
                'priority' => '0.5',
                'changefreq'=> 'daily'
            );
            array_push($url_sites,$url);

            $url = array(
                'loc' => $this->get('router')->generate('frontend_how_it_works_information',array('locale' => strtolower($lang->getLangCode()),'_locale' => strtolower($lang->getLangCode()))),
                'priority' => '0.5',
                'changefreq'=> 'daily'
            );
            array_push($url_sites,$url);

            $url = array(
                'loc' => $this->get('router')->generate('frontend_list_favorite',array('locale' => strtolower($lang->getLangCode()),'_locale' => strtolower($lang->getLangCode()))),
                'priority' => '0.5',
                'changefreq'=> 'daily'
            );
            array_push($url_sites,$url);

            $url = array(
                'loc' => $this->get('router')->generate('frontend_review_reservation',array('locale' => strtolower($lang->getLangCode()),'_locale' => strtolower($lang->getLangCode()))),
                'priority' => '0.5',
                'changefreq'=> 'daily'
            );
            array_push($url_sites,$url);

            $url = array(
                'loc' => $this->get('router')->generate('frontend_register_user',array('locale' => strtolower($lang->getLangCode()),'_locale' => strtolower($lang->getLangCode()))),
                'priority' => '0.5',
                'changefreq'=> 'daily'
            );
            array_push($url_sites,$url);


            $url = array(
                'loc' => $this->get('router')->generate('frontend_restore_password_user',array('locale' => strtolower($lang->getLangCode()),'_locale' => strtolower($lang->getLangCode()))),
                'priority' => '0.5',
                'changefreq'=> 'daily'
            );
            array_push($url_sites,$url);

            $url = array(
                'loc' => $this->get('router')->generate('frontend_register_confirmation_user',array('locale' => strtolower($lang->getLangCode()),'_locale' => strtolower($lang->getLangCode()))),
                'priority' => '0.5',
                'changefreq'=> 'daily'
            );
            array_push($url_sites,$url);

            $url = array(
                'loc' => $this->get('router')->generate('frontend_mycasatrip_pending',array('locale' => strtolower($lang->getLangCode()),'_locale' => strtolower($lang->getLangCode()))),
                'priority' => '0.5',
                'changefreq'=> 'daily'
            );
            array_push($url_sites,$url);

            $url = array(
                'loc' => $this->get('router')->generate('frontend_get_with_reservations_municipality',array('locale' => strtolower($lang->getLangCode()),'_locale' => strtolower($lang->getLangCode()))),
                'priority' => '0.5',
                'changefreq'=> 'daily'
            );
            array_push($url_sites,$url);

            $url = array(
                'loc' => $this->get('router')->generate('frontend_voted_best_list_ownership',array('locale' => strtolower($lang->getLangCode()),'_locale' => strtolower($lang->getLangCode()))),
                'priority' => '0.5',
                'changefreq'=> 'daily'
            );
            array_push($url_sites,$url);

            $url = array(
                'loc' => $this->get('router')->generate('frontend_about_us',array('locale' => strtolower($lang->getLangCode()),'_locale' => strtolower($lang->getLangCode()))),
                'priority' => '0.5',
                'changefreq'=> 'daily'
            );
            array_push($url_sites,$url);

            $url = array(
                'loc' => $this->get('router')->generate('frontend_contact_user',array('locale' => strtolower($lang->getLangCode()),'_locale' => strtolower($lang->getLangCode()))),
                'priority' => '0.5',
                'changefreq'=> 'daily'
            );
            array_push($url_sites,$url);

            $url = array(
                'loc' => $this->get('router')->generate('frontend_list_faq',array('locale' => strtolower($lang->getLangCode()),'_locale' => strtolower($lang->getLangCode()))),
                'priority' => '0.5',
                'changefreq'=> 'daily'
            );
            array_push($url_sites,$url);

            $url = array(
                'loc' => $this->get('router')->generate('frontend_type_list_ownership',array('type'=>'penthouse','locale' => strtolower($lang->getLangCode()),'_locale' => strtolower($lang->getLangCode()))),
                'priority' => '0.5',
                'changefreq'=> 'daily'
            );
            array_push($url_sites,$url);

            $url = array(
                'loc' => $this->get('router')->generate('frontend_type_list_ownership',array('type'=>'villa-con-piscina','locale' => strtolower($lang->getLangCode()),'_locale' => strtolower($lang->getLangCode()))),
                'priority' => '0.5',
                'changefreq'=> 'daily'
            );
            array_push($url_sites,$url);

            $url = array(
                'loc' => $this->get('router')->generate('frontend_type_list_ownership',array('type'=>'casa-particular','locale' => strtolower($lang->getLangCode()),'_locale' => strtolower($lang->getLangCode()))),
                'priority' => '0.5',
                'changefreq'=> 'daily'
            );
            array_push($url_sites,$url);

            $url = array(
                'loc' => $this->get('router')->generate('frontend_type_list_ownership',array('type'=>'apartamento','locale' => strtolower($lang->getLangCode()),'_locale' => strtolower($lang->getLangCode()))),
                'priority' => '0.5',
                'changefreq'=> 'daily'
            );
            array_push($url_sites,$url);

            $url = array(
                'loc' => $this->get('router')->generate('frontend_type_list_ownership',array('type'=>'propiedad-completa','locale' => strtolower($lang->getLangCode()),'_locale' => strtolower($lang->getLangCode()))),
                'priority' => '0.5',
                'changefreq'=> 'daily'
            );
            array_push($url_sites,$url);

            $url = array(
                'loc' => $this->get('router')->generate('frontend_legal_terms',array('locale' => strtolower($lang->getLangCode()),'_locale' => strtolower($lang->getLangCode()))),
                'priority' => '0.5',
                'changefreq'=> 'daily'
            );
            array_push($url_sites,$url);

            $url = array(
                'loc' => $this->get('router')->generate('frontend_security_privacity',array('locale' => strtolower($lang->getLangCode()),'_locale' => strtolower($lang->getLangCode()))),
                'priority' => '0.5',
                'changefreq'=> 'daily'
            );
            array_push($url_sites,$url);

            $url = array(
                'loc' => $this->get('router')->generate('frontend_sitemap_information',array('locale' => strtolower($lang->getLangCode()),'_locale' => strtolower($lang->getLangCode()))),
                'priority' => '0.5',
                'changefreq'=> 'daily'
            );
            array_push($url_sites,$url);


        }
        //login
        $url = array(
            'loc' => $this->get('router')->generate('mycp_login'),
            'priority' => '0.5',
            'changefreq'=> 'daily'
        );
        array_push($url_sites,$url);

        return $this->render('frontEndBundle:public:sitemap.html.twig', array(
            'url_sites'=>$url_sites,
            'urls_houses'=>$url_houses,
            'urls_destinations'=>$url_destinations,
            'hostname' => $hostname
        ));

    }
}
