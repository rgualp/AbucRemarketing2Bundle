<?php

namespace MyCp\PartnerBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * paAgencyPackageRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class paAgencyPackageRepository extends EntityRepository {

    public function getPackagesByAgency($idTravelAgency){
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder()
            ->from("PartnerBundle:paPackage", "pack")
            ->select("pack.id, pack.name, IF(EXISTS(SELECT aPack FROM PartnerBundle:paAgencyPackage aPack WHERE aPack.package = pack.id AND aPack.travelAgency = :idTravelAgency), 1, 0) as hasPackage")
            ->setParameter("idTravelAgency", $idTravelAgency);

        return $qb->getQuery()->execute();
    }
}
