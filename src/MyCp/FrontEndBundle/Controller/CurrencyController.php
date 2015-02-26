<?php

namespace MyCp\FrontEndBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class CurrencyController extends Controller
{

    public function getCurrenciesAction(Request $request, $params, $route, $routeParams = null)
    {
        $em = $this->getDoctrine()->getManager();
        $currencies = $em->getRepository('mycpBundle:currency')->findAll();
        $session = $request->getSession();
        $session->set("params_change_curr",$params);
        $routeParams = empty($routeParams) ? array() : $routeParams;
        $response = $this->render('FrontEndBundle:currency:currencies.html.twig', array(
                'currencies' => $currencies,
                'route' => $route,
                'routeParams' => $routeParams
            ));

        // cache control -> currencies rarely change
        $response->setSharedMaxAge(3600);

        return $response;
    }

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
                $userTourist = $em
                    ->getRepository('mycpBundle:userTourist')
                    ->findOneBy(array('user_tourist_user' => $user->getUserId()));

                if(!empty($userTourist)) {
                    $userTourist->setUserTouristCurrency($currency);
                    $em->persist($userTourist);
                    $em->flush();
                }
            }
        }

        return $this->redirect($this->generateUrl($route, $routeParams));
    }
}
