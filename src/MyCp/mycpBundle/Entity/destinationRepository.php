<?php

namespace MyCp\mycpBundle\Entity;

use Doctrine\ORM\EntityRepository;
use MyCp\mycpBundle\Entity\destination;
use MyCp\mycpBundle\Entity\destinationLang;
use MyCp\mycpBundle\Entity\destinationLocation;

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

        $municipality = $em->getRepository('mycpBundle:municipality')->find($data['ownership_address_municipality']);
        $province = $em->getRepository('mycpBundle:province')->find($data['ownership_address_province']);
        $destination_location = new destinationLocation();
        $destination_location->setDesLocDestination($destination);
        $destination_location->setDesLocMunicipality($municipality);
        $destination_location->setDesLocProvince($province);
        $em->persist($destination_location);
        $em->persist($destination);
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

        $municipality = $em->getRepository('mycpBundle:municipality')->find($data['ownership_address_municipality']);
        $province = $em->getRepository('mycpBundle:province')->find($data['ownership_address_province']);
        $destination_location = $em->getRepository('mycpBundle:destinationLocation')->findBy(array('des_loc_destination' => $id_destination));
        $destination_location[0]->setDesLocProvince($province);
        $destination_location[0]->setDesLocMunicipality($municipality);
        $em->persist($destination_location[0]);
        $em->persist($destination);

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
        }


        $em->flush();
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
    public function getAllDestinations($locale) {
        $em = $this->getEntityManager();
        $query_string = "SELECT d, dl, dm, dp FROM mycpBundle:destination d
                         JOIN d.destinationsLang dl
                         JOIN d.destinationsMunicipality dm
                         LEFT JOIN d.destinationsPhoto dp
                         WHERE d.des_active = 1
                         ORDER BY d.des_order";
    
        return $em->createQuery($query_string)->getResult();
    }
    
    /**
     * Yanet - Inicio
     */

    /**
     * Returns a set of destinations order by popularity
     * @param int $results_total Length of the return set
     * @return array
     */
    function get_popular_destination($results_total = null) {
        $em = $this->getEntityManager();
        $query_string = "SELECT d FROM mycpBundle:destination d
                         WHERE d.des_active <> 0
                         ORDER BY d.des_order ASC";
        return ($results_total != null && $results_total > 0) ? $em->createQuery($query_string)->setMaxResults($results_total)->getResult() : $em->createQuery($query_string)->getResult();
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

    /**
     * Este lo agregue
     */
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

    function destination_filter($municipality_id = null, $province_id = null, $exclude_destination_id = null, $exclude_municipality = null, $max_result_set = null) {
        $em = $this->getEntityManager();
        $query_string = "SELECT l FROM mycpBundle:destinationLocation l
                         JOIN l.des_loc_destination d
                         WHERE d.des_active <> 0";

        if ($municipality_id != null && $municipality_id != -1 && $municipality_id != '')
            $query_string = $query_string . " AND l.des_loc_municipality =$municipality_id";

        if ($exclude_municipality != null && $exclude_municipality != -1 && $exclude_municipality != '')
            $query_string = $query_string . " AND l.des_loc_municipality <> $exclude_municipality";

        if ($province_id != null && $province_id != -1 && $province_id != '')
            $query_string = $query_string . " AND l.des_loc_province =$province_id";

        if ($exclude_destination_id != null && $exclude_destination_id != -1 && $exclude_destination_id != '')
            $query_string = $query_string . " AND l.des_loc_destination <> $exclude_destination_id";

        $query_string = $query_string . " ORDER BY d.des_order ASC";
        
        $result = ($max_result_set != null && $max_result_set > 0) ? $em->createQuery($query_string)->setMaxResults($max_result_set)->getResult() : $em->createQuery($query_string)->getResult();

        $destinations = array();

        foreach ($result as $destLocation)
            $destinations[] = $destLocation->getDesLocDestination();

        return $destinations;
    }

    function ownsership_nearby_destination($municipality_id = null, $province_id = null, $max_result_set = null) {
        if ($municipality_id != null || $province_id != null) {
            $em = $this->getEntityManager();
            $query_string = "SELECT o FROM mycpBundle:ownership o
                         WHERE o.own_status = 1";

            if ($municipality_id != null && $municipality_id != -1 && $municipality_id != '')
                $query_string = $query_string . " AND o.own_address_municipality =$municipality_id";

            if ($province_id != null && $province_id != -1 && $province_id != '')
                $query_string = $query_string . " AND o.own_address_province =$province_id";

            $query_string = $query_string . " ORDER BY o.own_rating DESC";
            return ($max_result_set != null && $max_result_set > 0) ? $em->createQuery($query_string)->setMaxResults($max_result_set)->getResult() : $em->createQuery($query_string)->getResult();
        }
        return null;
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

    function getPhotos($destination_id) {

        $photos_array = array();
        $em = $this->getEntityManager();
        $destination_photos = $em->getRepository('mycpBundle:destinationPhoto')->findBy(array(
            'des_pho_destination' => $destination_id));

        foreach ($destination_photos as $destination_photo) {

            if ($destination_photo != null) {
                $photo_name = $destination_photo->getDesPhoPhoto()->getPhoName();

                if (file_exists(realpath("uploads/destinationImages/" . $photo_name))) {
                    $photos_array[$destination_photo->getDesPhoDestination()->getDesId()] = $photo_name;
                } else {
                    $photos_array[$destination_photo->getDesPhoDestination()->getDesId()] = 'no_photo.png';
                }
            } else {
                $photos_array[$destination_photo->getDesPhoDestination()->getDesId()] = 'no_photo.png';
            }
        }

        return $photos_array;
    }

    function getPhotoDescription($photos, $lang_code) {
        if (is_array($photos)) {
            $em = $this->getEntityManager();
            $query_string = "";

            $descriptions = array();

            foreach ($photos as $photo) {
                if(is_object($photo)){
                $query_string = "SELECT p FROM mycpBundle:photoLang p
                         JOIN p.pho_lang_id_lang l
                         WHERE l.lang_code='$lang_code'
                           AND p.pho_lang_id_photo=" . $photo->getPhoId();
                $descriptions[$photo->getPhoId()] = $em->createQuery($query_string)->setMaxResults(1)->getResult();
                }
            }

            return $descriptions;
        }
    }
    
    function get_list_by_ids($des_ids) {
        $em = $this->getEntityManager();
        $query_string = "SELECT o FROM mycpBundle:destination o WHERE o.des_id IN ($des_ids)";
        return $em->createQuery($query_string)->getResult();
    }

    /**
     * Yanet - Fin
     */
}
