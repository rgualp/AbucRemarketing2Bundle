<?php

namespace MyCp\mycpBundle\Entity;

use DateTime;
use Doctrine\ORM\EntityRepository;
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
        WHERE (ore.own_res_status = $reservedCode OR ore.own_res_status = $cancelledCode) AND gre.gen_res_from_date >= :start AND gre.gen_res_to_date <= :end");
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
        WHERE (ore.own_res_status = $reservedCode OR ore.own_res_status = $cancelledCode) AND ore.own_res_reservation_from_date >= :start AND ore.own_res_reservation_to_date <= :end AND own.own_id = :own_id");
        return $query->setParameter('start', $startParam)->setParameter('end', $endParam)->setParameter('own_id', $ownership)->getResult();
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
            o.own_commission_percent as commission_percent
            FROM mycpBundle:ownershipReservation ore JOIN ore.own_res_gen_res_id gre
            JOIN gre.gen_res_own_id o
            JOIN o.own_address_municipality as mun
            JOIN o.own_address_province as prov
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
            (select min(r.room_bathroom) from mycpBundle:room r where r.room_id = ore.own_res_selected_room_id) as room_bathroom
            FROM mycpBundle:ownershipReservation ore JOIN ore.own_res_gen_res_id gre
        WHERE ore.own_res_reservation_booking = :id_booking and gre.gen_res_own_id = :id_own");
        return $query->setParameter('id_booking', $id_booking)->setParameter('id_own', $own_id)->getArrayResult();
    }

    function getById($id_reservation) {
        $em = $this->getEntityManager();
        $query = $em->createQuery("SELECT ore,gre,us, ow,owm,owp,uc FROM mycpBundle:ownershipReservation ore
        JOIN ore.own_res_gen_res_id gre JOIN gre.gen_res_user_id us JOIN ore.own_res_own_id ow
        JOIN ow.own_address_municipality owm JOIN ow.own_address_province owp
        JOIN us.user_country uc
        WHERE ore.own_res_id= :id_reservation");
        return $query->setParameter('id_reservation', $id_reservation)->getArrayResult();
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
        $query = $em->createQuery("SELECT count(ore_avail) as available
                                   FROM mycpBundle:ownershipReservation ore_avail
                                   JOIN ore_avail.own_res_gen_res_id gen_res
                                   WHERE gen_res.gen_res_user_id = $id_user
                                     AND ore_avail.own_res_status=" . ownershipReservation::STATUS_AVAILABLE . "
                                     AND gen_res.gen_res_date > '$date_days'");
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

    function getOwnReservationsTotal()
    {
        $em = $this->getEntityManager();
        $query_string = "SELECT count(own_r) FROM mycpBundle:ownershipReservation own_r";
        return $em->createQuery($query_string)
            ->getSingleScalarResult();
    }

    function getOwnReservationsByPages($startIndex, $pageSize)
    {
        $em = $this->getEntityManager();
        $query_string = "SELECT own_r FROM mycpBundle:ownershipReservation own_r";
        return $em->createQuery($query_string)
            ->setFirstResult($startIndex)->setMaxResults($pageSize)->getResult();
    }

}
