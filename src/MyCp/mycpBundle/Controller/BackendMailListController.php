<?php

namespace MyCp\mycpBundle\Controller;

use MyCp\mycpBundle\Entity\metaLang;
use MyCp\mycpBundle\Form\metaType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use MyCp\mycpBundle\Helpers\BackendModuleName;

class BackendMailListController extends Controller {

    function listAction($items_per_page = 20){
        $service_security= $this->get('Secure');
        $service_security->verifyAccess();
        $em=$this->getDoctrine()->getManager();
        $page = 1;
        if (isset($_GET['page']))
            $page = $_GET['page'];
        $paginator = $this->get('ideup.simple_paginator');
        $paginator->setItemsPerPage($items_per_page);
        $list = $paginator->paginate($em->getRepository('mycpBundle:mailList')->findAll())->getResult();

        $service_log = $this->get('log');
        $service_log->saveLog('Visit', BackendModuleName::MODULE_MAIL_LIST);

        return $this->render('mycpBundle:mailList:list.html.twig', array(
                    'list' => $list,
                    'items_per_page' => $items_per_page,
                    'current_page' => $page,
                    'total_items' => $paginator->getTotalItems()
        ));
    }

    function addAction(){
        $service_security= $this->get('Secure');
        $service_security->verifyAccess();
        $em=$this->getDoctrine()->getManager();

        //TODO

        $service_log = $this->get('log');
        $service_log->saveLog('Insert new mail list', BackendModuleName::MODULE_MAIL_LIST);

        //TODO
    }

    function editAction($listId,Request $request) {
        $service_security= $this->get('Secure');
        $service_security->verifyAccess();
        $em=$this->getDoctrine()->getManager();

        //TODO

        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);
            if ($form->isValid()) {
                //TODO

                $message = 'Lista de correo modificada satisfactoriamente.';
                $this->get('session')->getFlashBag()->add('message_ok', $message);

                $service_log = $this->get('log');
                $service_log->saveLog('Update mailList, ' . $listId, BackendModuleName::MODULE_MAIL_LIST);

                return $this->redirect($this->generateUrl('mycp_list_mail_list'));
            }
        }

        //TODO
    }

    function deleteAction($listId) {
        $service_security= $this->get('Secure');
        $service_security->verifyAccess();

        $em = $this->getDoctrine()->getEntityManager();
        $list = $em->getRepository('mycpBundle:mailList')->find($listId);

        $users = $em->getRepository('mycpBundle:mailListUser')->findBy(array('mail_list' => $listId));
        if ($users) {
            foreach ($users as $user)
                $em->remove($user);
        }
        $em->remove($list);
        $em->flush();

         $service_log= $this->get('log');
          $service_log->saveLog('Remove mail list '.$listId, BackendModuleName::MODULE_MAIL_LIST);

        $message = 'Lista de correo eliminada satisfactoriamente.';
        $this->get('session')->getFlashBag()->add('message_ok', $message);
        return $this->redirect($this->generateUrl('mycp_list_mail_list'));
    }

    function usersAction($mailList,$items_per_page = 20){
        $service_security= $this->get('Secure');
        $service_security->verifyAccess();
        $em=$this->getDoctrine()->getManager();
        $users = $em->getRepository('mycpBundle:mailListUser')->findBy(array('mail_list' => $mailList));
        //TODO

        $service_log = $this->get('log');
        $service_log->saveLog('Visit users for a mail list', BackendModuleName::MODULE_MAIL_LIST);

        //TODO
    }

    function addUserAction($mailList){
        $service_security= $this->get('Secure');
        $service_security->verifyAccess();
        $em=$this->getDoctrine()->getManager();

        //TODO

        $service_log = $this->get('log');
        $service_log->saveLog('Insert new mail list user', BackendModuleName::MODULE_MAIL_LIST);

        //TODO
    }

    function deleteUserAction($mailListUserId) {
        $service_security= $this->get('Secure');
        $service_security->verifyAccess();

        $em = $this->getDoctrine()->getEntityManager();
        $listUser = $em->getRepository('mycpBundle:mailListUser')->find($mailListUserId);
        $mailListId = $listUser->getMailList()->getMailListId();

        $em->remove($listUser);
        $em->flush();

         $service_log= $this->get('log');
          $service_log->saveLog('Remove mail list user '.$mailListUserId, BackendModuleName::MODULE_MAIL_LIST);

        $message = 'El usuario ha sido eliminado de la lista de correo satisfactoriamente.';
        $this->get('session')->getFlashBag()->add('message_ok', $message);
        return $this->redirect($this->generateUrl('mycp_list_mail_list_user', array('mailList' => $mailListId)));
    }
}
