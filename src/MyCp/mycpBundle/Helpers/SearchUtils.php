<?php

/**
 * Utils methods for search
 *
 * @author Yanet Morales
 */

namespace MyCp\mycpBundle\Helpers;

use MyCp\mycpBundle\Entity\ownershipReservation;


class SearchUtils {

    public static function createDatesWhere($entity_manager, $arrivalDate = null, $leavingDate = null) {
        $where = "0";
        $reservations = SearchUtils::getWithReservations($entity_manager, $arrivalDate, $leavingDate);
        foreach ($reservations as $res)
                $where .= "," . $res["own_id"];

        $uDetails = SearchUtils::getWithUnavailabilityDetails($entity_manager, $arrivalDate, $leavingDate);
        foreach ($uDetails as $detail)
                $where .= "," . $detail["own_id"];
        return $where;
    }

    private static function getWithReservations($entity_manager, $arrivalDate = null, $leavingDate = null)
    {
        $reservations = array();
        if ($arrivalDate != null || $leavingDate != null) {

            $dates_where = "";
            $dates_where_count = "";

            if ($arrivalDate != null) {
                $dates_where .= ($dates_where != '') ? " OR " : "";
                $dates_where .= "(owr.own_res_reservation_from_date <= :arrival_date AND owr.own_res_reservation_to_date >= :arrival_date)";

                $dates_where_count .= ($dates_where_count != '') ? " OR " : "";
                $dates_where_count .= "(owr1.own_res_reservation_from_date <= :arrival_date AND owr1.own_res_reservation_to_date >= :arrival_date)";
            }

            if ($leavingDate != null) {
                $dates_where .= ($dates_where != '') ? " OR " : "";
                $dates_where .= "(owr.own_res_reservation_from_date <= :leaving_date AND owr.own_res_reservation_to_date >= :leaving_date)";

                $dates_where_count .= ($dates_where_count != '') ? " OR " : "";
                $dates_where_count .= "(owr1.own_res_reservation_from_date <= :leaving_date AND owr1.own_res_reservation_to_date >= :leaving_date)";
            }

            if ($arrivalDate != null && $leavingDate != null) {
                $dates_where .= ($dates_where != '') ? " OR " : "";
                $dates_where .= "(owr.own_res_reservation_from_date >= :arrival_date AND owr.own_res_reservation_to_date <= :leaving_date)";

                $dates_where_count .= ($dates_where_count != '') ? " OR " : "";
                $dates_where_count .= "(owr1.own_res_reservation_from_date >= :arrival_date AND owr1.own_res_reservation_to_date <= :leaving_date)";
            }

            $query_string = "SELECT DISTINCT o.own_id FROM mycpBundle:ownershipReservation owr
                                JOIN owr.own_res_gen_res_id r
                                JOIN r.gen_res_own_id o
                                WHERE owr.own_res_status = " . ownershipReservation::STATUS_RESERVED .
                    " AND (SELECT count(owr1) FROM mycpBundle:ownershipReservation owr1
                                   JOIN owr1.own_res_gen_res_id r1 WHERE r1.gen_res_own_id = o.own_id
                                   AND owr1.own_res_status = " . ownershipReservation::STATUS_RESERVED;
            $query_string .= ($dates_where_count != '') ? " AND ($dates_where_count)" : "";
            $query_string .= ") >= o.own_rooms_total";
            $query_string .= ($dates_where != '') ? " AND ($dates_where)" : "";

            $query_reservation = $entity_manager->createQuery($query_string);

            if ($arrivalDate != null) {
                $arrival = \DateTime::createFromFormat('d-m-Y', $arrivalDate);
                if($arrival == null)
                    $arrival = \DateTime::createFromFormat('Y-m-d', $arrivalDate);
                $query_reservation->setParameter('arrival_date', $arrival->format("Y-m-d"));
            }

            if ($leavingDate != null)
            {
                $departure = \DateTime::createFromFormat('d-m-Y', $leavingDate);
                if($departure == null)
                    $departure = \DateTime::createFromFormat('Y-m-d', $leavingDate);
                $query_reservation->setParameter('leaving_date', $departure->format("Y-m-d"));
            }

            $reservations = $query_reservation->getResult();

        }
        return $reservations;
    }

