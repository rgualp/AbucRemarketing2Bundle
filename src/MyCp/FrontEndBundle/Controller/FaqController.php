<?php

namespace MyCp\FrontEndBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class FaqController extends Controller {

     public function listAction() {
        $em = $this->getDoctrine()->getManager();
        $locale = $this->get('translator')->getLocale();
        $paginator = $this->get('ideup.simple_paginator');
        $items_per_page = 15;
        $paginator->setItemsPerPage($items_per_page);
        $faq_list=$paginator->paginate($em->getRepository('mycpBundle:faq')->get_faq_list_by_category($locale))->getResult();
        $page=1;
        if(isset($_GET['page']))$page=$_GET['page'];
        $category_list = $em->getRepository('mycpBundle:faq')->get_faq_category_list($locale);

        return $this->render('FrontEndBundle:faq:listFaq.html.twig', array(
            'categories' => $category_list,
            'faq_list' => $faq_list,
            'items_per_page'=>$items_per_page,
            'total_items'=>$paginator->getTotalItems(),
            'current_page'=>$page
        ));
    }

    public function filterAction() {
        $em = $this->getDoctrine()->getManager();
        $request = $this->getRequest();
        $locale = $this->get('translator')->getLocale();
        $category_id = $request->request->get('category_id');
        $paginator = $this->get('ideup.simple_paginator');
        $items_per_page = 15;
        $paginator->setItemsPerPage($items_per_page);
        $faq_list=$paginator->paginate($em->getRepository('mycpBundle:faq')->get_faq_list_by_category($locale, $category_id))->getResult();
        $page=1;
        if(isset($_GET['page']))$page=$_GET['page'];



        $response = $this->renderView('FrontEndBundle:faq:itemListFaq.html.twig', array(
            'faq_list' => $faq_list,
            'items_per_page'=>$items_per_page,
            'total_items'=>$paginator->getTotalItems(),
            'current_page'=>$page
                ));

        return new Response($response, 200);
    }
}
