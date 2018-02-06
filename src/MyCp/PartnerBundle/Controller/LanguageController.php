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
    public function setLocale($newLocale) {
        $request = $this->getRequest();

        // get last requested path
        $referer = $request->headers->get('referer');
        $lastPath = substr($referer, strpos($referer, $request->getBaseUrl()));
        $lastPath = str_replace($request->getBaseUrl(), '', $lastPath);

        // get last route
        $matcher = $this->get('router');
        $parameters = $matcher->match($lastPath);

        // set new locale (to session and to the route parameters)
        $parameters['_locale'] = $newLocale;
        $parameters['locale']=$newLocale;

        // default parameters has to be unsetted!
        $route = $parameters['_route'];
        unset($parameters['_route']);
        unset($parameters['_controller']);


        return $this->generateUrl($route, $parameters);
    }
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


        $newRoute = $this->setLocale($lang);

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
