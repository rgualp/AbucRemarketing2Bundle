<?php

namespace MyCp\FrontendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use MyCp\mycpBundle\Entity\ownershipReservation;
use MyCp\mycpBundle\Entity\generalReservation;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Email;

class reservationController extends Controller {

    public function get_count_cart_itemsAction(Request $request) {
        $services = array();
        if ($request->getSession()->get('services_pre_reservation'))
            $services = $request->getSession()->get('services_pre_reservation');
        return new Response(count($services));
    }

    public function clearAction(Request $request) {
        $request->getSession()->remove('services_pre_reservation');
        $trans = $this->get('translator');
        //var_dump($trans);exit();
        $message = $trans->trans('CART_EMPTY_SUCCESSFUL');
        //var_dump($message); exit();
        $this->get('session')->setFlash('message_global_success', $message);
        return $this->redirect($this->generateUrl('frontend_review_reservation'));
    }

    public function add_to_cartAction($id_ownership, Request $request) {
        $em = $this->getDoctrine()->getEntityManager();
        $ownership = $em->getRepository('mycpBundle:ownership')->find($id_ownership);
        if (!$request->get('data_reservation'))
            throw $this->createNotFoundException();
        $data = $request->get('data_reservation');
        //var_dump($data); exit();
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

        $services = array();
        if ($request->getSession()->get('services_pre_reservation'))
            $services = $request->getSession()->get('services_pre_reservation');

        for ($a = 0; $a < count($array_ids_rooms); $a++) {
            $insert = 1;
            $id = 0;
            foreach ($services as $serv) {
                if (isset($array_count_guests[$a]) && isset($array_count_kids[$a]) && $serv['from_date'] == $start_timestamp && $serv['to_date'] == $end_timestamp &&
                        $serv['room'] == $array_ids_rooms[$a] && $serv['guests'] == $array_count_guests[$a] &&
                        $serv['kids'] == $array_count_kids[$a] && $serv['ownership'] = $id_ownership) {
                    $insert = 0;
                }
                if ($serv['id'] > $id)
                    $id = $serv['id'];
            }
            if ($insert == 1) {
                $room = $em->getRepository('mycpBundle:room')->find($array_ids_rooms[$a]);
                $service['id'] = $id + 1;
                $service['from_date'] = $start_timestamp;
                $service['to_date'] = $end_timestamp;
                $service['room'] = $array_ids_rooms[$a];
                if (isset($array_count_kids[$a]))
                    $service['guests'] = $array_count_guests[$a];
                else
                    $service['guests'] = 1;
                if (isset($array_count_kids[$a]))
                    $service['kids'] = $array_count_kids[$a];
                else
                    $service['kids'] = 0;
                $service['ownership_name'] = $ownership->getOwnName();
                $service['ownership_id'] = $ownership->getOwnId();
                $service['ownership_mun'] = $ownership->getOwnAddressMunicipality()->getMunName();
                $service['ownership_percent'] = $ownership->getOwnCommissionPercent();
                $service['room_id'] = $room->getRoomId();
                $service['room_type'] = $room->getRoomType();
                $service['room_price_top'] = $room->getRoomPriceUpFrom();
                $service['room_price_down'] = $room->getRoomPriceDownFrom();
                array_push($services, $service);
            }
        }

        $request->getSession()->set('services_pre_reservation', $services);
        $request->getSession()->set('services_pre_reservation_last_own', $id_ownership);
        return $this->redirect($this->generateUrl('frontend_review_reservation'));
    }

    public function remove_from_cartAction($data, Request $request) {
        $array_data = explode('-', $data);
        $services = $request->getSession()->get('services_pre_reservation');
        $service = $services[$data[0] - 1];
        if ($service['from_date'] == $array_data[1]) {
            $service['from_date']+=86400;
        } else if ($service['to_date'] == $array_data[1]) {
            $service['to_date']-=86400;
        }

        if ($array_data[1] < $service['to_date'] && $array_data[1] > $service['from_date']) {
            $service_next = $service;
            $service['to_date'] = $array_data[1] - 86400;
            $service_next['from_date'] = $array_data[1] + 86400;
            $id = 0;
            foreach ($services as $serv) {
                if ($serv['id'] > $id)
                    $id = $serv['id'];
            }
            $service_next['id'] = $id + 1;
            array_push($services, $service_next);
        }



        if ($service['to_date'] < $service['from_date'])
            $service = 0;
        $services[$data[0] - 1] = $service;

        $keys = array_keys($services);
        foreach ($keys as $key) {
            if ($services[$key] == 0)
                unset($services[$key]);

            if ($services[$key]['from_date'] == $services[$key]['to_date'])
                unset($services[$key]);
        }
        //var_dump($services);

        $request->getSession()->set('services_pre_reservation', $services);

        if (count($services) < 1) {
            return new Response('0');
        }
        return $this->get_body_review_reservationAction($request);
    }

