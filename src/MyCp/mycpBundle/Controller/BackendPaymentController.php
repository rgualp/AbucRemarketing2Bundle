<?php

namespace MyCp\mycpBundle\Controller;

use MyCp\mycpBundle\Entity\log;
use MyCp\mycpBundle\Entity\ownershipPayment;
use MyCp\mycpBundle\Form\ownershipPaymentType;
use MyCp\mycpBundle\Helpers\DataBaseTables;
use MyCp\mycpBundle\Helpers\FileIO;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints\NotBlank;
use MyCp\mycpBundle\Helpers\BackendModuleName;

class BackendPaymentController extends Controller {

    function listAction($items_per_page, Request $request) {
        /*$service_security = $this->get('Secure');
        $service_security->verifyAccess();*/
        $em = $this->getDoctrine()->getManager();

        $paginator = $this->get('ideup.simple_paginator');
        $paginator->setItemsPerPage($items_per_page);
        $payments = $paginator->paginate($em->getRepository('mycpBundle:ownershipPayment')->findAllByCreationDate())->getResult();
        $page = 1;
        if (isset($_GET['page']))
            $page = $_GET['page'];
        return $this->render('mycpBundle:payment:list.html.twig', array(
            'list' => $payments,
            'items_per_page' => $items_per_page,
            'total_items' => $paginator->getTotalItems(),
            'current_page' => $page,
        ));
    }

    function newAction(Request $request) {
        //$service_security = $this->get('Secure');
        //$service_security->verifyAccess();
        $em = $this->getDoctrine()->getManager();
        $payment = new ownershipPayment();
        $form = $this->createForm(new ownershipPaymentType(), $payment);

        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);
            if ($form->isValid()) {

                $em->persist($payment);
                $em->flush();

                $message = 'Pago añadido satisfactoriamente.';
                $this->get('session')->getFlashBag()->add('message_ok', $message);

                $service_log = $this->get('log');
                $service_log->saveLog($payment->getNumber(), BackendModuleName::MODULE_ACCOMMODATION_PAYMENT, log::OPERATION_INSERT, DataBaseTables::ACCOMMODATION_PAYMENT);

                return $this->redirect($this->generateUrl('mycp_list_payments'));
            }
        }
        return $this->render('mycpBundle:payment:new.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    function editAction($id, Request $request) {
        //$service_security = $this->get('Secure');
        //$service_security->verifyAccess();
        $em = $this->getDoctrine()->getManager();
        $payment = $em->getRepository('mycpBundle:ownershipPayment')->find(array('id' => $id));
       $form = $this->createForm(new ownershipPaymentType(), $payment);

        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);
            if ($form->isValid()) {

                $em->persist($payment);
                $em->flush();

                $message = 'Pago actualizado satisfactoriamente.';
                $this->get('session')->getFlashBag()->add('message_ok', $message);

                $service_log = $this->get('log');
                $service_log->saveLog($payment->getNumber(), BackendModuleName::MODULE_ACCOMMODATION_PAYMENT, log::OPERATION_UPDATE, DataBaseTables::ACCOMMODATION_PAYMENT);

                return $this->redirect($this->generateUrl('mycp_list_payments'));
            }
        }

        return $this->render('mycpBundle:payment:new.html.twig', array(
            'form' => $form->createView(), 'edit_payment' => $id, 'payment' => $payment
        ));
    }

    function deleteAction($id) {
        //$service_security = $this->get('Secure');
        //$service_security->verifyAccess();

        $em = $this->getDoctrine()->getManager();
        $payment= $em->getRepository('mycpBundle:ownershipPayment')->find($id);

        if ($payment)
            $em->remove($payment);
        $em->flush();
        $message = 'Pago eliminado satisfactoriamente.';
        $this->get('session')->getFlashBag()->add('message_ok', $message);

        $service_log = $this->get('log');
        $service_log->saveLog($payment->getNumber(), BackendModuleName::MODULE_ACCOMMODATION_PAYMENT, log::OPERATION_DELETE, DataBaseTables::ACCOMMODATION_PAYMENT);


        return $this->redirect($this->generateUrl('mycp_list_payments'));
    }

}
