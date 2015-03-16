<?php

namespace MyCp\mycpBundle\Entity;

use Doctrine\ORM\EntityRepository;
use MyCp\mycpBundle\Helpers\SyncStatuses;
use MyCp\mycpBundle\Helpers\ReservationSortField;
use MyCp\mycpBundle\Helpers\OrderByHelper;

/**
 * ownershipReservationRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class generalReservationRepository extends EntityRepository {

    function getAll($filter_date_reserve, $filter_offer_number, $filter_reference, $filter_date_from, $filter_date_to, $sort_by, $filter_booking_number, $filter_status) {
        $gaQuery = "SELECT gre,
        (SELECT count(owres) FROM mycpBundle:ownershipReservation owres WHERE owres.own_res_gen_res_id = gre.gen_res_id),
        (SELECT SUM(owres2.own_res_count_adults) FROM mycpBundle:ownershipReservation owres2 WHERE owres2.own_res_gen_res_id = gre.gen_res_id),
        (SELECT SUM(owres3.own_res_count_childrens) FROM mycpBundle:ownershipReservation owres3 WHERE owres3.own_res_gen_res_id = gre.gen_res_id),
        (SELECT MIN(owres4.own_res_reservation_from_date) FROM mycpBundle:ownershipReservation owres4 WHERE owres4.own_res_gen_res_id = gre.gen_res_id),
        us, cou,own FROM mycpBundle:generalReservation gre
        JOIN gre.gen_res_own_id own
        JOIN gre.gen_res_user_id us
        JOIN us.user_country cou
        WHERE gre.gen_res_date LIKE :filter_date_reserve
        AND gre.gen_res_from_date LIKE :filter_date_from
        AND gre.gen_res_id LIKE :filter_offer_number
        AND own.own_mcp_code LIKE :filter_reference
        AND gre.gen_res_to_date LIKE :filter_date_to ";

        if($filter_status != "" && $filter_status != "-1" && $filter_status != "null")
            $gaQuery .= "AND gre.gen_res_status = :filter_status ";

        return $this->getByQuery($filter_date_reserve, $filter_offer_number, $filter_reference, $filter_date_from, $filter_date_to, $sort_by, $filter_booking_number, $filter_status, -1, $gaQuery);
    }

    function getByUserCasa($filter_date_reserve, $filter_offer_number, $filter_reference, $filter_date_from, $filter_date_to, $sort_by, $filter_booking_number, $user_casa_id, $filter_status) {
        $gaQuery = "SELECT gre,
        (SELECT count(owres) FROM mycpBundle:ownershipReservation owres WHERE owres.own_res_gen_res_id = gre.gen_res_id),
        (SELECT SUM(owres2.own_res_count_adults) FROM mycpBundle:ownershipReservation owres2 WHERE owres2.own_res_gen_res_id = gre.gen_res_id),
        (SELECT SUM(owres3.own_res_count_childrens) FROM mycpBundle:ownershipReservation owres3 WHERE owres3.own_res_gen_res_id = gre.gen_res_id),
        us, cou,own FROM mycpBundle:generalReservation gre
        JOIN gre.gen_res_own_id own
        JOIN gre.gen_res_user_id us
        JOIN us.user_country cou
        JOIN mycpBundle:userCasa uca with uca.user_casa_ownership = own.own_id
        WHERE gre.gen_res_date LIKE :filter_date_reserve
        AND gre.gen_res_from_date LIKE :filter_date_from
        AND gre.gen_res_id LIKE :filter_offer_number
        AND own.own_mcp_code LIKE :filter_reference
        AND gre.gen_res_to_date LIKE :filter_date_to
        AND uca.user_casa_id = :user_casa_id ";

        if($filter_status != "" && $filter_status != "-1" && $filter_status != "null")
            $gaQuery .= "AND gre.gen_res_status = :filter_status ";

        return $this->getByQuery($filter_date_reserve, $filter_offer_number, $filter_reference, $filter_date_from, $filter_date_to, $sort_by, $filter_booking_number, $filter_status, $user_casa_id, $gaQuery);
    }

    function getByQuery($filter_date_reserve, $filter_offer_number, $filter_reference, $filter_date_from, $filter_date_to, $sort_by, $filter_booking_number, $filter_status, $user_casa_id, $queryStr) {
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
            case ReservationSortField::RESERVATION_DEFAULT:
            case ReservationSortField::RESERVATION_NUMBER:
                $string_order = "ORDER BY gre.gen_res_id DESC";
                break;
            case ReservationSortField::RESERVATION_DATE:
                $string_order = "ORDER BY gre.gen_res_date ASC";
                break;
            case ReservationSortField::RESERVATION_ACCOMMODATION_CODE:
                $string_order = "ORDER BY own.own_mcp_code ASC";
                break;
            case ReservationSortField::RESERVATION_DATE_ARRIVE:
                $string_order = "ORDER BY gre.gen_res_from_date ASC";
                break;
            case ReservationSortField::RESERVATION_STATUS:
                $string_order = "ORDER BY gre.gen_res_status ASC";
                break;
            case ReservationSortField::RESERVATION_PRICE_TOTAL:
                $string_order = "ORDER BY gre.gen_res_total_in_site DESC";
                break;
        }
        $em = $this->getEntityManager();
        $queryStr = $queryStr . $string_order;
        $query = $em->createQuery($queryStr);

        if ($user_casa_id == -1) {
            $query->setParameters(array(
                'filter_date_reserve' => "%" . $filter_date_reserve . "%",
                'filter_date_from' => "%" . $filter_date_from . "%",
                'filter_offer_number' => "%" . $filter_offer_number . "%",
                'filter_reference' => "%" . $filter_reference . "%",
                'filter_date_to' => "%" . $filter_date_to . "%",
            ));
        } else {
            $query->setParameters(array(
                'filter_date_reserve' => "%" . $filter_date_reserve . "%",
                'filter_date_from' => "%" . $filter_date_from . "%",
                'filter_offer_number' => "%" . $filter_offer_number . "%",
                'filter_reference' => "%" . $filter_reference . "%",
                'filter_date_to' => "%" . $filter_date_to . "%",
                'user_casa_id' => $user_casa_id,
            ));
        }

        if($filter_status != "" && $filter_status != "-1" && $filter_status != "null")
            $query->setParameter ('filter_status',$filter_status);


        $array_genres = $query->getArrayResult();

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
            (SELECT count(g) FROM mycpBundle:generalReservation g WHERE g.gen_res_user_id = us.user_id $countReservations) as total_reserves
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

    function getAllBookings($filter_booking_number, $filter_date_booking, $filter_user_booking, $filter_arrive_date_booking) {
        $em = $this->getEntityManager();

        $filter_date_booking_array = explode('_', $filter_date_booking);
        if (count($filter_date_booking_array) > 1) {
            $filter_date_booking = $filter_date_booking_array[2] . '-' . $filter_date_booking_array[1] . '-' . $filter_date_booking_array[0];
        }

        $filter_arrive_date_booking_array = explode('_', $filter_arrive_date_booking);
        if (count($filter_arrive_date_booking_array) > 1) {
            $filter_arrive_date_booking = $filter_arrive_date_booking_array[2] . '-' . $filter_arrive_date_booking_array[1] . '-' . $filter_arrive_date_booking_array[0];
        }

        $where_arrival = "";

        if($filter_arrive_date_booking != "")
            $where_arrival = "AND (SELECT min(ow1.own_res_reservation_from_date) FROM mycpBundle:ownershipReservation ow1 WHERE ow1.own_res_reservation_booking = booking.booking_id) = '$filter_arrive_date_booking'";

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
        $where_arrival
        ORDER BY payment.id DESC");
        return $query->setParameters(array(
                            'filter_booking_number' => "%" . $filter_booking_number . "%",
                            'filter_user_booking' => "%" . $filter_user_booking . "%",
                            'filter_date_booking' => "%" . $filter_date_booking . "%",
                        ))
                        ->getArrayResult();
    }

    function getByUser($id_user, $ownId = null) {
        $em = $this->getEntityManager();

        $whereOwn = "";

        if ($ownId != null) {
            $whereOwn = " AND ow.own_id= $ownId ";
        }

        $queryString = "SELECT gre,
            (SELECT count(owres) FROM mycpBundle:ownershipReservation owres WHERE owres.own_res_gen_res_id = gre.gen_res_id) AS rooms,
            (SELECT SUM(owres2.own_res_count_adults) FROM mycpBundle:ownershipReservation owres2 WHERE owres2.own_res_gen_res_id = gre.gen_res_id) AS adults,
            (SELECT SUM(owres3.own_res_count_childrens) FROM mycpBundle:ownershipReservation owres3 WHERE owres3.own_res_gen_res_id = gre.gen_res_id) AS childrens,
            ow
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

    public function setAsNotAvailable($reservations_ids) {
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

            foreach ($ownReservations as $item) {

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

            $em->persist($generalReservation);
            $em->flush();
        }
    }

    function getCheckins($checkinDate, $orderBy = OrderByHelper::CHECKIN_ORDER_BY_ACCOMMODATION_CODE) {
        $queryStr = "SELECT gre,
        (SELECT count(owres) FROM mycpBundle:ownershipReservation owres WHERE owres.own_res_gen_res_id = gre.gen_res_id),
        (SELECT SUM(owres1.own_res_count_adults) FROM mycpBundle:ownershipReservation owres1 WHERE owres1.own_res_gen_res_id = gre.gen_res_id),
        (SELECT SUM(owres2.own_res_count_childrens) FROM mycpBundle:ownershipReservation owres2 WHERE owres2.own_res_gen_res_id = gre.gen_res_id),
        us, cou,own,prov,
        (SELECT MIN(p.created) FROM mycpBundle:payment p JOIN p.booking b WHERE b.booking_id = (SELECT MIN(owres3.own_res_reservation_booking) FROM mycpBundle:ownershipReservation owres3 WHERE owres3.own_res_gen_res_id = gre.gen_res_id))
        FROM mycpBundle:generalReservation gre
        JOIN gre.gen_res_own_id own
        JOIN gre.gen_res_user_id us
        JOIN us.user_country cou
        JOIN own.own_address_province prov
        WHERE gre.gen_res_from_date LIKE :filter_date_from
        AND gre.gen_res_status =" . generalReservation::STATUS_RESERVED;

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
        ));

        return $query->getArrayResult();
    }

}
