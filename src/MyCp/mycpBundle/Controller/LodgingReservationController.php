<?php

namespace MyCp\mycpBundle\Controller;

use Abuc\RemarketingBundle\Event\JobEvent;
use MyCp\mycpBundle\Entity\booking;
use MyCp\mycpBundle\Entity\payment;
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

class LodgingReservationController extends Controller
{
    public function list_reservationsAction($items_per_page, Request $request) {
        $service_security = $this->get('Secure');
        //$service_security->verifyAccess();
        $page = 1;
        $filter_date_reserve = $request->get('filter_date_reserve');
        $filter_offer_number = $request->get('filter_offer_number');
        $filter_reference = $request->get('filter_reference');
        $filter_date_from = $request->get('filter_date_from');
        $filter_date_to = $request->get('filter_date_to');
        $filter_booking_number = $request->get('filter_booking_number');
        $price = 0;
        $sort_by = $request->get('sort_by');
        if ($request->getMethod() == 'POST' && $filter_date_reserve == 'null' && $filter_offer_number == 'null' && $filter_reference == 'null' &&
            $filter_date_from == 'null' && $filter_date_to == 'null' && $sort_by == 'null' && $filter_booking_number == 'null'
        ) {
            $message = 'Debe llenar al menos un campo para filtrar.';
            $this->get('session')->getFlashBag()->add('message_error_local', $message);
            return $this->redirect($this->generateUrl('mycp_list_readonly_reservations'));
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


        $user = $this->get('security.context')->getToken()->getUser();

        if($user->getUserRole()!='ROLE_CLIENT_CASA')
            $reser = $em->getRepository('mycpBundle:generalReservation')->get_all_reservations($filter_date_reserve, $filter_offer_number, $filter_reference, $filter_date_from, $filter_date_to, $sort_by, $filter_booking_number);
        else
        {
            $userCasa = $em->getRepository('mycpBundle:userCasa')->get_user_casa_by_user_id($user->getUserId());
            //$reser = $em->getRepository('mycpBundle:generalReservation')->get_all_reservations($filter_date_reserve, $filter_offer_number, $filter_reference, $filter_date_from, $filter_date_to, $sort_by, $filter_booking_number);
            $reser = $em->getRepository('mycpBundle:generalReservation')->get_all_reservations_by_user_casa($filter_date_reserve, $filter_offer_number, $filter_reference, $filter_date_from, $filter_date_to, $sort_by, $filter_booking_number, $userCasa->getUserCasaId());
        }


        $reservations = $paginator->paginate($reser)->getResult();
        $filter_date_reserve_twig = str_replace('/', '_', $filter_date_reserve);
        $filter_date_from_twig = str_replace('/', '_', $filter_date_from);
        $filter_date_to_twig = str_replace('/', '_', $filter_date_to);
        $service_log = $this->get('log');
        $service_log->saveLog('Visit', BackendModuleName::MODULE_LODGING_RESERVATION);
        $total_nights = array();
        $service_time = $this->get('time');
        foreach ($reservations as $res) {
            $owns_res = $em->getRepository('mycpBundle:ownershipReservation')->findBy(array('own_res_gen_res_id' => $res[0]['gen_res_id']));
            $temp_total_nights = 0;
            foreach ($owns_res as $own) {
                $array_dates = $service_time->datesBetween($own->getOwnResReservationFromDate()->getTimestamp(), $own->getOwnResReservationToDate()->getTimestamp());
                $temp_total_nights+=count($array_dates) - 1;
            }
            array_push($total_nights, $temp_total_nights);
        }
        return $this->render('mycpBundle:reservation:list_readonly.html.twig', array(
            'total_nights' => $total_nights,
            'reservations' => $reservations,
            'items_per_page' => $items_per_page,
            'current_page' => $page,
            'total_items' => $paginator->getTotalItems(),
            'filter_date_reserve' => $filter_date_reserve,
            'filter_offer_number' => $filter_offer_number,
            'filter_booking_number' => $filter_booking_number,
            'filter_reference' => $filter_reference,
            'filter_date_from' => $filter_date_from,
            'filter_date_to' => $filter_date_to,
            'sort_by' => $sort_by,
            'filter_date_reserve_twig' => $filter_date_reserve_twig,
            'filter_date_from_twig' => $filter_date_from_twig,
            'filter_date_to_twig' => $filter_date_to_twig
        ));
    }

    public function details_reservationAction($id_reservation, $from_calendar,Request $request) {
        $service_security = $this->get('Secure');
        //$service_security->verifyAccess();
        $em = $this->getDoctrine()->getManager();
        $reservation = $em->getRepository('mycpBundle:generalReservation')->find($id_reservation);
        $ownership_reservations = $em->getRepository('mycpBundle:ownershipReservation')->findBy(array('own_res_gen_res_id' => $id_reservation));
        $errors = array();

        $service_time = $this->get('time');
        $post = $request->request->getIterator()->getArrayCopy();
        $dates = $service_time->dates_between($reservation->getGenResFromDate()->format('Y-m-d'), $reservation->getGenResToDate()->format('Y-m-d'));


        $user = $em->getRepository('mycpBundle:userTourist')->findBy(array('user_tourist_user' => $reservation->getGenResUserId()));
        $array_nights = array();
        $rooms = array();
        foreach ($ownership_reservations as $res) {
            $dates_temp = $service_time->dates_between($res->getOwnResReservationFromDate()->getTimestamp(), $res->getOwnResReservationToDate()->getTimestamp());
            array_push($rooms, $em->getRepository('mycpBundle:room')->find($res->getOwnResSelectedRoomId()));
            array_push($array_nights, count($dates_temp) - 1);
        }

        array_pop($dates);
        return $this->render('mycpBundle:reservation:reservationDetails_readonly.html.twig', array(
            'post' => $post,
            'errors' => $errors,
            'reservation' => $reservation,
            'user' => $user,
            'reservations' => $ownership_reservations,
            'rooms' => $rooms,
            'nights' => $array_nights,
            'id_reservation' => $id_reservation,
            'from_calendar' => $from_calendar));
    }
}
