<?php

namespace MyCp\mycpBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class BackendController extends Controller {

    public function backend_frontAction() {
        if ($this->get('security.context')->isGranted('ROLE_CLIENT_TOURIST')) {
            $this->get('security.context')->setToken(null);
            //$this->get('request')->getSession()->invalidate();
            return $this->redirect($this->generateUrl('frontend_welcome'));
        } else if ($this->get('security.context')->isGranted('ROLE_CLIENT_CASA')) {
            return $this->redirect($this->generateUrl('mycp_lodging_front'));
        } else {
            $user = $this->get('security.context')->getToken()->getUser();
            $em = $this->getDoctrine()->getManager();
            if ($user->getUserPhoto())
                $photo = $em->getRepository('mycpBundle:photo')->find($user->getUserPhoto()->getPhoId());
            else
                $photo = null;
            return $this->render('mycpBundle:backend:welcome.html.twig', array('photo' => $photo));
        }
    }

}
