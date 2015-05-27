<?php

namespace MyCp\mycpBundle\Controller;

use Abuc\RemarketingBundle\Event\JobEvent;
use MyCp\mycpBundle\Entity\booking;
use MyCp\mycpBundle\Entity\payment;
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

class BackendReservationController extends Controller {

    public function new_offerAction($id_tourist, $id_reservation) {

        $em = $this->getDoctrine()->getManager();
        $service_time = $this->get('time');
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

        if ($this->getRequest()->getMethod() == 'POST') {
            $request = $this->getRequest();

            if(count($bookings) > 0) {
                $booking = $em->getRepository("mycpBundle:booking")->find($bookings[0]["booking_id"]);
                $resultReservations = $reservationService->createReservedOfferFromRequest($request,$user,$booking);
                $newReservations = $resultReservations['reservations'];
                $arrayNightsByOwnershipReservation = $resultReservations['nights'];
                $general_reservation = $resultReservations['generalReservation'];

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
                    $mailMessage = ($postMessageBody != null && $postMessageBody != "") ? $postMessageBody : null;
                    $bookingService = $this->get('front_end.services.booking');
                    VoucherHelper::sendVoucherToClient($em, $bookingService, $emailService, $this, $general_reservation, 'NEW_OFFER_SUBJECT', $mailMessage, true);

                    //Enviar correo al equipo de reservación
                    Reservation::sendNewOfferToTeam($this, $emailService, $tourist, $newReservations, $arrayNightsByOwnershipReservation, $gen_res);
                } catch (\Exception $e) {
                    $this->get('session')->getFlashBag()->add('message_error_local', $e->getMessage());
                }

                $message = 'Nueva oferta ' . $general_reservation->getCASId() . ' creada satisfactoriamente.';
                $this->get('session')->getFlashBag()->add('message_ok', $message);

                return $this->redirect($this->generateUrl('mycp_details_reservation', array('id_reservation' => $general_reservation->getGenResId())));

            }
            else{
                $message = 'Solo se puede ofrecer una nueva oferta si la reservación original está cancelada y tiene algún pago asociado.';
                $this->get('session')->getFlashBag()->add('message_error_local', $message);
            }
        }

