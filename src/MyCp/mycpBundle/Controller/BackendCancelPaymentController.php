<?php

namespace MyCp\mycpBundle\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use MyCp\mycpBundle\Form\cancelPaymentType;
use MyCp\mycpBundle\Entity\cancelPayment;



class BackendCancelPaymentController extends Controller {

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
        $filter_name = $request->get('filter_name');
        $filter_payment_date_from = $request->get('filter_payment_date_from');
        $filter_payment_date_to = $request->get('filter_payment_date_to');
        $filter_own = $request->get('filter_own');


        if ($request->getMethod() == 'POST' && $filter_number == 'null' && $filter_code == 'null' &&
            $filter_method == 'null' && $filter_name == 'null' && $filter_payment_date_from == 'null' && $filter_payment_date_to == 'null' && $filter_own == 'null') {
            $message = 'Debe llenar al menos un campo para filtrar.';
            $this->get('session')->getFlashBag()->add('message_error_local', $message);
            return $this->redirect($this->generateUrl('mycp_list_cancel_payment'));
        }
        $paginator = $this->get('ideup.simple_paginator');
        $paginator->setItemsPerPage($items_per_page);
        $results = $paginator->paginate($em->getRepository('mycpBundle:cancelPayment')->findAllByFilters($filter_number, $filter_code, $filter_method, $filter_name,$filter_payment_date_from, $filter_payment_date_to,$filter_own))->getResult();
        $page = 1;
        if (isset($_GET['page']))
            $page = $_GET['page'];
        return $this->render('mycpBundle:cancelPayment:list.html.twig', array(
                'list' => $results,
                'items_per_page' => $items_per_page,
                'total_items' => $paginator->getTotalItems(),
                'current_page' => $page,
                'filter_number' => $filter_number,
                'filter_code' => $filter_code,
                'filter_method' => $filter_method,
                'filter_name' => $filter_name,
                'filter_payment_date_from' => $filter_payment_date_from,
                'filter_payment_date_to' => $filter_payment_date_to,
                'filter_own'=>$filter_own
            ));
    }

    /**
     * @param $post
     * @return Response
     */
    public function getPaymentAction($post) {
        if(isset($post['selected']) && $post['selected'] != ""){
            $em = $this->getDoctrine()->getManager();
            $pay_own = $em->getRepository("mycpBundle:pendingPayown")->findBy(array("cancel_id" => $post['selected']));
            $pay_tourist = $em->getRepository("mycpBundle:pendingPaytourist")->findBy(array("cancel_id" => $post['selected']));

            if(count($pay_own))
                return $this->render('mycpBundle:cancelPayment:pay_detail_own.html.twig',array(
                        'pay'=>$pay_tourist[0]
                    ));
            if(count($pay_tourist))
                return $this->render('mycpBundle:cancelPayment:pay_detail_tourist.html.twig',array(
                        'pay'=>$pay_tourist[0]
                    ));
        }
    }

    /**
     * @param $selectedValue
     * @return Response
     */
    public function getNomenclatorListAction($selectedValue)
    {
        $em = $this->getDoctrine()->getManager();
        $nomenclators = $em->getRepository('mycpBundle:cancelType')->findAll();
        return $this->render('mycpBundle:cancelPayment:listNomenclators.html.twig', array('nomenclators' => $nomenclators
            ,'selected'=>$selectedValue));
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
        $obj = $em->getRepository('mycpBundle:cancelPayment')->find($id);
        $form = $this->createForm(new cancelPaymentType(), $obj);

        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);
            if ($form->isValid()) {

                $em->persist($obj);
                $em->flush();

                $message = 'Pago actualizado satisfactoriamente.';
                $this->get('session')->getFlashBag()->add('message_ok', $message);

                return $this->redirect($this->generateUrl('mycp_list_cancel_payment'));
            }
        }

        return $this->render('mycpBundle:cancelPayment:new.html.twig', array(
                'form' => $form->createView(), 'edit_payment' => $id, 'obj' => $obj
            ));
    }

    /**
     * @param $id
     * @param Request $request
     * @return mixed
     */
    public function detailAction($id, Request $request){
        $em = $this->getDoctrine()->getManager();
        $cancel = $em->getRepository('mycpBundle:cancelPayment')->find($id);
        $id_booking = $cancel->getBooking()->getBookingId();

        $payment = $em->getRepository('mycpBundle:payment')->findOneBy(array("booking" => $id_booking));
        $user = $em->getRepository('mycpBundle:userTourist')->findOneBy(array('user_tourist_user' => $cancel->getBooking()->getBookingUserId()));
        $reservations = $em->getRepository('mycpBundle:ownershipReservation')->findBy(array('own_res_reservation_booking' => $id_booking), array('own_res_gen_res_id' => 'ASC'));

        $form = $this->createForm(new cancelPaymentType());

        return $this->render('mycpBundle:cancelPayment:detail.html.twig', array(
                'user' => $user,
                'form'=>$form->createView(),
                'reservations' => $reservations,
                'payment' => $payment,
                'cancel_payment'=>$em->getRepository('mycpBundle:cancelPayment')->findBy(array('booking' => $id_booking))
            ));
    }
}