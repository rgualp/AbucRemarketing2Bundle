<?php

namespace MyCp\mycpBundle\Controller;

use MyCp\mycpBundle\Entity\metaLang;
use MyCp\mycpBundle\Form\metaType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use MyCp\mycpBundle\Helpers\BackendModuleName;

class BackendMailListController extends Controller {

    function listAction(){
        $service_security= $this->get('Secure');
        $service_security->verifyAccess();
        $em=$this->getDoctrine()->getManager();

        //TODO

        $service_log = $this->get('log');
        $service_log->saveLog('Visit', BackendModuleName::MODULE_METATAGS);

        //TODO
    }

    function addAction(){
        $service_security= $this->get('Secure');
        $service_security->verifyAccess();
        $em=$this->getDoctrine()->getManager();

        //TODO

        $service_log = $this->get('log');
        $service_log->saveLog('Insert new mail list', BackendModuleName::MODULE_METATAGS);

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
                $service_log->saveLog('Update mailList, ' . $listId, BackendModuleName::MODULE_METATAGS);

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
          $service_log->saveLog('Remove mail list '.$listId, BackendModuleName::MODULE_USER);

        $message = 'Lista de correo eliminada satisfactoriamente.';
        $this->get('session')->getFlashBag()->add('message_ok', $message);
        return $this->redirect($this->generateUrl('mycp_list_mail_list'));
    }

    function usersAction($mailList){
        $service_security= $this->get('Secure');
        $service_security->verifyAccess();
        $em=$this->getDoctrine()->getManager();
        $users = $em->getRepository('mycpBundle:mailListUser')->findBy(array('mail_list' => $mailList));
        //TODO

        $service_log = $this->get('log');
        $service_log->saveLog('Visit', BackendModuleName::MODULE_METATAGS);

        //TODO
    }

    function addUserAction($mailList){
        $service_security= $this->get('Secure');
        $service_security->verifyAccess();
        $em=$this->getDoctrine()->getManager();

        //TODO

        $service_log = $this->get('log');
        $service_log->saveLog('Insert new mail list user', BackendModuleName::MODULE_METATAGS);

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
          $service_log->saveLog('Remove mail list user '.$mailListUserId, BackendModuleName::MODULE_USER);

        $message = 'El usuario ha sido eliminado de la lista de correo satisfactoriamente.';
        $this->get('session')->getFlashBag()->add('message_ok', $message);
        return $this->redirect($this->generateUrl('mycp_list_mail_list_user', array('mailList' => $mailListId)));
    }
}
