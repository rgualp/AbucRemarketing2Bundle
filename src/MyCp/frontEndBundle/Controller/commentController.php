<?php

namespace MyCp\FrontendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;


class commentController extends Controller {


    public function insertAction($ownid) {

        $request = $this->getRequest();
        $session = $request->getSession();
        
        if($session->get('comments_cant') == null)
            $session->set('comments_cant', 1);
        else $session->set('comments_cant', 2);
        
        $em = $this->getDoctrine()->getEntityManager();
        $user = $this->get('security.context')->getToken()->getUser();
        $data = array();
        $data['com_ownership_id'] = $ownid;
        $data['com_rating'] = $request->request->get('com_rating');
        $data['com_comments'] = $request->request->get('com_comments');
        
        if($session->get('comments_cant') == 1)
            $em->getRepository('mycpBundle:comment')->insert_comment($data, $user);

        if($session->get('comments_cant') == 2)
            $session->remove('comments_cant');
        
         $paginator = $this->get('ideup.simple_paginator');
         $items_per_page = 5;
         $paginator->setItemsPerPage($items_per_page);
         $comments = $paginator->paginate($em->getRepository('mycpBundle:comment')->get_comments($ownid))->getResult();
         $page = 1;
         if (isset($_GET['page']))
             $page = $_GET['page'];

        $friends = array();
        $own_obj = $em->getRepository('mycpBundle:ownership')->find($ownid);
        $ownership = array('ownname'=> $own_obj->getOwnName(),
                            'rating' => $own_obj->getOwnRating(),
                            'comments_total' => $own_obj->getOwnCommentsTotal());

        $response = $this->renderView('frontEndBundle:comment:listComments.html.twig', array(
            'comments' => $comments,
            'friends' => $friends,
            'show_comments_and_friends' => count($paginator->getTotalItems()) + count($friends),
            'comments_items_per_page' => $items_per_page,
            'comments_total_items' => $paginator->getTotalItems(),
            'comments_current_page' => $page,
            'ownership' => $ownership
                ));

        return new Response($response, 200);
    }

    

}
