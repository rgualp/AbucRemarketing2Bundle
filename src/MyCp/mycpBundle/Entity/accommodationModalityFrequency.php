<?php

namespace MyCp\mycpBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use MyCp\mycpBundle\Entity\generalReservation;

/**
 * accommodationCalendarFrequency
 *
 * @ORM\Table(name="accommodation_modality_frequency")
 * @ORM\Entity(repositoryClass="MyCp\mycpBundle\Entity\accommodationModalityFrequencyRepository")
 */
class accommodationModalityFrequency
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
     * @var \DateTime
     *
     * @ORM\Column(name="startDate", type="date")
     */
    private $startDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="endDate", type="date", nullable=true)
     */
    private $endDate;

    /**
     * @ORM\ManyToOne(targetEntity="ownership",inversedBy="modalityUpdateFrequency")
     * @ORM\JoinColumn(name="accommodation",referencedColumnName="own_id")
     */
    private $accommodation;

    /**
     * @ORM\ManyToOne(targetEntity="nomenclator",inversedBy="modalityUpdateFrequency")
     * @ORM\JoinColumn(name="modality",referencedColumnName="nom_id")
     */
    private $modality;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getAccommodation()
    {
        return $this->accommodation;
    }

    /**
     * @param mixed $accommodation
     * @return mixed
     */
    public function setAccommodation($accommodation)
    {
        $this->accommodation = $accommodation;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getEndDate()
    {
        return $this->endDate;
    }

    /**
     * @param \DateTime $endDate
     * @return mixed
     */
    public function setEndDate($endDate)
    {
        $this->endDate = $endDate;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getModality()
    {
        return $this->modality;
    }

    /**
     * @param mixed $modality
     * @return mixed
     */
    public function setModality($modality)
    {
        $this->modality = $modality;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getStartDate()
    {
        return $this->startDate;
    }

    /**
     * @param \DateTime $startDate
     * @return mixed
     */
    public function setStartDate($startDate)
    {
        $this->startDate = $startDate;
        return $this;
    }
}