    public function reviewAction(Request $request) {
        $em = $this->getDoctrine()->getEntityManager();
        $last_own = $request->getSession()->get('services_pre_reservation_last_own');
        if ($last_own)
            $ownership = $em->getRepository('mycpBundle:ownership')->find($last_own);
        else
            $ownership = 0;
        $services = array();
        if ($request->getSession()->get('services_pre_reservation'))
            $services = $request->getSession()->get('services_pre_reservation');

        return $this->render('frontEndBundle:reservation:reviewReservation.html.twig', array(
                    'services' => $services,
                    'ownership' => $ownership,
        ));
    }

    public function get_body_review_reservationAction(Request $request) {
        $services = array();
        if ($request->getSession()->get('services_pre_reservation'))
            $services = $request->getSession()->get('services_pre_reservation');

        $min_date = 0;
        $max_date = 0;

        if (isset($services[0]))
            $min_date = $services[0]['from_date'];

        if (isset($services[0]))
            $max_date = $services[0]['to_date'];


        foreach ($services as $serv) {
            if ($serv['from_date'] < $min_date)
                $min_date = $serv['from_date'];
            if ($serv['to_date'] > $max_date)
                $max_date = $serv['to_date'];
        }

        $service_time = $this->get('Time');
        $array_dates = $service_time->dates_between($min_date, $max_date);
        $array_dates_string = array();
        $array_season = array();
        $array_clear_date = array();
        //var_dump($services);
        if ($array_dates)
            foreach ($array_dates as $date) {
                array_push($array_dates_string, \date('d/m/Y', $date));
                $season = $service_time->season_by_date($date);
                array_push($array_season, $season);
                $insert = 1;
                foreach ($services as $serv) {
                    if ($date >= $serv['from_date'] && $date <= $serv['to_date']) {
                        $insert = 0;
                    }
                }
                if ($insert == 1) {
                    $array_clear_date[$date] = 1;
                }
            }
        return $this->render('frontEndBundle:reservation:bodyReviewReservation.html.twig', array(
                    'dates_string' => $array_dates_string,
                    'dates_timestamp' => $array_dates,
                    'services' => $services,
                    'array_season' => $array_season,
                    'array_clear_date' => $array_clear_date
        ));
    }

