<?php

namespace MyCp\FrontEndBundle\Controller;

use BeSimple\I18nRoutingBundle\Tests\Routing\RouterTest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class LanguageController extends Controller
{
    public function getLanguagesAction($route, $routeParams = null)
    {
        $routeParams = empty($routeParams) ? array() : $routeParams;

        $em = $this->getDoctrine()->getManager();
        $languages = $em
            ->getRepository('mycpBundle:lang')
            ->findBy(array('lang_active' => 1), array('lang_name' => 'ASC'));

        $response = $this->render('FrontEndBundle:language:languages.html.twig', array(
                'languages' => $languages,
                'route' => $route,
                'routeParams' => $routeParams
            ));

        // cache control -> languages rarely change
        $response->setSharedMaxAge(3600);

        return $response;
    }

    public function changeAction($lang, $route, $routeParams = null)
    {
        $em = $this->getDoctrine()->getManager();

        $routeParams = empty($routeParams) ? array() : json_decode(urldecode($routeParams), true);
        $routeParams['_locale'] = $lang;
        $routeParams['locale'] = $lang;
        $newRoute = $this->get('router')->generate($route, $routeParams);

        //Guardar en userTourist el lenguaje q cambio
        $lang = $em->getRepository('mycpBundle:lang')->findOneBy(array('lang_code' => $lang));
        $user = $this->getUser();

        if (!empty($user) && $user != 'anon.') {
            $userTourist = $em
                ->getRepository('mycpBundle:userTourist')
                ->findOneBy(array('user_tourist_user' => $user->getUserId()));

            // if the user is not a tourist (e.g. a staff member), the
            // userTourist does not exist
            if(isset($userTourist)) {
                $userTourist->setUserTouristLanguage($lang);
                $em->persist($userTourist);
                $em->flush();
            }
        }

        return $this->redirect($newRoute);
    }
}
