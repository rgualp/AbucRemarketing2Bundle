<?php

namespace MyCp\mycpBundle\Entity;

use Doctrine\ORM\EntityRepository;
use MyCp\mycpBundle\Entity\ownershipStatus;

/**
 * municipalityRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class municipalityRepository extends EntityRepository
{
    /**
     * Yanet - Inicio
     */
    function get_municipalities_for_autocomplete($part_name)
    {
        $em = $this->getEntityManager();

        $query = $em->createQuery("SELECT m FROM mycpBundle:municipality m
        WHERE m.mun_name LIKE '%$part_name%' ORDER BY m.mun_name ASC");
        return $query->getResult();
    }

    function get_municipalities()
    {
        $em = $this->getEntityManager();

        $query = $em->createQuery("SELECT m FROM mycpBundle:municipality m
        ORDER BY m.mun_name ASC");
        return $query->getResult();
    }

    function getAll()
    {
        $em = $this->getEntityManager();

        $query = $em->createQuery("SELECT m.mun_id, m.mun_name,p.prov_name,
            (select count(o.own_id) FROM mycpBundle:ownership o WHERE o.own_address_municipality = m.mun_id) as accommodations,
            (select count(distinct dl.des_loc_destination) FROM mycpBundle:destinationLocation dl WHERE dl.des_loc_municipality = m.mun_id) as destinations
            FROM mycpBundle:municipality m
            JOIN m.mun_prov_id p
            ORDER BY m.mun_name ASC");
        return $query->getResult();
    }
    
    function getAccommodations($id_municipality)
    {
        $em = $this->getEntityManager();

        $query = $em->createQuery("SELECT o FROM mycpBundle:ownership o 
                                   WHERE o.own_address_municipality = $id_municipality 
                                   ORDER BY o.own_mcp_code ASC");
        return $query->getResult();
    }
    
    function getDestinations($id_municipality)
    {
        $em = $this->getEntityManager();

        $query = $em->createQuery("SELECT d.des_id, d.des_name FROM mycpBundle:destinationLocation dl
                                   JOIN dl.des_loc_destination d
                                   WHERE dl.des_loc_municipality = $id_municipality 
                                   ORDER BY d.des_name ASC");
        return $query->getResult();
    }

    function get_with_reservations()
    {
        $em = $this->getEntityManager();

        $query_string = "SELECT m.mun_id,
                         m.mun_name,
                         prov.prov_id,
                         (SELECT count(gen_r) FROM mycpBundle:generalReservation gen_r  JOIN gen_r.gen_res_own_id o WHERE o.own_address_municipality = m.mun_id AND gen_r.gen_res_status = ".generalReservation::STATUS_RESERVED.") as visits,
                         (SELECT count(o1) FROM mycpBundle:room r JOIN r.room_ownership o1 WHERE r.room_active = 1 AND o1.own_status = ".ownershipStatus::STATUS_ACTIVE." AND o1.own_address_municipality = m.mun_id ORDER BY o1.own_rating DESC, o1.own_id ASC) as total_ownerships,
                         (SELECT DISTINCT count(d.des_id)
                                FROM mycpBundle:destinationLocation l
                                JOIN l.des_loc_destination d
                                WHERE d.des_active = 1 AND l.des_loc_municipality = m.mun_id) as total_destinations,
                          (SELECT MIN(pho.pho_name) FROM mycpBundle:destinationPhoto dp
                           JOIN dp.des_pho_photo pho
                           JOIN dp.des_pho_destination dest
                           WHERE dest.des_id = (SELECT min(d1.des_id) FROM mycpBundle:destinationLocation l1 JOIN l1.des_loc_destination d1 WHERE d1.des_active = 1 AND l1.des_loc_municipality = m.mun_id)
                           AND dp.des_pho_destination = dest.des_id AND (pho.pho_order =
                           (SELECT MIN(pho2.pho_order) FROM mycpBundle:destinationPhoto dp2
                           JOIN dp2.des_pho_photo pho2 WHERE dp2.des_pho_destination = dp.des_pho_destination ) or pho.pho_order is null)) as photo
                         FROM mycpBundle:municipality m
                         JOIN m.mun_prov_id prov
                         WHERE (SELECT count(gen_r1) FROM mycpBundle:generalReservation gen_r1  JOIN gen_r1.gen_res_own_id o2 WHERE o2.own_address_municipality = m.mun_id AND gen_r1.gen_res_status = ".generalReservation::STATUS_RESERVED.") > 0
                         ORDER BY visits DESC,m.mun_name ASC";

        $result = $em->createQuery($query_string)->getResult();
        $results = array();

        for ($i = 0; $i < count($result); $i++) {
            if ($result[$i]['photo'] == null)
                $result[$i]['photo'] = "no_photo.png";
            else if (!file_exists(realpath("uploads/destinationImages/" . $result[$i]['photo']))) {
                $result[$i]['photo'] = "no_photo.png";
            }

            $ownerships_list = "SELECT o.own_id as ownid,
                                o.own_name as ownname
                                FROM mycpBundle:ownership o
                                WHERE o.own_status = ".ownershipStatus::STATUS_ACTIVE."
                                AND o.own_address_municipality = ".$result[$i]['mun_id'].
                                " ORDER BY o.own_rating DESC, o.own_id ASC";

            $destinatios_list = "SELECT d.des_id as desid,
                                d.des_name as desname
                                FROM mycpBundle:destinationLocation l
                                JOIN l.des_loc_destination d
                                WHERE d.des_active = 1 AND l.des_loc_municipality = " . $result[$i]['mun_id'];

            $results[] = array('mun_id' => $result[$i]['mun_id'],
                               'municipality' => $result[$i]['mun_name'],
                               'prov_id' => $result[$i]['prov_id'],
                               'reservation_total' => $result[$i]['visits'],
                               'owns_total' => $result[$i]['total_ownerships'],
                               'photo' => $result[$i]['photo'],
                               'owns_list' => $em->createQuery($ownerships_list)->setMaxResults(5)->getResult(),
                               'des_total' => $result[$i]['total_destinations'],
                               'des_list' => $em->createQuery($destinatios_list)->setMaxResults(5)->getResult());
        }
        return $results;
    }


    /**
     * Yanet - Fin
     */

}
