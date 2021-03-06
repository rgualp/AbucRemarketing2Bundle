<?php

namespace MyCp\mycpBundle\Entity;

use Doctrine\ORM\EntityRepository;
use MyCp\mycpBundle\Entity\ownershipStatus;

/**
 * permissionRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class favoriteRepository extends EntityRepository {

    public function insert($data) {
        $em = $this->getEntityManager();
        $ownership = null;
        $user = null;
        $destination = null;
        if ($data['favorite_ownership_id'] != null)
            $ownership = $em->getRepository('mycpBundle:ownership')->find($data['favorite_ownership_id']);

        if ($data['favorite_user_id'] != null)
            $user = $em->getRepository('mycpBundle:user')->find($data['favorite_user_id']);

        if ($data['favorite_destination_id'] != null)
            $destination = $em->getRepository('mycpBundle:destination')->find($data['favorite_destination_id']);

        $is_ownership = $data['favorite_ownership_id'] != null;
        $element_id = ($is_ownership) ? $data['favorite_ownership_id'] : $data['favorite_destination_id'];
        if (!$this->isInFavorite($element_id, $is_ownership, $data['favorite_user_id'], $data['favorite_session_id'])) {
            $favorite = new favorite();
            $favorite->setFavoriteCreationDate(new \DateTime());
            $favorite->setFavoriteOwnership($ownership);
            $favorite->setFavoriteDestination($destination);
            $favorite->setFavoriteSessionId($data['favorite_session_id']);
            $favorite->setFavoriteUser($user);

            $em->persist($favorite);
            $em->flush();
        }
    }

    public function delete($data) {
        $em = $this->getEntityManager();
        $query_string = "DELETE mycpBundle:favorite f ";
        $where = "";

        if ($data['favorite_ownership_id'] != null)
            $where .= (($where == "") ? " WHERE " : " AND ") . " f.favorite_ownership =" . $data['favorite_ownership_id'];
        else if ($data['favorite_destination_id'] != null)
            $where .= (($where == "") ? " WHERE " : " AND ") . " f.favorite_destination =" . $data['favorite_destination_id'];

        if ($data['favorite_session_id'] != null)
            $where .= (($where == "") ? " WHERE " : " AND ") . " f.favorite_session_id ='" . $data['favorite_session_id'] . "'";
        else if ($data['favorite_user_id'] != null)
            $where .= (($where == "") ? " WHERE " : " AND ") . " f.favorite_user =" . $data['favorite_user_id'];

        $query = $em->createQuery($query_string . $where);
        $query->execute();
    }

    public function getTotal($user_id = null, $session_id = null) {
        try {
            $em = $this->getEntityManager();
            $query_string = "SELECT f FROM mycpBundle:favorite f";

            if ($user_id != null)
                $query_string.= " WHERE f.favorite_user = $user_id";
            else if ($session_id != null)
                $query_string .= " WHERE f.favorite_session_id = '$session_id'";

            return count($em->createQuery($query_string)->getResult());
        } catch (Exception $e) {
            return 0;
        }
    }

    public function isInFavorite($element_id, $is_ownership = true, $user_id = null, $session_id = null) {
        try {
            $em = $this->getEntityManager();
            $query_string = "SELECT f FROM mycpBundle:favorite f";
            $where = "";

            if ($user_id != null)
                $where.= " WHERE f.favorite_user = $user_id";
            else if ($session_id != null)
                $where .= " WHERE f.favorite_session_id = '$session_id'";

            $where .= ($where != "") ? (($is_ownership) ? " AND f.favorite_ownership = $element_id" : " AND f.favorite_destination = $element_id") : "";

            if ($where != "")
                return count($em->createQuery($query_string . $where)->getResult()) > 0;
            else
                return false;
        } catch (Exception $e) {
            return false;
        }
    }

    public function isInFavoriteArray($array_elements, $is_ownership = true, $user_id = null, $session_id = null) {
        if (is_array($array_elements)) {
            $results = array();
            foreach ($array_elements as $element) {
                if ($element != null) {
                    $id = ($is_ownership) ? $element->getOwnId() : $element->getDesId();
                    $results[$id] = $this->isInFavorite($id, $is_ownership, $user_id, $session_id);
                }
            }
            return $results;
        }
        return null;
    }

    public function getList($is_ownership = true, $user_id = null, $session_id = null, $max_results = null, $exclude_id_element = null) {
        try {
            $em = $this->getEntityManager();
            $query_string = "SELECT f FROM mycpBundle:favorite f";
            $where = "";

            if ($user_id != null)
                $where.= " WHERE f.favorite_user = $user_id";
            else if ($session_id != null)
                $where .= " WHERE f.favorite_session_id = '$session_id'";

            $where .= ($where != "") ? (($is_ownership) ? " AND f.favorite_ownership IS NOT NULL " : " AND f.favorite_destination IS NOT NULL ") : "";

            if ($exclude_id_element != null) {
                $where .= ($where != "") ? (($is_ownership) ? " AND f.favorite_ownership != $exclude_id_element " : " AND f.favorite_destination != $exclude_id_element ") : "";
            }

            if ($where != "")
                return ($max_results != null) ? $em->createQuery($query_string . $where)->setMaxResults($max_results)->getResult() : $em->createQuery($query_string . $where)->getResult();
            else
                return false;
        } catch (Exception $e) {
            return null;
        }
    }

    public function getElementIdList($is_ownership = true, $user_id = null, $session_id = null, $max_results = null, $exclude_id_element = null) {
        try {
            $em = $this->getEntityManager();
            $query_string = "SELECT f FROM mycpBundle:favorite f";
            $where = "";

            if ($user_id != null)
                $where.= " WHERE f.favorite_user = $user_id";
            else if ($session_id != null)
                $where .= " WHERE f.favorite_session_id = '$session_id'";

            $where .= ($where != "") ? (($is_ownership) ? " AND f.favorite_ownership IS NOT NULL " : " AND f.favorite_destination IS NOT NULL ") : "";

            if ($exclude_id_element != null) {
                $where .= ($where != "") ? (($is_ownership) ? " AND f.favorite_ownership != $exclude_id_element " : " AND f.favorite_destination != $exclude_id_element ") : "";
            }

            if ($where != "") {
                $favorites = ($max_results != null) ? $em->createQuery($query_string . $where)->setMaxResults($max_results)->getResult() : $em->createQuery($query_string . $where)->getResult();
                $results = "0";
                foreach ($favorites as $favorite) {
                    $results .= "," . (($is_ownership) ? $favorite->getFavoriteOwnership()->getOwnId() : $favorite->getFavoriteDestination()->getDesId());
                }
                return $results;
            }
            else
                return "0";
        } catch (Exception $e) {
            return "0";
        }
    }

    public function setToUser($user, $session_id) {
        $em = $this->getEntityManager();
        $to_set = $em->getRepository("mycpBundle:favorite")->findBy(array('favorite_session_id' => $session_id,
                                                                          'favorite_user' => null));

        foreach($to_set as $favorite)
        {
            if($favorite->getFavoriteOwnership() != null)
                $count = count($em->getRepository('mycpBundle:favorite')->findBy(array('favorite_user' => $user->getUserId(),
                                                                                       'favorite_ownership' => $favorite->getFavoriteOwnership())));
            else if($favorite->getFavoriteDestination())
                $count = count($em->getRepository('mycpBundle:favorite')->findBy(array('favorite_user' => $user->getUserId(),
                                                                                       'favorite_destination' => $favorite->getFavoriteDestination())));

            if($count == 0)
            {
                $favorite->setFavoriteSessionId(null);
                $favorite->setFavoriteUser($user);
                $em->persist($favorite);
            }
            else
                $em->remove($favorite);
        }
        $em->flush();
    }

    public function getFavoriteDestinations($user_id = null, $session_id = null, $max_results = null, $exclude_id_element = null, $locale="ES") {
        $where = "";
        $em = $this->getEntityManager();
        $query_string = "SELECT f, d.des_id as destination_id,
                         d.des_name as destination_name,
                        (SELECT min(p.pho_name) FROM mycpBundle:destinationPhoto dp JOIN dp.des_pho_photo p WHERE dp.des_pho_destination=d.des_id) as photo,
                        (SELECT min(mun1.mun_name) FROM mycpBundle:destinationLocation loc2 JOIN loc2.des_loc_municipality mun1 WHERE loc2.des_loc_destination = d.des_id ) as municipality_name,
                        (SELECT min(prov1.prov_name) FROM mycpBundle:destinationLocation loc3 JOIN loc3.des_loc_province prov1 WHERE loc3.des_loc_destination = d.des_id ) as province_name,
                        (SELECT count(o) FROM mycpBundle:ownership o WHERE o.own_status = ".ownershipStatus::STATUS_ACTIVE." AND o.own_address_municipality = (SELECT min(mun.mun_id) FROM mycpBundle:destinationLocation loc JOIN loc.des_loc_municipality mun WHERE loc.des_loc_destination = d.des_id)
                         AND o.own_address_province = (SELECT min(prov.prov_id) FROM mycpBundle:destinationLocation loc1 JOIN loc1.des_loc_province prov WHERE loc1.des_loc_destination = d.des_id)) as count_ownership,
                        (SELECT MIN(o1.own_minimum_price) FROM mycpBundle:ownership o1 WHERE o1.own_status = ".ownershipStatus::STATUS_ACTIVE." AND o1.own_address_municipality = (SELECT min(mun2.mun_id) FROM mycpBundle:destinationLocation loc4 JOIN loc4.des_loc_municipality mun2 WHERE loc4.des_loc_destination = d.des_id)
                         AND o1.own_address_province = (SELECT min(prov2.prov_id) FROM mycpBundle:destinationLocation loc5 JOIN loc5.des_loc_province prov2 WHERE loc5.des_loc_destination = d.des_id)) as min_price,
                         (SELECT dl.des_lang_brief from mycpBundle:destinationLang dl
                          JOIN dl.des_lang_lang l WHERE dl.des_lang_destination = d.des_id AND l.lang_code = '$locale') as desc_brief,
                          1 as is_in_favorities
                        FROM mycpBundle:favorite f
                        JOIN f.favorite_destination d";

        if ($user_id != null)
            $where.= (($where != "") ? " AND " : " WHERE "). " f.favorite_user = $user_id";
        else if ($session_id != null)
            $where .= (($where != "") ? " AND " : " WHERE "). " f.favorite_session_id = '$session_id'";

        if ($exclude_id_element != null)
            $where.= (($where != "") ? " AND " : " WHERE "). " d.des_id <> $exclude_id_element";

        $results = ($max_results != null) ? $em->createQuery($query_string.$where)->setMaxResults($max_results)->getResult() : $em->createQuery($query_string.$where)->getResult();

        for ($i = 0; $i < count($results); $i++) {
            if ($results[$i]['photo'] == null)
                $results[$i]['photo'] = "no_photo.png";
            else if (!file_exists(realpath("uploads/destinationImages/" . $results[$i]['photo']))) {
                $results[$i]['photo'] = "no_photo.png";
            }
        }
        return $results;
    }

    public function getFavoriteAccommodations($user_id = null, $session_id = null, $max_results = null, $exclude_id_element = null) {
        $where = "";
        $em = $this->getEntityManager();
        $query_string = "SELECT f, o.own_id as own_id,
                         o.own_name as own_name,
                        (SELECT min(p.pho_name) FROM mycpBundle:ownershipPhoto op JOIN op.own_pho_photo p WHERE op.own_pho_own=o.own_id) as photo,
                        prov.prov_name as prov_name,
                        mun.mun_name as mun_name,
                        o.own_comments_total as comments_total,
                        o.own_rating as rating,
                        o.own_category as category,
                        o.own_type as type,
                        o.own_minimum_price as minimum_price,
                        (SELECT count(fav) FROM mycpBundle:favorite fav WHERE " . (($user_id != null) ? " fav.favorite_user = $user_id " : " fav.favorite_user is null") . " AND " . (($session_id != null) ? " fav.favorite_session_id = '$session_id' " : " fav.favorite_session_id is null") . " AND fav.favorite_ownership=o.own_id) as is_in_favorites,
                        (SELECT count(r) FROM mycpBundle:room r WHERE r.room_ownership=o.own_id AND r.room_active = 1) as rooms_count,
                        (SELECT count(res) FROM mycpBundle:ownershipReservation res JOIN res.own_res_gen_res_id gen WHERE gen.gen_res_own_id = o.own_id AND res.own_res_status = ".ownershipReservation::STATUS_RESERVED.") as count_reservations,
                        (SELECT count(com) FROM mycpBundle:comment com WHERE com.com_ownership = o.own_id)  as comments,
                        1 as is_in_favorities
                        FROM mycpBundle:favorite f
                        JOIN f.favorite_ownership o
                        JOIN o.own_address_province prov
                         JOIN o.own_address_municipality mun
                        WHERE o.own_status = ".ownershipStatus::STATUS_ACTIVE;

        if ($user_id != null)
            $where.= " AND f.favorite_user = $user_id";
        else if ($session_id != null)
            $where .= " AND f.favorite_session_id = '$session_id'";

        if ($exclude_id_element != null)
            $where.= " AND o.own_id <> $exclude_id_element";

        $order_by = " ORDER BY o.own_ranking DESC, o.own_rating DESC, o.own_comments_total DESC, o.own_minimum_price DESC";

        $results = ($max_results != null) ? $em->createQuery($query_string.$where)->setMaxResults($max_results)->getResult() : $em->createQuery($query_string.$where.$order_by)->getResult();

        for ($i = 0; $i < count($results); $i++) {
            if ($results[$i]['photo'] == null)
                $results[$i]['photo'] = "no_photo.png";
            else if (!file_exists(realpath("uploads/ownershipImages/" . $results[$i]['photo']))) {
                $results[$i]['photo'] = "no_photo.png";
            }
        }
        return $results;
    }

}
