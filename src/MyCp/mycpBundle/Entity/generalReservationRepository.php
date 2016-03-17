<?php

namespace MyCp\mycpBundle\Entity;

use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\ORM\EntityRepository;
use MyCp\mycpBundle\Helpers\Dates;
use MyCp\mycpBundle\Helpers\Operations;
use MyCp\mycpBundle\Helpers\SyncStatuses;
use MyCp\mycpBundle\Helpers\OrderByHelper;

/**
 * ownershipReservationRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class generalReservationRepository extends EntityRepository {

    function getAll($filter_date_reserve, $filter_offer_number, $filter_reference, $filter_date_from, $filter_date_to, $sort_by, $filter_booking_number, $filter_status, $items_per_page=null, $page=null) {
        $gaQuery = "SELECT gre.gen_res_date, gre.gen_res_id, own.own_mcp_code, gre.gen_res_total_in_site,gre.gen_res_status,gre.gen_res_from_date,
        (SELECT count(owres) FROM mycpBundle:ownershipReservation owres WHERE owres.own_res_gen_res_id = gre.gen_res_id),
        (SELECT SUM(owres2.own_res_count_adults) FROM mycpBundle:ownershipReservation owres2 WHERE owres2.own_res_gen_res_id = gre.gen_res_id),
        (SELECT SUM(owres3.own_res_count_childrens) FROM mycpBundle:ownershipReservation owres3 WHERE owres3.own_res_gen_res_id = gre.gen_res_id),
        (SELECT MIN(owres4.own_res_reservation_from_date) FROM mycpBundle:ownershipReservation owres4 WHERE owres4.own_res_gen_res_id = gre.gen_res_id),
        (SELECT SUM(DATE_DIFF(owres5.own_res_reservation_to_date, owres5.own_res_reservation_from_date)) FROM mycpBundle:ownershipReservation owres5 WHERE owres5.own_res_gen_res_id = gre.gen_res_id),
        u.user_user_name, u.user_last_name, u.user_email
        FROM mycpBundle:generalReservation gre
        JOIN gre.gen_res_own_id own
        JOIN gre.gen_res_user_id u ";

        return $this->getByQuery($filter_date_reserve, $filter_offer_number, $filter_reference, $filter_date_from, $filter_date_to, $sort_by, $filter_booking_number, $filter_status, -1, $gaQuery,$items_per_page, $page);
    }

    function getByUserCasa($filter_date_reserve, $filter_offer_number, $filter_reference, $filter_date_from, $filter_date_to, $sort_by, $filter_booking_number, $user_casa_id, $filter_status) {
        $gaQuery = "SELECT gre.gen_res_date, gre.gen_res_id, own.own_mcp_code, gre.gen_res_total_in_site,gre.gen_res_status,gre.gen_res_from_date,
        (SELECT count(owres) FROM mycpBundle:ownershipReservation owres WHERE owres.own_res_gen_res_id = gre.gen_res_id),
        (SELECT SUM(owres2.own_res_count_adults) FROM mycpBundle:ownershipReservation owres2 WHERE owres2.own_res_gen_res_id = gre.gen_res_id),
        (SELECT SUM(owres3.own_res_count_childrens) FROM mycpBundle:ownershipReservation owres3 WHERE owres3.own_res_gen_res_id = gre.gen_res_id),
        (SELECT MIN(owres4.own_res_reservation_from_date) FROM mycpBundle:ownershipReservation owres4 WHERE owres4.own_res_gen_res_id = gre.gen_res_id),
        (SELECT SUM(DATE_DIFF(owres5.own_res_reservation_to_date, owres5.own_res_reservation_from_date)) FROM mycpBundle:ownershipReservation owres5 WHERE owres5.own_res_gen_res_id = gre.gen_res_id),
        u.user_user_name, u.user_last_name, u.user_email
        FROM mycpBundle:generalReservation gre
        JOIN gre.gen_res_own_id own
        JOIN mycpBundle:userCasa uca with uca.user_casa_ownership = own.own_id
        JOIN gre.gen_res_user_id u ";

        return $this->getByQuery($filter_date_reserve, $filter_offer_number, $filter_reference, $filter_date_from, $filter_date_to, $sort_by, $filter_booking_number, $filter_status, $user_casa_id, $gaQuery);
    }

    function getByQuery($filter_date_reserve, $filter_offer_number, $filter_reference, $filter_date_from, $filter_date_to, $sort_by, $filter_booking_number, $filter_status, $user_casa_id, $queryStr,$items_per_page = null, $page = null) {
        $filter_offer_number = strtolower($filter_offer_number);
        $filter_booking_number = strtolower($filter_booking_number);
        $filter_offer_number = str_replace('cas.', '', $filter_offer_number);
        $filter_offer_number = str_replace('cas', '', $filter_offer_number);
        $filter_offer_number = str_replace('.', '', $filter_offer_number);
        $filter_offer_number = str_replace(' ', '', $filter_offer_number);
        $array_offer_number = explode('-', $filter_offer_number);


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
            case OrderByHelper::DEFAULT_ORDER_BY:
            case OrderByHelper::RESERVATION_NUMBER:
                $string_order = "ORDER BY gre.gen_res_id DESC";
                break;
            case OrderByHelper::RESERVATION_DATE:
                $string_order = "ORDER BY gre.gen_res_date DESC, gre.gen_res_id DESC";
                break;
            case OrderByHelper::RESERVATION_ACCOMMODATION_CODE:
                $string_order = "ORDER BY LENGTH(own.own_mcp_code) ASC, own.own_mcp_code ASC, gre.gen_res_id DESC";
                break;
            case OrderByHelper::RESERVATION_DATE_ARRIVE:
                $string_order = "ORDER BY gre.gen_res_from_date DESC, gre.gen_res_id DESC";
                break;
            case OrderByHelper::RESERVATION_STATUS:
                $string_order = "ORDER BY gre.gen_res_status ASC, gre.gen_res_id DESC";
                break;
            case OrderByHelper::RESERVATION_PRICE_TOTAL:
                $string_order = "ORDER BY gre.gen_res_total_in_site DESC, gre.gen_res_id DESC";
                break;
            case OrderByHelper::RESERVATION_CLIENT:
                $string_order = "ORDER BY gre.gen_res_date DESC, u.user_user_name ASC, u.user_last_name ASC, u.user_email ASC, gre.gen_res_from_date DESC";
                break;
        }
        $em = $this->getEntityManager();

        $where = "";
        if(count($array_offer_number) > 1) {
            if($array_offer_number[0] < $array_offer_number[1])
                $where .= (($where != "") ? " AND ": " WHERE "). " gre.gen_res_id >= $array_offer_number[0] AND gre.gen_res_id <= $array_offer_number[1] ";
            else
                $where .= (($where != "") ? " AND ": " WHERE "). " gre.gen_res_id >= $array_offer_number[1] AND gre.gen_res_id <= $array_offer_number[0] ";
        }
        else if($filter_offer_number != "" and $filter_offer_number != "null")
            $where .= (($where != "") ? " AND ": " WHERE "). " gre.gen_res_id = $filter_offer_number";

        if($filter_date_from != "" && $filter_date_from != "null" && $filter_date_to != "" && $filter_date_to != "null")
            $where .= (($where != "") ? " AND ": " WHERE "). " gre.gen_res_from_date >= '$filter_date_from' AND gre.gen_res_to_date <= '$filter_date_to'";
        else if($filter_date_from != "" && $filter_date_from != "null" && ($filter_date_to == "" || $filter_date_to == "null"))
            $where .= (($where != "") ? " AND ": " WHERE "). " gre.gen_res_from_date >= '$filter_date_from'";
        else if(($filter_date_from == "" || $filter_date_from == "null") && $filter_date_to != "" && $filter_date_to != "null")
            $where .= (($where != "") ? " AND ": " WHERE "). " gre.gen_res_to_date <= '$filter_date_to'";

        if($filter_date_reserve != "" && $filter_date_reserve != "null")
            $where .= (($where != "") ? " AND ": " WHERE "). " gre.gen_res_date >= '$filter_date_reserve'";

        if($filter_reference != "" && $filter_reference != "null")
            $where .= (($where != "") ? " AND ": " WHERE "). " own.own_mcp_code LIKE '%$filter_reference%'";

        if($filter_status != "" && $filter_status != "-1" && $filter_status != "null")
            $where .= (($where != "") ? " AND ": " WHERE "). " gre.gen_res_status = $filter_status ";

        if ($user_casa_id != -1)
            $where .= (($where != "") ? " AND ": " WHERE "). " uca.user_casa_id = $user_casa_id ";

        $queryStr = $queryStr. $where . $string_order;
        //var_dump($queryStr); die;
        $query = $em->createQuery($queryStr);

        if($filter_status != "" && $filter_status != "-1" && $filter_status != "null") {
            $query->setParameter('filter_status', $filter_status);
        }

        $array_genres = ($items_per_page != null && $page != null) ? $query->setMaxResults($items_per_page)->setFirstResult(($page - 1) * $items_per_page)->getArrayResult() : $query->getArrayResult();

        $query = $em->createQuery("SELECT ownres,genres,booking FROM mycpBundle:ownershipReservation ownres
        JOIN ownres.own_res_gen_res_id genres JOIN ownres.own_res_reservation_booking booking
        WHERE booking.booking_id LIKE :filter_booking_number");
        $array_intersection = array();
        $flag = 0;
        if ($filter_booking_number != '') {
            $array_ownres = $query->setParameter('filter_booking_number', "%" . $filter_booking_number . "%")->getArrayResult();
            foreach ($array_genres as $gen) {
                foreach ($array_ownres as $res) {
                    if ($gen[0]['gen_res_id'] == $res['own_res_gen_res_id']['gen_res_id']) {
                        if ($flag == 0) {
                            $flag++;
                            array_push($array_intersection, $gen);
                        } else {
                            $flag_2 = 1;
                            foreach ($array_intersection as $item) {
                                if ($item[0]['gen_res_id'] == $gen[0]['gen_res_id']) {
                                    $flag_2 = 0;
                                }
                            }
                            if ($flag_2 == 1)
                                array_push($array_intersection, $gen);
                        }
                    }
                }
            }
        }
        else {
            $array_intersection = $array_genres;
        }
        return $array_intersection;
    }

    function getTotalReservations()
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->select("count(gen)")
           ->from("mycpBundle:generalReservation", "gen");
        return $qb->getQuery()->getSingleScalarResult();
    }

    function getUsers($filter_user_name, $filter_user_email, $filter_user_city, $filter_user_country, $sort_by, $ownId = null) {
        $filter_user_name = strtolower($filter_user_name);
        $filter_user_email = strtolower($filter_user_email);
        $filter_user_city = strtolower($filter_user_city);
        $filter_user_country = strtolower($filter_user_country);

        $string_order = '';
        switch ($sort_by) {
            case OrderByHelper::DEFAULT_ORDER_BY:
            case OrderByHelper::CLIENT_RESERVATIONS_TOTAL:
                $string_order = "ORDER BY total_reserves DESC";
                break;
            case OrderByHelper::CLIENT_NAME:
                $string_order = "ORDER BY us.user_user_name ASC";
                break;
            case OrderByHelper::CLIENT_CITY:
                $string_order = "ORDER BY us.user_city ASC, total_reserves DESC";
                break;
            case OrderByHelper::CLIENT_EMAIL:
                $string_order = "ORDER BY us.user_email ASC";
                break;
            case OrderByHelper::CLIENT_COUNTRY:
                $string_order = "ORDER BY cou.co_name ASC, total_reserves DESC";
                break;
        }

        $whereOwn = "";
        $countReservations = "";

        if ($ownId != null) {
            $whereOwn = " AND own.own_id = $ownId AND (gre.gen_res_status = " . generalReservation::STATUS_RESERVED . " OR gre.gen_res_status = " . generalReservation::STATUS_PARTIAL_RESERVED . ")";
            $countReservations = " AND g.gen_res_own_id = $ownId AND (g.gen_res_status = " . generalReservation::STATUS_RESERVED . " OR g.gen_res_status = " . generalReservation::STATUS_PARTIAL_RESERVED . ")";
        }

        $em = $this->getEntityManager();
        $queryString = "SELECT DISTINCT
            us.user_id,
            us.user_user_name,
            us.user_last_name,
            us.user_city,
            us.user_email,
            cou.co_name,
            (SELECT count(g) FROM mycpBundle:generalReservation g WHERE g.gen_res_user_id = us.user_id $countReservations) as total_reserves,
            (SELECT min(lang.lang_name) FROM mycpBundle:userTourist ut JOIN ut.user_tourist_language lang WHERE ut.user_tourist_user = us.user_id) as langName,
            (SELECT min(curr.curr_code) FROM mycpBundle:userTourist ut1 JOIN ut1.user_tourist_currency curr WHERE ut1.user_tourist_user = us.user_id) as currName
            FROM mycpBundle:generalReservation gre
            JOIN gre.gen_res_own_id own
            JOIN gre.gen_res_user_id us
            JOIN us.user_country cou
            WHERE (us.user_user_name LIKE :filter_user_name
            OR us.user_last_name LIKE :filter_user_name)
            AND us.user_email LIKE :filter_user_email
            AND us.user_city LIKE :filter_user_city
            AND cou.co_id LIKE :filter_user_country $whereOwn $string_order";

        $query = $em->createQuery($queryString);

        $query->setParameters(array(
            'filter_user_name' => "%" . $filter_user_name . "%",
            'filter_user_email' => "%" . $filter_user_email . "%",
            'filter_user_city' => "%" . $filter_user_city . "%",
            'filter_user_country' => "%" . $filter_user_country . "%",
        ));

        $array_genres = $query->getArrayResult();
        return $array_genres;
    }

    function getAllBookings($filter_booking_number, $filter_date_booking, $filter_user_booking, $filter_arrive_date_booking, $filter_reservation, $filter_ownership, $filter_currency) {
        $em = $this->getEntityManager();

        $filter_date_booking_array = explode('_', $filter_date_booking);
        if (count($filter_date_booking_array) > 1) {
            $filter_date_booking = $filter_date_booking_array[2] . '-' . $filter_date_booking_array[1] . '-' . $filter_date_booking_array[0];
        }

        $filter_arrive_date_booking_array = explode('_', $filter_arrive_date_booking);
        if (count($filter_arrive_date_booking_array) > 1) {
            $filter_arrive_date_booking = $filter_arrive_date_booking_array[2] . '-' . $filter_arrive_date_booking_array[1] . '-' . $filter_arrive_date_booking_array[0];
        }

        $where = "";
        if($filter_arrive_date_booking != "")
            $where .= " AND (SELECT min(ow1.own_res_reservation_from_date) FROM mycpBundle:ownershipReservation ow1 WHERE ow1.own_res_reservation_booking = booking.booking_id) = '$filter_arrive_date_booking' ";

        if($filter_reservation != "")
            $where .= " AND (SELECT min(ow2.own_res_gen_res_id) FROM mycpBundle:ownershipReservation ow2 WHERE ow2.own_res_reservation_booking = booking.booking_id) = '$filter_reservation' ";

        if($filter_ownership != "")
            $where .= " AND (SELECT min(own.own_mcp_code) FROM mycpBundle:ownershipReservation ow3 JOIN ow3.own_res_gen_res_id gres3 JOIN gres3.gen_res_own_id own WHERE ow3.own_res_reservation_booking = booking.booking_id) = '$filter_ownership' ";


        $query = $em->createQuery("SELECT payment.created,
        payment.payed_amount,
        booking.booking_id,
        curr.curr_code,
        booking.booking_user_dates,
        (SELECT min(co.co_name) FROM mycpBundle:user user JOIN user.user_country co WHERE user.user_id = booking.booking_user_id) as country,
        (SELECT min(ow.own_res_reservation_from_date) FROM mycpBundle:ownershipReservation ow WHERE ow.own_res_reservation_booking = booking.booking_id) as arrivalDate
        FROM mycpBundle:payment payment JOIN payment.booking booking
        JOIN payment.currency curr
        WHERE booking.booking_id LIKE :filter_booking_number
        AND booking.booking_user_dates LIKE :filter_user_booking
        AND payment.created LIKE :filter_date_booking
        ANd curr.curr_id LIKE :filter_currency
        $where
        ORDER BY payment.id DESC");
        return $query->setParameters(array(
                            'filter_booking_number' => "%" . $filter_booking_number . "%",
                            'filter_user_booking' => "%" . $filter_user_booking . "%",
                            'filter_date_booking' => "%" . $filter_date_booking . "%",
                            'filter_currency' => "%" . $filter_currency . "%",
                        ))
                        ->getArrayResult();
    }

    function getByUser($id_user, $ownId = null) {
        $em = $this->getEntityManager();

        $whereOwn = "";

        if ($ownId != null) {
            $whereOwn = " AND ow.own_id= $ownId ";
        }

        $queryString = "SELECT gre.gen_res_date,gre.gen_res_id,gre.gen_res_total_in_site,gre.gen_res_status,
            (SELECT count(owres) FROM mycpBundle:ownershipReservation owres WHERE owres.own_res_gen_res_id = gre.gen_res_id) AS rooms,
            (SELECT SUM(owres2.own_res_count_adults) FROM mycpBundle:ownershipReservation owres2 WHERE owres2.own_res_gen_res_id = gre.gen_res_id) AS adults,
            (SELECT SUM(owres3.own_res_count_childrens) FROM mycpBundle:ownershipReservation owres3 WHERE owres3.own_res_gen_res_id = gre.gen_res_id) AS childrens,
            (SELECT SUM(DATE_DIFF(owres5.own_res_reservation_to_date, owres5.own_res_reservation_from_date)) FROM mycpBundle:ownershipReservation owres5 WHERE owres5.own_res_gen_res_id = gre.gen_res_id) as totalNights,
            ow.own_mcp_code, ow.own_id, gre.gen_res_from_date
            FROM mycpBundle:generalReservation gre
            JOIN gre.gen_res_own_id ow
            JOIN gre.gen_res_user_id us
            WHERE us.user_id = :user_id $whereOwn
            ORDER BY gre.gen_res_id DESC";

        $query = $em->createQuery($queryString);
        return $query->setParameter('user_id', $id_user)->getArrayResult();
    }

    function findByUserAndStatus($id_user, $status_string, $string_sql) {
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

    function getAvailableByUser($id_reservation, $id_user) {
        $em = $this->getEntityManager();
        $query = $em->createQuery("SELECT gre FROM mycpBundle:generalReservation gre
        WHERE gre.gen_res_id = $id_reservation AND gre.gen_res_user_id = $id_user");
        return $query->getResult();
    }

    function getReminderAvailable() {
        $yesterday = date("Y-m-d", strtotime('yesterday'));
        $hour = date('G');
        $em = $this->getEntityManager();
        $query = $em->createQuery("SELECT gre FROM mycpBundle:generalReservation gre
        WHERE gre.gen_res_status = " . generalReservation::STATUS_AVAILABLE . " AND gre.gen_res_status_date = '$yesterday' AND gre.gen_res_hour = '$hour'");
        return $query->getResult();
    }

    function getTimeOverReservations() {
        // pone las reservaciones no disponibles despues de 48 horas.
        $day = date("Y-m-d", strtotime('-2 day'));
        $hour = date('G');
        $em = $this->getEntityManager();
        $query = $em->createQuery("SELECT gre FROM mycpBundle:generalReservation gre
        WHERE gre.gen_res_status = " . generalReservation::STATUS_AVAILABLE . " AND gre.gen_res_status_date <= '$day' AND gre.gen_res_hour = '$hour'");
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

    public function setAsNotAvailable($reservations_ids, $save_option = Operations::SAVE) {
        $em = $this->getEntityManager();
        foreach ($reservations_ids as $res_id) {
            $genRes = $em->getRepository('mycpBundle:generalReservation')->find($res_id);
            $genRes->setGenResStatus(generalReservation::STATUS_NOT_AVAILABLE);
            $genRes->setGenResStatusDate(new \DateTime());
            $genRes->setGenResHour(date('G'));
            $em->persist($genRes);

            $reservations = $em->getRepository('mycpBundle:ownershipReservation')->findBy(array('own_res_gen_res_id' => $res_id));

            foreach ($reservations as $res) {
                $res->setOwnResStatus(ownershipReservation::STATUS_NOT_AVAILABLE);
                $em->persist($res);

                if($save_option == Operations::SAVE_AND_UPDATE_CALENDAR) {
                    //Creando una no disponibilidad para cada reserva marcada como No Disponible si aun no hay no disponibilidades almacenadas en este rango de fecha
                    if ($em->getRepository("mycpBundle:unavailabilityDetails")->existByDateAndRoom($res->getOwnResSelectedRoomId(), $res->getOwnResReservationFromDate(), $res->getOwnResReservationToDate()) == 0) {
                        $uDetails = new unavailabilityDetails();
                        $room = $em->getRepository("mycpBundle:room")->find($res->getOwnResSelectedRoomId());
                        $uDetails->setRoom($room)
                            ->setUdSyncSt(SyncStatuses::ADDED)
                            ->setUdFromDate($res->getOwnResReservationFromDate())
                            ->setUdToDate($res->getOwnResReservationToDate())
                            ->setUdReason("Generada automaticamente por ser no disponible la reserva CAS." . $res->getOwnResGenResId()->getGenResId());

                        $em->persist($uDetails);
                    }
                }
            }
        }
        $em->flush();
    }

    public function getBookings($id_reservation) {
        $em = $this->getEntityManager();
        $query = $em->createQuery("SELECT DISTINCT book.booking_id FROM mycpBundle:ownershipReservation res
            JOIN res.own_res_reservation_booking book
            WHERE res.own_res_gen_res_id = " . $id_reservation);
        return $query->getResult();
    }

    /**
     *
     * @param generalReservation $generalReservation
     * @return array An array of ownershipReservations
     */
    public function getOwnershipReservations(generalReservation $generalReservation) {
        $em = $this->getEntityManager();
        $ownershipReservations = $em
                ->getRepository('mycpBundle:ownershipReservation')
                ->findBy(array('own_res_gen_res_id' => $generalReservation->getGenResId()));

        return $ownershipReservations;
    }

    /**
     * Returns whether or not a reminder email should be sent out.
     *
     * @param generalReservation $generalReservation
     * @return bool
     */
    public function shallSendOutReminderEmail(generalReservation $generalReservation) {
        $em = $this->getEntityManager();

        $userId = $generalReservation->getGenResUserId()->getUserId();
        $previousPayedReservations = count($em->getRepository("mycpBundle:generalReservation")->getPayedReservations($userId));

        if ($previousPayedReservations > 0)
            return false;

        if (!$generalReservation->hasStatusAvailable()) {
            return false;
        }

        $ownershipReservations = $em->getRepository('mycpBundle:generalReservation')->getOwnershipReservations($generalReservation);
        $isAtLeastOneOwnResAvailable = false;

        /** @var $ownershipReservation ownershipReservation */
        foreach ($ownershipReservations as $ownershipReservation) {

            if ($ownershipReservation->hasStatusAvailable()) {
                $isAtLeastOneOwnResAvailable = true;
                break;
            }
        }

        return $isAtLeastOneOwnResAvailable;
    }

    public function getPayedReservations($user_id) {
        $em = $this->getEntityManager();
        $query = $em->createQuery("SELECT genRes FROM mycpBundle:generalReservation genRes
            WHERE genRes.gen_res_user_id = $user_id
                AND (genRes.gen_res_status = " . generalReservation::STATUS_PARTIAL_RESERVED . " OR
                    genRes.gen_res_status = " . generalReservation::STATUS_RESERVED . ")"
        );
        return $query->getResult();
    }

    public function shallSendOutFeedbackReminderEmail(generalReservation $generalReservation) {
        $em = $this->getEntityManager();

        if (!$generalReservation->hasStatusReserved()) {
            return false;
        }
        $ownershipReservations = $em->getRepository('mycpBundle:generalReservation')->getOwnershipReservations($generalReservation);


        /** @var $ownershipReservation ownershipReservation */
        foreach ($ownershipReservations as $ownershipReservation) {

            if (!$ownershipReservation->hasStatusReserved()) {
                return false;
            }
        }

        $userId = $generalReservation->getGenResUserId()->getUserId();
        $ownershipId = $generalReservation->getGenResOwnId()->getOwnId();
        $date = $generalReservation->getGenResFromDate();

        $comments = count($em->getRepository("mycpBundle:comment")->getByUserOwnership($userId, $ownershipId, $date));

        return $comments == 0;
    }

    /**
     * @param $reservationId
     * @return generalReservation
     * @throws \LogicException
     */
    public function getGeneralReservationById($reservationId) {
        $em = $this->getEntityManager();
        $generalReservation = $em
                ->getRepository('mycpBundle:generalReservation')
                ->find($reservationId);

        if (empty($generalReservation)) {
            throw new \LogicException('No reservation found for ID ' . $reservationId);
        }

        return $generalReservation;
    }

    public function updateDates(generalReservation $generalReservation) {
        $em = $this->getEntityManager();
        $ownReservations = $em
                ->getRepository('mycpBundle:ownershipReservation')
                ->findBy(array("own_res_gen_res_id" => $generalReservation->getGenResId()));

        if (count($ownReservations) > 0) {
            $min_date = null;
            $max_date = null;
            $nights = 0;

            foreach ($ownReservations as $item) {

                $nights += $item->getOwnResNights();

                if ($min_date == null)
                    $min_date = $item->getOwnResReservationFromDate();
                else if ($item->getOwnResReservationFromDate() < $min_date)
                    $min_date = $item->getOwnResReservationFromDate();

                if ($max_date == null)
                    $max_date = $item->getOwnResReservationToDate();
                else if ($item->getOwnResReservationToDate() > $max_date)
                    $max_date = $item->getOwnResReservationToDate();
            }

            $generalReservation->setGenResFromDate($min_date);
            $generalReservation->setGenResToDate($max_date);
            $generalReservation->setGenResNights($nights);

            $em->persist($generalReservation);
            $em->flush();
        }
    }

    function getCheckins($checkinDate, $orderBy = OrderByHelper::CHECKIN_ORDER_BY_ACCOMMODATION_CODE) {
        $queryStr = "SELECT gre,
        (SELECT count(owres) FROM mycpBundle:ownershipReservation owres WHERE owres.own_res_gen_res_id = gre.gen_res_id AND owres.own_res_status = :reservationStatus),
        (SELECT SUM(owres1.own_res_count_adults) FROM mycpBundle:ownershipReservation owres1 WHERE owres1.own_res_gen_res_id = gre.gen_res_id AND owres1.own_res_status = :reservationStatus),
        (SELECT SUM(owres2.own_res_count_childrens) FROM mycpBundle:ownershipReservation owres2 WHERE owres2.own_res_gen_res_id = gre.gen_res_id AND owres2.own_res_status = :reservationStatus),
        us, cou,own,prov,
        (SELECT MIN(p.created) FROM mycpBundle:payment p JOIN p.booking b WHERE b.booking_id = (SELECT MIN(owres3.own_res_reservation_booking) FROM mycpBundle:ownershipReservation owres3 WHERE owres3.own_res_gen_res_id = gre.gen_res_id AND owres3.own_res_status = :reservationStatus))
        FROM mycpBundle:generalReservation gre
        JOIN gre.gen_res_own_id own
        JOIN gre.gen_res_user_id us
        JOIN us.user_country cou
        JOIN own.own_address_province prov
        WHERE gre.gen_res_from_date LIKE :filter_date_from
        AND gre.gen_res_status = :generalReservationReservedStatus OR gre.gen_res_status = :generalReservationPartialReservedStatus";

        switch($orderBy)
        {
            case OrderByHelper::DEFAULT_ORDER_BY:
            case OrderByHelper::CHECKIN_ORDER_BY_ACCOMMODATION_CODE:
                $queryStr .= " ORDER BY own.own_mcp_code ASC ";break;
            case OrderByHelper::CHECKIN_ORDER_BY_ACCOMMODATION_PROVINCE:
                $queryStr .= " ORDER BY prov.prov_name ASC, own.own_mcp_code ASC "; break;
            case OrderByHelper::CHECKIN_ORDER_BY_RESERVATION_CASCODE;
                $queryStr .= " ORDER BY gre.gen_res_id ASC ";break;
            case OrderByHelper::CHECKIN_ORDER_BY_RESERVATION_RESERVED_DATE;
                $queryStr .= " ORDER BY gre.gen_res_date ASC, own.own_mcp_code ASC "; break;
        }

        $array_date_from = explode('/', $checkinDate);
        if (count($array_date_from) > 1)
            $checkinDate = $array_date_from[2] . '-' . $array_date_from[1] . '-' . $array_date_from[0];

        $em = $this->getEntityManager();
        $query = $em->createQuery($queryStr);

        $query->setParameters(array(
            'filter_date_from' => "%" . $checkinDate . "%",
            'reservationStatus' => ownershipReservation::STATUS_RESERVED,
            'generalReservationReservedStatus' => generalReservation::STATUS_RESERVED,
            'generalReservationPartialReservedStatus' => generalReservation::STATUS_PARTIAL_RESERVED
        ));

        return $query->getArrayResult();
    }

    function getReservationsForNightCounterTotal()
    {
        $em = $this->getEntityManager();
        $query_string = "SELECT count(own_r) FROM mycpBundle:generalReservation own_r";
        return $em->createQuery($query_string)
            ->getSingleScalarResult();
    }

    function getReservationsByPagesForNightsCounter($startIndex, $pageSize)
    {
        $em = $this->getEntityManager();
        $query_string = "SELECT own_r FROM mycpBundle:generalReservation own_r";
        return $em->createQuery($query_string)
            ->setFirstResult($startIndex)->setMaxResults($pageSize)->getResult();
    }

    function getReservationsByIdAccommodation($idAccommodation)
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->select("owres")
            ->from("mycpBundle:ownershipReservation", "owres")
            ->join("owres.own_res_gen_res_id", "gres")
            ->where("gres.gen_res_own_id = :idAccommodation")
            ->setParameter("idAccommodation", $idAccommodation);
        return $qb->getQuery()->getResult();
    }

    function getReservationsToExport($filter_date_reserve, $filter_offer_number, $filter_reference, $filter_date_from, $filter_date_to, $sort_by, $filter_booking_number, $filter_status, $date)
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->select("owres")
            ->from("mycpBundle:ownershipReservation", "owres")
            ->join("owres.own_res_gen_res_id", "gres")
            ->join("gres.gen_res_own_id", "own")
            ->join("own.own_address_province", "prov")
            ->join("gres.gen_res_user_id", "user");


        $date = $date->format("Y-m-d");
        $qb->where("gres.gen_res_date >= :filter_date")
            ->setParameter("filter_date", $date);

        if($filter_date_reserve != "" && $filter_date_reserve != "null"){
            $filter_date_reserve = Dates::createForQuery($filter_date_reserve, "d/m/Y");

            $qb->andWhere("gres.gen_res_date >= :filter_date_reserve")
               ->setParameter("filter_date_reserve", $filter_date_reserve);
        }

        if($filter_date_from != "" && $filter_date_from != "null" && $filter_date_to != "" && $filter_date_to != "null") {
            $filter_date_from = Dates::createForQuery($filter_date_from, "d/m/Y");
            $filter_date_to = Dates::createForQuery($filter_date_to, "d/m/Y");

            $qb->andWhere("gres.gen_res_date >= :filter_date_from")
                ->andWhere("gres.gen_res_to_date <= :filter_date_to")
                ->setParameter("filter_date_from", $filter_date_from)
                ->setParameter("filter_date_to", $filter_date_to);
        }
        else if($filter_date_from != "" && $filter_date_from != "null" && ($filter_date_to == "" || $filter_date_to == "null")) {
            $filter_date_from = Dates::createForQuery($filter_date_from, "d/m/Y");

            $qb->andWhere("gres.gen_res_date >= :filter_date_from")
                ->setParameter("filter_date_from", $filter_date_from);
        }
        else if(($filter_date_from == "" || $filter_date_from == "null") && $filter_date_to != "" && $filter_date_to != "null"){
            $filter_date_to = Dates::createForQuery($filter_date_to, "d/m/Y");

            $qb->andWhere("gres.gen_res_to_date <= :filter_date_to")
                ->setParameter("filter_date_to", $filter_date_to);
        }

        if($filter_offer_number != "" && $filter_offer_number != "null"){
            $filter_offer_number = strtolower($filter_offer_number);
            $filter_offer_number = str_replace('cas.', '', $filter_offer_number);
            $filter_offer_number = str_replace('cas', '', $filter_offer_number);
            $filter_offer_number = str_replace('.', '', $filter_offer_number);
            $filter_offer_number = str_replace(' ', '', $filter_offer_number);
            $array_offer_number = explode('-', $filter_offer_number);

            if(count($array_offer_number) > 1) {
                if($array_offer_number[0] < $array_offer_number[1])
                {
                    $qb->andWhere("gres.gen_res_id >= :filter_offer_number1")
                        ->andWhere("gres.gen_res_id <= :filter_offer_number2")
                        ->setParameter("filter_offer_number1", $array_offer_number[0])
                        ->setParameter("filter_offer_number2", $array_offer_number[1]);
                }
                else{
                    $qb->andWhere("gres.gen_res_id >= :filter_offer_number1")
                        ->andWhere("gres.gen_res_id <= :filter_offer_number2")
                        ->setParameter("filter_offer_number1", $array_offer_number[1])
                        ->setParameter("filter_offer_number2", $array_offer_number[0]);
                }
            }
            else if($filter_offer_number != "" and $filter_offer_number != "null"){
                $qb->andWhere("gres.gen_res_id = :filter_offer_number")
                    ->setParameter("filter_offer_number", $filter_offer_number);
            }
        }

        if($filter_booking_number != "" and $filter_booking_number != "null"){
            $filter_booking_number = strtolower($filter_booking_number);

            $qb->andWhere("owres.own_res_reservation_booking = :filter_booking")
                ->setParameter("filter_booking", $filter_booking_number);
        }

        if($filter_reference != "" && $filter_reference != "null"){
            $filter_reference = strtolower($filter_reference);

            $qb->andWhere("own.own_mcp_code LIKE ':filter_mcp_code'")
                ->setParameter("filter_mcp_code", "%".$filter_reference."%");
        }

        if($filter_status != "" && $filter_status != "-1" && $filter_status != "null"){
            $qb->andWhere("gres.gen_res_status = ':filter_status'")
                ->setParameter("filter_status", "%".$filter_status."%");
        }

        switch ($sort_by) {
            case OrderByHelper::DEFAULT_ORDER_BY:
            case OrderByHelper::RESERVATION_NUMBER:
                $qb->orderBy("gres.gen_res_id", "DESC");
                break;
            case OrderByHelper::RESERVATION_DATE:
                $qb->orderBy("gres.gen_res_date", "DESC")
                    ->addOrderBy("gres.gen_res_id", "DESC");
                break;
            case OrderByHelper::RESERVATION_ACCOMMODATION_CODE:
                $qb->orderBy("LENGTH(own.own_mcp_code)", "ASC")
                    ->addOrderBy("own.own_mcp_code", "ASC")
                    ->addOrderBy("gres.gen_res_id", "DESC");
                break;
            case OrderByHelper::RESERVATION_DATE_ARRIVE:
                $qb->orderBy("gres.gen_res_from_date", "DESC")
                    ->addOrderBy("gres.gen_res_id", "DESC");
                break;
            case OrderByHelper::RESERVATION_STATUS:
                $qb->orderBy("gres.gen_res_status", "ASC")
                    ->addOrderBy("gres.gen_res_id", "DESC");
                break;
            case OrderByHelper::RESERVATION_PRICE_TOTAL:
                $qb->orderBy("gres.gen_res_total_in_site", "DESC")
                    ->addOrderBy("gres.gen_res_id", "DESC");
                break;
            case OrderByHelper::RESERVATION_CLIENT:
                $qb->orderBy("gres.gen_res_date", "DESC")
                    ->addOrderBy("u.user_user_name", "ASC")
                    ->addOrderBy("u.user_last_name", "ASC")
                    ->addOrderBy("u.user_email", "ASC")
                    ->addOrderBy("gres.gen_res_id", "DESC");
                   // ->addOrderBy("gres.gen_res_from_date", "DESC");
                break;
        }

        return $qb->getQuery()->getResult();
    }
}
