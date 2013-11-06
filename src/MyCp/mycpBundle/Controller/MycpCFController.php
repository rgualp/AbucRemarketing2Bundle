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

        return new Response(json_encode($to_send_data));
    }

    // <editor-fold defaultstate="collapsed" desc="Aux Methods">
    private function _getReservationsFullData($reservations) {
        $_reserv_data = array();
        foreach ($reservations as $i => $reservation) {
            $_reserv_data[$i]["num"] = $reservation->getGenResId();
            $_reserv_data[$i]["arrival_date"] = $reservation->getGenResFromDate();
            $_reserv_data[$i]["reservation_date"] = $reservation->getGenResDate();
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
            $rooms_details[$i]["from_date"] = $_room_details->getOwnResReservationFromDate();
            $rooms_details[$i]["to_date"] = $_room_details->getOwnResReservationToDate();
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

        return $house;
    }

    private function _getCurrencyExchange() {
        $em = $this->getDoctrine()->getEntityManager();
        $currencies = $em->getRepository('mycpBundle:currency')->findAll();
        foreach ($currencies as $currency) {
            $currency_data[strtoupper($currency->getCurrCode())] = $currency->getCurrCucChange();
        }
        if(!array_key_exists("CHF", $currency_data)){
            $currency_data["CHF"] = 1;
        }        
        return $currency_data;
    }

// </editor-fold>
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
//-----------------------------------------------------------------------------        
    // <editor-fold defaultstate="collapsed" desc="MyCP Offline App Reservations Commiting Methods">
    public function mycpOfflineReservationsCommitAction(Request $request) {
        
    }

// </editor-fold>
}

?>
