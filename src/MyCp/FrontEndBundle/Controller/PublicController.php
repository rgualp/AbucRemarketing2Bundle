<?php

namespace MyCp\FrontEndBundle\Controller;

use MyCp\FrontEndBundle\Helpers\Utils;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Security\Core\SecurityContext;
use MyCp\mycpBundle\Entity\metaTag;

class PublicController extends Controller {

    public function homePageAction() {
        return $this->redirect($this->generateUrl('frontend_welcome'));
    }

    public function getMetaTagsAction($section = metaTag::SECTION_GENERAL, $onlyDescription = false)
    {
        $em = $this->getDoctrine()->getManager();
        $lang_code = $this->getRequest()->getLocale();
        $metas=$em->getRepository('mycpBundle:metaTag')->getMetas($section, $lang_code);

        if(!$onlyDescription)
        {
        $response = $this->render('FrontEndBundle:public:metas.html.twig', array(
            'metas'=>$metas
        ));

        return $response;
        }
        else
            return new Response(($metas != null) ? $metas->getMetaLangDescription(): "");
    }

    public function welcomeAction() {
        $mobileDetector = $this->get('mobile_detect.mobile_detector');

        $em = $this->getDoctrine()->getManager();
        $session = $this->getRequest()->getSession();
        $glogal_locale = $this->get('translator')->getLocale();

        //var_dump($session->get("just_logged"));
        if($session->has("just_logged") && $session->get("just_logged"))
        {
            $locale = strtolower($session->get("user_lang"));
            //var_dump($locale);
            $locale = array('locale' => $locale, '_locale' => $locale);
            $session->remove("just_logged");
            return $this->redirect($this->generateUrl("frontend_welcome", $locale));
        }

        $provinces = $em->getRepository('mycpBundle:province')->findAll();
        $slide_folder = rand(1, 1);

        $paginator = $this->get('ideup.simple_paginator');

        if ($mobileDetector->isMobile()) {
            $items_per_page = 2;
        }else{
            $items_per_page = 3 * ($session->get("top_rated_show_rows") != null ? $session->get("top_rated_show_rows") : 2);
        }

        $paginator->setItemsPerPage($items_per_page);
        $user_ids = $em->getRepository('mycpBundle:user')->getIds($this);
        $own_top20_list = $paginator->paginate($em->getRepository('mycpBundle:ownership')->top20($glogal_locale, null, $user_ids["user_id"], $user_ids["session_id"]))->getResult();
        $statistics = $em->getRepository("mycpBundle:ownership")->top20Statistics();


        if ($mobileDetector->isMobile()){
            $response = $this->render('MyCpMobileFrontendBundle:frontend:index.html.twig', array(
                'locale' => $glogal_locale,
                'provinces' => $provinces,
                'slide_folder' => $slide_folder,
                'own_top20_list' => $own_top20_list,
                'top_rated_total_items' => $paginator->getTotalItems(),
                'premium_total' => $statistics['premium_total'],
                'midrange_total' => $statistics['midrange_total'],
                'economic_total' => $statistics['economic_total']
            ));
        }else{
            $response = $this->render('FrontEndBundle:public:home.html.twig', array(
                'locale' => $glogal_locale,
                'provinces' => $provinces,
                'slide_folder' => $slide_folder,
                'own_top20_list' => $own_top20_list,
                'premium_total' => $statistics['premium_total'],
                'midrange_total' => $statistics['midrange_total'],
                'economic_total' => $statistics['economic_total']
            ));
        }

        // cache control
        $response->setSharedMaxAge(600);

        return $response;
    }

    public function topNavAction($route, $routeParams = null)
    {
        $routeParams = empty($routeParams) ? array() : $routeParams;

        $em = $this->getDoctrine()->getManager();
        $user_ids = $em->getRepository('mycpBundle:user')->getIds($this);

        $countItems = $em->getRepository('mycpBundle:favorite')->getTotal($user_ids['user_id'],$user_ids['session_id']);
        $response = $this->render('FrontEndBundle:layout:topNav.html.twig', array(
                'route' => $route,
                'routeParams' => $routeParams,
                'count_fav'=>$countItems
            ));


        return $response;
    }

