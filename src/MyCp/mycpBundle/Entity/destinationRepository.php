<?php

namespace MyCp\mycpBundle\Entity;

use Doctrine\ORM\EntityRepository;
use MyCp\mycpBundle\Entity\destination;
use MyCp\mycpBundle\Entity\destinationLang;
use MyCp\mycpBundle\Entity\destinationLocation;
use MyCp\frontEndBundle\Helpers\Utils;

/**
 * destinationRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class destinationRepository extends EntityRepository {

    function insert_destination($data) {
        //var_dump($data);
        $active = 0;
        if (isset($data['public']))
            $active = 1;
        $em = $this->getEntityManager();
        $destination = new destination();
        $destination->setDesName($data['name']);
        $destination->setDesOrder(9999);
        $destination->setDesActive($active);
        $destination->setDesGeolocateX($data['geolocate_x']);
        $destination->setDesGeolocateY($data['geolocate_y']);
        $destination->setDesPoblation($data['poblation']);
        $destination->setDesRefPlace($data['ref_place']);
        $destination->setDesCatLocationX($data['cat_location_x']);
        $destination->setDesCatLocationY($data['cat_location_y']);
        $destination->setDesCatLocationProvX($data['cat_location_prov_x']);
        $destination->setDesCatLocationProvY($data['cat_location_prov_y']);

        $municipality = $em->getRepository('mycpBundle:municipality')->find($data['ownership_address_municipality']);
        $province = $em->getRepository('mycpBundle:province')->find($data['ownership_address_province']);
        $destination_location = new destinationLocation();
        $destination_location->setDesLocDestination($destination);
        $destination_location->setDesLocMunicipality($municipality);
        $destination_location->setDesLocProvince($province);
        $em->persist($destination_location);

        $keys = array_keys($data);

        foreach ($keys as $item) {
            if (strpos($item, 'brief') !== false) {

                $id = substr($item, 6, strlen($item));
                $destination_lang = new destinationLang();
                $destination_lang->setDesLangBrief($data['brief_' . $id]);
                $destination_lang->setDesLangDesc($data['desc_' . $id]);
                $repo = $em->getRepository('mycpBundle:lang');
                $lang = $repo->find($id);
                $destination_lang->setDesLangLang($lang);
                $destination_lang->setDesLangDestination($destination);
                $em->persist($destination_lang);
            }

            if (strpos($item, 'category_') !== false) {
                $id_cat = str_replace('category_','',$item);
                $category = $em->getRepository('mycpBundle:destinationCategory')->find($id_cat);
                $destination->getDesCategories()->add($category);
            }


            /* if(strpos($item, 'municipality')!==false)
              {
              $id=substr($item, 13, strlen($item));
              $repo=$em->getRepository('mycpBundle:municipality');
              $municipality=$repo->find($id);
              $destination_municipality=new destinationMunicipality();
              $destination_municipality->setDesMunDestination($destination);
              $destination_municipality->setDesMunMunicipality($municipality);
              $em->persist($destination_municipality);
              } */
        }
        $em->persist($destination);
        $em->flush();
    }

    function edit_destination($data) {
        $id_destination = $data['edit_destination'];
        $active = 0;
        if (isset($data['public']))
            $active = 1;
        $em = $this->getEntityManager();
        $destination = $em->getRepository('mycpBundle:destination')->find($id_destination);
        $destination->setDesName($data['name']);
        //$destination->setDesOrder(9999);
        $destination->setDesActive($active);
        $destination->setDesGeolocateX($data['geolocate_x']);
        $destination->setDesGeolocateY($data['geolocate_y']);
        $destination->setDesPoblation($data['poblation']);
        $destination->setDesRefPlace($data['ref_place']);
        $destination->setDesCatLocationX($data['cat_location_x']);
        $destination->setDesCatLocationY($data['cat_location_y']);
        $destination->setDesCatLocationProvX($data['cat_location_prov_x']);
        $destination->setDesCatLocationProvY($data['cat_location_prov_y']);
        $destination->getDesCategories()->clear();

        $municipality = $em->getRepository('mycpBundle:municipality')->find($data['ownership_address_municipality']);
        $province = $em->getRepository('mycpBundle:province')->find($data['ownership_address_province']);
        $destination_location = $em->getRepository('mycpBundle:destinationLocation')->findBy(array('des_loc_destination' => $id_destination));
        $destination_location[0]->setDesLocProvince($province);
        $destination_location[0]->setDesLocMunicipality($municipality);
        $em->persist($destination_location[0]);

        $query = $em->createQuery("DELETE mycpBundle:destinationlang des WHERE des.des_lang_destination=$id_destination");
        $query->execute();

        $keys = array_keys($data);
        foreach ($keys as $item) {
            if (strpos($item, 'brief') !== false) {

                $id = substr($item, 6, strlen($item));
                $destination_lang = new destinationLang();
                $destination_lang->setDesLangBrief($data['brief_' . $id]);
                $destination_lang->setDesLangDesc($data['desc_' . $id]);
                $repo = $em->getRepository('mycpBundle:lang');
                $lang = $repo->find($id);
                $destination_lang->setDesLangLang($lang);
                $destination_lang->setDesLangDestination($destination);
                $em->persist($destination_lang);
            }

            if (strpos($item, 'category_') !== false) {
                $id_cat = str_replace('category_','',$item);
                $category = $em->getRepository('mycpBundle:destinationCategory')->find($id_cat);
                $destination->getDesCategories()->add($category);
            }
        }
        $em->persist($destination);
        //var_dump($destination); exit();
        $em->flush();
        //exit();
    }

    function delete_destination($id_destination) {
        $em = $this->getEntityManager();
        $query = $em->createQuery("DELETE mycpBundle:destinationlang des WHERE des.des_lang_des_id=$id_destination");
        return $query->getArrayResult();
    }

    function get_all_destinations($filter_name, $filter_active, $filter_province, $filter_municipality, $sort_by) {
        $string = '';
        if ($filter_active != 'null' && $filter_active != '') {
            $string = "AND des.des_active = '$filter_active'";
        }

        $string2 = '';
        if ($filter_province != 'null' && $filter_province != '') {
            $string2 = "AND dl.des_loc_province = '$filter_province'";
        }
        $string3 = '';
        if ($filter_municipality != 'null' && $filter_municipality != '') {
            $string3 = "AND dl.des_loc_municipality = '$filter_municipality'";
        }

        $string4 = '';
        switch ($sort_by) {
            case 0:
                $string4 = "ORDER BY des.des_order ASC";
                break;

            case 1:
                $string4 = "ORDER BY des.des_name ASC";
                break;

            case 2:
                $string4 = "ORDER BY des.des_name DESC";
                break;

            case 3:
                $string4 = "ORDER BY dl.des_loc_province ASC";
                break;

            case 4:
                $string4 = "ORDER BY dl.des_loc_municipality ASC";
                break;
        }

        $em = $this->getEntityManager();

        $query = $em->createQuery("SELECT dl,des FROM mycpBundle:destinationLocation dl
        JOIN dl.des_loc_destination des
        WHERE des.des_name LIKE '%$filter_name%' $string $string2 $string3 $string4
        ");

        return $query->getResult();
    }

//-----------------------------------------------------------------------------

    /**
     * 
     * @param type $locale
     * @return array with all destinations and its inner data...
     * @author Daniel Sampedro <darthdaniel85@gmail.com>
     */
    public function getAllDestinations($locale, $user_id, $session_id,$results_total = null) {
        $em = $this->getEntityManager();
        /*$query_string = "SELECT d, dl, dm, dp FROM mycpBundle:destination d
                         LEFT JOIN d.destinationsLang dl
                         JOIN d.destinationsMunicipality dm
                         LEFT JOIN d.destinationsPhoto dp
                         WHERE d.des_active = 1
                         ORDER BY d.des_order";
    
        return $em->createQuery($query_string)->getResult();*/
         $query_string = "SELECT d, 
                         (SELECT dl.des_lang_brief from mycpBundle:destinationLang dl 
                          JOIN dl.des_lang_lang l WHERE dl.des_lang_destination = d.des_id AND l.lang_code = '$locale'),
                          (SELECT MIN(pho.pho_name) FROM mycpBundle:destinationPhoto dp
                           JOIN dp.des_pho_photo pho
                           WHERE dp.des_pho_destination = d.des_id AND (pho.pho_order =
                           (SELECT MIN(pho2.pho_order) FROM mycpBundle:destinationPhoto dp2
                           JOIN dp2.des_pho_photo pho2 WHERE dp2.des_pho_destination = dp.des_pho_destination ) or pho.pho_order is null)) as photo,
                           (SELECT count(o) FROM mycpBundle:ownership o WHERE o.own_status = 1 AND o.own_address_municipality = (SELECT min(mun.mun_id) FROM mycpBundle:destinationLocation loc JOIN loc.des_loc_municipality mun WHERE loc.des_loc_destination = d.des_id)
                         AND o.own_address_province = (SELECT min(prov.prov_id) FROM mycpBundle:destinationLocation loc1 JOIN loc1.des_loc_province prov WHERE loc1.des_loc_destination = d.des_id)
                         AND (SELECT count(r1) FROM mycpBundle:room r1 WHERE r1.room_ownership = o.own_id) <> 0) as count_ownership,
                           (SELECT count(fav) FROM mycpBundle:favorite fav WHERE ".(($user_id != null)? " fav.favorite_user = $user_id " : " fav.favorite_user is null")." AND ".(($session_id != null)? " fav.favorite_session_id = '$session_id' " : " fav.favorite_session_id is null"). " AND fav.favorite_destination=d.des_id) as is_in_favorites
                         FROM mycpBundle:destination d
                         WHERE d.des_active <> 0
                         ORDER BY d.des_order ASC";
        
        /*$query = $em->createQuery($query_string);
        var_dump(count($query->getResult()));
        exit();*/

     $results = ($results_total != null && $results_total > 0) ? $em->createQuery($query_string)->setMaxResults($results_total)->getResult() : $em->createQuery($query_string)->getResult();
         
     for ($i = 0; $i< count($results); $i++) {
        if($results[$i]['photo']== null)
            $results[$i]['photo'] = "no_photo.png";
        else if(!file_exists(realpath("uploads/destinationImages/".$results[$i]['photo'])))
        {
            $results[$i]['photo'] = "no_photo.png";
        }         
     }
    /* var_dump($results[0]['photo']);
     exit();*/
        return $results;
    }
    
    /**
     * Yanet - Inicio
     */

    /**
     * Returns a set of destinations order by popularity
     * @param int $results_total Length of the return set
     * @return array
     */
    function get_popular_destination($results_total = null, $user_id = null, $session_id = null,$locale='ES') {
        $em = $this->getEntityManager();
       /* $query_string = "SELECT d FROM mycpBundle:destination d
                         WHERE d.des_active <> 0
                         ORDER BY d.des_order ASC";
        return ($results_total != null && $results_total > 0) ? $em->createQuery($query_string)->setMaxResults($results_total)->getResult() : $em->createQuery($query_string)->getResult();*/
        $query_string = "SELECT d.des_id as des_id,
                          d.des_name as des_name,
                          (SELECT dl.des_lang_brief from mycpBundle:destinationLang dl 
                          JOIN dl.des_lang_lang l WHERE dl.des_lang_destination = d.des_id AND l.lang_code = '$locale') as desc_brief,
                          (SELECT MIN(pho.pho_name) FROM mycpBundle:destinationPhoto dp
                           JOIN dp.des_pho_photo pho
                           WHERE dp.des_pho_destination = d.des_id AND (pho.pho_order =
                           (SELECT MIN(pho2.pho_order) FROM mycpBundle:destinationPhoto dp2
                           JOIN dp2.des_pho_photo pho2 WHERE dp2.des_pho_destination = dp.des_pho_destination ) or pho.pho_order is null)) as photo,
                         (SELECT count(fav) FROM mycpBundle:favorite fav WHERE ".(($user_id != null)? " fav.favorite_user = $user_id " : " fav.favorite_user is null")." AND ".(($session_id != null)? " fav.favorite_session_id = '$session_id' " : " fav.favorite_session_id is null"). " AND fav.favorite_destination=d.des_id) as is_in_favorites,
                         (SELECT min(mun1.mun_name) FROM mycpBundle:destinationLocation loc2 JOIN loc2.des_loc_municipality mun1 WHERE loc2.des_loc_destination = d.des_id ) as municipality_name,
                         (SELECT min(prov1.prov_name) FROM mycpBundle:destinationLocation loc3 JOIN loc3.des_loc_province prov1 WHERE loc3.des_loc_destination = d.des_id ) as province_name,
                         (SELECT count(o) FROM mycpBundle:ownership o WHERE o.own_status = 1 AND o.own_address_municipality = (SELECT min(mun.mun_id) FROM mycpBundle:destinationLocation loc JOIN loc.des_loc_municipality mun WHERE loc.des_loc_destination = d.des_id)
                         AND o.own_address_province = (SELECT min(prov.prov_id) FROM mycpBundle:destinationLocation loc1 JOIN loc1.des_loc_province prov WHERE loc1.des_loc_destination = d.des_id)) as count_ownership,
                         (SELECT MIN(o1.own_minimum_price) FROM mycpBundle:ownership o1 WHERE o1.own_status = 1 AND o1.own_address_municipality = (SELECT min(mun2.mun_id) FROM mycpBundle:destinationLocation loc4 JOIN loc4.des_loc_municipality mun2 WHERE loc4.des_loc_destination = d.des_id)
                         AND o1.own_address_province = (SELECT min(prov2.prov_id) FROM mycpBundle:destinationLocation loc5 JOIN loc5.des_loc_province prov2 WHERE loc5.des_loc_destination = d.des_id)) as min_price
                         FROM mycpBundle:destination d
                         WHERE d.des_active <> 0
                         ORDER BY d.des_order ASC";
        
     $results = ($results_total != null && $results_total > 0) ? $em->createQuery($query_string)->setMaxResults($results_total)->getResult() : $em->createQuery($query_string)->getResult();
         
     for ($i = 0; $i< count($results); $i++) {
        if($results[$i]['photo']== null)
            $results[$i]['photo'] = "no_photo.png";
        else if(!file_exists(realpath("uploads/destinationImages/".$results[$i]['photo'])))
        {
            $results[$i]['photo'] = "no_photo.png";
        }         
     }
    /* var_dump($results[0]['photo']);
     exit();*/
        return $results;
    }
    
    public function get_destination($destination_id,$locale) {
        $em = $this->getEntityManager();
         $query_string = "SELECT d, 
                         (SELECT dl.des_lang_brief from mycpBundle:destinationLang dl 
                          JOIN dl.des_lang_lang l WHERE dl.des_lang_destination = d.des_id AND l.lang_code = '$locale') as desc_brief,
                         (SELECT description.des_lang_desc from mycpBundle:destinationLang description 
                          JOIN description.des_lang_lang desc_l WHERE description.des_lang_destination = d.des_id AND desc_l.lang_code = '$locale') as desc_full,
                         (SELECT min(mun) FROM mycpBundle:destinationLocation loc JOIN loc.des_loc_municipality mun WHERE loc.des_loc_destination = d.des_id ) as municipality_id,
                         (SELECT min(prov) FROM mycpBundle:destinationLocation loc1 JOIN loc1.des_loc_province prov WHERE loc1.des_loc_destination = d.des_id ) as province_id,
                         (SELECT min(mun1.mun_name) FROM mycpBundle:destinationLocation loc2 JOIN loc2.des_loc_municipality mun1 WHERE loc2.des_loc_destination = d.des_id ) as municipality_name,
                         (SELECT min(prov1.prov_name) FROM mycpBundle:destinationLocation loc3 JOIN loc3.des_loc_province prov1 WHERE loc3.des_loc_destination = d.des_id ) as province_name
                         FROM mycpBundle:destination d
                         WHERE d.des_id = $destination_id
                         ORDER BY d.des_order ASC";
        
     return $em->createQuery($query_string)->getOneOrNullResult();    
    }
    
    

    /**
     * Returns a set of destination�s descriptions
     * @param array $destination_array - Set of destination to search it's description
     * @param string $lang_code
     * @return array
     */
    function get_destination_description($destination_array, $lang_code) {
        if (is_array($destination_array)) {
            $descriptions_array = array();
            $em = $this->getEntityManager();
            $query_string = "";

            foreach ($destination_array as $destination) {
                $query_string = "SELECT dl FROM mycpBundle:destinationLang dl
                         JOIN dl.des_lang_lang l
                         WHERE l.lang_code='$lang_code'
                           AND dl. des_lang_destination=" . $destination->getDesId();

                $desc = $em->createQuery($query_string)->setMaxResults(1)->getResult();
                $descriptions_array[$destination->getDesId()] = ($desc != null) ? $desc[0] : null;
            }

            return $descriptions_array;
        }
    }

    /**
     * Returns a set of destination's photos
     * @param array $destination_array
     * @return array
     */
    function get_destination_photos($destination_array) {
        if (is_array($destination_array)) {
            $photos_array = array();
            $em = $this->getEntityManager();

            foreach ($destination_array as $destination) {
                $destination_photo = $em->getRepository('mycpBundle:destinationPhoto')->findOneBy(array(
                    'des_pho_destination' => $destination->getDesId()));
                if ($destination_photo != null) {
                    $photo_name = $destination_photo->getDesPhoPhoto()->getPhoName();


                    if (file_exists(realpath("uploads/destinationImages/" . $photo_name))) {
                        $photos_array[$destination->getDesId()] = $photo_name;
                    } else {
                        $photos_array[$destination->getDesId()] = 'no_photo.png';
                    }
                } else {
                    $photos_array[$destination->getDesId()] = 'no_photo.png';
                }
            }

            return $photos_array;
        }
    }

    /**
     * Returns a set of destination's locations
     * @param array $destination_array
     * @return array
     */
    function get_destination_location($destination_array) {
        if (is_array($destination_array)) {
            $location_array = array();
            $em = $this->getEntityManager();

            foreach ($destination_array as $destination) {
                $destination_location = $em->getRepository('mycpBundle:destinationLocation')->findOneBy(array(
                    'des_loc_destination' => $destination->getDesId()));
                if ($destination_location != null) {
                    $province = $destination_location->getDesLocProvince();
                    $municipality = $destination_location->getDesLocMunicipality();

                    $location_array[$destination->getDesId()] = $municipality . (($province != null && $province != '' && $municipality != null && $municipality != '') ? ' / ' : '') . $province;
                }
                else
                    $location_array[$destination->getDesId()] = "";
            }
            return $location_array;
        }
    }
    
    function get_destination_location_entity($destination_array) {
        if (is_array($destination_array)) {
            $location_array = array();
            $em = $this->getEntityManager();

            foreach ($destination_array as $destination) {
                $destination_location = $em->getRepository('mycpBundle:destinationLocation')->findOneBy(array(
                    'des_loc_destination' => $destination->getDesId()));
                    $location_array[$destination->getDesId()] = $destination_location;
            }
            return $location_array;
        }
    }

    function get_destination_by_location($province, $municipality = null) {
        $em = $this->getEntityManager();
        $query_string = "SELECT l FROM mycpBundle:destinationLocation l
                         JOIN l.des_loc_destination d
                         WHERE d.des_active = 1 AND l.des_loc_province = " . $province;
        
        if($municipality != null)
            $query_string.= " AND l.des_loc_municipality = ". $municipality;
        
        $query_string.= " ORDER BY d.des_order";

        return $em->createQuery($query_string)->getResult();        
    }

    function destination_filter($locale,$municipality_id = null, $province_id = null, $exclude_destination_id = null, $exclude_municipality = null, $max_result_set = null, $user_id = null, $session_id = null) {
        $em = $this->getEntityManager();
        $query_string = "SELECT DISTINCT d.des_id as desid,
                         d.des_poblation as desPoblation,
                         d.des_ref_place as desRefPlace,
                         d.des_name as desname,
                         (SELECT min(p.pho_name) FROM mycpBundle:destinationPhoto dp JOIN dp.des_pho_photo p WHERE dp.des_pho_destination=d.des_id) as photo,
                         (SELECT count(o) FROM mycpBundle:ownership o WHERE o.own_status = 1 AND o.own_address_municipality = (SELECT min(mun.mun_id) FROM mycpBundle:destinationLocation loc JOIN loc.des_loc_municipality mun WHERE loc.des_loc_destination = d.des_id)
                         AND o.own_address_province = (SELECT min(prov.prov_id) FROM mycpBundle:destinationLocation loc1 JOIN loc1.des_loc_province prov WHERE loc1.des_loc_destination = d.des_id)) as count_ownership,
                         (SELECT dl.des_lang_brief from mycpBundle:destinationLang dl 
                          JOIN dl.des_lang_lang l WHERE dl.des_lang_destination = d.des_id AND l.lang_code = '$locale') as description,
                         (SELECT count(fav) FROM mycpBundle:favorite fav WHERE ".(($user_id != null)? " fav.favorite_user = $user_id " : " fav.favorite_user is null")." AND ".(($session_id != null)? " fav.favorite_session_id = '$session_id' " : " fav.favorite_session_id is null"). " AND fav.favorite_destination=d.des_id) as is_in_favorites  
                         FROM mycpBundle:destinationLocation loct
                         JOIN loct.des_loc_destination d
                         WHERE d.des_active <> 0";

        if ($municipality_id != null && $municipality_id != -1 && $municipality_id != '')
            $query_string = $query_string . " AND loct.des_loc_municipality =$municipality_id";

        if ($exclude_municipality != null && $exclude_municipality != -1 && $exclude_municipality != '')
            $query_string = $query_string . " AND loct.des_loc_municipality <> $exclude_municipality";

        if ($province_id != null && $province_id != -1 && $province_id != '')
            $query_string = $query_string . " AND loct.des_loc_province =$province_id";

        if ($exclude_destination_id != null && $exclude_destination_id != -1 && $exclude_destination_id != '')
            $query_string = $query_string . " AND loct.des_loc_destination <> $exclude_destination_id";

        $query_string = $query_string . " ORDER BY d.des_order ASC";
        
        $results = ($max_result_set != null && $max_result_set > 0) ? $em->createQuery($query_string)->setMaxResults($max_result_set)->getResult() : $em->createQuery($query_string)->getResult();
        
        for ($i = 0; $i < count($results); $i++) {
            if ($results[$i]['photo'] == null)
                $results[$i]['photo'] = "no_photo.png";
            else if (!file_exists(realpath("uploads/destinationImages/" . $results[$i]['photo']))) {
                $results[$i]['photo'] = "no_photo.png";
            }
        }
        
        return $results;
    }

    function ownsership_nearby_destination($municipality_id = null, $province_id = null, $max_result_set = null, $exclude_own_id = null, $user_id = null, $session_id = null) {
        if ($municipality_id != null || $province_id != null) {
            $em = $this->getEntityManager();
            $query_string = "SELECT DISTINCT o.own_id,
                             o.own_name,
                             prov.prov_name,
                             mun.mun_name,
                             o.own_comments_total as comments_total,
                             o.own_rating as rating,
                             o.own_category as category,
                             o.own_type as type,
                             o.own_minimum_price as minimum_price,
                             (SELECT min(p.pho_name) FROM mycpBundle:ownershipPhoto op JOIN op.own_pho_photo p WHERE op.own_pho_own=o.own_id) as photo,
                             (SELECT count(fav) FROM mycpBundle:favorite fav WHERE ".(($user_id != null)? " fav.favorite_user = $user_id " : " fav.favorite_user is null")." AND ".(($session_id != null)? " fav.favorite_session_id = '$session_id' " : " fav.favorite_session_id is null"). " AND fav.favorite_ownership=o.own_id) as is_in_favorites,
                             (SELECT count(r) FROM mycpBundle:room r WHERE r.room_ownership=o.own_id) as rooms_count,
                        (SELECT count(res) FROm mycpBundle:ownershipReservation res JOIN res.own_res_gen_res_id gen WHERE gen.gen_res_own_id = o.own_id AND res.own_res_status = 5) as count_reservations,
                        (SELECT count(com) FROM mycpBundle:comment com WHERE com.com_ownership = o.own_id)  as comments
                             FROM mycpBundle:room r1
                             JOIN r1.room_ownership o
                             JOIN o.own_address_province prov
                             JOIN o.own_address_municipality mun
                             WHERE o.own_status = 1";

            if ($municipality_id != null && $municipality_id != -1 && $municipality_id != '')
                $query_string = $query_string . " AND o.own_address_municipality =$municipality_id";

            if ($province_id != null && $province_id != -1 && $province_id != '')
                $query_string = $query_string . " AND o.own_address_province =$province_id";
            
            if($exclude_own_id != null && $exclude_own_id != "")
                $query_string = $query_string . " AND o.own_id <>$exclude_own_id";

            $query_string = $query_string . " ORDER BY o.own_rating DESC";
            $results = ($max_result_set != null && $max_result_set > 0) ? $em->createQuery($query_string)->setMaxResults($max_result_set)->getResult() : $em->createQuery($query_string)->getResult();
            
            for ($i = 0; $i < count($results); $i++) {
            if ($results[$i]['photo'] == null)
                $results[$i]['photo'] = "no_photo.png";
            else if (!file_exists(realpath("uploads/ownershipImages/" . $results[$i]['photo']))) {
                $results[$i]['photo'] = "no_photo.png";
            }
        }
        return $results;
        }
        return null;
    }

    function destinations_in_province_name($province_name) {
       
            $em = $this->getEntityManager();
            $query_string = "SELECT DISTINCT d.des_id,
                             d.des_name,
                             d.des_name as des_name_for_url,
                             prov.prov_name,
                             (SELECT min(p.pho_name) FROM mycpBundle:destinationPhoto dp JOIN dp.des_pho_photo p WHERE dp.des_pho_destination=d.des_id) as photo,
                             (SELECT MIN(o1.own_minimum_price) FROM mycpBundle:ownership o1 WHERE o1.own_address_province = prov.prov_id) as min_price
                             FROM mycpBundle:destinationLocation dloc
                             JOIN dloc.des_loc_province prov
                             JOIN dloc.des_loc_destination d
                             WHERE prov.prov_name LIKE '%$province_name%'";

            
            //$query_string = $query_string . " ORDER BY o.own_rating DESC";
            
            $results = $em->createQuery($query_string)->getResult();
            
            for ($i = 0; $i < count($results); $i++) {
            if ($results[$i]['photo'] == null)
                $results[$i]['photo'] = "no_photo.png";
            else if (!file_exists(realpath("uploads/ownershipImages/" . $results[$i]['photo']))) {
                $results[$i]['photo'] = "no_photo.png";
            }
            
            $results[$i]['des_name_for_url'] = Utils::url_normalize($results[$i]['des_name_for_url']);
        }
        return $results;
    }
    
    function get_destination_owns_statistics($destination_array) {
        if (is_array($destination_array)) {
            $statistics_array = array();
            $em = $this->getEntityManager();

            foreach ($destination_array as $destination) {
                $destination_location = $em->getRepository('mycpBundle:destinationLocation')->findOneBy(array(
                    'des_loc_destination' => $destination->getDesId()));
                if ($destination_location != null) {
                    $province = $destination_location->getDesLocProvince();
                    $municipality = $destination_location->getDesLocMunicipality();
                    $query_string = "SELECT count(o),MIN(o.own_minimum_price) FROM mycpBundle:ownership o
                         WHERE o.own_status = 1";
                    if ($municipality != null)
                        $query_string = $query_string . " AND o.own_address_municipality =" . $municipality->getMunId();

                    if ($province != null)
                        $query_string = $query_string . " AND o.own_address_province =" . $province->getProvId();
                    $results = $em->createQuery($query_string)->getResult();
                    $statistics_array[$destination->getDesId()] = array(
                        'total' => intval($results[0][1]),
                        'minimum_prize' => floatval($results[0][2])
                    );
                }
            }
            return $statistics_array;
        }
    }

    function getPhotos($destination_id, $location) {

        $photos_array = array();
        $photos_array['photo_name'] = array();
        $photos_array['photo_description'] = array();
        $em = $this->getEntityManager();
        
        $query_string = "SELECT dp,p.pho_name as photo,
                        (SELECT MIN(pl.pho_lang_description) from mycpBundle:photoLang pl JOIN pl.pho_lang_id_lang l
                         WHERE l.lang_code='$location'
                           AND pl.pho_lang_id_photo=dp.des_pho_photo) as description
                        FROM mycpBundle:destinationPhoto dp
                        JOIN dp.des_pho_photo p
                         WHERE dp.des_pho_destination=" . $destination_id;
        
        $destination_photos = $em->createQuery($query_string)->getResult();

        foreach ($destination_photos as $destination_photo) {

            if ($destination_photo['photo'] != null) {
                if (file_exists(realpath("uploads/destinationImages/" . $destination_photo['photo']))) {
                    $photos_array['photo_name'][] = $destination_photo['photo'];
                    $photos_array['photo_description'][] = $destination_photo['description'];
                } 
            }
        }

        return $photos_array;
    }
    
    function get_list_by_ids($des_ids) {
        $em = $this->getEntityManager();
        $query_string = "SELECT o FROM mycpBundle:destination o WHERE o.des_id IN ($des_ids)";
        return $em->createQuery($query_string)->getResult();
    }

    /**
     * Yanet - Fin
     */
    
    function get_for_main_menu()
    {
        $em = $this->getEntityManager();
        $query_string = "SELECT DISTINCT d.des_id, d.des_name,                         
                         (SELECT min(mun) FROM mycpBundle:destinationLocation loc JOIN loc.des_loc_municipality mun WHERE loc.des_loc_destination = d.des_id ) as municipality_id,
                         (SELECT min(prov) FROM mycpBundle:destinationLocation loc1 JOIN loc1.des_loc_province prov WHERE loc1.des_loc_destination = d.des_id ) as province_id,
                         (SELECT count(o) FROM mycpBundle:ownership o where o.own_address_municipality = municipality_id AND o.own_address_province = province_id AND (SELECT count(r1) FROM mycpBundle:room r1 WHERE r1.room_ownership = o.own_id) <> 0 as total_owns   
                         FROM mycpBundle:destination d
                         ORDER BY total_owns DESC, d.des_name ASC";
        return $em->createQuery($query_string)->getResult();
    }
    
}
