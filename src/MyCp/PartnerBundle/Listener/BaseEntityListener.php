<?php
/**
 * Created by PhpStorm.
 * User: YANET
 * Date: 13/05/2015
 * Time: 1:53 PM
 */

namespace  MyCp\PartnerBundle\Listener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use MyCp\mycpBundle\Entity\ownership;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use MyCp\PartnerBundle\Entity\baseEntity;
use Symfony\Component\DependencyInjection\ContainerInterface;

class BaseEntityListener {

   /* private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }*/


    public function preUpdate($entity, PreUpdateEventArgs $args)
    {
        //$user = $this->container->get('security.context')->getToken()->getUser();
        $entity->setModified(new \DateTime())
            //->setModifiedBy($user)
        ;
    }

    public function prePersist($entity, LifecycleEventArgs $args)
    {
       /* if($entity->getCreatedBy() == null) {
            $user = $this->container->get('security.context')->getToken()->getUser();
            $entity->setCreatedBy($user);
        }*/

    }

} 