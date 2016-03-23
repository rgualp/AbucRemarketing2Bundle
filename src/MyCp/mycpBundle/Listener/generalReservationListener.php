<?php
/**
 * Created by PhpStorm.
 * User: YANET
 * Date: 13/05/2015
 * Time: 1:53 PM
 */

namespace MyCp\mycpBundle\Listener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use MyCp\mycpBundle\Entity\generalReservation;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Symfony\Component\DependencyInjection\ContainerInterface;

class generalReservationListener {

    protected $service_container;

    public function __construct(ContainerInterface $service_container)
    {
        $this->service_container = $service_container;
    }

    public function preUpdate(generalReservation $entity, PreUpdateEventArgs $args)
    {
        $entity->setModified(new \DateTime())
            ->setModifiedBy();
    }

} 