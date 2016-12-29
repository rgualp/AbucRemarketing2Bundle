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
use Symfony\Component\HttpFoundation\JsonResponse;

class BackendReservationController extends Controller {

    public function new_offerAction($id_tourist, $id_reservation) {

        $em = $this->getDoctrine()->getManager();
        $service_time = $this->get('time');
        $service_log = $this->get('log');
        $reservationService = $this->get('mycp.reservation.service');
        $user = $em->getRepository('mycpBundle:user')->find($id_tourist);
        $tourist = $em->getRepository('mycpBundle:userTourist')->findOneBy(array("user_tourist_user" => $id_tourist));
        $gen_res = $em->getRepository('mycpBundle:generalReservation')->find($id_reservation);
        $reservations = $em->getRepository('mycpBundle:ownershipReservation')->findBy(array("own_res_gen_res_id" => $id_reservation));
        $bookings = $em->getRepository("mycpBundle:booking")->getByGeneralReservation($id_reservation);
        $ownership = $gen_res->getGenResOwnId();
        $resRooms = array();

        foreach ($reservations as $res) {
            $room = $em->getRepository("mycpBundle:room")->find($res->getOwnResSelectedRoomId());
            array_push($resRooms, $room);
        }

        $ownerships = $em->getRepository('mycpBundle:ownership')->getSimilars($ownership, $resRooms);
        $array_nights = array();
        $rooms = array();
        $payment = 0;
        foreach ($reservations as $res) {
            $nights = $service_time->nights($res->getOwnResReservationFromDate()->getTimestamp(), $res->getOwnResReservationToDate()->getTimestamp());
            array_push($rooms, $em->getRepository('mycpBundle:room')->find($res->getOwnResSelectedRoomId()));
            array_push($array_nights, $nights);

            $payment += $res->getOwnResTotalInSite() - $res->getOwnResTotalInSite() * $res->getOwnResGenResId()->getGenResOwnId()->getOwnCommissionPercent() / 100;
        }

        if($this->getRequest()->getMethod() == 'POST') {
            $request = $this->getRequest();

            if(count($bookings) > 0) {
                $booking = $em->getRepository("mycpBundle:booking")->find($bookings[0]["booking_id"]);
                $resultReservations = $reservationService->createReservedOfferFromRequest($request, $user, $booking);
                $newReservations = $resultReservations['reservations'];
                $arrayNightsByOwnershipReservation = $resultReservations['nights'];
                $general_reservation = $resultReservations['generalReservation'];

                $serviceFee = $gen_res->getServiceFee();
                //var_dump($serviceFee->getId()); die;

                $general_reservation->setServiceFee($serviceFee);

                //Deleting booking association from cancelled and payed reservation
                foreach ($reservations as $res) {
                    $res->setOwnResReservationBooking(null);
                    $em->persist($res);
                }
                $em->flush();

                try {
                    $emailService = $this->get('mycp.service.email_manager');
                    //Enviar correo al cliente con el texto escrito y el voucher como adjunto
                    $postMessageBody = $request->get("message_body");
                    if($postMessageBody !== "") {
                        $from = $this->getUser();
                        $to = $general_reservation->getGenResUserId();
                        $subject = "Reservación " . $general_reservation->getCASId();

                        $message = $em->getRepository("mycpBundle:message")->insert($from, $to, $subject, $postMessageBody);
                        if($message != null)
                            $service_log->saveLog($message->getLogDescription(), BackendModuleName::MODULE_CLIENT_MESSAGES, log::OPERATION_INSERT, DataBaseTables::MESSAGE);
                    }

                    $mailMessage = ($postMessageBody != null && $postMessageBody != "") ? $postMessageBody : null;
                    $bookingService = $this->get('front_end.services.booking');
                    VoucherHelper::sendVoucherToClient($em, $bookingService, $emailService, $this, $general_reservation, 'NEW_OFFER_SUBJECT', $mailMessage, true);

                    //Enviar correo al equipo de reservación
                    Reservation::sendNewOfferToTeam($this, $emailService, $tourist, $newReservations, $arrayNightsByOwnershipReservation, $gen_res);
                }
                catch (\Exception $e) {
                    $this->get('session')->getFlashBag()->add('message_error_local', $e->getMessage());
                }

                $message = 'Nueva oferta ' . $general_reservation->getCASId() . ' creada satisfactoriamente.';
                $this->get('session')->getFlashBag()->add('message_ok', $message);

                $service_log->saveLog($general_reservation->getLogDescription() . " (Nueva oferta)", BackendModuleName::MODULE_RESERVATION, log::OPERATION_INSERT, DataBaseTables::GENERAL_RESERVATION);
                $service_log->saveNewOfferLog($general_reservation, $gen_res, false);

                return $this->redirect($this->generateUrl('mycp_details_reservation', array('id_reservation' => $general_reservation->getGenResId())));

            }
            else {
                $message = 'Solo se puede ofrecer una nueva oferta si la reservación original está cancelada y tiene algún pago asociado.';
                $this->get('session')->getFlashBag()->add('message_error_local', $message);
            }
        }

        if($gen_res->getGenResStatus() != generalReservation::STATUS_CANCELLED || count($bookings) == 0) {
            $message = 'Solo se puede ofrecer una nueva oferta si la reservación original está cancelada y tiene algún pago asociado.';
            $this->get('session')->getFlashBag()->add('message_error_local', $message);
        }
        $currentServiceFee = $gen_res->getServiceFee();

        return $this->render('mycpBundle:reservation:new_offer.html.twig', array(
            'reservation' => $gen_res,
            'id_tourist' => $id_tourist,
            'user' => $user,
            'tourist' => $tourist,
            'ownerships' => $ownerships,
            'bookings' => $bookings,
            'reservations' => $reservations,
            'rooms' => $rooms,
            'nights' => $array_nights,
            'payment' => $payment,
            'currentServiceFee' => $currentServiceFee
        ));
    }

