<?php

namespace MyCp\mycpBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * pendingPaytouristRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class pendingPaytouristRepository extends EntityRepository {

    function findAllByFilters()
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder()
            ->from("mycpBundle:pendingPaytourist", "op");
       return $qb->getQuery();

    }
}
