<?php

namespace MyCp\mycpBundle\Controller;


use MyCp\mycpBundle\Form\pendingPaymentAgencyType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use MyCp\mycpBundle\Form\pendingPaytouristType;
use MyCp\mycpBundle\Form\cancelPaymentType;
use MyCp\mycpBundle\Entity\pendingPaytourist;



class BackendPendingPaymentAgencyController extends Controller {

    /**
     * @param $items_per_page
     * @param Request $request
     * @return Response
     */
    function listAction($items_per_page, Request $request) {
        /*$service_security = $this->get('Secure');
        $service_security->verifyAccess();*/
        $em = $this->getDoctrine()->getManager();

        $filter_number = $request->get('filter_number');
        $filter_code = $request->get('filter_code');
        $filter_method = $request->get('filter_method');
        $filter_payment_date_from = $request->get('filter_payment_date_from');
        $filter_payment_date_to = $request->get('filter_payment_date_to');

        $filter_type = $request->get('filter_type ');
        $filter_destination = $request->get('filter_destination');
        $filter_booking = $request->get('filter_booking');
        $filter_accommodation = $request->get('filter_accommodation');
        $filter_reservation = $request->get('filter_reservation');

        if ($request->getMethod() == 'POST' && $filter_number == 'null' && $filter_code == 'null' &&
            $filter_method == 'null' && $filter_payment_date_from == 'null' && $filter_payment_date_to == 'null' &&
            $filter_type == 'null' && $filter_destination == 'null' &&
            $filter_booking == 'null' && $filter_accommodation == 'null' && $filter_reservation == 'null'
        ) {
            $message = 'Debe llenar al menos un campo para filtrar.';
            $this->get('session')->getFlashBag()->add('message_error_local', $message);
            return $this->redirect($this->generateUrl('mycp_list_payments_pending_tourist'));
        }
        $paginator = $this->get('ideup.simple_paginator');
        $paginator->setItemsPerPage($items_per_page);
        $payments = $paginator->paginate($em->getRepository('PartnerBundle:paPendingPaymentAgency')->findAllByFilters($filter_number, $filter_code, $filter_method, $filter_payment_date_from, $filter_payment_date_to, $filter_type, $filter_destination, $filter_booking, $filter_accommodation, $filter_reservation))->getResult();
        $page = 1;
        if (isset($_GET['page']))
            $page = $_GET['page'];
        return $this->render('mycpBundle:pendingAgency:list.html.twig', array(
                'list' => $payments,
                'items_per_page' => $items_per_page,
                'total_items' => $paginator->getTotalItems(),
                'current_page' => $page,
                'filter_number' => $filter_number,
                'filter_code' => $filter_code,
                'filter_method' => $filter_method,
                'filter_payment_date_from' => $filter_payment_date_from,
                'filter_payment_date_to' => $filter_payment_date_to,
                'filter_type' => $filter_type,
                'filter_destination' => $filter_destination,
                'filter_booking' => $filter_booking,
                'filter_accommodation' => $filter_accommodation,
                'filter_reservation' => $filter_reservation
            ));
    }


    /**
     * @param $id
     * @param Request $request
     * @return mixed
     */
    public function detailAction($id, Request $request){
        $em = $this->getDoctrine()->getManager();
        $payment = $em->getRepository('PartnerBundle:paPendingPaymentAgency')->find($id);

        return $this->render('mycpBundle:pendingAgency:detail.html.twig', array(
                'payment'=>$payment
            ));
    }

    /**
     * @param $id
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    function editAction($id, Request $request) {
        /*$service_security = $this->get('Secure');
        $service_security->verifyAccess();*/
        $em = $this->getDoctrine()->getManager();
        $payment = $em->getRepository('PartnerBundle:paPendingPaymentAgency')->find($id);
        $form = $this->createForm(new pendingPaymentAgencyType(), $payment);

        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $user = $this->getUser();
                $payment->setUser($user);
                $payment->setRegisterDate(new \DateTime());

                $em->persist($payment);
                $em->flush();

                $message = 'Pago actualizado satisfactoriamente.';
                $this->get('session')->getFlashBag()->add('message_ok', $message);

                return $this->redirect($this->generateUrl('mycp_list_payments_pending_agency'));
            }
        }

        return $this->render('mycpBundle:pendingAgency:new.html.twig', array(
                'form' => $form->createView(), 'edit_payment' => $id, 'payment' => $payment
            ));
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    function saveAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $cheked=$request->get('cheked');
        if(count($cheked)){
            foreach($cheked as $item){
                $pay= $em->getRepository('PartnerBundle:paPendingPaymentAgency')->find($item);
                $pay->setType($em->getRepository('mycpBundle:nomenclator')->findOneBy(array("nom_name" => 'pendingPayment_payed_status')));

                $user = $this->getUser();
                $pay->setUser($user);
                $pay->setRegisterDate(new \DateTime());

                $em->persist($pay);
            }
            $em->flush();
            return new JsonResponse(['success' => true, 'message' =>'Se han adicionado los pagos satisfactoriamente']);
        }
        return new JsonResponse(['success' => false, 'message' =>'Debe de seleccionar algún pago']);
    }

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function exportAction(Request $request) {
        try {
            /*$service_security = $this->get('Secure');
            $service_security->verifyAccess();*/
            $em = $this->getDoctrine()->getManager();

            $filter_number = $request->get('filter_number');
            $filter_code = $request->get('filter_code');
            $filter_method = $request->get('filter_method');
            $filter_payment_date_from = $request->get('filter_payment_date_from');
            $filter_payment_date_to = $request->get('filter_payment_date_to');

            $filter_type = $request->get('filter_type ');
            $filter_destination = $request->get('filter_destination');
            $filter_booking = $request->get('filter_booking');
            $filter_accommodation = $request->get('filter_accommodation');
            $filter_reservation = $request->get('filter_reservation');

            $items = $em->getRepository('PartnerBundle:paPendingPaymentAgency')->findAllByFilters($filter_number, $filter_code, $filter_method, $filter_payment_date_from, $filter_payment_date_to, $filter_type, $filter_destination, $filter_booking, $filter_accommodation, $filter_reservation)->getResult();

            $date = new \DateTime();
            if(count($items)) {
                $exporter = $this->get("mycp.service.export_to_excel");
                return $exporter->exportPendingAgencyPayment($items, $date);
            }
            else {
                $message = 'No hay datos para llenar el Excel a descargar.';
                $this->get('session')->getFlashBag()->add('message_ok', $message);
                return $this->redirect($this->generateUrl("mycp_list_payments_pending_agency"));
            }
        }
        catch (\Exception $e) {
            $message = 'Ha ocurrido un error. Por favor, introduzca correctamente los valores para filtrar.';
            $this->get('session')->getFlashBag()->add('message_error_main', $message);

            return $this->redirect($this->generateUrl("mycp_list_payments_pending_agency"));
        }
    }
}