    public function changeDatesAction($id_tourist, $id_reservation, Request $request) {
        $em = $this->getDoctrine()->getManager();
        $reservation = $em->getRepository('mycpBundle:generalReservation')->find($id_reservation);
        $ownership_reservations = $em->getRepository('mycpBundle:ownershipReservation')->findBy(array('own_res_gen_res_id' => $id_reservation));
        $errors = array("numeric_price" => 0, "price" => 0, "date" => 0);
        $post = $request->request->getIterator()->getArrayCopy();
        $message = "";

        if($request->getMethod() == 'POST') {

            $keys = array_keys($post);
            $existError = false;

            foreach ($keys as $key) {
                $splitted = explode("_", $key);
                $resId = $splitted[count($splitted) - 1];
                if(strpos($key, 'service_room_price') !== false) {

                    if(!is_numeric($post[$key]) && !$errors["numeric_price"]) {
                        $errors["numeric_price"] = 1;
                        $message .= 'En el campo precio por noche tiene que introducir un valor numérico.<br/>';
                        $existError = true;
                    }
                    else if($post[$key] != "") {
                        $reservationPrice = $post["price_" . $resId];

                        if($post[$key] != 0 && $post[$key] != $reservationPrice && !$errors["price"]) {
                            $errors["price"] = 1;
                            $message .= 'El precio por noche tiene que ser igual al sugerido.<br/>';
                            $existError = true;
                        }
                    }
                }
                if(strpos($key, 'date_from') !== false) {
                    $originalDate = $post["original_date_" . $resId];

                    if($post[$key] == $originalDate && !$errors["date"]) {
                        $errors["date"] = 1;
                        $message .= 'La fecha no puede ser la misma de la reservación. <br/>';
                        $existError = true;
                    }
                }
            }

            if(!$existError) {
                $newGeneralReservation = $reservation->getClone();
                $newGeneralReservation->setGenResStatus(generalReservation::STATUS_RESERVED);
                $newGeneralReservation->setGenResDate(new \DateTime());
                $newGeneralReservation->setModified(new \DateTime());
                $newGeneralReservation->setModifiedBy($this->getUser());
                $em->persist($newGeneralReservation);
                $em->flush();

                $temp_price = 0;
                $fromDate = null;
                $toDate = null;
                $newReservations = array();
                $ownNights = array();
                foreach ($ownership_reservations as $ownership_reservation) {

                    if($post['service_room_price_' . $ownership_reservation->getOwnResId()] != 0 && $post["price_" . $ownership_reservation->getOwnResId()] != $post['service_room_price_' . $ownership_reservation->getOwnResId()]) {
                        $errors['service_room_price_' . $ownership_reservation->getOwnResId()] = 1;
                        $message = 'El precio por noche tiene que coincidir con el sugerido.';
                        $this->get('session')->getFlashBag()->add('message_error_local', $message);
                        $em->remove($newGeneralReservation);
                        $em->flush();
                    }
                    else {

                        $start = explode('/', $post['date_from_' . $ownership_reservation->getOwnResId()]);
                        $nights = $post['nights_' . $ownership_reservation->getOwnResId()];
                        $start_timestamp = mktime(0, 0, 0, $start[1], $start[0], $start[2]);
                        $end_timestamp = strtotime("+" . $nights . " day", $start_timestamp);

                        $newOwnRes = $ownership_reservation->getClone();
                        $newOwnRes->setOwnResGenResId($newGeneralReservation);
                        $newOwnRes->setOwnResStatus(ownershipReservation::STATUS_RESERVED);

                        $newOwnRes->setOwnResReservationFromDate(new \DateTime(date("Y-m-d H:i:s", $start_timestamp)));
                        $newOwnRes->setOwnResReservationToDate(new \DateTime(date("Y-m-d H:i:s", $end_timestamp)));

                        if(isset($post['service_room_price_' . $ownership_reservation->getOwnResId()]) && $post['service_room_price_' . $ownership_reservation->getOwnResId()] != "" && $post['service_room_price_' . $ownership_reservation->getOwnResId()] != "0") {
                            $temp_price += $post['service_room_price_' . $ownership_reservation->getOwnResId()] * $nights;
                            $newOwnRes->setOwnResNightPrice($post['service_room_price_' . $ownership_reservation->getOwnResId()]);
                        }

                        if($fromDate == null || $newOwnRes->getOwnResReservationFromDate() < $fromDate)
                            $fromDate = $newOwnRes->getOwnResReservationFromDate();
                        if($toDate == null || $newOwnRes->getOwnResReservationToDate() > $toDate)
                            $toDate = $newOwnRes->getOwnResReservationToDate();

                        $em->persist($newOwnRes);
                        array_push($newReservations, $newOwnRes);

                        $em->flush();

                        $ownership_reservation->setOwnResReservationBooking(null);
                        $em->persist($ownership_reservation);
                    }
                }

                $newGeneralReservation->setGenResSaved(1);
                $newGeneralReservation->setGenResFromDate($fromDate);
                $newGeneralReservation->setGenResToDate($toDate);

                $em->persist($newGeneralReservation);
                $em->flush();

                $time = $this->get("Time");
                foreach ($newReservations as $nReservation) {
                    $ownNights[$nReservation->getOwnResId()] = $time->nights($nReservation->getOwnResReservationFromDate()->getTimestamp(), $nReservation->getOwnResReservationToDate()->getTimestamp());
                }

                $service_log = $this->get('log');
                $service_log->saveLog($reservation->getLogDescription() . " (Nueva oferta por cambio de fechas)", BackendModuleName::MODULE_RESERVATION, log::OPERATION_INSERT, DataBaseTables::GENERAL_RESERVATION);
                $service_log->saveNewOfferLog($newGeneralReservation, $reservation, true);

                //Enviar correo al cliente con el texto escrito y el voucher como adjunto
                try {
                    $mailMessage = ($post["message_body"] != null && $post["message_body"] != "") ? $post["message_body"] : null;
                    $bookingService = $this->get('front_end.services.booking');
                    $emailService = $this->get('mycp.service.email_manager');
                    \MyCp\mycpBundle\Helpers\VoucherHelper::sendVoucherToClient($em, $bookingService, $emailService, $this, $newGeneralReservation, 'NEW_OFFER_SUBJECT', $mailMessage, true);

                    //Enviar correo al equipo de reserva
                    $user = $newGeneralReservation->getGenResUserId();
                    $userTourist = $em->getRepository('mycpBundle:userTourist')->findOneBy(array('user_tourist_user' => $user));
                    Reservation::sendNewOfferToTeam($this, $emailService, $userTourist, $newReservations, $ownNights, $reservation);
                }
                catch (\Exception $e) {
                    $this->get('session')->getFlashBag()->add('message_error_local', $e->getMessage());
                }

                $message = 'Nueva oferta ' . $newGeneralReservation->getCASId() . ' creada satisfactoriamente.';
                $this->get('session')->getFlashBag()->add('message_ok', $message);
                return $this->redirect($this->generateUrl('mycp_details_reservation', array('id_reservation' => $newGeneralReservation->getGenResId())));
            }
            else {
                $this->get('session')->getFlashBag()->add('message_error_local', $message);
                return $this->redirect($this->generateUrl('mycp_new_offer_reservation', array("id_tourist" => $id_tourist, "id_reservation" => $id_reservation)));
            }
        }
    }