    public function loginAction() {
        $mobileDetector = $this->get('mobile_detect.mobile_detector');
        $request = $this->getRequest();
        $session = $request->getSession();
        if ($request->attributes->has(SecurityContext::AUTHENTICATION_ERROR)) {
            $error = $request->attributes->get(SecurityContext::AUTHENTICATION_ERROR);
        } else {
            $error = $session->get(SecurityContext::AUTHENTICATION_ERROR);
            $session->remove(SecurityContext::AUTHENTICATION_ERROR);
        }

        if ($mobileDetector->isMobile()) {
            return $this->render('@MyCpMobileFrontend/security/login.html.twig', array(
                'last_username' => $session->get(SecurityContext::LAST_USERNAME),
                'error' => $error,
            ));
        }else{
            return $this->render('FrontEndBundle:public:login.html.twig', array(
                'last_username' => $session->get(SecurityContext::LAST_USERNAME),
                'error' => $error,
            ));
        }

    }

    public function homeCarrouselAction() {
        $em = $this->getDoctrine()->getManager();
        $user_ids = $em->getRepository('mycpBundle:user')->getIds($this);

        $popular_destinations_list = $em->getRepository('mycpBundle:destination')->getPopularDestinations(12, $user_ids['user_id'], $user_ids['session_id']);
        /*$last_added = $em->getRepository('mycpBundle:ownership')->lastAdded(12, $user_ids['user_id'], $user_ids['session_id']);
        $offers_list = array();
        $economic_own_list = $em->getRepository('mycpBundle:ownership')->getByCategory('Económica', 12,null, $user_ids['user_id'], $user_ids['session_id']);
        $medium_own_list = $em->getRepository('mycpBundle:ownership')->getByCategory('Rango medio', 12,null, $user_ids['user_id'], $user_ids['session_id']);
        $premium_own_list = $em->getRepository('mycpBundle:ownership')->getByCategory('Premium', 12,null, $user_ids['user_id'], $user_ids['session_id']);*/

        return $this->render('FrontEndBundle:public:homeCarousel.html.twig', array(
                    'popular_places' => $popular_destinations_list,
                    //'last_added' => $last_added,
                    //'offers' => $offers_list,
                    //'economic_own_list' => $economic_own_list,
                    //'medium_own_list' => $medium_own_list,
                    //'premium_own_list' => $premium_own_list
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

        if(!Utils::validateEmail($email_from) || !Utils::validateEmail($email_to))
            return new Response("error");

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

    public function getMainMenuDestinationsAction()
    {
        $em = $this->getDoctrine()->getManager();
        $destinations = $em->getRepository('mycpBundle:destination')->getMainMenu();

         $for_url = array();

        foreach ($destinations as $prov)
        {
            $prov['des_name'] = str_replace("ñ", "nn", $prov['des_name']);
            $for_url[$prov['des_id']] = Utils::urlNormalize($prov['des_name']);
            $for_url[$prov['des_id']] = str_replace("nn", "ñ", $for_url[$prov['des_id']]);
        }

        return $this->render('FrontEndBundle:utils:mainMenuDestinationItems.html.twig', array(
              'destinations'=>$destinations,
              'for_url' => $for_url
        ));
    }

    public function getMainMenuAccomodationsAction()
    {
        $em = $this->getDoctrine()->getManager();
        $provinces = $em->getRepository('mycpBundle:province')->getMainMenu();

        $for_url = array();

        foreach ($provinces as $prov)
            $for_url[$prov['prov_id']] = Utils::urlNormalize($prov['prov_name']);

        return $this->render('FrontEndBundle:utils:mainMenuAccomodationItems.html.twig', array(
              'provinces'=>$provinces,
              'for_url' => $for_url
        ));
    }

    public function getMainMenuMycasatripAction()
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $notifications = ($user != null && $user != "anon.") ? $em->getRepository('mycpBundle:ownershipReservation')->getMainMenu($user->getUserId()) : array();

        return $this->render('FrontEndBundle:utils:mainMenuMyCasaTripItems.html.twig', array(
              'notifications'=>($user != null && $user != "anon.") ?$notifications[0]['available']: 0
        ));
    }

    public function siteMapAction()
    {
        $em = $this->getDoctrine()->getManager();
        $urls = array();
        $hostname = $this->getRequest()->getHost();

        $languages=$em->getRepository('mycpBundle:lang')->findBy(array('lang_active'=>1));

        //houses
        $url_houses=array();
        $houses=$em->getRepository('mycpBundle:ownership')->findBy(array('own_status'=>  \MyCp\mycpBundle\Entity\ownershipStatus::STATUS_ACTIVE));
        foreach($languages as $lang) {
            $routingParams = array('locale' => strtolower($lang->getLangCode()), '_locale' => strtolower($lang->getLangCode()));

            $url = array(
                'loc' => $this->get('router')->generate('frontend_search_ownership', $routingParams),
                'priority' => '0.8',
                'changefreq'=> 'monthly'
            );
            array_push($url_houses,$url);
            foreach($houses as $house)
            {
                $house_name=Utils::urlNormalize($house->getOwnName());
                $url = array(
                    'loc' => $this->get('router')->generate('frontend_details_ownership',
                            array_merge($routingParams, array('own_name' => $house_name))),
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
            $routingParams = array('locale' => strtolower($lang->getLangCode()), '_locale' => strtolower($lang->getLangCode()));

            $url = array(
                'loc' => $this->get('router')->generate('frontend_list_destinations', $routingParams),
                'priority' => '0.8',
                'changefreq'=> 'monthly'
            );
            array_push($url_destinations,$url);
            foreach($destinations as $destination)
            {
                $destination_name=Utils::urlNormalize($destination->getDesName());
                $url = array(
                    'loc' => $this->get('router')->generate('frontend_details_destination',
                            array_merge($routingParams, array('destination_name' => $destination_name))),
                    'priority' => '0.8',
                    'changefreq'=> 'monthly'
                );
                array_push($url_destinations,$url);
            }
        }

        // site pages
        $url_sites=array();
        foreach($languages as $lang) {
            $routingParams = array('locale' => strtolower($lang->getLangCode()), '_locale' => strtolower($lang->getLangCode()));

            $url = array(
                'loc' => $this->get('router')->generate('frontend_welcome', $routingParams),
                'priority' => '0.5',
                'changefreq'=> 'daily'
            );
            array_push($url_sites,$url);

            $url = array(
                'loc' => $this->get('router')->generate('frontend_how_it_works_information', $routingParams),
                'priority' => '0.5',
                'changefreq'=> 'daily'
            );
            array_push($url_sites,$url);

            $url = array(
                'loc' => $this->get('router')->generate('frontend_list_favorite', $routingParams),
                'priority' => '0.5',
                'changefreq'=> 'daily'
            );
            array_push($url_sites,$url);

            $url = array(
                'loc' => $this->get('router')->generate('frontend_view_cart', $routingParams),
                'priority' => '0.5',
                'changefreq'=> 'daily'
            );
            array_push($url_sites,$url);

            $url = array(
                'loc' => $this->get('router')->generate('frontend_register_user', $routingParams),
                'priority' => '0.5',
                'changefreq'=> 'daily'
            );
            array_push($url_sites,$url);


            $url = array(
                'loc' => $this->get('router')->generate('frontend_restore_password_user', $routingParams),
                'priority' => '0.5',
                'changefreq'=> 'daily'
            );
            array_push($url_sites,$url);

            $url = array(
                'loc' => $this->get('router')->generate('frontend_register_confirmation_user', $routingParams),
                'priority' => '0.5',
                'changefreq'=> 'daily'
            );
            array_push($url_sites,$url);

            $url = array(
                'loc' => $this->get('router')->generate('frontend_mycasatrip_pending', $routingParams),
                'priority' => '0.5',
                'changefreq'=> 'daily'
            );
            array_push($url_sites,$url);

            $url = array(
                'loc' => $this->get('router')->generate('frontend_get_with_reservations_municipality', $routingParams),
                'priority' => '0.5',
                'changefreq'=> 'daily'
            );
            array_push($url_sites,$url);

            $url = array(
                'loc' => $this->get('router')->generate('frontend_voted_best_list_ownership', $routingParams),
                'priority' => '0.5',
                'changefreq'=> 'daily'
            );
            array_push($url_sites,$url);

            $url = array(
                'loc' => $this->get('router')->generate('frontend_about_us', $routingParams),
                'priority' => '0.5',
                'changefreq'=> 'daily'
            );
            array_push($url_sites,$url);

            $url = array(
                'loc' => $this->get('router')->generate('frontend_contact_user', $routingParams),
                'priority' => '0.5',
                'changefreq'=> 'daily'
            );
            array_push($url_sites,$url);

            $url = array(
                'loc' => $this->get('router')->generate('frontend_list_faq', $routingParams),
                'priority' => '0.5',
                'changefreq'=> 'daily'
            );
            array_push($url_sites,$url);

            $url = array(
                'loc' => $this->get('router')->generate('frontend_type_list_ownership',
                        array_merge($routingParams, array('type' => 'penthouse'))),
                'priority' => '0.5',
                'changefreq'=> 'daily'
            );
            array_push($url_sites,$url);

            $url = array(
                'loc' => $this->get('router')->generate('frontend_type_list_ownership',
                        array_merge($routingParams, array('type' => 'villa-con-piscina'))),
                'priority' => '0.5',
                'changefreq'=> 'daily'
            );
            array_push($url_sites,$url);

            $url = array(
                'loc' => $this->get('router')->generate('frontend_type_list_ownership',
                        array_merge($routingParams, array('type' => 'casa-particular'))),
                'priority' => '0.5',
                'changefreq'=> 'daily'
            );
            array_push($url_sites,$url);

            $url = array(
                'loc' => $this->get('router')->generate('frontend_type_list_ownership',
                        array_merge($routingParams, array('type' => 'apartamento'))),
                'priority' => '0.5',
                'changefreq'=> 'daily'
            );
            array_push($url_sites,$url);

            $url = array(
                'loc' => $this->get('router')->generate('frontend_type_list_ownership',
                        array_merge($routingParams, array('type' => 'propiedad-completa'))),
                'priority' => '0.5',
                'changefreq'=> 'daily'
            );
            array_push($url_sites,$url);

            $url = array(
                'loc' => $this->get('router')->generate('frontend_legal_terms', $routingParams),
                'priority' => '0.5',
                'changefreq'=> 'daily'
            );
            array_push($url_sites,$url);

            $url = array(
                'loc' => $this->get('router')->generate('frontend_security_privacity', $routingParams),
                'priority' => '0.5',
                'changefreq'=> 'daily'
            );
            array_push($url_sites,$url);

            $url = array(
                'loc' => $this->get('router')->generate('frontend_sitemap_information', $routingParams),
                'priority' => '0.5',
                'changefreq'=> 'daily'
            );
            array_push($url_sites,$url);

            //login
            $url = array(
                'loc' => $this->get('router')->generate('frontend_public_login', $routingParams),
                'priority' => '0.5',
                'changefreq'=> 'daily'
            );
            array_push($url_sites,$url);
        }

        return $this->render('FrontEndBundle:public:sitemap.html.twig', array(
            'url_sites'=>$url_sites,
            'urls_houses'=>$url_houses,
            'urls_destinations'=>$url_destinations,
            'hostname' => $hostname
        ));

    }

    public function appRentaDownloadAction(){
        //variable
        $pathToFile = $this->container->getParameter("configuration.dir.additionalsFiles");

        $pathToCont = $pathToFile."download_cont.txt";
        $file = fopen($pathToCont,"a");
        fclose($file);
        //fputs($ar,1);
        if (is_writeable($pathToCont)){
            $arrayFile=file($pathToCont);
            $arrayFile[0] = (count($arrayFile) <= 0) ? (1) : (++$arrayFile[0]);
            $file=fopen($pathToCont,"w");
            fwrite($file,$arrayFile[0]);
            fclose($file);
        }

        $response = new BinaryFileResponse($pathToFile.'MyCasaRenta.apk');
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT,'MyCasaRenta.apk');
        return $response;
    }

    public function appTripDownloadAction(){
        //variable
        $pathToFile = $this->container->getParameter("configuration.dir.additionalsFiles");

        $pathToCont = $pathToFile."download_cont_trip.txt";
        $file = fopen($pathToCont,"a");
        fclose($file);
        //fputs($ar,1);
        if (is_writeable($pathToCont)){
            $arrayFile=file($pathToCont);
            $arrayFile[0] = (count($arrayFile) <= 0) ? (1) : (++$arrayFile[0]);
            $file=fopen($pathToCont,"w");
            fwrite($file,$arrayFile[0]);
            fclose($file);
        }

        $response = new BinaryFileResponse($pathToFile.'MyCasaTrip.apk');
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT,'MyCasaTrip.apk');
        return $response;
    }
}
