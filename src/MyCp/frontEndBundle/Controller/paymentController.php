<?php

namespace MyCp\FrontendBundle\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class paymentController extends Controller {

    public function skrillAction()
    {
        return $this->render(
            'frontEndBundle:Payment:skrill.html.twig',
            array('name' => 'Jakob'));
    }
}