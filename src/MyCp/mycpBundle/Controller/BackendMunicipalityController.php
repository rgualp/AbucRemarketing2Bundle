<?php

namespace MyCp\mycpBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

use MyCp\mycpBundle\Entity\lang;
use MyCp\mycpBundle\Entity\photo;
use MyCp\mycpBundle\Entity\log;
use MyCp\mycpBundle\Entity\langFlag;
use MyCp\mycpBundle\Form\langType;
use MyCp\mycpBundle\Helpers\BackendModuleName;



class BackendMunicipalityController extends Controller
{
    public function listAction($items_per_page)
    {
        /*$service_security= $this->get('Secure');
        $service_security->verifyAccess();*/
        $page=1;
        if(isset($_GET['page']))$page=$_GET['page'];
        $em = $this->getDoctrine()->getEntityManager();
        $paginator = $this->get('ideup.simple_paginator');
        $paginator->setItemsPerPage($items_per_page);
        $municipalities=$paginator->paginate($em->getRepository('mycpBundle:municipality')->getAll())->getResult();

        $service_log= $this->get('log');
        $service_log->saveLog('Visit',BackendModuleName::MODULE_MUNICIPALITY);

        return $this->render('mycpBundle:municipality:list.html.twig',array(
            'municipalities'=>$municipalities,
            'items_per_page'=>$items_per_page,
            'current_page'=>$page,
            'total_items'=>$paginator->getTotalItems()
        ));
    }
}
