<?php

namespace MyCp\mycpBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;


class BackendController extends Controller
{
     public function backend_frontAction()
     {
         return $this->render('mycpBundle:backend:welcome.html.twig');
     }
}
