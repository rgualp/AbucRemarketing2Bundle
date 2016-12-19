<?php


namespace MyCp\mycpBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ownershipRankingExtra
 *
 * @ORM\Table(name="ownership_ranking_extra")
 * @ORM\Entity
 *
 */
class ownershipRankingExtra
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
     * @ORM\ManyToOne(targetEntity="ownership",inversedBy="rankingExtras")
     * @ORM\JoinColumn(name="accommodation",referencedColumnName="own_id")
     */
    private $accommodation;

    /**
     * @ORM\ManyToOne(targetEntity="rankingPoint",inversedBy="accommodationsRanking")
     * @ORM\JoinColumn(name="rankingPoints",referencedColumnName="id")
     */
    private $rankingPoints;

    /**
     * @var datetime
     *
     * @ORM\Column(name="startDate", type="date")
     */
    private $startDate;

    /**
     * @var datetime
     *
     * @ORM\Column(name="endDate", type="date")
     */
    private $endDate;

    /**
     * @var int
     *
     * @ORM\Column(name="fad", type="integer", nullable=true)
     */
    private $fad;

    /**
     * @var int
     *
     * @ORM\Column(name="rr", type="integer", nullable=true)
     */
    private $rr;

    /**
     * @var int
     *
     * @ORM\Column(name="ri", type="integer", nullable=true)
     */
    private $ri;

    /**
     * @var integer
     *
     * @ORM\Column(name="sd", type="integer", nullable=true)
     */
    private $sd;

    /**
     * @var integer
     *
     * @ORM\Column(name="reservations", type="integer", nullable=true)
     */
    private $reservations;

    /**
     * @var integer
     *
     * @ORM\Column(name="positiveComments", type="integer", nullable=true)
     */
    private $positiveComments;

    /**
     * @var integer
     *
     * @ORM\Column(name="awards", type="integer", nullable=true)
     */
    private $awards;

    /**
     * @var integer
     *
     * @ORM\Column(name="confidence", type="integer", nullable=true)
     */
    private $confidence;

    /**
     * @var integer
     *
     * @ORM\Column(name="newOffersReserved", type="integer", nullable=true)
     */
    private $newOffersReserved;

    /**
     * @var integer
     *
     * @ORM\Column(name="failureCasa", type="integer", nullable=true)
     */
    private $failureCasa;

    /**
     * @var integer
     *
     * @ORM\Column(name="negativeComments", type="integer", nullable=true)
     */
    private $negativeComments;

    /**
     * @var integer
     *
     * @ORM\Column(name="snd", type="integer", nullable=true)
     */
    private $snd;

    /**
     * @var integer
     *
     * @ORM\Column(name="penalties", type="integer", nullable=true)
     */
    private $penalties;

    /**
     * @var integer
     *
     * @ORM\Column(name="failureClients", type="integer", nullable=true)
     */
    private $failureClients;

    /**
     * @var integer
     *
     * @ORM\Column(name="facturation", type="integer", nullable=true)
     */
    private $facturation;

    /**
     * @var decimal
     *
     * @ORM\Column(name="ranking", type="decimal", nullable=true)
     */
    private $ranking;

    /**
     * @var integer
     *
     * @ORM\Column(name="place", type="integer", nullable=true)
     */
    private $place;

    /**
     * @var integer
     *
     * @ORM\Column(name="destinationPlace", type="integer", nullable=true)
     */
    private $destinationPlace;

    /**
     * @ORM\ManyToOne(targetEntity="nomenclator",inversedBy="rankingCategories")
     * @ORM\JoinColumn(name="category",referencedColumnName="nom_id")
     */
    private $category;

    /**
     * @var decimal
     *
     * @ORM\Column(name="currentMonthFacturation", type="decimal", nullable=true)
     */
    private $currentMonthFacturation;

    /**
     * @var decimal
     *
     * @ORM\Column(name="totalFacturation", type="decimal", nullable=true)
     */
    private $totalFacturation;

    /**
     * @var integer
     *
     * @ORM\Column(name="totalAvailableRooms", type="integer", nullable=true)
     */
    private $totalAvailableRooms;

    /**
     * @var integer
     *
     * @ORM\Column(name="totalNonAvailableRooms", type="integer", nullable=true)
     */
    private $totalNonAvailableRooms;

    /**
     * @var integer
     *
     * @ORM\Column(name="totalReservedRooms", type="integer", nullable=true)
     */
    private $totalReservedRooms;

    /**
     * @var integer
     *
     * @ORM\Column(name="visits", type="integer", nullable=true)
     */
    private $visits;

    /**
     * @var decimal
     *
     * @ORM\Column(name="totalAvailableFacturation", type="decimal", nullable=true)
     */
    private $totalAvailableFacturation;

    /**
     * @var decimal
     *
     * @ORM\Column(name="totalNonAvailableFacturation", type="decimal", nullable=true)
     */
    private $totalNonAvailableFacturation;

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
     * @return int
     */
    public function getDestinationPlace()
    {
        return $this->destinationPlace;
    }

    /**
     * @param int $destinationPlace
     * @return mixed
     */
    public function setDestinationPlace($destinationPlace)
    {
        $this->destinationPlace = $destinationPlace;
        return $this;
    }

    /**
     * @return datetime
     */
    public function getEndDate()
    {
        return $this->endDate;
    }

    /**
     * @param datetime $endDate
     * @return mixed
     */
    public function setEndDate($endDate)
    {
        $this->endDate = $endDate;
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
    public function getId()
    {
        return $this->id;
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
    public function getNewOffersReserved()
    {
        return $this->newOffersReserved;
    }

    /**
     * @param int $newOffersReserved
     * @return mixed
     */
    public function setNewOffersReserved($newOffersReserved)
    {
        $this->newOffersReserved = $newOffersReserved;
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
    public function getPlace()
    {
        return $this->place;
    }

    /**
     * @param int $place
     * @return mixed
     */
    public function setPlace($place)
    {
        $this->place = $place;
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
     * @return decimal
     */
    public function getRanking()
    {
        return $this->ranking;
    }

    /**
     * @param decimal $ranking
     * @return mixed
     */
    public function setRanking($ranking)
    {
        $this->ranking = $ranking;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getRankingPoints()
    {
        return $this->rankingPoints;
    }

    /**
     * @param mixed $rankingPoints
     * @return mixed
     */
    public function setRankingPoints($rankingPoints)
    {
        $this->rankingPoints = $rankingPoints;
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
     * @return datetime
     */
    public function getStartDate()
    {
        return $this->startDate;
    }

    /**
     * @param datetime $startDate
     * @return mixed
     */
    public function setStartDate($startDate)
    {
        $this->startDate = $startDate;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @param mixed $category
     * @return mixed
     */
    public function setCategory($category)
    {
        $this->category = $category;
        return $this;
    }

    /**
     * @return decimal
     */
    public function getCurrentMonthFacturation()
    {
        return $this->currentMonthFacturation;
    }

    /**
     * @param decimal $currentMonthFacturation
     * @return mixed
     */
    public function setCurrentMonthFacturation($currentMonthFacturation)
    {
        $this->currentMonthFacturation = $currentMonthFacturation;
        return $this;
    }

    /**
     * @return int
     */
    public function getTotalAvailableRooms()
    {
        return $this->totalAvailableRooms;
    }

    /**
     * @param int $totalAvailableRooms
     * @return mixed
     */
    public function setTotalAvailableRooms($totalAvailableRooms)
    {
        $this->totalAvailableRooms = $totalAvailableRooms;
        return $this;
    }

    /**
     * @return decimal
     */
    public function getTotalFacturation()
    {
        return $this->totalFacturation;
    }

    /**
     * @param decimal $totalFacturation
     * @return mixed
     */
    public function setTotalFacturation($totalFacturation)
    {
        $this->totalFacturation = $totalFacturation;
        return $this;
    }

    /**
     * @return int
     */
    public function getTotalNonAvailableRooms()
    {
        return $this->totalNonAvailableRooms;
    }

    /**
     * @param int $totalNonAvailableRooms
     * @return mixed
     */
    public function setTotalNonAvailableRooms($totalNonAvailableRooms)
    {
        $this->totalNonAvailableRooms = $totalNonAvailableRooms;
        return $this;
    }

    /**
     * @return int
     */
    public function getTotalReservedRooms()
    {
        return $this->totalReservedRooms;
    }

    /**
     * @param int $totalReservedRooms
     * @return mixed
     */
    public function setTotalReservedRooms($totalReservedRooms)
    {
        $this->totalReservedRooms = $totalReservedRooms;
        return $this;
    }

    /**
     * @return decimal
     */
    public function getTotalAvailableFacturation()
    {
        return $this->totalAvailableFacturation;
    }

    /**
     * @param decimal $totalAvailableFacturation
     * @return mixed
     */
    public function setTotalAvailableFacturation($totalAvailableFacturation)
    {
        $this->totalAvailableFacturation = $totalAvailableFacturation;
        return $this;
    }

    /**
     * @return decimal
     */
    public function getTotalNonAvailableFacturation()
    {
        return $this->totalNonAvailableFacturation;
    }

    /**
     * @param decimal $totalNonAvailableFacturation
     * @return mixed
     */
    public function setTotalNonAvailableFacturation($totalNonAvailableFacturation)
    {
        $this->totalNonAvailableFacturation = $totalNonAvailableFacturation;
        return $this;
    }

    /**
     * @return int
     */
    public function getVisits()
    {
        return $this->visits;
    }

    /**
     * @param int $visits
     * @return mixed
     */
    public function setVisits($visits)
    {
        $this->visits = $visits;
        return $this;
    }



}