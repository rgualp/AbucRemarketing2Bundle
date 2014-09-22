<?php

namespace MyCp\mycpBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use MyCp\mycpBundle\Helpers\BackendModuleName;


class LodgingCommentController extends Controller
{
    public function list_commentAction($items_per_page, Request $request)
    {
        $service_security= $this->get('Secure');
        //$service_security->verifyAccess();

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
            return $this->redirect($this->generateUrl('mycp_list_readonly_comments'));
        }
        if($filter_ownership=='null') $filter_ownership='';
        if($filter_keyword=='null') $filter_keyword='';
        if(isset($_GET['page']))$page=$_GET['page'];
        $paginator = $this->get('ideup.simple_paginator');
        $paginator->setItemsPerPage($items_per_page);
        $em = $this->getDoctrine()->getEntityManager();

        $user = $this->get('security.context')->getToken()->getUser();

        if($user->getUserRole()!='ROLE_CLIENT_CASA')
            $comments_list = $em->getRepository('mycpBundle:comment')->get_all_comment($filter_ownership,$filter_user,$filter_keyword, $filter_rate,$sort_by);
        else
        {
            $user_casa = $em->getRepository('mycpBundle:userCasa')->get_user_casa_by_user_id($user->getUserId());
            $comments_list = $em->getRepository('mycpBundle:comment')->get_comment_by_user_casa($filter_ownership,$filter_user,$filter_keyword, $filter_rate,$sort_by, $user_casa->getUserCasaId());
        }

        $comments= $paginator->paginate($comments_list)->getResult();
        //var_dump($destinations[0]->getDesLocMunicipality()->getMunName()); exit();

        $service_log= $this->get('log');
        $service_log->saveLog('Visit module',  BackendModuleName::MODULE_LODGING_COMMENT);
        return $this->render('mycpBundle:comment:list_readonly.html.twig', array(
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

}
