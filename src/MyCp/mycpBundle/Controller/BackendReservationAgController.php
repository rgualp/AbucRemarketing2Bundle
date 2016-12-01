<?php

namespace MyCp\mycpBundle\Controller;

use Abuc\RemarketingBundle\Event\JobEvent;
use MyCp\mycpBundle\Entity\booking;
use MyCp\mycpBundle\Entity\payment;
use MyCp\mycpBundle\Helpers\DataBaseTables;
use MyCp\mycpBundle\Helpers\Operations;
use MyCp\mycpBundle\Helpers\OwnershipStatuses;
use MyCp\mycpBundle\Helpers\Reservation;
use MyCp\mycpBundle\JobData\GeneralReservationJobData;
use MyCp\PartnerBundle\Entity\paReservation;
use MyCp\PartnerBundle\Entity\paReservationDetail;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use MyCp\mycpBundle\Entity\ownershipReservation;
use MyCp\mycpBundle\Entity\generalReservation;
use MyCp\mycpBundle\Entity\log;
use Symfony\Component\Validator\Constraints\Date;
use Symfony\Component\Validator\Constraints\NotBlank;
use MyCp\mycpBundle\Form\reservationType;
use MyCp\mycpBundle\Helpers\BackendModuleName;
use MyCp\mycpBundle\Helpers\VoucherHelper;

class BackendReservationAgController extends Controller {


    /**
     * @param $items_per_page
     * @param Request $request
     * @inheritdoc  este metodo es para el listado de cliente agencias
     * @return Response
     */
    public function listReservationsByUsersAGAction($items_per_page, Request $request) {
        /*$service_security = $this->get('Secure');
        $service_security->verifyAccess();*/
        $page = 1;

        $filter_name = $request->get('filter_name');
        $filter_agencia = $request->get('filter_agencia');
        $filter_status = $request->get('filter_status');
        $filter_accommodation = $request->get('filter_accommodation');
        $filter_destination = $request->get('filter_destination');
        $filter_range_from = $request->get('filter_range_from');
        $filter_range_to = $request->get('filter_range_to');


        if ($request->getMethod() == 'POST' &&  $filter_name == 'null'&&  $filter_agencia == 'null' && $filter_status == 'null' && $filter_accommodation == 'null' && $filter_agencia == 'null' &&
            $filter_destination == 'null' && $filter_range_from == "null"  && $filter_range_to == "null") {
            $message = 'Debe llenar al menos un campo para filtrar o seleccionar un criterio de ordenación.';
            $this->get('session')->getFlashBag()->add('message_error_local', $message);
            return $this->redirect($this->generateUrl('mycp_list_reservations_byuser_ag'));
        }
        if ($filter_name == 'null')
            $filter_name = '';

        if ($filter_agencia == 'null')
            $filter_agencia = '';

        if ($filter_status == 'null')
            $filter_status = '';
        if ($filter_accommodation == 'null')
            $filter_accommodation = '';
        if ($filter_destination == 'null')
            $filter_destination = '';
        if ($filter_range_from == 'null')
            $filter_range_from = '';
        if ($filter_range_to == 'null')
            $filter_range_to = '';

        if (isset($_GET['page']))
            $page = $_GET['page'];

        $em = $this->getDoctrine()->getManager();
        $paginator = $this->get('ideup.simple_paginator');
        $paginator->setItemsPerPage($items_per_page);
        $reservations = $paginator->paginate($em->getRepository('mycpBundle:generalReservation')->getByUsersAg($filter_name, $filter_agencia, $filter_status, $filter_accommodation, $filter_destination, $filter_range_from, $filter_range_to))->getResult();//$paginator->paginate($em->getRepository('mycpBundle:generalReservation')
        //->getUsers($filter_user_name, $filter_user_email, $filter_user_city, $filter_user_country, $sort_by))->getResult();

//        $service_log = $this->get('log');
//        $service_log->saveLog('Visit', BackendModuleName::MODULE_RESERVATION);

        return $this->render('mycpBundle:reservation:list_byclient_ag.html.twig', array(
            'reservations' => $reservations,
            'items_per_page' => $items_per_page,
            'current_page' => $page,
            'total_items' => $paginator->getTotalItems(),
            'filter_name' => $filter_name,
            'filter_agencia' => $filter_agencia,

            'filter_status' => $filter_status,

            'filter_accommodation' => $filter_accommodation,
            'filter_destination' => $filter_destination,
            'filter_range_from' => $filter_range_from,
            'filter_range_to' => $filter_range_to
        ));
    }

