<?php

namespace MyCp\frontEndBundle\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class languageController extends Controller
{
    public function get_languagesAction()
    {
        $em=$this->getDoctrine()->getEntityManager();
        $languages=$em->getRepository('mycpBundle:lang')->findBy(array('lang_active' => 1),array('lang_name' => 'ASC'));
        return $this->render('frontEndBundle:language:languages.html.twig',array('languages'=>$languages));
    }
    
     public function changeAction() {
        $request = $this->getRequest();
        $session = $request->getSession();
        
            $lang_code = $request->request->get('lang_code');
            $lang_name = $request->request->get('lang_name');
            $this->get('translator')->setLocale($lang_code);
            $session->set('app_lang_name',$lang_name);
            //echo $lang_name;
            
            //$this->redirect($refresh_url);
        
        return new Response(null, 200);
    }
}
