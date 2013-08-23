<?php

namespace MyCp\frontEndBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class languageController extends Controller
{
    public function get_languagesAction()
    {
        $em=$this->getDoctrine()->getEntityManager();
        $languages=$em->getRepository('mycpBundle:lang')->findBy(array('lang_active' => 1),array('lang_name' => 'ASC'));
        return $this->render('frontEndBundle:language:languages.html.twig',array('languages'=>$languages));
    }
}