    public function list_reservations_bookingAction($items_per_page, Request $request) {
        $service_security = $this->get('Secure');
        $service_security->verifyAccess();

        $filter_booking_number = $request->get('filter_booking_number');
        $filter_date_booking = $request->get('filter_date_booking');
        $filter_user_booking = 'null';
        $filter_agencia_booking = $request->get('filter_user_booking');
        $filter_arrive_date_booking = $request->get('filter_arrive_date_booking');
        $filter_reservation = $request->get("filter_reservation");
        $filter_ownership = $request->get("filter_ownership");
        $filter_currency = $request->get("filter_currency");

        if ($request->getMethod() == 'POST' && $filter_booking_number == 'null' && $filter_date_booking == 'null' && $filter_agencia_booking == 'null' && $filter_arrive_date_booking == 'null' && $filter_reservation == 'null' && $filter_ownership == 'null' && $filter_currency == 'null') {
            $message = 'Debe llenar al menos un campo para filtrar.';
            $this->get('session')->getFlashBag()->add('message_error_local', $message);
            return $this->redirect($this->generateUrl('mycp_list_reservations_booking'));
        }

        if ($filter_booking_number == 'null')
            $filter_booking_number = '';
        if ($filter_date_booking == 'null')
            $filter_date_booking = '';
        if ($filter_user_booking == 'null')
            $filter_user_booking = '';
        if ($filter_agencia_booking == 'null')
            $filter_agencia_booking = '';
        if ($filter_arrive_date_booking == 'null')
            $filter_arrive_date_booking = '';
        if ($filter_reservation == 'null')
            $filter_reservation = '';
        if ($filter_currency == 'null')
            $filter_currency = '';

        if ($filter_ownership == 'null')
            $filter_ownership = '';

        $page = 1;
        if (isset($_GET['page']))
            $page = $_GET['page'];
        $em = $this->getDoctrine()->getManager();
        $paginator = $this->get('ideup.simple_paginator');
        $paginator->setItemsPerPage($items_per_page);

        $bookings = $paginator->paginate($em->getRepository('mycpBundle:generalReservation')
                                ->getAllBookings($filter_booking_number, $filter_date_booking, $filter_user_booking, $filter_arrive_date_booking, $filter_reservation, $filter_ownership, $filter_currency, $filter_agencia_booking, true))->getResult();
//        $service_log = $this->get('log');
//        $service_log->saveLog('Visit', BackendModuleName::MODULE_RESERVATION);

        $filter_date_booking = str_replace('_', '/', $filter_date_booking);
        $filter_arrive_date_booking = str_replace('_', '/', $filter_arrive_date_booking);

        return $this->render('mycpBundle:reservation:list_booking_ag.html.twig', array(
                    "bookings" => $bookings,
                    'items_per_page' => $items_per_page,
                    'current_page' => $page,
                    'filter_booking_number' => $filter_booking_number,
                    'filter_date_booking' => $filter_date_booking,
                    'filter_user_booking' => $filter_agencia_booking,
                    'filter_arrive_date_booking' => $filter_arrive_date_booking,
                    'filter_reservation' => $filter_reservation,
                    'filter_ownership' => $filter_ownership,
                    'filter_currency' => $filter_currency,
                    'total_items' => $paginator->getTotalItems(),
        ));
    }

    public function details_bookingAction($id_booking) {
        $service_security = $this->get('Secure');
        $service_security->verifyAccess();
        $em = $this->getDoctrine()->getManager();
        $payment = $em->getRepository('mycpBundle:payment')->findOneBy(array("booking" => $id_booking));
        $user = $em->getRepository('mycpBundle:user')->findOneBy(array('user_id' => $payment->getBooking()->getBookingUserId()));
        $reservations = $em->getRepository('mycpBundle:ownershipReservation')->findBy(array('own_res_reservation_booking' => $id_booking, 'own_res_status' => ownershipReservation::STATUS_RESERVED), array('own_res_gen_res_id' => 'ASC'));
        return $this->render('mycpBundle:reservation:booking_agDetails.html.twig', array(
                    'user' => $user,
                    'reservations' => $reservations,
                    'payment' => $payment
        ));
    }

