<?php

namespace MyCp\FrontEndBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


class CommentController extends Controller {


    public function insertAction($ownid, Request $request) {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $data = array();
        $data['com_ownership_id'] = $ownid;
        $data['com_rating'] = $request->request->get('com_rating');
        $data['com_comments'] = $request->request->get('com_comments');

        $own_obj = $em->getRepository('mycpBundle:ownership')->find($ownid);

        $existComment = $em->getRepository('mycpBundle:comment')->findOneBy(array("com_ownership" => $ownid,
            "com_user" => $user->getUserId(),
            "com_rate" => $data['com_rating'],
            "com_comments" => $data['com_comments']));

        if($existComment == null) {
            $em->getRepository('mycpBundle:comment')->insert($data, $user);

            if($own_obj->getOwnEmail1()!=null) {
                $body = $this->render('FrontEndBundle:mails:commentNotification.html.twig', array(
                    'host_user_name' => $own_obj->getOwnHomeowner1(),
                    'user_name' => $user->getUserName() . ' ' . $user->getUserLastName(),
                    'comment' => $request->request->get('com_comments')
                ));

                $service_email = $this->get('mycp.service.email_manager');
                $service_email->sendEmail($own_obj->getOwnEmail1(),'Nuevos comentarios recibidos', $body->getContent());
            }
        }

         $paginator = $this->get('ideup.simple_paginator');
         $items_per_page = 5;
         $paginator->setItemsPerPage($items_per_page);
         $comments = $paginator->paginate($em->getRepository('mycpBundle:comment')->getByOwnership($ownid))->getResult();
         $page = 1;
         if (isset($_GET['page']))
             $page = $_GET['page'];

        $friends = array();

        $ownership = array('ownname'=> $own_obj->getOwnName(),
                            'rating' => $own_obj->getOwnRating(),
                            'comments_total' => $own_obj->getOwnCommentsTotal());

        $response = $this->renderView('FrontEndBundle:comment:listComments.html.twig', array(
            'comments' => $comments,
            'friends' => $friends,
            'show_comments_and_friends' => count($paginator->getTotalItems()) + count($friends),
            'comments_items_per_page' => $items_per_page,
            'comments_total_items' => $paginator->getTotalItems(),
            'comments_current_page' => $page,
            'can_public_comment' => $em->getRepository("mycpBundle:comment")->canPublicComment($user->getUserId(), $ownid),
            'ownership' => $ownership
                ));

        return new Response($response, 200);
    }



}
