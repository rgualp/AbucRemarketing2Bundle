<?php


namespace MyCp\mycpBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ownershipRankingExtra
 *
 * @ORM\Table(name="ownership_ranking_extra_year")
 * @ORM\Entity
 *
 */
class ownershipRankingExtraYear
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
     * @var int
     *
     * @ORM\Column(name="year", type="integer")
     */
    private $year;


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
     * @ORM\Column(name="currentYearFacturation", type="decimal", nullable=true)
     */
    private $currentYearFacturation;

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
     * @return integer
     */
    public function getYear()
    {
        return $this->year;
    }

    /**
     * @param integer $year
     * @return mixed
     */
    public function setYear($year)
    {
        $this->year = $year;
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
    public function getCurrentYearFacturation()
    {
        return $this->currentYearFacturation;
    }

    /**
     * @param decimal $currentYearFacturation
     * @return mixed
     */
    public function setCurrentYearFacturation($currentYearFacturation)
    {
        $this->currentYearFacturation = $currentYearFacturation;
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