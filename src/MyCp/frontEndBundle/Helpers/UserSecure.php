<?php

namespace MyCp\frontEndBundle\Helpers;

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
        $user = $this->security_context->getToken()->getUser();
        $session = $this->container->get('session');
        //Cambiar el sitio a la moneda y lenguaje ultimo del sitio almacenados en userTourist
        $userTourist = $this->em->getRepository('mycpBundle:userTourist')->findOneBy(array('user_tourist_user' => $user->getUserId()));
        $currencies = $this->em->getRepository('mycpBundle:currency')->findAll();
        $tourist_currency = null;
        if ($userTourist)
            $tourist_currency = $userTourist->getUserTouristCurrency();

        if (!$tourist_currency) {
            $curr_by_default = $this->em->getRepository('mycpBundle:currency')->findOneBy(array('curr_default' => 1));

            if (isset($curr_by_default)) {
                $this->container->get('session')->set("curr_rate", $curr_by_default->getCurrCucChange());
                $this->container->get('session')->set("curr_symbol", $curr_by_default->getCurrSymbol());
                $this->container->get('session')->set("curr_acronym", $curr_by_default->getCurrCode());
            } else {
                $session->set("curr_rate", 1);
                $session->set("curr_symbol", "$");
                $session->set("curr_acronym", "CUC");
            }
        }
        //var_dump($_SESSION); exit();
        //exit();

        /*
          var_dump($this->container->get('request')->getLocale());

          $tourist_lang = $userTourist->getUserTouristLanguage();
          $locale = ($tourist_lang == null) ? "ES" : strtolower($tourist_lang->getLangCode());
          $this->container->get('translator')->setLocale($locale);
          $this->container->get('request')->setLocale($locale);
          $this->container->get('request')->setDefaultLocale($locale);
          $session->set('app_lang_name', (($tourist_lang == null) ? "Español" : $tourist_lang->getLangName()));
          $session->set("_locale", $locale); */

        //Pasar lo q esta en los favoritos al usuario loggueado
        $session_id = $this->em->getRepository('mycpBundle:user')->get_session_id_with_request($this->container->get('request'));

        if ($session_id != null)
            $this->em->getRepository('mycpBundle:favorite')->set_to_user($user->getUserId(), $session_id);

        //Pasar lo q esta en el historial al usuario loggueado
        if ($session_id != null)
            $this->em->getRepository('mycpBundle:userHistory')->set_to_user($user->getUserId(), $session_id);

        /* var_dump($this->container->get('request')->getLocale());
          echo "<pre>";
          var_dump($this->container->get('request'));
          echo "</pre>";
          exit(); */
    }

}