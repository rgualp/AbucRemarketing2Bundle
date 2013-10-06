<?php

namespace MyCp\FrontendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use MyCp\mycpBundle\Entity\ownershipReservation;
use MyCp\mycpBundle\Entity\booking;
use MyCp\mycpBundle\Entity\generalReservation;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Email;

class reservationController extends Controller
{

    public function get_count_cart_itemsAction(Request $request)
    {
        $services = array();
        if ($request->getSession()->get('services_pre_reservation'))
            $services = $request->getSession()->get('services_pre_reservation');
        return new Response(count($services));
    }

    public function clearAction(Request $request)
    {
        $request->getSession()->remove('services_pre_reservation');
        $trans = $this->get('translator');
        //var_dump($trans);exit();
        $message = $trans->trans('CART_EMPTY_SUCCESSFUL');
        //var_dump($message); exit();
        $this->get('session')->setFlash('message_global_success', $message);
        return $this->redirect($this->generateUrl('frontend_review_reservation'));
    }

    public function add_to_cartAction($id_ownership, Request $request)
    {
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
                    $serv['kids'] == $array_count_kids[$a] && $serv['ownership'] = $id_ownership
                ) {
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
                $service['ownership_prov'] = $ownership->getOwnAddressProvince()->getProvName();
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

    public function remove_from_cartAction($data, Request $request)
    {
        $array_data = explode('-', $data);
        $services = $request->getSession()->get('services_pre_reservation');
        $service = $services[$data[0] - 1];
        if ($service['from_date'] == $array_data[1]) {
            $service['from_date'] += 86400;
        } else if ($service['to_date'] == $array_data[1]) {
            $service['to_date'] -= 86400;
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

    public function reviewAction(Request $request)
    {
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

    public function get_body_review_reservationAction(Request $request)
    {
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

    public function review_confirmAction(Request $request)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $user = $this->get('security.context')->getToken()->getUser();
        $services = array();
        $reservations = array();
        $own_ids = array();
        $gen_res = array();

        $array_photos = array();
        if ($request->getSession()->get('services_pre_reservation'))
            $services = $request->getSession()->get('services_pre_reservation');
        $keys = array_keys($services);
        if (count($services) > 0) {
            $res_array = array();
            $own_visited = array();
            foreach ($services as $service) {
                $res_own_id = $service['ownership_id'];

                $array_group_by_own_id = array();
                $flag = 1;
                foreach ($own_visited as $own) {
                    if ($own == $res_own_id) {
                        $flag = 0;
                    }
                }
                if ($flag == 1)
                    foreach ($services as $item) {
                        if ($res_own_id == $item['ownership_id']) {
                            array_push($array_group_by_own_id, $item);
                        }
                    }
                array_push($res_array, $array_group_by_own_id);
                array_push($own_visited, $res_own_id);
            }
            $service_time = $this->get('Time');
            $nigths = array();
            foreach ($res_array as $res_item) {
                if (isset($res_item[0])) {
                    $ownership = $em->getRepository('mycpBundle:ownership')->find($res_item[0]['ownership_id']);
                    $general_reservation = new generalReservation();
                    $general_reservation->setGenResUserId($user);
                    $general_reservation->setGenResDate(new \DateTime(date('Y-m-d')));
                    $general_reservation->setGenResStatusDate(new \DateTime(date('Y-m-d')));
                    $general_reservation->setGenResStatus(0);
                    $general_reservation->setGenResFromDate(new \DateTime(date("Y-m-d H:i:s", $res_item[0]['from_date'])));
                    $general_reservation->setGenResToDate(new \DateTime(date("Y-m-d H:i:s", $res_item[count($res_item) - 1]['to_date'])));
                    $general_reservation->setGenResSaved(0);
                    $general_reservation->setGenResOwnId($ownership);

                    $total_price = 0;
                    $partial_total_price = array();
                    foreach ($res_item as $item) {
                        $array_dates = $service_time->dates_between($item['from_date'], $item['to_date']);
                        $temp_price = 0;
                        for ($a = 0; $a < count($array_dates); $a++) {
                            if ($a < count($array_dates) - 1) {
                                $season = $service_time->season_by_date($array_dates[$a]);
                                if ($season == 'down') {
                                    if ($item['room_type'] == "Habitación Triple" && $item['guests'] + $item['kids'] >= 3) {
                                        $total_price += $item['room_price_down'] + 10;
                                        $temp_price += $item['room_price_down'] + 10;
                                    }
                                    else {
                                        $total_price += $item['room_price_down'];
                                        $temp_price += $item['room_price_down'];

                                    }
                                }
                                else {
                                    if ($item['room_type'] == "Habitación Triple" && $item['guests'] + $item['kids'] >= 3) {
                                        $total_price += $item['room_price_top'] + 10;
                                        $temp_price += $item['room_price_top'] + 10;

                                    }
                                    else {
                                        $total_price += $item['room_price_top'];
                                        $temp_price += $item['room_price_top'];
                                    }
                                }
                            }

                        }
                        array_push($partial_total_price, $temp_price);
                    }
                    $general_reservation->setGenResTotalInSite($total_price);
                    $em->persist($general_reservation);

                    $flag_1 = 0;
                    foreach ($res_item as $item) {

                        $ownership_reservation = new ownershipReservation();
                        $ownership_reservation->setOwnResCountAdults($item['guests']);
                        $ownership_reservation->setOwnResCountChildrens($item['kids']);
                        $ownership_reservation->setOwnResNightPrice(0);
                        $ownership_reservation->setOwnResStatus(0);
                        $ownership_reservation->setOwnResReservationFromDate(new \DateTime(date("Y-m-d H:i:s", $item['from_date'])));
                        $ownership_reservation->setOwnResReservationToDate(new \DateTime(date("Y-m-d H:i:s", $item['to_date'])));
                        $ownership_reservation->setOwnResSelectedRoomId($item['room']);
                        $ownership_reservation->setOwnResGenResId($general_reservation);
                        $ownership_reservation->setOwnResRoomType($item['room_type']);
                        $ownership_reservation->setOwnResTotalInSite($partial_total_price[$flag_1]);
                        array_push($reservations, $ownership_reservation);

                        $ownership_photo = $em->getRepository('mycpBundle:ownership')->get_ownership_photo($ownership_reservation->getOwnResGenResId()->getGenResOwnId()->getOwnId());
                        array_push($array_photos, $ownership_photo);

                        $array_dates = $service_time->dates_between($ownership_reservation->getOwnResReservationFromDate()->getTimestamp(), $ownership_reservation->getOwnResReservationToDate()->getTimestamp());
                        array_push($nigths, count($array_dates) - 1);

                        $em->persist($ownership_reservation);
                        $em->flush();
                        array_push($own_ids, $ownership_reservation->getOwnResId());
                        $flag_1++;

                    }

                }
            }

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

        // Enviando mail al cliente
        $body = $this->render('frontEndBundle:mails:email_check_available.html.twig', array(
            'user' => $user,
            'reservations' => $reservations,
            'ids' => $own_ids,
            'nigths' => $nigths,
            'photos' => $array_photos
        ));
        $locale = $this->get('translator');
        $subject = $locale->trans('VIEW_DETAILS');
        $service_email = $this->get('Email');
        $service_email->send_email(
            $subject, 'reservation@mycasaparticular.com', 'MyCasaParticular.com', $user->getUserEmail(), $body
        );

        //Enviando mail al reservation team
        $user_tourist = $em->getRepository('mycpBundle:userTourist')->findOneBy(array('user_tourist_user' => $user->getUserId()));
        $array_own_res_home = array();
        foreach ($reservations as $gen) {
            $offset = $gen->getOwnResGenResId()->getGenResId();
            $temp = array();
            if (isset($array_own_res_home[$offset]))
                $temp = $array_own_res_home[$offset];
            array_push($temp, $gen);
            $array_own_res_home[$offset] = $temp;

        }
        $flag_3 = 0;
        $service_email = $this->get('Email');
        foreach ($array_own_res_home as $own_array) {
            $temp_nigths = array_slice($nigths, $flag_3, count($own_array));
            $flag_3 = count($own_array);
            $body = $this->render('frontEndBundle:mails:rt_email_check_available.html.twig', array(
                'user' => $user,
                'user_tourist' => $user_tourist,
                'reservations' => $own_array,
                'nigths' => $temp_nigths,
            ));
            $subject = "MyCasaParticular Reservas - " . strtoupper($user_tourist->getUserTouristLanguage()->getLangCode());
            $service_email->send_email(
                $subject, 'no.reply@mycasaparticular.com', $subject, $user->getUserEmail(), $body
            );
        }

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

    public function redirect_reservation_reservationAction(Request $request)
    {

        if ($request->getMethod() == "POST") {
            $post = $request->request->getIterator()->getArrayCopy();
            $keys = array_keys($post);
            if (!$keys) {
                $message = $this->get('translator')->trans("PLEASE_SELECT_RESERVATION");
                $this->get('session')->setFlash('message_global_success', $message);
                return $this->redirect($this->generateUrl('frontend_mycasatrip_available'));
            }

            $array_ids = array();
            foreach ($keys as $key) {
                array_push($array_ids, str_replace('checkbox_', '', $key));
            }
            $request->getSession()->set('reservation_own_ids', $array_ids);
            return $this->redirect($this->generateUrl('frontend_reservation_reservation'));
        }
        else
            return $this->redirect($this->generateUrl('frontend_mycasatrip_available'));
    }

    public function reservation_reservationAction(Request $request)
    {
        $session = $request->getSession();

        $array_ids = $session->get('reservation_own_ids');
        if (!$array_ids) {
            return $this->redirect($this->generateUrl('frontend_mycasatrip_available'));
        }
        $service_time = $this->get('time');
        $em = $this->getDoctrine()->getEntityManager();
        $user = $this->get('security.context')->getToken()->getUser();
        $userTourist = $em->getRepository('mycpBundle:userTourist')->findOneBy(array('user_tourist_user' => $user->getUserId()));
        $reservations = array();
        foreach ($array_ids as $id) {
            array_push($reservations, $em->getRepository('mycpBundle:ownershipReservation')->find($id));
        }

        $min_date = $reservations[0]->getOwnResReservationFromDate()->getTimestamp();
        $max_date = $reservations[0]->getOwnResReservationFromDate()->getTimestamp();
        $array_reservations_timestamp = array();
        $array_partial_dates = array();
        $array_limits_dates = array();
        $total_price = 0;
        $total_percent_price = 0;
        $commissions = array();
        foreach ($reservations as $reservation) {
            if ($min_date > $reservation->getOwnResReservationFromDate()->getTimestamp()) {
                $min_date = $reservation->getOwnResReservationFromDate()->getTimestamp();
            }

            if ($max_date < $reservation->getOwnResReservationToDate()->getTimestamp()) {
                $max_date = $reservation->getOwnResReservationToDate()->getTimestamp();
            }
            $temp[0] = $reservation->getOwnResReservationFromDate()->getTimestamp();
            $temp[1] = $reservation->getOwnResReservationToDate()->getTimestamp();
            array_push($array_reservations_timestamp, $temp);

            $array_dates_temp = $service_time->dates_between($reservation->getOwnResReservationFromDate()->getTimestamp(), $reservation->getOwnResReservationToDate()->getTimestamp());
            foreach ($array_dates_temp as $temp2) {
                $array_partial_dates[$temp2] = '1';
            }
            $commission = $reservation->getOwnResGenResId()->GetGenResOwnId()->getOwnCommissionPercent();
            $array_limits_dates[$reservation->getOwnResReservationToDate()->getTimestamp()] = 1;
            $total_price += $reservation->getOwnResNightPrice() * (count($array_dates_temp) - 1);
            $total_percent_price += $reservation->getOwnResNightPrice() * (count($array_dates_temp) - 1) * $commission / 100;

            $insert = 1;
            foreach ($commissions as $com) {
                if ($com == $commission) {
                    $insert = 0;
                    break;
                }
            }
            if ($insert == 1) {
                array_push($commissions, $commission);
            }

        }
        $array_dates = $service_time->dates_between($min_date, $max_date);

        $array_dates_string = array();
        foreach ($array_dates as $date) {
            array_push($array_dates_string, \date('d/m/Y', $date));
        }
        $errors = null;
        $post = null;
        $post_country = null;

        if ($request->getMethod() == "POST") {
            $errors = array();
            $post = $request->request->getIterator()->getArrayCopy();

            $post_country = $em->getRepository('mycpBundle:country')->find($post['user_tourist_nationality']);
            $not_blank_validator = new NotBlank();
            $not_blank_validator->message = $this->get('translator')->trans("FILL_FIELD");
            $array_keys = array_keys($post);
            $count = $errors_validation = $count_errors = 0;

            foreach ($post as $item) {
                $errors[$array_keys[$count]] = $errors_validation = $this->get('validator')->validateValue($item, $not_blank_validator);
                $count_errors += count($errors_validation);
                $count++;
            }

            $email_validator = new Email();
            $email_validator->message = $this->get('translator')->trans("INVALID_EMAIL");
            $errors['user_tourist_email'] = $errors_validation = $this->get('validator')->validateValue($post['user_tourist_email'], $email_validator);
            $count_errors += count($errors_validation);
            $count++;
            $errors['user_tourist_email_confirm'] = $errors_validation = $this->get('validator')->validateValue($post['user_tourist_email_confirm'], $email_validator);
            $count_errors += count($errors_validation);
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
                if (isset($post['user_tourist_send_newsletter']))
                    $user->setUserNewsletters(1);
                $em->persist($user);

                $userTourist->setUserTouristGender($post['user_tourist_gender']);
                $userTourist->setUserTouristPostalCode($post['user_tourist_zip_code']);
                $userTourist->setUserTouristCell($post['user_tourist_cell']);
                $em->persist($userTourist);
                $em->flush();

                $own_reservations = array();
                if ($session->get('reservation_own_ids'))
                    $own_reservations = $session->get('reservation_own_ids');


                $booking = new booking();

                if (isset($post['protection']))
                    $booking->setBookingCancelProtection(1);
                else
                    $booking->setBookingCancelProtection(0);

                $curr_rate = $session->get('curr_rate');
                if (!$curr_rate) $curr_rate = 1;

                $booking->setBookingCurrency($userTourist->getUserTouristCurrency());
                $booking->setBookingPrepay(($total_percent_price + 10) * $curr_rate);
                $booking->setBookingUserId($user->getUserId());
                $booking->setBookingUserDates($user->getUserUserName() . ', ' . $user->getUserEmail());
                $em->persist($booking);

                foreach ($own_reservations as $own_res) {
                    $own = new ownershipReservation();
                    $own = $em->getRepository('mycpBundle:ownershipReservation')->find($own_res);
                    $own->setOwnResReservationBooking($booking);
                    $own->setOwnResStatus(2);
                    $em->persist($own);
                }
                $em->flush();
                $request->getSession()->set('reservation_own_ids', null);
                $all_own_available = $em->getRepository('mycpBundle:ownershipReservation')->find_by_user_and_status_object($user->getUserId(), 1);

                foreach ($all_own_available as $own) {
                    $em->remove($own);
                }
                $em->flush();

                $bookingId = $booking->getBookingId();
                return $this->forward('frontEndBundle:payment:skrillPayment', array('bookingId' => $bookingId));
            }
        }
        $countries = $em->getRepository('mycpBundle:country')->findAll();
        return $this->render('frontEndBundle:reservation:reservation.html.twig', array(
            'limit_dates' => $array_limits_dates,
            'dates_string' => $array_dates_string,
            'dates_partial' => $array_partial_dates,
            'dates' => $array_dates,
            'user_tourist' => $userTourist,
            'user' => $user,
            'countries' => $countries,
            'reservations' => $reservations,
            'reservations_timestamp' => $array_reservations_timestamp,
            'total_price' => $total_price,
            'errors' => $errors,
            'commissions' => $commissions,
            'total_percent_price' => $total_percent_price,
            'post' => $post,
            'post_country' => $post_country,
        ));

    }

    function confirmationAction($id_booking)
    {
        $service_time = $this->get('Time');
        $user = $this->get('security.context')->getToken()->getUser();
        $em = $this->getDoctrine()->getManager();
        $own_res = $em->getRepository('mycpBundle:ownershipReservation')->findBy(array('own_res_reservation_booking' => $id_booking));
        $booking = $em->getRepository('mycpBundle:booking')->find($id_booking);
        $nights = array();
        $rooms = array();
        $commissions = array();
        $total_price = 0;
        $total_percent_price = 0;
        foreach ($own_res as $own) {
            $array_dates = $service_time->dates_between($own->getOwnResReservationFromDate()->getTimestamp(), $own->getOwnResReservationToDate()->getTimestamp());
            array_push($nights, count($array_dates) - 1);
            array_push($rooms, $em->getRepository('mycpBundle:room')->find($own->getOwnResSelectedRoomId()));
            $total_price += $own->getOwnResNightPrice() * (count($array_dates) - 1);
            $commission = $own->getOwnResGenResId()->GetGenResOwnId()->getOwnCommissionPercent();
            $total_percent_price += $own->getOwnResNightPrice() * (count($array_dates) - 1) * $commission / 100;
            $insert = 1;
            foreach ($commissions as $com) {
                if ($com == $commission) {
                    $insert = 0;
                    break;
                }
            }
            if ($insert == 1) {
                array_push($commissions, $commission);
            }
            $general=$own->getOwnResGenResId();
            $general->setGenResStatus(2);
            $own->setOwnResStatus(5);
            $em->persist($own);
            $em->persist($general);
        }
        $em->flush();
        return $this->render('frontEndBundle:reservation:confirmReservation.html.twig', array(
            'own_res' => $own_res,
            'user' => $user,
            'booking' => $booking,
            'nights' => $nights,
            'rooms' => $rooms,
            'total_price' => $total_price,
            'total_percent_price' => $total_percent_price,
            'commissions' => $commissions
        ));
    }

}
