<?php

namespace MyCp\mycpBundle\Entity;

use Doctrine\ORM\EntityRepository;
use MyCp\mycpBundle\Helpers\SyncStatuses;


class roomRepository extends EntityRepository {


    public function getNotSynchronized() {
        $em = $this->getEntityManager();
        $query_string = "SELECT o
                        FROM mycpBundle:room o
                        WHERE o.room_sync_st<>" . "'" . SyncStatuses::SYNC . "'";

        return $em->createQuery($query_string)->getResult();
    }

    public function setAllSync() {
        $em = $this->getEntityManager();
        foreach ($this->getHousesToOfflineApp() as $_house) {
            $_house->setOwnSync(true);
            $em->persist($_house);
        }
        $em->flush();
    }

}