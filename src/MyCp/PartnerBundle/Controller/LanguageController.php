<?php

namespace MyCp\PartnerBundle\Controller;

use BeSimple\I18nRoutingBundle\Tests\Routing\RouterTest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class LanguageController extends Controller
{
    /**
     * @param $route
     * @param null $routeParams
     * @return Response
     */
    public function getLanguagesAction($route, $routeParams = null)
    {
        $routeParams = empty($routeParams) ? array() : $routeParams;

        $em = $this->getDoctrine()->getManager();
        $languages["ES"] = $em->getRepository("mycpBundle:lang")->findOneBy(array("lang_code" => "ES"));
        $languages["EN"] = $em->getRepository("mycpBundle:lang")->findOneBy(array("lang_code" => "EN"));

        $response = $this->render('PartnerBundle:language:languages.html.twig', array(
            'languages' => $languages,
            'route' => $route,
            'routeParams' => $routeParams
        ));
        $response->setSharedMaxAge(3600);
        return $response;
    }

    /**
     * @param $lang
     * @param $route
     * @param null $routeParams
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function changeAction($lang, $route, $routeParams = null)
    {
        $em = $this->getDoctrine()->getManager();

        $routeParams = empty($routeParams) ? array() : json_decode(urldecode($routeParams), true);
        $routeParams['_locale'] = $lang;
        $routeParams['locale'] = $lang;
        $newRoute = $this->get('router')->generate($route, $routeParams);

        //Guardar en usuario el lenguaje que cambio
        $lang = $em->getRepository('mycpBundle:lang')->findOneBy(array('lang_code' => $lang));
        $user = $this->getUser();
        if (!empty($user) && $user != 'anon.') {
            $user->setUserLanguage($lang);
            $em->persist($user);
            $em->flush();
        }

        return $this->redirect($newRoute);
    }
}
