<?php

namespace MyCp\mycpBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * overrideUserRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class overrideUserRepository extends EntityRepository {

    /**
     * @return array
     */
    public function getAll() {
        $em = $this->getEntityManager();
        $query = $em->createQuery("SELECT u FROM mycpBundle:overrideuser u");
        return $query->getResult();
    }
}
