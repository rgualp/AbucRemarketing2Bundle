<?php

namespace MyCp\PartnerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\JsonResponse;

class DashboardController extends Controller
{
    public function indexAction()
    {
        return new JsonResponse([
            'success' => true,
            'html' => $this->renderView('PartnerBundle:Dashboard:requests.html.twig', array('apis' => 'asdasd', 'form'=>'dsdsd')),
            'msg' => 'Vista del listado de apis']);
    }
}
