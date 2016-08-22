<?php

namespace MyCp\PartnerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * @Route("/{_locale}/partner")
 */
class FrontendController extends Controller
{
    /**
     * @Route("/", name="partner_home")
     */
    public function indexAction()
    {
        return $this->render('PartnerBundle:Frontend:index.html.twig');
    }
}
