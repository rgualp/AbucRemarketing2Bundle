<?php

namespace MyCp\FrontendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class informationController extends Controller {
    
     public function listAction($information_type) {
        $em = $this->getDoctrine()->getEntityManager();
        $locale = $this->get('translator')->getLocale();
        $information_list = $em->getRepository('mycpBundle:information')->list_information($information_type,$locale);

        return $this->render('frontEndBundle:information:listInformation.html.twig', array(
            'information_type' => $information_type,
            'information_list' =>$information_list
        ));
    }
}
