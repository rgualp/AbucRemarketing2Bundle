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
    function getNotifications($idOwnership, $filters, $active = true) {
        if(!isset($filters)){
            $filters = array();
        }

        $filters['ownership'] = $idOwnership;

        if($active){
            //$filters['actionResponse'] = '';
            $filters['status'] = notification::STATUS_NEW;
        }
        else{
            $filters['status'] = notification::STATUS_READ;
            //$filters['actionResponse'] = array('prop'=>'actionResponse', 'key'=>'actionResponse', 'operator'=>'!=', 'value'=>'');
        }


        if(!isset($filters['subtype']) || $filters['subtype'] == ''){
            unset($filters['subtype']);
        }

        if(isset($filters['date_from']) && $filters['date_from'] != '' && isset($filters['date_to']) && $filters['date_to'] != ''){
            $date_from =  \DateTime::createFromFormat('d/m/Y H:i:s', $filters['date_from'].'00:00:00');
            $date_to =  \DateTime::createFromFormat('d/m/Y H:i:s', $filters['date_to'].'00:00:00');

            if($filters['date_from'] == $filters['date_to']){
                $date_to->modify('+1 day');
            }

            $filters['created_date_from'] = array('prop'=>'created', 'key'=>'created_date_from', 'operator'=>'>=', 'value'=>$date_from->format('Y-m-d'));
            $filters['created_date_to'] = array('prop'=>'created', 'key'=>'created_date_to', 'operator'=>'<=', 'value'=>$date_to->format('Y-m-d'));
        }
        unset($filters['date_from']);
        unset($filters['date_to']);


        $qb = $this->createQueryBuilder('n');
        foreach ($filters as $key => $value) {
            if(is_array($value)){
                if(count($qb->getParameters()) < 1){
                    $qb->where('n.'.$value['prop'].$value['operator'].':'.$value['key'])->setParameter($value['key'], $value['value']);
                }
                else{
                    $qb->andWhere('n.'.$value['prop'].$value['operator'].':'.$value['key'])->setParameter($value['key'], $value['value']);
                }
            }
            else{
                if(count($qb->getParameters()) < 1){
                    $qb->where('n.'.$key.' = :'.$key)->setParameter($key, $value);
                }
                else{
                    $qb->andWhere('n.'.$key.' = :'.$key)->setParameter($key, $value);
                }
            }
        }
        $qb->orderBy('n.created', 'DESC');
        return $qb->getQuery()->getResult();
        //return $this->findBy($filters, array('created' => 'DESC'));
    }

    function getInactives($idOwnership, $filters) {
        if(!isset($filters)){
            $filters = array();
        }

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