    public function checkinAction(Request $request) {
        $service_security = $this->get('Secure');
        $service_security->verifyAccess();
        $em = $this->getDoctrine()->getManager();
        $filter_date_from = $request->get('filter_date_from');
        $sort_by = $request->get('sort_by');

        if ($filter_date_from == 'null')
            $filter_date_from = '';

        if ($sort_by == 'null')
            $sort_by = \MyCp\mycpBundle\Helpers\OrderByHelper::DEFAULT_ORDER_BY;

        if ($filter_date_from == "") {
            $filter_date_from = new \DateTime();
            $startTimeStamp = $filter_date_from->getTimestamp();
            $startTimeStamp = strtotime("+5 day", $startTimeStamp);
            $filter_date_from->setTimestamp($startTimeStamp);
            $filter_date_from = $filter_date_from->format("d/m/Y");
        } else {
            $filter_date_from = str_replace('_', '/', $filter_date_from);
        }

        $checkins = $em->getRepository("mycpBundle:generalReservation")->getCheckinsPartner($filter_date_from, $sort_by);


        return $this->render('mycpBundle:agency:checkIn.html.twig', array(
            'list' => $checkins,
            'filter_date_from' => $filter_date_from,
            'sort_by' => $sort_by));
    }

    public function list_reservations_agAction($items_per_page, Request $request) {
        $service_security = $this->get('Secure');
        $service_security->verifyAccess();
        $page = 1;
        $filter_date_reserve = $request->get('filter_date_reserve');
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
            return $this->redirect($this->generateUrl('mycp_list_reservations_ag'));
        }

        if ($filter_date_reserve == 'null')
            $filter_date_reserve = '';
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
        $filter_date_from = str_replace('_', '/', $filter_date_from);
        $filter_date_to = str_replace('_', '/', $filter_date_to);

        $em = $this->getDoctrine()->getManager();
        $paginator = $this->get('ideup.simple_paginator');
        $paginator->setItemsPerPage($items_per_page);

        $all = $paginator->paginate($em->getRepository('mycpBundle:generalReservation')
            ->getAllPag($filter_date_reserve, $filter_offer_number, $filter_reference, $filter_date_from, $filter_date_to, $sort_by, $filter_booking_number, $filter_status, $filter_client, $items_per_page, $page, true))->getResult();
        $reservations = $all['reservations'];
        $filter_date_reserve_twig = str_replace('/', '_', $filter_date_reserve);
        $filter_date_from_twig = str_replace('/', '_', $filter_date_from);
        $filter_date_to_twig = str_replace('/', '_', $filter_date_to);

        $totalItems = $all['totalItems'];
        $last_page_number = ceil($totalItems / $items_per_page);

        $start_page = ($page == 1) ? ($page) : ($page - 1);
        $end_page = ($page == $last_page_number) ? ($last_page_number) : ($page + 1);

