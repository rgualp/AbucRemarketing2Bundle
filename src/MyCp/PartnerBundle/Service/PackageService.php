<?php

namespace MyCp\PartnerBundle\Service;

use Doctrine\Common\Persistence\ObjectManager;
use MyCp\FrontEndBundle\Helpers\Time;
use MyCp\PartnerBundle\Entity\paPendingPaymentAccommodation;
use MyCp\PartnerBundle\Entity\paPendingPaymentAgency;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class PackageService extends Controller
{
    /**
     * @var ObjectManager
     */
    private $em;
    protected $container;

    public function __construct(ObjectManager $em, $container)
    {
        $this->em = $em;
        $this->container = $container;
    }

    public function getActivePackage()
    {


        $user = $this->getUser();


        $tourOperator = $this->em->getRepository("PartnerBundle:paTourOperator")->findOneBy(array("tourOperator" => $user->getUserId()));
        $travelAgency = $tourOperator->getTravelAgency();

        return $travelAgency->getAgencyPackages()[0]->getPackage();
    }

    public function isSpecialPackage(){
        return $this->getActivePackage()->isSpecial();
    }
    public function isEconomicPackage(){
        return $this->getActivePackage()->isEconomic();
    }
    public function isBasicPackage(){
        return $this->getActivePackage()->isBasic();
    }

    public function getActivePackageFromAgency($travelAgency)
    {
        return $travelAgency->getAgencyPackages()[0]->getPackage();
    }

    public function isSpecialPackageFromAgency($travelAgency)
    {
        return $this->getActivePackageFromAgency($travelAgency)->isSpecial();
    }
}
