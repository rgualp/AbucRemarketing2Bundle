<?php

namespace MyCp\FrontEndBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class UserCasaController extends Controller {

    public function homeAction() {

        return $this->render('FrontEndBundle:userCasa:home.html.twig');
    }

    public function activateAccountAction($secret = "0")
    {
        return $this->render('FrontEndBundle:userCasa:activateAccount.html.twig');
    }
}