        if ($gen_res->getGenResStatus() != generalReservation::STATUS_CANCELLED || count($bookings) == 0) {
            $message = 'Solo se puede ofrecer una nueva oferta si la reservación original está cancelada y tiene algún pago asociado.';
            $this->get('session')->getFlashBag()->add('message_error_local', $message);
        }

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
                    'payment' => $payment
        ));
    }

    public function changeDatesAction($id_tourist, $id_reservation, Request $request) {
        $em = $this->getDoctrine()->getManager();
        $reservation = $em->getRepository('mycpBundle:generalReservation')->find($id_reservation);
        $ownership_reservations = $em->getRepository('mycpBundle:ownershipReservation')->findBy(array('own_res_gen_res_id' => $id_reservation));
        $errors = array("numeric_price" => 0, "price" => 0, "date" => 0);
        $post = $request->request->getIterator()->getArrayCopy();
        $message = "";

        if ($request->getMethod() == 'POST') {

            $keys = array_keys($post);
            $existError = false;

            foreach ($keys as $key) {
                $splitted = explode("_", $key);
                $resId = $splitted[count($splitted) - 1];
                if (strpos($key, 'service_room_price') !== false) {

                    if (!is_numeric($post[$key]) && !$errors["numeric_price"]) {
                        $errors["numeric_price"] = 1;
                        $message .= 'En el campo precio por noche tiene que introducir un valor numérico.<br/>';
                        $existError = true;
                    } else if ($post[$key] != "") {
                        $reservationPrice = $post["price_" . $resId];

                        if ($post[$key] != 0 && $post[$key] != $reservationPrice && !$errors["price"]) {
                            $errors["price"] = 1;
                            $message .= 'El precio por noche tiene que ser igual al sugerido.<br/>';
                            $existError = true;
                        }
                    }
                }
                if (strpos($key, 'date_from') !== false) {
                    $originalDate = $post["original_date_" . $resId];

                    if ($post[$key] == $originalDate && !$errors["date"]) {
                        $errors["date"] = 1;
                        $message .= 'La fecha no puede ser la misma de la reservación. <br/>';
                        $existError = true;
                    }
                }
            }

            if (!$existError) {
                $newGeneralReservation = $reservation->getClone();
                $newGeneralReservation->setGenResStatus(generalReservation::STATUS_RESERVED);
                $newGeneralReservation->setGenResDate(new \DateTime());
                $em->persist($newGeneralReservation);
                $em->flush();

                $temp_price = 0;
                $fromDate = null;
                $toDate = null;
                $newReservations = array();
                $ownNights = array();
                foreach ($ownership_reservations as $ownership_reservation) {

                    if ($post['service_room_price_' . $ownership_reservation->getOwnResId()] != 0 && $post["price_" . $ownership_reservation->getOwnResId()] != $post['service_room_price_' . $ownership_reservation->getOwnResId()]) {
                        $errors['service_room_price_' . $ownership_reservation->getOwnResId()] = 1;
                        $message = 'El precio por noche tiene que coincidir con el sugerido.';
                        $this->get('session')->getFlashBag()->add('message_error_local', $message);
                        $em->remove($newGeneralReservation);
                        $em->flush();
                    } else {

                        $start = explode('/', $post['date_from_' . $ownership_reservation->getOwnResId()]);
                        $nights = $post['nights_' . $ownership_reservation->getOwnResId()];
                        $start_timestamp = mktime(0, 0, 0, $start[1], $start[0], $start[2]);
                        $end_timestamp = strtotime("+" . $nights . " day", $start_timestamp);

                        $newOwnRes = $ownership_reservation->getClone();
                        $newOwnRes->setOwnResGenResId($newGeneralReservation);
                        $newOwnRes->setOwnResStatus(ownershipReservation::STATUS_RESERVED);

                        $newOwnRes->setOwnResReservationFromDate(new \DateTime(date("Y-m-d H:i:s", $start_timestamp)));
                        $newOwnRes->setOwnResReservationToDate(new \DateTime(date("Y-m-d H:i:s", $end_timestamp)));

                        if (isset($post['service_room_price_' . $ownership_reservation->getOwnResId()]) && $post['service_room_price_' . $ownership_reservation->getOwnResId()] != "" && $post['service_room_price_' . $ownership_reservation->getOwnResId()] != "0") {
                            $temp_price+=$post['service_room_price_' . $ownership_reservation->getOwnResId()] * $nights;
                            $newOwnRes->setOwnResNightPrice($post['service_room_price_' . $ownership_reservation->getOwnResId()]);
                        }


                        if ($fromDate == null || $newOwnRes->getOwnResReservationFromDate() < $fromDate)
                            $fromDate = $newOwnRes->getOwnResReservationFromDate();
                        if ($toDate == null || $newOwnRes->getOwnResReservationToDate() > $toDate)
                            $toDate = $newOwnRes->getOwnResReservationToDate();

                        $em->persist($newOwnRes);
                        array_push($newReservations, $newOwnRes);

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
                foreach($newReservations as $nReservation)
                {
                    $ownNights[$nReservation->getOwnResId()] =  $time->nights($nReservation->getOwnResReservationFromDate()->getTimestamp(),$nReservation->getOwnResReservationToDate()->getTimestamp());
                }

                $service_log = $this->get('log');
                $service_log->saveLog('Nueva oferta para ' . $reservation->getCASId(), BackendModuleName::MODULE_RESERVATION);

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
                catch(\Exception $e)
                {
                    $this->get('session')->getFlashBag()->add('message_error_local', $e->getMessage());
                }

                $message = 'Nueva oferta ' . $newGeneralReservation->getCASId() . ' creada satisfactoriamente.';
                $this->get('session')->getFlashBag()->add('message_ok', $message);
                return $this->redirect($this->generateUrl('mycp_details_reservation', array('id_reservation' => $newGeneralReservation->getGenResId())));
            }
            else
            {
                $this->get('session')->getFlashBag()->add('message_error_local', $message);
                return $this->redirect($this->generateUrl('mycp_new_offer_reservation', array("id_tourist" => $id_tourist, "id_reservation" => $id_reservation)));
            }
        }
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

        $reservations = $paginator->paginate($em->getRepository('mycpBundle:generalReservation')
                                ->getAll($filter_date_reserve, $filter_offer_number, $filter_reference, $filter_date_from, $filter_date_to, $sort_by, $filter_booking_number, $filter_status))->getResult();
        $filter_date_reserve_twig = str_replace('/', '_', $filter_date_reserve);
        $filter_date_from_twig = str_replace('/', '_', $filter_date_from);
        $filter_date_to_twig = str_replace('/', '_', $filter_date_to);
        $service_log = $this->get('log');
        $service_log->saveLog('Visit', BackendModuleName::MODULE_RESERVATION);
        //$total_nights = array();
        $service_time = $this->get('time');
        /*foreach ($reservations as $res) {
            $owns_res = $em->getRepository('mycpBundle:ownershipReservation')->findBy(array('own_res_gen_res_id' => $res[0]['gen_res_id']));
            $temp_total_nights = generalReservation::getTotalPayedNights($owns_res, $service_time);
            array_push($total_nights, $temp_total_nights);
        }*/
        return $this->render('mycpBundle:reservation:list.html.twig', array(
                    //'total_nights' => $total_nights,
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
                    'filter_status' => $filter_status
        ));
    }

    public function list_reservations_bookingAction($items_per_page, Request $request) {
        $service_security = $this->get('Secure');
        $service_security->verifyAccess();

        $filter_booking_number = $request->get('filter_booking_number');
        $filter_date_booking = $request->get('filter_date_booking');
        $filter_user_booking = $request->get('filter_user_booking');
        $filter_arrive_date_booking = $request->get('filter_arrive_date_booking');

        if ($request->getMethod() == 'POST' && $filter_booking_number == 'null' && $filter_date_booking == 'null' && $filter_user_booking == 'null' && $filter_arrive_date_booking == 'null') {
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
        if ($filter_arrive_date_booking == 'null')
            $filter_arrive_date_booking = '';

        $page = 1;
        if (isset($_GET['page']))
            $page = $_GET['page'];
        $em = $this->getDoctrine()->getManager();
        $paginator = $this->get('ideup.simple_paginator');
        $paginator->setItemsPerPage($items_per_page);

        $bookings = $paginator->paginate($em->getRepository('mycpBundle:generalReservation')
                                ->getAllBookings($filter_booking_number, $filter_date_booking, $filter_user_booking, $filter_arrive_date_booking))->getResult();
        $service_log = $this->get('log');
        $service_log->saveLog('Visit', BackendModuleName::MODULE_RESERVATION);

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
                    'total_items' => $paginator->getTotalItems(),
        ));
    }

    public function details_bookingAction($id_booking) {
        $service_security = $this->get('Secure');
        $service_security->verifyAccess();
        $em = $this->getDoctrine()->getManager();
        $payment = $em->getRepository('mycpBundle:payment')->findOneBy(array("booking" => $id_booking));
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
        $userTourist = $em->getRepository('mycpBundle:userTourist')->findBy(array('user_tourist_user' => $id_client));
        $reservations = $em->getRepository('mycpBundle:generalReservation')->getByUser($id_client);
        $price = 0;
       // $total_nights = array();

        if ($request->getMethod() == 'POST') {

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

        /*foreach ($reservations as $reservation) {
            $owns_res = $em->getRepository('mycpBundle:ownershipReservation')->findBy(array('own_res_gen_res_id' => $reservation[0]['gen_res_id']));
            $temp_total_nights = generalReservation::getTotalPayedNights($owns_res, $service_time);

            array_push($total_nights, $temp_total_nights);
        }*/
        return $this->render('mycpBundle:reservation:reservationDetailsClient.html.twig', array(
                    //'total_nights' => $total_nights,
                    'reservations' => $reservations,
                    'client' => $client,
                    'errors' => '',
                    'tourist' => $userTourist[0]
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
                $em->getRepository('mycpBundle:ownershipReservation')->insert($post);
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
        $reservation = $em->getRepository('mycpBundle:generalReservation')->find($id_reservation);
        $ownership_reservations = $em->getRepository('mycpBundle:ownershipReservation')->findBy(array('own_res_gen_res_id' => $id_reservation));

        $service_time = $this->get('time');

        $user = $em->getRepository('mycpBundle:userTourist')->findBy(array('user_tourist_user' => $reservation->getGenResUserId()));

        $rooms = array();
       // $total_nights = array();
        foreach ($ownership_reservations as $res) {
            array_push($rooms, $em->getRepository('mycpBundle:room')->find($res->getOwnResSelectedRoomId()));
           // $temp_total_nights = 0;
           // $nights = $service_time->nights($res->getOwnResReservationFromDate()->getTimestamp(), $res->getOwnResReservationToDate()->getTimestamp());
            //$temp_total_nights+=$nights;

           //array_push($total_nights, $temp_total_nights);
        }

        return $this->render('mycpBundle:reservation:reservationDetailsPartial.html.twig', array(
                    //'nights' => $total_nights,
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
        $cancelled_total = 0;
        $outdated_total = 0;
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
                    switch ($post[$key]) {
                        case ownershipReservation::STATUS_NOT_AVAILABLE: $non_available_total++;
                            break;
                        case ownershipReservation::STATUS_AVAILABLE: $available_total++;
                            break;
                        case ownershipReservation::STATUS_CANCELLED: $cancelled_total++;
                            break;
                        case ownershipReservation::STATUS_OUTDATED: $outdated_total++;
                            break;
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
                $totalPrice = 0;
                $nights = 0;
                foreach ($ownership_reservations as $ownership_reservation) {
                    $start = explode('/', $post['date_from_' . $ownership_reservation->getOwnResId()]);
                    $end = explode('/', $post['date_to_' . $ownership_reservation->getOwnResId()]);
                    $start_timestamp = mktime(0, 0, 0, $start[1], $start[0], $start[2]);
                    $end_timestamp = mktime(0, 0, 0, $end[1], $end[0], $end[2]);

                    $ownership_reservation->setOwnResReservationFromDate(new \DateTime(date("Y-m-d H:i:s", $start_timestamp)));
                    $ownership_reservation->setOwnResReservationToDate(new \DateTime(date("Y-m-d H:i:s", $end_timestamp)));

                    if (isset($post['service_room_price_' . $ownership_reservation->getOwnResId()]) && $post['service_room_price_' . $ownership_reservation->getOwnResId()] != "" && $post['service_room_price_' . $ownership_reservation->getOwnResId()] != "0") {
                        $ownership_reservation->setOwnResNightPrice($post['service_room_price_' . $ownership_reservation->getOwnResId()]);
                    }
                    else
                        $ownership_reservation->setOwnResNightPrice(0);

                    $ownership_reservation->setOwnResCountAdults($post['service_room_count_adults_' . $ownership_reservation->getOwnResId()]);
                    $ownership_reservation->setOwnResCountChildrens($post['service_room_count_childrens_' . $ownership_reservation->getOwnResId()]);
                    $ownership_reservation->setOwnResStatus($post['service_own_res_status_' . $ownership_reservation->getOwnResId()]);

                    $tripleRoomFeed = $this->container->getParameter('configuration.triple.room.charge');
                    $partialTotalPrice = \MyCp\FrontEndBundle\Helpers\ReservationHelper::getTotalPrice($em, $service_time, $ownership_reservation, $tripleRoomFeed);
                    $totalPrice+=$partialTotalPrice;
                    $ownership_reservation->setOwnResTotalInSite($partialTotalPrice);

                    $partialNights = $service_time->nights($start_timestamp, $end_timestamp);
                    $nights+=$partialNights;
                    $ownership_reservation->setOwnResNights($partialNights);

                    if ($post['service_own_res_status_' . $ownership_reservation->getOwnResId()] == ownershipReservation::STATUS_RESERVED) {
                        $this->updateICal($em,$ownership_reservation->getOwnResSelectedRoomId());
                    }
                }
                $message = 'Reserva actualizada satisfactoriamente.';
                $reservation->setGenResTotalInSite($totalPrice);
                $reservation->setGenResSaved(1);
                $reservation->setGenResNights($nights);
                if ($reservation->getGenResStatus() != generalReservation::STATUS_RESERVED) {
                    if ($non_available_total > 0 && $non_available_total == $details_total) {
                        $reservation->setGenResStatus(generalReservation::STATUS_NOT_AVAILABLE);
                    } else if ($available_total > 0 && $available_total == $details_total) {
                        $reservation->setGenResStatus(generalReservation::STATUS_AVAILABLE);
                    } else if ($non_available_total > 0 && $available_total > 0)
                        $reservation->setGenResStatus(generalReservation::STATUS_PARTIAL_AVAILABLE);

                    else if ($cancelled_total > 0 && $cancelled_total != $details_total) {
                        $reservation->setGenResStatus(generalReservation::STATUS_PARTIAL_CANCELLED);
                    } else if ($outdated_total > 0 && $outdated_total == $details_total)
                        $reservation->setGenResStatus(generalReservation::STATUS_OUTDATED);
                }
                if ($cancelled_total > 0 && $cancelled_total == $details_total) {
                    $reservation->setGenResStatus(generalReservation::STATUS_CANCELLED);
                }
                $em->persist($reservation);
                $em->flush();
                $service_log = $this->get('log');
                $service_log->saveLog('Edit entity for ' . $reservation->getCASId(), BackendModuleName::MODULE_RESERVATION);

                $this->get('session')->getFlashBag()->add('message_ok', $message);
            }
        }

        $user = $em->getRepository('mycpBundle:userTourist')->findBy(array('user_tourist_user' => $reservation->getGenResUserId()));
        //$array_nights = array();
        $rooms = array();
        foreach ($ownership_reservations as $res) {
           // $nights = $service_time->nights($res->getOwnResReservationFromDate()->getTimestamp(), $res->getOwnResReservationToDate()->getTimestamp());
            array_push($rooms, $em->getRepository('mycpBundle:room')->find($res->getOwnResSelectedRoomId()));
           // array_push($array_nights, $nights);
        }

        array_pop($dates);
        return $this->render('mycpBundle:reservation:reservationDetails.html.twig', array(
                    'post' => $post,
                    'errors' => $errors,
                    'reservation' => $reservation,
                    'user' => $user,
                    'reservations' => $ownership_reservations,
                    'rooms' => $rooms,
                    //'nights' => $array_nights,
                    'id_reservation' => $id_reservation));
    }

    public function send_reservationAction($id_reservation) {
        $em = $this->getDoctrine()->getManager();
        $generalReservation = $em->getRepository("mycpBundle:generalReservation")->find($id_reservation);
        $custom_message = $this->getRequest()->get('message_to_client');
        if($custom_message !== "")
        {
            $from = $this->getUser();
            $to = $generalReservation->getGenResUserId();
            $subject = "Reservación ".$generalReservation->getCASId();

            $em->getRepository("mycpBundle:message")->insert($from, $to, $subject, $custom_message);
            $service_log= $this->get('log');
            $service_log->saveLog('Insert client message',  BackendModuleName::MODULE_CLIENT_MESSAGES);
        }

        if ($generalReservation->getGenResStatus() == generalReservation::STATUS_RESERVED || $generalReservation->getGenResStatus() == generalReservation::STATUS_PARTIAL_RESERVED) {
            $bookingService = $this->get('front_end.services.booking');
            $emailService = $this->get('mycp.service.email_manager');
            VoucherHelper::sendVoucherToClient($em, $bookingService, $emailService, $this, $generalReservation, 'SEND_VOUCHER_SUBJECT', $custom_message, false);
        }
        else {

            $service_email = $this->get('Email');
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
        $reservation = $em->getRepository('mycpBundle:ownershipReservation')->getById($id_reservation);

        $user_id = $reservation[0]['own_res_gen_res_id']['gen_res_user_id']['user_id'];
        $reservations_user = $em->getRepository('mycpBundle:ownershipReservation')->getReservationsByIdUser($user_id);

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
                $em->getRepository('mycpBundle:ownershipReservation')->edit($reservation[0], $post);
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
        $selected = '-1';
        if (isset($post['selected']) && $post['selected'] != "")
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

            \MyCp\mycpBundle\Helpers\VoucherHelper::sendVoucher($em, $bookingService, $service_email, $this, $id_reservation, $emailToSend, true);
        } catch (\Exception $e) {
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

        if ($save_to_disk == true) {
            $content_out = $dompdf->output();
            $fpdf = fopen("vouchers/$name.pdf", 'w');
            fwrite($fpdf, $content_out);
            fclose($fpdf);
        }
        else
            $dompdf->stream($name . ".pdf", array("Attachment" => false));
    }

    public function checkinAction(Request $request) {
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

        $total_nights = array();
        $service_time = $this->get('time');
        foreach ($checkins as $res) {
            $genRes = $em->getRepository('mycpBundle:generalReservation')->find($res[0]['gen_res_id']);
            $reservations = $em->getRepository('mycpBundle:ownershipReservation')->findBy(array("own_res_gen_res_id" => $res[0]['gen_res_id']), array("own_res_reservation_from_date" => "ASC"));
            $nights = $genRes->getTotalStayedNights($reservations, $service_time);
            array_push($total_nights, $nights);
        }

        return $this->render('mycpBundle:reservation:checkIn.html.twig', array(
                    'list' => $checkins,
                    'filter_date_from' => $filter_date_from,
                    'sort_by' => $sort_by,
                    'nights' => $total_nights));
    }

    private function updateICal($em,$roomId) {
        try {
            $calendarService = $this->get('mycp.service.calendar');
            $room = $em->getRepository("mycpBundle:room")->find($roomId);
            $calendarService->createICalForRoom($room->getRoomId(), $room->getRoomCode());
            return "Se actualizó satisfactoriamente el fichero .ics asociado a esta habitación.";
        } catch (\Exception $e) {
            var_dump( "Ha ocurrido un error mientras se actualizaba el fichero .ics de la habitación. Error: " . $e->getMessage());
            exit;
        }
    }

    public function newCleanOfferAction($idClient)
    {
        $em = $this->getDoctrine()->getManager();

        $client = null;
        $tourist = null;

        if($idClient != null && $idClient != "null" && $idClient != "")
        {
            $client = $em->getRepository("mycpBundle:user")->find($idClient);
            $tourist = $em->getRepository("mycpBundle:userTourist")->findOneBy(array('user_tourist_user' => $client->getUserId()));

            if($tourist == null)
            {
                $defaultLangCode = $this->container->getParameter('configuration.default.language.code');
                $defaultCurrencyCode = $this->container->getParameter('configuration.default.currency.code');
                $tourist = $em->getRepository('mycpBundle:userTourist')->createDefaultTourist($defaultLangCode, $defaultCurrencyCode, $client);
            }
        }

        if ($this->getRequest()->getMethod() == 'POST') {
            $request = $this->getRequest();
            $reservationService = $this->get("mycp.reservation.service");
            $resultReservations = $reservationService->createAvailableOfferFromRequest($request, $client);
            $newReservations = $resultReservations['reservations'];
            $arrayNightsByOwnershipReservation = $resultReservations['nights'];
            $general_reservation = $resultReservations['generalReservation'];
            $operation = $request->get("save_operation");

            $message = 'Nueva oferta ' . $general_reservation->getCASId() . ' creada satisfactoriamente.';
            $this->get('session')->getFlashBag()->add('message_ok', $message);

            switch($operation)
            {
                case Operations::SAVE_OFFER_AND_SEND: {
                    //Enviar correo al cliente incluyendo el texto
                                                          

                    return $this->redirect($this->generateUrl('mycp_list_reservations'));
                }
                case Operations::SAVE_OFFER_AND_VIEW:
                {
                    return $this->redirect($this->generateUrl('mycp_details_reservation', array('id_reservation' => $general_reservation->getGenResId())));
                }
                case Operations::SAVE_AND_EXIT:
                {
                    return $this->redirect($this->generateUrl('mycp_list_reservations'));
                }
            }

        }


        return $this->render('mycpBundle:reservation:newCleanOffer.html.twig', array(
            'client' => $client, 'tourist' => $tourist));
    }

    public function searchClientsAction()
    {
        $em = $this->getDoctrine()->getManager();
        $touristClients = $em->getRepository("mycpBundle:user")->findBy(array("user_role" => "ROLE_CLIENT_TOURIST"), array("user_user_name" => "ASC"));

        return $this->render('mycpBundle:utils:searchClientsTab.html.twig', array(
            'users' => $touristClients));
    }

    public function searchAccommodationsAction()
    {
        $em = $this->getDoctrine()->getManager();
        $ownerships = $em->getRepository("mycpBundle:ownership")->findBy(array("own_status" => OwnershipStatuses::ACTIVE), array("own_mcp_code" => "ASC"));

        return $this->render('mycpBundle:utils:offerAccommodationControl.html.twig', array(
            'ownerships' => $ownerships));
    }
}

