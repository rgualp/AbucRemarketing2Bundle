<?php

namespace MyCp\PartnerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
/**
 * @Route("/partner")
 *
 */
class BackendController extends Controller
{
    /**
     * @Route("/backend", name="partner_backend")
     */
    public function indexAction()
    {
        return $this->render('PartnerBundle:Backend:index.html.twig');
    }
}