    public function review_confirmAction(Request $request) {
        $em = $this->getDoctrine()->getEntityManager();
        $user = $this->get('security.context')->getToken()->getUser();
        $services = array();
        $reservations = array();
        $array_photos = array();
        if ($request->getSession()->get('services_pre_reservation'))
            $services = $request->getSession()->get('services_pre_reservation');
        $keys = array_keys($services);
        if (count($services) > 0) {
            $general_reservation = new generalReservation();
            $general_reservation->setGenResUserId($user);
            $general_reservation->setGenResDate(new \DateTime(date('Y-m-d')));
            $general_reservation->setGenResStatusDate(new \DateTime(date('Y-m-d')));
            $general_reservation->setGenResStatus(0);
            $general_reservation->setGenResFromDate(new \DateTime(date("Y-m-d H:i:s", $services[$keys[0]]['from_date'])));
            $general_reservation->setGenResToDate(new \DateTime(date("Y-m-d H:i:s", $services[$keys[count($services) - 1]]['to_date'])));
            $general_reservation->setGenResSaved(0);
            $em->persist($general_reservation);
            $em->flush();

            foreach ($services as $service) {
                $total_price = 0;
                $service_time = $this->get('Time');
                $array_dates = $service_time->dates_between($service['from_date'], $service['to_date']);
                $ownership = $em->getRepository('mycpBundle:ownership')->find($service['ownership_id']);
                for ($a = 0; $a < count($array_dates); $a++) {
                    if ($a < count($array_dates) - 1) {
                        $season = $service_time->season_by_date($array_dates[$a]);
                        if ($season == 'down') {
                            if ($service['room_type'] == "Habitación Triple" && $service['guests'] + $service['kids'] >= 3)
                                $total_price += $service['room_price_down'] + 10;
                            else
                                $total_price += $service['room_price_down'];
                        }
                        else {
                            if ($service['room_type'] == "Habitación Triple" && $service['guests'] + $service['kids'] >= 3)
                                $total_price += $service['room_price_top'] + 10;
                            else
                                $total_price += $service['room_price_top'];
                        }
                    }
                }

                $ownership_reservation = new ownershipReservation();
                $ownership_reservation->setOwnResCountAdults($service['guests']);
                $ownership_reservation->setOwnResCountChildrens($service['kids']);
                $ownership_reservation->setOwnResNightPrice(0);
                $ownership_reservation->setOwnResStatus(0);
                $ownership_reservation->setOwnResReservationFromDate(new \DateTime(date("Y-m-d H:i:s", $service['from_date'])));
                $ownership_reservation->setOwnResReservationToDate(new \DateTime(date("Y-m-d H:i:s", $service['to_date'])));
                $ownership_reservation->setOwnResSelectedRoomId($service['room']);
                $ownership_reservation->setOwnResGenResId($general_reservation);
                $ownership_reservation->setOwnResOwnId($ownership);
                $ownership_reservation->setOwnResRoomType($service['room_type']);
                $ownership_reservation->setOwnResTotalInSite($total_price);

                $photos = $em->getRepository('mycpBundle:ownership')->getPhotos($ownership->getOwnId());
                array_push($array_photos, $photos);
                $em->persist($ownership_reservation);
                array_push($reservations, $ownership_reservation);
                //var_dump($reservations);
            }
            $em->flush();
        }
        else {
            return $this->redirect($this->generateUrl('frontend_review_reservation'));
        }

        $request->getSession()->set('services_pre_reservation', null);


        /*
         * Hallando otros ownerships en el mismo destino
         */
        $user_ids = $em->getRepository('mycpBundle:user')->user_ids($this);
        $ownership = $em->getRepository('mycpBundle:ownership')->find($services[0]['ownership_id']);

        $owns_in_destination = $em->getRepository("mycpBundle:destination")->ownsership_nearby_destination($ownership->getOwnAddressMunicipality()->getMunId(), $ownership->getOwnAddressProvince()->getProvId(), 4, $services[0]['ownership_id']);
        $owns_in_destination_photo = $em->getRepository("mycpBundle:ownership")->get_photos_array($owns_in_destination);
        $owns_in_destination_favorities = $em->getRepository('mycpBundle:favorite')->is_in_favorite_array($owns_in_destination, true, $user_ids['user_id'], $user_ids['session_id']);

        $locale = $this->get('translator')->getLocale();
        $destinations = $em->getRepository('mycpBundle:destination')->destination_filter(null, $ownership->getOwnAddressProvince()->getProvId(), null, $ownership->getOwnAddressMunicipality()->getMunId(), 2);
        $destinations_photos = $em->getRepository('mycpBundle:destination')->get_destination_photos($destinations);
        $destinations_descriptions = $em->getRepository('mycpBundle:destination')->get_destination_description($destinations, $locale);
        $destinations_favorities = $em->getRepository('mycpBundle:favorite')->is_in_favorite_array($destinations, false, $user_ids['user_id'], $user_ids['session_id']);
        $destinations_count = $em->getRepository('mycpBundle:destination')->get_destination_owns_statistics($destinations);

        //var_dump($reservations); exit();
        // Enviando mail al cliente
        $body = $this->render('frontEndBundle:mails:email_check_available.html.twig', array(
            'user' => $user,
            'reservations' => $reservations,
            'photos' => $array_photos
        ));

        $locale = $this->get('translator');
        $subject = $locale->trans('VIEW_DETAILS');
        $service_email = $this->get('Email');
        $service_email->send_email(
                $subject, 'reservation@mycasaparticular.com', 'MyCasaParticular.com', $user->getUserEmail(), $body
        );
        //Enviando mail al reservation team


        return $this->render('frontEndBundle:reservation:confirmReview.html.twig', array(
                    "owns_in_destination" => $owns_in_destination,
                    "owns_in_destination_photos" => $owns_in_destination_photo,
                    "owns_in_destination_favorities" => $owns_in_destination_favorities,
                    "owns_in_destination_total" => count($em->getRepository("mycpBundle:destination")->ownsership_nearby_destination($ownership->getOwnAddressMunicipality()->getMunId(), $ownership->getOwnAddressProvince()->getProvId())),
                    "other_destinations" => $destinations,
                    "other_destinations_photos" => $destinations_photos,
                    "other_destinations_descriptions" => $destinations_descriptions,
                    "other_destinations_favorities" => $destinations_favorities,
                    "other_destinations_count" => $destinations_count
        ));
    }

