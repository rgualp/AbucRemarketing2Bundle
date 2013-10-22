<?php

namespace MyCp\frontEndBundle\Helpers;

use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

class UserSecure {

    private $em;
    private $container;
    private $security_context;

    public function __construct($entity_manager, $container, $security_context) {
        $this->em = $entity_manager;
        $this->container = $container;
        $this->security_context = $security_context;
    }

    public function onSecurityInteractiveLogin(InteractiveLoginEvent $event) {
        $user = $this->security_context->getToken()->getUser();
        $session = $this->container->get('session');
        //exit();
        //Cambiar el sitio a la moneda y lenguaje ultimo del sitio almacenados en userTourist
        $userTourist = $this->em->getRepository('mycpBundle:userTourist')->findOneBy(array('user_tourist_user' => $user->getUserId()));

        /*$tourist_currency = $userTourist->getUserTouristCurrency();
        $session->set("curr_rate", ($tourist_currency == null) ? 1 : $tourist_currency->getCurrCucChange());
        $session->set("curr_symbol", ($tourist_currency == null) ? "$" : $tourist_currency->getCurrSymbol());
        $session->set("curr_acronym", ($tourist_currency == null) ? "CUC" : $tourist_currency->getCurrCode());
        
        var_dump($this->container->get('request')->getLocale());

        $tourist_lang = $userTourist->getUserTouristLanguage();
        $locale = ($tourist_lang == null) ? "ES" : strtolower($tourist_lang->getLangCode());
        $this->container->get('translator')->setLocale($locale);
        $this->container->get('request')->setLocale($locale);
        $this->container->get('request')->setDefaultLocale($locale);
        $session->set('app_lang_name', (($tourist_lang == null) ? "Español" : $tourist_lang->getLangName()));
        $session->set("_locale", $locale);*/

        //Pasar lo q esta en los favoritos al usuario loggueado
        $session_id = $this->em->getRepository('mycpBundle:user')->get_session_id_with_request($this->container->get('request'));
        
        if($session_id != null)
            $this->em->getRepository('mycpBundle:favorite')->set_to_user($user->getUserId(),$session_id);
        
        //Pasar lo q esta en el historial al usuario loggueado
        if($session_id != null)
            $this->em->getRepository('mycpBundle:userHistory')->set_to_user($user->getUserId(),$session_id);
        
        /*var_dump($this->container->get('request')->getLocale());
        echo "<pre>";
        var_dump($this->container->get('request'));
        echo "</pre>";
        exit();*/
        
    }

}