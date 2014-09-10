<?php

namespace MyCp\mycpBundle\Entity;

use Doctrine\ORM\EntityRepository;
use MyCp\mycpBundle\Entity\season;

/**
 * seasonRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class seasonRepository extends EntityRepository {

    public function getAll()
    {
        $em = $this->getEntityManager();

        $query = $em->createQuery("SELECT s
            FROM mycpBundle:season s
            ORDER BY s.season_startdate ASC");
        return $query->getResult();
    }
}
