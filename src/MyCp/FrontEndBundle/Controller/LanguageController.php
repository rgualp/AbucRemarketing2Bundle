<?php

namespace MyCp\FrontEndBundle\Controller;

use BeSimple\I18nRoutingBundle\Tests\Routing\RouterTest;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class LanguageController extends Controller {

    public function get_languagesAction() {

        $em = $this->getDoctrine()->getManager();
        $languages = $em->getRepository('mycpBundle:lang')->findBy(array('lang_active' => 1), array('lang_name' => 'ASC'));
        $response = $this->render('FrontEndBundle:language:languages.html.twig', array('languages' => $languages));

        // cache control
        $response->setSharedMaxAge(36000);

        return $response;
    }

    public function changeAction($lang) {

        $request = $this->getRequest();
        $session = $request->getSession();
        $em = $this->getDoctrine()->getManager();
        $last_route=$session->get('app_last_route');
        $route_array=explode('.',$last_route);
        $last_route=$route_array[0];
        $last_route_params=$session->get('app_last_route_params');
        $last_route_params['_locale']=$lang;
        $last_route_params['locale']=$lang;

        $last_route = empty($last_route) ? 'frontend_welcome' : $last_route;
        $new_route=$this->get('router')->generate($last_route,$last_route_params);

        //Guardar en userTourist el lenguaje q cambio
        $lang = $em->getRepository('mycpBundle:lang')->findOneBy(array('lang_code' => $lang));
        $user = $this->getUser();

        if ($user != null && $user!='anon.') {
            $userTourist = $em->getRepository('mycpBundle:userTourist')->findOneBy(array('user_tourist_user' => $user->getUserId()));

            // if the user is not a tourist (e.g. a staff member), the
            // userTourist does not exist
            if(isset($userTourist)) {
                $userTourist->setUserTouristLanguage($lang);
                $em->persist($userTourist);
                $em->flush();
            }
        }

        return $this->redirect($new_route);
    }

}
