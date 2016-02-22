<?php

namespace MyCp\mycpBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use MyCp\mycpBundle\Entity\award;
use MyCp\mycpBundle\Form\awardType;
use MyCp\mycpBundle\Helpers\BackendModuleName;

class BackendAwardController extends Controller {

    function listAction($items_per_page, Request $request) {
        //$service_security = $this->get('Secure');
        //$service_security->verifyAccess();
        $em = $this->getDoctrine()->getManager();

        $paginator = $this->get('ideup.simple_paginator');
        $paginator->setItemsPerPage($items_per_page);
        $awards = $paginator->paginate($em->getRepository('mycpBundle:award')->findAll())->getResult();
        $page = 1;
        if (isset($_GET['page']))
            $page = $_GET['page'];
        return $this->render('mycpBundle:award:list.html.twig', array(
                    'list' => $awards,
                    'items_per_page' => $items_per_page,
                    'total_items' => $paginator->getTotalItems(),
                    'current_page' => $page,
        ));
    }

    function newAction(Request $request) {
        //$service_security = $this->get('Secure');
        //$service_security->verifyAccess();
        $em = $this->getDoctrine()->getManager();
        $award = new award();
        $form = $this->createForm(new awardType(), $award);

        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $em->persist($award);
                $em->flush();
                $message = 'Premio añadido satisfactoriamente.';
                $this->get('session')->getFlashBag()->add('message_ok', $message);

                $service_log = $this->get('log');
                $service_log->saveLog('Create award, ' . $award->getName(), BackendModuleName::MODULE_ALBUM);

                return $this->redirect($this->generateUrl('mycp_list_awards'));
            }
        }
        return $this->render('mycpBundle:award:new.html.twig', array(
                    'form' => $form->createView(),
        ));
    }

    function editAction($id, Request $request) {
        //$service_security = $this->get('Secure');
        //$service_security->verifyAccess();
        $em = $this->getDoctrine()->getManager();
        $award = $em->getRepository('mycpBundle:award')->find(array('id' => $id));
        $form = $this->createForm(new awardType(), $award);
        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $em->persist($award);
                $em->flush();
                $message = 'Premio actualizado satisfactoriamente.';
                $this->get('session')->getFlashBag()->add('message_ok', $message);

                $service_log = $this->get('log');
                $service_log->saveLog('Edit award, ' . $award->getName(), BackendModuleName::MODULE_ALBUM);

                return $this->redirect($this->generateUrl('mycp_list_awards'));
            }
        }

        return $this->render('mycpBundle:award:new.html.twig', array(
                    'form' => $form->createView(), 'edit_award' => $id, 'name_award' => $award->getName()
        ));
    }

    function deleteAction($id) {
        //$service_security = $this->get('Secure');
        //$service_security->verifyAccess();
        $em = $this->getDoctrine()->getManager();
        $award = $em->getRepository('mycpBundle:award')->find($id);

        if ($award)
            $em->remove($award);
        $em->flush();
        $message = 'Premio eliminado satisfactoriamente.';
        $this->get('session')->getFlashBag()->add('message_ok', $message);

        $service_log = $this->get('log');
        $service_log->saveLog('Delete award, ' . $id, BackendModuleName::MODULE_ALBUM);

        return $this->redirect($this->generateUrl('mycp_list_awards'));
    }

}
