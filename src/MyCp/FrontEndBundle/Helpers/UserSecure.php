<?php

namespace MyCp\FrontEndBundle\Helpers;

use BeSimple\SoapWsdl\Dumper\Dumper;
use Symfony\Component\Security\Core\Event\AuthenticationFailureEvent;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpFoundation\RedirectResponse;

class UserSecure {

    private $em;
    private $container;
    private $security_context;

    public function __construct($entity_manager, $container, $security_context) {
        $this->em = $entity_manager;
        $this->container = $container;
        $this->security_context = $security_context;
    }

    public function onKernelResponse(FilterResponseEvent $event) {
        $token = $this->security_context->getToken();
        if ($token != null && $token->getUser() != 'anon.' && $this->security_context->isGranted('ROLE_CLIENT_TOURIST')) {
            $user = $this->security_context->getToken()->getUser();
            if ($user->getUserEnabled() != 1) {
                $this->security_context->setToken(null);
                $this->container->get('request')->getSession()->invalidate();
                $login_route = $this->container->get('router')->generate('frontend_login');
                $this->container->get('session')->getFlashBag()->add('message_error_local', 'NOT_ENABLED_USER');
                $event->setResponse(new RedirectResponse($login_route));
            }
        }
    }

    public function onSecurityInteractiveLogin(InteractiveLoginEvent $event) {
        $session = $this->container->get('session');
        $user = $this->security_context->getToken()->getUser();
       /* $hash_user = hash('sha256', $user->getUserUserName());
        $hash_email = hash('sha256', $user->getUserEmail());
        if($user->getRegisterNotification()==''){
            //Registrando al user en HDS-MEAN
            // abrimos la sesión cURL
            $ch = curl_init();
            // definimos la URL a la que hacemos la petición
            curl_setopt($ch, CURLOPT_URL,$this->container->getParameter('url.mean')."register");
            // definimos el número de campos o parámetros que enviamos mediante POST
            curl_setopt($ch, CURLOPT_POST, 1);
            // definimos cada uno de los parámetros
            curl_setopt($ch, CURLOPT_POSTFIELDS, "email=".$hash_email.'_'.$this->container->getParameter('mean_project')."&last=".$user->getUserLastName()."&first=".$user->getUserLastName()."&password=".$user->getUserPassword()."&username=".$hash_user.'_'.$this->container->getParameter('mean_project'));
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            // recibimos la respuesta y la guardamos en una variable
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $remote_server_output = curl_exec ($ch);
            // cerramos la sesión cURL
            curl_close ($ch);
            $user->setRegisterNotification(true);
            $this->em->persist($user);
            $this->em->flush();
        }
        //// abrimos la sesión cURL
        $ch = curl_init();
        // definimos la URL a la que hacemos la petición
        curl_setopt($ch, CURLOPT_URL,$this->container->getParameter('url.mean')."access-token?username=".$hash_user.'_'.$this->container->getParameter('mean_project')."&password=".$user->getPassword()."&email=".$hash_email.'_'.$this->container->getParameter('mean_project'));
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        // recibimos la respuesta y la guardamos en una variable
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec ($ch);
        // cerramos la sesión cURL
        curl_close ($ch);
        if(!$response) {
            $session->set('access-token', "");
        }else{
            $response_temp= json_decode($response);
            $session->set('access-token', $response_temp->token);
            $user->setOnline(true);
            $this->em->persist($user);
            $this->em->flush();
        }*/


        //Pasar lo q esta en los favoritos al usuario loggueado
        $session_id = $this->em->getRepository('mycpBundle:user')->getSessionIdWithRequest($this->container->get('request'));

        if ($session_id != null)
        {
            $this->em->getRepository('mycpBundle:favorite')->setToUser($user, $session_id);
            $this->em->getRepository('mycpBundle:userHistory')->setToUser($user, $session_id);
            $this->em->getRepository('mycpBundle:cart')->setToUser($user, $session_id);
        }

        //Cambiar el sitio a la moneda y lenguaje ultimo del sitio almacenados en userTourist
        $userTourist = $this->em->getRepository('mycpBundle:userTourist')->findOneBy(array('user_tourist_user' => $user->getUserId()));

        $tourist_currency = null;
        if ($userTourist) {
            $tourist_currency = $userTourist->getUserTouristCurrency();

            if (!$tourist_currency) {
                $curr_by_default = $this->em->getRepository('mycpBundle:currency')->findOneBy(array('curr_default' => 1));

                if (isset($curr_by_default)) {
                    $this->container->get('session')->set("curr_rate", $curr_by_default->getCurrCucChange());
                    $this->container->get('session')->set("curr_symbol", $curr_by_default->getCurrSymbol());
                    $this->container->get('session')->set("curr_acronym", $curr_by_default->getCurrCode());
                } else {
                    $price_in_currency = $this->em->getRepository('mycpBundle:currency')->findOneBy(array('curr_site_price_in' => true));
                    $session->set("curr_rate", $price_in_currency->getCurrCucChange());
                    $session->set("curr_symbol", $price_in_currency->getCurrSymbol());
                    $session->set("curr_acronym", $price_in_currency->getCurrCode());
                }
            }

            $tourist_language = $userTourist->getUserTouristLanguage();

            if (isset($tourist_language)) {
                $locale = strtolower($tourist_language->getLangCode());
                $session->set('user_lang', $locale);
                $session->set('app_lang_name', $tourist_language->getLangName());
                $session->set('app_lang_code', $tourist_language->getLangCode());
                $session->set("just_logged", true);

                //$locale = array('locale' => $locale, '_locale' => $locale);
            }
        }
    }

    public function onAuthenticationFailure(AuthenticationFailureEvent $event)
    {
        $lang = $this->container->get('session')->get('user_language');
        $this->container->get('session')->set('user_failure_language', $lang);
    }

}
