<?php
/**
 * Created by PhpStorm.
 * User: YANET
 * Date: 13/05/2015
 * Time: 1:53 PM
 */

namespace MyCp\mycpBundle\Listener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use MyCp\mycpBundle\Entity\ownershipPayment;

class OwnershipPaymentListener {

    public function prePersist(ownershipPayment $entity, LifecycleEventArgs $args)
    {
        //$entity = $args->getEntity();
        $em = $args->getEntityManager();
        $this->generatePaymentNumber($em, $entity, $entity->getPaymentDate());
    }

    public function preUpdate(ownershipPayment $entity, PreUpdateEventArgs $args)
    {
        $em = $args->getEntityManager();
        $changeSet = $args->getEntityChangeSet();
        if(isset($changeSet["payment_date"]))
        {
            $newDate = $changeSet["payment_date"];
            if($args->hasChangedField('payment_date') && ($newDate[0]->format("Y") != $entity->getPaymentDate()->format("Y")))
                $this->generatePaymentNumber($em, $entity, $args->getNewValue("payment_date"));
        }
    }

    private function generatePaymentNumber($em, $entity, $paymentDate)
    {
            $year = $paymentDate->format("Y");

            $queryString = "SELECT MAX(op.number) from mycpBundle:ownershipPayment op WHERE op.number LIKE :number";

            $maxPaymentNumber = $em->createQuery($queryString)->setParameter('number', $year . "%")->getSingleScalarResult();

            if ($maxPaymentNumber === null)
                $maxPaymentNumber = 1;
            else {
                $maxPaymentNumber = $this->getNumberValue($maxPaymentNumber);
                $maxPaymentNumber++;
            }


            if ($maxPaymentNumber < 100)
                $newPaymentNumber = $year . str_pad($maxPaymentNumber, 3, "0", STR_PAD_LEFT);
            else
                $newPaymentNumber = $year . $maxPaymentNumber;

            //if($entity->getOwnMcpCode() == null || $entity->getOwnMcpCode() == "") {
            $entity->setNumber($newPaymentNumber);
            //}
    }

    private function getNumberValue($paymentNumber)
    {
        $paymentNumber = substr ($paymentNumber , 4 );
        $paymentNumber = ltrim($paymentNumber, '0');

        return $paymentNumber;
    }
} 