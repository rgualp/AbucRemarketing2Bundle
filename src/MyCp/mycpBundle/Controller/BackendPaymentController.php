<?php

namespace MyCp\mycpBundle\Controller;

use MyCp\mycpBundle\Entity\effectiveMethodPayment;
use MyCp\mycpBundle\Entity\log;
use MyCp\mycpBundle\Entity\ownershipPayment;
use MyCp\mycpBundle\Entity\transferMethodPayment;
use MyCp\mycpBundle\Form\effectiveMethodPaymentType;
use MyCp\mycpBundle\Form\ownershipPaymentType;
use MyCp\mycpBundle\Form\transferMethodPaymentType;
use MyCp\mycpBundle\Helpers\BackendModuleName;
use MyCp\mycpBundle\Helpers\DataBaseTables;
use MyCp\PartnerBundle\Entity\paInvoice;
use Proxies\__CG__\MyCp\mycpBundle\Entity\ownershipStatus;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class BackendPaymentController extends Controller
{

    function listAction($items_per_page, Request $request)
    {
        //$service_security = $this->get('Secure');
        //$service_security->verifyAccess();
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

    function newAction(Request $request)
    {
        $service_security = $this->get('Secure');
        $service_security->verifyAccess();
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

    function editAction($id, Request $request)
    {
        $service_security = $this->get('Secure');
        $service_security->verifyAccess();
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

    function deleteAction($id)
    {
        $service_security = $this->get('Secure');
        $service_security->verifyAccess();

        $em = $this->getDoctrine()->getManager();
        $payment = $em->getRepository('mycpBundle:ownershipPayment')->find($id);

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
        $service_security = $this->get('Secure');
        $service_security->verifyAccess();

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

        $accommodationsNoPayment = $em->getRepository('mycpBundle:ownershipPayment')->accommodationsNoInscriptionPayment(false, $filter_name, $filter_code, $filter_destination, $filter_creation_date_from, $filter_creation_date_to);

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

    public function setPaymentCallbackAction()
    {
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

    function inactiveAccommodationAction($id)
    {
        /*$service_security = $this->get('Secure');
        $service_security->verifyAccess();*/

        $em = $this->getDoctrine()->getManager();
        $accommodation = $em->getRepository('mycpBundle:ownership')->find($id);

        if ($accommodation) {
            $status = $em->getRepository("mycpBundle:ownershipStatus")->find(ownershipStatus::STATUS_INACTIVE);
            $accommodation->setOwnStatus($status);
            $em->persist($accommodation);
            $em->flush();
        }

        $message = 'Se ha inactivado satisfactoriamente el alojamiento ' . $accommodation->getOwnMcpCode();
        $this->get('session')->getFlashBag()->add('message_ok', $message);

        $service_log = $this->get('log');
        $service_log->saveLog($message, BackendModuleName::MODULE_ACCOMMODATION_PAYMENT, log::OPERATION_UPDATE, DataBaseTables::OWNERSHIP);


        return $this->redirect($this->generateUrl('mycp_accommodations_no_payment'));
    }

    function sendEmailReminderAction($id, Request $request)
    {
        /*$service_security = $this->get('Secure');
        $service_security->verifyAccess();*/

        try {
            $emailManager = $this->get('mycp.service.email_manager');

            $em = $this->getDoctrine()->getManager();
            $accommodation = $em->getRepository('mycpBundle:ownership')->find($id);

            $accommodationEmail = ($accommodation->getOwnEmail1()) ? $accommodation->getOwnEmail1() : $accommodation->getOwnEmail2();
            $userName = ($accommodation->getOwnHomeowner1()) ? $accommodation->getOwnHomeowner1() : $accommodation->getOwnHomeowner2();

            $emailSubject = "Esperamos por ti en MyCasaParticular.com";

            $termsUrl = $this->generateUrl('frontend_legal_terms', array(
                'locale' => 'es',
                '_locale' => 'es'
            ), true);

            $emailBody = $emailManager->getViewContent('MyCpCasaModuleBundle:mail:payment_reminder.html.twig',
                array('user_name' => $userName,
                    'termsUrl' => $termsUrl,
                    'user_locale' => "es"));

            $emailManager->sendEmail($accommodationEmail, $emailSubject, $emailBody, "casa@mycasaparticular.com");

            $message = 'Se ha enviado satisfactoriamente un correo de recordatorio de pago al alojamiento ' . $accommodation->getOwnMcpCode();
            $this->get('session')->getFlashBag()->add('message_ok', $message);

            $service_log = $this->get('log');
            $service_log->saveLog($message, BackendModuleName::MODULE_ACCOMMODATION_PAYMENT, log::OPERATION_EMAIL);
        } catch (\Exception $e) {
            $message = 'Ha ocurrido un error: ' . $e->getMessage();
            $this->get('session')->getFlashBag()->add('message_error_main', $message);
        }

        return $this->redirect($this->generateUrl('mycp_accommodations_no_payment'));

    }

    function methodsAction($items_per_page, Request $request)
    {

        $em = $this->getDoctrine()->getManager();

        $filter_name = $request->get('filter_name');
        $filter_code = $request->get('filter_code');
        $filter_destination = $request->get('filter_destination');
        $filter_province = $request->get('filter_province');

        if ($request->getMethod() == 'POST' && $filter_name == 'null' && $filter_code == 'null' && $filter_destination == 'null' &&
            $filter_province == 'null') {
            $message = 'Debe llenar al menos un campo para filtrar.';
            $this->get('session')->getFlashBag()->add('message_error_local', $message);
            return $this->redirect($this->generateUrl('mycp_methods_payment'));
        }
        if ($filter_name == 'null')
            $filter_name = '';

        if ($filter_code == 'null')
            $filter_code = '';
        $page = 1;
        if (isset($_GET['page']))
            $page = $_GET['page'];

        $paginator = $em->getRepository('mycpBundle:ownership')->getPaymentMethodsList($filter_name, $filter_code, $filter_destination, $filter_province, $items_per_page, $page);
        return $this->render('mycpBundle:payment:methods.html.twig', array(
            'list' => $paginator->getQuery()->getResult(),
            'paginator' => $paginator,
            'items_per_page' => $paginator->getLimit(),
            'total_items' => $paginator->count(),
            'current_page' => $paginator->getCurrentPage(),
            'filter_name' => $filter_name,
            'filter_code' => $filter_code,
            'filter_province' => $filter_province,
            'filter_destination' => $filter_destination
        ));
    }

    public function insertTransferMethodAction($idAccommodation, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $transferMethod = new transferMethodPayment();
        $accommodation = $em->getRepository("mycpBundle:ownership")->find($idAccommodation);
        $form = $this->createForm(new transferMethodPaymentType(), $transferMethod);

        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);
            if ($form->isValid() && $accommodation != null) {

                $transferMethod->setAccommodation($accommodation);
                $em->persist($transferMethod);
                $em->flush();

                $message = 'Método de pago añadido satisfactoriamente.';
                $this->get('session')->getFlashBag()->add('message_ok', $message);

                $service_log = $this->get('log');
                $service_log->saveLog($transferMethod->getId(), BackendModuleName::MODULE_ACCOMMODATION_PAYMENT, log::OPERATION_INSERT, DataBaseTables::ACCOMMODATION_PAYMENT);

                return $this->redirect($this->generateUrl('mycp_methods_payment'));
            }
        }
        return $this->render('mycpBundle:payment:new_method.html.twig', array(
            'form' => $form->createView(),
            'accommodation' => $accommodation
        ));

    }

    public function editTransferMethodAction($id, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $transferMethod = $em->getRepository("mycpBundle:transferMethodPayment")->find($id);
        $form = $this->createForm(new transferMethodPaymentType(), $transferMethod);

        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);
            if ($form->isValid()) {

                $em->persist($transferMethod);
                $em->flush();

                $message = 'Método de pago actualizado satisfactoriamente.';
                $this->get('session')->getFlashBag()->add('message_ok', $message);

                $service_log = $this->get('log');
                $service_log->saveLog($transferMethod->getId(), BackendModuleName::MODULE_ACCOMMODATION_PAYMENT, log::OPERATION_UPDATE, DataBaseTables::ACCOMMODATION_PAYMENT);

                return $this->redirect($this->generateUrl('mycp_methods_payment'));
            }
        }
        return $this->render('mycpBundle:payment:new_method.html.twig', array(
            'form' => $form->createView(),
            'accommodation' => $transferMethod->getAccommodation(),
            'edit_payment' => $transferMethod->getId()
        ));

    }

    public function deleteTransferMethodAction($id, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $transferMethod = $em->getRepository("mycpBundle:transferMethodPayment")->find($id);

        if ($transferMethod)
            $em->remove($transferMethod);
        $em->flush();

        $message = 'Método de pago eliminado satisfactoriamente.';
        $this->get('session')->getFlashBag()->add('message_ok', $message);

        $service_log = $this->get('log');
        $service_log->saveLog($transferMethod->getId(), BackendModuleName::MODULE_ACCOMMODATION_PAYMENT, log::OPERATION_DELETE, DataBaseTables::ACCOMMODATION_PAYMENT);


        return $this->redirect($this->generateUrl('mycp_methods_payment'));


    }

    public function insertEffectiveMethodAction($idAccommodation, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $effectiveMethod = new effectiveMethodPayment();
        $accommodation = $em->getRepository("mycpBundle:ownership")->find($idAccommodation);
        $form = $this->createForm(new effectiveMethodPaymentType(), $effectiveMethod);

        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);
            if ($form->isValid() && $accommodation != null) {

                $effectiveMethod->setAccommodation($accommodation);
                $em->persist($effectiveMethod);
                $em->flush();

                $message = 'Método de pago añadido satisfactoriamente.';
                $this->get('session')->getFlashBag()->add('message_ok', $message);

                $service_log = $this->get('log');
                $service_log->saveLog($effectiveMethod->getId(), BackendModuleName::MODULE_ACCOMMODATION_PAYMENT, log::OPERATION_INSERT, DataBaseTables::ACCOMMODATION_PAYMENT);

                return $this->redirect($this->generateUrl('mycp_methods_payment'));
            }
        }
        return $this->render('mycpBundle:payment:new_method_effective.html.twig', array(
            'form' => $form->createView(),
            'accommodation' => $accommodation
        ));

    }

    public function editEffectiveMethodAction($id, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $effectiveMethod = $em->getRepository("mycpBundle:effectiveMethodPayment")->find($id);
        $form = $this->createForm(new effectiveMethodPaymentType(), $effectiveMethod);

        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);
            if ($form->isValid()) {

                $em->persist($effectiveMethod);
                $em->flush();

                $message = 'Método de pago actualizado satisfactoriamente.';
                $this->get('session')->getFlashBag()->add('message_ok', $message);

                $service_log = $this->get('log');
                $service_log->saveLog($effectiveMethod->getId(), BackendModuleName::MODULE_ACCOMMODATION_PAYMENT, log::OPERATION_UPDATE, DataBaseTables::ACCOMMODATION_PAYMENT);

                return $this->redirect($this->generateUrl('mycp_methods_payment'));
            }
        }
        return $this->render('mycpBundle:payment:new_method_effective.html.twig', array(
            'form' => $form->createView(),
            'accommodation' => $effectiveMethod->getAccommodation(),
            'edit_payment' => $effectiveMethod->getId()
        ));

    }

    public function deleteEffectiveMethodAction($id, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $effectiveMethod = $em->getRepository("mycpBundle:effectiveMethodPayment")->find($id);

        if ($effectiveMethod)
            $em->remove($effectiveMethod);
        $em->flush();

        $message = 'Método de pago eliminado satisfactoriamente.';
        $this->get('session')->getFlashBag()->add('message_ok', $message);

        $service_log = $this->get('log');
        $service_log->saveLog($effectiveMethod->getId(), BackendModuleName::MODULE_ACCOMMODATION_PAYMENT, log::OPERATION_DELETE, DataBaseTables::ACCOMMODATION_PAYMENT);


        return $this->redirect($this->generateUrl('mycp_methods_payment'));


    }

    public function list_reservations_ag_reservedAction($items_per_page, Request $request)
    {
        $service_security = $this->get('Secure');
        $service_security->verifyAccess();
        $page = 1;
        $filterbr = $request->get('filterbr');
        $filter_date_reserve = $request->get('filter_date_reserve');
        $filter_agency = $request->get('filter_agency');
        $filter_date_reserve2 = $request->get('filter_date_reserve2');
        $filter_offer_number = $request->get('filter_offer_number');
        $filter_reference = $request->get('filter_reference');
        $filter_client = $request->get('filter_client');
        $filter_date_from = $request->get('filter_date_from');
        $filter_date_to = $request->get('filter_date_to');
        $filter_booking_number = $request->get('filter_booking_number');
        $filter_status = $request->get('filter_status');
        $price = 0;
        $sort_by = $request->get('sort_by');
        if ($request->getMethod() == 'POST' && $filter_date_reserve == 'null' && $filter_offer_number == 'null' && $filter_reference == 'null' &&
            $filter_date_from == 'null' && $filter_date_to == 'null' && $sort_by == 'null' && $filter_booking_number == 'null' && $filter_status == 'null') {
            $message = 'Debe llenar al menos un campo para filtrar.';
            $this->get('session')->getFlashBag()->add('message_error_local', $message);
            return $this->redirect($this->generateUrl('mycp_list_reservations_ag_reserved'));
        }

        if ($filter_agency == 'null')
            $filter_agency = '';
        if ($filter_date_reserve == 'null')
            $filter_date_reserve = '';
        if ($filterbr == 'null')
            $filterbr = '';
        if ($filter_date_reserve2 == 'null')
            $filter_date_reserve2 = '';
        if ($filter_offer_number == 'null')
            $filter_offer_number = '';
        if ($filter_booking_number == 'null')
            $filter_booking_number = '';
        if ($filter_reference == 'null')
            $filter_reference = '';
        if ($filter_client == 'null')
            $filter_client = '';
        if ($filter_date_from == 'null')
            $filter_date_from = '';
        if ($filter_date_to == 'null')
            $filter_date_to = '';
        if ($filter_status == 'null')
            $filter_status = '';
        if ($sort_by == 'null')
            $sort_by = '';

        if (isset($_GET['page']))
            $page = $_GET['page'];
        $filter_date_reserve = str_replace('_', '/', $filter_date_reserve);
        $filter_date_reserve2 = str_replace('_', '/', $filter_date_reserve2);
        $filter_date_from = str_replace('_', '/', $filter_date_from);
        $filter_date_to = str_replace('_', '/', $filter_date_to);

        $em = $this->getDoctrine()->getManager();
        $paginator = $this->get('ideup.simple_paginator');
        $paginator->setItemsPerPage($items_per_page);

        $all = $paginator->paginate($em->getRepository('mycpBundle:generalReservation')
            ->getAllPagReserved($filter_date_reserve, $filter_date_reserve2, $filterbr, $filter_agency, $filter_offer_number, $filter_reference, $filter_date_from, $filter_date_to, $sort_by, $filter_booking_number, $filter_status, $filter_client, $items_per_page, $page, true))->getResult();
        $reservations = $all['reservations'];
        $filter_date_reserve_twig = str_replace('/', '_', $filter_date_reserve);
        $filter_date_reserve2_twig = str_replace('/', '_', $filter_date_reserve2);
        $filter_date_from_twig = str_replace('/', '_', $filter_date_from);
        $filter_date_to_twig = str_replace('/', '_', $filter_date_to);

        $totalItems = $all['totalItems'];
        $last_page_number = ceil($totalItems / $items_per_page);
        $all = $paginator->paginate($em->getRepository('mycpBundle:generalReservation')
            ->getAllPagReserved(null, null, null, null, null, null, null, null, null, null, null, null, null, $page, true))->getResult();
        $reservations1 = $all['reservations'];

        $mindate = reset($reservations1)['gen_res_date'];
        $maxdate = end($reservations1)['gen_res_date'];


        $start_page = ($page == 1) ? ($page) : ($page - 1);
        $end_page = ($page == $last_page_number) ? ($last_page_number) : ($page + 1);

        return $this->render('mycpBundle:payment:list_ag_reserved.html.twig', array(
            //'total_nights' => $total_nights,
            'reservations' => $reservations,
            'items_per_page' => $items_per_page,
            'current_page' => $page,
            'total_items' => $totalItems,
            'last_page_number' => $last_page_number,
            'start_page' => $start_page,
            'end_page' => $end_page,
            'filterbr' => $filterbr,
            'filter_date_reserve' => $filter_date_reserve,
            'filter_date_reserve2' => $filter_date_reserve2,
            'filter_offer_number' => $filter_offer_number,
            'filter_booking_number' => $filter_booking_number,
            'filter_reference' => $filter_reference,
            'filter_date_from' => $filter_date_from,
            'filter_date_to' => $filter_date_to,
            'sort_by' => $sort_by,
            'filter_date_reserve_twig' => $filter_date_reserve_twig,
            'filter_date_reserve2_twig' => $filter_date_reserve2_twig,
            'filter_date_from_twig' => $filter_date_from_twig,
            'filter_date_to_twig' => $filter_date_to_twig,
            'filter_status' => $filter_status,
            'filter_agency' => $filter_agency,
            'filter_client' => $filter_client,
            'date1' => $mindate,
            'date2' => $maxdate

        ));
    }

    public function list_ivoice_agAction($items_per_page, Request $request)
    {
        $service_security = $this->get('Secure');
        $service_security->verifyAccess();
        $page = 1;
        $filter_invoice = $request->get('filter_invoice');

        $filter_agency = $request->get('filter_agency');
        $filter_date_reserve = $request->get('filter_date_reserve');
        $filter_date_reserve2 = $request->get('filter_date_reserve2');


        if ($request->getMethod() == 'POST' && $filter_date_reserve == 'null' && $filter_date_reserve2 == 'null' && $filter_invoice == 'null' && $filter_agency == 'null') {
            $message = 'Debe llenar al menos un campo para filtrar.';
            $this->get('session')->getFlashBag()->add('message_error_local', $message);
            return $this->redirect($this->generateUrl('mycp_list_ivoice_ag'));
        }

        if ($filter_agency == 'null')
            $filter_agency = '';
        if ($filter_date_reserve == 'null')
            $filter_date_reserve = '';

        if ($filter_date_reserve2 == 'null')
            $filter_date_reserve2 = '';
        if ($filter_invoice == 'null')
            $filter_invoice = '';


        if (isset($_GET['page']))
            $page = $_GET['page'];
        $filter_date_reserve = str_replace('_', '/', $filter_date_reserve);
        $filter_date_reserve2 = str_replace('_', '/', $filter_date_reserve2);


        $em = $this->getDoctrine()->getManager();
        $paginator = $this->get('ideup.simple_paginator');
        $paginator->setItemsPerPage($items_per_page);

        $all = $paginator->paginate($em->getRepository('PartnerBundle:paInvoice')
            ->getAllPag($filter_date_reserve, $filter_date_reserve2, $filter_invoice, $filter_agency, $items_per_page, $page, true))->getResult();
        $reservations = $all['reservations'];
        $filter_date_reserve_twig = str_replace('/', '_', $filter_date_reserve);
        $filter_date_reserve2_twig = str_replace('/', '_', $filter_date_reserve2);


        $totalItems = $all['totalItems'];
        $last_page_number = ceil($totalItems / $items_per_page);


        $start_page = ($page == 1) ? ($page) : ($page - 1);
        $end_page = ($page == $last_page_number) ? ($last_page_number) : ($page + 1);
        $all = $paginator->paginate($em->getRepository('mycpBundle:generalReservation')
            ->getAllPagReserved(null, null, null, null, null, null, null, null, null, null, null, null, null, $page, true))->getResult();
        $reservations1 = $all['reservations'];

        $mindate = reset($reservations1)['gen_res_date'];
        $maxdate = end($reservations1)['gen_res_date'];
        return $this->render('mycpBundle:payment:list_ag_invoice.html.twig', array(
            //'total_nights' => $total_nights,
            'reservations' => $reservations,
            'items_per_page' => $items_per_page,
            'current_page' => $page,
            'total_items' => $totalItems,
            'last_page_number' => $last_page_number,
            'start_page' => $start_page,
            'end_page' => $end_page,
            'filter_invoice' => $filter_invoice,
            'filter_agency' => $filter_agency,
            'filter_date_reserve' => $filter_date_reserve,
            'filter_date_reserve2' => $filter_date_reserve2,

            'filter_date_reserve_twig' => $filter_date_reserve_twig,
            'filter_date_reserve2_twig' => $filter_date_reserve2_twig,
            'date1' => $mindate,
            'date2' => $maxdate


        ));
    }

    public function invoice_ag_selectionAction($items_per_page, Request $request)
    {
        $service_security = $this->get('Secure');
        $service_security->verifyAccess();
        $page = 1;

        $filter_date_reserve = $request->get('filter_date_reserve');
        $filter_agency = $request->get('filter_agency');
        $filter_date_reserve2 = $request->get('filter_date_reserve2');

        $price = 0;
        $sort_by = $request->get('sort_by');

        if ($filter_agency == 'null')
            $filter_agency = '';
        if ($filter_date_reserve == 'null')
            $filter_date_reserve = '';

        if ($filter_date_reserve2 == 'null')
            $filter_date_reserve2 = '';


        if ($sort_by == 'null')
            $sort_by = '';

        if (isset($_GET['page']))
            $page = $_GET['page'];
        $filter_date_reserve = str_replace('_', '/', $filter_date_reserve);
        $filter_date_reserve2 = str_replace('_', '/', $filter_date_reserve2);


        $em = $this->getDoctrine()->getManager();
        $paginator = $this->get('ideup.simple_paginator');
        $paginator->setItemsPerPage($items_per_page);

        $all = $em->getRepository('mycpBundle:generalReservation')
            ->getAllPagReserved(null, null, null, $filter_agency, null, null, $filter_date_reserve, $filter_date_reserve2, $sort_by, null, null, null, $items_per_page, $page, true);
        $reservations = $all['reservations'];
        $data = $em->getRepository('PartnerBundle:paInvoice')->getLastCreatedDatePartner();
        if (count($data)) {
            $lastdate = $data[0]['filename'];

            $dtData = split('-', $lastdate); // $dtData[0] = date part, $dtData[1] = Prefix part
            $dtDate = split('_', $dtData[0]);
            $month1 = date("m", strtotime($dtDate[1])); //Get the month of retrived date

//$date2 = date("Ymd"); // Get current date
// OR
            $date2 = date("Ym", strtotime("+1 day", strtotime($dtDate[1]))); // OR Increment last-date by one day
            $month2 = date("m", strtotime($date2));  // Get updated date's  month

// now calculate month difference between dates. if months are same, prefix will be increased else prefix will be reset to 1
            $prefix = (($month2 - $month1) == 0) ? str_pad(++$dtData[1], 2, '0', STR_PAD_LEFT) : str_pad(1, 2, '0', STR_PAD_LEFT);
            $pdfName = 'F_' . $date2 . '-' . $prefix;
        } else {
            $lastdate = (new \DateTime())->format('Ym');
            $pdfName = 'F_' . $lastdate . '-' . '01';
        }


        return new JsonResponse([
            'success' => true,
            'reservations' => $reservations,
            'invoice' => $pdfName,


        ]);

    }

    public function invoice_ag_exportAction($items_per_page, Request $request)
    {
        $service_security = $this->get('Secure');
        $service_security->verifyAccess();
        $page = 1;

        $filter_date_reserve = $request->get('filter_date_reserve');
        $filter_agency = $request->get('filter_agency');
        $filter_date_reserve2 = $request->get('filter_date_reserve2');

        $price = 0;
        $sort_by = $request->get('sort_by');

        if ($filter_agency == 'null')
            $filter_agency = '';
        if ($filter_date_reserve == 'null')
            $filter_date_reserve = '';

        if ($filter_date_reserve2 == 'null')
            $filter_date_reserve2 = '';


        if ($sort_by == 'null')
            $sort_by = '';

        if (isset($_GET['page']))
            $page = $_GET['page'];
        $filter_date_reserve = str_replace('_', '/', $filter_date_reserve);
        $filter_date_reserve2 = str_replace('_', '/', $filter_date_reserve2);


        $em = $this->getDoctrine()->getManager();
        $paginator = $this->get('ideup.simple_paginator');
        $paginator->setItemsPerPage($items_per_page);

        $all = $em->getRepository('mycpBundle:generalReservation')
            ->getAllPagReserved(null, null, null, $filter_agency, null, null, $filter_date_reserve, $filter_date_reserve2, $sort_by, null, null, null, $items_per_page, $page, true);
        $reservations = $all['reservations'];
        $user = $this->getUser();


        $path = $this->container->getParameter('configuration.dir.invoice');
        $data = $em->getRepository('PartnerBundle:paInvoice')->getLastCreatedDatePartner();
        if (count($data)) {
            $lastdate = $data[0]['filename'];
            $dtData = split('-', $lastdate); // $dtData[0] = date part, $dtData[1] = Prefix part
            $dtDate = split('_', $dtData[0]);
            $month1 = date("m", strtotime($dtDate[1])); //Get the month of retrived date

//$date2 = date("Ymd"); // Get current date
// OR
            $date2 = date("Ym", strtotime("+1 day", strtotime($dtDate[1]))); // OR Increment last-date by one day
            $month2 = date("m", strtotime($date2));  // Get updated date's  month

// now calculate month difference between dates. if months are same, prefix will be increased else prefix will be reset to 1
            $prefix = (($month2 - $month1) == 0) ? str_pad(++$dtData[1], 2, '0', STR_PAD_LEFT) : str_pad(1, 2, '0', STR_PAD_LEFT);
            $pdfName = 'F_' . $date2 . '-' . $prefix;
        } else {
            $lastdate = (new \DateTime())->format('Ym');
            $pdfName = 'F_' . $lastdate . '-' . '01';
        }

        $response = $this->render('mycpBundle:pdf:invoiceAgency.html.twig', array('reservations' => $reservations, 'user' => $user, 'ID' => $pdfName));

        $success = false;

        $pdfFilePath = $path . "$pdfName.pdf";
        if (file_exists($pdfFilePath)) {

        } else {
            $pdfService = $this->get('front_end.services.pdf');

            $success = $pdfService->storeHtmlAsPdf($response, $pdfFilePath);


            if ($success) {
                $invoice = new paInvoice();
                $invoice->setFilename($pdfName);
                $invoice->setInvoicedate((new \DateTime()));
                $em->persist($invoice);
                $em->flush();

                $repo = $em->getRepository('mycpBundle:generalReservation');

                foreach ($reservations as $reserva) {
                    $gr = $repo->find($reserva['gen_res_id']);
                    $gr->setGenResStatus(10);
                    $gr->setGenResStatusDate(new \DateTime());
                    $gr->setInvoice($invoice);
                    $repo->addInvoice($gr->getGenResId(), $invoice->getId());

                    $em->persist($gr);

                }
                $em->flush();
            }
        }


        return new JsonResponse([
            'success' => $success


        ]);


//
//
//
//      return  new BinaryFileResponse($pdfFilePath);

    }

    public function exportReservationsAction(Request $request)
    {
        try {
            //$service_security = $this->get('Secure');
            //$service_security->verifyAccess();
            $filters_apply = array();
            $page = 1;
            $filterbr = $request->get('filterbr');
            $filter_date_reserve = $request->get('filter_date_reserve');
            $filter_agency = $request->get('filter_agency');
            $filter_date_reserve2 = $request->get('filter_date_reserve2');
            $filter_offer_number = $request->get('filter_offer_number');
            $filter_reference = $request->get('filter_reference');
            $filter_client = $request->get('filter_client');
            $filter_date_from = $request->get('filter_date_from');
            $filter_date_to = $request->get('filter_date_to');
            $filter_booking_number = $request->get('filter_booking_number');
            $filter_status = $request->get('filter_status');
            $sort_by = $request->get('sort_by');
            $em = $this->getDoctrine()->getManager();
            $array_agency = array();
            if ($filter_agency != 'null')
                $array_agency = explode(',', $filter_agency);
            foreach ($array_agency as $ange) {
                array_push($filters_apply, '-Agencia:' . $em->getRepository("PartnerBundle:PaTravelAgency")->find($ange)->getName());
            }

            if ($filter_agency == 'null')
                $filter_agency = '';
            if ($filter_client != 'null' && $filter_client != '')
                array_push($filters_apply, '-Cliente:' . $filter_client);
            if ($filter_date_reserve != 'null')
                array_push($filters_apply, '-Check in from:' . $filter_date_reserve);
            if ($filter_date_reserve == 'null')
                $filter_date_reserve = '';

            if ($filter_date_reserve2 != 'null')
                array_push($filters_apply, '-Check in to:' . $filter_date_reserve2);
            if ($filterbr != 'null')
                array_push($filters_apply, '-BR:' . $filterbr);
            if ($filterbr == 'null')
                $filterbr = '';
            if ($filter_booking_number != 'null')
                array_push($filters_apply, '-Booking:' . $filter_booking_number);
            if ($filter_date_reserve2 == 'null')
                $filter_date_reserve2 = '';
            if ($filter_offer_number == 'null')
                $filter_offer_number = '';
            if ($filter_booking_number == 'null')
                $filter_booking_number = '';

            if ($filter_reference == 'null')
                $filter_reference = '';

            if ($filter_client == 'null')
                $filter_client = '';

            if ($filter_date_from == 'null')
                $filter_date_from = '';
            if ($filter_date_to == 'null')
                $filter_date_to = '';
            if ($filter_status == 'null')
                $filter_status = '';
            if ($sort_by == 'null')
                $sort_by = '';
            if (isset($_GET['page']))
                $page = $_GET['page'];
            $filter_date_reserve = str_replace('_', '/', $filter_date_reserve);
            $filter_date_reserve2 = str_replace('_', '/', $filter_date_reserve2);
            $filter_date_from = str_replace('_', '/', $filter_date_from);
            $filter_date_to = str_replace('_', '/', $filter_date_to);
            $date = new \DateTime();
            $date = date_modify($date, "-5 days");
            $paginator = $this->get('ideup.simple_paginator');
            $paginator->setItemsPerPage(10);
            $em = $this->getDoctrine()->getManager();
            $all = $paginator->paginate($em->getRepository('mycpBundle:generalReservation')
                ->getAllPagReserved($filter_date_reserve, $filter_date_reserve2, $filterbr, $filter_agency, $filter_offer_number, $filter_reference, $filter_date_from, $filter_date_to, $sort_by, $filter_booking_number, $filter_status, $filter_client, null, $page, true))->getResult();
            $reservations = $all['reservations'];

            $date = new \DateTime();
            $date = date_modify($date, "-5 days");
            if (count($reservations)) {
                $exporter = $this->get("mycp.service.export_to_excel");
                return $exporter->exportReservationsReservedAg($reservations, $date, $filters_apply);
            } else {
                $message = 'No hay datos para llenar el Excel a descargar.';
                $this->get('session')->getFlashBag()->add('message_ok', $message);

                return $this->redirect($this->generateUrl("mycp_list_reservations_ag_reserved"));
            }
        } catch (\Exception $e) {
            $message = 'Ha ocurrido un error. Por favor, introduzca correctamente los valores para filtrar.';
            $this->get('session')->getFlashBag()->add('message_error_main', $message);

            return $this->redirect($this->generateUrl("mycp_list_reservations_ag_reserved"));
        }
    }

}
