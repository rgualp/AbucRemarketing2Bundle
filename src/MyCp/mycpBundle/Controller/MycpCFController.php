<?php

namespace MyCp\mycpBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Description of MycpCF  - Mycp Communication Foundation
 *
 * @author Daniel
 */
class MycpCFController extends Controller {

    // <editor-fold defaultstate="collapsed" desc="Reservations Updating Methods">
    public function mycpOfflineReservationsUpdAction(Request $request) {
        $em = $this->getDoctrine()->getEntityManager();
        $init_date = $request->get('init_date');
        $end_date = $request->get('end_date');

        $reserv_in_dates = $em->getRepository('mycpBundle:generalReservation')->getBetweenDates($init_date, $end_date);

        $to_send_data = array(
            "reservations" => $this->_getReservationsFullData($reserv_in_dates),
            "currency_exchange" => $this->_getCurrencyExchange());

        //  return new Response(gzencode(json_encode($to_send_data)));
        return new Response(json_encode($to_send_data));
    }

    // <editor-fold defaultstate="collapsed" desc="Aux Methods">
    private function _getReservationsFullData($reservations) {
        $_reserv_data = array();
        foreach ($reservations as $i => $reservation) {
            $_reserv_data[$i]["num"] = $reservation->getGenResId();
            $_reserv_data[$i]["arrival_date"] = $reservation->getGenResFromDate()->format('Y-m-d');
            $_reserv_data[$i]["reservation_date"] = $reservation->getGenResDate()->format('Y-m-d');
            $_reserv_data[$i]["status"] = $reservation->getGenResStatus();

            $_reserv_data[$i]["res_rooms_details"] = $this->_getResRoomDetails($reservation);
            $_reserv_data[$i]["client"] = $this->_getClient($reservation);
            $_reserv_data[$i]["house"] = $this->_getHouse($reservation);
        }
        return $_reserv_data;
    }

    private function _getResRoomDetails($reservation) {
        $rooms_details = array();
        foreach ($reservation->getOwn_reservations() as $i => $_room_details) {
            $rooms_details[$i]["room_num"] = $_room_details->getOwnResSelectedRoomId();
            $rooms_details[$i]["from_date"] = $_room_details->getOwnResReservationFromDate()->format('Y-m-d');
            $rooms_details[$i]["to_date"] = $_room_details->getOwnResReservationToDate()->format('Y-m-d');
            $rooms_details[$i]["adults_count"] = $_room_details->getOwnResCountAdults();
            $rooms_details[$i]["children_count"] = $_room_details->getOwnResCountChildrens();
            $rooms_details[$i]["night_price"] = $_room_details->getOwnResNightPrice();
        }
        return $rooms_details;
    }

    private function _getClient($reservation) {
        $client["name"] = $reservation->getGenResUserId()->getUserUserName();
        $client["email"] = $reservation->getGenResUserId()->getUserEmail();
        $client["street"] = $reservation->getGenResUserId()->getUserAddress();
        $client["pcode"] = $reservation->getGenResUserId()->getUserCountry()->getCoCode();
        $client["country"] = $reservation->getGenResUserId()->getUserCountry()->getCoName();
        return $client;
    }

    private function _getHouse($reservation) {
        $house["code"] = $reservation->getGenResOwnId()->getOwnMcpCode();
        $house["name"] = $reservation->getGenResOwnId()->getOwnName();
        $house["proprietary"] = $reservation->getGenResOwnId()->getPropietariesStringList();
        $house["address"] = $reservation->getGenResOwnId()->getFullAddress();
        $house["phone"] = $reservation->getGenResOwnId()->getOwnPhoneNumber();
        $house["min_price"] = $reservation->getGenResOwnId()->getOwnMinimumPrice();
        $house["max_price"] = $reservation->getGenResOwnId()->getOwnMaximumPrice();
        $house["com_percent"] = $reservation->getGenResOwnId()->getOwnCommissionPercent();

        return $house;
    }

    private function _getCurrencyExchange() {
        $em = $this->getDoctrine()->getEntityManager();
        $currencies = $em->getRepository('mycpBundle:currency')->findAll();
        foreach ($currencies as $currency) {
            $currency_data[strtoupper($currency->getCurrCode())] = $currency->getCurrCucChange();
        }
        if (!array_key_exists("CHF", $currency_data)) {
            $currency_data["CHF"] = 1;
        }
        return $currency_data;
    }

// </editor-fold>
// </editor-fold>
//-----------------------------------------------------------------------------
    // <editor-fold defaultstate="collapsed" desc="Reservations Commiting Methods">
    public function mycpOfflineReservationsCommitAction(Request $request) {
        $json_reservations = $request->get('content');
        $reservations = json_decode($json_reservations);
        if (is_array($reservations)) {
            $em = $this->getDoctrine()->getEntityManager();
            foreach ($reservations as $reservation) {
                $entity_res = $em->getRepository('mycpBundle:generalReservation')->find($reservation->id);
                if (!empty($entity_res)) {
                    $entity_res->setGenResStatus($this->_reserservationStatusConversor($reservation->status));
                    $entity_res->getGenResOwnId()->setOwnCommissionPercent($reservation->house_percent);
                    $em->persist($entity_res);
                }
            }
            $em->flush();
        }

        //sending email...
        //$this->_sendReservationsEmail($user, $reservations);

        return new Response("done!");
    }

    private function _sendReservationsEmail($em, $user, $reservations) {
        $array_photos = array();
        $array_nigths = array();
        $service_time = $this->get('time');

        foreach ($reservations as $res) {
            $photos = $em->getRepository('mycpBundle:ownership')->getPhotos($res->getOwnResGenResId()->getGenResOwnId()->getOwnId());
            array_push($array_photos, $photos);
            $array_dates = $service_time->dates_between($res->getOwnResReservationFromDate()->getTimestamp(), $res->getOwnResReservationToDate()->getTimestamp());
            array_push($array_nigths, count($array_dates));
        }
        // Enviando mail al cliente
        $body = $this->render('frontEndBundle:mails:email_offer_available.html.twig', array(
            'user' => $user,
            'reservations' => $reservations,
            'photos' => $array_photos,
            'nights' => $array_nigths
        ));

        $locale = $this->get('translator');
        $subject = $locale->trans('REQUEST_STATUS_CHANGED');
        $service_email = $this->get('Email');
        $service_email->send_email(
                $subject, 'reservation@mycasaparticular.com', 'MyCasaParticular.com', $user->getUserEmail(), $body
        );
    }

    private function _reserservationStatusConversor($status) {
        switch ($status) {
            case "Pending":return 0;
            case "Sended":return 1;
            case "Not_avaliable":return 2;
            case "Paid":return 3;
        }
    }

// </editor-fold>
//-----------------------------------------------------------------------------
    // <editor-fold defaultstate="collapsed" desc="Users Update Methods">
    /**
     * Legend: fn: Full Name, un: User Name, ps: Password... reducing data transmition
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function mycpOfflineUsersUpdAction() {
        $em = $this->getDoctrine()->getEntityManager();
        $users = $em->getRepository('mycpBundle:user')->findBy(array('user_role' => 'ROLE_CLIENT_STAFF'));

        $users_data = array();
        foreach ($users as $i => $user) {
            $users_data[$i]["fn"] = $user->getUserUserName() . " " . $user->getUserLastName();
            $users_data[$i]["un"] = $user->getUserName();
            $users_data[$i]["ps"] = $user->getUserPassword();
        }
        return new Response(json_encode($users_data));
    }

// </editor-fold>
}

?>
