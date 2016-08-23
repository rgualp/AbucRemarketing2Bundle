<?php


namespace MyCp\PartnerBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * paAgencyPackage
 *
 * @ORM\Table(name="pa_agency_package")
 * @ORM\Entity(repositoryClass="MyCp\PartnerBundle\Repository\paPackageRepository")
 *
 */
class paAgencyPackage extends baseEntity
{

    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="paTravelAgency",inversedBy="agencyPackages")
     * @ORM\JoinColumn(name="travel_agency",referencedColumnName="id")
     */
    private $travelAgency;

    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="paPackage",inversedBy="agencyPackages")
     * @ORM\JoinColumn(name="package",referencedColumnName="id")
     */
    private $package;

    /**
     * @var datetime
     *
     * @ORM\Column(name="datePayment", type="datetime", nullable=true)
     */
    private $datePayment;

    /**
     * @var decimal
     *
     * @ORM\Column(name="payedAmount", type="decimal", precision=2, nullable=true)
     */
    private $payedAmount;

    public function __construct() {
        parent::__construct();

    }

    /**
     * @return \DateTime
     */
    public function getDatePayment()
    {
        return $this->datePayment;
    }

    /**
     * @param \DateTime $datePayment
     * @return mixed
     */
    public function setDatePayment($datePayment)
    {
        $this->datePayment = $datePayment;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPackage()
    {
        return $this->package;
    }

    /**
     * @param mixed $package
     * @return mixed
     */
    public function setPackage($package)
    {
        $this->package = $package;
        return $this;
    }

    /**
     * @return decimal
     */
    public function getPayedAmount()
    {
        return $this->payedAmount;
    }

    /**
     * @param decimal $payedAmount
     * @return mixed
     */
    public function setPayedAmount($payedAmount)
    {
        $this->payedAmount = $payedAmount;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getTravelAgency()
    {
        return $this->travelAgency;
    }

    /**
     * @param mixed $travelAgency
     * @return mixed
     */
    public function setTravelAgency($travelAgency)
    {
        $this->travelAgency = $travelAgency;
        return $this;
    }
}