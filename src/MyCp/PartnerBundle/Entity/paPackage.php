<?php


namespace MyCp\PartnerBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * paPackage
 *
 * @ORM\Table(name="pa_package")
 * @ORM\Entity(repositoryClass="MyCp\PartnerBundle\Repository\paPackageRepository")
 * @ORM\EntityListeners({"MyCp\PartnerBundle\Listener\BaseEntityListener"})
 *
 */
class paPackage extends baseEntity
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var decimal
     *
     * @ORM\Column(name="price", type="decimal", precision=2)
     */
    private $price;

    /**
     * @var boolean
     *
     * @ORM\Column(name="completePayment", type="boolean")
     */
    private $completePayment;

    /**
     * @var integer
     *
     * @ORM\Column(name="maxTourOperators", type="integer")
     */
    private $maxTourOperators;

    /**
     * @ORM\OneToMany(targetEntity="paAgencyPackage", mappedBy="package")
     */
    private $agencyPackages;


    public function __construct() {
        parent::__construct();

        $this->agencyPackages = new ArrayCollection();
        $this->completePayment = false;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return decimal
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @param decimal $price
     * @return mixed
     */
    public function setPrice($price)
    {
        $this->price = $price;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getAgencyPackages()
    {
        return $this->agencyPackages;
    }

    /**
     * @param mixed $agencyPackages
     * @return mixed
     */
    public function setAgencyPackages($agencyPackages)
    {
        $this->agencyPackages = $agencyPackages;
        return $this;
    }

    /**
     * Add agencyPackage
     *
     * @param paAgencyPackage $agencyPackage
     *
     * @return mixed
     */
    public function addAgencyPackage(paAgencyPackage $agencyPackage)
    {
        $this->agencyPackages[] = $agencyPackage;

        return $this;
    }

    /**
     * Remove agencyPackage
     *
     * @param paAgencyPackage $agencyPackage
     */
    public function removeAgencyPackage(paAgencyPackage $agencyPackage)
    {
        $this->agencyPackages->removeElement($agencyPackage);
    }

    /**
     * @return int
     */
    public function getMaxTourOperators()
    {
        return $this->maxTourOperators;
    }

    /**
     * @param int $maxTourOperators
     * @return mixed
     */
    public function setMaxTourOperators($maxTourOperators)
    {
        $this->maxTourOperators = $maxTourOperators;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isCompletePayment()
    {
        return $this->completePayment;
    }

    /**
     * @return boolean
     */
    public function getCompletePayment()
    {
        return $this->completePayment;
    }


    /**
     * @param boolean $completePayment
     * @return mixed
     */
    public function setCompletePayment($completePayment)
    {
        $this->completePayment = $completePayment;
        return $this;
    }



}