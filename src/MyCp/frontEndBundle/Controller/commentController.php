<?php

namespace MyCp\FrontendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class commentController extends Controller {
    
     public function insertAction($ownid) {
        $request = $this->getRequest();
        $em = $this->getDoctrine()->getEntityManager();

        $data = array();
        $data['com_ownership_id'] = $ownid;
        $data['com_rating'] = $request->request->get('com_rating');
        $data['com_user_name'] = $request->request->get('com_user_name');
        $data['com_comments'] = $request->request->get('com_comments');
        $data['com_email'] = $request->request->get('com_email');

        $em->getRepository('mycpBundle:comment')->insert_comment($data);
        $list = $em->getRepository('mycpBundle:comment')->get_comments($ownid);
        $friends = array();

        $response = $this->renderView('frontEndBundle:comment:listComments.html.twig', array(
            'comments' => $list,
            'friends' => $friends,
            'show_comments_and_friends' => count($list) + count($friends)
                ));

        return new Response($response, 200);
    }

    

}