        return $this->render('mycpBundle:reservation:list_ag.html.twig', array(
            //'total_nights' => $total_nights,
            'reservations' => $reservations,
            'items_per_page' => $items_per_page,
            'current_page' => $page,
            'total_items' => $totalItems,
            'last_page_number' => $last_page_number,
            'start_page'=>$start_page,
            'end_page'=>$end_page,
            'filter_date_reserve' => $filter_date_reserve,
            'filter_offer_number' => $filter_offer_number,
            'filter_booking_number' => $filter_booking_number,
            'filter_reference' => $filter_reference,
            'filter_date_from' => $filter_date_from,
            'filter_date_to' => $filter_date_to,
            'sort_by' => $sort_by,
            'filter_date_reserve_twig' => $filter_date_reserve_twig,
            'filter_date_from_twig' => $filter_date_from_twig,
            'filter_date_to_twig' => $filter_date_to_twig,
            'filter_status' => $filter_status,
            'filter_client' => $filter_client
        ));
    }

    public function details_reservation_agAction($id_reservation, Request $request) {
        $em = $this->getDoctrine()->getManager();
        $reservation = $em->getRepository('mycpBundle:generalReservation')->find($id_reservation);
        $ownership_reservations = $em->getRepository('mycpBundle:ownershipReservation')->findBy(array('own_res_gen_res_id' => $id_reservation));
        $offerLog = $em->getRepository("mycpBundle:offerLog")->findOneBy(array("log_offer_reservation" => $id_reservation), array("log_date" => "DESC"));
        $errors = array();

        $service_time = $this->get('time');
        $reservationService = $this->get('mycp.reservation.service');

        $dates = $service_time->datesBetween($reservation->getGenResFromDate()->format('Y-m-d'), $reservation->getGenResToDate()->format('Y-m-d'));
        $post = $request->request->getIterator()->getArrayCopy();
        if ($request->getMethod() == 'POST') {
            try {
                $errors = $reservationService->updateReservationFromRequest($post, $reservation, $ownership_reservations);

                $calendarService = $this->get("mycp.service.calendar");
                $calendarService->createICalForAccommodation($reservation->getGenResOwnId()->getOwnId());
                if(count($errors) == 0) {
                    $message = 'Reserva actualizada satisfactoriamente.';
                    $this->get('session')->getFlashBag()->add('message_ok', $message);
                }
            }
            catch(\Exception $e)
            {
                $message = 'Error: '.$e->getMessage();
                $this->get('session')->getFlashBag()->add('message_error_main', $message);
            }
        }

        $user = $reservation->getGenResUserId();
        $array_nights = array();
        $rooms = array();
        foreach ($ownership_reservations as $res) {
            $nights = $service_time->nights($res->getOwnResReservationFromDate()->getTimestamp(), $res->getOwnResReservationToDate()->getTimestamp());
            array_push($rooms, $em->getRepository('mycpBundle:room')->find($res->getOwnResSelectedRoomId()));
            array_push($array_nights, $nights);
        }

        array_pop($dates);
        $currentServiceFee = $reservation->getServiceFee();

        $client = $reservation->getTravelAgencyDetailReservations()->first()->getReservation()->getClient();

        return $this->render('mycpBundle:reservation:reservation_agDetails.html.twig', array(
            'post' => $post,
            'errors' => $errors,
            'reservation' => $reservation,
            'user' => $user,
            'client'=>$client,
            'reservations' => $ownership_reservations,
            'rooms' => $rooms,
            'nights' => $array_nights,
            'id_reservation' => $id_reservation,
            "offerLog" => $offerLog,
            'currentServiceFee' => $currentServiceFee
        ));
    }

    public function send_reservationAction($id_reservation) {
        $em = $this->getDoctrine()->getManager();
        $generalReservation = $em->getRepository("mycpBundle:generalReservation")->find($id_reservation);
        $custom_message = $this->getRequest()->get('message_to_client');

        if($custom_message != "" && isset($custom_message))
        {
            // dump($custom_message); die;
            $from = $this->getUser();
            $to = $generalReservation->getGenResUserId();
            $subject = "Reservación ".$generalReservation->getCASId();

            $message = $em->getRepository("mycpBundle:message")->insert($from, $to, $subject, $custom_message);
            if($message != null) {
                $service_log = $this->get('log');
                $service_log->saveLog($message->getLogDescription(), BackendModuleName::MODULE_CLIENT_MESSAGES, log::OPERATION_INSERT, DataBaseTables::MESSAGE);
            }
        }

        if ($generalReservation->getGenResStatus() == generalReservation::STATUS_RESERVED || $generalReservation->getGenResStatus() == generalReservation::STATUS_PARTIAL_RESERVED) {
            $bookingService = $this->get('front_end.services.booking');
            $emailService = $this->get('mycp.service.email_manager');
            VoucherHelper::sendVoucherToClient($em, $bookingService, $emailService, $this, $generalReservation, 'SEND_VOUCHER_SUBJECT', $custom_message, false);
        }
        else {

            $service_email = $this->get('Email');
            $service_email->sendReservation($id_reservation, $custom_message, false);

            // inform listeners that a reservation was sent out
            $dispatcher = $this->get('event_dispatcher');
            $eventData = new GeneralReservationJobData($id_reservation);
            $dispatcher->dispatch('mycp.event.reservation.sent_out', new JobEvent($eventData));
        }

        $message = 'Reserva enviada satisfactoriamente';
        $this->get('session')->getFlashBag()->add('message_ok', $message);
        return $this->redirect($this->generateUrl('mycp_details_reservation_ag', array('id_reservation' => $id_reservation)));
    }

    public function exportReservationsAction(Request $request) {
        try {
            //$service_security = $this->get('Secure');
            //$service_security->verifyAccess();
            $filter_date_reserve = $request->get('filter_date_reserve');
            $filter_offer_number = $request->get('filter_offer_number');
            $filter_reference = $request->get('filter_reference');
            $filter_client = $request->get('filter_client');
            $filter_date_from = $request->get('filter_date_from');
            $filter_date_to = $request->get('filter_date_to');
            $filter_booking_number = $request->get('filter_booking_number');
            $filter_status = $request->get('filter_status');
            $sort_by = $request->get('sort_by');

            if ($filter_date_reserve == 'null')
                $filter_date_reserve = '';
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

            $date = new \DateTime();
            $date = date_modify($date, "-5 days");

            $em = $this->getDoctrine()->getManager();
            $reservations = $em->getRepository('mycpBundle:generalReservation')
                ->getReservationsAgToExport($filter_date_reserve, $filter_offer_number, $filter_reference, $filter_date_from, $filter_date_to, $sort_by, $filter_booking_number, $filter_status,$filter_client,  $date);

            if(count($reservations)) {
                $exporter = $this->get("mycp.service.export_to_excel");
                return $exporter->exportReservationsAg($reservations, $date);
            }
            else{
                $message = 'No hay datos para llenar el Excel a descargar.';
                $this->get('session')->getFlashBag()->add('message_ok', $message);
                return $this->redirect($this->generateUrl("mycp_list_reservations_ag"));
            }
        }
        catch(\Exception $e){
            $message = 'Ha ocurrido un error. Por favor, introduzca correctamente los valores para filtrar.';
            $this->get('session')->getFlashBag()->add('message_error_main', $message);

            return $this->redirect($this->generateUrl("mycp_list_reservations_ag"));
        }
    }

    public function setNotAvailableCallbackAction() {
        $request = $this->getRequest();
        $em = $this->getDoctrine()->getManager();
        $reservations_ids = $request->request->get('reservations_ids');
        $save_option = $request->request->get('save_option');
        $page = $request->request->get('page');
        $service_log= $this->get('log');
        $logMessage = "";
        $response = null;

        //Modificar el estado
        $em->getRepository('mycpBundle:generalReservation')->setAsNotAvailable($reservations_ids, $save_option);

        //Enviar por correo a los clientes
        $service_email = $this->get('Email');

        try {
            foreach ($reservations_ids as $genResId) {
                $service_email->sendReservation($genResId, null, false);
                $logMessage = ($logMessage == "") ? $logMessage."CAS.".$genResId : $logMessage.", CAS.".$genResId;

                // inform listeners that a reservation was sent out
                $dispatcher = $this->get('event_dispatcher');
                $eventData = new GeneralReservationJobData($genResId);
                $dispatcher->dispatch('mycp.event.reservation.sent_out', new JobEvent($eventData));
            }

            $service_log->saveLog("Se han colocado ".count($reservations_ids)." reservas como no disponibles: ".$logMessage, BackendModuleName::MODULE_RESERVATION, log::OPERATION_UPDATE, DataBaseTables::GENERAL_RESERVATION);
            $message = ($save_option == Operations::SAVE_AND_UPDATE_CALENDAR) ? 'Se han modificado ' . count($reservations_ids) . ' reservaciones como No Disponibles, se almacenaron las No Disponibilidades y se ha notificado a los clientes respectivos. Todas las operaciones fueron satisfactorias.' :
                'Se han modificado ' . count($reservations_ids) . ' reservaciones como No Disponibles y se ha notificado a los clientes respectivos. Ambas operaciones fueron satisfactorias.';
            $this->get('session')->getFlashBag()->add('message_ok', $message);

            $response = ($page != "" && $page != "0") ? $this->generateUrl('mycp_list_reservations_ag', array("page" => $page)) : $this->generateUrl('mycp_list_reservations_ag');
        } catch (\Exception $e) {
            $message = 'Los correos no pudieron ser enviados.';
            $this->get('session')->getFlashBag()->add('message_error_local', $message);
            $response = "ERROR";
        }
        return new Response($response);
    }

    public function newCleanOfferAction($idClient, $idClientOfAg, $attendedDate)
    {
        $em = $this->getDoctrine()->getManager();

        $client = null;

        if($idClient != null && $idClient != "null" && $idClient != "")
        {
            $client = $em->getRepository("mycpBundle:user")->find($idClient);
        }

        if($idClientOfAg != null && $idClientOfAg != "null" && $idClientOfAg != "")
        {
            $paClient = $em->getRepository("PartnerBundle:paClient")->find($idClientOfAg);
        }

        if ($this->getRequest()->getMethod() == 'POST') {
            $request = $this->getRequest();
            $reservationService = $this->get("mycp.reservation.service");
            $service_log= $this->get('log');

            $resultReservations = $reservationService->createAvailableOfferFromRequest($request, $client, $attendedDate);

            $newReservations = $resultReservations['reservations'];
            $arrayNightsByOwnershipReservation = $resultReservations['nights'];
            $general_reservation = $resultReservations['generalReservation'];
            $operation = $request->get("save_operation");

            $childrens = 0;
            $adults = 0;
            foreach ($newReservations as $newReservation) {
                $adults += $newReservation->getOwnResCountAdults();
                $childrens += $newReservation->getOwnResCountChildrens();
            }
            $paReservation = new paReservation();
            $paReservation->setClient($paClient);
            $paReservation->setAdults($adults);
            $paReservation->setAdultsWithAccommodation($adults);
            $paReservation->setChildren($childrens);
            $paReservation->setChildrenWithAccommodation($childrens);
            $paReservation->setCreated(new \DateTime());
            $paReservation->setModified(new \DateTime());
            $paReservation->setClosed(1);
            $em->persist($paReservation);

            $paReservationDetail = new paReservationDetail();
            $paReservationDetail->setReservation($paReservation);
            $paReservationDetail->setReservationDetail($general_reservation);
            $em->persist($paReservationDetail);
            $em->flush();

            $message = 'Nueva oferta ' . $general_reservation->getCASId() . ' creada satisfactoriamente.';
            $this->get('session')->getFlashBag()->add('message_ok', $message);

            // inform listeners that a reservation was sent out (remarketing)
            $dispatcher = $this->get('event_dispatcher');
            $eventData = new GeneralReservationJobData($general_reservation->getGenResId());
            $dispatcher->dispatch('mycp.event.reservation.sent_out', new JobEvent($eventData));

            $service_log->saveLog($general_reservation->getLogDescription()." (Nueva oferta)", BackendModuleName::MODULE_RESERVATION, log::OPERATION_INSERT, DataBaseTables::GENERAL_RESERVATION);
            $service_log->saveNewOfferLog($general_reservation,null,false);

            switch($operation)
            {
                case Operations::SAVE_OFFER_AND_SEND: {
                    //Enviar correo al cliente incluyendo el texto
                    $custom_message = $request->get('message_body');
                    if($custom_message !== "")
                    {
                        $from = $this->getUser();
                        $to = $general_reservation->getGenResUserId();
                        $subject = "Reservación ".$general_reservation->getCASId();

                        $createdMessage = $em->getRepository("mycpBundle:message")->insert($from, $to, $subject, $custom_message);
                        if($createdMessage != null)
                            $service_log->saveLog($createdMessage->getLogDescription(), BackendModuleName::MODULE_CLIENT_MESSAGES, log::OPERATION_INSERT, DataBaseTables::MESSAGE);
                    }

                    $mailer = $this->get('Email');
                    $mailer->sendReservation($general_reservation->getGenResId(), $custom_message, true);

                    $message = 'Oferta '.$general_reservation->getCASId().' enviada satisfactoriamente.';
                    $this->get('session')->getFlashBag()->add('message_ok', $message);

                    return $this->redirect($this->generateUrl('mycp_list_reservations_ag'));
                }
                case Operations::SAVE_OFFER_AND_VIEW:
                {
                    return $this->redirect($this->generateUrl('mycp_details_reservation_ag', array('id_reservation' => $general_reservation->getGenResId())));
                }
                case Operations::SAVE_AND_EXIT:
                {
                    return $this->redirect($this->generateUrl('mycp_list_reservations_ag'));
                }
            }
        }

        return $this->render('mycpBundle:reservation:newCleanOfferAg.html.twig', array(
            'client' => $client, 'clientOfAg' => $paClient, "attendedDate" => $attendedDate));
    }

    public function generateClientCallbackAction() {
        $request = $this->getRequest();
        $users_ids = array_unique($request->request->get('users_ids'));

        $exporter = $this->get("mycp.service.export_to_excel");
        $exporter->generateClientsAg($users_ids);

        return new Response($this->generateUrl("mycp_download_clients"), 200);
    }
}

