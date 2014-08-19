<?php

namespace MyCp\mycpBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints\NotBlank;

use MyCp\mycpBundle\Entity\comment;
use MyCp\mycpBundle\Entity\log;
use MyCp\mycpBundle\Form\commentType;
use MyCp\mycpBundle\Helpers\BackendModuleName;

class BackendCommentController extends Controller
{

    public function list_commentAction($items_per_page, Request $request)
    {
        $service_security= $this->get('Secure');
        $service_security->verifyAccess();

        $page=1;
        $data='';
        $filter_ownership=$request->get('filter_ownership');
        $filter_user=$request->get('filter_user');
        $sort_by=$request->get('sort_by');
        $filter_keyword=$request->get('filter_keyword');
        $filter_rate=$request->get('filter_rate');
        if($request->getMethod()=='POST' && $filter_ownership=='null' && $filter_user=='null' && $filter_keyword=='null' && $filter_rate=='null')
        {
            $message='Debe llenar al menos un campo para filtrar.';
            $this->get('session')->getFlashBag()->add('message_error_local',$message);
            return $this->redirect($this->generateUrl('mycp_list_comments'));
        }
        if($filter_ownership=='null') $filter_ownership='';
        if($filter_keyword=='null') $filter_keyword='';
        if(isset($_GET['page']))$page=$_GET['page'];
        $paginator = $this->get('ideup.simple_paginator');
        $paginator->setItemsPerPage($items_per_page);
        $em = $this->getDoctrine()->getEntityManager();
        $comments= $paginator->paginate($em->getRepository('mycpBundle:comment')->get_all_comment($filter_ownership,$filter_user,$filter_keyword, $filter_rate,$sort_by))->getResult();
        //var_dump($destinations[0]->getDesLocMunicipality()->getMunName()); exit();

        $service_log= $this->get('log');
        $service_log->saveLog('Visit module',  BackendModuleName::MODULE_COMMENT);
        return $this->render('mycpBundle:comment:list.html.twig', array(
            'comments' => $comments,
            'items_per_page'=>$items_per_page,
            'current_page'=>$page,
            'total_items'=>$paginator->getTotalItems(),
            'filter_ownership'=>$filter_ownership,
            'filter_user'=>$filter_user,
            'filter_keyword'=>$filter_keyword,
            'filter_rate'=>$filter_rate,
            'sort_by'=>$sort_by
        ));
    }

    public function new_commentAction(Request $request)
    {
        $service_security= $this->get('Secure');
        $service_security->verifyAccess();
        $comment=new comment();
        $form=$this->createForm( new commentType(),$comment);
        if($request->getMethod()=='POST')
        {
            $post_form=$request->get('mycp_mycpbundle_currencytype');
            $form->handleRequest($request);
            if($form->isValid())
            {
                $em = $this->getDoctrine()->getEntityManager();
                $em->persist($comment);
                $em->flush();
                $message='Comentario añadido satisfactoriamente.';
                $this->get('session')->getFlashBag()->add('message_ok',$message);


                $comment_db=$em->getRepository('mycpBundle:comment')->find($comment->getComId());
                $comment_db->setComDate(new \DateTime(date('Y-m-d')));
                $em->persist($comment_db);
                $em->flush();

                $service_log= $this->get('log');
                $service_log->saveLog('Create entity ',BackendModuleName::MODULE_COMMENT);

                return $this->redirect($this->generateUrl('mycp_list_comments'));
            }

        }
        return $this->render('mycpBundle:comment:new.html.twig',array('form'=>$form->createView()));
    }

    public function edit_commentAction($id_comment,Request $request)
    {
        $service_security= $this->get('Secure');
        $service_security->verifyAccess();
        $message='';
        $em = $this->getDoctrine()->getEntityManager();
        $comment=$em->getRepository('mycpBundle:comment')->find($id_comment);
        $form=$this->createForm( new commentType,$comment);
        if($request->getMethod()=='POST')
        {
            $post_form=$request->get('mycp_mycpbundle_commenttype');
            $form->handleRequest($request);
            if($form->isValid())
            {
                $em->persist($comment);
                $em->flush();
                $message='Comentario actualizado satisfactoriamente.';
                $this->get('session')->getFlashBag()->add('message_ok',$message);

                $service_log= $this->get('log');
                $service_log->saveLog('Edit entity ',BackendModuleName::MODULE_COMMENT);

                return $this->redirect($this->generateUrl('mycp_list_comments'));
            }

        }

        return $this->render('mycpBundle:comment:new.html.twig',array('form'=>$form->createView(),'edit_comment'=>$comment->getComId()));

    }

    public function delete_commentAction($id_comment)
    {
        $service_security= $this->get('Secure');
        $service_security->verifyAccess();
        $em = $this->getDoctrine()->getEntityManager();
        $comment=$em->getRepository('mycpBundle:comment')->find($id_comment);

            $user_comment=$comment->getComUser()->getUserName();
            $own_comment=$comment->getComOwnership()->getOwnMcpCode();
            $em->remove($comment);
            $em->flush();
            $message='El comentario se ha eliminado satisfactoriamente.';
            $this->get('session')->getFlashBag()->add('message_ok',$message);

            $service_log= $this->get('log');
            $service_log->saveLog('Delete entity ',BackendModuleName::MODULE_COMMENT);

            return $this->redirect($this->generateUrl('mycp_list_comments'));
    }

    public function get_comments_by_ownershipAction($id_own)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $comments=$em->getRepository('mycpBundle:comment')->findBy(array('com_ownership'=>$id_own));
        return $this->render('mycpBundle:utils:comments.html.twig',array('comments'=>$comments));
    }

    public function get_all_rateAction($post)
    {
        $selected='';
        if(isset($post['selected']))
            $selected=$post['selected'];
        return $this->render('mycpBundle:utils:range_max_5_no_0.html.twig',array('selected'=>$selected));
    }

}
