<?php

namespace MyCp\mycpBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * nomenclatorStatRepository
 *
 */
class nomenclatorStatRepository extends EntityRepository
{

    public function getRootNomenclators()
    {
        $em=$this->getEntityManager();
        $query_string = "SELECT n FROM mycpBundle:nomenclatorStat n WHERE n.nom_parent IS NULL";
        return $em->createQuery($query_string)->getResult();
    }

    public function getChildsNomenclators()
    {
        $em=$this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->select('sn')
            ->from("mycpBundle:nomenclatorStat", "sn")
            ->join("sn.nom_parent", "np")
            ->orderBy('np.nom_id', 'ASC')
            ->addOrderBy('sn.nom_id', 'ASC')
            ->where('sn.nom_parent IS NOT NULL')
        ->andWhere("(SELECT count(os) FROM mycpBundle:ownershipStat os WHERE os.stat_nomenclator = sn.nom_id) > 0");

        return $qb->getQuery()->getResult();
    }
}
