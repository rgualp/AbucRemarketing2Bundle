<?php

namespace MyCp\FrontEndBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CurrencyController extends Controller
{

    public function getCurrenciesAction(Request $request, $params, $route, $routeParams = null)
    {
        $em = $this->getDoctrine()->getManager();
        $currencies = $em->getRepository('mycpBundle:currency')->createQueryBuilder('currency')
            ->where('currency.curr_code != :current')
            ->setParameters( array('current'=> $this->get('session')->get('curr_symbol')))->getQuery()->getResult();
        $session = $request->getSession();
        $session->set("params_change_curr", $params);
        $routeParams = empty($routeParams) ? array() : $routeParams;
        $mobileDetector = $this->get('mobile_detect.mobile_detector');
        if ($mobileDetector->isMobile()) {
            $response = $this->render('MyCpMobileFrontendBundle:utils:currencies.html.twig', array(
                'currencies' => $currencies,
                'route' => $route,
                'routeParams' => $routeParams
            ));
        } else {
            $response = $this->render('FrontEndBundle:currency:currencies.html.twig', array(
                'currencies' => $currencies,
                'route' => $route,
                'routeParams' => $routeParams
            ));
        }
        // cache control -> currencies rarely change
        $response->setSharedMaxAge(3600);

        return $response;
    }

    public function changeAction(Request $request, $currencyCode, $route, $routeParams = null)
    {
        $routeParams = empty($routeParams) ? array() : json_decode(urldecode($routeParams), true);
        $session = $request->getSession();
        $em = $this->getDoctrine()->getManager();
        $currency = $em
            ->getRepository('mycpBundle:currency')
            ->findOneBy(array('curr_code' => $currencyCode));

        if (!empty($currency)) {
            $session->set("curr_rate", $currency->getCurrCucChange());
            $session->set("curr_symbol", $currency->getCurrSymbol());
            $session->set("curr_acronym", $currency->getCurrCode());
            $user = $this->getUser();

            if (!empty($user) && $user != 'anon.') {
                $userTourist = $em
                    ->getRepository('mycpBundle:userTourist')
                    ->findOneBy(array('user_tourist_user' => $user->getUserId()));

                if (!empty($userTourist)) {
                    $userTourist->setUserTouristCurrency($currency);
                    $em->persist($userTourist);
                    $em->flush();
                }
            }
        }
        return $this->setCurrency($currencyCode);
    }

    public function setCurrency($newcurrency)
    {
        $request = $this->get('request');
        // get last requested path
        $referer = $request->headers->get('referer');
        $lastPath = substr($referer, strpos($referer, $request->getHost()));
        $lastPath = str_replace($request->getHost(), '', $lastPath);
        // get last route
        $matcher = $this->get('router');
        $parameters = $matcher->match($lastPath);
        // set new locale (to session and to the route parameters)
        // default parameters has to be unsetted!
        $route = $parameters['_route'];
        unset($parameters['_route']);
        unset($parameters['_controller']);
        return new Response($this->generateUrl($route, $parameters), Response::HTTP_OK);
//        return $this->generateUrl($route, $parameters);
    }
}