    public function reservation_reservationAction($id_reservation, Request $request) {

        $em = $this->getDoctrine()->getEntityManager();
        $user = $this->get('security.context')->getToken()->getUser();
        $userTourist = $em->getRepository('mycpBundle:userTourist')->findOneBy(array('user_tourist_user' => $user->getUserId()));
        $reservation = $em->getRepository('mycpBundle:ownershipReservation')->get_reservation_available_by_user($id_reservation, $user->getUserId());
        $service_time = $this->get('Time');
        $start_timestamp = $reservation[0]['own_res_reservation_from_date']->getTimestamp();
        $end_timestamp = $reservation[0]['own_res_reservation_to_date']->getTimestamp();
        $array_dates = $service_time->dates_between($start_timestamp, $end_timestamp);

        $errors = array();

        $post = null;
        if ($this->getRequest()->getMethod() == 'POST') {
            $post = $request->request->getIterator()->getArrayCopy();

            /*             * *
             * Validacion
             */
            $not_blank_validator = new NotBlank();
            $not_blank_validator->message = $this->get('translator')->trans("FILL_FIELD");
            $array_keys = array_keys($post);
            $count = $errors_validation = $count_errors = 0;

            foreach ($post as $item) {
                $errors[$array_keys[$count]] = $errors_validation = $this->get('validator')->validateValue($item, $not_blank_validator);
                $count_errors+=count($errors_validation);
                $count++;
            }

            $email_validator = new Email();
            $email_validator->message = $this->get('translator')->trans("INVALID_EMAIL");
            $errors['user_tourist_email'] = $errors_validation = $this->get('validator')->validateValue($post['user_tourist_email'], $email_validator);
            $count_errors+=count($errors_validation);
            $count++;
            $errors['user_tourist_email_confirm'] = $errors_validation = $this->get('validator')->validateValue($post['user_tourist_email_confirm'], $email_validator);
            $count_errors+=count($errors_validation);
            $count++;

            if ($post['user_tourist_email'] != $post['user_tourist_email_confirm']) {
                $errors['user_tourist_email_confirm'] = $this->get('translator')->trans("NOT_EQUALS_EMAIL");
                //var_dump("No iguales ");
                $count_errors++;
            }


            if ($count_errors == 0) {

                $user->setUserEmail($post['user_tourist_email']);
                $user->setUserLastName($post['user_tourist_last_name']);
                $user->setUserUserName($post['user_tourist_name']);
                $user->setUserNewsletters($post['user_tourist_name']);
                $user->setUserPhone($post['user_tourist_name']);
                $user->setUserCountry($em->getRepository('mycpBundle:country')->find($post['user_tourist_nationality']));
                $em->persist($user);
                $em->flush();

                $userTourist->setUserTouristGender($post['user_tourist_gender']);
                $userTourist->setUserTouristPostalCode($post['user_tourist_zip_code']);
                $userTourist->setUserTouristCell($post['user_tourist_cell']);
                $em->persist($userTourist);
                $em->flush();

                $reservation_entity = $em->getRepository('mycpBundle:ownershipReservation')->find($reservation[0]['own_res_id']);

                $pre_payment=($reservation_entity->getOwnResNightPrice()*(count($array_dates)-1)) * $reservation_entity->getOwnResOwnId()->getOwnCommissionPercent()/100 + 10;
                $reservation_entity->setOwnResPrePayment($pre_payment);
                $reservation_entity->setOwnResHour($post['reservation_hour']);
                $em->persist($reservation_entity);
                $em->flush();
                //todo Jakob
                exit();
            }

        }

        if ($post != null)
            return $this->render('frontEndBundle:reservation:reservation.html.twig', array(
                        'user' => $user,
                        'user_tourist' => $userTourist,
                        'reservation' => $reservation,
                        'dates' => $array_dates,
                        'id_reservation' => $id_reservation,
                        'errors' => $errors,
                        'total_errors' => $count_errors,
                        'countries' => $em->getRepository("mycpBundle:country")->findBy(array(), array('co_name' => "ASC")),
                        'post' => $post,
                        'post_country' => (isset($post['user_tourist_nationality']) && $post['user_tourist_nationality'] != null) ? $em->getRepository('mycpBundle:country')->find($post['user_tourist_nationality'])->getCoName() : ""
            ));
        else
            return $this->render('frontEndBundle:reservation:reservation.html.twig', array(
                        'user' => $user,
                        'user_tourist' => $userTourist,
                        'reservation' => $reservation,
                        'dates' => $array_dates,
                        'id_reservation' => $id_reservation,
                        'errors' => $errors,
                        'countries' => $em->getRepository("mycpBundle:country")->findBy(array(), array('co_name' => "ASC"))
            ));
    }

    function confirmationAction($id_reservation) {
        $service_time = $this->get('Time');
        $user = $this->get('security.context')->getToken()->getUser();
        $em = $this->getDoctrine()->getManager();
        $own_res = $em->getRepository('mycpBundle:ownershipReservation')->find($id_reservation);
        $room = $em->getRepository('mycpBundle:room')->find($own_res->getOwnResSelectedRoomId());
        $array_rooms = array();
        $start_timestamp = $own_res->getOwnResReservationFromDate()->getTimestamp();
        $end_timestamp = $own_res->getOwnResReservationToDate()->getTimestamp();
        $array_dates = $service_time->dates_between($start_timestamp, $end_timestamp);
        $count_nights = count($array_dates) - 1;

        return $this->render('frontEndBundle:reservation:confirmReservation.html.twig', array(
                    'count_nights' => $count_nights,
                    'own_res' => $own_res,
                    'room' => $room
        ));
    }

}
