<?php

namespace MyCp\PartnerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class BackendController extends Controller
{
    public function indexAction()
    {
        return $this->render('PartnerBundle:Backend:index.html.twig');
    }
}
