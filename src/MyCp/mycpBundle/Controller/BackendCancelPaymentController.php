<?php

namespace MyCp\mycpBundle\Controller;


use MyCp\mycpBundle\Form\cancelPaymentType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


class BackendCancelPaymentController extends Controller {


    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function exportAction(Request $request) {
        try {
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

            $items = $em->getRepository('mycpBundle:cancelPayment')->findAllByFilters($filter_number, $filter_code, $filter_method, $filter_name,$filter_payment_date_from, $filter_payment_date_to,$filter_own)->getResult();

            $date = new \DateTime();
            if(count($items)) {
                $exporter = $this->get("mycp.service.export_to_excel");
                return $exporter->exportCancelPayment($items, $date);
            }
            else {
                $message = 'No hay datos para llenar el Excel a descargar.';
                $this->get('session')->getFlashBag()->add('message_ok', $message);
                return $this->redirect($this->generateUrl("mycp_list_cancel_payment"));
            }
        }
        catch (\Exception $e) {
            $message = 'Ha ocurrido un error. Por favor, introduzca correctamente los valores para filtrar.';
            $this->get('session')->getFlashBag()->add('message_error_main', $message);

            return $this->redirect($this->generateUrl("mycp_list_cancel_payment"));
        }
    }
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

            if(count($pay_own) && count($pay_tourist)){
                return $this->render('mycpBundle:cancelPayment:pay_detail_tourist_own.html.twig',array(
                        'own'=>$em->getRepository("mycpBundle:pendingPayown")->findOneBy(array("cancel_id" => $post['selected'])),
                        'tourist'=>$em->getRepository("mycpBundle:pendingPaytourist")->findOneBy(array("cancel_id" => $post['selected']))
                ));
            }
            else if(count($pay_own))
                return $this->render('mycpBundle:cancelPayment:pay_detail_own.html.twig',array(
                        'pays'=>$pay_own
                    ));
            else if(count($pay_tourist))
                return $this->render('mycpBundle:cancelPayment:pay_detail_tourist.html.twig',array(
                        'pays'=>$pay_tourist
                    ));
            else
                return $this->render('mycpBundle:cancelPayment:pay_detail_tourist.html.twig',array(
                        'pays'=>array()
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
        /*$service_security = $this->get('Secure');
        $service_security->verifyAccess();*/
        $em = $this->getDoctrine()->getManager();
        $obj = $em->getRepository('mycpBundle:cancelPayment')->find($id);
        $form = $this->createForm(new cancelPaymentType(), $obj);

        $user_tourist= $em->getRepository('mycpBundle:userTourist')->findBy(array('user_tourist_user' => $obj->getBooking()->getBookingUserId()));
        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $post = $request->get("mycp_mycpbundle_cancelpayment");
                $giveTourist = (isset($post["give_tourist"]) && $post["give_tourist"] == "1") ? true: false;

                $obj->setGiveTourist($giveTourist);
                $em->persist($obj);
                $em->flush();

                $message = 'Pago actualizado satisfactoriamente.';
                $this->get('session')->getFlashBag()->add('message_ok', $message);

                return $this->redirect($this->generateUrl('mycp_list_cancel_payment'));
            }
        }

