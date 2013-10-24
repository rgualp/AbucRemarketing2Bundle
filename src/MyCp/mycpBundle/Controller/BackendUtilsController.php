<?php

namespace MyCp\mycpBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;


class BackendUtilsController extends Controller
{

    public function set_img_orderAction($ids,Request $request)
    {
        $ids=str_replace(' ','',$ids);
        $ids_array= explode(",", $ids);
        $em = $this->getDoctrine()->getEntityManager();
        $order=1;
        foreach($ids_array as $id)
        {
            $photo=new \MyCp\mycpBundle\Entity\photo();
            $photo=$em->getRepository('mycpBundle:photo')->find($id);
            $photo->setPhoOrder($order);
            $em->persist($photo);
            $em->flush();
            $order++;
        }
        return new Response($ids);
    }

    public function get_active_listAction($selected)
    {
        return $this->render('mycpBundle:utils:active.html.twig',array('selected'=>$selected));
    }
    
    public function process_imagesAction()
    {
        $results_array = \MyCp\mycpBundle\Helpers\Images::process_images_with_directory_info($this->container);
        $session = $this->getRequest()->getSession();        
        $session->set('process_images_msg', $results_array['msg_text']);
        
        return $this->render('mycpBundle:utils:process_images.html.twig',array(
                             'message'=>$results_array['msg_text'],
                             'finished_process' => $results_array['finished']));
    }
    
    public function copy_imagesAction()
    {
        $session = $this->getRequest()->getSession(); 
        $results_array = \MyCp\mycpBundle\Helpers\Images::copy_from_processed_to_main_directory($this->container, $session->get('process_images_msg'));
        return $this->render('mycpBundle:utils:process_images.html.twig',array(
                             'message'=>$results_array['msg_text'],
                             'finished_copy' => $results_array['finished']));
    }
}
