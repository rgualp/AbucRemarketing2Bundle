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

class BackendReservationController extends Controller {

    public function new_offerAction($id_tourist, $id_reservation) {

        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('mycpBundle:user')->find($id_tourist);
        $gen_res = $em->getRepository('mycpBundle:generalReservation')->find($id_reservation);
        $id_mun = $gen_res->getGenResOwnId()->getOwnAddressMunicipality();
        $ownerships = $em->getRepository('mycpBundle:ownership')->findBy(array('own_address_municipality' => $id_mun));

        if ($this->getRequest()->getMethod() == 'POST') {
            $request = $this->getRequest();
            $id_ownership = $request->get('data_ownership');

            $em = $this->getDoctrine()->getManager();
            $ownership = $em->getRepository('mycpBundle:ownership')->find($id_ownership);

            if (!$request->get('data_reservation'))
                throw $this->createNotFoundException();
            $data = $request->get('data_reservation');
            $data = explode('/', $data);

            $from_date = $data[0];
            $to_date = $data[1];
            $ids_rooms = $data[2];

            $count_guests = $data[3];
            $count_kids = $data[4];
            $array_ids_rooms = explode('&', $ids_rooms);
            array_shift($array_ids_rooms);
            $array_count_guests = explode('&', $count_guests);
            array_shift($array_count_guests);
            $array_count_kids = explode('&', $count_kids);
            array_shift($array_count_kids);

            $reservation_date_from = $from_date;
            $reservation_date_from = explode('&', $reservation_date_from);

            $start_timestamp = mktime(0, 0, 0, $reservation_date_from[1], $reservation_date_from[0], $reservation_date_from[2]);

            $reservation_date_to = $to_date;
            $reservation_date_to = explode('&', $reservation_date_to);
            $end_timestamp = mktime(0, 0, 0, $reservation_date_to[1], $reservation_date_to[0], $reservation_date_to[2]);

            $general_reservation = new generalReservation();
            $general_reservation->setGenResUserId($user);
            $general_reservation->setGenResDate(new \DateTime(date('Y-m-d')));
            $general_reservation->setGenResStatusDate(new \DateTime(date('Y-m-d')));
            $general_reservation->setGenResHour(date('G'));
            $general_reservation->setGenResStatus(generalReservation::STATUS_PENDING);
            $general_reservation->setGenResFromDate(new \DateTime(date("Y-m-d H:i:s", $start_timestamp)));
            $general_reservation->setGenResToDate(new \DateTime(date("Y-m-d H:i:s", $end_timestamp)));
            $general_reservation->setGenResSaved(0);
            $general_reservation->setGenResOwnId($ownership);
            $em->persist($general_reservation);

            $service_time = $this->get('Time');
            $total_price = 0;

            $destination_id = ($ownership->getOwnDestination() != null) ? $ownership->getOwnDestination()->getDesId() : null;
            $count_adults = 0;
            $count_children = 0;
            for ($i = 0; $i < count($array_ids_rooms); $i++) {
                $room = $em->getRepository('mycpBundle:room')->find($array_ids_rooms[$i]);
                $count_adults = (isset($array_count_kids[$i])) ? $array_count_guests[$i] : 1;
                $count_children = (isset($array_count_kids[$i])) ? $array_count_kids[$i] : 0;

                $array_dates = $service_time->datesBetween($start_timestamp, $end_timestamp);
                $temp_price = 0;
                $triple_room_recharge = ($room->isTriple() && $count_adults + $count_children >= 3) ? $this->container->getParameter('configuration.triple.room.charge') : 0;
                $seasons = $em->getRepository("mycpBundle:season")->getSeasons($start_timestamp, $end_timestamp, $destination_id);
                for ($a = 0; $a < count($array_dates) - 1; $a++) {
                    $seasonType = $service_time->seasonTypeByDate($seasons, $array_dates[$a]);
                    $roomPrice = $room->getPriceBySeasonType($seasonType);
                    $total_price += $roomPrice + $triple_room_recharge;
                    $temp_price += $roomPrice + $triple_room_recharge;
                }

                $ownership_reservation = new ownershipReservation();
                $ownership_reservation->setOwnResCountAdults($count_adults);
                $ownership_reservation->setOwnResCountChildrens($count_children);
                $ownership_reservation->setOwnResNightPrice(0);
                $ownership_reservation->setOwnResStatus(ownershipReservation::STATUS_PENDING);
                $ownership_reservation->setOwnResReservationFromDate(new \DateTime(date("Y-m-d H:i:s", $start_timestamp)));
                $ownership_reservation->setOwnResReservationToDate(new \DateTime(date("Y-m-d H:i:s", $end_timestamp)));
                $ownership_reservation->setOwnResSelectedRoomId($room);
                $ownership_reservation->setOwnResRoomPriceDown($room->getRoomPriceDownTo());
                $ownership_reservation->setOwnResRoomPriceUp($room->getRoomPriceUpTo());
                $specialPrice = ($room->getRoomPriceSpecial() != null && $room->getRoomPriceSpecial() > 0)? $room->getRoomPriceSpecial() : $room->getRoomPriceUpTo();
                $ownership_reservation->setOwnResRoomPriceSpecial($specialPrice);
                $ownership_reservation->setOwnResGenResId($general_reservation);
                $ownership_reservation->setOwnResRoomType($room->getRoomType());
                $ownership_reservation->setOwnResTotalInSite($temp_price);
                $general_reservation->setGenResTotalInSite($total_price);
                $em->persist($ownership_reservation);
            }
            $em->flush();
            $message = "Reserva añadida satisfactoriamente";
            $this->get('session')->getFlashBag()->add('message_ok', $message);

            return $this->redirect($this->generateUrl('mycp_list_reservations'));
        }
        return $this->render('mycpBundle:reservation:new_offer.html.twig', array(
                    'id_reservation' => $id_reservation,
                    'id_tourist' => $id_tourist,
                    'user' => $user,
                    'ownerships' => $ownerships
        ));
    }

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
        $price = 0;
        $sort_by = $request->get('sort_by');
        if ($request->getMethod() == 'POST' && $filter_date_reserve == 'null' && $filter_offer_number == 'null' && $filter_reference == 'null' &&
                $filter_date_from == 'null' && $filter_date_to == 'null' && $sort_by == 'null' && $filter_booking_number == 'null'
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

        $reservations = $paginator->paginate($em->getRepository('mycpBundle:generalReservation')
                                ->getAll($filter_date_reserve, $filter_offer_number, $filter_reference, $filter_date_from, $filter_date_to, $sort_by, $filter_booking_number))->getResult();
        $filter_date_reserve_twig = str_replace('/', '_', $filter_date_reserve);
        $filter_date_from_twig = str_replace('/', '_', $filter_date_from);
        $filter_date_to_twig = str_replace('/', '_', $filter_date_to);
        $service_log = $this->get('log');
        $service_log->saveLog('Visit', BackendModuleName::MODULE_RESERVATION);
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
        return $this->render('mycpBundle:reservation:list.html.twig', array(
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

    public function list_reservations_bookingAction($items_per_page, Request $request) {
        $service_security = $this->get('Secure');
        $service_security->verifyAccess();

        $filter_booking_number = $request->get('filter_booking_number');
        $filter_date_booking = $request->get('filter_date_booking');
        $filter_user_booking = $request->get('filter_user_booking');

        if ($request->getMethod() == 'POST' && $filter_booking_number == 'null' && $filter_date_booking == 'null' && $filter_user_booking == 'null') {
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

        $page = 1;
        if (isset($_GET['page']))
            $page = $_GET['page'];
        $em = $this->getDoctrine()->getManager();
        $paginator = $this->get('ideup.simple_paginator');
        $paginator->setItemsPerPage($items_per_page);

        $bookings = $paginator->paginate($em->getRepository('mycpBundle:generalReservation')
                                ->getAllBookings($filter_booking_number, $filter_date_booking, $filter_user_booking))->getResult();
        $service_log = $this->get('log');
        $service_log->saveLog('Visit', BackendModuleName::MODULE_RESERVATION);

        $filter_date_booking = str_replace('_', '/', $filter_date_booking);

        return $this->render('mycpBundle:reservation:list_booking.html.twig', array(
                    "bookings" => $bookings,
                    'items_per_page' => $items_per_page,
                    'current_page' => $page,
                    'filter_booking_number' => $filter_booking_number,
                    'filter_date_booking' => $filter_date_booking,
                    'filter_user_booking' => $filter_user_booking,
                    'total_items' => $paginator->getTotalItems(),
        ));
    }

    public function details_bookingAction($id_booking) {
        $service_security = $this->get('Secure');
        $service_security->verifyAccess();
        $em = $this->getDoctrine()->getManager();
        $payment = $em->getRepository('mycpBundle:payment')->findOneBy(array("booking"=>$id_booking));
        $user = $em->getRepository('mycpBundle:userTourist')->findOneBy(array('user_tourist_user' => $payment->getBooking()->getBookingUserId()));
        $reservations = $em->getRepository('mycpBundle:ownershipReservation')->findBy(array('own_res_reservation_booking' => $id_booking, 'own_res_status' => ownershipReservation::STATUS_RESERVED));
        return $this->render('mycpBundle:reservation:bookingDetails.html.twig', array(
                    'user' => $user,
                    'reservations' => $reservations,
                    'payment' => $payment
        ));
    }

    public function list_reservations_userAction($items_per_page, Request $request) {
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
            return $this->redirect($this->generateUrl('mycp_list_reservations_user'));
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
        $reservations = $paginator->paginate($em->getRepository('mycpBundle:generalReservation')
                                ->getUsers($filter_user_name, $filter_user_email, $filter_user_city, $filter_user_country, $sort_by))->getResult();

        $service_log = $this->get('log');
        $service_log->saveLog('Visit', BackendModuleName::MODULE_RESERVATION);

        $currencies = array();
        $languages = array();
        foreach ($reservations as $reservation) {
            $user_tourist = $em->getRepository('mycpBundle:userTourist')->findBy(array('user_tourist_user' => $reservation['user_id']));
            if ($user_tourist[0]->getUserTouristCurrency())
                array_push($currencies, $user_tourist[0]->getUserTouristCurrency()->getCurrCode());

            if ($user_tourist[0]->getUserTouristLanguage())
                array_push($languages, $user_tourist[0]->getUserTouristLanguage()->getLangName());
        }

        return $this->render('mycpBundle:reservation:list_client.html.twig', array(
                    'languages' => $languages,
                    'currencies' => $currencies,
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

    public function details_client_reservationAction($id_client, Request $request) {

        //$service_security= $this->get('Secure');
        //$service_security->verifyAccess();
        //$service_log= $this->get('log');
        //$service_log->saveLog('Visit', BackendModuleName::MODULE_RESERVATION);

        $service_time = $this->get('time');


        $em = $this->getDoctrine()->getEntityManager();
        $client = $em->getRepository('mycpBundle:user')->find($id_client);
        $reservations = $em->getRepository('mycpBundle:generalReservation')->getByUser($id_client);
        $price = 0;
        $total_nights = array();

        if ($request->getMethod() == 'POST') {

            $post = $request->request->getIterator()->getArrayCopy();
            //var_dump($post); exit();
            foreach ($reservations as $reservation) {
                $res_db = $em->getRepository('mycpBundle:generalReservation')->find($reservation[0]['gen_res_id']);
                $res_db->setGenResStatus($post['resume_status_res_' . $reservation[0]['gen_res_id']]);
                $em->persist($res_db);

                $own_reservations = $em->getRepository('mycpBundle:ownershipReservation')->findBy(array('own_res_gen_res_id' => $reservation[0]['gen_res_id']));
                foreach ($own_reservations as $own_reservation) {
                    if (isset($post['service_room_type_' . $own_reservation->getOwnResId()])) {
                        $own_reservation->setOwnResRoomType($post['service_room_type_' . $own_reservation->getOwnResId()]);
                        $own_reservation->setOwnResCountAdults($post['service_room_count_adults_' . $own_reservation->getOwnResId()]);
                        $own_reservation->setOwnResCountChildrens($post['service_room_count_childrens_' . $own_reservation->getOwnResId()]);
                        $own_reservation->setOwnResNightPrice($post['service_room_price_' . $own_reservation->getOwnResId()]);
                        $own_reservation->setOwnResStatus($post['service_own_res_status_' . $own_reservation->getOwnResId()]);
                        $em->persist($own_reservation);
                    }
                }
            }
            $em->flush();
            $message = 'Reservas actualizadas satisfactoriamente.';

            /* $service_log= $this->get('log');
              $service_log->saveLog('Create entity for '.$ownership->getOwnMcpCode(), BackendModuleName::MODULE_RESERVATION); */

            $this->get('session')->getFlashBag()->add('message_ok', $message);
            return $this->redirect($this->generateUrl('mycp_details_client_reservation', array('id_client' => $id_client)));
        }

        $service_time = $this->get('time');
        foreach ($reservations as $reservation) {
            $temp_total_nights = 0;
            $owns_res = $em->getRepository('mycpBundle:ownershipReservation')->findBy(array('own_res_gen_res_id' => $reservation[0]['gen_res_id']));

            foreach ($owns_res as $own) {
                $array_dates = $service_time->datesBetween($own->getOwnResReservationFromDate()->getTimestamp(), $own->getOwnResReservationToDate()->getTimestamp());
                $temp_total_nights+=count($array_dates) - 1;
            }
            array_push($total_nights, $temp_total_nights);
        }
        return $this->render('mycpBundle:reservation:reservationDetailsClient.html.twig', array(
                    'total_nights' => $total_nights,
                    'reservations' => $reservations,
                    'client' => $client,
                    'errors' => ''
        ));
    }

    public function new_reservationAction(Request $request) {
        $service_security = $this->get('Secure');
        $service_security->verifyAccess();
        $data = array();
        $em = $this->getDoctrine()->getEntityManager();
        $role = $em->getRepository('mycpBundle:role')->findBy(array('role_name' => 'ROLE_CLIENT_TOURIST'));
        $post = $request->get('mycp_mycpbundle_reservationtype');
        $ownerships = $em->getRepository('mycpBundle:ownership')->findAll();
        $rooms = $em->getRepository('mycpBundle:room')->findBy(array('room_ownership' => $post['reservation_ownership'], 'room_active' => true));
        $users = $em->getRepository('mycpBundle:user')->findAll();
        $data['ownerships'] = $ownerships;
        $data['rooms'] = $rooms;
        $data['users'] = $users;

        $form = $this->createForm(new reservationType($data));

        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $em->getRepository('mycpBundle:ownershipReservation')->new_reservation($post);
                $message = 'Reserva añadida satisfactoriamente.';
                $ownership = $em->getRepository('mycpBundle:ownership')->find($post['reservation_ownership']);

                $service_log = $this->get('log');
                $service_log->saveLog('Create entity for ' . $ownership->getOwnMcpCode(), BackendModuleName::MODULE_RESERVATION);

                $this->get('session')->getFlashBag()->add('message_ok', $message);
                return $this->redirect($this->generateUrl('mycp_list_reservations'));
            }
        }
        return $this->render('mycpBundle:reservation:new.html.twig', array('form' => $form->createView(), 'role' => $role[0]));
    }

    public function details_reservation_partialAction($id_reservation) {
        $em = $this->getDoctrine()->getEntityManager();
        $reservation = new generalReservation();
        $reservation = $em->getRepository('mycpBundle:generalReservation')->find($id_reservation);
        $ownership_reservations = $em->getRepository('mycpBundle:ownershipReservation')->findBy(array('own_res_gen_res_id' => $id_reservation));

        $service_time = $this->get('time');

        $user = $em->getRepository('mycpBundle:userTourist')->findBy(array('user_tourist_user' => $reservation->getGenResUserId()));

        $rooms = array();
        $total_nights = array();
        foreach ($ownership_reservations as $res) {
            array_push($rooms, $em->getRepository('mycpBundle:room')->find($res->getOwnResSelectedRoomId()));
            $temp_total_nights = 0;
            $array_dates = $service_time->datesBetween($res->getOwnResReservationFromDate()->getTimestamp(), $res->getOwnResReservationToDate()->getTimestamp());
            $temp_total_nights+=count($array_dates) - 1;

            array_push($total_nights, $temp_total_nights);
        }

        return $this->render('mycpBundle:reservation:reservationDetailsPartial.html.twig', array(
                    'nights' => $total_nights,
                    'reservation' => $reservation,
                    'user' => $user,
                    'reservations' => $ownership_reservations,
                    'rooms' => $rooms,
                    'id_reservation' => $id_reservation
        ));
    }

    public function details_reservationAction($id_reservation, Request $request) {
        $em = $this->getDoctrine()->getManager();
        $reservation = $em->getRepository('mycpBundle:generalReservation')->find($id_reservation);
        $ownership_reservations = $em->getRepository('mycpBundle:ownershipReservation')->findBy(array('own_res_gen_res_id' => $id_reservation));
        $errors = array();

        $service_time = $this->get('time');
        $post = $request->request->getIterator()->getArrayCopy();
        $dates = $service_time->datesBetween($reservation->getGenResFromDate()->format('Y-m-d'), $reservation->getGenResToDate()->format('Y-m-d'));
        $not_available = true;
        $details_total = 0;
        $available_total = 0;
        $non_available_total = 0;
        if ($request->getMethod() == 'POST') {
            $keys = array_keys($post);

            foreach ($keys as $key) {
                $splitted = explode("_", $key);
                $own_res_id = $splitted[count($splitted) - 1];
                if (strpos($key, 'service_room_price') !== false) {

                    if (!is_numeric($post[$key])) {
                        $errors[$key] = 1;
                    }
                }
                if (strpos($key, 'service_own_res_status') !== false) {
                    $details_total++;
                    if ($post[$key] == ownershipReservation::STATUS_NOT_AVAILABLE) {
                        $non_available_total++;
                    } else if ($post[$key] == ownershipReservation::STATUS_AVAILABLE) {
                        $available_total++;
                    }
                }

                if (strpos($key, 'date_from') !== false) {
                    $start = explode('/', $post['date_from_' . $own_res_id]);
                    $end = explode('/', $post['date_to_' . $own_res_id]);
                    $start_timestamp = mktime(0, 0, 0, $start[1], $start[0], $start[2]);
                    $end_timestamp = mktime(0, 0, 0, $end[1], $end[0], $end[2]);

                    if ($start_timestamp > $end_timestamp)
                        $errors[$key] = 1;
                }
            }

            if (count($errors) == 0) {
                $temp_price = 0;
                foreach ($ownership_reservations as $ownership_reservation) {
                    if (isset($post['service_room_price_' . $ownership_reservation->getOwnResId()]) && $post['service_room_price_' . $ownership_reservation->getOwnResId()] != "" && $post['service_room_price_' . $ownership_reservation->getOwnResId()] != "0") {
                        $temp_price+=$post['service_room_price_' . $ownership_reservation->getOwnResId()] * (count($dates) - 1);
                        $ownership_reservation->setOwnResNightPrice($post['service_room_price_' . $ownership_reservation->getOwnResId()]);
                    }
                    else
                    //$temp_price+=$ownership_reservation->getOwnResTotalInSite();
                        $ownership_reservation->setOwnResCountAdults($post['service_room_count_adults_' . $ownership_reservation->getOwnResId()]);
                    $ownership_reservation->setOwnResCountChildrens($post['service_room_count_childrens_' . $ownership_reservation->getOwnResId()]);
                    $ownership_reservation->setOwnResStatus($post['service_own_res_status_' . $ownership_reservation->getOwnResId()]);
                    $ownership_reservation->setOwnResRoomType($post['service_room_type_' . $ownership_reservation->getOwnResId()]);


                    $start = explode('/', $post['date_from_' . $ownership_reservation->getOwnResId()]);
                    $end = explode('/', $post['date_to_' . $ownership_reservation->getOwnResId()]);
                    $start_timestamp = mktime(0, 0, 0, $start[1], $start[0], $start[2]);
                    $end_timestamp = mktime(0, 0, 0, $end[1], $end[0], $end[2]);

                    $ownership_reservation->setOwnResReservationFromDate(new \DateTime(date("Y-m-d H:i:s", $start_timestamp)));
                    $ownership_reservation->setOwnResReservationToDate(new \DateTime(date("Y-m-d H:i:s", $end_timestamp)));
                    $em->persist($ownership_reservation);
                }
                $message = 'Reserva actualizada satisfactoriamente.';
                $reservation->setGenResSaved(1);
                if ($reservation->getGenResStatus() != generalReservation::STATUS_RESERVED) {
                    if ($non_available_total > 0 && $non_available_total == $details_total) {
                        $reservation->setGenResStatus(generalReservation::STATUS_NOT_AVAILABLE);
                    } else if ($available_total > 0 && $available_total == $details_total) {
                        $reservation->setGenResStatus(generalReservation::STATUS_AVAILABLE);
                    } else if ($non_available_total > 0 && $available_total > 0)
                        $reservation->setGenResStatus(generalReservation::STATUS_PARTIAL_AVAILABLE);
                }
                $em->persist($reservation);
                $em->flush();
                $service_log = $this->get('log');
                $service_log->saveLog('Edit entity for CAS.' . $reservation->getGenResId(), BackendModuleName::MODULE_RESERVATION);

                $this->get('session')->getFlashBag()->add('message_ok', $message);
            }
        }

        $user = $em->getRepository('mycpBundle:userTourist')->findBy(array('user_tourist_user' => $reservation->getGenResUserId()));
        $array_nights = array();
        $rooms = array();
        foreach ($ownership_reservations as $res) {
            $dates_temp = $service_time->datesBetween($res->getOwnResReservationFromDate()->getTimestamp(), $res->getOwnResReservationToDate()->getTimestamp());
            array_push($rooms, $em->getRepository('mycpBundle:room')->find($res->getOwnResSelectedRoomId()));
            array_push($array_nights, count($dates_temp) - 1);
        }

        array_pop($dates);
        return $this->render('mycpBundle:reservation:reservationDetails.html.twig', array(
                    'post' => $post,
                    'errors' => $errors,
                    'reservation' => $reservation,
                    'user' => $user,
                    'reservations' => $ownership_reservations,
                    'rooms' => $rooms,
                    'nights' => $array_nights,
                    'id_reservation' => $id_reservation));
    }

    public function send_reservationAction($id_reservation) {
        $service_email = $this->get('Email');
        $service_time = $this->get('time');

        //send reserved reservations
        $em = $this->getDoctrine()->getManager();
        $generalReservation = $em->getRepository("mycpBundle:generalReservation")->find($id_reservation);
        $own_reservations = $em->getRepository('mycpBundle:ownershipReservation')->findBy(array('own_res_gen_res_id' => $id_reservation, 'own_res_status' => ownershipReservation::STATUS_RESERVED));

        $total_price = 0;
        if ($own_reservations) {

            $general_reservation = $own_reservations[0]->getOwnResGenResId();
            $general_reservation->setGenResStatus(generalReservation::STATUS_RESERVED);
            $general_reservation->setGenResStatusDate(new \DateTime());
            $em->persist($general_reservation);
            $user = $own_reservations[0]->getOwnResGenResId()->getGenResUserId();
            $user_tourist = $em->getRepository('mycpBundle:userTourist')->findOneBy(array('user_tourist_user' => $user->getUserId()));

            foreach ($own_reservations as $own_reservation) {
                $dates_temp = $service_time->datesBetween($own_reservation->getOwnResReservationFromDate()->getTimestamp(), $own_reservation->getOwnResReservationToDate()->getTimestamp());
                $total_price+=$own_reservation->getOwnResNightPrice() * (count($dates_temp) - 1);
            }

            $total_price = $total_price * $own_reservations[0]->getOwnResGenResId()->getGenResOwnId()->getOwnCommissionPercent() / 100;
            $configuration_service_fee = $this->container->getParameter('configuration.service.fee');
            $prepay = ($total_price + $configuration_service_fee) * $user_tourist->getUserTouristCurrency()->getCurrCucChange();
            $booking = new booking();
            $booking->setBookingCurrency($user_tourist->getUserTouristCurrency());
            $booking->setBookingPrepay($prepay);
            $booking->setBookingUserDates($user_tourist->getUserTouristUser()->getUserUserName() . ", " . $user_tourist->getUserTouristUser()->getUserEmail());
            $booking->setBookingCancelProtection(0);
            $booking->setBookingUserId($user_tourist->getUserTouristUser()->getUserId());
            $em->persist($booking);

            $array_photos = array();
            $array_ownres_by_house = array();
            $service_time = $this->get('time');
            $array_houses_ids = array();
            $array_nigths = array();
            $array_houses = array();

            foreach ($own_reservations as $own_reservation) {
                $own_reservation->setOwnResReservationBooking($booking);
                $em->persist($own_reservation);

                $photos = $em->getRepository('mycpBundle:ownership')->getPhotos($own_reservation->getOwnResGenResId()->getGenResOwnId()->getOwnId());
                array_push($array_photos, $photos);
                $array_dates = $service_time->datesBetween($own_reservation->getOwnResReservationFromDate()->getTimestamp(), $own_reservation->getOwnResReservationToDate()->getTimestamp());
                array_push($array_nigths, count($array_dates) - 1);
                $array_nigths_by_ownres[$own_reservation->getOwnResId()] = count($array_dates) - 1;

                $insert = true;
                foreach ($array_houses_ids as $item) {
                    if ($own_reservation->getOwnResGenResId()->getGenResOwnId()->getOwnId() == $item) {
                        $insert = false;
                    }
                }

                if ($insert) {
                    array_push($array_houses_ids, $own_reservation->getOwnResGenResId()->getGenResOwnId()->getOwnId());
                    array_push($array_houses, $own_reservation->getOwnResGenResId()->getGenResOwnId());
                }
                if (isset($array_ownres_by_house[$own_reservation->getOwnResGenResId()->getGenResOwnId()->getOwnId()]))
                    $temp_array = $array_ownres_by_house[$own_reservation->getOwnResGenResId()->getGenResOwnId()->getOwnId()];
                else
                    $temp_array = array();

                array_push($temp_array, $own_reservation);
                $array_ownres_by_house[$own_reservation->getOwnResGenResId()->getGenResOwnId()->getOwnId()] = $temp_array;
            }
            $em->flush();

            $response = $this->view_confirmation($booking->getBookingId());
            $user_locale = $user_tourist->getUserTouristLanguage()->getLangCode();
            $pdf_name = 'voucher' . $user_tourist->getUserTouristUser()->getUserId() . '_' . $booking->getBookingId();
            $this->download_pdf($response, $pdf_name, true);
            $attach = "http://" . $_SERVER['HTTP_HOST'] . "/web/vouchers/$pdf_name.pdf";

            // Enviando mail al cliente
            $service_email = $this->get('Email');


            $body = $this->render('FrontEndBundle:mails:email_offer_available.html.twig', array(
                'booking' => $booking->getBookingId(),
                'user' => $user_tourist->getUserTouristUser(),
                'reservations' => $own_reservations,
                'photos' => $array_photos,
                'nights' => $array_nigths,
                'user_locale' => $user_locale,
                'user_currency' => ($user_tourist != null) ? $user_tourist->getUserTouristCurrency() : null,
                'reservationStatus' => $generalReservation->getGenResStatus()
            ));

            $locale = $this->get('translator');
            $subject = $locale->trans('PAYMENT_CONFIRMATION', array(), "messages", $user_locale);
            $service_email->sendEmail(
                    $subject, 'reservation1@mycasaparticular.com', $subject . ' - MyCasaParticular.com', $user_tourist->getUserTouristUser()->getUserEmail(), $body, $attach
            );


            // enviando mail a reservation team
            $payment_pending = 0;
            $temp_tourist[0] = $user_tourist;
            foreach ($array_ownres_by_house as $owns) {

                $body_res = $this->render('FrontEndBundle:mails:rt_payment_confirmation.html.twig', array(
                    'user' => $user_tourist->getUserTouristUser(),
                    'user_tourist' => $temp_tourist,
                    'reservations' => $owns,
                    'nights' => $array_nigths_by_ownres,
                    'payment_pending' => $payment_pending
                ));
                $service_email->sendEmail(
                        'Confirmación de pago', 'no-reply@mycasaparticular.com', 'MyCasaParticular.com', 'reservation@mycasaparticular.com', $body_res
                );
            }

            // enviando mail al propietario
            foreach ($array_ownres_by_house as $owns) {
                $body_prop = $this->render('FrontEndBundle:mails:email_house_confirmation.html.twig', array(
                    'user' => $user_tourist->getUserTouristUser(),
                    'user_tourist' => $temp_tourist,
                    'reservations' => $owns,
                    'nights' => $array_nigths_by_ownres
                ));
                $prop_email = $owns[0]->getOwnResGenResId()->getGenResOwnId()->getOwnEmail1();
                if ($prop_email)
                    $service_email->sendEmail(
                            'Confirmación de reserva', 'no-reply@mycasaparticular.com', 'MyCasaParticular.com', $prop_email, $body_prop
                    );
            }
        }
        else {
            $custom_message = $this->getRequest()->get('message_to_client');
            if (isset($custom_message[0]))
                $custom_message[0] = strtoupper($custom_message[0]);
            $service_email->sendReservation($id_reservation, $custom_message);

            // inform listeners that a reservation was sent out
            $dispatcher = $this->get('event_dispatcher');
            $eventData = new GeneralReservationJobData($id_reservation);
            $dispatcher->dispatch('mycp.event.reservation.sent_out', new JobEvent($eventData));
        }

        $message = 'Reserva enviada satisfactoriamente';
        $this->get('session')->getFlashBag()->add('message_ok', $message);
        return $this->redirect($this->generateUrl('mycp_details_reservation', array('id_reservation' => $id_reservation)));
    }

    public function edit_reservationAction($id_reservation, Request $request) {
        $service_security = $this->get('Secure');
        $service_security->verifyAccess();
        $em = $this->getDoctrine()->getEntityManager();
        $reservation = $em->getRepository('mycpBundle:ownershipReservation')->get_reservation_by_id($id_reservation);

        $user_id = $reservation[0]['own_res_gen_res_id']['gen_res_user_id']['user_id'];
        $reservations_user = $em->getRepository('mycpBundle:ownershipReservation')->get_reservations_by_id_user($user_id);

        $data['total'] = 0;
        $data['post'] = 0;
        $errors = array();
        foreach ($reservations_user as $reser) {

            $dif = $reser['own_res_reservation_from_date']->diff($reser['own_res_reservation_to_date']);
            $dif = $dif->format('%r%a');
            $data['total']+= $reser['own_res_night_price'] * ($dif - 1);
        }
//        $currency_change=$reservation[0]['own_res_gen_res_id']['gen_res_user_id']['user_currency']['curr_cuc_change'];
        $currency_change = 25;
        $data['total_cuc'] = $data['total'] / $currency_change;
        $service_cost = $em->getRepository('mycpBundle:config')->findAll();
        $data['service_cost_cuc'] = $service_cost[0]->getConfServiceCost();
        $data['service_cost'] = $data['service_cost_cuc'] * $currency_change;

        $data['total_neto_cuc'] = $data['total_cuc'] + $data['service_cost_cuc'];
        $data['total_neto'] = $data['total_neto_cuc'] * $currency_change;

        $data['commission_percent_cuc'] = $data['total_cuc'] * $reservation[0]['own_res_commission_percent'] / 100;
        $data['commission_percent'] = $data['commission_percent_cuc'] * $currency_change;

        $data['avance_total_cuc'] = $data['service_cost_cuc'] + $data['commission_percent_cuc'];
        $data['avance_total'] = $data['avance_total_cuc'] * $currency_change;

        $data['pay_cuba_cuc'] = $data['total_cuc'] - $data['avance_total_cuc'];
        $data['pay_cuba'] = $data['pay_cuba_cuc'] * $currency_change;


        $post = $request->request->getIterator()->getArrayCopy();
        $post['selected_room'] = $reservation[0]['own_res_selected_room'];
        if ($request->getMethod() == 'POST') {
            $data['ownership'] = $request->get('ownership');
            $post['selected_room'] = $request->get('selected_room');
            $not_blank_validator = new NotBlank();
            $not_blank_validator->message = "Este campo no puede estar vacío.";
            $array_keys = array_keys($post);
            $count = $errors_validation = $count_errors = 0;
            foreach ($post as $item) {
                $errors[$array_keys[$count]] = $errors_validation = $this->get('validator')->validateValue($item, $not_blank_validator);
                if ($array_keys[$count] != 'percent')
                    $count_errors+=count($errors_validation);
                $count++;
            }

            if ($count_errors == 0) {
                $em->getRepository('mycpBundle:ownershipReservation')->edit_reservation($reservation[0], $post);
                $message = 'Reserva actualizada satisfactoriamente.';
                $ownership = $em->getRepository('mycpBundle:ownership')->find($post['ownership']);

                $service_log = $this->get('log');
                $service_log->saveLog('Edit entity for ' . $ownership->getOwnMcpCode(), BackendModuleName::MODULE_RESERVATION);

                $this->get('session')->getFlashBag()->add('message_ok', $message);
                return $this->redirect($this->generateUrl('mycp_edit_reservation', array('id_reservation' => $id_reservation)));
            }
        } else {
            $data['ownership'] = $reservation[0]['own_res_own_id']['own_id'];
            $post['percent'] = $reservation[0]['own_res_commission_percent'];
        }

        $reservation = $reservation[0];
        return $this->render('mycpBundle:reservation:reservationEdit.html.twig', array('errors' => $errors, 'data' => $data, 'reservation' => $reservation, 'id_reservation' => $id_reservation, 'post' => $post));
    }

    public function get_ownershipsAction($data) {
        $em = $this->getDoctrine()->getEntityManager();
        $ownerships = $em->getRepository('mycpBundle:ownership')->findAll();
        return $this->render('mycpBundle:utils:ownerships.html.twig', array('ownerships' => $ownerships, 'data' => $data));
    }

    public function get_percent_listAction($post) {
        $selected = '';
        if (isset($post['percent']))
            $selected = $post['percent'];
        return $this->render('mycpBundle:utils:percent.html.twig', array('selected' => $selected));
    }

    public function get_numeric_listAction($post) {
        $selected = '';
        if (isset($post['selected']))
            $selected = $post['selected'];
        return $this->render('mycpBundle:utils:range_max_4_no_0.html.twig', array('selected' => $selected));
    }

    public function get_numeric_list_0Action($post) {
        $selected = '';
        if (isset($post['selected']))
            $selected = $post['selected'];
        return $this->render('mycpBundle:utils:range_max_4.html.twig', array('selected' => $selected));
    }

    public function get_reservation_statusAction($post) {
        $selected = '';
        if (isset($post['selected']))
            $selected = $post['selected'];
        return $this->render('mycpBundle:utils:reservation_status.html.twig', array('selected' => $selected));
    }

    public function get_general_reservation_statusAction($post) {
        $selected = '';
        if (isset($post['selected']))
            $selected = $post['selected'];
        return $this->render('mycpBundle:utils:general_reservation_status.html.twig', array('selected' => $selected));
    }

    public function reservationStatusNameAction($status) {
        return $this->render('mycpBundle:utils:reservation_status_name.html.twig', array('status' => $status));
    }

    public function getGeneralReservationStatusNameAction($status, $showInDiv = true, $wrap = true) {
        return $this->render('mycpBundle:utils:general_reservation_status_name.html.twig', array('status' => $status, 'wrap' => $wrap, 'showInDiv' => $showInDiv));
    }

    public function get_rooms_by_ownershipAction($id_ownership, $selected_room) {
        $em = $this->getDoctrine()->getEntityManager();
        $rooms = $em->getRepository('mycpBundle:room')->findBy(array('room_ownership' => $id_ownership, "room_active" => true));
        return $this->render('mycpBundle:utils:rooms.html.twig', array('rooms' => $rooms, 'selected_room' => $selected_room));
    }

    public function delete_reservationAction($id_reservation) {
        $em = $this->getDoctrine()->getEntityManager();
        $reservation = $em->getRepository('mycpBundle:ownershipReservation')->find($id_reservation);
        $ownership = $em->getRepository('mycpBundle:ownership')->find($reservation->getOwnResOwnId());
        $em->remove($reservation->getOwnResGenResId());
        $em->remove($reservation);
        $em->flush();
        $message = 'Reserva eliminada satisfactoriamente.';

        $service_log = $this->get('log');
        $service_log->saveLog('Delete entity for ' . $ownership->getOwnMcpCode(), BackendModuleName::MODULE_RESERVATION);

        $this->get('session')->getFlashBag()->add('message_ok', $message);
        return $this->redirect($this->generateUrl('mycp_list_reservations'));
    }

    public function setNotAvailableCallbackAction() {
        $request = $this->getRequest();
        $em = $this->getDoctrine()->getManager();
        $reservations_ids = $request->request->get('reservations_ids');
        $response = null;

        //Modificar el estado
        $em->getRepository('mycpBundle:generalReservation')->setAsNotAvailable($reservations_ids);

        //Enviar por correo a los clientes
        $service_email = $this->get('Email');

        try {
            foreach ($reservations_ids as $genResId) {
                $service_email->sendReservation($genResId, null, false);

                // inform listeners that a reservation was sent out
                $dispatcher = $this->get('event_dispatcher');
                $eventData = new GeneralReservationJobData($genResId);
                $dispatcher->dispatch('mycp.event.reservation.sent_out', new JobEvent($eventData));
            }

            $message = 'Se han modificado ' . count($reservations_ids) . ' reservaciones como No Disponibles y se ha notificado a los clientes respectivos. Ambas operaciones fueron satisfactorias.';
            $this->get('session')->getFlashBag()->add('message_ok', $message);

            $response = $this->generateUrl('mycp_list_reservations');
        } catch (\Exception $e) {
            $message = 'Los correos no pudieron ser enviados.';
            $this->get('session')->getFlashBag()->add('message_error_local', $message);
            $response = "ERROR";
        }
        return new Response($response);
    }

    public function sendVoucherToReservationTeamAction($id_reservation) {
        try {
        $em = $this->getDoctrine()->getManager();
        $bookingService = $this->get('front_end.services.booking');
        $service_email = $this->get('mycp.service.email_manager');
        $emailToSend = 'reservation@mycasaparticular.com';

         \MyCp\mycpBundle\Helpers\VoucherHelper::sendVoucher($em, $bookingService, $service_email, $this, $id_reservation, $emailToSend);
        } catch (\Exception $e) {
            $message = 'Error al enviar el voucher asociado a la reservación CAS.' . $id_reservation. ". ".$e->getMessage();
            $this->get('session')->getFlashBag()->add('message_error_main', $message);
        }

        return $this->redirect($this->generateUrl('mycp_list_reservations'));

    }

    function getLodgingSortByAction($sort_by) {
        $selected = '';
        if (isset($sort_by))
            $selected = $sort_by;
        return $this->render('mycpBundle:utils:lodging_reservation_sort_by.html.twig', array('selected' => $selected));
    }

    function getClientSortByAction($sort_by) {
        $selected = '';
        if (isset($sort_by))
            $selected = $sort_by;
        return $this->render('mycpBundle:utils:reservation_client_sort_by.html.twig', array('selected' => $selected));
    }

    function get_sort_byAction($sort_by) {
        $selected = '';
        if (isset($sort_by))
            $selected = $sort_by;
        return $this->render('mycpBundle:utils:reservation_sort_by.html.twig', array('selected' => $selected));
    }

    function view_confirmation($id_booking) {
        $bookingService = $this->get('front_end.services.booking');
        return $bookingService->getPrintableBookingConfirmationResponse($id_booking);
    }

    function download_pdf($html, $name, $save_to_disk = false, $id_booking = null) {

        require_once($this->get('kernel')->getRootDir() . '/config/dompdf_config.inc.php');
        $dompdf = new \DOMPDF();
        $dompdf->load_html($html);
        $dompdf->set_paper("a4");
        $dompdf->render();

        if ($save_to_disk == true) {
            $content_out = $dompdf->output();
            $fpdf = fopen("vouchers/$name.pdf", 'w');
            fwrite($fpdf, $content_out);
            fclose($fpdf);
        }
        else
            $dompdf->stream($name . ".pdf", array("Attachment" => false));
    }

}