        return $this->render('mycpBundle:cancelPayment:new.html.twig', array(
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
        $cancel = $em->getRepository('mycpBundle:cancelPayment')->find($id);
        $id_booking = $cancel->getBooking()->getBookingId();

        $payment = $em->getRepository('mycpBundle:payment')->findOneBy(array("booking" => $id_booking));
        $user = $em->getRepository('mycpBundle:userTourist')->findOneBy(array('user_tourist_user' => $cancel->getBooking()->getBookingUserId()));

        $form = $this->createForm(new cancelPaymentType());

        return $this->render('mycpBundle:cancelPayment:detail.html.twig', array(
                'user' => $user,
                'form'=>$form->createView(),
                'reservations' => $cancel->getOwnreservations(),
                'payment' => $payment,
                'cancel_payment'=>$em->getRepository('mycpBundle:cancelPayment')->findBy(array('booking' => $id_booking))
            ));
    }

    /**
     * @param $idcancel
     * @return Response
     */
    public function payAction($idcancel){
        return $this->render('mycpBundle:cancelPayment:pay.html.twig', array(
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
                return $this->render('mycpBundle:cancelPayment:pay_detail_own_full.html.twig',array(
                        'pays'=>$pay_own,
                        'idcancel'=>$post['selected']
                    ));
            else if(count($pay_tourist))
                return $this->render('mycpBundle:cancelPayment:pay_detail_tourist_full.html.twig',array(
                        'pays'=>$pay_tourist,
                        'idcancel'=>$post['selected']
                    ));
            else
                return $this->render('mycpBundle:cancelPayment:pay_detail_tourist_full.html.twig',array(
                        'pays'=>array()
                    ));
        }
    }
    public function submitAction($idcancel){
        $em = $this->getDoctrine()->getManager();
        $templatingService = $this->container->get('templating');
        $emailService = $this->container->get('mycp.service.email_manager');
        $service_time = $this->get('time');

        $cancelPayment = $em->getRepository('mycpBundle:cancelPayment')->find($idcancel);
        $user_tourist= $em->getRepository('mycpBundle:userTourist')->findOneBy(array('user_tourist_user' => $cancelPayment->getBooking()->getBookingUserId()));
        $rooms = array();
        $total_nights = array();

        $pay_own = $em->getRepository("mycpBundle:pendingPayown")->findOneBy(array("cancel_id" => $idcancel));
        $pay_tourist= $em->getRepository("mycpBundle:pendingPaytourist")->findOneBy(array("cancel_id" => $idcancel));
        $payment = $em->getRepository('mycpBundle:payment')->findOneBy(array("booking" => $cancelPayment->getBooking()->getBookingId()));

        $reservations_ids=$cancelPayment->getOwnreservations();
        if(!empty($pay_own)){
            $array_id_ownership=array();
            foreach($reservations_ids as $genResId){
                $ownershipReservation = $em->getRepository('mycpBundle:ownershipReservation')->find($genResId->getOwnResId());
                $price=$this->calculatePriceOwn($ownershipReservation->getOwnResReservationFromDate(),$ownershipReservation->getOwnResReservationToDate(),$ownershipReservation->getOwnResTotalInSite(),$ownershipReservation->getOwnResGenResId()->getGenResOwnId()->getOwnCommissionPercent());
                if (!array_key_exists ($ownershipReservation->getOwnResGenResId()->getGenResOwnId()->getOwnId(), $array_id_ownership)){
                    //Adiciono el id de la casa al arreglo de casas
                    $array_id_ownership[$ownershipReservation->getOwnResGenResId()->getGenResOwnId()->getOwnId()] = array('idown'=>$ownershipReservation->getOwnResGenResId()->getGenResOwnId()->getOwnId(),'price'=>$price,'ownershipReservations'=>array($ownershipReservation),'arrival_date'=>$ownershipReservation->getOwnResReservationFromDate());
                }
                else{
                    $array_id_ownership[$ownershipReservation->getOwnResGenResId()->getGenResOwnId()->getOwnId()]['price'] = $array_id_ownership[$ownershipReservation->getOwnResGenResId()->getGenResOwnId()->getOwnId()]['price']+$price;
                    $array_id_ownership[$ownershipReservation->getOwnResGenResId()->getGenResOwnId()->getOwnId()]['ownershipReservations'][] = $ownershipReservation;
                }
            }
            foreach($array_id_ownership as $item){
                $ownership = $em->getRepository('mycpBundle:ownership')->find($item['idown']);
                //Notifico un pago de tipo propietario
                $body = $templatingService->renderResponse('mycpBundle:pendingOwn:mail.html.twig', array(
                        'user_locale'=>'es',
                        'ownership'=>$ownership,
                        'ownershipReservations'=>$item['ownershipReservations'],
                        'price'=>$item['price'],
                        'reason'=>$cancelPayment->getReason(),
                        'payment_date'=>$pay_own->getPaymentDate()
                    ));
                 $emailService->sendEmail(array("reservation@mycasaparticular.com","sarahy_amor@yahoo.com","andy.cabrera08@gmail.com"),"Pago Pendiente a Propietario:",$body,"no-reply@mycasaparticular.com");
                //$emailService->sendEmail(array("damian.flores@mycasaparticular.com","andy.cabrera08@gmail.com"),"Pago Pendiente a Propietario:",$body,"no-reply@mycasaparticular.com");
            }

        }
        if(!empty($pay_tourist)){
            $array_id_ownership=array();
            foreach($reservations_ids as $genResId){
                $ownershipReservation = $em->getRepository('mycpBundle:ownershipReservation')->find($genResId->getOwnResId());
                if (!in_array ($ownershipReservation->getOwnResGenResId()->getGenResOwnId()->getOwnId(), $array_id_ownership)){
                    //Adiciono el id de la casa al arreglo de casas
                    $array_id_ownership[] = $ownershipReservation->getOwnResGenResId()->getGenResOwnId()->getOwnId();
                    //Adiciono al arreglo de reservaciones
                    $ownershipReservations[]=$ownershipReservation;
                    //Adiciono las rooms de esa casa
                    array_push($rooms, $em->getRepository('mycpBundle:room')->find($ownershipReservation->getOwnResSelectedRoomId()));
                    $temp_total_nights = 0;
                    $nights = $service_time->nights($ownershipReservation->getOwnResReservationFromDate()->getTimestamp(), $ownershipReservation->getOwnResReservationToDate()->getTimestamp());
                    $temp_total_nights += $nights;
                    array_push($total_nights, $temp_total_nights);
                }
            }

            //Notifico un pago de tipo turista
            $pay_cost=$cancelPayment->getBooking()->getBookingPrepay();
            $body = $templatingService->renderResponse('mycpBundle:pendingTourist:mail.html.twig', array(
                    'user_locale'=>'es',
                    'user_tourist' => $user_tourist,
                    'payment'=>$payment,
                    'pending_tourist'=>$pay_tourist,
                    'pay_cost'=>$pay_cost,
                    'ownershipReservations'=>$ownershipReservations,
                    'rooms'=>$rooms
                ));
            $emailService->sendEmail(array("reservation@mycasaparticular.com","sarahy_amor@yahoo.com","andy.cabrera08@gmail.com"),"Pago Pendiente a Turista:",$body,"no-reply@mycasaparticular.com");
            //$emailService->sendEmail(array("damian.flores@mycasaparticular.com","andy.cabrera08@gmail.com"),"Pago Pendiente a Turista:",$body,"no-reply@mycasaparticular.com");

        }
        $cancelPayment->setSubmitEmail(true);
        $em->persist($cancelPayment);
        $em->flush();
        return $this->redirect($this->generateUrl('mycp_list_cancel_payment'));
    }

    /**
     * @param $reservations_ids
     * @return array
     */
    public function calculateTourist($reservations_ids,$sum_tax){
        $service_time = $this->get('time');
        $price=0;
        $fixed=0;
        if(count($reservations_ids)){
            foreach($reservations_ids as $genResId){
                $ownershipReservation=$this->em->getRepository('mycpBundle:ownershipReservation')->find($genResId);
                $generalReservation = $ownershipReservation->getOwnResGenResId();
                if($fixed==0)
                    $fixed=$generalReservation->getServiceFee()->getFixedFee();
                $price =$price+ $this->em->getRepository('mycpBundle:ownershipReservation')->cancelReservationByTourist($this->em->getRepository('mycpBundle:ownershipReservation')->find($genResId),$service_time,$sum_tax,$service_time);
            }
        }
        return array('price'=>$price,'fixed'=>$fixed);

    }
    /**
     * @param $from
     * @param $to
     * @param $price_total_in_site
     * @param $commission_percent
     * @return float
     */
    public function calculatePriceOwn($from,$to,$price_total_in_site,$commission_percent){
        $service_time = $this->get('time');
        $day=$service_time->diffInDays($to->format("Y-m-d"), $from->format("Y-m-d"));

        if($day==1 || $day ==2){
            $price=($price_total_in_site/$day)*(1-$commission_percent/100)*0.5;
            return $price;
        }
        else{
            $price=($price_total_in_site/$day)*(1-$commission_percent/100);
            return $price;
        }
    }
}