<?php

namespace MyCp\mycpBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * pendingPayownRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class pendingPayownRepository extends EntityRepository {

    function findAllByFilters()
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder()
            ->from("mycpBundle:pendingPayown", "op");
        return $qb->getQuery();

    }
}
