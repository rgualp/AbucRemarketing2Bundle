<?php

namespace MyCp\CasaModuleBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('MyCpCasaModuleBundle:Default:index.html.twig', array('name' => $name));
    }
}