    public function list_reservationsAction($items_per_page, Request $request) {
        try {
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
            if($request->getMethod() == 'POST' && $filter_date_reserve == 'null' && $filter_offer_number == 'null' && $filter_reference == 'null' &&
                $filter_date_from == 'null' && $filter_date_to == 'null' && $sort_by == 'null' && $filter_booking_number == 'null' && $filter_status == 'null'
            ) {
                $message = 'Debe llenar al menos un campo para filtrar.';
                $this->get('session')->getFlashBag()->add('message_error_local', $message);
                return $this->redirect($this->generateUrl('mycp_list_reservations'));
            }

            if($filter_date_reserve == 'null')
                $filter_date_reserve = '';
            if($filter_offer_number == 'null')
                $filter_offer_number = '';
            if($filter_booking_number == 'null')
                $filter_booking_number = '';
            if($filter_reference == 'null')
                $filter_reference = '';
            if($filter_date_from == 'null')
                $filter_date_from = '';
            if($filter_date_to == 'null')
                $filter_date_to = '';
            if($filter_status == 'null')
                $filter_status = '';
            if($sort_by == 'null')
                $sort_by = '';

            if(isset($_GET['page']))
                $page = $_GET['page'];
            $filter_date_reserve = str_replace('_', '/', $filter_date_reserve);
            $filter_date_from = str_replace('_', '/', $filter_date_from);
            $filter_date_to = str_replace('_', '/', $filter_date_to);

            $em = $this->getDoctrine()->getManager();
            $paginator = $this->get('ideup.simple_paginator');
            $paginator->setItemsPerPage($items_per_page);

            $reservations = $paginator->paginate($em->getRepository('mycpBundle:generalReservation')
                ->getAll($filter_date_reserve, $filter_offer_number, $filter_reference, $filter_date_from, $filter_date_to, $sort_by, $filter_booking_number, $filter_status, $items_per_page, $page, false))->getResult();
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

            $totalItems = $em->getRepository("mycpBundle:generalReservation")->getTotalReservations($filter_date_reserve, $filter_offer_number, $filter_reference, $filter_date_from, $filter_date_to, $filter_booking_number, $filter_status);
            return $this->render('mycpBundle:reservation:list.html.twig', array(
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
        catch (\Exception $e) {
            $message = 'Ha ocurrido un error. Por favor, introduzca correctamente los valores para filtrar.';
            $this->get('session')->getFlashBag()->add('message_error_main', $message);

            return $this->redirect($this->generateUrl("mycp_list_reservations"));
        }
    }

    public function list_reservations_bookingAction($items_per_page, Request $request) {
        $service_security = $this->get('Secure');
        $service_security->verifyAccess();

        $filter_booking_number = $request->get('filter_booking_number');
        $filter_date_booking = $request->get('filter_date_booking');
        $filter_user_booking = $request->get('filter_user_booking');
        $filter_arrive_date_booking = $request->get('filter_arrive_date_booking');
        $filter_reservation = $request->get("filter_reservation");
        $filter_ownership = $request->get("filter_ownership");
        $filter_currency = $request->get("filter_currency");

        if($request->getMethod() == 'POST' && $filter_booking_number == 'null' && $filter_date_booking == 'null' && $filter_user_booking == 'null' && $filter_arrive_date_booking == 'null' && $filter_reservation == 'null' && $filter_ownership == 'null' && $filter_currency == 'null') {
            $message = 'Debe llenar al menos un campo para filtrar.';
            $this->get('session')->getFlashBag()->add('message_error_local', $message);
            return $this->redirect($this->generateUrl('mycp_list_reservations_booking'));
        }

        if($filter_booking_number == 'null')
            $filter_booking_number = '';
        if($filter_date_booking == 'null')
            $filter_date_booking = '';
        if($filter_user_booking == 'null')
            $filter_user_booking = '';
        if($filter_arrive_date_booking == 'null')
            $filter_arrive_date_booking = '';
        if($filter_reservation == 'null')
            $filter_reservation = '';
        if($filter_currency == 'null')
            $filter_currency = '';

        if($filter_ownership == 'null')
            $filter_ownership = '';

        $page = 1;
        if(isset($_GET['page']))
            $page = $_GET['page'];
        $em = $this->getDoctrine()->getManager();
        $paginator = $this->get('ideup.simple_paginator');
        $paginator->setItemsPerPage($items_per_page);

        $bookings = $paginator->paginate($em->getRepository('mycpBundle:generalReservation')
            ->getAllBookings($filter_booking_number, $filter_date_booking, $filter_user_booking, $filter_arrive_date_booking, $filter_reservation, $filter_ownership, $filter_currency, "", false))->getResult();
//        $service_log = $this->get('log');
//        $service_log->saveLog('Visit', BackendModuleName::MODULE_RESERVATION);

        $filter_date_booking = str_replace('_', '/', $filter_date_booking);
        $filter_arrive_date_booking = str_replace('_', '/', $filter_arrive_date_booking);

        return $this->render('mycpBundle:reservation:list_booking.html.twig', array(
            "bookings" => $bookings,
            'items_per_page' => $items_per_page,
            'current_page' => $page,
            'filter_booking_number' => $filter_booking_number,
            'filter_date_booking' => $filter_date_booking,
            'filter_user_booking' => $filter_user_booking,
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
        $user = $em->getRepository('mycpBundle:userTourist')->findOneBy(array('user_tourist_user' => $payment->getBooking()->getBookingUserId()));
        $reservations = $em->getRepository('mycpBundle:ownershipReservation')->findBy(array('own_res_reservation_booking' => $id_booking, 'own_res_status' => ownershipReservation::STATUS_RESERVED), array('own_res_gen_res_id' => 'ASC'));
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
        if($request->getMethod() == 'POST' && ($sort_by == "" || $sort_by == "null" || $sort_by == "0") && $filter_user_name == 'null' && $filter_user_email == 'null' && $filter_user_city == 'null' &&
            $filter_user_country == 'null'
        ) {
            $message = 'Debe llenar al menos un campo para filtrar o seleccionar un criterio de ordenación.';
            $this->get('session')->getFlashBag()->add('message_error_local', $message);
            return $this->redirect($this->generateUrl('mycp_list_reservations_user'));
        }
        if($filter_user_name == 'null')
            $filter_user_name = '';
        if($filter_user_email == 'null')
            $filter_user_email = '';
        if($filter_user_city == 'null')
            $filter_user_city = '';
        if($filter_user_country == 'null')
            $filter_user_country = '';
        if($sort_by == 'null')
            $sort_by = '';

        if(isset($_GET['page']))
            $page = $_GET['page'];

        $em = $this->getDoctrine()->getManager();
        $paginator = $this->get('ideup.simple_paginator');
        $paginator->setItemsPerPage($items_per_page);
        $reservations = $paginator->paginate($em->getRepository('mycpBundle:generalReservation')
            ->getUsers($filter_user_name, $filter_user_email, $filter_user_city, $filter_user_country, $sort_by))->getResult();

//        $service_log = $this->get('log');
//        $service_log->saveLog('Visit', BackendModuleName::MODULE_RESERVATION);

        return $this->render('mycpBundle:reservation:list_client.html.twig', array(
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

    public function listReservationsByUsersAction($items_per_page, Request $request) {
        /*$service_security = $this->get('Secure');
        $service_security->verifyAccess();*/
        $page = 1;

        $filter_name = $request->get('filter_name');
        $filter_status = $request->get('filter_status');
        $filter_accommodation = $request->get('filter_accommodation');
        $filter_destination = $request->get('filter_destination');
        $filter_range_from = $request->get('filter_range_from');
        $filter_range_to = $request->get('filter_range_to');

        if($request->getMethod() == 'POST' && $filter_name == 'null' && $filter_status == 'null' && $filter_accommodation == 'null' &&
            $filter_destination == 'null' && $filter_range_from == "null" && $filter_range_to == "null"
        ) {
            $message = 'Debe llenar al menos un campo para filtrar o seleccionar un criterio de ordenación.';
            $this->get('session')->getFlashBag()->add('message_error_local', $message);
            return $this->redirect($this->generateUrl('mycp_list_reservations_byuser'));
        }
        if($filter_name == 'null')
            $filter_name = '';
        if($filter_status == 'null')
            $filter_status = '';
        if($filter_accommodation == 'null')
            $filter_accommodation = '';
        if($filter_destination == 'null')
            $filter_destination = '';
        if($filter_range_from == 'null')
            $filter_range_from = '';
        if($filter_range_to == 'null')
            $filter_range_to = '';

        if(isset($_GET['page']))
            $page = $_GET['page'];

        $em = $this->getDoctrine()->getManager();
        $paginator = $this->get('ideup.simple_paginator');
        $paginator->setItemsPerPage($items_per_page);
        $reservations = $paginator->paginate($em->getRepository('mycpBundle:generalReservation')->getByUsers($filter_name, $filter_status, $filter_accommodation, $filter_destination, $filter_range_from, $filter_range_to))->getResult();//$paginator->paginate($em->getRepository('mycpBundle:generalReservation')
        //->getUsers($filter_user_name, $filter_user_email, $filter_user_city, $filter_user_country, $sort_by))->getResult();

//        $service_log = $this->get('log');
//        $service_log->saveLog('Visit', BackendModuleName::MODULE_RESERVATION);

        return $this->render('mycpBundle:reservation:list_byclient.html.twig', array(
            'reservations' => $reservations,
            'items_per_page' => $items_per_page,
            'current_page' => $page,
            'total_items' => $paginator->getTotalItems(),
            'filter_name' => $filter_name,
            'filter_status' => $filter_status,
            'filter_accommodation' => $filter_accommodation,
            'filter_destination' => $filter_destination,
            'filter_range_from' => $filter_range_from,
            'filter_range_to' => $filter_range_to
        ));
    }

    public function exportUsersReservationsAction($idClient) {
        $exporter = $this->get("mycp.service.export_to_excel");
        return $exporter->exportUsersReservations($idClient);
    }

    public function details_client_reservationAction($id_client, Request $request) {

        //$service_security= $this->get('Secure');
        // $service_security->verifyAccess();
        //$service_log= $this->get('log');
        // $service_log->saveLog('Visit', BackendModuleName::MODULE_RESERVATION);

        $service_time = $this->get('time');


        $em = $this->getDoctrine()->getManager();
        $client = $em->getRepository('mycpBundle:user')->find($id_client);
        $userTourist = $em->getRepository('mycpBundle:userTourist')->findBy(array('user_tourist_user' => $id_client));
        $reservations = $em->getRepository('mycpBundle:generalReservation')->getByUser($id_client);
        $price = 0;

        if($request->getMethod() == 'POST') {

            $post = $request->request->getIterator()->getArrayCopy();
            //var_dump($post); exit();
            /*foreach ($reservations as $reservation) {
                /*$res_db = $em->getRepository('mycpBundle:generalReservation')->find($reservation[0]['gen_res_id']);
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

                        if ($post['service_own_res_status_' . $own_reservation->getOwnResId()] == ownershipReservation::STATUS_RESERVED) {
                            $this->updateICal($em,$own_reservation->getOwnResSelectedRoomId());
                        }
                    }
                }
            }
            $em->flush();
            $message = 'Reservas actualizadas satisfactoriamente.';

            /* $service_log= $this->get('log');
              $service_log->saveLog('Create entity for '.$ownership->getOwnMcpCode(), BackendModuleName::MODULE_RESERVATION); */

            /*$this->get('session')->getFlashBag()->add('message_ok', $message);*/
            return $this->redirect($this->generateUrl('mycp_details_client_reservation', array('id_client' => $id_client)));
        }
        return $this->render('mycpBundle:reservation:reservationDetailsClient.html.twig', array(
            'reservations' => $reservations,
            'client' => $client,
            'errors' => '',
            'tourist' => $userTourist[0]

        ));
    }

    public function details_client_reservationAgAction($id_client, Request $request) {

        //$service_security= $this->get('Secure');
        // $service_security->verifyAccess();
        //$service_log= $this->get('log');
        // $service_log->saveLog('Visit', BackendModuleName::MODULE_RESERVATION);

        $service_time = $this->get('time');


        $em = $this->getDoctrine()->getManager();
//        $client_agency = $em->getRepository('PartnerBundle:paClient')->find($id_client);
        $client_agency = $em->getRepository('mycpBundle:generalReservation')->getNameAgencyOfTheClient($id_client);
        ///$userTourist = $em->getRepository('mycpBundle:userTourist')->findBy(array('user_tourist_user' => $id_client));
        $reservations = $em->getRepository('mycpBundle:generalReservation')->getByUserAg($id_client);
        $price = 0;

        if($request->getMethod() == 'POST') {

            $post = $request->request->getIterator()->getArrayCopy();
            //var_dump($post); exit();
            /*foreach ($reservations as $reservation) {
                /*$res_db = $em->getRepository('mycpBundle:generalReservation')->find($reservation[0]['gen_res_id']);
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

                        if ($post['service_own_res_status_' . $own_reservation->getOwnResId()] == ownershipReservation::STATUS_RESERVED) {
                            $this->updateICal($em,$own_reservation->getOwnResSelectedRoomId());
                        }
                    }
                }
            }
            $em->flush();
            $message = 'Reservas actualizadas satisfactoriamente.';

            /* $service_log= $this->get('log');
              $service_log->saveLog('Create entity for '.$ownership->getOwnMcpCode(), BackendModuleName::MODULE_RESERVATION); */

            /*$this->get('session')->getFlashBag()->add('message_ok', $message);*/
            return $this->redirect($this->generateUrl('mycp_details_client_reservationAG', array('id_client' => $id_client)));
        }
        return $this->render('mycpBundle:reservation:reservationDetailsClientAg.html.twig', array(
            'reservations' => $reservations,
            'client' => $client_agency,
            'errors' => ''

        ));
    }

    public function new_reservationAction(Request $request) {
        $service_security = $this->get('Secure');
        $service_security->verifyAccess();
        $data = array();
        $em = $this->getDoctrine()->getManager();
        $role = $em->getRepository('mycpBundle:role')->findBy(array('role_name' => 'ROLE_CLIENT_TOURIST'));
        $post = $request->get('mycp_mycpbundle_reservationtype');
        $ownerships = $em->getRepository('mycpBundle:ownership')->findAll();
        $rooms = $em->getRepository('mycpBundle:room')->findBy(array('room_ownership' => $post['reservation_ownership'], 'room_active' => true));
        $users = $em->getRepository('mycpBundle:user')->findAll();
        $data['ownerships'] = $ownerships;
        $data['rooms'] = $rooms;
        $data['users'] = $users;

        $form = $this->createForm(new reservationType($data));

        if($request->getMethod() == 'POST') {
            $form->handleRequest($request);
            if($form->isValid()) {
                $em->getRepository('mycpBundle:ownershipReservation')->insert($post);
                $message = 'Reserva añadida satisfactoriamente.';
                $ownership = $em->getRepository('mycpBundle:ownership')->find($post['reservation_ownership']);

                $service_log = $this->get('log');
                $service_log->saveLog($ownership->getLogDescription() . " (Nueva reservación)", BackendModuleName::MODULE_RESERVATION, log::OPERATION_INSERT, DataBaseTables::GENERAL_RESERVATION);

                $this->get('session')->getFlashBag()->add('message_ok', $message);
                return $this->redirect($this->generateUrl('mycp_list_reservations'));
            }
        }
        return $this->render('mycpBundle:reservation:new.html.twig', array('form' => $form->createView(), 'role' => $role[0]));
    }

    public function details_reservation_partialAction($id_reservation) {
        $em = $this->getDoctrine()->getManager();
        $reservation = $em->getRepository('mycpBundle:generalReservation')->find($id_reservation);
        $ownership_reservations = $em->getRepository('mycpBundle:ownershipReservation')->getByIdObj($id_reservation);

        $service_time = $this->get('time');
        $user = $em->getRepository('mycpBundle:userTourist')->findBy(array('user_tourist_user' => $reservation->getGenResUserId()));

        $rooms = array();
        $total_nights = array();
        foreach ($ownership_reservations as $res) {
            array_push($rooms, $em->getRepository('mycpBundle:room')->find($res->getOwnResSelectedRoomId()));
            $temp_total_nights = 0;
            $nights = $service_time->nights($res->getOwnResReservationFromDate()->getTimestamp(), $res->getOwnResReservationToDate()->getTimestamp());
            $temp_total_nights += $nights;

            array_push($total_nights, $temp_total_nights);
        }

        $bookings = $em->getRepository("mycpBundle:generalReservation")->getAllBookings(null, null, null, null, $id_reservation, null, null, "", false);
        $logs = $em->getRepository("mycpBundle:offerLog")->getLogs($id_reservation);
        $currentServiceFee = $reservation->getServiceFee();

        return $this->render('mycpBundle:reservation:reservationDetailsPartial.html.twig', array(
            'nights' => $total_nights,
            'reservation' => $reservation,
            'user' => $user,
            'reservations' => $ownership_reservations,
            'rooms' => $rooms,
            'id_reservation' => $id_reservation,
            'bookings' => $bookings,
            'logs' => $logs,
            'currentServiceFee' => $currentServiceFee
        ));
    }

    public function details_reservationAction($id_reservation, Request $request) {
        $em = $this->getDoctrine()->getManager();
        $reservation = $em->getRepository('mycpBundle:generalReservation')->find($id_reservation);
        $ownership_reservations = $em->getRepository('mycpBundle:ownershipReservation')->findBy(array('own_res_gen_res_id' => $id_reservation));
        $offerLog = $em->getRepository("mycpBundle:offerLog")->findOneBy(array("log_offer_reservation" => $id_reservation), array("log_date" => "DESC"));
        $errors = array();

        $service_time = $this->get('time');
        $reservationService = $this->get('mycp.reservation.service');

        $dates = $service_time->datesBetween($reservation->getGenResFromDate()->format('Y-m-d'), $reservation->getGenResToDate()->format('Y-m-d'));
        $post = $request->request->getIterator()->getArrayCopy();
        if($request->getMethod() == 'POST') {
            try {
                $errors = $reservationService->updateReservationFromRequest($post, $reservation, $ownership_reservations);

                $calendarService = $this->get("mycp.service.calendar");
                $calendarService->createICalForAccommodation($reservation->getGenResOwnId()->getOwnId());
                if(count($errors) == 0) {
                    $message = 'Reserva actualizada satisfactoriamente.';
                    $this->get('session')->getFlashBag()->add('message_ok', $message);
                }
            }
            catch (\Exception $e) {
                $message = 'Error: ' . $e->getMessage();
                $this->get('session')->getFlashBag()->add('message_error_main', $message);
            }
        }

        $user = $em->getRepository('mycpBundle:userTourist')->findBy(array('user_tourist_user' => $reservation->getGenResUserId()));
        $array_nights = array();
        $rooms = array();
        foreach ($ownership_reservations as $res) {
            $nights = $service_time->nights($res->getOwnResReservationFromDate()->getTimestamp(), $res->getOwnResReservationToDate()->getTimestamp());
            array_push($rooms, $em->getRepository('mycpBundle:room')->find($res->getOwnResSelectedRoomId()));
            array_push($array_nights, $nights);
        }

        array_pop($dates);
        $currentServiceFee = $reservation->getServiceFee();

        return $this->render('mycpBundle:reservation:reservationDetails.html.twig', array(
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

        if($custom_message != "" && isset($custom_message)) {
            // dump($custom_message); die;
            $from = $this->getUser();
            $to = $generalReservation->getGenResUserId();
            $subject = "Reservación " . $generalReservation->getCASId();

            $message = $em->getRepository("mycpBundle:message")->insert($from, $to, $subject, $custom_message);
            if($message != null) {
                $service_log = $this->get('log');
                $service_log->saveLog($message->getLogDescription(), BackendModuleName::MODULE_CLIENT_MESSAGES, log::OPERATION_INSERT, DataBaseTables::MESSAGE);
            }
        }

        if($generalReservation->getGenResStatus() == generalReservation::STATUS_RESERVED || $generalReservation->getGenResStatus() == generalReservation::STATUS_PARTIAL_RESERVED) {
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
        return $this->redirect($this->generateUrl('mycp_details_reservation', array('id_reservation' => $id_reservation)));
    }

    public function edit_reservationAction($id_reservation, Request $request) {
        $service_security = $this->get('Secure');
        $service_security->verifyAccess();
        $em = $this->getDoctrine()->getManager();
        $reservation = $em->getRepository('mycpBundle:ownershipReservation')->getById($id_reservation);

        $user_id = $reservation[0]['own_res_gen_res_id']['gen_res_user_id']['user_id'];
        $reservations_user = $em->getRepository('mycpBundle:ownershipReservation')->getReservationsByIdUser($user_id);

        $data['total'] = 0;
        $data['post'] = 0;
        $errors = array();
        foreach ($reservations_user as $reser) {

            $dif = $reser['own_res_reservation_from_date']->diff($reser['own_res_reservation_to_date']);
            $dif = $dif->format('%r%a');
            $data['total'] += $reser['own_res_night_price'] * ($dif - 1);
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
        if($request->getMethod() == 'POST') {
            $data['ownership'] = $request->get('ownership');
            $post['selected_room'] = $request->get('selected_room');
            $not_blank_validator = new NotBlank();
            $not_blank_validator->message = "Este campo no puede estar vacío.";
            $array_keys = array_keys($post);
            $count = $errors_validation = $count_errors = 0;
            foreach ($post as $item) {
                $errors[$array_keys[$count]] = $errors_validation = $this->get('validator')->validateValue($item, $not_blank_validator);
                if($array_keys[$count] != 'percent')
                    $count_errors += count($errors_validation);
                $count++;
            }

            if($count_errors == 0) {
                $em->getRepository('mycpBundle:ownershipReservation')->edit($reservation[0], $post);
                $message = 'Reserva actualizada satisfactoriamente.';
                $ownership = $em->getRepository('mycpBundle:ownership')->find($post['ownership']);

                $service_log = $this->get('log');
                $service_log->saveLog($reservation->getLogDescription(), BackendModuleName::MODULE_RESERVATION, log::OPERATION_UPDATE, DataBaseTables::GENERAL_RESERVATION);

                $this->get('session')->getFlashBag()->add('message_ok', $message);
                return $this->redirect($this->generateUrl('mycp_edit_reservation', array('id_reservation' => $id_reservation)));
            }
        }
        else {
            $data['ownership'] = $reservation[0]['own_res_own_id']['own_id'];
            $post['percent'] = $reservation[0]['own_res_commission_percent'];
        }

        $reservation = $reservation[0];
        return $this->render('mycpBundle:reservation:reservationEdit.html.twig', array('errors' => $errors, 'data' => $data, 'reservation' => $reservation, 'id_reservation' => $id_reservation, 'post' => $post));
    }

    public function getBookingsCallbackAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $reservationId = $request->get("reservation");
        $bookings = $em->getRepository("mycpBundle:generalReservation")->getAllBookings(null, null, null, null, $reservationId, null, null, "", false);
        $content = $this->renderView("mycpBundle:utils:bookings.html.twig", array("bookings" => $bookings));
        return new Response($content, 200);
    }

    public function getLogsCallbackAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $reservationId = $request->get("reservation");
        $logs = $em->getRepository("mycpBundle:offerLog")->getLogs($reservationId);
        $content = $this->renderView("mycpBundle:utils:offerLogs.html.twig", array("logs" => $logs));
        return new Response($content, 200);
    }

    public function getNotificationsCallbackAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $reservationId = $request->get("reservation");
        $notifications = $em->getRepository("mycpBundle:notification")->findBy(array("reservation" => $reservationId));
        $content = $this->renderView("mycpBundle:utils:notifications.html.twig", array("notifications" => $notifications));
        return new Response($content, 200);
    }

    public function get_ownershipsAction($data) {
        $em = $this->getDoctrine()->getManager();
        $ownerships = $em->getRepository('mycpBundle:ownership')->findAll();
        return $this->render('mycpBundle:utils:ownerships.html.twig', array('ownerships' => $ownerships, 'data' => $data));
    }

    public function get_percent_listAction($post) {
        $selected = '';
        if(isset($post['percent']))
            $selected = $post['percent'];
        return $this->render('mycpBundle:utils:percent.html.twig', array('selected' => $selected));
    }

    public function get_numeric_listAction($post) {
        $selected = '';
        if(isset($post['selected']))
            $selected = $post['selected'];
        return $this->render('mycpBundle:utils:range_max_4_no_0.html.twig', array('selected' => $selected));
    }

    public function get_numeric_list_0Action($post) {
        $selected = '';
        if(isset($post['selected']))
            $selected = $post['selected'];
        return $this->render('mycpBundle:utils:range_max_4.html.twig', array('selected' => $selected));
    }

    public function get_reservation_statusAction($post) {
        $selected = '';
        if(isset($post['selected']))
            $selected = $post['selected'];
        return $this->render('mycpBundle:utils:reservation_status.html.twig', array('selected' => $selected));
    }

    public function get_general_reservation_statusAction($post) {
        $selected = '-1';
        if(isset($post['selected']) && $post['selected'] != "")
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
        $em = $this->getDoctrine()->getManager();
        $rooms = $em->getRepository('mycpBundle:room')->findBy(array('room_ownership' => $id_ownership, "room_active" => true));
        return $this->render('mycpBundle:utils:rooms.html.twig', array('rooms' => $rooms, 'selected_room' => $selected_room));
    }

    public function delete_reservationAction($id_reservation) {
        $em = $this->getDoctrine()->getManager();
        $reservation = $em->getRepository('mycpBundle:ownershipReservation')->find($id_reservation);
        $ownership = $em->getRepository('mycpBundle:ownership')->find($reservation->getOwnResOwnId());
        $em->remove($reservation->getOwnResGenResId());
        $em->remove($reservation);
        $em->flush();
        $message = 'Reserva eliminada satisfactoriamente.';

        $service_log = $this->get('log');
        $service_log->saveLog($reservation->getLogDescription(), BackendModuleName::MODULE_RESERVATION, log::OPERATION_DELETE, DataBaseTables::GENERAL_RESERVATION);

        $this->get('session')->getFlashBag()->add('message_ok', $message);
        return $this->redirect($this->generateUrl('mycp_list_reservations'));
    }

    public function setNotAvailableCallbackAction() {
        $request = $this->getRequest();
        $em = $this->getDoctrine()->getManager();
        $reservations_ids = $request->request->get('reservations_ids');
        $save_option = $request->request->get('save_option');
        $page = $request->request->get('page');
        $service_log = $this->get('log');
        $logMessage = "";
        $response = null;

        //Modificar el estado
        $em->getRepository('mycpBundle:generalReservation')->setAsNotAvailable($reservations_ids, $save_option);

        //Enviar por correo a los clientes
        $service_email = $this->get('Email');

        try {
            foreach ($reservations_ids as $genResId) {
                $service_email->sendReservation($genResId, null, false);
                $logMessage = ($logMessage == "") ? $logMessage . "CAS." . $genResId : $logMessage . ", CAS." . $genResId;

                // inform listeners that a reservation was sent out
                $dispatcher = $this->get('event_dispatcher');
                $eventData = new GeneralReservationJobData($genResId);
                $dispatcher->dispatch('mycp.event.reservation.sent_out', new JobEvent($eventData));
            }

            $service_log->saveLog("Se han colocado " . count($reservations_ids) . " reservas como no disponibles: " . $logMessage, BackendModuleName::MODULE_RESERVATION, log::OPERATION_UPDATE, DataBaseTables::GENERAL_RESERVATION);
            $message = ($save_option == Operations::SAVE_AND_UPDATE_CALENDAR) ? 'Se han modificado ' . count($reservations_ids) . ' reservaciones como No Disponibles, se almacenaron las No Disponibilidades y se ha notificado a los clientes respectivos. Todas las operaciones fueron satisfactorias.' :
                'Se han modificado ' . count($reservations_ids) . ' reservaciones como No Disponibles y se ha notificado a los clientes respectivos. Ambas operaciones fueron satisfactorias.';
            $this->get('session')->getFlashBag()->add('message_ok', $message);

            $response = ($page != "" && $page != "0") ? $this->generateUrl('mycp_list_reservations', array("page" => $page)) : $this->generateUrl('mycp_list_reservations');
        }
        catch (\Exception $e) {
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

            \MyCp\mycpBundle\Helpers\VoucherHelper::sendVoucher($em, $bookingService, $service_email, $this, $id_reservation, $emailToSend, false);
        }
        catch (\Exception $e) {
            $CASId = \MyCp\FrontEndBundle\Helpers\ReservationHelper::getCASId($id_reservation);
            $message = 'Error al enviar el voucher asociado a la reservación ' . $CASId . ". " . $e->getMessage();
            $this->get('session')->getFlashBag()->add('message_error_main', $message);
        }

        return $this->redirect($this->generateUrl('mycp_list_reservations'));
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

        if($save_to_disk == true) {
            $content_out = $dompdf->output();
            $fpdf = fopen("vouchers/$name.pdf", 'w');
            fwrite($fpdf, $content_out);
            fclose($fpdf);
        }
        else
            $dompdf->stream($name . ".pdf", array("Attachment" => false));
    }

    public function checkinAction(Request $request) {
        $service_security = $this->get('Secure');
        $service_security->verifyAccess();
        $em = $this->getDoctrine()->getManager();
        $filter_date_from = $request->get('filter_date_from');
        $sort_by = $request->get('sort_by');

        if($filter_date_from == 'null')
            $filter_date_from = '';

        if($sort_by == 'null')
            $sort_by = \MyCp\mycpBundle\Helpers\OrderByHelper::DEFAULT_ORDER_BY;

        if($filter_date_from == "") {
            $filter_date_from = new \DateTime();
            $startTimeStamp = $filter_date_from->getTimestamp();
            $startTimeStamp = strtotime("+5 day", $startTimeStamp);
            $filter_date_from->setTimestamp($startTimeStamp);
            $filter_date_from = $filter_date_from->format("d/m/Y");
        }
        else {
            $filter_date_from = str_replace('_', '/', $filter_date_from);
        }

        $checkins = $em->getRepository("mycpBundle:generalReservation")->getCheckins($filter_date_from, $sort_by);


        return $this->render('mycpBundle:reservation:checkIn.html.twig', array(
            'list' => $checkins,
            'filter_date_from' => $filter_date_from,
            'sort_by' => $sort_by));
    }


    public function newCleanOfferAction($idClient, $attendedDate) {
        $em = $this->getDoctrine()->getManager();

        $client = null;
        $tourist = null;

        if($idClient != null && $idClient != "null" && $idClient != "") {
            $client = $em->getRepository("mycpBundle:user")->find($idClient);
            $tourist = $em->getRepository("mycpBundle:userTourist")->findOneBy(array('user_tourist_user' => $client->getUserId()));

            if($tourist == null) {
                $defaultLangCode = $this->container->getParameter('configuration.default.language.code');
                $defaultCurrencyCode = $this->container->getParameter('configuration.default.currency.code');
                $tourist = $em->getRepository('mycpBundle:userTourist')->createDefaultTourist($defaultLangCode, $defaultCurrencyCode, $client);
            }
        }

        if($this->getRequest()->getMethod() == 'POST') {
            $request = $this->getRequest();
            $reservationService = $this->get("mycp.reservation.service");
            $service_log = $this->get('log');

            $resultReservations = $reservationService->createAvailableOfferFromRequest($request, $client, $attendedDate);

            $newReservations = $resultReservations['reservations'];
            $arrayNightsByOwnershipReservation = $resultReservations['nights'];
            $general_reservation = $resultReservations['generalReservation'];
            $operation = $request->get("save_operation");

            $message = 'Nueva oferta ' . $general_reservation->getCASId() . ' creada satisfactoriamente.';
            $this->get('session')->getFlashBag()->add('message_ok', $message);

            // inform listeners that a reservation was sent out (remarketing)
            $dispatcher = $this->get('event_dispatcher');
            $eventData = new GeneralReservationJobData($general_reservation->getGenResId());
            $dispatcher->dispatch('mycp.event.reservation.sent_out', new JobEvent($eventData));

            $service_log->saveLog($general_reservation->getLogDescription() . " (Nueva oferta)", BackendModuleName::MODULE_RESERVATION, log::OPERATION_INSERT, DataBaseTables::GENERAL_RESERVATION);
            $service_log->saveNewOfferLog($general_reservation, null, false);

            switch ($operation) {
                case Operations::SAVE_OFFER_AND_SEND: {
                    //Enviar correo al cliente incluyendo el texto
                    $custom_message = $request->get('message_body');
                    if($custom_message !== "") {
                        $from = $this->getUser();
                        $to = $general_reservation->getGenResUserId();
                        $subject = "Reservación " . $general_reservation->getCASId();

                        $createdMessage = $em->getRepository("mycpBundle:message")->insert($from, $to, $subject, $custom_message);
                        if($createdMessage != null)
                            $service_log->saveLog($createdMessage->getLogDescription(), BackendModuleName::MODULE_CLIENT_MESSAGES, log::OPERATION_INSERT, DataBaseTables::MESSAGE);
                    }

                    $mailer = $this->get('Email');
                    $mailer->sendReservation($general_reservation->getGenResId(), $custom_message, true, true);

                    $message = 'Oferta ' . $general_reservation->getCASId() . ' enviada satisfactoriamente.';
                    $this->get('session')->getFlashBag()->add('message_ok', $message);

                    return $this->redirect($this->generateUrl('mycp_list_reservations'));
                }
                case Operations::SAVE_OFFER_AND_VIEW: {
                    return $this->redirect($this->generateUrl('mycp_details_reservation', array('id_reservation' => $general_reservation->getGenResId())));
                }
                case Operations::SAVE_AND_EXIT: {
                    return $this->redirect($this->generateUrl('mycp_list_reservations'));
                }
            }
        }

        return $this->render('mycpBundle:reservation:newCleanOffer.html.twig', array(
            'client' => $client, 'tourist' => $tourist, "attendedDate" => $attendedDate));
    }

    public function searchClientsAction() {
        $em = $this->getDoctrine()->getManager();
        $touristClients = $em->getRepository("mycpBundle:user")->findBy(array("user_role" => "ROLE_CLIENT_TOURIST"), array("user_user_name" => "ASC"));

        return $this->render('mycpBundle:utils:searchClientsTab.html.twig', array(
            'users' => $touristClients));
    }

    public function searchAccommodationsAction() {
        $em = $this->getDoctrine()->getManager();
        $destinations = $em->getRepository("mycpBundle:destination")->findBy(array(), array("des_name" => "ASC"));
        $ownerships = $em->getRepository("mycpBundle:ownership")->findBy(array("own_status" => OwnershipStatuses::ACTIVE), array("own_mcp_code" => "ASC"));
        $currentServiceFee = $em->getRepository("mycpBundle:serviceFee")->getCurrent();

        return $this->render('mycpBundle:utils:offerAccommodationControl.html.twig', array(
            'ownerships' => $ownerships,
            "destinations" => $destinations,
            'currentServiceFee' => $currentServiceFee
        ));
    }

    public function syncPaymentsAction() {
        $request = $this->getRequest();
        $bookings_ids = $request->request->get('bookings_ids');

        $response = null;

        try {
            $syncronizer = $this->get("mycp_sync_payment");
            $syncronizer->syncronizeBookings($bookings_ids);
            $response = $this->generateUrl("mycp_reservation_sync_booking_list");
        }
        catch (\Exception $e) {
            $response = $e->getTraceAsString();
        }
        return new Response($response);
    }

    public function syncBookingsAction($sinceDate) {
        $syncronizer = $this->get("mycp_sync_payment");
        $sinceDateArg = null;

        if($sinceDate != null) {
            $dateArgs = split("_", $sinceDate);
            $sinceDateArg = $dateArgs[2] . '-' . $dateArgs[1] . '-' . $dateArgs[0];
            $sinceDateArg = strToTime($sinceDateArg);
        }

        $bookings = $syncronizer->getAllToSync($sinceDateArg);

        $sinceDate = str_replace('_', '/', $sinceDate);

        return $this->render('mycpBundle:reservation:syncBooking.html.twig', array(
            "bookings" => $bookings,
            'filter_date' => $sinceDate
        ));
    }

    public function exportReservationsAction(Request $request) {
        try {
            //$service_security = $this->get('Secure');
            //$service_security->verifyAccess();
            $filter_date_reserve = $request->get('filter_date_reserve');
            $filter_offer_number = $request->get('filter_offer_number');
            $filter_reference = $request->get('filter_reference');
            $filter_date_from = $request->get('filter_date_from');
            $filter_date_to = $request->get('filter_date_to');
            $filter_booking_number = $request->get('filter_booking_number');
            $filter_status = $request->get('filter_status');
            $sort_by = $request->get('sort_by');

            if($filter_date_reserve == 'null')
                $filter_date_reserve = '';
            if($filter_offer_number == 'null')
                $filter_offer_number = '';
            if($filter_booking_number == 'null')
                $filter_booking_number = '';
            if($filter_reference == 'null')
                $filter_reference = '';
            if($filter_date_from == 'null')
                $filter_date_from = '';
            if($filter_date_to == 'null')
                $filter_date_to = '';
            if($filter_status == 'null')
                $filter_status = '';
            if($sort_by == 'null')
                $sort_by = '';

            $date = new \DateTime();
            $date = date_modify($date, "-5 days");

            $em = $this->getDoctrine()->getManager();
            $reservations = $em->getRepository('mycpBundle:generalReservation')
                ->getReservationsToExport($filter_date_reserve, $filter_offer_number, $filter_reference, $filter_date_from, $filter_date_to, $sort_by, $filter_booking_number, $filter_status, $date);

            if(count($reservations)) {
                $exporter = $this->get("mycp.service.export_to_excel");
                return $exporter->exportReservations($reservations, $date);
            }
            else {
                $message = 'No hay datos para llenar el Excel a descargar.';
                $this->get('session')->getFlashBag()->add('message_ok', $message);
                return $this->redirect($this->generateUrl("mycp_list_reservations"));
            }
        }
        catch (\Exception $e) {
            $message = 'Ha ocurrido un error. Por favor, introduzca correctamente los valores para filtrar.';
            $this->get('session')->getFlashBag()->add('message_error_main', $message);

            return $this->redirect($this->generateUrl("mycp_list_reservations"));
        }
    }

    public function generateClientCallbackAction() {
        $request = $this->getRequest();
        $users_ids = array_unique($request->request->get('users_ids'));

        $exporter = $this->get("mycp.service.export_to_excel");
        $exporter->generateClients($users_ids);

        return new Response($this->generateUrl("mycp_download_clients"), 200);
    }

    function downloadClientCallbackAction() {
        $exporter = $this->get("mycp.service.export_to_excel");
//        $result=$exporter->exportClients();
        return $exporter->exportClients();
    }

    /**
     * @param Request $request
     */
    public function showModalEmailAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $config = $em->getRepository('mycpBundle:configEmail')->findAll();
        $array_destinations = explode(',', $request->get('arraydestinations'));
        $email_destination = $em->getRepository('mycpBundle:emailDestination')->findDestinations($array_destinations);
        $user = $em->getRepository('mycpBundle:user')->find($request->get('iduser'));
        $userTourist = $em->getRepository('mycpBundle:userTourist')->findBy(array('user_tourist_user' => $request->get('iduser')));


        switch ((count($userTourist)) ? $userTourist[0]->getUserTouristLanguage()->getLangCode() : 'EN') {
            case 'ES': {
                $content_config = array('subject' => (count($config)) ? $config[0]->getSubjectEs() : '', 'introduction' => (count($config)) ? $config[0]->getIntroductionEs() : '', 'foward' => (count($config)) ? $config[0]->getFowardEs() : '');
                break;
            }
            case 'EN': {
                $content_config = array('subject' => (count($config)) ? $config[0]->getSubjectEn() : '', 'introduction' => (count($config)) ? $config[0]->getIntroductionEn() : '', 'foward' => (count($config)) ? $config[0]->getFowardEn() : '');
                break;
            }
            case'DE': {
                $content_config = array('subject' => (count($config)) ? $config[0]->getSubjectDe() : '', 'introduction' => (count($config)) ? $config[0]->getIntroductionDe() : '', 'foward' => (count($config)) ? $config[0]->getFowardDe() : '');
                break;
            }
        }
        return $this->render('mycpBundle:reservation:modal_email.html.twig', array('config' => $config, 'user' => $user, 'content_config' => $content_config, 'email_destination' => $email_destination, 'language_email' => strtolower((count($userTourist)) ? $userTourist[0]->getUserTouristLanguage()->getLangCode() : 'EN')));
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    function sendEmailDestinationAction(Request $request) {
        $service_email = $this->get('Email');
        $content = '<p>' . $request->get("emailIntroduction") . '</br>' . $request->get("emailContent") . '</br>' . $request->get("emailFoward") . '</p>';
        $service_email->sendTemplatedEmail($request->get("emailSubject"), 'noreply@mycasaparticular.com', $request->get("emailUser"), $content);
        return new JsonResponse(array('success' => true));
    }

    public function sendVoucherToReservationTeamFromBookingAction($id_booking) {
        try {
            $em = $this->getDoctrine()->getManager();
            $bookingService = $this->get('front_end.services.booking');
            $service_email = $this->get('mycp.service.email_manager');
            $emailToSend = 'reservation@mycasaparticular.com';

            \MyCp\mycpBundle\Helpers\VoucherHelper::sendVoucherByBookingId($em, $bookingService, $service_email, $this, $id_booking, $emailToSend, false);
        }
        catch (\Exception $e) {
            $message = 'Error al enviar el voucher asociado al booking ' . $id_booking . ". " . $e->getMessage();
            $this->get('session')->getFlashBag()->add('message_error_main', $message);
        }

        return $this->redirect($this->generateUrl('mycp_details_reservations_booking', array("id_booking" => $id_booking)));
    }
}

