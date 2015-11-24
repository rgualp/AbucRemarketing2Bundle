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

class LodgingReservationController extends Controller {

    public function list_reservationsAction($items_per_page, Request $request) {
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
        if ($filter_status == 'null')
            $filter_status = '';

        if (isset($_GET['page']))
            $page = $_GET['page'];
        $filter_date_reserve = str_replace('_', '/', $filter_date_reserve);
        $filter_date_from = str_replace('_', '/', $filter_date_from);
        $filter_date_to = str_replace('_', '/', $filter_date_to);

        $em = $this->getDoctrine()->getManager();
        $paginator = $this->get('ideup.simple_paginator');
        $paginator->setItemsPerPage($items_per_page);


        $user = $this->get('security.context')->getToken()->getUser();

        if ($user->getUserRole() != 'ROLE_CLIENT_CASA')
            $reser = $em->getRepository('mycpBundle:generalReservation')->getAll($filter_date_reserve, $filter_offer_number, $filter_reference, $filter_date_from, $filter_date_to, $sort_by, $filter_booking_number, $filter_status);
        else {
            $userCasa = $em->getRepository('mycpBundle:userCasa')->getByUser($user->getUserId());
            $reser = $em->getRepository('mycpBundle:generalReservation')->getByUserCasa($filter_date_reserve, $filter_offer_number, $filter_reference, $filter_date_from, $filter_date_to, $sort_by, $filter_booking_number, $userCasa->getUserCasaId(), $filter_status);
        }


        $reservations = $paginator->paginate($reser)->getResult();
        $filter_date_reserve_twig = str_replace('/', '_', $filter_date_reserve);
        $filter_date_from_twig = str_replace('/', '_', $filter_date_from);
        $filter_date_to_twig = str_replace('/', '_', $filter_date_to);
        $service_log = $this->get('log');
        $service_log->saveLog('Visit', BackendModuleName::MODULE_LODGING_RESERVATION);
        return $this->render('mycpBundle:reservation:list_readonly.html.twig', array(
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
                    'filter_date_to_twig' => $filter_date_to_twig,
                    'filter_status' => $filter_status,
        ));
    }

