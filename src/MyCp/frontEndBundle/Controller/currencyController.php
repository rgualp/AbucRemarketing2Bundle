<?php

namespace MyCp\frontEndBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class currencyController extends Controller {

    public function get_currenciesAction() {
        $em = $this->getDoctrine()->getEntityManager();
        $currencies = $em->getRepository('mycpBundle:currency')->findAll();
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
            
            //$this->redirect($refresh_url);
        }
        
        return new Response(null, 200);;
    }

}
