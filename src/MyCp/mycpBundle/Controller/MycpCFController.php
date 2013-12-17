<?php

namespace MyCp\mycpBundle\Controller;

use DateTime;
use MyCp\mycpBundle\Entity\ownership;
use MyCp\mycpBundle\Entity\room;
use MyCp\mycpBundle\Entity\unavailabilityDetails;
use MyCp\mycpBundle\Helpers\SyncStatuses;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Description of MycpCF  - Mycp Communication Foundation
 *
 * @author Daniel
 */
class MycpCFController extends Controller {

    const SUCCESS_CONFIRM_MSG = "success";

//-----------------------------------------------------------------------------    
    public function mycpFrontControllerAction(Request $request) {
        $operation = $request->get('operation');
        switch ($operation) {
            case MyCPOperations::UPDATE_RESERVATIONS:
                $_data = $this->mycpUpdateReservations($request);
                break;
            case MyCPOperations::CONFIRM_RESERVATIONS_UPDATE:
                return new Response($this->mycpConfirmUpdateReservations($request));
            case MyCPOperations::COMMIT_RESERVATIONS:
                return new Response($this->_commitReservations($request));
            case MyCPOperations::UPDATE_HOUSES:
                $_data = $this->_updateHouses();
                break;
            case MyCPOperations::CONFIRM_HOUSES_UPDATE:
                return new Response($this->_confirmUpdateHouses());
            case MyCPOperations::COMMIT_HOUSES:
                return new Response($this->_commitHouses($request));
            case MyCPOperations::UPDATE_USERS:
                $_data = $this->_updateUsers();
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
    private function _commitReservations(Request $request) {
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

        return self::SUCCESS_CONFIRM_MSG;
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
    private function _updateHouses() {
        $em = $this->getDoctrine()->getEntityManager();

        //loading not synchronozed houses
        $houses_data = array();
        $not_sync_houses = $em->getRepository('mycpBundle:ownership')->getNotSynchronized();
        foreach ($not_sync_houses as $_house) {
            $houses_data[] = array(
                "co" => $_house->getOwnMcpCode(),
                "ss" => $_house->getSyncSt(),
                "na" => $_house->getOwnName(),
                "pr" => $_house->getOwnHomeowner1(),
                "ad" => $_house->getFullAddress(),
                "ph" => $_house->getOwnPhoneNumber(),
                "mp" => $_house->getOwnMinimumPrice(),
                "xp" => $_house->getOwnMaximumPrice(),
                "cp" => $_house->getOwnCommissionPercent());
        }

        //loading not synchronized rooms
        $rooms_data = array();
        $not_sync_rooms = $em->getRepository('mycpBundle:room')->getNotSynchronized();
        foreach ($not_sync_rooms as $_room) {
            $rooms_data[] = array(
                "hc" => $_room->getRoomOwnership()->getOwnMcpCode(),
                "nu" => $_room->getRoomId(),
                "ss" => $_room->getSyncSt(),
                "ty" => $_room->getRoomType(),
                "be" => $_room->getRoomBeds(),
                "uf" => $_room->getRoomPriceUpFrom(),
                "ut" => $_room->getRoomPriceUpTo(),
                "df" => $_room->getRoomPriceDownFrom(),
                "dt" => $_room->getRoomPriceDownTo(),
                "cl" => $_room->getRoomClimate(),
                "au" => $_room->getRoomAudiovisual(),
                "sm" => $_room->getRoomSmoker(),
                "sf" => $_room->getRoomSafe(),
                "ba" => $_room->getRoomBaby(),
                "bh" => $_room->getRoomBathroom(),
                "st" => $_room->getRoomStereo(),
                "wi" => $_room->getRoomWindows(),
                "by" => $_room->getRoomBalcony(),
                "te" => $_room->getRoomTerrace(),
                "ya" => $_room->getRoomYard()
            );
        }

        //loading not synchronized unavailabilities details
        $uds_data = array();
        $not_sync_uds = $em->getRepository('mycpBundle:unavailabilityDetails')->getNotSynchronized();
        foreach ($not_sync_uds as $ud) {
            $uds_data[] = array(
                "hc" => $ud->getRoom()->getRoomOwnership()->getOwnMcpCode(),
                "rn" => $ud->getRoom()->getRoomId(),
                "ss" => $ud->getSyncSt(),
                "fd" => $ud->getUdFromDate()->format('Y-m-d'),
                "td" => $ud->getUdToDate()->format('Y-m-d'),
                "rs" => $ud->getUdReason()
            );
        }

        $all_data = array("houses" => $houses_data, "rooms" => $rooms_data, "uds" => $uds_data);

//        echo "<pre>";
//        print_r($all_data);
//        echo "</pre>";
//        exit;

        return $all_data;
    }

//-----------------------------------------------------------------------------
    private function _confirmUpdateHouses() {
        $em = $this->getDoctrine()->getEntityManager();
        $all_houses_data = $this->_updateHouses();

        $houses = $all_houses_data["houses"];
        foreach ($houses as $house) {
            $e_house = $em->getRepository('mycpBundle:ownership')->findOneBy(array('own_mcp_code' => $house["co"]));
            $this->_normalizeBySyncStatus($em, $e_house, $house["ss"]);
        }

        $rooms = $all_houses_data["rooms"];
        foreach ($rooms as $room) {
            $e_house = $em->getRepository('mycpBundle:ownership')->findOneBy(array('own_mcp_code' => $room["hc"]));
            $e_room = $em->getRepository('mycpBundle:room')->findOneBy(array('room_ownership' => $e_house, 'room_id' => $room["nu"]));
            $this->_normalizeBySyncStatus($em, $e_room, $room["ss"]);
        }

        $uds = $all_houses_data["uds"];
        foreach ($uds as $ud) {
            $e_house = $em->getRepository('mycpBundle:ownership')->findOneBy(array('own_mcp_code' => $ud["hc"]));
            $e_room = $em->getRepository('mycpBundle:room')->findOneBy(array('room_ownership' => $e_house, 'room_id' => $ud["rn"]));
            $e_ud = $e_room->getUd($ud["fd"], $ud["td"]);
            $this->_normalizeBySyncStatus($em, $e_ud, $ud["ss"]);
        }
        $em->flush();
        return self::SUCCESS_CONFIRM_MSG;
    }

    private function _normalizeBySyncStatus($em, $entity, $status) {
        switch ($status) {
            case SyncStatuses::DELETED:
                $em->remove($entity);
                break;
            case SyncStatuses::ADDED:
            case SyncStatuses::UPDATED:
            default:
                $entity->setSyncSt(SyncStatuses::SYNC);
                $em->persist($entity);
                break;
        }
    }

//-----------------------------------------------------------------------------
    private function _commitHouses(Request $request) {
        $em = $this->getDoctrine()->getEntityManager();
        $json_houses = $request->get('content');
       // $json_houses = '{"uds":[{"rn":2,"rs":"Client Reservation","ss":0,"td":"2013-12-20","fd":"2013-12-17","hc":"CH138"}],"houses":[],"rooms":[{"nu":2,"dt":2,"wi":2,"sm":2,"ba":2,"uf":2,"ya":2,"te":2,"by":2,"sf":2,"ut":2,"hc":"CH138","au":2,"ty":2,"df":2,"cl":2,"bh":2,"ss":0,"st":2,"be":2}]}';
        $houses_mod_data = json_decode($json_houses);

//        $houses = $houses_mod_data->houses;
//        if (is_array($houses)) {
//            foreach ($houses as $house) {
//                $e_house = $em->getRepository('mycpBundle:ownership')->findOneBy(array('own_mcp_code' => $house->hc));
//                switch ($house->ss) {
//                    case SyncStatuses::ADDED:
//                        $e_house = new ownership();
//                    case SyncStatuses::UPDATED:
//                        $e_house->setOwnMcpCode($house->co);
//                        $e_house->setOwnName($house->na);
//                        $e_house->setOwnHomeowner1($house->pr);
//                        $e_house->setOwnAddressStreet($house->ad);
//                        $e_house->setOwnPhoneNumber($house->ph);
//                        $e_house->setOwnMinimumPrice($house->mp);
//                        $e_house->setOwnMaximumPrice($house->xp);
//                        $e_house->setOwnCommissionPercent($house->cp);
//                        $em->persist($e_house);
//                        break;
//                    case SyncStatuses::DELETED:
//                        $em->remove($e_house);
//                        break;
//                }
//            }
//        }
//        
//        $rooms = $houses_mod_data->rooms;
//        if (is_array($rooms)) {
//            foreach ($rooms as $room) {
//                $_house = $em->getRepository('mycpBundle:ownership')->findOneBy(array('own_mcp_code' => $room->hc));
//                $e_room = $_house->getRoom($room->nu);
//                switch ($room->ss) {
//                    case SyncStatuses::ADDED:
//                        $e_room = new room();
//                        $e_room->setRoomOwnership($_house);
//                    case SyncStatuses::UPDATED:
//                        $em->persist($e_room);
//                        break;
//                    case SyncStatuses::DELETED:
//                        $em->remove($e_room);
//                        break;
//                }
//            }
//        }

//        $uds = $houses_mod_data->uds;
//        if (is_array($uds)) {
//            foreach ($uds as $ud) {
//                $_house = $em->getRepository('mycpBundle:ownership')->findOneBy(array('own_mcp_code' => $ud->hc));
//                $_room = $_house->getRoom($ud->rn);                
//                $_new_ud = $_room->getUd($ud->fd, $ud->td);
//                switch ($ud->ss) {
//                    case SyncStatuses::ADDED:
//                        $_new_ud = new unavailabilityDetails();
//                        $_new_ud->setRoom($_room);
//                        $_room->getOwn_unavailability_details()->add($_new_ud);
//                    case SyncStatuses::UPDATED:
//                        $_new_ud->setUdFromDate(new DateTime($ud->fd));
//                        $_new_ud->setUdToDate(new DateTime($ud->td));
//                        $_new_ud->setUdReason($ud->rs);
//                        $em->persist($_new_ud);
//                        break;
//                    case SyncStatuses::DELETED:
//                        $em->remove($e_house);
//                        break;
//                }
//            }
//        }

        $em->flush();
        return self::SUCCESS_CONFIRM_MSG;
    }

    // </editor-fold>
//-----------------------------------------------------------------------------
    // <editor-fold defaultstate="collapsed" desc="Users Methods">
    private function _updateUsers() {
        $em = $this->getDoctrine()->getEntityManager();
        $users = $em->getRepository('mycpBundle:user')->findBy(array('user_role' => 'ROLE_CLIENT_STAFF'));

        $users_data = array();
        foreach ($users as $i => $user) {
            $users_data[$i]["fn"] = $user->getUserUserName() . " " . $user->getUserLastName();
            $users_data[$i]["un"] = $user->getUserName();
            $users_data[$i]["ps"] = $user->getUserPassword();
        }
        return $users_data;
    }

// </editor-fold>
}

//-----------------------------------------------------------------------------
class MyCPOperations {

    const UPDATE_RESERVATIONS = 1;
    const CONFIRM_RESERVATIONS_UPDATE = 2;
    const COMMIT_RESERVATIONS = 3;
    const UPDATE_HOUSES = 4;
    const CONFIRM_HOUSES_UPDATE = 5;
    const COMMIT_HOUSES = 6;
    const UPDATE_USERS = 7;

}

?>
