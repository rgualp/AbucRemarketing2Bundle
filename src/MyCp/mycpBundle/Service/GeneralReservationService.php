<?php

namespace MyCp\FrontEndBundle\Service;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManager;
use MyCp\mycpBundle\Entity\generalReservation;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class GeneralReservationService extends Controller
{
    /**
     * @var ObjectManager
     */
    private $em;

    public function __construct(ObjectManager $em)
    {
        $this->em = $em;
    }

    public function update(generalReservation $entity)
    {

    }

}
