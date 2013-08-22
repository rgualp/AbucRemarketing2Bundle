<?php

namespace MyCp\frontEndBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class currencyController extends Controller
{
    public function get_currenciesAction()
    {
        $em=$this->getDoctrine()->getEntityManager();
        $currencies=$em->getRepository('mycpBundle:currency')->findAll();
        return $this->render('frontEndBundle:currency:currencies.html.twig',array('currencies'=>$currencies));
    }
}
