<?php
namespace MyCp\PartnerBundle\Listener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use MyCp\PartnerBundle\Entity\paTravelAgency;
use Doctrine\ORM\Event\PreUpdateEventArgs;

class AgencyListener {

    public function prePersist(provider $entity, LifecycleEventArgs $args)
    {
        //$entity = $args->getEntity();
        $em = $args->getEntityManager();
        $this->generateAutomaticCode($em, $entity, $entity->getCountry());
    }

    public function preUpdate(provider $entity, PreUpdateEventArgs $args)
    {
        $em = $args->getEntityManager();
        $changeSet = $args->getEntityChangeSet();
        if(isset($changeSet["country"]))
        {
            if($args->hasChangedField('province'))
                $this->generateAutomaticCode($em, $entity, $args->getNewValue("country"));
        }
    }

    private function generateAutomaticCode($em, $entity, $country)
    {
            $prefix='P';
            $code = $country->getCode();
            $query = "SELECT MAX(SUBSTRING(o.code, 5)*1) AS code FROM PartnerBundle:paTravelAgency o WHERE o.code LIKE :mycode";
            $codeMCP = $em->createQuery($query)->setParameter('mycode', "%" . $prefix.$code . "%")->getSingleScalarResult();
            if (count($codeMCP)) {
                $number = (int)$codeMCP;
                $number++;

                $str_number = ''.$number;
                if($number < 100){
                    $str_number = str_pad($str_number, 3, "0", STR_PAD_LEFT);
                }

                $code = $prefix.$code . $str_number;
                $entity->setCode($code);
            }
        else{
            $codeMCP=0;
            $number = (int)$codeMCP;
            $number++;

            $str_number = ''.$number;
            if($number < 100){
                $str_number = str_pad($str_number, 3, "0", STR_PAD_LEFT);
            }

            $code = $prefix.$code . $str_number;
            $entity->setCode($code);
        }
    }
} 