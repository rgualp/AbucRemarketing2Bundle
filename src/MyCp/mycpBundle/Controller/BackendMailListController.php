<?php

namespace MyCp\mycpBundle\Controller;

use MyCp\mycpBundle\Entity\mailList;
use MyCp\mycpBundle\Form\mailListType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use MyCp\mycpBundle\Helpers\BackendModuleName;

class BackendMailListController extends Controller {

    function listAction($items_per_page = 20) {
        $service_security = $this->get('Secure');
        $service_security->verifyAccess();
        $em = $this->getDoctrine()->getManager();
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

    function addAction(Request $request) {
        $service_security = $this->get('Secure');
        $service_security->verifyAccess();
        $em = $this->getDoctrine()->getManager();

        $mailList = new mailList();
        $form = $this->createForm(new mailListType(), $mailList);

        if ($request->getMethod() == 'POST') {
            $post_form = $request->get('mycp_mycpbundle_maillisttype');
            $form->handleRequest($request);
            if ($form->isValid()) {
                $mailListWithFunction = $em->getRepository("mycpBundle:mailList")->findBy(array("mail_list_function" => $post_form['mail_list_function']));
                if (count($mailListWithFunction) == 0 && $post_form['mail_list_function'] != 0) {
                    $em->persist($mailList);
                    $em->flush();
                    $message = 'Lista de correo añadida satisfactoriamente.';
                    $this->get('session')->getFlashBag()->add('message_ok', $message);

                    $service_log = $this->get('log');
                    $service_log->saveLog('Create entity ' . $post_form['mail_list_name'], BackendModuleName::MODULE_MAIL_LIST);

                    return $this->redirect($this->generateUrl('mycp_list_mail_list'));
                } else {
                    if (count($mailListWithFunction) != 0) {
                        $message = 'Ya existe una lista asociada a esta función o comando.';
                        $this->get('session')->getFlashBag()->add('message_error_main', $message);
                    }
                    if ($post_form['mail_list_function'] == 0) {
                        $message = 'Debe seleccionar una función o comando de la lista desplegable.';
                        $this->get('session')->getFlashBag()->add('message_error_main', $message);
                    }
                }
            }
        }
        return $this->render('mycpBundle:mailList:new.html.twig', array('form' => $form->createView()));
    }

    function editAction($listId, Request $request) {
        $service_security = $this->get('Secure');
        $service_security->verifyAccess();
        $em = $this->getDoctrine()->getManager();
        $mailList = $em->getRepository('mycpBundle:mailList')->find($listId);
        $mailFunction = $mailList->getMailListFunction();

        $form = $this->createForm(new mailListType(), $mailList);
        if ($request->getMethod() == 'POST') {
            $post_form = $request->get('mycp_mycpbundle_maillisttype');
            $form->handleRequest($request);
            if ($form->isValid()) {
                $mailList->setMailListFunction($mailFunction);
                $em->persist($mailList);
                $em->flush();
                $message = 'Lista de correo modificada satisfactoriamente.';
                $this->get('session')->getFlashBag()->add('message_ok', $message);

                $service_log = $this->get('log');
                $service_log->saveLog('Update mailList, ' . $post_form['mail_list_name'], BackendModuleName::MODULE_MAIL_LIST);

                return $this->redirect($this->generateUrl('mycp_list_mail_list'));
            }
        }

        return $this->render('mycpBundle:mailList:new.html.twig', array('form' => $form->createView(), 'listId' => $listId, 'edit' => true));
    }

    function deleteAction($listId) {
        $service_security = $this->get('Secure');
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

        $service_log = $this->get('log');
        $service_log->saveLog('Remove mail list ' . $listId, BackendModuleName::MODULE_MAIL_LIST);

        $message = 'Lista de correo eliminada satisfactoriamente.';
        $this->get('session')->getFlashBag()->add('message_ok', $message);
        return $this->redirect($this->generateUrl('mycp_list_mail_list'));
    }

    function usersAction($mailList, $items_per_page = 20) {
        $service_security = $this->get('Secure');
        $service_security->verifyAccess();
        $em = $this->getDoctrine()->getManager();
        $mList = $em->getRepository("mycpBundle:mailList")->find($mailList);
        $page = 1;
        if (isset($_GET['page']))
            $page = $_GET['page'];
        $paginator = $this->get('ideup.simple_paginator');
        $paginator->setItemsPerPage($items_per_page);
        $list = $paginator->paginate($em->getRepository('mycpBundle:mailListUser')->findBy(array('mail_list' => $mailList)))->getResult();

        $service_log = $this->get('log');
        $service_log->saveLog('Visit users for a mail list', BackendModuleName::MODULE_MAIL_LIST);

        return $this->render('mycpBundle:mailList:users.html.twig', array(
                    'list' => $list,
                    'mailList' => $mList,
                    'items_per_page' => $items_per_page,
                    'current_page' => $page,
                    'total_items' => $paginator->getTotalItems()
        ));
    }

    function addUserAction($mailList) {
        $service_security = $this->get('Secure');
        $service_security->verifyAccess();
        $em = $this->getDoctrine()->getManager();

        //TODO

        $service_log = $this->get('log');
        $service_log->saveLog('Insert new mail list user', BackendModuleName::MODULE_MAIL_LIST);

        //TODO
    }

    function deleteUserAction($mailListUserId) {
        $service_security = $this->get('Secure');
        $service_security->verifyAccess();

        $em = $this->getDoctrine()->getEntityManager();
        $listUser = $em->getRepository('mycpBundle:mailListUser')->find($mailListUserId);
        $mailListId = $listUser->getMailList()->getMailListId();

        $em->remove($listUser);
        $em->flush();

        $service_log = $this->get('log');
        $service_log->saveLog('Remove mail list user ' . $mailListUserId, BackendModuleName::MODULE_MAIL_LIST);

        $message = 'El usuario ha sido eliminado de la lista de correo satisfactoriamente.';
        $this->get('session')->getFlashBag()->add('message_ok', $message);
        return $this->redirect($this->generateUrl('mycp_list_mail_list_user', array('mailList' => $mailListId)));
    }

}
