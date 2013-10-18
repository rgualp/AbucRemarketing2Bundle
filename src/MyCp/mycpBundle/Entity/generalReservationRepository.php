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
class generalReservationRepository extends EntityRepository
{
    function get_all_reservations($filter_date_reserve, $filter_offer_number, $filter_reference, $filter_date_from,$filter_date_to,$sort_by)
    {
        $filter_offer_number=strtolower($filter_offer_number);
        $filter_offer_number=str_replace('cas.','',$filter_offer_number);
        $filter_offer_number=str_replace('cas','',$filter_offer_number);
        $filter_offer_number=str_replace('.','',$filter_offer_number);
        $array_date_reserve=explode('/',$filter_date_reserve);
        $array_date_from=explode('/',$filter_date_from);
        $array_date_to=explode('/',$filter_date_to);
        if(count($array_date_reserve)>1)
            $filter_date_reserve=$array_date_reserve[2].'-'.$array_date_reserve[1].'-'.$array_date_reserve[0];
        if(count($array_date_from)>1)
            $filter_date_from=$array_date_from[2].'-'.$array_date_from[1].'-'.$array_date_from[0];
        if(count($array_date_to)>1)
            $filter_date_to=$array_date_to[2].'-'.$array_date_to[1].'-'.$array_date_to[0];

        $string_order='';
        switch($sort_by){
            case 0:
                $string_order="ORDER BY gre.gen_res_id DESC";
                break;
            case 1:
                $string_order="ORDER BY gre.gen_res_date ASC";
                break;
            case 2:
                $string_order="ORDER BY gre.gen_res_own_id ASC";
                break;
            case 3:
                $string_order="ORDER BY gre.gen_res_from_date ASC";
                break;
            case 4:
                $string_order="ORDER BY gre.gen_res_status ASC";
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
        WHERE gre.gen_res_date LIKE '%$filter_date_reserve%'
        AND gre.gen_res_from_date LIKE '%$filter_date_from%'
        AND gre.gen_res_id LIKE '%$filter_offer_number%'
        AND own.own_mcp_code LIKE '%$filter_reference%'
        AND gre.gen_res_to_date LIKE '%$filter_date_to%' $string_order");
        return $query->getArrayResult();
    }

    function get_reservations_by_user()
    {
        $em = $this->getEntityManager();
        $query = $em->createQuery("SELECT gre,(SELECT count(owres) FROM mycpBundle:ownershipReservation owres WHERE owres.own_res_gen_res_id = gre.gen_res_id) AS rooms,(SELECT SUM(owres2.own_res_count_adults) FROM mycpBundle:ownershipReservation owres2 WHERE owres2.own_res_gen_res_id = gre.gen_res_id) AS adults,(SELECT SUM(owres3.own_res_count_childrens) FROM mycpBundle:ownershipReservation owres3 WHERE owres3.own_res_gen_res_id = gre.gen_res_id) AS childrens,ow FROM mycpBundle:generalReservation gre
        JOIN gre.gen_res_own_id ow ORDER BY gre.gen_res_id DESC");
        return $query->getArrayResult();
    }

    function find_by_user_and_status($id_user, $status_string, $string_sql)
    {
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

    function get_reservation_available_by_user($id_reservation ,$id_user)
    {
        $em = $this->getEntityManager();
        $query = $em->createQuery("SELECT gre FROM mycpBundle:generalReservation gre
        WHERE gre.gen_res_id = $id_reservation AND gre.gen_res_user_id = $id_user");
        return $query->getResult();
    }

    function get_reminder_available()
    {
        $yesterday = date("Y-m-d",strtotime('yesterday'));
        $em = $this->getEntityManager();
        $query = $em->createQuery("SELECT gre FROM mycpBundle:generalReservation gre
        WHERE gre.gen_res_status = 1 AND gre.gen_res_status_date = '$yesterday'");
        return $query->getResult();
    }


}
