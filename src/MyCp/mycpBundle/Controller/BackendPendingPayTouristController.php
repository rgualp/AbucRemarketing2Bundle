<?php

namespace MyCp\mycpBundle\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use MyCp\mycpBundle\Form\pendingPaytouristType;
use MyCp\mycpBundle\Form\cancelPaymentType;
use MyCp\mycpBundle\Entity\pendingPaytourist;



class BackendPendingPayTouristController extends Controller {

    /**
     * @param $items_per_page
     * @param Request $request
     * @return Response
     */
    function listAction($items_per_page, Request $request) {
        $service_security = $this->get('Secure');
        $service_security->verifyAccess();
        $em = $this->getDoctrine()->getManager();

        $paginator = $this->get('ideup.simple_paginator');
        $paginator->setItemsPerPage($items_per_page);
        $payments = $paginator->paginate($em->getRepository('mycpBundle:pendingPaytourist')->findAllByFilters())->getResult();
        $page = 1;
        if (isset($_GET['page']))
            $page = $_GET['page'];
        return $this->render('mycpBundle:pendingTourist:list.html.twig', array(
                'list' => $payments,
                'items_per_page' => $items_per_page,
                'total_items' => $paginator->getTotalItems(),
                'current_page' => $page
            ));
    }


    /**
     * @param $id
     * @param Request $request
     * @return mixed
     */
    public function detailAction($id, Request $request){
        $em = $this->getDoctrine()->getManager();
        $pending_payment = $em->getRepository('mycpBundle:pendingPaytourist')->find($id);
        $id_booking = $pending_payment->getCancelId()->getBooking()->getBookingId();

        $payment = $em->getRepository('mycpBundle:payment')->findOneBy(array("booking" => $id_booking));
        $user = $em->getRepository('mycpBundle:userTourist')->findOneBy(array('user_tourist_user' => $pending_payment->getCancelId()->getBooking()->getBookingUserId()));
        $reservations = $em->getRepository('mycpBundle:ownershipReservation')->findBy(array('own_res_reservation_booking' => $id_booking), array('own_res_gen_res_id' => 'ASC'));

        $form = $this->createForm(new cancelPaymentType());

        return $this->render('mycpBundle:pendingTourist:detail.html.twig', array(
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
        $payment = $em->getRepository('mycpBundle:pendingPaytourist')->find($id);
        $form = $this->createForm(new pendingPaytouristType(), $payment);

        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);
            if ($form->isValid()) {

                $em->persist($payment);
                $em->flush();

                $message = 'Pago actualizado satisfactoriamente.';
                $this->get('session')->getFlashBag()->add('message_ok', $message);

                return $this->redirect($this->generateUrl('mycp_list_payments_pending_tourist'));
            }
        }

        return $this->render('mycpBundle:pendingTourist:new.html.twig', array(
                'form' => $form->createView(), 'edit_payment' => $id, 'payment' => $payment
            ));
    }

    /**
     * @param $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    function deleteAction($id) {
        $service_security = $this->get('Secure');
        $service_security->verifyAccess();

        $em = $this->getDoctrine()->getManager();
        $payment= $em->getRepository('mycpBundle:pendingPaytourist')->find($id);

        if ($payment)
            $em->remove($payment);
        $em->flush();
        $message = 'Pago eliminado satisfactoriamente.';
        $this->get('session')->getFlashBag()->add('message_ok', $message);

        return $this->redirect($this->generateUrl('mycp_list_payments_pending_tourist'));
    }
}