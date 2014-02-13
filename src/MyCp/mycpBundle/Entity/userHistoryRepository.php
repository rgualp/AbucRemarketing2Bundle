<?php

namespace MyCp\mycpBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * userHistoryRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class userHistoryRepository extends EntityRepository
{
    public function insert($is_ownership, $element_id, $user_ids) {
        $em = $this->getEntityManager();
        //$user_ids = $em->getRepository('mycpBundle:user')->user_ids($controller);
        
        $ownership = null;
        $user = null;
        $destination = null;
        if ($is_ownership)
            $ownership = $em->getRepository('mycpBundle:ownership')->find($element_id);
        else
            $destination = $em->getRepository('mycpBundle:destination')->find($element_id);

        if ($user_ids["user_id"] != null)
            $user = $em->getRepository('mycpBundle:user')->find($user_ids["user_id"]);            

        $array_element = $this->get_from_history($user_ids, $element_id,$is_ownership);
        $element = $array_element[0];
        if($element == null)
        {
            $history = new userHistory();
            $history->setUserHistoryVisitDate(new \DateTime());
            $history->setUserHistoryOwnership($ownership);
            $history->setUserHistoryDestination($destination);
            $history->setUserHistorySessionId($user_ids["session_id"]);
            $history->setUserHistoryUser($user);
            $history->setUserHistoryVisitCount(1);

            $em->persist($history);
            $em->flush();
        }
        else
        {
            $element->setUserHistoryVisitDate(new \DateTime());
            $element->setUserHistoryVisitCount($element->getUserHistoryVisitCount() + 1);
            $em->persist($element);
            $em->flush();
        }
    }
    
     public function get_from_history($user_ids, $element_id, $is_ownership = true) {
        try {
            $em = $this->getEntityManager();
            $query_string = "SELECT h FROM mycpBundle:userHistory h ";
            $where = "";

            if ($user_ids["user_id"] != null)
                $where.= " WHERE h.user_history_user = ".$user_ids['user_id'];
            else if ($user_ids["session_id"] != null)
                $where .= " WHERE h.user_history_session_id = '".$user_ids["session_id"]."'";

            $where .= ($where != "") ? (($is_ownership) ? " AND h.user_history_ownership = $element_id" : " AND h.user_history_destination = $element_id") : "";

            if ($where != "")
                return $em->createQuery($query_string . $where . " ORDER BY h.user_history_visit_date DESC")->getResult();
            else
                return null;
        } catch (Exception $e) {
            return false;
        }
    }
    
    public function get_list($user_ids, $is_ownership = true, $max_results = null, $exclude_id_element = null) {
        try {
            $em = $this->getEntityManager();
            $query_string = "SELECT h FROM mycpBundle:userHistory h ";
            $where = "";

            if ($user_ids["user_id"] != null)
                $where.= " WHERE h.user_history_user = ".$user_ids['user_id'];
            else if ($user_ids["session_id"] != null)
                $where .= " WHERE h.user_history_session_id = '".$user_ids["session_id"]."'";

            $where .= ($where != "") ? (($is_ownership) ? " AND h.user_history_ownership IS NOT NULL " : " AND h.user_history_destination IS NOT NULL ") : "";

            if($exclude_id_element != null)
            {
                $where .= ($where != "") ? (($is_ownership) ? " AND h.user_history_ownership != $exclude_id_element " : " AND h.user_history_destination != $exclude_id_element ") : "";
            }
            
            if ($where != "")
                return ($max_results != null) ? $em->createQuery($query_string . $where . " ORDER BY h.user_history_visit_date DESC ")->setMaxResults($max_results)->getResult() : $em->createQuery($query_string . $where . " ORDER BY h.user_history_visit_date DESC ")->getResult();
            else
                return null;
        } catch (Exception $e) {
            return null;
        }
    }
    
    public function get_list_entity($user_ids, $is_ownership = true, $max_results = null, $exclude_id_element = null) {
        try {
            $em = $this->getEntityManager();
            $query_string = "SELECT h FROM mycpBundle:userHistory h ";
            $where = "";

            if ($user_ids["user_id"] != null)
                $where.= " WHERE h.user_history_user = ".$user_ids['user_id'];
            else if ($user_ids["session_id"] != null)
                $where .= " WHERE h.user_history_session_id = '".$user_ids["session_id"]."'";

            $where .= ($where != "") ? (($is_ownership) ? " AND h.user_history_ownership IS NOT NULL " : " AND h.user_history_destination IS NOT NULL ") : "";
            
            if($exclude_id_element != null)
            {
                $where .= ($where != "") ? (($is_ownership) ? " AND h.user_history_ownership != $exclude_id_element " : " AND h.user_history_destination != $exclude_id_element ") : "";
            }

            if ($where != "")
            {
                $query_results = ($max_results != null) ? $em->createQuery($query_string . $where . " ORDER BY h.user_history_visit_date DESC ")->setMaxResults($max_results)->getResult() : $em->createQuery($query_string . $where . " ORDER BY h.user_history_visit_date DESC ")->getResult();
                $results = array();
                
                foreach ($query_results as $history) {
                    $results[] = ($is_ownership) ? $history->getUserHistoryOwnership() : $history->getUserHistoryDestination();
                }
                
                return $results;
            }
            else
                return null;
        } catch (Exception $e) {
            return null;
        }
    }
    
