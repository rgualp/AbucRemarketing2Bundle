<?php

namespace MyCp\mycpBundle\Controller;

use MyCp\mycpBundle\Entity\log;
use MyCp\mycpBundle\Entity\ownershipPayment;
use MyCp\mycpBundle\Form\ownershipPaymentType;
use MyCp\mycpBundle\Helpers\DataBaseTables;
use MyCp\mycpBundle\Helpers\FileIO;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints\NotBlank;
use MyCp\mycpBundle\Helpers\BackendModuleName;

class BackendPaymentController extends Controller {

    function listAction($items_per_page, Request $request) {
        /*$service_security = $this->get('Secure');
        $service_security->verifyAccess();*/
        $em = $this->getDoctrine()->getManager();

        $filter_number = $request->get('filter_number');
        $filter_code = $request->get('filter_code');
        $filter_service = $request->get('filter_service');
        $filter_method = $request->get('filter_method');
        $filter_payment_date_from = $request->get('filter_payment_date_from');
        $filter_payment_date_to = $request->get('filter_payment_date_to');

        if ($request->getMethod() == 'POST' && $filter_number == 'null' && $filter_code == 'null' && $filter_service == 'null' &&
            $filter_method == 'null' && $filter_payment_date_from == 'null' && $filter_payment_date_to == 'null') {
            $message = 'Debe llenar al menos un campo para filtrar.';
            $this->get('session')->getFlashBag()->add('message_error_local', $message);
            return $this->redirect($this->generateUrl('mycp_list_payments'));
        }
        /*if ($filter_number == 'null')
            $filter_number = '';

        if ($filter_code == 'null')
            $filter_code = '';*/

        $paginator = $this->get('ideup.simple_paginator');
        $paginator->setItemsPerPage($items_per_page);
        $payments = $paginator->paginate($em->getRepository('mycpBundle:ownershipPayment')->findAllByCreationDate($filter_number, $filter_code, $filter_service, $filter_method, $filter_payment_date_from, $filter_payment_date_to))->getResult();
        $page = 1;
        if (isset($_GET['page']))
            $page = $_GET['page'];
        return $this->render('mycpBundle:payment:list.html.twig', array(
            'list' => $payments,
            'items_per_page' => $items_per_page,
            'total_items' => $paginator->getTotalItems(),
            'current_page' => $page,
            'filter_number' => $filter_number,
            'filter_code' => $filter_code,
            'filter_service' => $filter_service,
            'filter_method' => $filter_method,
            'filter_payment_date_from' => $filter_payment_date_from,
            'filter_payment_date_to' => $filter_payment_date_to
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

    function accommodationsNoPaymentsAction($items_per_page, Request $request)
    {
        //$service_security = $this->get('Secure');
        //$service_security->verifyAccess();

        $em = $this->getDoctrine()->getManager();

        $filter_name = $request->get('filter_name');
        $filter_code = $request->get('filter_code');
        $filter_destination = $request->get('filter_destination');
        $filter_creation_date_from = $request->get('filter_creation_date_from');
        $filter_creation_date_to = $request->get('filter_creation_date_to');

        if ($request->getMethod() == 'POST' && $filter_name == 'null' && $filter_code == 'null' && $filter_destination == 'null' &&
            $filter_creation_date_from == 'null' && $filter_creation_date_to == 'null') {
            $message = 'Debe llenar al menos un campo para filtrar.';
            $this->get('session')->getFlashBag()->add('message_error_local', $message);
            return $this->redirect($this->generateUrl('mycp_accommodations_no_payment'));
        }
        /*if ($filter_name == 'null')
            $filter_name = '';

        if ($filter_code == 'null')
            $filter_code = '';*/

        $accommodationsNoPayment = $em->getRepository('mycpBundle:ownershipPayment')->accommodationsNoInscriptionPayment(false,$filter_name, $filter_code, $filter_destination, $filter_creation_date_from, $filter_creation_date_to);

        $paginator = $this->get('ideup.simple_paginator');
        $paginator->setItemsPerPage($items_per_page);
        $accommodationsNoPayment = $paginator->paginate($accommodationsNoPayment)->getResult();
        $page = 1;
        if (isset($_GET['page']))
            $page = $_GET['page'];
        return $this->render('mycpBundle:payment:accommodations.html.twig', array(
            'list' => $accommodationsNoPayment,
            'items_per_page' => $items_per_page,
            'total_items' => $paginator->getTotalItems(),
            'current_page' => $page,
            'filter_code' => $filter_code,
            'filter_name' => $filter_name,
            'filter_destination' => $filter_destination,
            'filter_creation_date_from' => $filter_creation_date_from,
            'filter_creation_date_to' => $filter_creation_date_to
        ));
    }

    public function setPaymentCallbackAction() {
        $request = $this->getRequest();
        $em = $this->getDoctrine()->getManager();
        $accommodations_ids = $request->request->get('accommodations_ids');
        $items_per_page = $request->request->get('items_per_page');
        $filter_code = $request->request->get('filter_code');
        $filter_creation_date_from = $request->request->get('filter_creation_date_from');
        $filter_creation_date_to = $request->request->get('filter_creation_date_to');
        $filter_destination = $request->request->get('filter_destination');
        $filter_name = $request->request->get('filter_name');

        $service = $request->request->get('service');
        $method = $request->request->get('method');
        $amount = $request->request->get('amount');
        $paymentDate = $request->request->get('paymentDate');

        $response = null;

        $service_log = $this->get('log');
        //Premiar
        $em->getRepository('mycpBundle:ownershipPayment')->setAccommodationPayment($accommodations_ids, $service, $method, $amount, $paymentDate, $service_log);

        $message = 'Se ha registrado exitosamente el pago de ' . count($accommodations_ids);
        $this->get('session')->getFlashBag()->add('message_ok', $message);

        $response = $this->generateUrl('mycp_accommodations_no_payment', array("items_per_page" => $items_per_page,
            "filter_code" => $filter_code, "filter_destination" => $filter_destination, "filter_name" => $filter_name,
            "filter_creation_date_from" => $filter_creation_date_from, "filter_creation_date_to" => $filter_creation_date_to));

        return new Response($response);
    }

}
