<?php

namespace MyCp\frontEndBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class languageController extends Controller {

    public function get_languagesAction() {
        $em = $this->getDoctrine()->getEntityManager();
        $languages = $em->getRepository('mycpBundle:lang')->findBy(array('lang_active' => 1), array('lang_name' => 'ASC'));
        return $this->render('frontEndBundle:language:languages.html.twig', array('languages' => $languages));
    }

    public function changeAction() {
        $request = $this->getRequest();
        $session = $request->getSession();
        $em = $this->getDoctrine()->getEntityManager();

        $lang_id = $request->request->get('lang_id');
        $lang_code = $request->request->get('lang_code');
        $lang_name = $request->request->get('lang_name');
        $this->get('translator')->setLocale($lang_code);
        $session->set('app_lang_name', $lang_name);
        $session->set("_locale", $lang_code);
        //echo $lang_name;
        //$this->redirect($refresh_url);
        //Guardar en userTourist el lenguaje q cambio
        $lang = $em->getRepository('mycpBundle:lang')->findOneBy(array('lang_code' => $lang_code));
        $user = $this->get('security.context')->getToken()->getUser();

        if ($user != null) {
            $userTourist = $em->getRepository('mycpBundle:userTourist')->findOneBy(array('user_tourist_user' => $user->getUserId()));
            $userTourist->setUserTouristLanguage($lang);
            $em->persist($userTourist);
            $em->flush();
        }

        return new Response(null, 200);
    }

}
