<?php

namespace MyCp\FrontendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class informationController extends Controller {
    
     public function about_usAction(Request $request) {
        $em = $this->getDoctrine()->getEntityManager();
        $numbers=$em->getRepository('mycpBundle:information')->get_numbers();
        $numbers=$numbers[0];
        $lang=$request->getLocale();
        $information_about_us=$em->getRepository('mycpBundle:information')->get_information_about_us($lang);
        //var_dump($information_about_us);
        return $this->render('frontEndBundle:information:aboutUs.html.twig', array(
            'numbers'=>$numbers,
            'information'=>$information_about_us
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
