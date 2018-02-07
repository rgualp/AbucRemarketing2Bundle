<?php

namespace MyCp\PartnerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class CurrencyController extends Controller
{

    /**
     * @param Request $request
     * @param $params
     * @param $route
     * @param null $routeParams
     * @return Response
     */
    public function setCurrency($newcurrency) {
        $request = $this->getRequest();

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


        return $this->generateUrl($route, $parameters);
    }
    public function getCurrenciesAction(Request $request, $params, $route, $routeParams = null)
    {
        $em = $this->getDoctrine()->getManager();
        $currencies = $em->getRepository('mycpBundle:currency')->findAll();
        $session = $request->getSession();
        $session->set("params_change_curr",$params);
        $routeParams = empty($routeParams) ? array() : $routeParams;
        $response = $this->render('PartnerBundle:currency:currencies.html.twig', array(
                'currencies' => $currencies,
                'route' => $route,
                'routeParams' => $routeParams
            ));

        // cache control -> currencies rarely change
        $response->setSharedMaxAge(3600);

        return $response;
    }

    /**
     * @param Request $request
     * @param $currencyCode
     * @param $route
     * @param null $routeParams
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function changeAction(Request $request,
                                          $currencyCode,
                                          $route,
                                          $routeParams = null)
    {
        $routeParams = empty($routeParams) ? array() : json_decode(urldecode($routeParams), true);

        $session = $request->getSession();
        $em = $this->getDoctrine()->getManager();
        $currency = $em
            ->getRepository('mycpBundle:currency')
            ->findOneBy(array('curr_code' => $currencyCode));

        if(!empty($currency)) {
            $session->set("curr_rate", $currency->getCurrCucChange());
            $session->set("curr_symbol",  $currency->getCurrSymbol());
            $session->set("curr_acronym", $currency->getCurrCode());

            $user = $this->getUser();

            if(!empty($user) && $user != 'anon.') {
                $user->setUserCurrency($currency);
                $em->persist($user);
                $em->flush();
            }
        }
        return $this->redirect($this->setCurrency($currencyCode));
    }
}
