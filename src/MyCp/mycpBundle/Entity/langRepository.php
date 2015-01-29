<?php

namespace MyCp\mycpBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * langRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class langRepository extends EntityRepository
{

    function getAll()
    {
        $em = $this->getEntityManager();
        $query = $em->createQuery('SELECT l FROM mycpBundle:lang l');
        $query->useResultCache(TRUE);
        return $query->getArrayResult();
    }
    function getActive()
    {
        $em = $this->getEntityManager();
        $query_string = "SELECT l FROM mycpBundle:lang l
                         WHERE l.lang_active = 1
                         ORDER BY l.lang_name";

        return $em->createQuery($query_string)->getResult();
    }
}
