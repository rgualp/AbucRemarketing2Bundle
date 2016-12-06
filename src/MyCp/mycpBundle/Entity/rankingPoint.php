<?php

namespace MyCp\mycpBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * rankingPoint
 *
 * @ORM\Table(name="ranking_point")
 * @ORM\Entity(repositoryClass="MyCp\mycpBundle\Entity\rankingPointRepository")
 */
class rankingPoint
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
     * @ORM\Column(name="creationDate", type="datetime")
     */
    private $creationDate;

    /**
     * @var boolean
     *
     * @ORM\Column(name="active", type="boolean")
     */
    private $active;

    /**
     * @var integer
     *
     * @ORM\Column(name="fad", type="integer")
     */
    private $fad;

    /**
     * @var integer
     *
     * @ORM\Column(name="rr", type="integer")
     */
    private $rr;

    /**
     * @var integer
     *
     * @ORM\Column(name="ri", type="integer")
     */
    private $ri;

    /**
     * @var integer
     *
     * @ORM\Column(name="sd", type="integer")
     */
    private $sd;

    /**
     * @var integer
     *
     * @ORM\Column(name="reservations", type="integer")
     */
    private $reservations;

    /**
     * @var integer
     *
     * @ORM\Column(name="positiveComments", type="integer")
     */
    private $positiveComments;

    /**
     * @var integer
     *
     * @ORM\Column(name="awards", type="integer")
     */
    private $awards;

    /**
     * @var integer
     *
     * @ORM\Column(name="confidence", type="integer")
     */
    private $confidence;

    /**
     * @var integer
     *
     * @ORM\Column(name="newOffers", type="integer")
     */
    private $newOffers;

    /**
     * @var integer
     *
     * @ORM\Column(name="failureCasa", type="integer")
     */
    private $failureCasa;

    /**
     * @var integer
     *
     * @ORM\Column(name="negativeComments", type="integer")
     */
    private $negativeComments;

    /**
     * @var integer
     *
     * @ORM\Column(name="snd", type="integer")
     */
    private $snd;

    /**
     * @var integer
     *
     * @ORM\Column(name="penalties", type="integer")
     */
    private $penalties;

    /**
     * @var integer
     *
     * @ORM\Column(name="failureClients", type="integer")
     */
    private $failureClients;

    /**
     * @var integer
     *
     * @ORM\Column(name="facturation", type="integer")
     */
    private $facturation;

    /**
     * @ORM\OneToMany(targetEntity="ownershipRankingExtra",mappedBy="rankingPoints")
     */
    private $accommodationsRanking;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->accommodationsRanking = new ArrayCollection();
    }

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
     * @return boolean
     */
    public function isActive()
    {
        return $this->active;
    }

    /**
     * @param boolean $active
     * @return mixed
     */
    public function setActive($active)
    {
        $this->active = $active;
        return $this;
    }

    /**
     * @return int
     */
    public function getAwards()
    {
        return $this->awards;
    }

    /**
     * @param int $awards
     * @return mixed
     */
    public function setAwards($awards)
    {
        $this->awards = $awards;
        return $this;
    }

    /**
     * @return int
     */
    public function getConfidence()
    {
        return $this->confidence;
    }

    /**
     * @param int $confidence
     * @return mixed
     */
    public function setConfidence($confidence)
    {
        $this->confidence = $confidence;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getCreationDate()
    {
        return $this->creationDate;
    }

    /**
     * @param \DateTime $creationDate
     * @return mixed
     */
    public function setCreationDate($creationDate)
    {
        $this->creationDate = $creationDate;
        return $this;
    }

    /**
     * @return int
     */
    public function getFacturation()
    {
        return $this->facturation;
    }

    /**
     * @param int $facturation
     * @return mixed
     */
    public function setFacturation($facturation)
    {
        $this->facturation = $facturation;
        return $this;
    }

    /**
     * @return int
     */
    public function getFad()
    {
        return $this->fad;
    }

    /**
     * @param int $fad
     * @return mixed
     */
    public function setFad($fad)
    {
        $this->fad = $fad;
        return $this;
    }

    /**
     * @return int
     */
    public function getFailureCasa()
    {
        return $this->failureCasa;
    }

    /**
     * @param int $failureCasa
     * @return mixed
     */
    public function setFailureCasa($failureCasa)
    {
        $this->failureCasa = $failureCasa;
        return $this;
    }

    /**
     * @return int
     */
    public function getFailureClients()
    {
        return $this->failureClients;
    }

    /**
     * @param int $failureClients
     * @return mixed
     */
    public function setFailureClients($failureClients)
    {
        $this->failureClients = $failureClients;
        return $this;
    }

    /**
     * @return int
     */
    public function getNegativeComments()
    {
        return $this->negativeComments;
    }

    /**
     * @param int $negativeComments
     * @return mixed
     */
    public function setNegativeComments($negativeComments)
    {
        $this->negativeComments = $negativeComments;
        return $this;
    }

    /**
     * @return int
     */
    public function getNewOffers()
    {
        return $this->newOffers;
    }

    /**
     * @param int $newOffers
     * @return mixed
     */
    public function setNewOffers($newOffers)
    {
        $this->newOffers = $newOffers;
        return $this;
    }

    /**
     * @return int
     */
    public function getPenalties()
    {
        return $this->penalties;
    }

    /**
     * @param int $penalties
     * @return mixed
     */
    public function setPenalties($penalties)
    {
        $this->penalties = $penalties;
        return $this;
    }

    /**
     * @return int
     */
    public function getPositiveComments()
    {
        return $this->positiveComments;
    }

    /**
     * @param int $positiveComments
     * @return mixed
     */
    public function setPositiveComments($positiveComments)
    {
        $this->positiveComments = $positiveComments;
        return $this;
    }

    /**
     * @return int
     */
    public function getReservations()
    {
        return $this->reservations;
    }

    /**
     * @param int $reservations
     * @return mixed
     */
    public function setReservations($reservations)
    {
        $this->reservations = $reservations;
        return $this;
    }

    /**
     * @return int
     */
    public function getRi()
    {
        return $this->ri;
    }

    /**
     * @param int $ri
     * @return mixed
     */
    public function setRi($ri)
    {
        $this->ri = $ri;
        return $this;
    }

    /**
     * @return int
     */
    public function getRr()
    {
        return $this->rr;
    }

    /**
     * @param int $rr
     * @return mixed
     */
    public function setRr($rr)
    {
        $this->rr = $rr;
        return $this;
    }

    /**
     * @return int
     */
    public function getSd()
    {
        return $this->sd;
    }

    /**
     * @param int $sd
     * @return mixed
     */
    public function setSd($sd)
    {
        $this->sd = $sd;
        return $this;
    }

    /**
     * @return int
     */
    public function getSnd()
    {
        return $this->snd;
    }

    /**
     * @param int $snd
     * @return mixed
     */
    public function setSnd($snd)
    {
        $this->snd = $snd;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getAccommodationsRanking()
    {
        return $this->accommodationsRanking;
    }

    /**
     * @param mixed $accommodationsRanking
     */
    public function setAccommodationsRanking($accommodationsRanking)
    {
        $this->accommodationsRanking = $accommodationsRanking;
    }


}
