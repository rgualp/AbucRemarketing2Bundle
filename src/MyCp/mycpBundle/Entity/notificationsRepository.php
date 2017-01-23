<?php

namespace MyCp\mycpBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\ORM\QueryBuilder;
use MyCp\mycpBundle\Entity\ownership;
use MyCp\mycpBundle\Entity\ownershipDescriptionLang;
use MyCp\mycpBundle\Entity\ownershipGeneralLang;
use MyCp\mycpBundle\Entity\ownershipKeywordLang;
use MyCp\mycpBundle\Entity\room;
use MyCp\mycpBundle\Entity\userCasa;
use MyCp\mycpBundle\Helpers\OrderByHelper;
use MyCp\mycpBundle\Helpers\OwnershipStatuses;
use MyCp\mycpBundle\Helpers\SyncStatuses;
use MyCp\mycpBundle\Entity\ownershipStatus;
use MyCp\mycpBundle\Helpers\Dates;
use MyCp\mycpBundle\Helpers\SearchUtils;
use MyCp\mycpBundle\Helpers\FilterHelper;
use MyCp\mycpBundle\Service\TranslatorResponseStatusCode;


class notificationsRepository extends EntityRepository {
    function getActives($idOwnership, $filters) {
        $filters['ownership'] = $idOwnership;
        $filters['actionResponse'] = '';

        if(!isset($filters['subtype']) || $filters['subtype'] == ''){
            unset($filters['subtype']);
        }

        return $this->findBy($filters, array('created' => 'DESC'));
    }

    function getInactives($idOwnership, $filters) {
        $qb = $this->createQueryBuilder('n');
        $qb->where('n.ownership = :ownership')->andWhere('n.actionResponse != :actionResponse')
            ->setParameter('ownership', $idOwnership)
            ->setParameter('actionResponse', '');

        if(isset($filters['subtype']) && $filters['subtype'] != ''){
            $qb->andWhere('n.subtype = :subtype')->setParameter('subtype', $filters['subtype']);
        }

        return $qb->getQuery()->getResult();
    }
}
