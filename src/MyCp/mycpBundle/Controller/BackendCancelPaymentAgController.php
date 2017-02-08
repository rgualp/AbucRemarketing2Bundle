<?php

namespace MyCp\mycpBundle\Controller;


use MyCp\PartnerBundle\Form\paCancelPaymentType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use MyCp\mycpBundle\Form\cancelPaymentType;
use MyCp\mycpBundle\Entity\cancelPayment;



class BackendCancelPaymentAgController extends Controller {


    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function exportAction(Request $request) {
        try {
//            $service_security = $this->get('Secure');
//            $service_security->verifyAccess();
            $em = $this->getDoctrine()->getManager();

            $filter_number = $request->get('filter_number');
            $filter_code = $request->get('filter_code');
            $filter_method = $request->get('filter_method');
            $filter_name = $request->get('filter_name');
            $filter_payment_date_from = $request->get('filter_payment_date_from');
            $filter_payment_date_to = $request->get('filter_payment_date_to');
            $filter_own = $request->get('filter_own');

            $items = $em->getRepository('PartnerBundle:paCancelPayment')->findAllByFilters($filter_number, $filter_code, $filter_method, $filter_name,$filter_payment_date_from, $filter_payment_date_to,$filter_own)->getResult();

            $date = new \DateTime();
            if(count($items)) {
                $exporter = $this->get("mycp.service.export_to_excel");
                return $exporter->exportCancelPayment($items, $date);
            }
            else {
                $message = 'No hay datos para llenar el Excel a descargar.';
                $this->get('session')->getFlashBag()->add('message_ok', $message);
                return $this->redirect($this->generateUrl("mycp_list_cancel_payment_ag"));
            }
        }
        catch (\Exception $e) {
            $message = 'Ha ocurrido un error. Por favor, introduzca correctamente los valores para filtrar.';
            $this->get('session')->getFlashBag()->add('message_error_main', $message);

            return $this->redirect($this->generateUrl("mycp_list_cancel_payment_ag"));
        }
    }
    /**
     * @param $items_per_page
     * @param Request $request
     * @return Response
     */
    function listAction($items_per_page, Request $request) {
//        $service_security = $this->get('Secure');
//        $service_security->verifyAccess();
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
            return $this->redirect($this->generateUrl('mycp_list_cancel_payment_ag'));
        }
        $paginator = $this->get('ideup.simple_paginator');
        $paginator->setItemsPerPage($items_per_page);
        $results = $paginator->paginate($em->getRepository('PartnerBundle:paCancelPayment')->findAllByFilters($filter_number, $filter_code, $filter_method, $filter_name,$filter_payment_date_from, $filter_payment_date_to,$filter_own))->getResult();
        $page = 1;
        if (isset($_GET['page']))
            $page = $_GET['page'];
        return $this->render('mycpBundle:cancelPaymentAgency:list.html.twig', array(
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
            $paAccommodation = $em->getRepository("PartnerBundle:paPendingPaymentAccommodation")->findBy(array("cancelPayment" => $post['selected']));
            $payAgency = $em->getRepository("PartnerBundle:paPendingPaymentAgency")->findBy(array("cancelPayment" => $post['selected']));

            if(count($paAccommodation))
                return $this->render('mycpBundle:cancelPaymentAgency:pay_detail_own.html.twig',array(
                        'pays'=>$paAccommodation
                    ));
            else if(count($payAgency))
                return $this->render('mycpBundle:cancelPaymentAgency:pay_detail_agency.html.twig',array(
                        'pays'=>$payAgency
                    ));
            else
                return $this->render('mycpBundle:cancelPaymentAgency:pay_detail_agency.html.twig',array(
                        'pays'=>array()
                    ));
        }
    }

    /**
     * @param $id
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    function editAction($id, Request $request) {
//        $service_security = $this->get('Secure');
//        $service_security->verifyAccess();
        $em = $this->getDoctrine()->getManager();
        $obj = $em->getRepository('PartnerBundle:paCancelPayment')->find($id);
        $form = $this->createForm(new cancelPaymentType(), $obj);

        $user_tourist= $em->getRepository('mycpBundle:userTourist')->findBy(array('user_tourist_user' => $obj->getBooking()->getBookingUserId()));
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

        return $this->render('mycpBundle:cancelPaymentAgency:new.html.twig', array(
                'form' => $form->createView(), 'edit_payment' => $id, 'obj' => $obj,'user_tourist'=>$user_tourist[0]
            ));
    }

    /**
     * @param $id
     * @param Request $request
     * @return mixed
     */
    public function detailAction($id, Request $request){
        $em = $this->getDoctrine()->getManager();
        $cancel = $em->getRepository('PartnerBundle:paCancelPayment')->find($id);
        $id_booking = $cancel->getBooking()->getBookingId();

        $payment = $em->getRepository('mycpBundle:payment')->findOneBy(array("booking" => $id_booking));
        $tourOperator = $em->getRepository('PartnerBundle:paTourOperator')->findOneBy(array('tourOperator' => $cancel->getBooking()->getBookingUserId()));

        $form = $this->createForm(new paCancelPaymentType());

        return $this->render('mycpBundle:cancelPaymentAgency:detail.html.twig', array(
                'user' => $tourOperator->getTourOperator(),
                'form'=>$form->createView(),
                'reservations' => $cancel->getOwnreservations(),
                'payment' => $payment,
                'idCancel' => $id,
                'cancel_payment'=>$em->getRepository('PartnerBundle:paCancelPayment')->findBy(array('booking' => $id_booking))
            ));
    }

    /**
     * @param $idcancel
     * @return Response
     */
    public function payAction($idcancel){
        return $this->render('mycpBundle:cancelPaymentAgency:pay.html.twig', array(
                'idcancel'=>$idcancel
        ));
    }

    /**
     * @param $post
     * @return Response
     */
    public function getPaymentByCancelAction($post) {
        if(isset($post['selected']) && $post['selected'] != ""){
            $em = $this->getDoctrine()->getManager();
            $pay_own = $em->getRepository("mycpBundle:pendingPayown")->findBy(array("cancel_id" => $post['selected']));
            $pay_tourist = $em->getRepository("mycpBundle:pendingPaytourist")->findBy(array("cancel_id" => $post['selected']));

            if(count($pay_own))
                return $this->render('mycpBundle:cancelPaymentAgency:pay_detail_own_full.html.twig',array(
                        'pays'=>$pay_own,
                        'idcancel'=>$post['selected']
                    ));
            else if(count($pay_tourist))
                return $this->render('mycpBundle:cancelPaymentAgency:pay_detail_tourist_full.html.twig',array(
                        'pays'=>$pay_tourist,
                        'idcancel'=>$post['selected']
                    ));
            else
                return $this->render('mycpBundle:cancelPaymentAgency:pay_detail_tourist_full.html.twig',array(
                        'pays'=>array()
                    ));
        }
    }
}