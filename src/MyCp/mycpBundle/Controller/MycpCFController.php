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
    /**
     * MYCP - CF Operations
     */

    const UPDATE_RESERVATIONS = 1;
    const CONFIRM_RESERVATIONS_UPDATE = 2;
    const COMMIT_RESERVATIONS = 3;
    const UPDATE_HOUSES = 4;
    const CONFIRM_HOUSES_UPDATE = 5;
    const COMMIT_HOUSES = 6;
    const UPDATE_USERS = 7;
//-----------------------------------------------------------------------------
    const SUCCESS_CONFIRM_MSG = "success";

//-----------------------------------------------------------------------------    
    public function mycpFrontControllerAction(Request $request) {
        $operation = $request->get('operation');
        switch ($operation) {
            case self::UPDATE_RESERVATIONS:
                $_data = $this->mycpUpdateReservations($request);
                break;
            case self::CONFIRM_RESERVATIONS_UPDATE:
                return new Response($this->mycpConfirmUpdateReservations($request));
            case self::COMMIT_RESERVATIONS:
                $_data = $this->mycpUploadReservations($request);
                break;
            case self::UPDATE_HOUSES:
                $_data = $this->mycpDownloadHouses();
                break;
            case self::CONFIRM_HOUSES_UPDATE: 
                return new Response($this->_mycpConfirmDownloadHouses());
            case self::COMMIT_HOUSES:
                $_data = $this->mycpUploadReservations($request);
                break;
            case self::UPDATE_USERS:
                $_data = $this->mycpDownloadUsers();
                break;
        }
        return new Response(json_encode($_data));
    }

//-----------------------------------------------------------------------------
    // <editor-fold defaultstate="collapsed" desc="Reservations Methods">
    // <editor-fold defaultstate="collapsed" desc="Download Methods">
    public function mycpUpdateReservations(Request $request) {
        $em = $this->getDoctrine()->getEntityManager();
        $init_date = $request->get('init_date');
        $end_date = $request->get('end_date');

        $reserv_in_dates = $em->getRepository('mycpBundle:generalReservation')->getValidBetweenDates($init_date, $end_date);

        $to_send_data = array(
            "reservations" => $this->_getReservationsFullData($reserv_in_dates),
            "currency_exchange" => $this->_getCurrencyExchange());

        return $to_send_data;
    }

    private function mycpConfirmUpdateReservations(Request $request) {
        $em = $this->getDoctrine()->getEntityManager();
        $init_date = $request->get('init_date');
        $end_date = $request->get('end_date');

        $em->getRepository('mycpBundle:generalReservation')->setSyncBetweenDates($init_date, $end_date);
        return self::SUCCESS_CONFIRM_MSG;
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
            $rooms_details[$i]["rn"] = $_room_details->getOwnResSelectedRoomId();
            $rooms_details[$i]["fd"] = $_room_details->getOwnResReservationFromDate()->format('Y-m-d');
            $rooms_details[$i]["td"] = $_room_details->getOwnResReservationToDate()->format('Y-m-d');
            $rooms_details[$i]["ac"] = $_room_details->getOwnResCountAdults();
            $rooms_details[$i]["cc"] = $_room_details->getOwnResCountChildrens();
            $rooms_details[$i]["np"] = $_room_details->getOwnResNightPrice();
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
    // <editor-fold defaultstate="collapsed" desc="Upload Methods">
    private function mycpUploadReservations(Request $request) {
        $json_reservations = $request->get('content');
        $reservations = json_decode($json_reservations);
        $em = $this->getDoctrine()->getEntityManager();

        $distinct_clients = array();
        $res_per_clients = array();

        if (is_array($reservations)) {
            foreach ($reservations as $reservation) {
                $entity_res = $em->getRepository('mycpBundle:generalReservation')->find($reservation->id);
                if (!empty($entity_res)) {
                    $entity_res->setGenResStatus($this->_reserservationStatusConversor($reservation->status));
                    $entity_res->getGenResOwnId()->setOwnCommissionPercent($reservation->house_percent);
                    $em->persist($entity_res);

                    if (!in_array($entity_res->getGenResUserId(), $distinct_clients)) {
                        $distinct_clients[] = $entity_res->getGenResUserId();
                    }
                    $res_per_clients[$entity_res->getGenResUserId()->getUserId()][] = $entity_res;
                }
            }
            $em->flush();
        }

        //sending email...
        foreach ($distinct_clients as $client) {
            $this->_sendReservationsEmail($em, $client, $res_per_clients[$client->getUserId()]);
        }

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
// </editor-fold>
//-----------------------------------------------------------------------------
    // <editor-fold defaultstate="collapsed" desc="Houses - UnAvailabilities Methods">
    public function mycpDownloadHouses() {
        $em = $this->getDoctrine()->getEntityManager();
        $houses_to_send = $em->getRepository('mycpBundle:ownership')->getHousesToOfflineApp();

        $_houses_data = array();
        foreach ($houses_to_send as $i => $house_to_send) {
            $_houses_data[$i]["co"] = $house_to_send->getOwnMcpCode();
            $_houses_data[$i]["na"] = $house_to_send->getOwnName();
            $_houses_data[$i]["pr"] = $house_to_send->getOwnHomeowner1();
            $_houses_data[$i]["ad"] = $house_to_send->getFullAddress();
            $_houses_data[$i]["ph"] = $house_to_send->getOwnPhoneNumber();
            $_houses_data[$i]["mp"] = $house_to_send->getOwnMinimumPrice();
            $_houses_data[$i]["xp"] = $house_to_send->getOwnMaximumPrice();
            $_houses_data[$i]["cp"] = $house_to_send->getOwnCommissionPercent();
            $_houses_data[$i]["rt"] = $house_to_send->getOwnRoomsTotal();

            //loading unavailabilities details
            $_udetails = array();
            foreach ($house_to_send->getCurrentUDs() as $j => $_ud) {
                $_udetails[$j]["rn"] = $_ud->getRoomNum();
                $_udetails[$j]["fd"] = $_ud->getUdFromDate()->format('Y-m-d H:m:s');
                $_udetails[$j]["td"] = $_ud->getUdToDate()->format('Y-m-d H:m:s');
                $_udetails[$j]["rs"] = $_ud->getUdReason();
            }
            $_houses_data[$i]["_ud"] = $_udetails;
        }
        return $_houses_data;
    }
    
     public function _mycpConfirmDownloadHouses() {
        $em = $this->getDoctrine()->getEntityManager();
        $em->getRepository('mycpBundle:ownership')->setHousesSync();
        return self::SUCCESS_CONFIRM_MSG;
    }

    public function mycpOfflineHousesCommitAction(Request $request) {
        $json_reservations = $request->get('content');
        $reservations = json_decode($json_reservations);
        $em = $this->getDoctrine()->getEntityManager();
    }

    // </editor-fold>
//-----------------------------------------------------------------------------
    // <editor-fold defaultstate="collapsed" desc="Users Methods">
    public function mycpDownloadUsers() {
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
