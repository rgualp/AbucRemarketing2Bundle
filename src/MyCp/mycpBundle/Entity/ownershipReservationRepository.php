<?php

namespace MyCp\mycpBundle\Entity;

use DateTime;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\ResultSetMapping;
use MyCp\mycpBundle\Helpers\SyncStatuses;

/**
 * ownershipReservationRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class ownershipReservationRepository extends EntityRepository {

    function getAll($filter_date_reserve, $filter_offer_number, $filter_reference, $filter_date_from, $filter_date_to, $sort_by) {
        $filter_offer_number = strtolower($filter_offer_number);
        $filter_offer_number = str_replace('cas.', '', $filter_offer_number);
        $filter_offer_number = str_replace('cas', '', $filter_offer_number);
        $filter_offer_number = str_replace('.', '', $filter_offer_number);
        $array_date_reserve = explode('/', $filter_date_reserve);
        $array_date_from = explode('/', $filter_date_from);
        $array_date_to = explode('/', $filter_date_to);
        if (count($array_date_reserve) > 1)
            $filter_date_reserve = $array_date_reserve[2] . '-' . $array_date_reserve[1] . '-' . $array_date_reserve[0];
        if (count($array_date_from) > 1)
            $filter_date_from = $array_date_from[2] . '-' . $array_date_from[1] . '-' . $array_date_from[0];
        if (count($array_date_to) > 1)
            $filter_date_to = $array_date_to[2] . '-' . $array_date_to[1] . '-' . $array_date_to[0];

        $string_order = '';
        switch ($sort_by) {
            case 1:
                $string_order = "ORDER BY ore.own_res_reservation_date ASC";
                break;
            case 2:
                $string_order = "ORDER BY ore.own_res_own_id ASC";
                break;
            case 3:
                $string_order = "ORDER BY ore.own_res_reservation_from_date ASC";
                break;
            case 4:
                $string_order = "ORDER BY ore.own_res_reservation_status ASC";
                break;
        }
        $em = $this->getEntityManager();
        $query = $em->createQuery("SELECT ore,gre,ow FROM mycpBundle:ownershipReservation ore JOIN ore.own_res_gen_res_id gre
        JOIN ore.own_res_own_id ow
        WHERE ore.own_res_reservation_date LIKE :filter_date_reserve AND ore.own_res_id LIKE :filter_offer_number
        AND ow.own_mcp_code LIKE :filter_reference AND ore.own_res_reservation_from_date LIKE :filter_date_from
        AND ore.own_res_reservation_to_date LIKE :filter_date_to $string_order");

        $query->setParameters(array(
            'filter_date_reserve' => "%" . $filter_date_reserve . "%",
            'filter_offer_number' => "%" . $filter_offer_number . "%",
            'filter_reference' => "%" . $filter_reference . "%",
            'filter_date_from' => "%" . $filter_date_from . "%",
            'filter_date_to' => "%" . $filter_date_to . "%",
        ));
        return $query->getArrayResult();
    }

    function getReservationsByIdUser($id_user) {
        $em = $this->getEntityManager();
        $query = $em->createQuery("SELECT ore,gre FROM mycpBundle:ownershipReservation ore JOIN ore.own_res_gen_res_id gre
        WHERE gre.gen_res_user_id = :id_user");
        return $query->setParameter('id_user', $id_user)->getArrayResult();
    }

    function getOwnResByOwnership($id_ownership) {
        $em = $this->getEntityManager();
        $query = $em->createQuery("SELECT ore,gre FROM mycpBundle:ownershipReservation ore JOIN ore.own_res_gen_res_id gre
        WHERE gre.gen_res_own_id = :id_ownership");
        return $query->setParameter('id_ownership', $id_ownership)->getArrayResult();
    }

    function getReservationReservedByOwnership($id_ownership) {
        $em = $this->getEntityManager();
        $reservedCode = ownershipReservation::STATUS_RESERVED;
        $query = $em->createQuery("SELECT ore FROM mycpBundle:ownershipReservation ore JOIN ore.own_res_gen_res_id gre join mycpBundle:room ro with ore.own_res_selected_room_id = ro.room_id
        WHERE gre.gen_res_own_id = :id_ownership and ore.own_res_status = $reservedCode");
        return $query->setParameter('id_ownership', $id_ownership)->getResult();
    }

    function getReservationReserved($startParam, $endParam) {
        $em = $this->getEntityManager();
        $reservedCode = ownershipReservation::STATUS_RESERVED;
        $cancelledCode = ownershipReservation::STATUS_CANCELLED;
        $query = $em->createQuery("SELECT ore.own_res_status,coun.co_name, user.user_name, user.user_last_name, ro.room_num, ore, gre.gen_res_id, gre.gen_res_from_date, gre.gen_res_to_date
            FROM mycpBundle:ownershipReservation ore
            JOIN mycpBundle:room ro with ore.own_res_selected_room_id = ro.room_id
            JOIN ore.own_res_gen_res_id gre
            JOIN gre.gen_res_user_id user
            JOIN gre.gen_res_own_id own
            JOIN user.user_country coun
        WHERE (ore.own_res_status = $reservedCode OR ore.own_res_status = $cancelledCode)
        AND ((ore.own_res_reservation_from_date >= '$startParam' AND ore.own_res_reservation_to_date <= '$endParam')
         OR (ore.own_res_reservation_to_date >= '$startParam' AND ore.own_res_reservation_to_date <= '$endParam') OR
    (ore.own_res_reservation_from_date <= '$endParam' AND ore.own_res_reservation_from_date >= '$startParam'))");
        return $query->setParameter('start', $startParam)->setParameter('end', $endParam)->getResult();
    }

    function getReservationReservedByOwnershipAndDate($ownership, $startParam, $endParam) {
        $em = $this->getEntityManager();
        $reservedCode = ownershipReservation::STATUS_RESERVED;
        $cancelledCode = ownershipReservation::STATUS_CANCELLED;
        $query = $em->createQuery("SELECT ore.own_res_status,coun.co_name, user.user_name, user.user_last_name, ro.room_num, ore, gre.gen_res_id, ore.own_res_reservation_from_date, ore.own_res_reservation_to_date
            FROM mycpBundle:ownershipReservation ore
            JOIN mycpBundle:room ro with ore.own_res_selected_room_id = ro.room_id
            JOIN ore.own_res_gen_res_id gre
            JOIN gre.gen_res_user_id user
            JOIN gre.gen_res_own_id own
            JOIN user.user_country coun
        WHERE (ore.own_res_status = $reservedCode OR ore.own_res_status = $cancelledCode)
        AND ((ore.own_res_reservation_from_date >= '$startParam' AND ore.own_res_reservation_to_date <= '$endParam')
         OR (ore.own_res_reservation_to_date >= '$startParam' AND ore.own_res_reservation_to_date <= '$endParam') OR
    (ore.own_res_reservation_from_date <= '$endParam' AND ore.own_res_reservation_from_date >= '$startParam'))
        AND own.own_id = :own_id");
        return $query->setParameter('own_id', $ownership)->getResult();
    }

    function getReservationReservedByRoomAndDate($idRoom, $startParam, $endParam, $justReservations = false) {
        $em = $this->getEntityManager();
        $reservedCode = ownershipReservation::STATUS_RESERVED;
        $cancelledCode = ownershipReservation::STATUS_CANCELLED;
        $statusWhere = ($justReservations) ? "ore.own_res_status = $reservedCode" : "(ore.own_res_status = $reservedCode OR ore.own_res_status = $cancelledCode)";
        $query = $em->createQuery("SELECT ore.own_res_status,ore.own_res_id,ore.own_res_reservation_from_date, ore.own_res_reservation_to_date
            FROM mycpBundle:ownershipReservation ore
        WHERE $statusWhere
        AND ((ore.own_res_reservation_from_date >= '$startParam' AND ore.own_res_reservation_to_date <= '$endParam')
         OR (ore.own_res_reservation_to_date >= '$startParam' AND ore.own_res_reservation_to_date <= '$endParam') OR
    (ore.own_res_reservation_from_date <= '$endParam' AND ore.own_res_reservation_from_date >= '$startParam'))
        AND ore.own_res_selected_room_id = $idRoom
        ORDER BY ore.own_res_reservation_from_date");

        //dump($query->getDQL());exit;
        return $query->getResult();
    }

    function getByBookingAndOwnership($id_booking, $own_id) {
        $em = $this->getEntityManager();
        $query = $em->createQuery("SELECT ore FROM mycpBundle:ownershipReservation ore JOIN ore.own_res_gen_res_id gre
        WHERE ore.own_res_reservation_booking = :id_booking and gre.gen_res_own_id = :id_own");
        return $query->setParameter('id_booking', $id_booking)->setParameter('id_own', $own_id)->getResult();
    }

    function getByBooking($id_booking) {
        $em = $this->getEntityManager();
        $query = $em->createQuery("SELECT DISTINCT
            o.own_id as id,
            o.own_name as name,
            o.own_mcp_code as mycp_code,
            o.own_homeowner_1 as owner_1,
            o.own_homeowner_2 as owner_2,
            o.own_address_street as main_street,
            o.own_address_number as number,
            o.own_address_between_street_1 as street_1,
            o.own_address_between_street_2 as street_2,
            mun.mun_name as municipality,
            prov.prov_name as province,
            o.own_phone_number as phone_number,
            prov.prov_phone_code as prov_code,
            o.own_geolocate_y as geo_y,
            o.own_geolocate_x as geo_x,
            o.own_commission_percent as commission_percent,
            serviceFee.id as service_fee,
            serviceFee.fixedFee,
            serviceFee.current as currentFee,
            o.own_email_1,
            o.own_email_2
            FROM mycpBundle:ownershipReservation ore JOIN ore.own_res_gen_res_id gre
            JOIN gre.gen_res_own_id o
            JOIN o.own_address_municipality as mun
            JOIN o.own_address_province as prov
            JOIN gre.service_fee serviceFee
        WHERE ore.own_res_reservation_booking = :id_booking");
        return $query->setParameter('id_booking', $id_booking)->getArrayResult();
    }

    function getRoomsByAccomodation($id_booking, $own_id) {
        $em = $this->getEntityManager();
        $query = $em->createQuery("SELECT
            ore.own_res_id,
            ore.own_res_room_type,
            ore.own_res_reservation_from_date,
            ore.own_res_reservation_to_date,
            ore.own_res_count_adults,
            ore.own_res_count_childrens,
            (select min(r.room_bathroom) from mycpBundle:room r where r.room_id = ore.own_res_selected_room_id) as room_bathroom,
            ore.own_res_total_in_site as priceInSite,
            ore.own_res_night_price as priceNight
            FROM mycpBundle:ownershipReservation ore JOIN ore.own_res_gen_res_id gre
        WHERE ore.own_res_reservation_booking = :id_booking and gre.gen_res_own_id = :id_own");
        return $query->setParameter('id_booking', $id_booking)->setParameter('id_own', $own_id)->getArrayResult();
    }

    function getById($id_reservation) {
        $em = $this->getEntityManager();
        $query = $em->createQuery("SELECT ore,gre,us, ow,owm,owp,uc FROM mycpBundle:ownershipReservation ore
        JOIN ore.own_res_gen_res_id gre JOIN gre.gen_res_user_id us JOIN gre.gen_res_own_id ow
        JOIN ow.own_address_municipality owm JOIN ow.own_address_province owp
        JOIN us.user_country uc
        WHERE ore.own_res_gen_res_id= :id_reservation");
        return $query->setParameter('id_reservation', $id_reservation)->getArrayResult();
    }

    function getByIdObj($id_reservation) {
        $em = $this->getEntityManager();
        $query = $em->createQuery("SELECT ore,gre,us, ow,owm,owp,uc FROM mycpBundle:ownershipReservation ore
        JOIN ore.own_res_gen_res_id gre JOIN gre.gen_res_user_id us JOIN gre.gen_res_own_id ow
        JOIN ow.own_address_municipality owm JOIN ow.own_address_province owp
        JOIN us.user_country uc
        WHERE ore.own_res_gen_res_id= :id_reservation");
        return $query->setParameter('id_reservation', $id_reservation)->getResult();
    }

    function edit($reservation, $data) {
        $em = $this->getEntityManager();
        $reservation = $em->getRepository('mycpBundle:ownershipReservation')->find($reservation['own_res_id']);
        $ownership = $em->getRepository('mycpBundle:ownership')->find($data['ownership']);
        $reservation->setOwnResOwnId($ownership);
        $reservation->setOwnResSelectedRoom($data['selected_room']);
        $reservation->setOwnResCountAdults($data['count_adults']);
        $reservation->setOwnResCountChildrens($data['count_childrens']);
        $array_date_from = explode('/', $data['reservation_from_date']);
        $array_date_to = explode('/', $data['reservation_to_date']);
        $date_from = $array_date_from[2] . '-' . $array_date_from[1] . '-' . $array_date_from[0];
        $date_to = $array_date_to[2] . '-' . $array_date_to[1] . '-' . $array_date_to[0];
        $reservation->setOwnResReservationFromDate(new DateTime($date_from));
        $reservation->setOwnResReservationToDate(new DateTime($date_to));
        $reservation->setOwnResNightPrice($data['night_price']);
        $reservation->setOwnResCommissionPercent($data['commission_percent']);
        $em->persist($reservation);
        $em->flush();
    }

    function insert($data) {
        $em = $this->getEntityManager();
        $ownership = $em->getRepository('mycpBundle:ownership')->findBy(array('own_id' => $data['reservation_ownership']));
        $user = $em->getRepository('mycpBundle:user')->findBy(array('user_id' => $data['user']));
        $general_reservation = new generalReservation();
        $general_reservation->setGenResUserId($user[0]);
        $em->persist($general_reservation);
        $reservation = new ownershipReservation();
        $reservation->setOwnResOwnId($ownership[0]);
        $reservation->setOwnResGenResId($general_reservation);
        $reservation->setOwnResCommissionPercent($data['percent']);
        $reservation->setOwnResCountAdults($data['count_adults']);
        $reservation->setOwnResCountChildrens($data['count_childrens']);
        $reservation->setOwnResSelectedRoom($data['selected_room']);
        $reservation->setOwnResNightPrice($data['night_price']);

        $array_date_from = explode('/', $data['reservation_from_date']);
        $date_from = $array_date_from[2] . '-' . $array_date_from[1] . '-' . $array_date_from[0];
        $array_date_to = explode('/', $data['reservation_to_date']);
        $date_to = $array_date_to[2] . '-' . $array_date_to[1] . '-' . $array_date_to[0];

        $reservation->setOwnResReservationDate(new DateTime(date('Y-m-d')));
        $reservation->setOwnResReservationFromDate(new DateTime($date_from));
        $reservation->setOwnResReservationToDate(new DateTime($date_to));
        $reservation->setOwnResReservationStatus(ownershipReservation::STATUS_PENDING);
        $reservation->setOwnResReservationStatusDate(new DateTime(date('Y-m-d')));
        $em->persist($reservation);
        $em->flush();
    }

    function getAvailableByUser($id_reservation, $id_user) {
        $em = $this->getEntityManager();
        $query = $em->createQuery("SELECT owre,genres,own,mun FROM mycpBundle:ownershipReservation owre
        JOIN owre.own_res_gen_res_id genres JOIN owre.own_res_own_id own JOIN own.own_address_municipality mun
        WHERE owre.own_res_id = :id_reservation AND genres.gen_res_user_id = :id_user");
        return $query->setParameters(array(
                            "id_reservation" => $id_reservation,
                            "id_user" => $id_user
                        ))
                        ->getArrayResult();
    }

    function findByUserAndStatus($id_user, $status_string, $string_sql) {
        $em = $this->getEntityManager();
        $query = $em->createQuery("SELECT us,ownre,
        ow,mun,prov,gre,booking FROM mycpBundle:ownershipReservation ownre JOIN ownre.own_res_gen_res_id gre
        JOIN gre.gen_res_own_id ow
        JOIN gre.gen_res_user_id us
        JOIN ow.own_address_municipality mun
        LEFT JOIN ownre.own_res_reservation_booking booking
        JOIN ow.own_address_province prov
        WHERE $status_string AND us.user_id=$id_user $string_sql");

        return $query->getArrayResult();
    }

    function countForMenu($id_user) {
        $date = \date('Y-m-j');
        $new_date = strtotime('-30 day', strtotime($date));
        $new_date = \date('Y-m-j', $new_date);

        $date_days = \date('Y-m-j');
        $date_days = strtotime('-60 hours', strtotime($date_days));
        $date_days = \date('Y-m-j', $date_days);

        $em = $this->getEntityManager();
        $query = $em->createQuery("SELECT count(ore_pend) as pending,
        (SELECT count(ore_avail) FROM mycpBundle:ownershipReservation ore_avail JOIN ore_avail.own_res_gen_res_id gen_res WHERE gen_res.gen_res_user_id = $id_user AND ore_avail.own_res_status=" . ownershipReservation::STATUS_AVAILABLE . " AND gen_res.gen_res_status_date > '$date_days')  as available,
        (SELECT count(ore_res) FROM mycpBundle:ownershipReservation ore_res JOIN ore_res.own_res_gen_res_id gen_res_r WHERE gen_res_r.gen_res_user_id = $id_user AND ore_res.own_res_status=" . ownershipReservation::STATUS_RESERVED . " AND gen_res_r.gen_res_date > '$new_date')  as reserve,
        (SELECT count(ore_cons) FROM mycpBundle:ownershipReservation ore_cons JOIN ore_cons.own_res_gen_res_id gen_res_cons WHERE gen_res_cons.gen_res_user_id = $id_user AND ore_cons.own_res_status <>" . ownershipReservation::STATUS_RESERVED . " AND gen_res_cons.gen_res_date < '$new_date')  as consult,
        (SELECT count(owre_payed) FROM mycpBundle:ownershipReservation owre_payed JOIN owre_payed.own_res_gen_res_id gen_res_p WHERE gen_res_p.gen_res_user_id = $id_user AND owre_payed.own_res_status=" . ownershipReservation::STATUS_RESERVED . ")  as payed,
        (SELECT count(ore_res_hist) FROM mycpBundle:ownershipReservation ore_res_hist JOIN ore_res_hist.own_res_gen_res_id gen_res_h  WHERE gen_res_h.gen_res_user_id = $id_user AND ore_res_hist.own_res_status=" . ownershipReservation::STATUS_RESERVED . " AND gen_res_h.gen_res_date < '$new_date')  as reserve_history,
        (SELECT count(fav) FROM mycpBundle:favorite fav WHERE fav.favorite_user = $id_user AND fav.favorite_ownership IS NOT NULL)  as favorites_ownerships,
        (SELECT count(fav_des) FROM mycpBundle:favorite fav_des WHERE fav_des.favorite_user = $id_user AND fav_des.favorite_destination IS NOT NULL)  as favorites_destinations,
        (SELECT count(com) FROM mycpBundle:comment com WHERE com.com_user = $id_user AND com.com_ownership IS NOT NULL AND com.com_public =1) as comments_ownerships
        FROM mycpBundle:ownershipReservation ore_pend JOIN ore_pend.own_res_gen_res_id gre_pend WHERE gre_pend.gen_res_user_id = $id_user AND ore_pend.own_res_status=0 AND gre_pend.gen_res_date > '$new_date'");
        return $query->getArrayResult();
    }

    function getMainMenu($id_user) {
        $date_days = \date('Y-m-j');
        $date_days = strtotime('-60 hours', strtotime($date_days));
        $date_days = \date('Y-m-j', $date_days);

        $em = $this->getEntityManager();
        /*$query = $em->createQuery("SELECT count(ore_avail) as available
                                   FROM mycpBundle:ownershipReservation ore_avail
                                   JOIN ore_avail.own_res_gen_res_id gen_res
                                   WHERE gen_res.gen_res_user_id = $id_user
                                     AND ore_avail.own_res_status=" . ownershipReservation::STATUS_AVAILABLE . "
                                     AND gen_res.gen_res_date > '$date_days'");*/
        $query=$em->createQuery("SELECT count(ow) as available FROM mycpBundle:ownershipReservation ownre
        JOIN ownre.own_res_gen_res_id gre
        JOIN gre.gen_res_own_id ow
        JOIN gre.gen_res_user_id us
        JOIN ow.own_address_municipality mun
        LEFT JOIN ownre.own_res_reservation_booking booking
        JOIN ow.own_address_province prov
        WHERE ownre.own_res_status=" . ownershipReservation::STATUS_AVAILABLE . "
        AND us.user_id=$id_user
        AND gre.gen_res_date > '$date_days'");
        return $query->getScalarResult();
    }

    function getByUserAndStatus($id_user, $status) {
        $em = $this->getEntityManager();
        $query = $em->createQuery("SELECT ownre FROM mycpBundle:ownershipReservation ownre JOIN ownre.own_res_gen_res_id genres
        WHERE genres.gen_res_user_id= :id_user AND ownre.own_res_status= :status");
        return $query->setParameters(array(
                    "id_user" => $id_user,
                    "status" => $status
                ))->getResult();
    }

    function getNotSyncs() {
        $em = $this->getEntityManager();
        $query = $em->createQuery("SELECT ownre FROM mycpBundle:ownershipReservation ownre JOIN ownre.own_res_gen_res_id genres
            WHERE ownre.own_res_sync_st <> " . SyncStatuses::SYNC);
        return $query->getResult();
    }

    function getByOwnershipAndUser($status, $own_id, $user_id) {
        $em = $this->getEntityManager();
        $query_string = "SELECT own_r FROM mycpBundle:ownershipReservation own_r JOIN own_r.own_res_gen_res_id gen_res
                             WHERE gen_res.gen_res_own_id = :own_id" .
                " AND gen_res.gen_res_user_id = :user_id" .
                " AND own_r.own_res_status = :status";
        $results = $em->createQuery($query_string)
                      ->setParameters(array(
                          'own_id' => $own_id,
                          'user_id' => $user_id,
                          'status' => $status))
                      ->getResult();

        return $results;
    }

    function getReservationReservedByGeneralAndDate($genRes, $startParam, $endParam) {
        $em = $this->getEntityManager();
        $reservedCode = ownershipReservation::STATUS_RESERVED;
        $query = $em->createQuery("SELECT ore
            FROM mycpBundle:ownershipReservation ore
            JOIN ore.own_res_gen_res_id gre
        WHERE (ore.own_res_status = $reservedCode) AND (ore.own_res_reservation_from_date >= :start OR ore.own_res_reservation_to_date <= :end) AND gre.gen_res_id = :gen_res_id");
        return $query->setParameter('start', $startParam)->setParameter('end', $endParam)->setParameter('gen_res_id', $genRes)->getResult();
    }

    function getReservationReservedByRoomAndDateForCalendar($roomId, $startParam, $endParam) {
        $em = $this->getEntityManager();
        $reservedCode = ownershipReservation::STATUS_RESERVED;
        $query = $em->createQuery("SELECT ore
            FROM mycpBundle:ownershipReservation ore
        WHERE (ore.own_res_status = $reservedCode)
        AND ((ore.own_res_reservation_from_date >= :start AND ore.own_res_reservation_from_date <= :end) OR
             (ore.own_res_reservation_to_date >= :start AND ore.own_res_reservation_to_date <= :end) OR
             (ore.own_res_reservation_from_date <= :start AND ore.own_res_reservation_to_date >= :end))
        AND  ore.own_res_reservation_from_date <> :end
        AND  ore.own_res_reservation_to_date <> :start
        AND ore.own_res_selected_room_id = :room_id
        ORDER BY ore.own_res_reservation_from_date ASC");
        return $query->setParameter('start', $startParam)->setParameter('end', $endParam)->setParameter('room_id', $roomId)->getResult();
    }

    function getOwnReservationsForNightsCounterTotal()
    {
        $em = $this->getEntityManager();
        $query_string = "SELECT count(own_r) FROM mycpBundle:ownershipReservation own_r";
        return $em->createQuery($query_string)
            ->getSingleScalarResult();
    }

    function getOwnReservationsByPagesForNightsCounter($startIndex, $pageSize)
    {
        $em = $this->getEntityManager();
        $query_string = "SELECT own_r FROM mycpBundle:ownershipReservation own_r";
        return $em->createQuery($query_string)
            ->setFirstResult($startIndex)->setMaxResults($pageSize)->getResult();
    }

    function getReservationReservedByRoom($roomId,$startParam, $endParam) {
        $em = $this->getEntityManager();
        $reservedCode = ownershipReservation::STATUS_RESERVED;
        $cancelledCode = ownershipReservation::STATUS_CANCELLED;
        $query = $em->createQuery("SELECT ore.own_res_id, ore.own_res_status,coun.co_name, user.user_name, user.user_last_name, ro.room_num, ore, gre.gen_res_id, gre.gen_res_from_date, gre.gen_res_to_date, ore.own_res_reservation_from_date, ore.own_res_reservation_to_date
            FROM mycpBundle:ownershipReservation ore
            JOIN mycpBundle:room ro with ore.own_res_selected_room_id = ro.room_id
            JOIN ore.own_res_gen_res_id gre
            JOIN gre.gen_res_user_id user
            JOIN gre.gen_res_own_id own
            JOIN user.user_country coun
        WHERE (ore.own_res_status = $reservedCode) AND ore.own_res_selected_room_id = :roomId AND gre.gen_res_from_date >= :start AND gre.gen_res_to_date <= :end");
        return $query->setParameter('start', $startParam)->setParameter('end', $endParam)->setParameter('roomId', $roomId)->getResult();
    }
    function getReservationReservedByRoomCasaModule($roomId,$startParam, $endParam) {
        $em = $this->getEntityManager();
        $reservedCode = ownershipReservation::STATUS_RESERVED;
        $cancelledCode = ownershipReservation::STATUS_CANCELLED;
        $query = $em->createQuery("SELECT ore.own_res_id, ore.own_res_status,coun.co_name, user.user_name, user.user_last_name, ro.room_num, ore, gre.gen_res_id, gre.gen_res_from_date, gre.gen_res_to_date, ore.own_res_reservation_from_date, ore.own_res_reservation_to_date
            FROM mycpBundle:ownershipReservation ore
            JOIN mycpBundle:room ro with ore.own_res_selected_room_id = ro.room_id
            JOIN ore.own_res_gen_res_id gre
            JOIN gre.gen_res_user_id user
            JOIN gre.gen_res_own_id own
            JOIN user.user_country coun
        WHERE (ore.own_res_status = $reservedCode) AND ore.own_res_selected_room_id = :roomId AND((gre.gen_res_from_date>=:start AND gre.gen_res_from_date < :end) OR (gre.gen_res_to_date>=:start AND gre.gen_res_to_date < :end) OR (gre.gen_res_from_date<=:start AND gre.gen_res_to_date > :end) )");
        return $query->setParameter('start', $startParam)->setParameter('end', $endParam)->setParameter('roomId', $roomId)->getResult();
    }
    function getReservationCancelledByRoom($roomId,$startParam, $endParam) {
        $em = $this->getEntityManager();
        $cancelledCode = ownershipReservation::STATUS_CANCELLED;
        $query = $em->createQuery("SELECT ore.own_res_id, ore.own_res_status,coun.co_name, user.user_name, user.user_last_name, ro.room_num, ore, gre.gen_res_id, gre.gen_res_from_date, gre.gen_res_to_date, ore.own_res_reservation_from_date, ore.own_res_reservation_to_date
            FROM mycpBundle:ownershipReservation ore
            JOIN mycpBundle:room ro with ore.own_res_selected_room_id = ro.room_id
            JOIN ore.own_res_gen_res_id gre
            JOIN gre.gen_res_user_id user
            JOIN gre.gen_res_own_id own
            JOIN user.user_country coun
        WHERE (ore.own_res_status = $cancelledCode) AND ore.own_res_selected_room_id = :roomId AND gre.gen_res_from_date >= :start AND gre.gen_res_to_date <= :end");
        return $query->setParameter('start', $startParam)->setParameter('end', $endParam)->setParameter('roomId', $roomId)->getResult();
    }

    public function getAllReservations($olders, $date = null, $startIndex = 0, $maxResults = 500)
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->select("res")
            ->from("mycpBundle:ownershipReservation", "res")
            ->join("res.own_res_gen_res_id", "gen")
            ->orderBy('res.own_res_gen_res_id', 'ASC')
            ->setFirstResult($startIndex)
            ->setMaxResults($maxResults);

        if($date != null)
        {
            if($olders)
              $qb->andWhere("gen.gen_res_date <= :date")->setParameter('date',$date);
            else
                $qb->andWhere("gen.gen_res_date >= :date")->setParameter('date',$date);
        }

        return $qb->getQuery()->getResult();
    }

    public function getNextReservations($ownId, $maxResults)
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $today = new DateTime();
        $qb->select("min(res.own_res_reservation_from_date) as arrivalDate",
            "count(res) as rooms, sum(res.own_res_count_adults) as adults", "sum(res.own_res_count_childrens) as kids")
            ->from("mycpBundle:ownershipReservation", "res")
            ->join("res.own_res_gen_res_id", "gen")
            ->orderBy('res.own_res_reservation_from_date', 'DESC')
            ->where("gen.gen_res_own_id = :ownId")
            ->andWhere("res.own_res_status = :status")
            ->andWhere("res.own_res_reservation_from_date >= :date")
            ->groupBy("res.own_res_gen_res_id")
            ->setMaxResults($maxResults)
            ->setParameter("ownId", $ownId)
            ->setParameter("date", $today->format('Y-m-d'))
            ->setParameter("status", ownershipReservation::STATUS_RESERVED);

        return $qb->getQuery()->getResult();
    }

    public function getLastClients($ownId, $maxResults)
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $today = new DateTime();
        $qb->select("client.user_user_name", "client.user_last_name", "client.user_email", "co.co_name", "client.user_id")
            ->from("mycpBundle:generalReservation", "res")
            ->join("res.gen_res_user_id", "client")
            ->join("client.user_country", "co")
            ->orderBy('res.gen_res_from_date', 'DESC')
            ->where("res.gen_res_own_id = :ownId")
            ->andWhere("res.gen_res_status = :status")
            ->andWhere("res.gen_res_from_date < :date")
            ->setMaxResults($maxResults)
            ->setParameter("ownId", $ownId)
            ->setParameter("date", $today->format('Y-m-d'))
            ->setParameter("status", generalReservation::STATUS_RESERVED);

        return $qb->getQuery()->getResult();
    }

    public function getReservationByRoomByStartDate($roomId,$startParam) {
    $em = $this->getEntityManager();
    $reservedCode = ownershipReservation::STATUS_RESERVED;
    $query = $em->createQuery("SELECT ore.own_res_id,ore.own_res_count_adults,ore.own_res_count_childrens, ore.own_res_status,coun.co_name, user.user_name, user.user_last_name, ro.room_num, ore, gre.gen_res_id, gre.gen_res_from_date, gre.gen_res_to_date, ore.own_res_reservation_from_date, ore.own_res_reservation_to_date
            FROM mycpBundle:ownershipReservation ore
            JOIN mycpBundle:room ro with ore.own_res_selected_room_id = ro.room_id
            JOIN ore.own_res_gen_res_id gre
            JOIN gre.gen_res_user_id user
            JOIN gre.gen_res_own_id own
            JOIN user.user_country coun
        WHERE (ore.own_res_status = $reservedCode) AND ore.own_res_selected_room_id = :roomId AND gre.gen_res_from_date >= :start");
        return $query->setParameter('start', $startParam)->setParameter('roomId', $roomId)->getResult();
}

    public function getCountReservationsByRoomAndDates($roomId,$startDate, $endDate) {
        $em = $this->getEntityManager();
        $reservedCode = ownershipReservation::STATUS_RESERVED;
        $startDate = $startDate->format('Y-m-d');
        $endDate = $endDate->format('Y-m-d');
        $query = $em->createQuery("SELECT COUNT(ore.own_res_id)
            FROM mycpBundle:ownershipReservation ore
            JOIN mycpBundle:room ro with ore.own_res_selected_room_id = ro.room_id
            JOIN ore.own_res_gen_res_id gre
            JOIN gre.gen_res_user_id user
            JOIN gre.gen_res_own_id own
            JOIN user.user_country coun
        WHERE (ore.own_res_status = $reservedCode) AND ore.own_res_selected_room_id = :roomId AND gre.gen_res_from_date >= :start AND gre.gen_res_to_date <= :endDate");
        return $query->setParameter('start', $startDate)->setParameter('endDate', $endDate)->setParameter('roomId', $roomId)->getSingleScalarResult();
    }

    public function getFromToDestinationCliente($accommodationId, $userId, $confirmFromDate, $confirmToDate){


        /*
         * Parametros
         *
         *  set @accommodationId = 10;
            set @userId = 23595;
            set @confirmFromDate = "2016-08-05";
            set @confirmToDate = "2016-08-07";
         *
         * De donde viene
         *
         * select o.own_mcp_code, owres.`own_res_reservation_from_date` as fromDate, owres.`own_res_reservation_to_date` as toDate
from ownershipreservation owres
join generalreservation gres on owres.own_res_gen_res_id = gres.gen_res_id
join user u on u.user_id = gres.gen_res_user_id
join ownership o on o.own_id = gres.gen_res_own_id
join booking b on b.booking_id = owres.own_res_reservation_booking
join payment p on p.`booking_id` = b.`booking_id`
where gres.gen_res_status = 2
and (p.status = 1 or p.status = 4)
and u.user_id = @userId
and gres.gen_res_own_id != @accommodationId
and (owres.`own_res_reservation_to_date` >= DATE_SUB(@confirmFromDate, INTERVAL 14 day) and owres.`own_res_reservation_to_date` <= @confirmFromDate)
order by owres.`own_res_reservation_from_date` DESC
limit 1;

        Para donde va
select o.own_id, o.own_mcp_code, owres.`own_res_reservation_from_date` as fromDate, owres.`own_res_reservation_to_date` as toDate
from ownershipreservation owres
join generalreservation gres on owres.own_res_gen_res_id = gres.gen_res_id
join user u on u.user_id = gres.gen_res_user_id
join ownership o on o.own_id = gres.gen_res_own_id
join booking b on b.booking_id = owres.own_res_reservation_booking
join payment p on p.`booking_id` = b.`booking_id`
where gres.gen_res_status = 2
and (p.status = 1 or p.status = 4)
and u.user_id = @userId
and gres.gen_res_own_id != @accommodationId
and (owres.`own_res_reservation_from_date` >= @confirmToDate and owres.`own_res_reservation_from_date` <= DATE_ADD(@confirmToDate, INTERVAL 14 day))
order by owres.`own_res_reservation_from_date` ASC
limit 1
;

        */

        $em = $this->getEntityManager();


        $query = 'select  o.own_id as idCasa, 
                          owres.own_res_reservation_from_date as fromDate, 
                          owres.own_res_reservation_to_date as toDate
                from ownershipreservation owres
                join generalreservation gres on owres.own_res_gen_res_id = gres.gen_res_id
                join user u on u.user_id = gres.gen_res_user_id
                join ownership o on o.own_id = gres.gen_res_own_id
                join booking b on b.booking_id = owres.own_res_reservation_booking
                join payment p on p.booking_id = b.booking_id
                where gres.gen_res_status = 2
                and (p.status = 1 or p.status = 4)
                and u.user_id = '. $userId .'
                and gres.gen_res_own_id != '. $accommodationId .'
                and (owres.own_res_reservation_to_date >= DATE_SUB("'. $confirmFromDate .'", INTERVAL 14 day) and owres.own_res_reservation_to_date <= "'. $confirmFromDate .'")
                order by owres.own_res_reservation_from_date DESC
                limit 1
         ';

        $stmt = $em->getConnection()->query($query);
        $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $FromToTravel = array();
        if (count($results) > 0){
            $FromToTravel['from'] = $results[0]['idCasa'];
        }

        $query = 'select o.own_id as idCasa, 
                          owres.own_res_reservation_from_date as fromDate, 
                          owres.own_res_reservation_to_date as toDate
                    from ownershipreservation owres
                    join generalreservation gres on owres.own_res_gen_res_id = gres.gen_res_id
                    join user u on u.user_id = gres.gen_res_user_id
                    join ownership o on o.own_id = gres.gen_res_own_id
                    join booking b on b.booking_id = owres.own_res_reservation_booking
                    join payment p on p.`booking_id` = b.`booking_id`
                    where gres.gen_res_status = 2
                    and (p.status = 1 or p.status = 4)
                    and u.user_id = '. $userId .'
                    and gres.gen_res_own_id != '. $accommodationId .'
                    and (owres.`own_res_reservation_from_date` >= "'. $confirmToDate .'" and owres.`own_res_reservation_from_date` <= DATE_ADD("'. $confirmToDate .'", INTERVAL 14 day))
                    order by owres.`own_res_reservation_from_date` ASC
                    limit 1
         ';

        $stmt = $em->getConnection()->query($query);
        $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        if (count($results) > 0){
            $FromToTravel['to'] = $results[0]['idCasa'];
        }

        return $FromToTravel;

    }
    /**
     */
    function getBookingById($id_booking) {
        $em = $this->getEntityManager();
        $query = $em->createQuery("SELECT min(ore.own_res_reservation_from_date) as arrivalDate  FROM mycpBundle:ownershipReservation ore
        WHERE ore.own_res_reservation_booking = :id_booking");
        return $query->setParameter('id_booking', $id_booking)->getResult();
    }

    function cancelReservationByAgency($ownershipReservation, $timerService){
        $em = $this->getEntityManager();
        $generalReservation = $ownershipReservation->getOwnResGenResId();

        $user = $generalReservation->getGenResUserId();
        $tourOperator = $em->getRepository("PartnerBundle:paTourOperator")->findOneBy(array("tourOperator" => $user->getUserId()));
        $travelAgency = $tourOperator->getTravelAgency();
        $commission = $travelAgency->getCommission() / 100;

        $today = \date('Y-m-d');
        $totalDiffDays = $timerService->diffInDays($today, $generalReservation->getGenResFromDate()->format("Y-m-d"));
        $serviceFee = $generalReservation->getServiceFee();
        $refundTotal = 0;
        $totalToSubstractFromGeneralTotal = 0;
        $nights = $timerService->diffInDays($ownershipReservation->getOwnResReservationToDate()->format("Y-m-d"), $ownershipReservation->getOwnResReservationFromDate()->format("Y-m-d"));
        $roomPrice = $ownershipReservation->getOwnResTotalInSite() / $nights;

        $agencyTax = $em->getRepository("mycpBundle:serviceFee")->calculateTouristServiceFee(1, $nights, $roomPrice,$serviceFee->getId());
        $agencyTax = $agencyTax * $ownershipReservation->getOwnResTotalInSite();

        $accommodationCommission = $generalReservation->getGenResOwnId()->getOwnCommissionPercent() / 100;
        $firstNightPayment = 0;

        if($totalDiffDays > 7){
            $firstNightPayment = $roomPrice * (1 - $accommodationCommission);

            $refundTotal = $ownershipReservation->getOwnResTotalInSite() * (1 - $commission) - $commission  * ($agencyTax + $serviceFee->getFixedTax());
        }
        else{

            if($nights > 1)
                $refundTotal = $ownershipReservation->getOwnResTotalInSite() * (1 - $commission) - $roomPrice - $commission  * ($agencyTax + $serviceFee->getFixedTax());
        }

        $totalToSubstractFromGeneralTotal = $ownershipReservation->getOwnResTotalInSite() * (1 - $accommodationCommission);

        return array(
            "refundTotal" => $refundTotal,
            "firstNightPayment" => $firstNightPayment,
            "totalToSubtract" => $totalToSubstractFromGeneralTotal
        );
}

    function getByIds($idsArray){
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder()
            ->select("owres")
            ->from("mycpBundle:ownershipReservation", "owres")
            ->where("owres.own_res_id IN (:ids)")
            ->setParameter("ids", $idsArray)
            ->orderBy("owres.own_res_gen_res_id");

        return $qb->getQuery()->getResult();
    }

}
