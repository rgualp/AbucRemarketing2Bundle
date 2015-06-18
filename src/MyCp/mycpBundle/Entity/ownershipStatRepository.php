<?php

namespace MyCp\mycpBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * ownershipStatRepository
 *
 */
class ownershipStatRepository extends EntityRepository
{

    public function getData($nomenclatorStat, $municipality = null)
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder()
             ->select('os')
             ->from("mycpBundle:ownershipStat", "os")
             ->where("os.stat_nomenclator = :nomenclatorId")
             ->setParameter("nomenclatorId", $nomenclatorStat->getNomId());

        if($municipality != null) {
            $qb->andWhere("os.stat_municipality = :municipalityId")
               ->setParameter("municipalityId", $municipality->getMunId());
        }

        return $qb->getQuery()->getResult();
    }

    public function insertOrUpdate($nomenclator, $municipality, $value)
    {
        $em = $this->getEntityManager();
        $stat = $em->getRepository("mycpBundle:ownershipStat")->findOneBy(array("stat_municipality" => $municipality->getMunId(), "stat_nomenclator" => $nomenclator->getNomId()));

        if($stat === null)
            $stat = new ownershipStat();

        $stat->setStatNomenclator($nomenclator);
        $stat->setStatMunicipality($municipality);
        $stat->setStatValue($value);

        $em->persist($stat);
        $em->flush();
    }
}