    public function set_to_user($user_id, $session_id) {
        $em = $this->getEntityManager();
        $query = $em->createQuery("UPDATE mycpBundle:userHistory h
                                   SET h.user_history_user= $user_id,
                                       h.user_history_session_id = NULL
                                   WHERE h.user_history_session_id='$session_id'");
        $query->execute();
    }
    
    public function get_history_destinations($user_id = null, $session_id = null, $max_results = null, $exclude_id_element = null) {
        $where = "";
        $em = $this->getEntityManager();
        $query_string = "SELECT f, d.des_id as destination_id,
                         d.des_name as destination_name,
                        (SELECT min(p.pho_name) FROM mycpBundle:destinationPhoto dp JOIN dp.des_pho_photo p WHERE dp.des_pho_destination=d.des_id) as photo,
                        (SELECT min(mun1.mun_name) FROM mycpBundle:destinationLocation loc2 JOIN loc2.des_loc_municipality mun1 WHERE loc2.des_loc_destination = d.des_id ) as municipality_name,
                        (SELECT min(prov1.prov_name) FROM mycpBundle:destinationLocation loc3 JOIN loc3.des_loc_province prov1 WHERE loc3.des_loc_destination = d.des_id ) as province_name,
                        (SELECT count(o) FROM mycpBundle:ownership o WHERE o.own_status = 1 AND o.own_address_municipality = (SELECT min(mun.mun_id) FROM mycpBundle:destinationLocation loc JOIN loc.des_loc_municipality mun WHERE loc.des_loc_destination = d.des_id)
                         AND o.own_address_province = (SELECT min(prov.prov_id) FROM mycpBundle:destinationLocation loc1 JOIN loc1.des_loc_province prov WHERE loc1.des_loc_destination = d.des_id)) as count_ownership,
                        (SELECT MIN(o1.own_minimum_price) FROM mycpBundle:ownership o1 WHERE o1.own_status = 1 AND o1.own_address_municipality = (SELECT min(mun2.mun_id) FROM mycpBundle:destinationLocation loc4 JOIN loc4.des_loc_municipality mun2 WHERE loc4.des_loc_destination = d.des_id)
                         AND o1.own_address_province = (SELECT min(prov2.prov_id) FROM mycpBundle:destinationLocation loc5 JOIN loc5.des_loc_province prov2 WHERE loc5.des_loc_destination = d.des_id)) as min_price,
                        (SELECT count(fav) FROM mycpBundle:favorite fav WHERE ".(($user_id != null)? " fav.favorite_user = $user_id " : " fav.favorite_user is null")." AND ".(($session_id != null)? " fav.favorite_session_id = '$session_id' " : " fav.favorite_session_id is null"). " AND fav.favorite_destination=d.des_id) as is_in_favorites 
                        FROM mycpBundle:userHistory f 
                        JOIN f.user_history_destination d";

        if ($user_id != null)
            $where.= (($where != "") ? " AND " : " WHERE "). " f.user_history_user = $user_id";
        else if ($session_id != null)
            $where .= (($where != "") ? " AND " : " WHERE "). " f.user_history_session_id = '$session_id'";

        if ($exclude_id_element != null)
            $where.= (($where != "") ? " AND " : " WHERE "). " d.des_id <> $exclude_id_element";

        return ($max_results != null) ? $em->createQuery($query_string.$where)->setMaxResults($max_results)->getResult() : $em->createQuery($query_string.$where)->getResult();
    }
    
    public function get_history_ownerships($user_id = null, $session_id = null, $max_results = null, $exclude_id_element = null) {
        $where = "";
        $em = $this->getEntityManager();
        $query_string = "SELECT f, o.own_id as own_id,
                         o.own_name as own_name,
                        (SELECT min(p.pho_name) FROM mycpBundle:ownershipPhoto op JOIN op.own_pho_photo p WHERE op.own_pho_own=o.own_id) as photo,
                        prov.prov_name as prov_name,
                        o.own_comments_total as comments_total,
                        o.own_rating as rating,
                        o.own_minimum_price as minimum_price,
                        (SELECT count(fav) FROM mycpBundle:favorite fav WHERE ".(($user_id != null)? " fav.favorite_user = $user_id " : " fav.favorite_user is null")." AND ".(($session_id != null)? " fav.favorite_session_id = '$session_id' " : " fav.favorite_session_id is null"). " AND fav.favorite_ownership=o.own_id) as is_in_favorites
                        FROM mycpBundle:userHistory f 
                        JOIN f.user_history_ownership o
                        JOIN o.own_address_province prov
                        WHERE o.own_status = 1 ";

        if ($user_id != null)
            $where.= " AND f.user_history_user = $user_id";
        else if ($session_id != null)
            $where .= " AND f.user_history_session_id = '$session_id'";

        if ($exclude_id_element != null)
            $where.= " AND o.own_id <> $exclude_id_element";

        return ($max_results != null) ? $em->createQuery($query_string.$where)->setMaxResults($max_results)->getResult() : $em->createQuery($query_string.$where)->getResult();
    }
}
