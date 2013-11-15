<?php

namespace MyCp\mycpBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * provinceRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class provinceRepository extends EntityRepository
{
    /**
     * Yanet - Inicio
     */
    function getProvincesForAutocomplete($prov_part_name)
    {
        $em = $this->getEntityManager();

        $query = $em->createQuery("SELECT p FROM mycpBundle:province p
        WHERE p.prov_name LIKE '%$prov_part_name%' ORDER BY p.prov_name ASC");
        return $query->getResult();
    }
    
    function getProvinces()
    {
        $em = $this->getEntityManager();

        $query = $em->createQuery("SELECT p FROM mycpBundle:province p
        ORDER BY p.prov_name ASC");
        return $query->getResult();
    }


    /**
     * Yanet - Fin
     */
    
    function get_for_main_menu()
    {
        $em = $this->getEntityManager();
        $query_string = "SELECT DISTINCT p.prov_id, p.prov_name, 
                        (SELECT count(o) FROM mycpBundle:ownership o where o.own_address_province = p.prov_id) as total_owns   
                         FROM mycpBundle:province p
                         ";
        return $em->createQuery($query_string)->getResult();
    }
}
