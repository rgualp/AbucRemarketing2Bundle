<?php

namespace MyCp\mycpBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use MyCp\mycpBundle\Entity\season;
use Symfony\Component\Validator\Constraints\NotBlank;
use MyCp\mycpBundle\Helpers\BackendModuleName;

class BackendSeasonController extends Controller {

   public function listAction($items_per_page) {
        /* $service_security= $this->get('Secure');
          $service_security->verifyAccess(); */
        $page = 1;
        if (isset($_GET['page']))
            $page = $_GET['page'];
        $em = $this->getDoctrine()->getEntityManager();
        $paginator = $this->get('ideup.simple_paginator');
        $paginator->setItemsPerPage($items_per_page);
        $seasons = $paginator->paginate($em->getRepository('mycpBundle:season')->getAll())->getResult();

        $service_log = $this->get('log');
        $service_log->saveLog('Visit', BackendModuleName::MODULE_SEASON);

        return $this->render('mycpBundle:season:list.html.twig', array(
                    'seasons' => $seasons,
                    'items_per_page' => $items_per_page,
                    'current_page' => $page,
                    'total_items' => $paginator->getTotalItems()
        ));
    }
}
