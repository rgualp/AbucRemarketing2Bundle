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
                $_data = $this->mycpUpdateReservations();
                break;
            case MyCPOperations::CONFIRM_RESERVATIONS_UPDATE:
                return new Response($this->mycpConfirmUpdateReservations());
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
    public function mycpUpdateReservations() {
        return array(
            "rs" => $this->_getReservations(),
            "rd" => $this->_getResRoomDetails(),
            "ce" => $this->_getCurrencyExchange());
    }

    private function mycpConfirmUpdateReservations() {
        $em = $this->getDoctrine()->getEntityManager();
        $reservations_all_data = $this->mycpUpdateReservations();

        $reservations = $reservations_all_data["rs"];
        foreach ($reservations as $_res) {
            $ent_res = $em->getRepository('mycpBundle:generalReservation')->find($_res["nu"]);
            switch ($_res["ss"]) {
                default:
                case SyncStatuses::ADDED:
                case SyncStatuses::UPDATED:
                    $ent_res->setGenResSyncSt(SyncStatuses::SYNC);
                    $em->persist($ent_res);
                    break;
                case SyncStatuses::DELETED:
                    $em->remove($ent_res);
                    break;
            }
        }

        $rooms_details = $reservations_all_data["rd"];
        foreach ($rooms_details as $_rd) {
            $ent_rd_res = $em->getRepository('mycpBundle:generalReservation')->find($_rd["ri"]);
            $ent_rd = $em->getRepository('mycpBundle:ownershipReservation')->findOneBy(array("own_res_gen_res_id" => $ent_rd_res, "own_res_selected_room_id" => $_rd["rn"]));
            switch ($_res["ss"]) {
                case SyncStatuses::ADDED:
                case SyncStatuses::UPDATED:
                    $ent_rd->setOwnResSyncSt(SyncStatuses::SYNC);
                    $em->persist($ent_rd);
                    break;
                case SyncStatuses::DELETED:
                    $em->remove($ent_rd);
                    break;
            }
        }

        $em->flush();
        return self::SUCCESS_CONFIRM_MSG;
    }

    // <editor-fold defaultstate="collapsed" desc="Aux Methods">
    private function _getReservations() {
        $em = $this->getDoctrine()->getEntityManager();
        $reservations = $em->getRepository('mycpBundle:generalReservation')->getNotSyncs();

        $_reserv_data = array();
        foreach ($reservations as $i => $reservation) {
            $_reserv_data[$i]["nu"] = $reservation->getGenResId();
            $_reserv_data[$i]["ss"] = $reservation->getGenResSyncSt();
            $_reserv_data[$i]["ad"] = $reservation->getGenResFromDate()->format('Y-m-d');
            $_reserv_data[$i]["rd"] = $reservation->getGenResDate()->format('Y-m-d');
            $_reserv_data[$i]["st"] = $reservation->getGenResStatus();
            $_reserv_data[$i]["rl"] = $em->getRepository('mycpBundle:generalReservation')->getResponseLanguaje($reservation);
            $_reserv_data[$i]["rc"] = $em->getRepository('mycpBundle:generalReservation')->getResponseCurrency($reservation);

            $_reserv_data[$i]["cl"] = $this->_getClient($reservation);
            $_reserv_data[$i]["ho"] = $this->_getHouse($reservation);
        }
        return $_reserv_data;
    }

    private function _getResRoomDetails() {
        $em = $this->getDoctrine()->getEntityManager();
        $room_details = $em->getRepository('mycpBundle:ownershipReservation')->getNotSyncs();

        $rooms_details_data = array();
        foreach ($room_details as $i => $_room_detail) {
            $rooms_details_data[$i]["ri"] = $_room_detail->getOwnResGenResId()->getGenResId();
            $rooms_details_data[$i]["rn"] = $_room_detail->getOwnResSelectedRoomId();
            $rooms_details_data[$i]["ss"] = $_room_detail->getOwnResSyncSt();
            $rooms_details_data[$i]["fd"] = $_room_detail->getOwnResReservationFromDate()->format('Y-m-d');
            $rooms_details_data[$i]["td"] = $_room_detail->getOwnResReservationToDate()->format('Y-m-d');
            $rooms_details_data[$i]["ac"] = $_room_detail->getOwnResCountAdults();
            $rooms_details_data[$i]["cc"] = $_room_detail->getOwnResCountChildrens();
            $rooms_details_data[$i]["np"] = $_room_detail->getOwnResNightPrice();
        }
        return $rooms_details_data;
    }

    private function _getClient($reservation) {
        $client["na"] = $reservation->getGenResUserId()->getUserUserName();
        $client["em"] = $reservation->getGenResUserId()->getUserEmail();
        $client["st"] = $reservation->getGenResUserId()->getUserAddress();
        $client["pc"] = $reservation->getGenResUserId()->getUserCountry()->getCoCode();
        $client["co"] = $reservation->getGenResUserId()->getUserCountry()->getCoName();
        return $client;
    }

    private function _getHouse($reservation) {
        $house["co"] = is_object($reservation->getGenResOwnId()) ? $reservation->getGenResOwnId()->getOwnMcpCode() : "-";
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
        //    $json_reservations = $request->get('content');
        $json_reservations = '{"re":[{"rn":27,"ss":1,"rt":"","st":"Sended"}],"rd":[{"nu":1,"rn":27,"np":30,"at":1,"ss":1,"td":"2014-02-28","fd":"2014-02-23","ct":0}]}';
        $res_data = json_decode($json_reservations);
        $em = $this->getDoctrine()->getEntityManager();

        $distinct_clients_data = array();
        $res_per_clients = array();
        $reservations = $res_data->re;
        if (is_array($reservations)) {
            foreach ($reservations as $reservation) {
                $entity_res = $em->getRepository('mycpBundle:generalReservation')->find($reservation->rn);
                if (!empty($entity_res)) {
                    $entity_res->setGenResStatus($this->_reserservationStatusConversor($reservation->st));
                    $em->persist($entity_res);

                    if (!in_array($entity_res->getGenResUserId(), $distinct_clients_data)) {
                        $distinct_clients_data[] = array("client" => $entity_res->getGenResUserId(), "rtext" => $reservation->rt);
                    }
                    $res_per_clients[$entity_res->getGenResUserId()->getUserId()][] = $entity_res;

                    //sending email...
                    $service_email = $this->get('Email');
                    $service_email->send_reservation($entity_res->getGenResId());
                }
            }
        }
        $res_rooms_details = $res_data->rd;
        if (is_array($res_rooms_details)) {
            foreach ($res_rooms_details as $res_room_detail) {
                $rrd = $em->getRepository('mycpBundle:ownershipReservation')->findOneBy(array('own_res_gen_res_id' => $res_room_detail->rn, 'own_res_selected_room_id' => $res_room_detail->nu));
                $room = $em->getRepository('mycpBundle:room')->findOneBy(array('room_num' => $rrd->getOwnResSelectedRoomId(), 'room_ownership' => $rrd->getOwnResGenResId()->getGenResOwnId()));
                $_ud = $em->getRepository('mycpBundle:unavailabilityDetails')->findOneBy(array('room' => $room, 'ud_from_date' => $rrd->getOwnResReservationFromDate(), 'ud_to_date' => $rrd->getOwnResReservationToDate()));
                if (!empty($rrd)) {
                    $rrd->setOwnResReservationFromDate(new DateTime($res_room_detail->fd));
                    $rrd->setOwnResReservationToDate(new DateTime($res_room_detail->td));
                    $rrd->setOwnResCountAdults($res_room_detail->at);
                    $rrd->setOwnResCountChildrens($res_room_detail->ct);
                    $rrd->setOwnResNightPrice($res_room_detail->np);
                    $em->persist($rrd);

                    //manage UDs for each reservation...
                    if (!empty($_ud)) {
                        switch ($res_room_detail->ss) {
                            case SyncStatuses::ADDED:
                                $_ud = new unavailabilityDetails();
                            case SyncStatuses::UPDATED:
                                $_ud->setUdFromDate(new DateTime($res_room_detail->fd));
                                $_ud->setUdToDate(new DateTime($res_room_detail->td));
                                $_ud->setUdReason("Client reservation");
                                $_ud->setRoom($room);
                                $_ud->setSyncSt(SyncStatuses::SYNC);
                                $em->persist($_ud);
                                break;
                            case SyncStatuses::DELETED:
                                $em->remove($_ud);
                                break;
                        }
                    }
                }
            }
        }
        $em->flush();
        return self::SUCCESS_CONFIRM_MSG;
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
                "ad" => $_house->getOwnAddressStreet(),
                "pv" => $_house->getOwnAddressProvince() != null ? $_house->getOwnAddressProvince()->getProvCode() : "HAB",
                "ph" => $_house->getOwnPhoneNumber(),
                "mp" => $_house->getOwnMinimumPrice(),
                "xp" => $_house->getOwnMaximumPrice(),
                "cp" => $_house->getOwnCommissionPercent(),
                "em" => $_house->getOwnEmail1(),
                "ca" => $_house->getOwnCategory(),
                "ty" => $_house->getOwnType());
        }

        //loading not synchronized rooms
        $rooms_data = array();
        $not_sync_rooms = $em->getRepository('mycpBundle:room')->getNotSynchronized();
        foreach ($not_sync_rooms as $_room) {
            $rooms_data[] = array(
                "hc" => $_room->getRoomOwnership()->getOwnMcpCode(),
                "nu" => $_room->getRoomNum(),
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
            $e_room = $em->getRepository('mycpBundle:room')->findOneBy(array('room_ownership' => $e_house, 'room_num' => $room["nu"]));

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
//        $json_houses = $request->get('content');
        $json_houses = ' {"uds":[{"rn":1,"rs":"Client Reservation","ss":0,"td":"2014-03-10","fd":"2014-03-05","hc":"CH001"}],"houses":[{"ty":"Penthouse","mp":45,"ca":"Premium","cp":20,"na":"Casa Particular en Guanabo La Casa de Elena","ad":"Calle 472","xp":30,"ss":1,"em":"reservacion@mycasaparticular.com","ph":"243 5643","hc":"CH138","pr":"Elena Moriño Castellano"}],"rooms":[]}';
        $houses_mod_data = json_decode($json_houses);

        $houses = $houses_mod_data->houses;
        if (is_array($houses)) {
            foreach ($houses as $house) {
                $e_house = $em->getRepository('mycpBundle:ownership')->findOneBy(array('own_mcp_code' => $house->hc));
                switch ($house->ss) {
                    case SyncStatuses::ADDED:
                        $e_house = new ownership();
                    case SyncStatuses::UPDATED:
                        $e_house->setOwnMcpCode($house->hc);
                        $e_house->setOwnName($house->na);
                        $e_house->setOwnHomeowner1($house->pr);
                        $e_house->setOwnAddressStreet($house->ad);
                        $e_house->setOwnPhoneNumber($house->ph);
                        $e_house->setOwnMinimumPrice($house->mp);
                        $e_house->setOwnMaximumPrice($house->xp);
                        $e_house->setOwnCommissionPercent($house->cp);
                        $e_house->setOwnEmail1($house->em);
                        $e_house->setOwnCategory($house->ca);
                        $e_house->setOwnType($house->ty);
                        $em->persist($e_house);
                        break;
                    case SyncStatuses::DELETED:
                        $em->remove($e_house);
                        break;
                }
            }
        }
        $em->flush();

        $rooms = $houses_mod_data->rooms;
        if (is_array($rooms)) {
            foreach ($rooms as $room) {
                $_house = $em->getRepository('mycpBundle:ownership')->findOneBy(array('own_mcp_code' => $room->hc));
                $e_room = $_house->getRoom($room->nu);
                switch ($room->ss) {
                    case SyncStatuses::ADDED:
                        $e_room = new room();
                        $e_room->setRoomOwnership($_house);
                        $_house->addOwnRoom($e_room);
                    case SyncStatuses::UPDATED:
                        $e_room->setRoomNum($room->nu);
                        $e_room->setRoomType($room->ty);
                        $e_room->setRoomBeds($room->be);
                        $e_room->setRoomPriceUpFrom($room->uf);
                        $e_room->setRoomPriceUpTo($room->ut);
                        $e_room->setRoomPriceDownFrom($room->df);
                        $e_room->setRoomPriceDownTo($room->dt);
                        $e_room->setRoomClimate($room->cl);
                        $e_room->setRoomAudiovisual($room->au);
                        $e_room->setRoomSmoker($room->sm);
                        $e_room->setRoomSafe($room->sf);
                        $e_room->setRoomBaby($room->ba);
                        $e_room->setRoomBathroom($room->bh);
                        $e_room->setRoomStereo($room->st);
                        $e_room->setRoomWindows($room->wi);
                        $e_room->setRoomBalcony($room->by);
                        $e_room->setRoomTerrace($room->te);
                        $e_room->setRoomYard($room->ya);
                        $em->persist($e_room);
                        break;
                    case SyncStatuses::DELETED:
                        $em->remove($e_room);
                        break;
                }
            }
        }
        $em->flush();

        $uds = $houses_mod_data->uds;
        if (is_array($uds)) {
            foreach ($uds as $ud) {
                $_house = $em->getRepository('mycpBundle:ownership')->findOneBy(array('own_mcp_code' => $ud->hc));
                $_room = $_house->getRoom($ud->rn);
                
                $_new_ud = $_room->getUd($ud->fd, $ud->td);
                switch ($ud->ss) {
                    case SyncStatuses::ADDED:
                        $_new_ud = new unavailabilityDetails();
                        $_new_ud->setRoom($_room);
                        $_room->getOwn_unavailability_details()->add($_new_ud);
                    case SyncStatuses::UPDATED:
                        $_new_ud->setUdFromDate(new DateTime($ud->fd));
                        $_new_ud->setUdToDate(new DateTime($ud->td));
                        $_new_ud->setUdReason($ud->rs);
                        $em->persist($_new_ud);
                        break;
                    case SyncStatuses::DELETED:
                        $em->remove($e_house);
                        break;
                }
            }
        }

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