    public function details_reservationAction($id_reservation, $from_calendar, Request $request) {
        $service_security = $this->get('Secure');
        $service_security->verifyAccess();
        $em = $this->getDoctrine()->getManager();
        $reservation = $em->getRepository('mycpBundle:generalReservation')->find($id_reservation);
        $ownership_reservations = $em->getRepository('mycpBundle:ownershipReservation')->findBy(array('own_res_gen_res_id' => $id_reservation));
        $errors = array();

        $service_time = $this->get('time');
        $post = $request->request->getIterator()->getArrayCopy();
        $dates = $service_time->datesBetween($reservation->getGenResFromDate()->format('Y-m-d'), $reservation->getGenResToDate()->format('Y-m-d'));


        $user = $em->getRepository('mycpBundle:userTourist')->findBy(array('user_tourist_user' => $reservation->getGenResUserId()));
        $array_nights = array();
        $rooms = array();
        foreach ($ownership_reservations as $res) {
            $nights = $service_time->nights($res->getOwnResReservationFromDate()->getTimestamp(), $res->getOwnResReservationToDate()->getTimestamp());
            array_push($rooms, $em->getRepository('mycpBundle:room')->find($res->getOwnResSelectedRoomId()));
            array_push($array_nights, $nights);
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

    public function listReservationsByUserAction($items_per_page, Request $request) {
        $service_security = $this->get('Secure');
        $service_security->verifyAccess();
        $page = 1;
        $filter_user_name = $request->get('filter_user_name');
        $filter_user_email = $request->get('filter_user_email');
        $filter_user_city = $request->get('filter_user_city');
        $filter_user_country = $request->get('filter_user_country');

        $sort_by = $request->get('sort_by');
        if ($request->getMethod() == 'POST' && ($sort_by == "" || $sort_by == "null" || $sort_by == "0") && $filter_user_name == 'null' && $filter_user_email == 'null' && $filter_user_city == 'null' &&
                $filter_user_country == 'null') {
            $message = 'Debe llenar al menos un campo para filtrar o seleccionar un criterio de ordenación.';
            $this->get('session')->getFlashBag()->add('message_error_local', $message);
            return $this->redirect($this->generateUrl('mycp_list_readonly_reservations'));
        }
        if ($filter_user_name == 'null')
            $filter_user_name = '';
        if ($filter_user_email == 'null')
            $filter_user_email = '';
        if ($filter_user_city == 'null')
            $filter_user_city = '';
        if ($filter_user_country == 'null')
            $filter_user_country = '';
        if ($sort_by == 'null')
            $sort_by = '';

        if (isset($_GET['page']))
            $page = $_GET['page'];

        $em = $this->getDoctrine()->getEntityManager();
        $paginator = $this->get('ideup.simple_paginator');
        $paginator->setItemsPerPage($items_per_page);

        $user = $this->get('security.context')->getToken()->getUser();

        if ($user->getUserRole() != 'ROLE_CLIENT_CASA')
            $reser = $em->getRepository('mycpBundle:generalReservation')->getUsers($filter_user_name, $filter_user_email, $filter_user_city, $filter_user_country, $sort_by);
        else {
            $userCasa = $em->getRepository('mycpBundle:userCasa')->getByUser($user->getUserId());
            $reser = $em->getRepository('mycpBundle:generalReservation')->getUsers($filter_user_name, $filter_user_email, $filter_user_city, $filter_user_country, $sort_by, $userCasa->getUserCasaOwnership()->getOwnId());
        }

        $reservations = $paginator->paginate($reser)->getResult();

        $service_log = $this->get('log');
        $service_log->saveLog('Visit', BackendModuleName::MODULE_RESERVATION);

        /*$languages = array();
        foreach ($reservations as $reservation) {
            $user_tourist = $em->getRepository('mycpBundle:userTourist')->findBy(array('user_tourist_user' => $reservation['user_id']));
            if ($user_tourist[0]->getUserTouristLanguage())
                array_push($languages, $user_tourist[0]->getUserTouristLanguage()->getLangName());
        }*/

        return $this->render('mycpBundle:reservation:list_client_readonly.html.twig', array(
                    //'languages' => $languages,
                    'reservations' => $reservations,
                    'items_per_page' => $items_per_page,
                    'current_page' => $page,
                    'total_items' => $paginator->getTotalItems(),
                    'filter_user_name' => $filter_user_name,
                    'filter_user_email' => $filter_user_email,
                    'filter_user_city' => $filter_user_city,
                    'filter_user_country' => $filter_user_country,
                    'sort_by' => $sort_by
        ));
    }

    public function detailsClientReservationAction($id_client, Request $request) {

        //$service_security= $this->get('Secure');
        //$service_security->verifyAccess();
        //$service_log= $this->get('log');
        //$service_log->saveLog('Visit', BackendModuleName::MODULE_RESERVATION);

        $service_time = $this->get('time');

        $em = $this->getDoctrine()->getManager();
        $client = $em->getRepository('mycpBundle:user')->find($id_client);
        $user = $this->get('security.context')->getToken()->getUser();
        $userCasa = $em->getRepository('mycpBundle:userCasa')->getByUser($user->getUserId());
        $reservations = $em->getRepository('mycpBundle:generalReservation')->getByUser($id_client, $userCasa->getUserCasaOwnership()->getOwnId());
        $userTourist = $em->getRepository('mycpBundle:userTourist')->findBy(array('user_tourist_user' => $id_client));
        $price = 0;
        $total_nights = array();

        foreach ($reservations as $reservation) {
            $temp_total_nights = 0;
            $owns_res = $em->getRepository('mycpBundle:ownershipReservation')->findBy(array('own_res_gen_res_id' => $reservation['gen_res_id']));

            foreach ($owns_res as $own) {
                $nights = $service_time->nights($own->getOwnResReservationFromDate()->getTimestamp(), $own->getOwnResReservationToDate()->getTimestamp());
                $temp_total_nights+=$nights;
            }
            array_push($total_nights, $temp_total_nights);
        }
        return $this->render('mycpBundle:reservation:reservationDetailsClientReadonly.html.twig', array(
                    'total_nights' => $total_nights,
                    'reservations' => $reservations,
                    'client' => $client,
                    'errors' => '',
                    'tourist' => $userTourist[0]
        ));
    }

    public function detailsReservationPartialAction($id_reservation) {
        $em = $this->getDoctrine()->getManager();
        $reservation = $em->getRepository('mycpBundle:generalReservation')->find($id_reservation);
        $ownership_reservations = $em->getRepository('mycpBundle:ownershipReservation')->findBy(array('own_res_gen_res_id' => $id_reservation));

        $service_time = $this->get('time');

        $user = $em->getRepository('mycpBundle:userTourist')->findBy(array('user_tourist_user' => $reservation->getGenResUserId()));

        $rooms = array();
        $total_nights = array();
        foreach ($ownership_reservations as $res) {
            array_push($rooms, $em->getRepository('mycpBundle:room')->find($res->getOwnResSelectedRoomId()));
            $temp_total_nights = 0;
            $nights = $service_time->nights($res->getOwnResReservationFromDate()->getTimestamp(), $res->getOwnResReservationToDate()->getTimestamp());
            $temp_total_nights+=$nights;

            array_push($total_nights, $temp_total_nights);
        }

        return $this->render('mycpBundle:reservation:reservationDetailsPartialReadonly.html.twig', array(
                    'nights' => $total_nights,
                    'reservation' => $reservation,
                    'user' => $user,
                    'reservations' => $ownership_reservations,
                    'rooms' => $rooms,
                    'id_reservation' => $id_reservation
        ));
    }
}
