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

    public function getChilds(nomenclatorStat $nomenclator)
    {

    }
}
