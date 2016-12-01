<?php

namespace MyCp\FrontEndBundle\Controller;

use MyCp\mycpBundle\Entity\mycpService;
use MyCp\mycpBundle\Helpers\FileIO;
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
use Symfony\Component\HttpFoundation\BinaryFileResponse;

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
    public function reservationAfterLoginAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $user_ids = $em->getRepository('mycpBundle:user')->getIds($this);
        $cartItems = $em->getRepository('mycpBundle:cart')->getCartItemsAfterLogin($user_ids);
        $ownerShip=$em->getRepository('mycpBundle:generalReservation')->getOwnShipReserByUser($user_ids);
        $insert=1;
        //Validar que no se haga una reserva que ya fuese realizada
        foreach ($ownerShip as $item){
            $ownDateFrom = $item->getOwnResReservationFromDate()->getTimestamp();
            $ownDateTo = $item->getOwnResReservationToDate()->getTimestamp();


            foreach ($cartItems as $cart) {
                $cartDateFrom = $cart->getCartDateFrom()->getTimestamp();
                $cartDateTo = $cart->getCartDateTo()->getTimestamp();
                if((($ownDateFrom <= $cartDateFrom && $ownDateTo >= $cartDateFrom) ||
                        ($ownDateFrom <= $cartDateTo && $ownDateTo >= $cartDateTo))
                    && $item->getOwnResSelectedRoomId()==$cart->getCartRoom()->getRoomId())
                    $insert=0;
            }
        }
        if($insert==1){  //sino hay un error
            $arrayIdCart=array();
            foreach ($cartItems as $cart){
                $arrayIdCart[]=$cart->getCartId();
            }
            $own_ids=array();
            //Es que el usuario mando a hacer una reserva
            $own_ids=$this->checkDispo($arrayIdCart,$request,true);
            $request->getSession()->set('reservation_own_ids', $own_ids);
            return $this->redirect($this->generateUrl('frontend_reservation_reservation'));
        }
        else{
            $message = $this->get('translator')->trans("ADD_TO_CEST_ERROR");
            $this->get('session')->getFlashBag()->add('message_global_error', $message);
            return $this->redirect($this->generateUrl('frontend_mycasatrip_available'));
        }
    }
    /**
     * @param $id_car
     * @param $request
     * @return bool|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function checkDispo($arrayIdCart,$request,$inmediatily_booking){
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $reservations = array();
        $own_ids = array();
        $array_photos = array();
        $user_ids = $em->getRepository('mycpBundle:user')->getIds($this);
        foreach($arrayIdCart as $temp){
            $cartItem = $em->getRepository('mycpBundle:cart')->find($temp);
            $cartItems[]=$cartItem;
        }

        $min_date = null;
        $max_date = null;
        $generalReservations = array();

        if (count($cartItems) > 0) {
            $res_array = array();
            $own_visited = array();
            foreach ($cartItems as $item) {

                if ($min_date == null)
                    $min_date = $item->getCartDateFrom();
                else if ($item->getCartDateFrom() < $min_date)
                    $min_date = $item->getCartDateFrom();

                if ($max_date == null)
                    $max_date = $item->getCartDateTo();
                else if ($item->getCartDateTo() > $max_date)
                    $max_date = $item->getCartDateTo();

                $res_own_id = $item->getCartRoom()->getRoomOwnership()->getOwnId();

                $array_group_by_own_id = array();
                $flag = 1;
                foreach ($own_visited as $own) {
                    if ($own == $res_own_id) {
                        $flag = 0;
                    }
                }
                if ($flag == 1)
                    foreach ($cartItems as $item) {
                        if ($res_own_id == $item->getCartRoom()->getRoomOwnership()->getOwnId()) {
                            array_push($array_group_by_own_id, $item);
                        }
                    }
                array_push($res_array, $array_group_by_own_id);
                array_push($own_visited, $res_own_id);
            }
            $service_time = $this->get('Time');
            $nigths = array();
            foreach ($res_array as $resByOwn) {
                if (isset($resByOwn[0])) {
                    $ownership = $em->getRepository('mycpBundle:ownership')->find($resByOwn[0]->getCartRoom()->getRoomOwnership()->getOwnId());

                    $serviceFee = $em->getRepository("mycpBundle:serviceFee")->getCurrent();
                    $general_reservation = new generalReservation();
                    $general_reservation->setGenResUserId($user);
                    $general_reservation->setGenResDate(new \DateTime(date('Y-m-d')));
                    $general_reservation->setGenResStatusDate(new \DateTime(date('Y-m-d')));
                    $general_reservation->setGenResHour(date('G'));
                    if($inmediatily_booking)
                        $general_reservation->setGenResStatus(generalReservation::STATUS_AVAILABLE);
                    else
                        $general_reservation->setGenResStatus(generalReservation::STATUS_PENDING);
                    $general_reservation->setGenResFromDate($min_date);
                    $general_reservation->setGenResToDate($max_date);
                    $general_reservation->setGenResSaved(0);
                    $general_reservation->setGenResOwnId($ownership);
                    $general_reservation->setGenResDateHour(new \DateTime(date('H:i:s')));
                    $general_reservation->setServiceFee($serviceFee);


                    $total_price = 0;
                    $partial_total_price = array();
                    $destination_id = ($ownership->getOwnDestination() != null) ? $ownership->getOwnDestination()->getDesId() : null;
                    foreach ($resByOwn as $item) {
                        $triple_room_recharge = ($item->getTripleRoomCharged()) ? $this->container->getParameter('configuration.triple.room.charge') : 0;
                        $array_dates = $service_time->datesBetween($item->getCartDateFrom()->getTimestamp(), $item->getCartDateTo()->getTimestamp());
                        $temp_price = 0;
                        $seasons = $em->getRepository("mycpBundle:season")->getSeasons($item->getCartDateFrom()->getTimestamp(), $item->getCartDateTo()->getTimestamp(), $destination_id);

                        for ($a = 0; $a < count($array_dates) - 1; $a++) {
                            $seasonType = $service_time->seasonTypeByDate($seasons, $array_dates[$a]);
                            $roomPrice = $item->getCartRoom()->getPriceBySeasonType($seasonType);
                            $total_price += $roomPrice + $triple_room_recharge;
                            $temp_price += $roomPrice + $triple_room_recharge;
                        }
                        array_push($partial_total_price, $temp_price);
                    }
                    $general_reservation->setGenResTotalInSite($total_price);
                    $em->persist($general_reservation);

                    $arrayKidsAge = array();

                    $flag_1 = 0;
                    foreach ($resByOwn as $item) {
                        $ownership_reservation = $item->createReservation($general_reservation, $partial_total_price[$flag_1]);
                        if($inmediatily_booking)
                            $ownership_reservation->setOwnResStatus(ownershipReservation::STATUS_AVAILABLE);


                        array_push($reservations, $ownership_reservation);

                        $ownership_photo = $em->getRepository('mycpBundle:ownership')->getOwnershipPhoto($ownership_reservation->getOwnResGenResId()->getGenResOwnId()->getOwnId());
                        array_push($array_photos, $ownership_photo);

                        $nightsCount = $service_time->nights($ownership_reservation->getOwnResReservationFromDate()->getTimestamp(), $ownership_reservation->getOwnResReservationToDate()->getTimestamp());
                        array_push($nigths, $nightsCount);

                        if($item->getChildrenAges() != null)
                        {
                            $arrayKidsAge[$item->getCartRoom()->getRoomNum()] = $item->getChildrenAges();
                        }

                        $em->persist($ownership_reservation);
                        $em->flush();
                        array_push($own_ids, $ownership_reservation->getOwnResId());
                        $flag_1++;
                    }
                    $general_reservation->setChildrenAges($arrayKidsAge);
                    $em->flush();

                    //update generalReservation dates
                    $em->getRepository("mycpBundle:generalReservation")->updateDates($general_reservation);
                    array_push($generalReservations, $general_reservation->getGenResId());

                    if($general_reservation->getGenResOwnId()->getOwnInmediateBooking()){
                        $smsService = $this->get("mycp.notification.service");
                        $smsService->sendInmediateBookingSMSNotification($general_reservation);
                    }

                }
            }
        } else {
            return false;
        }
        $locale = $this->get('translator')->getLocale();
        // Enviando mail al cliente
        if(!$inmediatily_booking){
            $body = $this->render('FrontEndBundle:mails:email_check_available.html.twig', array(
                    'user' => $user,
                    'reservations' => $reservations,
                    'ids' => $own_ids,
                    'nigths' => $nigths,
                    'photos' => $array_photos,
                    'user_locale' => $locale
                ));

            if($user != null) {
                $locale = $this->get('translator');
                $subject = $locale->trans('REQUEST_SENT');
                $service_email = $this->get('Email');
                $service_email->sendEmail(
                    $subject, 'reservation@mycasaparticular.com', 'MyCasaParticular.com', $user->getUserEmail(), $body
                );
            }
        }

        if(!$inmediatily_booking){
            //Enviando mail al reservation team
            foreach($generalReservations as $genResId){
                //Enviando correo a solicitud@mycasaparticular.com
                \MyCp\FrontEndBundle\Helpers\ReservationHelper::sendingEmailToReservationTeam($genResId, $em, $this, $service_email, $service_time, $request, 'solicitud@mycasaparticular.com', 'no-reply@mycasaparticular.com');
            }
        }
        foreach($arrayIdCart as $temp){
            $cartItem = $em->getRepository('mycpBundle:cart')->find($temp);
            $em->remove($cartItem);
        }
        $em->flush();
        if(!$inmediatily_booking) //esta consultando la disponibilidad
            return true;
        else                      //esta haciendo una reserva
            return $own_ids;
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
        $currentServiceFee = $em->getRepository("mycpBundle:serviceFee")->getCurrent();
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
        $generalReservationIds = array();
        $triple_room_recharge = $this->container->getParameter('configuration.triple.room.charge');
        foreach ($reservations as $reservation) {
            $generalReservationIds[] = $reservation->getOwnResGenResId()->getGenResId();
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

        $generalReservationIds = array_unique($generalReservationIds);
        $touristTax = 0;

        foreach($generalReservationIds as $genResId){
            $tax = $em->getRepository("mycpBundle:serviceFee")->calculateTouristServiceFeeByGeneralReservation($genResId, $service_time);
            $touristTax += $tax;

            /*var_dump($tax);*/
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
                $configuration_service_fee = floatval($currentServiceFee->getFixedFee());
                //$totalNights = count($array_dates_string) - 1;
                //$touristTax = $em->getRepository("mycpBundle:serviceFee")->calculateTouristServiceFee(count($reservations), $totalNights, $total_price / $totalNights);
                $prepayment = ($touristTax  + $configuration_service_fee + $total_percent_price)* $currency->getCurrCucChange();
                $booking->setBookingPrepay($prepayment);
                $booking->setBookingUserId($user->getUserId());
                $booking->setBookingUserDates($user->getUserUserName() . ', ' . $user->getUserEmail());
                $em->persist($booking);

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

                $paymentMethod = $post['payment_method'];

                switch($paymentMethod){
                    case "skrill": return $this->forward('FrontEndBundle:Payment:skrillPayment', array('bookingId' => $bookingId));
                    case "postfinance": return $this->forward('FrontEndBundle:Payment:postFinancePayment', array('bookingId' => $bookingId, 'method' => "POSTFINANCE"));
//                    case "visa": return $this->forward('FrontEndBundle:Payment:postFinancePayment', array('bookingId' => $bookingId, 'method' => "VISA"));
//                    case "mastercard": return $this->forward('FrontEndBundle:Payment:postFinancePayment', array('bookingId' => $bookingId, 'method' => "MASTERCARD"));
                     default: return $this->forward('FrontEndBundle:Payment:skrillPayment', array('bookingId' => $bookingId));
                }


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
                    'seasons' => $season_types,
                    'currentServiceFee' => $currentServiceFee,
                    'touristTax' => $touristTax
        ));
    }

    public function confirmationAction($id_booking) {
        $em = $this->getDoctrine()->getManager();
        $payment = $em->getRepository('mycpBundle:payment')->findOneBy(array('booking' => $id_booking));

        if (empty($payment)) {
            throw $this->createNotFoundException();
        }

        if ($payment->getStatus() === PaymentHelper::STATUS_PROCESSED)
            $payment->generateEcomerceTracking($this->getDoctrine()->getManager(), $this);

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
        $voucherPDFPath = $bookingService->getVoucherFilePathByBookingId($id_booking);

        if(file_exists($voucherPDFPath))
            return new BinaryFileResponse($voucherPDFPath);
        else{
            $message = $this->get('translator')->trans("PAYMENT_NOT_EXISTS");
            $this->get('session')->getFlashBag()->add('message_global_error', $message);

            $this->redirect($this->generateUrl("frontend_mycasatrip_payment"));
        }

        /*if ($to_print) {
            return $bookingService
                            ->getPrintableBookingConfirmationResponse($id_booking);
        }

        return $bookingService->getBookingConfirmationResponse($id_booking);*/
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


    public function testPaymentEmailsAction(Request $request, $idbooking)
    {
        $em = $this->getDoctrine()->getManager();
        $payment = $em->getRepository('mycpBundle:payment')->findOneBy(array('booking' => $idbooking));

        $booking = $payment->getBooking();
        $bookingId = $booking->getBookingId();
        $user = $user = $em->getRepository('mycpBundle:user')->find($booking->getBookingUserId());
        $userId = $user->getUserId();
        $userTourist = $em->getRepository('mycpBundle:userTourist')->findOneBy(array('user_tourist_user' => $userId));
        $ownershipReservations = $em
            ->getRepository('mycpBundle:ownershipReservation')
            ->findBy(array('own_res_reservation_booking' => $bookingId), array("own_res_gen_res_id" => "ASC"));

        $rooms = array();

        foreach($ownershipReservations as $reservation)
        {
            $room = $em->getRepository('mycpBundle:room')->find($reservation->getOwnResSelectedRoomId());

            $rooms[$reservation->getOwnResId()] = $room;
        }

        $arrayPhotos = array();
        $arrayNights = array();
        $arrayNightsByOwnershipReservation = array();
        $arrayHouses = array();
        $arrayHousesIds = array();
        $arrayOwnershipReservationByHouse = array();
        $timeService = $this->get('time');

        $cont = 0;

        foreach ($ownershipReservations as $own) {
            $rootOwn = $own->getOwnResGenResId()->getGenResOwnId();
            $rootOwnId = $rootOwn->getOwnId();

            $photos = $em
                ->getRepository('mycpBundle:ownership')
                ->getPhotos($rootOwnId);

            array_push($arrayPhotos, $photos);
            $array_dates = $timeService->datesBetween(
                $own->getOwnResReservationFromDate()->getTimestamp(),
                $own->getOwnResReservationToDate()->getTimestamp()
            );
            array_push($arrayNights, count($array_dates) - 1);
            $arrayNightsByOwnershipReservation[$own->getOwnResId()] = count($array_dates) - 1;

            $insert = true;
            foreach ($arrayHousesIds as $item) {
                if ($rootOwnId == $item) {
                    $insert = false;
                }
            }

            if ($insert) {
                array_push($arrayHousesIds, $rootOwnId);
                array_push($arrayHouses, $rootOwn);
            }

            if (isset($arrayOwnershipReservationByHouse[$rootOwnId])) {
                $temp_array = $arrayOwnershipReservationByHouse[$rootOwnId];
            } else {
                $temp_array = array();
            }

            array_push($temp_array, $own);
            $arrayOwnershipReservationByHouse[$rootOwnId] = $temp_array;
            $cont++;
        }

//        $pdfFilePath = $this->createBookingVoucherIfNotExisting($bookingId);

        // Send email to customer
        $emailService = $this->get('Email');

        $userLocale = strtolower($userTourist->getUserTouristLanguage()->getLangCode());
//        $body = $this->render('FrontEndBundle:mails:boletin.html.twig', array(
//            'bookId' => $bookingId,
//            'user' => $user,
//            'reservations' => $ownershipReservations,
//            'photos' => $arrayPhotos,
//            'nights' => $arrayNights,
//            'user_locale' => $userLocale,
//            'user_currency' => ($userTourist != null) ? $userTourist->getUserTouristCurrency() : null,
//            'reservationStatus' => (count($ownershipReservations) > 0) ? $ownershipReservations[0]->getOwnResGenResId()->getGenResStatus() : generalReservation::STATUS_NONE
//        ));

        $locale = $this->get('translator');
        $subject = $locale->trans('PAYMENT_CONFIRMATION', array(), "messages", $userLocale);

        $logger = $this->get('logger');
        $userEmail = trim($user->getUserEmail());

//        try {
//            $emailService->sendEmail(
//                $subject,
//                'send@mycasaparticular.com',
//                $subject . ' - MyCasaParticular.com',
//                $userEmail,
//                $body,
//                $pdfFilePath
//            );
//
//            /*$emailService->sendEmail(
//                 $subject,
//                 'send@mycasaparticular.com',
//                 $subject . ' - MyCasaParticular.com',
//                 "luiseduardo@hds.li",
//                 $body,
//                 $pdfFilePath
//             );*/
//
//            $logger->info('Successfully sent email to user ' . $userEmail . ', PDF path : ' .
//                (isset($pdfFilePath) ? $pdfFilePath : '<empty>'));
//        } catch (\Exception $e) {
//            $logger->error(sprintf(
//                'EMAIL: Could not send Email to User. Booking ID: %s, Email: %s',
//                $bookingId, $userEmail));
//            $logger->error($e->getMessage());
//        }

//        // send email to reservation team
//        foreach ($arrayOwnershipReservationByHouse as $owns) {
//            $bodyRes = $this->render(
//                'FrontEndBundle:mails:rt_payment_confirmation.html.twig',
//                array(
//                    'user' => $user,
//                    'user_tourist' => array($userTourist),
//                    'reservations' => $owns,
//                    'nights' => $arrayNightsByOwnershipReservation,
//                    'payment_pending' => $paymentPending,
//                    'rooms' => $rooms,
//                    'booking' => $bookingId,
//                    'payedAmount' => $booking->getPayedAmount()
//                )
//            );
//
//            try {
//                /* $emailService->sendEmail(
//                     'Confirmación de pago',
//                     'no-reply@mycasaparticular.com',
//                     'MyCasaParticular.com',
//                     'reservation@mycasaparticular.com',
//                     $bodyRes
//                 );*/
//
//                $emailService->sendEmail(
//                    'Confirmación de pago',
//                    'no-reply@mycasaparticular.com',
//                    'MyCasaParticular.com',
//                    'confirmacion@mycasaparticular.com',
//                    $bodyRes
//                );
//
//                $logger->info('Successfully sent email to reservation team. Booking ID: ' . $bookingId);
//            } catch (\Exception $e) {
//                $logger->error('EMAIL: Could not send Email to reservation team. Booking ID: ' . $bookingId);
//                $logger->error($e->getMessage());
//            }
//        }

        //dump($arrayOwnershipReservationByHouse);die;

        // send email to accommodation owner
        foreach ($arrayOwnershipReservationByHouse as $key => $owns) {

            $fromToTravel = $em->getRepository('mycpBundle:ownershipReservation')->getFromToDestinationCliente($key,$user->getUserId(), date_format($owns[0]->getOwnResReservationFromDate(), 'Y-m-d'), date_format($owns[0]->getOwnResReservationToDate(), 'Y-m-d'));
            $houseFrom = null;
            if (array_key_exists('from', $fromToTravel)){
                $houseFrom = $em->getRepository('mycpBundle:ownership')->findOneBy(array('own_id' => $fromToTravel['from']));
            }
            $houseTo = null;
            if (array_key_exists('to', $fromToTravel)){
                $houseTo = $em->getRepository('mycpBundle:ownership')->findOneBy(array('own_id' => $fromToTravel['to']));
            }

            return $this->render(
                'FrontEndBundle:mails:email_house_confirmation.html.twig',
                array(
                    'user' => $user,
                    'user_tourist' => array($userTourist),
                    'reservations' => $owns,
                    'nights' => $arrayNightsByOwnershipReservation,
                    'rooms' => $rooms,
                    'booking' => $bookingId,
                    'houseFrom' => $houseFrom,
                    'houseTo' => $houseTo
                )
            );
        }
    }

}
