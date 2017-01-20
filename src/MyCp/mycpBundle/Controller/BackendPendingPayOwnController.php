<?php

namespace MyCp\mycpBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use MyCp\mycpBundle\Form\pendingPayownershipType;
use MyCp\mycpBundle\Form\cancelPaymentType;
use MyCp\mycpBundle\Entity\pendingPayown;

class BackendPendingPayOwnController extends Controller {

    /**
     * @param $items_per_page
     * @param Request $request
     * @return Response
     */
    function listAction($items_per_page, Request $request) {
        $service_security = $this->get('Secure');
        $service_security->verifyAccess();
        $em = $this->getDoctrine()->getManager();

        $filter_number = $request->get('filter_number');
        $filter_code = $request->get('filter_code');
        $filter_method = $request->get('filter_method');
        $filter_payment_date_from = $request->get('filter_payment_date_from');
        $filter_payment_date_to = $request->get('filter_payment_date_to');

        if ($request->getMethod() == 'POST' && $filter_number == 'null' && $filter_code == 'null' &&
            $filter_method == 'null' && $filter_payment_date_from == 'null' && $filter_payment_date_to == 'null') {
            $message = 'Debe llenar al menos un campo para filtrar.';
            $this->get('session')->getFlashBag()->add('message_error_local', $message);
            return $this->redirect($this->generateUrl('mycp_list_payments_pending_ownership'));
        }

        $paginator = $this->get('ideup.simple_paginator');
        $paginator->setItemsPerPage($items_per_page);
        $payments = $paginator->paginate($em->getRepository('mycpBundle:pendingPayown')->findAllByFilters($filter_number, $filter_code, $filter_method, $filter_payment_date_from, $filter_payment_date_to))->getResult();
        $page = 1;
        if (isset($_GET['page']))
            $page = $_GET['page'];
        return $this->render('mycpBundle:pendingOwn:list.html.twig', array(
                'list' => $payments,
                'items_per_page' => $items_per_page,
                'total_items' => $paginator->getTotalItems(),
                'current_page' => $page,
                'filter_number' => $filter_number,
                'filter_code' => $filter_code,
                'filter_method' => $filter_method,
                'filter_payment_date_from' => $filter_payment_date_from,
                'filter_payment_date_to' => $filter_payment_date_to
            ));
    }

    /**
     * @param $id
     * @param Request $request
     * @return mixed
     */
    public function detailAction($id, Request $request){
        $em = $this->getDoctrine()->getManager();
        $pending_payment = $em->getRepository('mycpBundle:pendingPayown')->find($id);
        $id_booking = $pending_payment->getCancelId()->getBooking()->getBookingId();

        $payment = $em->getRepository('mycpBundle:payment')->findOneBy(array("booking" => $id_booking));
        $user = $em->getRepository('mycpBundle:userTourist')->findOneBy(array('user_tourist_user' => $pending_payment->getCancelId()->getBooking()->getBookingUserId()));
        $reservations = $em->getRepository('mycpBundle:ownershipReservation')->findBy(array('own_res_reservation_booking' => $id_booking), array('own_res_gen_res_id' => 'ASC'));

        $form = $this->createForm(new cancelPaymentType());

        return $this->render('mycpBundle:pendingOwn:detail.html.twig', array(
                'user' => $user,
                'form'=>$form->createView(),
                'reservations' => $reservations,
                'payment' => $payment,
                'cancel_payment'=>$em->getRepository('mycpBundle:cancelPayment')->findBy(array('booking' => $id_booking))
            ));
    }

    /**
     * @param $id
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    function editAction($id, Request $request) {
        $service_security = $this->get('Secure');
        $service_security->verifyAccess();
        $em = $this->getDoctrine()->getManager();
        $payment = $em->getRepository('mycpBundle:pendingPayown')->find($id);
        $form = $this->createForm(new pendingPayownershipType(), $payment);

        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);
            if ($form->isValid()) {

                $em->persist($payment);
                $em->flush();

                $message = 'Pago actualizado satisfactoriamente.';
                $this->get('session')->getFlashBag()->add('message_ok', $message);

                return $this->redirect($this->generateUrl('mycp_list_payments_pending_ownership'));
            }
        }

        return $this->render('mycpBundle:pendingOwn:new.html.twig', array(
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
               $pay= $em->getRepository('mycpBundle:pendingPayown')->find($item);
               $pay->setType($em->getRepository('mycpBundle:nomenclator')->findOneBy(array("nom_name" => 'payment_successful')));
               $em->persist($pay);
           }
           $em->flush();
           return new JsonResponse(['success' => true, 'message' =>'Se ha adicionado el pago satisfactoriamente']);
       }
        return new JsonResponse(['success' => false, 'message' =>'Debe de seleccionar algún pago']);
    }
}