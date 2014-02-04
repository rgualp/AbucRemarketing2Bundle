<?php

namespace MyCp\FrontendBundle\Controller;

use Doctrine\Common\Cache\ArrayCache;
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
        $locale=$this->get('translator')->getLocale();
        $em = $this->getDoctrine()->getEntityManager();
        $nomenclator=$em->getRepository('mycpBundle:nomenclator')->findBy(array('nom_name'=>'how_it_works'));
        $information=$em->getRepository('mycpBundle:information')->findBy(array('info_id_nom'=>$nomenclator[0]->getNomId()));
        $contents=$em->getRepository('mycpBundle:informationLang')->findBy(array('info_lang_info'=>$information[0]->getInfoId()));
        $content=array();
        foreach($contents as $cont)
        {
            if(strtolower($cont->getInfoLangLang()->getLangCode())== strtolower($locale))
                array_push($content, array('title'=>$cont->getInfoLangName(),'content'=>$cont->getInfoLangContent()));
        }
        return $this->render('frontEndBundle:information:howItWorks.html.twig' , array('contents'=>$content));
    }
    
    public function legal_termsAction()
    {
        $locale=$this->get('translator')->getLocale();
        $em = $this->getDoctrine()->getEntityManager();
                
        $list=$em->getRepository('mycpBundle:information')->list_information('legal_terms', $locale);
        
        
        return $this->render('frontEndBundle:information:legal_terms.html.twig' , array(
            'list'=>$list
                ));
        
    }
    
    public function security_privacityAction()
    {
        $locale=$this->get('translator')->getLocale();
        $em = $this->getDoctrine()->getEntityManager();
                
        $list=$em->getRepository('mycpBundle:information')->list_information('security_privacity', $locale);
                
        return $this->render('frontEndBundle:information:security_privacity.html.twig' , array(
            'list'=>$list
                ));
        
    }
}
