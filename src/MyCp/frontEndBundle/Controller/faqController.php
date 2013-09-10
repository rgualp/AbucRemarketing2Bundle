<?php

namespace MyCp\FrontendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class faqController extends Controller {
    
     public function listAction() {
        $em = $this->getDoctrine()->getEntityManager();
        $locale = $this->get('translator')->getLocale();
        $category_list = $em->getRepository('mycpBundle:faq')->get_faq_category_list($locale);        
        $faq_list = $em->getRepository('mycpBundle:faq')->get_faq_list_by_category($locale);

        return $this->render('frontEndBundle:faq:listFaq.html.twig', array(
            'categories' => $category_list,
            'faq_list' => $faq_list
        ));
    }
    
    public function filterAction() {
        $em = $this->getDoctrine()->getEntityManager();
        $request = $this->getRequest();
        $locale = $this->get('translator')->getLocale();
        $category_id = $request->request->get('category_id');
        $faq_list = $em->getRepository('mycpBundle:faq')->get_faq_list_by_category($locale, $category_id);

        $response = $this->renderView('frontEndBundle:faq:itemListFaq.html.twig', array(
            'faq_list' => $faq_list
                ));
        
        return new Response($response, 200);
    }
}
