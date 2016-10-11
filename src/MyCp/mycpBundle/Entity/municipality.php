<?php

namespace MyCp\mycpBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * municipality
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="MyCp\mycpBundle\Entity\municipalityRepository")
 */
class municipality
{
    /**
     * @var integer
     *
     * @ORM\Column(name="mun_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $mun_id;

    /**
     * @ORM\ManyToOne(targetEntity="province", inversedBy="prov_municipalities")
     * @ORM\JoinColumn(name="mun_prov_id", referencedColumnName="prov_id", nullable=false)
     * @return integer
     */
    private $mun_prov_id;

    /**
     * @var string
     *
     * @ORM\Column(name="mun_name", type="string", length=255, nullable=false)
     * @Assert\NotBlank()
     */
    private $mun_name;

    /**
     * @ORM\OneToMany(targetEntity="ownershipStat", mappedBy="stat_municipality")
     */
    private $statMunicipalities;

    /**
     * @ORM\OneToMany(targetEntity="localOperationAssistant",mappedBy="municipality")
     */
    private $localOperationAssistants;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->statMunicipalities = new \Doctrine\Common\Collections\ArrayCollection();
        $this->localOperationAssistants = new ArrayCollection();
    }

    /**
     * Get mun_id
     *
     * @return integer
     */
    public function getMunId()
    {
        return $this->mun_id;
    }


    /**
     * Set mun_name
     *
     * @param string $munName
     * @return municipality
     */
    public function setMunName($munName)
    {
        $this->mun_name = $munName;

        return $this;
    }

    /**
     * Get mun_name
     *
     * @return string
     */
    public function getMunName()
    {
        return $this->mun_name;
    }

    /**
     * Set mun_prov_id
     *
     * @param \MyCp\mycpBundle\Entity\province $munProvId
     * @return municipality
     */
    public function setMunProvId($munProvId)
    {
        $this->mun_prov_id = $munProvId;

        return $this;
    }

    /**
     * Get mun_prov_id
     *
     * @return \MyCp\mycpBundle\Entity\province
     */
    public function getMunProvId()
    {
        return $this->mun_prov_id;
    }

    /**
     * Add stat
     *
     * @param ownershipStat $stat
     * @return municipality
     */
    public function addStatMinicipalities(ownershipStat $stat)
    {
        $this->statMunicipalities[] = $stat;

        return $this;
    }

    /**
     * Remove stat
     *
     * @param ownershipStat $stat
     */
    public function removeStatMinicipalities(ownershipStat $stat)
    {
        $this->statMunicipalities->removeElement($stat);
    }

    /**
     * Get stats
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getStatMinicipalities()
    {
        return $this->statMunicipalities;
    }
    public function setStatMinicipalities(ArrayCollection $stats)
    {
        $this->statMunicipalities = $stats;
        return $this;
    }


    /**
     * Retur object as string
     * @return string
     */
    public function __toString()
    {
        return $this->getMunName();
    }

    /*Logs functions*/
    public function getLogDescription()
    {
        return "Municipio ".$this->getMunName();
    }

    /**
     * @return mixed
     */
    public function getLocalOperationAssistants()
    {
        return $this->localOperationAssistants;
    }

    /**
     * @param mixed $localOperationAssistants
     * @return mixed
     */
    public function setLocalOperationAssistants($localOperationAssistants)
    {
        $this->localOperationAssistants = $localOperationAssistants;
        return $this;
    }
}