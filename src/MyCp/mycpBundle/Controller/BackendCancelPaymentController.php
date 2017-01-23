<?php

namespace MyCp\mycpBundle\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use MyCp\mycpBundle\Form\pendingPaytouristType;
use MyCp\mycpBundle\Form\cancelPaymentType;
use MyCp\mycpBundle\Entity\pendingPaytourist;



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
        $filter_payment_date_from = $request->get('filter_payment_date_from');
        $filter_payment_date_to = $request->get('filter_payment_date_to');

        if ($request->getMethod() == 'POST' && $filter_number == 'null' && $filter_code == 'null' &&
            $filter_method == 'null' && $filter_payment_date_from == 'null' && $filter_payment_date_to == 'null') {
            $message = 'Debe llenar al menos un campo para filtrar.';
            $this->get('session')->getFlashBag()->add('message_error_local', $message);
            return $this->redirect($this->generateUrl('mycp_list_payments_pending_tourist'));
        }
        $paginator = $this->get('ideup.simple_paginator');
        $paginator->setItemsPerPage($items_per_page);
        $results = $paginator->paginate($em->getRepository('mycpBundle:cancelPayment')->findAllByFilters($filter_number, $filter_code, $filter_method, $filter_payment_date_from, $filter_payment_date_to))->getResult();
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
                'filter_payment_date_from' => $filter_payment_date_from,
                'filter_payment_date_to' => $filter_payment_date_to
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
}