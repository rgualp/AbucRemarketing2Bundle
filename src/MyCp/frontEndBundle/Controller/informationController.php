<?php

namespace MyCp\FrontendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class informationController extends Controller {
    
     public function listAction($information_type) {
        $em = $this->getDoctrine()->getEntityManager();
        $locale = $this->get('translator')->getLocale();
        
        $paginator = $this->get('ideup.simple_paginator');
        $items_per_page = 15;
        $paginator->setItemsPerPage($items_per_page);
        $information_list=$paginator->paginate($em->getRepository('mycpBundle:information')->list_information($information_type,$locale))->getResult();
        $page=1;
        if(isset($_GET['page']))$page=$_GET['page'];

        return $this->render('frontEndBundle:information:listInformation.html.twig', array(
            'information_type' => $information_type,
            'information_list' =>$information_list,
            'items_per_page'=>$items_per_page,
            'total_items'=>$paginator->getTotalItems(),
            'current_page'=>$page
            
        ));
    }
    
    public function sitemapAction()
    {
        return $this->render('frontEndBundle:information:sitemap.html.twig');
    }
    
    public function how_it_worksAction()
    {
       return $this->render('frontEndBundle:information:howItWorks.html.twig'); 
    }
}
