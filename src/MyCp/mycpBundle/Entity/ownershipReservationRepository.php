<?php

namespace MyCp\mycpBundle\Entity;

use Doctrine\ORM\EntityRepository;
use MyCp\mycpBundle\Entity\ownership;

/**
 * ownershipReservationRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class ownershipReservationRepository extends EntityRepository {

    function get_all_reservations($filter_date_reserve, $filter_offer_number, $filter_reference, $filter_date_from, $filter_date_to, $sort_by) {
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
        WHERE ore.own_res_reservation_date LIKE '%$filter_date_reserve%' AND ore.own_res_id LIKE '%$filter_offer_number%'
        AND ow.own_mcp_code LIKE '%$filter_reference%' AND ore.own_res_reservation_from_date LIKE '%$filter_date_from%'
        AND ore.own_res_reservation_to_date LIKE '%$filter_date_to%' $string_order");
        return $query->getArrayResult();
    }

    function get_reservations_by_id_user($id_user) {
        $em = $this->getEntityManager();
        $query = $em->createQuery("SELECT ore,gre FROM mycpBundle:ownershipReservation ore JOIN ore.own_res_gen_res_id gre
        WHERE gre.gen_res_user_id =$id_user");
        return $query->getArrayResult();
    }

    function get_reservation_by_id($id_reservation) {
        $em = $this->getEntityManager();
        $query = $em->createQuery("SELECT ore,gre,us, ow,owm,owp,uc FROM mycpBundle:ownershipReservation ore
        JOIN ore.own_res_gen_res_id gre JOIN gre.gen_res_user_id us JOIN ore.own_res_own_id ow
        JOIN ow.own_address_municipality owm JOIN ow.own_address_province owp
        JOIN us.user_country uc
        WHERE ore.own_res_id=$id_reservation");
        return $query->getArrayResult();
    }

    function edit_reservation($reservation, $data) {
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
        $reservation->setOwnResReservationFromDate(new \DateTime($date_from));
        $reservation->setOwnResReservationToDate(new \DateTime($date_to));
        $reservation->setOwnResNightPrice($data['night_price']);
        $reservation->setOwnResCommissionPercent($data['commission_percent']);
        $em->persist($reservation);
        $em->flush();
    }

    function new_reservation($data) {
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

        $reservation->setOwnResReservationDate(new \DateTime(date('Y-m-d')));
        $reservation->setOwnResReservationFromDate(new \DateTime($date_from));
        $reservation->setOwnResReservationToDate(new \DateTime($date_to));
        $reservation->setOwnResReservationStatus(0);
        $reservation->setOwnResReservationStatusDate(new \DateTime(date('Y-m-d')));
        $em->persist($reservation);
        $em->flush();
    }

    function get_reservation_available_by_user($id_reservation ,$id_user)
    {
        $em = $this->getEntityManager();
        $query = $em->createQuery("SELECT owre,genres,own,mun FROM mycpBundle:ownershipReservation owre
        JOIN owre.own_res_gen_res_id genres JOIN owre.own_res_own_id own JOIN own.own_address_municipality mun
        WHERE owre.own_res_id = $id_reservation AND genres.gen_res_user_id = $id_user");
        return $query->getArrayResult();
    }

    function find_by_user_and_status($id_user, $status_string, $string_sql)
    {
        $em = $this->getEntityManager();
        $query = $em->createQuery("SELECT us,ownre,
        (SELECT pho.pho_name FROM mycpBundle:ownershipPhoto owpho JOIN owpho.own_pho_photo pho WHERE owpho.own_pho_own = gre.gen_res_own_id AND pho.pho_order =
        (SELECT MIN(pho2.pho_order) FROM mycpBundle:ownershipPhoto owpho2 JOIN owpho2.own_pho_photo pho2 WHERE owpho2.own_pho_own = gre.gen_res_own_id ))  AS photo ,
        ow,mun,prov,gre FROM mycpBundle:ownershipReservation ownre JOIN ownre.own_res_gen_res_id gre
        JOIN gre.gen_res_own_id ow
        JOIN gre.gen_res_user_id us
        JOIN ow.own_address_municipality mun
        JOIN ow.own_address_province prov
        WHERE $status_string AND us.user_id=$id_user $string_sql");
        return $query->getArrayResult();
    }

    function find_count_for_menu($id_user)
    {
        $date = \date('Y-m-j');
        $new_date = strtotime ( '-30 day' , strtotime ( $date ) ) ;
        $new_date = \date ( 'Y-m-j' , $new_date );

        $date_days = \date('Y-m-j');
        $date_days = strtotime ( '-60 hours' , strtotime ( $date_days ) ) ;
        $date_days = \date ( 'Y-m-j' , $date_days );

        $em = $this->getEntityManager();
        $query = $em->createQuery("SELECT count(ore_pend) as pending,
        (SELECT count(ore_avail) FROM mycpBundle:ownershipReservation ore_avail JOIN ore_avail.own_res_gen_res_id gen_res WHERE gen_res.gen_res_user_id = $id_user AND ore_avail.own_res_status=1 AND gen_res.gen_res_date > '$date_days')  as available,
        (SELECT count(ore_res) FROM mycpBundle:ownershipReservation ore_res JOIN ore_res.own_res_gen_res_id gen_res_r WHERE gen_res_r.gen_res_user_id = $id_user AND ore_res.own_res_status=2 AND gen_res_r.gen_res_date > '$new_date')  as reserve,
        (SELECT count(gre_cons) FROM mycpBundle:generalReservation gre_cons WHERE gre_cons.gen_res_user_id = $id_user AND gre_cons.gen_res_status=0 AND gre_cons.gen_res_date < '$new_date')  as consult,
        (SELECT count(owre_payed) FROM mycpBundle:ownershipReservation owre_payed JOIN owre_payed.own_res_gen_res_id gen_res_p WHERE gen_res_p.gen_res_user_id = $id_user AND owre_payed.own_res_status=5)  as payed,
        (SELECT count(ore_res_hist) FROM mycpBundle:ownershipReservation ore_res_hist JOIN ore_res_hist.own_res_gen_res_id gen_res_h  WHERE gen_res_h.gen_res_user_id = $id_user AND ore_res_hist.own_res_status=3 AND gen_res_h.gen_res_date < '$new_date')  as reserve_history,
        (SELECT count(fav) FROM mycpBundle:favorite fav WHERE fav.favorite_user = $id_user AND fav.favorite_ownership IS NOT NULL)  as favorites_ownerships,
        (SELECT count(fav_des) FROM mycpBundle:favorite fav_des WHERE fav_des.favorite_user = $id_user AND fav_des.favorite_destination IS NOT NULL)  as favorites_destinations
        FROM mycpBundle:ownershipReservation ore_pend JOIN ore_pend.own_res_gen_res_id gre_pend WHERE gre_pend.gen_res_user_id = $id_user AND ore_pend.own_res_status=0 AND gre_pend.gen_res_date > '$new_date'");
        return $query->getArrayResult();
    }

    function find_by_user_and_status_object($id_user,$status)
    {
        $em = $this->getEntityManager();
        $query = $em->createQuery("SELECT ownre FROM mycpBundle:ownershipReservation ownre JOIN ownre.own_res_gen_res_id genres
        WHERE genres.gen_res_user_id=$id_user AND ownre.own_res_status='$status'");
        return $query->getResult();
    }


}
