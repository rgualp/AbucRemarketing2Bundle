<?php
/**
 * Created by PhpStorm.
 * User: YANET
 * Date: 13/05/2015
 * Time: 1:53 PM
 */

namespace MyCp\mycpBundle\Listener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use MyCp\mycpBundle\Entity\ownership;
use Doctrine\ORM\Event\PreUpdateEventArgs;

class OwnershipListener {

    public function prePersist(ownership $entity, LifecycleEventArgs $args)
    {
        //$entity = $args->getEntity();
        $em = $args->getEntityManager();
        $this->generateAutomaticCode($em, $entity, $entity->getOwnAddressProvince());
    }

    public function preUpdate(ownership $entity, PreUpdateEventArgs $args)
    {
        $em = $args->getEntityManager();
        if($args->hasChangedField('own_address_province') || $args->hasChangedField("own_destination"))
        {
            $this->generateAutomaticCode($em, $entity, $args->getNewValue("own_address_province"));
        }
    }

    private function generateAutomaticCode($em, $entity, $province)
    {
        $queryString = "SELECT MAX(o.own_automatic_mcp_code) from mycpBundle:ownership o WHERE o.own_address_province = :province";

        $maxAutomaticIndex = $em->createQuery($queryString)->setParameter('province', $province)->getSingleScalarResult();

        if($maxAutomaticIndex === null)
            $maxAutomaticIndex = 0;

        $entity->setOwnAutomaticMcpCode($maxAutomaticIndex + 1);
        //$newGeneratedCode = "";

        if( $entity->getOwnAutomaticMcpCode() < 100)
            $newGeneratedCode =  $province->getProvOwnCode().str_pad($entity->getOwnAutomaticMcpCode(), 3, "0", STR_PAD_LEFT);
        else
            $newGeneratedCode =  $province->getProvOwnCode().$entity->getOwnAutomaticMcpCode();

        $entity->setOwnMcpCodeGenerated($newGeneratedCode);
    }
} 