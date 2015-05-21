<?php
/**
 * Created by PhpStorm.
 * User: YANET
 * Date: 13/05/2015
 * Time: 1:53 PM
 */

namespace MyCp\mycpBundle\Listener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use \MyCp\FrontEndBundle\Helpers\Time;
use MyCp\mycpBundle\Entity\ownershipReservation;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

class OwnershipReservationListener {

    private $timer;

    public function __construct($timer)
    {
        $this->timer = $timer;
    }

    public function prePersist(ownershipReservation $entity, LifecycleEventArgs $args)
    {
        $this->calculateNights($entity);
    }

    public function preUpdate(ownershipReservation $entity, PreUpdateEventArgs $args)
    {
        if($args->hasChangedField('own_res_reservation_from_date') || $args->hasChangedField("own_res_reservation_to_date"))
        {
            $this->calculateNights($entity);
        }
    }

    private function calculateNights($entity)
    {
        //$timer = $this->get("Time");
        $nights = $this->timer->nights($entity->getOwnResReservationFromDate()->getTimestamp(),$entity->getOwnResReservationToDate()->getTimestamp());
        $entity->setOwnResNights($nights);
    }
} 