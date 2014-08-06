<?php

namespace MyCp\mycpBundle\Entity;

use Doctrine\ORM\EntityRepository;
use MyCp\mycpBundle\Helpers\SyncStatuses;

/**
 * ownershipReservationRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class generalReservationRepository extends EntityRepository {

    function get_all_reservations($filter_date_reserve, $filter_offer_number, $filter_reference,
                                  $filter_date_from, $filter_date_to, $sort_by, $filter_booking_number) {
        $filter_offer_number = strtolower($filter_offer_number);
        $filter_booking_number = strtolower($filter_booking_number);
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
            case 0:
                $string_order = "ORDER BY gre.gen_res_id DESC";
                break;
            case 1:
                $string_order = "ORDER BY gre.gen_res_date ASC";
                break;
            case 2:
                $string_order = "ORDER BY gre.gen_res_own_id ASC";
                break;
            case 3:
                $string_order = "ORDER BY gre.gen_res_from_date ASC";
                break;
            case 4:
                $string_order = "ORDER BY gre.gen_res_status ASC";
                break;
        }
        $em = $this->getEntityManager();
        $query = $em->createQuery("SELECT gre,
        (SELECT count(owres) FROM mycpBundle:ownershipReservation owres WHERE owres.own_res_gen_res_id = gre.gen_res_id),
        (SELECT SUM(owres2.own_res_count_adults) FROM mycpBundle:ownershipReservation owres2 WHERE owres2.own_res_gen_res_id = gre.gen_res_id),
        (SELECT SUM(owres3.own_res_count_childrens) FROM mycpBundle:ownershipReservation owres3 WHERE owres3.own_res_gen_res_id = gre.gen_res_id),
        us, cou,own FROM mycpBundle:generalReservation gre
        JOIN gre.gen_res_own_id own
        JOIN gre.gen_res_user_id us
        JOIN us.user_country cou
        WHERE gre.gen_res_date LIKE :filter_date_reserve
        AND gre.gen_res_from_date LIKE :filter_date_from
        AND gre.gen_res_id LIKE :filter_offer_number
        AND own.own_mcp_code LIKE :filter_reference
        AND gre.gen_res_to_date LIKE :filter_date_to $string_order");


        $query->setParameters(array(
            'filter_date_reserve' => "%".$filter_date_reserve."%",
            'filter_date_from' => "%".$filter_date_from."%",
            'filter_offer_number' => "%".$filter_offer_number."%",
            'filter_reference' => "%".$filter_reference."%",
            'filter_date_to' => "%".$filter_date_to."%",
        ));

        $array_genres=$query->getArrayResult();

        $query = $em->createQuery("SELECT ownres,genres,booking FROM mycpBundle:ownershipReservation ownres
        JOIN ownres.own_res_gen_res_id genres JOIN ownres.own_res_reservation_booking booking
        WHERE booking.booking_id LIKE :filter_booking_number");
        $array_intersection= array();
        $flag=0;
        if($filter_booking_number!='')
        {
            $array_ownres=$query->setParameter('filter_booking_number',"%".$filter_booking_number."%")->getArrayResult();
            foreach($array_genres as $gen)
            {
                foreach($array_ownres as $res)
                {
                    if($gen[0]['gen_res_id']==$res['own_res_gen_res_id']['gen_res_id'])
                    {
                        if($flag==0)
                        {
                            $flag++;
                            array_push($array_intersection, $gen);
                        }
                        else
                        {
                            $flag_2=1;
                            foreach($array_intersection as $item)
                            {
                                if($item[0]['gen_res_id']==$gen[0]['gen_res_id'])
                                {
                                    $flag_2=0;
                                }
                            }
                            if($flag_2==1)
                                array_push($array_intersection, $gen);
                        }
                    }
                }
            }
        }
        else
        {
            $array_intersection=$array_genres;
        }
        return $array_intersection;
    }

    function get_all_users($filter_user_name, $filter_user_email, $filter_user_city,
                                  $filter_user_country, $sort_by) {
        $filter_user_name = strtolower($filter_user_name);
        $filter_user_email = strtolower($filter_user_email);
        $filter_user_city = strtolower($filter_user_city);
        $filter_user_country = strtolower($filter_user_country);

        $string_order = '';
        switch ($sort_by) {
            case 0:
            case 5:
                $string_order = "ORDER BY total_reserves DESC";
                break;
            case 1:
                $string_order = "ORDER BY us.user_user_name ASC";
                break;
            case 2:
                $string_order = "ORDER BY us.user_city ASC";
                break;
            case 3:
                $string_order = "ORDER BY us.user_email ASC";
                break;
            case 4:
                $string_order = "ORDER BY cou.co_name ASC";
                break;

        }
        $em = $this->getEntityManager();
        $query = $em->createQuery("SELECT DISTINCT
            us.user_id,
            us.user_user_name,
            us.user_last_name,
            us.user_city,
            us.user_email,
            cou.co_name,
            (SELECT count(g) FROM mycpBundle:generalReservation g WHERE g.gen_res_user_id = us.user_id) as total_reserves
            FROM mycpBundle:generalReservation gre
            JOIN gre.gen_res_own_id own
            JOIN gre.gen_res_user_id us
            JOIN us.user_country cou
            WHERE (us.user_user_name LIKE :filter_user_name
            OR us.user_last_name LIKE :filter_user_name)
            AND us.user_email LIKE :filter_user_email
            AND us.user_city LIKE :filter_user_city
            AND cou.co_id LIKE :filter_user_country $string_order");

        $query->setParameters(array(
            'filter_user_name' => "%".$filter_user_name."%",
            'filter_user_email' => "%".$filter_user_email."%",
            'filter_user_city' => "%".$filter_user_city."%",
            'filter_user_country' => "%".$filter_user_country."%",
        ));

        $array_genres=$query->getArrayResult();
        return $array_genres;
    }

    function get_all_bookings($filter_booking_number,$filter_date_booking,$filter_user_booking)
    {
        $em = $this->getEntityManager();

        $filter_date_booking_array = explode('_', $filter_date_booking);
        if (count($filter_date_booking_array) > 1)
        {
            $filter_date_booking = $filter_date_booking_array[2] . '-' . $filter_date_booking_array[1] . '-' . $filter_date_booking_array[0];
        }

        $query = $em->createQuery("SELECT payment,booking,curr FROM mycpBundle:payment payment JOIN payment.booking booking
        JOIN payment.currency curr
        WHERE booking.booking_id LIKE :filter_booking_number
        AND booking.booking_user_dates LIKE :filter_user_booking
        AND payment.created LIKE :filter_date_booking
        ORDER BY payment.id DESC");
        return $query->setParameters(array(
            'filter_booking_number' => "%".$filter_booking_number."%",
            'filter_user_booking' => "%".$filter_user_booking."%",
            'filter_date_booking' => "%".$filter_date_booking."%",
        ))
                ->getArrayResult();
    }
    function get_reservations_by_user($id_user) {
        $em = $this->getEntityManager();
        $query = $em->createQuery("SELECT gre,
            (SELECT count(owres) FROM mycpBundle:ownershipReservation owres WHERE owres.own_res_gen_res_id = gre.gen_res_id) AS rooms,
            (SELECT SUM(owres2.own_res_count_adults) FROM mycpBundle:ownershipReservation owres2 WHERE owres2.own_res_gen_res_id = gre.gen_res_id) AS adults,
            (SELECT SUM(owres3.own_res_count_childrens) FROM mycpBundle:ownershipReservation owres3 WHERE owres3.own_res_gen_res_id = gre.gen_res_id) AS childrens,
            ow
            FROM mycpBundle:generalReservation gre
            JOIN gre.gen_res_own_id ow
            JOIN gre.gen_res_user_id us
            WHERE us.user_id = :user_id
            ORDER BY gre.gen_res_id DESC");
        return $query->setParameter('user_id', $id_user)->getArrayResult();
    }

    function find_by_user_and_status($id_user, $status_string, $string_sql) {
        $em = $this->getEntityManager();
        $query = $em->createQuery("SELECT us,gre,
        (SELECT pho.pho_name FROM mycpBundle:ownershipPhoto owpho JOIN owpho.own_pho_photo pho WHERE owpho.own_pho_own = gre.gen_res_own_id AND pho.pho_order =
        (SELECT MIN(pho2.pho_order) FROM mycpBundle:ownershipPhoto owpho2 JOIN owpho2.own_pho_photo pho2 WHERE owpho2.own_pho_own = gre.gen_res_own_id ))  AS photo ,
        ow,mun,prov FROM mycpBundle:generalReservation gre
        JOIN gre.gen_res_own_id ow
        JOIN gre.gen_res_user_id us
        JOIN ow.own_address_municipality mun
        JOIN ow.own_address_province prov
        WHERE $status_string AND us.user_id=$id_user $string_sql");
        return $query->getArrayResult();
    }

    function get_reservation_available_by_user($id_reservation, $id_user) {
        $em = $this->getEntityManager();
        $query = $em->createQuery("SELECT gre FROM mycpBundle:generalReservation gre
        WHERE gre.gen_res_id = $id_reservation AND gre.gen_res_user_id = $id_user");
        return $query->getResult();
    }

    function get_reminder_available() {
        $yesterday = date("Y-m-d", strtotime('yesterday'));
        $hour = date('G');
        $em = $this->getEntityManager();
        $query = $em->createQuery("SELECT gre FROM mycpBundle:generalReservation gre
        WHERE gre.gen_res_status = ".generalReservation::STATUS_AVAILABLE." AND gre.gen_res_status_date = '$yesterday' AND gre.gen_res_hour = '$hour'");
        return $query->getResult();
    }

    function get_time_over_reservations() {
        // pone las reservaciones no disponibles despues de 48 horas.
        $day = date("Y-m-d", strtotime('-2 day'));
        $hour = date('G');
        $em = $this->getEntityManager();
        $query = $em->createQuery("SELECT gre FROM mycpBundle:generalReservation gre
        WHERE gre.gen_res_status = ".generalReservation::STATUS_AVAILABLE." AND gre.gen_res_status_date = '$day' AND gre.gen_res_hour = '$hour'");
        return $query->getResult();
    }

    function getNotSyncs() {
        $em = $this->getEntityManager();
        $query = $em->createQuery("SELECT gre FROM mycpBundle:generalReservation gre
            WHERE gre.gen_res_sync_st <> " . SyncStatuses::SYNC);
        return $query->getResult();
    }

    public function getResponseLanguaje($reservation) {
        $em = $this->getEntityManager();
        $userTourist = $em->getRepository("mycpBundle:userTourist")->findOneBy(array('user_tourist_user' => $reservation->getGenResUserId()));
        return $userTourist != null ? $userTourist->getUserTouristLanguage()->getLangCode() : "EN";
    }

    public function getResponseCurrency($reservation) {
        $em = $this->getEntityManager();
        $userTourist = $em->getRepository("mycpBundle:userTourist")->findOneBy(array('user_tourist_user' => $reservation->getGenResUserId()));
        return $userTourist != null ? $userTourist->getUserTouristCurrency()->getCurrCode() : "usd";
    }

}
