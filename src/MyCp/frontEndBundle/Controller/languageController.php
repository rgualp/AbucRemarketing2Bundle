<?php

namespace MyCp\frontEndBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class languageController extends Controller {

    public function get_languagesAction() {

        $em = $this->getDoctrine()->getManager();
        $languages = $em->getRepository('mycpBundle:lang')->findBy(array('lang_active' => 1), array('lang_name' => 'ASC'));
        return $this->render('frontEndBundle:language:languages.html.twig', array('languages' => $languages));
    }

    public function changeAction($lang) {

        $request = $this->getRequest();
        $session = $request->getSession();
        $em = $this->getDoctrine()->getManager();
        $last_route=$session->get('app_last_route');
        $last_route_params=$session->get('app_last_route_params');
        $last_route_params['_locale']=$lang;
        $new_route=$this->get('router')->generate($last_route,$last_route_params);

        //Guardar en userTourist el lenguaje q cambio
        $lang = $em->getRepository('mycpBundle:lang')->findOneBy(array('lang_code' => $lang));
        $user = $this->getUser();

        if ($user != null && $user!='anon.') {
            $userTourist = $em->getRepository('mycpBundle:userTourist')->findOneBy(array('user_tourist_user' => $user->getUserId()));
            $userTourist->setUserTouristLanguage($lang);
            $em->persist($userTourist);
            $em->flush();
        }

        return $this->redirect($new_route);
    }

}
