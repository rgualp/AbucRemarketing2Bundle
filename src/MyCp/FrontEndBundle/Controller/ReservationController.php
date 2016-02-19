<?php

namespace MyCp\FrontEndBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use MyCp\mycpBundle\Entity\ownershipReservation;
use MyCp\mycpBundle\Entity\booking;
use MyCp\mycpBundle\Entity\season;
use MyCp\mycpBundle\Entity\generalReservation;
use MyCp\mycpBundle\Entity\cart;
use Symfony\Component\Validator\Constraints\DateTime;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Email;
use MyCp\FrontEndBundle\Helpers\PaymentHelper;
use MyCp\FrontEndBundle\Helpers\ReservationHelper;

class ReservationController extends Controller {

    public function redirectReservationAction(Request $request) {

        if ($request->getMethod() == "POST") {
            $post = $request->request->getIterator()->getArrayCopy();
            $keys = array_keys($post);
            if (!$keys) {
                $message = $this->get('translator')->trans("PLEASE_SELECT_RESERVATION");
                $this->get('session')->getFlashBag()->add('message_no_select', $message);
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

    public function reservationAction(Request $request) {
        $session = $request->getSession();

        $array_ids = $session->get('reservation_own_ids');
        //var_dump($array_ids); exit();
        if (!$array_ids) {
            return $this->forward('FrontEndBundle:Mycasatrip:available', array('order_by' => 0));
            //return $this->redirect($this->generateUrl('frontend_mycasatrip_available'));
        }

        $service_time = $this->get('time');
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
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
        $rooms = array();
        $triple_room_recharge = $this->container->getParameter('configuration.triple.room.charge');
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

            $array_dates_temp = $service_time->datesBetween($reservation->getOwnResReservationFromDate()->getTimestamp(), $reservation->getOwnResReservationToDate()->getTimestamp());
            foreach ($array_dates_temp as $temp2) {
                $array_partial_dates[$temp2] = '1';
            }
            $commission = $reservation->getOwnResGenResId()->GetGenResOwnId()->getOwnCommissionPercent();
            $array_limits_dates[$reservation->getOwnResReservationToDate()->getTimestamp()][$reservation->getOwnResId()] = 1;
            $total_price_current_reservation = ReservationHelper::getTotalPrice($em, $service_time, $reservation, $triple_room_recharge);
            $total_price += $total_price_current_reservation;
            $total_percent_price += $total_price_current_reservation * $commission / 100;

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
        $array_dates_string = array();
        $array_dates_string_day = array();
        $array_dates = $service_time->datesBetween($min_date, $max_date);
        foreach ($array_dates as $date) {
            array_push($array_dates_string, \date('/m/Y', $date));
            array_push($array_dates_string_day, \date('d', $date));
        }

        $season_types = array();
        $season_types_temp = array();

        foreach ($array_dates as $date) {
            $season_types_temp = array();

            foreach ($reservations as $res) {
                $destination_id = ($res->getOwnResGenResId()->getGenResOwnId()->getOwnDestination() != null) ? $res->getOwnResGenResId()->getGenResOwnId()->getOwnDestination()->getDesId() : null;
                $seasons = $em->getRepository("mycpBundle:season")->getSeasons($min_date, $max_date, $destination_id);
                $season_types_temp[] = $service_time->seasonTypeByDate($seasons, $date);
            }

            $season_types[$date] = $season_types_temp;
        }

        $errors = null;
        $post = null;
        $post_country = null;
        $count_errors = 0;

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


            if (!\MyCp\FrontEndBundle\Helpers\Utils::validateEmail($post['user_tourist_email'])) {
                $errors['user_tourist_email'] = $email_validator->message;
                $count_errors++;
                $count++;
            }

            if (!\MyCp\FrontEndBundle\Helpers\Utils::validateEmail($post['user_tourist_email_confirm'])) {
                $errors['user_tourist_email_confirm'] = $email_validator->message;
                $count_errors++;
                $count++;
            }

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

                $currency = null;

                $price_in_currency = $em->getRepository('mycpBundle:currency')->findOneBy(array('curr_site_price_in' => true));

                if ($session->get('curr_acronym') == null OR $session->get('curr_acronym') == $price_in_currency->getCurrCode()) {
                    $currency = $em->getRepository('mycpBundle:currency')->findOneBy(array('curr_code' => 'USD'));
                } else {
                    $currency = $em->getRepository('mycpBundle:currency')->findOneBy(array('curr_code' => $session->get('curr_acronym')));
                }

                $booking->setBookingCurrency($currency);
                $configuration_service_fee = $this->container->getParameter('configuration.service.fee');
                $booking->setBookingPrepay(($total_percent_price + $configuration_service_fee) * $currency->getCurrCucChange());
                $booking->setBookingUserId($user->getUserId());
                $booking->setBookingUserDates($user->getUserUserName() . ', ' . $user->getUserEmail());
                $em->persist($booking);
                //var_dump($booking); exit();

                foreach ($own_reservations as $own_res) {
                    $own = $em->getRepository('mycpBundle:ownershipReservation')->find($own_res);
                    $own->setOwnResReservationBooking($booking);
                    $em->persist($own);

                    //Colocando la hora de llegada
                    $general_reservation = $own->getOwnResGenResId();
                    $general_reservation->setGenResArrivalHour($post['reservation_hour']);
                    $em->persist($general_reservation);
                }
                $em->flush();
                $request->getSession()->set('reservation_own_ids', null);
                $all_own_available = $em->getRepository('mycpBundle:ownershipReservation')->getByUserAndStatus($user->getUserId(), 1);

                /* foreach ($all_own_available as $own) {
                  $own->
                  $em->persist($own);
                  }
                  $em->flush(); */

                $bookingId = $booking->getBookingId();
                return $this->forward('FrontEndBundle:Payment:skrillPayment', array('bookingId' => $bookingId));
            }
        }
        $countries = $em->getRepository('mycpBundle:country')->findAll();

        return $this->render('FrontEndBundle:reservation:reservation.html.twig', array(
                    'limit_dates' => $array_limits_dates,
                    'dates_string' => $array_dates_string,
                    'dates_string_day' => $array_dates_string_day,
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
                    'total_errors' => $count_errors,
                    'seasons' => $season_types
        ));
    }

    public function confirmationAction($id_booking) {
        $em = $this->getDoctrine()->getManager();
        $payment = $em->getRepository('mycpBundle:payment')->findOneBy(array('booking' => $id_booking));

        if (empty($payment)) {
            throw $this->createNotFoundException();
        }

        switch ($payment->getStatus()) {
            case PaymentHelper::STATUS_PENDING:
            case PaymentHelper::STATUS_SUCCESS:
            case PaymentHelper::STATUS_PROCESSED:
                return $this->renderPaymentConfirmationPage($id_booking);

            case PaymentHelper::STATUS_CANCELLED:
            case PaymentHelper::STATUS_FAILED:
                return $this->forward('FrontEndBundle:Reservation:reservation');

            default:
                throw $this->createNotFoundException();
        }
    }

    private function renderPaymentConfirmationPage($id_booking) {
        $url = $this->generateUrl('frontend_view_confirmation_reservation', array('id_booking' => $id_booking));
        return $this->render('FrontEndBundle:reservation:afterpayment.html.twig', array('url' => $url));
    }

    public function viewConfirmationAction(Request $request, $id_booking, $to_print = false, $no_user = false) {
        /** @var \MyCp\FrontEndBundle\Service\BookingService $bookingService */
        $bookingService = $this->get('front_end.services.booking');

        if ($to_print) {
            return $bookingService
                            ->getPrintableBookingConfirmationResponse($id_booking);
        }

        return $bookingService->getBookingConfirmationResponse($id_booking);
    }

    public function generatePdfVoucherAction($id_booking, $name = "voucher") {
        $pdfResponse = $this->forward(
                'FrontEndBundle:Reservation:viewConfirmation', array('id_booking' => $id_booking, 'to_print' => true)
        );

        $pdfService = $this->get('front_end.services.pdf');
        $pdfService->streamHtmlAsPdf($pdfResponse, $name);

        return $this->forward(
                        'FrontEndBundle:Reservation:viewConfirmation', array('id_booking' => $id_booking)
        );
    }

}
