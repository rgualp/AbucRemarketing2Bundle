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

        if(!$this->getUser()->ifTouroperator()){
            $user = $this->getUser();
        }
        else{
        $user = $this->getUser()->getMentor();
        }
        $tourOperator = $this->em->getRepository("PartnerBundle:paTourOperator")->findOneBy(array("tourOperator" => $user->getUserId()));
        $travelAgency = $tourOperator->getTravelAgency();

        return $travelAgency->getAgencyPackages()[0]->getPackage();
    }

    public function isSpecialPackage(){
        return $this->getActivePackage()->isSpecial();
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
