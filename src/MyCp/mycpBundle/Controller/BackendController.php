<?php

namespace MyCp\mycpBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;


class BackendController extends Controller
{
     public function backend_frontAction()
     {
         $user = $this->get('security.context')->getToken()->getUser();
         $user_id=$user->getUserId();
         $em=$this->getDoctrine()->getManager();
         if($user->getUserPhoto())
            $photo=$em->getRepository('mycpBundle:photo')->find($user->getUserPhoto()->getPhoId());
         else
             $photo=null;
         return $this->render('mycpBundle:backend:welcome.html.twig',array('photo'=>$photo));
     }
}