    private static function getWithUnavailabilityDetails($entity_manager, $arrivalDate = null, $leavingDate = null)
    {
        $uDetails = array();
        if ($arrivalDate != null || $leavingDate != null) {

            $dates_where = "";
            $dates_where_count = "";

            if ($arrivalDate != null) {
                $dates_where .= ($dates_where != '') ? " OR " : "";
                $dates_where .= "(ud.ud_from_date <= :arrival_date AND ud.ud_to_date >= :arrival_date)";

                $dates_where_count .= ($dates_where_count != '') ? " OR " : "";
                $dates_where_count .= "(ud1.ud_from_date <= :arrival_date AND ud1.ud_to_date >= :arrival_date)";
            }

            if ($leavingDate != null) {
                $dates_where .= ($dates_where != '') ? " OR " : "";
                $dates_where .= "(ud.ud_from_date <= :leaving_date AND ud.ud_to_date >= :leaving_date)";

                $dates_where_count .= ($dates_where_count != '') ? " OR " : "";
                $dates_where_count .= "(ud1.ud_from_date <= :leaving_date AND ud1.ud_to_date >= :leaving_date)";
            }

            if ($arrivalDate != null && $leavingDate != null) {
                $dates_where .= ($dates_where != '') ? " OR " : "";
                $dates_where .= "(ud.ud_from_date >= :arrival_date AND ud.ud_to_date <= :leaving_date)";

                $dates_where_count .= ($dates_where_count != '') ? " OR " : "";
                $dates_where_count .= "(ud1.ud_from_date >= :arrival_date AND ud1.ud_to_date <= :leaving_date)";
            }

            $query_string = "SELECT DISTINCT ow.own_id from mycpBundle:unavailabilityDetails ud
                             JOIN ud.room r
                             JOIN r.room_ownership ow
                             WHERE (SELECT count(ud1) FROM mycpBundle:unavailabilityDetails ud1
                                   JOIN ud1.room r1 WHERE r1.room_ownership = ow.own_id ";

            $query_string .= ($dates_where_count != '') ? " AND ($dates_where_count)" : "";
            $query_string .= ") >= ow.own_rooms_total";
            $query_string .= ($dates_where != '') ? " AND ($dates_where)" : "";

            $query_details = $entity_manager->createQuery($query_string);

            if ($arrivalDate != null) {
                $arrival = \DateTime::createFromFormat('d-m-Y', $arrivalDate);
                if($arrival == null)
                    $arrival = \DateTime::createFromFormat('Y-m-d', $arrivalDate);
                $query_details->setParameter('arrival_date', $arrival->format("Y-m-d"));
            }

            if ($leavingDate != null)
            {
                $departure = \DateTime::createFromFormat('d-m-Y', $leavingDate);
                if($departure == null)
                    $departure = \DateTime::createFromFormat('Y-m-d', $leavingDate);
                $query_details->setParameter('leaving_date', $departure->format("Y-m-d"));
            }

            $uDetails = $query_details->getResult();

        }
        return $uDetails;
    }

    public static function getBasicQuery($room_filter, $user_id, $session_id) {
        $query_string = "";
        if (!$room_filter) {
            $query_string = "SELECT DISTINCT o.own_id as own_id,
                             o.own_name as own_name,
                            (SELECT min(p.pho_name) FROM mycpBundle:ownershipPhoto op JOIN op.own_pho_photo p WHERE op.own_pho_own=o.own_id
                            AND (p.pho_order = (select min(p1.pho_order) from  mycpBundle:ownershipPhoto op1 JOIN op1.own_pho_photo p1
                            where op1.own_pho_own = o.own_id) or p.pho_order is null) as photo,
                            prov.prov_name as prov_name,
                            mun.mun_name as mun_name,
                            o.own_comments_total as comments_total,
                            o.own_rating as rating,
                            o.own_category as category,
                            o.own_type as type,
                            o.own_minimum_price as minimum_price,
                            (SELECT count(fav) FROM mycpBundle:favorite fav WHERE " . (($user_id != null) ? " fav.favorite_user = :user_id " : " fav.favorite_user is null") . " AND " . (($session_id != null) ? " fav.favorite_session_id = :session_id " : " fav.favorite_session_id is null") . " AND fav.favorite_ownership=o.own_id) as is_in_favorites,
                            (SELECT count(room) FROM mycpBundle:room room WHERE room.room_ownership=o.own_id AND room.room_active = 1) as rooms_count,
                            (SELECT count(res) FROM mycpBundle:ownershipReservation res JOIN res.own_res_gen_res_id gen WHERE gen.gen_res_own_id = o.own_id AND res.own_res_status = " . ownershipReservation::STATUS_RESERVED . ") as count_reservations,
                            (SELECT count(com) FROM mycpBundle:comment com WHERE com.com_ownership = o.own_id)  as comments,
                            o.own_facilities_breakfast as breakfast,
                            o.own_facilities_dinner as dinner,
                            o.own_facilities_parking as parking,
                            o.own_water_piscina as pool,
                            o.own_description_laundry as laundry,
                            o.own_description_internet as internet,
                            o.own_water_sauna as sauna,
                            o.own_description_pets as pets,
                            o.own_water_jacuzee as jacuzee,
                            o.own_langs as langs
                             FROM mycpBundle:ownership o
                             JOIN o.own_address_province prov
                             JOIN o.own_address_municipality mun
                             WHERE o.own_status = 1 ";
        } else {
            $query_string = "SELECT DISTINCT o.own_id as own_id,
                             o.own_name as own_name,
                            (SELECT min(p.pho_name) FROM mycpBundle:ownershipPhoto op JOIN op.own_pho_photo p WHERE op.own_pho_own=o.own_id
                            AND (p.pho_order = (select min(p1.pho_order) from  mycpBundle:ownershipPhoto op1 JOIN op1.own_pho_photo p1
                            where op1.own_pho_own = o.own_id) or p.pho_order is null) as photo,
                            prov.prov_name as prov_name,
                            mun.mun_name as mun_name,
                            o.own_comments_total as comments_total,
                            o.own_rating as rating,
                            o.own_category as category,
                            o.own_type as type,
                            o.own_minimum_price as minimum_price,
                            (SELECT count(fav) FROM mycpBundle:favorite fav WHERE " . (($user_id != null) ? " fav.favorite_user = :user_id " : " fav.favorite_user is null") . " AND " . (($session_id != null) ? " fav.favorite_session_id = :session_id " : " fav.favorite_session_id is null") . " AND fav.favorite_ownership=o.own_id) as is_in_favorites,
                            (SELECT count(room) FROM mycpBundle:room room WHERE room.room_ownership=o.own_id AND room.room_active = 1) as rooms_count,
                            (SELECT count(res) FROm mycpBundle:ownershipReservation res JOIN res.own_res_gen_res_id gen WHERE gen.gen_res_own_id = o.own_id AND res.own_res_status = " . ownershipReservation::STATUS_RESERVED . ") as count_reservations,
                            (SELECT count(com) FROM mycpBundle:comment com WHERE com.com_ownership = o.own_id)  as comments ,
                            o.own_facilities_breakfast as breakfast,
                            o.own_facilities_dinner as dinner,
                            o.own_facilities_parking as parking,
                            o.own_water_piscina as pool,
                            o.own_description_laundry as laundry,
                            o.own_description_internet as internet,
                            o.own_water_sauna as sauna,
                            o.own_description_pets as pets,
                            o.own_water_jacuzee as jacuzee,
                            o.own_langs as langs
                             FROM mycpBundle:room r
                             JOIN r.room_ownership o
                             JOIN o.own_address_province prov
                             JOIN o.own_address_municipality mun
                             WHERE o.own_status = 1
                               AND r.room_active = 1 ";
        }

        return $query_string;
    }

    public static function getTextWhere($text) {
        if ($text != null && $text != '' && $text != 'null')
            return "(prov.prov_name LIKE :text OR o.own_name LIKE :text OR o.own_mcp_code LIKE :text OR mun.mun_name LIKE :text)";
    }


public static function getFilterWhere($filters) {
        $where = "";
        if ($filters != null && is_array($filters)) {

            if (key_exists('own_beds_total', $filters) && $filters['own_beds_total'] != null && is_array($filters['own_beds_total']) && count($filters['own_beds_total']) > 0)
            {
                $insideWhere = SearchUtils::getPlusFilterString($filters['own_beds_total'], "r.room_beds", 6);
                if($insideWhere != "")
                    $where .= " AND (" . $insideWhere . ")";
            }

            if (key_exists('own_category', $filters) && $filters['own_category'] != null && is_array($filters['own_category']) && count($filters['own_category']) > 0)
            {
                $insideWhere = SearchUtils::getStringFromArray($filters['own_category']);
                if($insideWhere != "")
                    $where .= " AND o.own_category IN (" . $insideWhere . ")";
            }

            if (key_exists('own_type', $filters) && $filters['own_type'] != null && is_array($filters['own_type']) && count($filters['own_type']) > 0)
            {
                $insideWhere = SearchUtils::getStringFromArray($filters['own_type']);

                if($insideWhere != "")
                    $where .= " AND o.own_type IN (" . $insideWhere . ")";
            }

            if (key_exists('own_price_from', $filters) && $filters['own_price_from'] != null && is_array($filters['own_price_from']) && count($filters['own_price_from']) > 0 && $filters['own_price_to'] != null && is_array($filters['own_price_to']) && count($filters['own_price_to']) > 0) {
                $prices_where = "";

                for ($i = 0; $i < count($filters['own_price_from']); $i++) {
                    $prices_where .= ($prices_where != '' ? " AND " : "") . "(o.own_minimum_price >=" . $filters['own_price_from'][$i] . " AND o.own_minimum_price <=" . $filters['own_price_to'][$i] . ")";
                }

                $where = $where . (($prices_where != "") ? " AND ($prices_where)" : "");
            }


            if (key_exists('room_type', $filters) && $filters['room_type'] != null && is_array($filters['room_type']) && count($filters['room_type']) > 0)
            {
                $insideWhere = SearchUtils::getStringFromArray($filters['room_type']);

                if($insideWhere != "")
                    $where .= " AND r.room_type IN (" . $insideWhere . ")";
            }

            if (key_exists('room_climatization', $filters) && $filters['room_climatization'] != null && $filters['room_climatization'] != 'null' && $filters['room_climatization'] != '')
            {
                $where .= " AND r.room_climate LIKE '%" . $filters['room_climatization'] . "%'";
            }

            if (key_exists('room_safe', $filters) && $filters['room_safe'])
                $where .= " AND r.room_safe = 1";

            if (key_exists('room_audiovisuals', $filters) && $filters['room_audiovisuals'])
                $where .= " AND (r.room_audiovisual <>'' OR r.room_audiovisual IS NOT NULL)";

            if (key_exists('room_kids', $filters) && $filters['room_kids'])
                $where .= " AND r.room_baby = 1";

            if (key_exists('room_smoker', $filters) && $filters['room_smoker'])
                $where .= " AND r.room_smoker = 1";

            if (key_exists('room_windows_total', $filters) && $filters['room_windows_total'] != null && is_array($filters['room_windows_total']) && count($filters['room_windows_total']) > 0)
            {
                $insideWhere = SearchUtils::getPlusFilterString($filters['room_windows_total'], "r.room_windows", 6);

                if($insideWhere != "")
                    $where .= " AND (" . $insideWhere . ")";
            }

            if (key_exists('room_balcony', $filters) && $filters['room_balcony'])
                $where .= " AND r.room_balcony = 1";

            if (key_exists('room_terraza', $filters) && $filters['room_terraza'])
                $where .= " AND r.room_terrace = 1";

            if (key_exists('room_courtyard', $filters) && $filters['room_courtyard'])
                $where .= " AND r.room_yard = 1";

            if (key_exists('room_bathroom', $filters) && $filters['room_bathroom'] != null && is_array($filters['room_bathroom']) && count($filters['room_bathroom']) > 0)
            {
                $insideWhere = SearchUtils::getStringFromArray($filters['room_bathroom']);

                if($insideWhere != "")
                    $where .= " AND r.room_bathroom IN (" . $insideWhere . ")";
            }

            if (key_exists('own_others_pets', $filters) && $filters['own_others_pets'])
                $where .= " AND o.own_description_pets = 1";

            if (key_exists('own_others_internet', $filters) && $filters['own_others_internet'])
                $where .= " AND o.own_description_internet = 1";

            if (key_exists('own_others_languages', $filters) && $filters['own_others_languages'] != null && is_array($filters['own_others_languages']) && count($filters['own_others_languages']) > 0) {
                $lang_where = "";

                for ($i = 0; $i < count($filters['own_others_languages']); $i++) {
                    $lang_where .= ($lang_where != '' ? " AND " : "") . "o.own_langs LIKE '" . $filters['own_others_languages'][$i] . "'";
                }

                $where = $where . (($lang_where != "") ? " AND ($lang_where)" : "");
            }

            if (key_exists('own_others_included', $filters) && $filters['own_others_included'] != null && is_array($filters['own_others_included']) && count($filters['own_others_included']) > 0)
                $where .= SearchUtils::getServicesIncludedFilterWhere($filters['own_others_included']);

            if (key_exists('own_others_not_included', $filters) && $filters['own_others_not_included'] != null && is_array($filters['own_others_not_included']) && count($filters['own_others_not_included']) > 0)
                $where .= SearchUtils::getServicesNotIncludedFilterWhere($filters['own_others_not_included']);

            if (key_exists('own_rooms_number', $filters) && $filters['own_rooms_number'] != null && is_array($filters['own_rooms_number']) && count($filters['own_rooms_number']) > 0)
            {
                $insideWhere =  SearchUtils::getPlusFilterString($filters['own_rooms_number'], "o.own_rooms_total", 6);

                if($insideWhere != "")
                    $where.= " AND (" .$insideWhere. ")";
            }
        }
        return $where;
    }

    public static function getServicesIncludedFilterWhere($filter) {
        $where = "";
        for ($i = 0; $i < count($filter); $i++) {
            switch ($filter[$i]) {
                case 'JACUZZY':
                    $where .= " AND o.own_water_jacuzee = 1";
                    break;
                case 'SAUNA':
                    $where .= " AND o.own_water_sauna = 1";
                    break;
                case 'POOL':
                    $where .= " AND o.own_water_piscina = 1";
                    break;
            }
        }
        return $where;
    }

    public static function getServicesNotIncludedFilterWhere($filter) {
        $where = "";
        for ($i = 0; $i < count($filter); $i++) {
            switch ($filter[$i]) {
                case 'BREAKFAST':
                    $where .= " AND o.own_facilities_breakfast = 1";
                    break;
                case 'DINNER':
                    $where .= " AND o.own_facilities_dinner = 1";
                    break;
                case 'LAUNDRY':
                    $where .= " AND o.own_description_laundry = 1";
                    break;
                case 'PARKING':
                    $where .= " AND o.own_facilities_parking = 1";
                    break;
            }
        }
        return $where;
    }

    public static function getPlusFilterString($filter, $field_string, $plus_value) {
        $where = "";
        $plus_value_string = "+" . $plus_value;
        if (in_array($plus_value_string, $filter)) {
            $where = "$field_string >= $plus_value";
        }
        if (count($filter) > 0) {
            $in_where = SearchUtils::getStringFromArray($filter, false, $plus_value_string);

            if ($in_where != "0" && $in_where != "")
                $where .= (($where != "") ? " OR " : "") . "$field_string IN (" . $in_where . ")";
        }
        //var_dump("Filtro " . $where);
        return $where;
    }

    public static function getOrder($order_by) {
        switch($order_by)
        {
            case OrderByHelper::DEFAULT_ORDER_BY:
            case OrderByHelper::SEARCHER_BEST_VALUED:
                return "  ORDER BY o.own_ranking DESC, o.own_comments_total DESC, count_reservations DESC ";
            case OrderByHelper::SEARCHER_A_Z:
                return "  ORDER BY o.own_name ASC, o.own_ranking DESC, o.own_comments_total DESC, count_reservations DESC ";
            case OrderByHelper::SEARCHER_Z_A:
                return "  ORDER BY o.own_name DESC, o.own_ranking DESC, o.own_comments_total DESC, count_reservations DESC ";
            case OrderByHelper::SEARCHER_PRICE_HIGH_LOW:
                return "  ORDER BY o.own_minimum_price DESC, o.own_ranking DESC, o.own_comments_total DESC, count_reservations DESC ";
            case OrderByHelper::SEARCHER_PRICE_LOW_HIGH:
                return "  ORDER BY o.own_minimum_price ASC, o.own_ranking DESC, o.own_comments_total DESC, count_reservations DESC";
            case OrderByHelper::SEARCHER_RESERVATIONS_HIGH_LOW:
                return "  ORDER BY count_reservations DESC, o.own_ranking DESC, o.own_comments_total DESC ";
            case OrderByHelper::SEARCHER_RESERVATIONS_LOW_HIGH:
                return "  ORDER BY count_reservations ASC, o.own_ranking DESC, o.own_comments_total DESC ";
            case OrderByHelper::SEARCHER_WORST_VALUED:
                return "  ORDER BY o.own_ranking ASC, o.own_comments_total ASC, count_reservations DESC ";


        }
        
    }

    private static function getStringFromArray($array, $has_string_items = true, $element_to_remove = null) {
        if (is_array($array)) {
            $quotas_element = (($has_string_items) ? "'" : "");
            $string_value = "";

            foreach ($array as $item) {
                if (($element_to_remove != null && $item != $element_to_remove) || $element_to_remove == null)
                    $string_value .= (($string_value != "") ? "," : "") . $quotas_element . $item . $quotas_element;
            }
            return $string_value;
        }
        return null;
    }

}

?>
