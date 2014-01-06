<?php

namespace MyCp\mycpBundle\Entity;

use Doctrine\ORM\EntityRepository;
use MyCp\mycpBundle\Helpers\SyncStatuses;

/**
 * ud_sync_st
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class unavailabilityDetailsRepository extends EntityRepository {

    public function getNotSynchronized() {
        $em = $this->getEntityManager();
        $query_string = "SELECT o
                        FROM mycpBundle:unavailabilityDetails o
                        WHERE o.ud_sync_st<>" . SyncStatuses::SYNC;

        return $em->createQuery($query_string)->getResult();
    }

}
