<?php

namespace MyCp\frontEndBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class currencyController extends Controller {

    public function get_currenciesAction($params) {
        $em = $this->getDoctrine()->getEntityManager();
        $currencies = $em->getRepository('mycpBundle:currency')->findAll();
        $request = $this->getRequest();
        $session = $request->getSession();
        $session->set("params_change_curr",$params);
        return $this->render('frontEndBundle:currency:currencies.html.twig', array('currencies' => $currencies));
    }

    public function changeAction() {
        $request = $this->getRequest();
        $session = $request->getSession();
        $em = $this->getDoctrine()->getEntityManager();
        if ($request->getMethod() == 'POST') {
            $curr_id = $request->request->get('curr_id');
            $refresh_url = $request->request->get('refresh_url');

            //echo $refresh_url;

            $currency = $em->getRepository('mycpBundle:currency')->find($curr_id);

            $session->set("curr_rate", ($curr_id == 0) ? 1 : $currency->getCurrCucChange());
            $session->set("curr_symbol", ($curr_id == 0) ? "$" : $currency->getCurrSymbol());
            $session->set("curr_acronym", ($curr_id == 0) ? "CUC" : $currency->getCurrCode());

            //Guardar en userTourist la moneda q cambio
            $user = $this->get('security.context')->getToken()->getUser();

            if ($user != null) {
                $userTourist = $em->getRepository('mycpBundle:userTourist')->findOneBy(array('user_tourist_user' => $user->getUserId()));
                $userTourist->setUserTouristCurrency($currency);
                $em->persist($userTourist);
                $em->flush();
            }

            //$this->redirect($refresh_url);
        }

        return new Response(null, 200);
        ;
    }

    public function change_currencyAction($curr_code)
    {
        $request = $this->getRequest();
        $session = $request->getSession();
        $em = $this->getDoctrine()->getEntityManager();
        $currency = $em->getRepository('mycpBundle:currency')->findOneBy(array('curr_code'=>$curr_code));
        if($currency)
        {
            $session->set("curr_rate", $currency->getCurrCucChange());
            $session->set("curr_symbol",  $currency->getCurrSymbol());
            $session->set("curr_acronym", $currency->getCurrCode());

            $user = $this->get('security.context')->getToken()->getUser();
            $userTourist = $em->getRepository('mycpBundle:userTourist')->findOneBy(array('user_tourist_user' => $user->getUserId()));
            if($userTourist)
            {
                $userTourist->setUserTouristCurrency($currency);
                $em->persist($userTourist);
                $em->flush();
            }
        }
        $route_params=$session->get('params_change_curr');
        return $this->redirect($this->generateUrl($route_params['_route'],$route_params['_route_params']));
    }

}
