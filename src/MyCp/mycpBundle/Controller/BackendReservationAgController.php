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

        $checkins = $em->getRepository("mycpBundle:generalReservation")->getCheckins($filter_date_from, $sort_by);

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
        $filter_date_from = $request->get('filter_date_from');
        $filter_date_to = $request->get('filter_date_to');
        $filter_booking_number = $request->get('filter_booking_number');
        $filter_status = $request->get('filter_status');
        $price = 0;
        $sort_by = $request->get('sort_by');
        if ($request->getMethod() == 'POST' && $filter_date_reserve == 'null' && $filter_offer_number == 'null' && $filter_reference == 'null' &&
            $filter_date_from == 'null' && $filter_date_to == 'null' && $sort_by == 'null' && $filter_booking_number == 'null' && $filter_status == 'null'
        ) {
            $message = 'Debe llenar al menos un campo para filtrar.';
            $this->get('session')->getFlashBag()->add('message_error_local', $message);
            return $this->redirect($this->generateUrl('mycp_list_reservations'));
        }

        if ($filter_date_reserve == 'null')
            $filter_date_reserve = '';
        if ($filter_offer_number == 'null')
            $filter_offer_number = '';
        if ($filter_booking_number == 'null')
            $filter_booking_number = '';
        if ($filter_reference == 'null')
            $filter_reference = '';
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
            ->getAllPag($filter_date_reserve, $filter_offer_number, $filter_reference, $filter_date_from, $filter_date_to, $sort_by, $filter_booking_number, $filter_status, $items_per_page, $page, true))->getResult();
        $reservations = $all['reservations'];
        $filter_date_reserve_twig = str_replace('/', '_', $filter_date_reserve);
        $filter_date_from_twig = str_replace('/', '_', $filter_date_from);
        $filter_date_to_twig = str_replace('/', '_', $filter_date_to);
//            $service_log = $this->get('log');
//            $service_log->saveLog('Visit', BackendModuleName::MODULE_RESERVATION);
        /*$total_nights = array();
        $service_time = $this->get('time');
        foreach ($reservations as $res) {
            $owns_res = $em->getRepository('mycpBundle:ownershipReservation')->findBy(array('own_res_gen_res_id' => $res[0]["gen_res_id"]));
            $temp_total_nights = generalReservation::getTotalPayedNights($owns_res, $service_time);
            array_push($total_nights, $temp_total_nights);
        }*/

        $totalItems = $all['totalItems'];
        return $this->render('mycpBundle:reservation:list_ag.html.twig', array(
            //'total_nights' => $total_nights,
            'reservations' => $reservations,
            'items_per_page' => $items_per_page,
            'current_page' => $page,
            'total_items' => $totalItems,
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
            'last_page_number' => ceil($totalItems / $items_per_page)
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

        return $this->render('mycpBundle:reservation:reservation_agDetails.html.twig', array(
            'post' => $post,
            'errors' => $errors,
            'reservation' => $reservation,
            'user' => $user,
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
}

