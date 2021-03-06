<?php

namespace MyCp\mycpBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * countryRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class countryRepository extends EntityRepository {

    function findAllByAlphabetical()
    {
        $em = $this->getEntityManager();
        $query = $em->createQuery("
        SELECT co FROM mycpBundle:country co
        ORDER BY co.co_name ASC");
        return $query->getResult();
    }

}
