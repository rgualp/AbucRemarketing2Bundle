<?php

namespace MyCp\mycpBundle\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\OneToMany;
use MyCp\mycpBundle\Helpers\SyncStatuses;

/**
 * generalreservation
 *
 * @ORM\Table(name="generalreservation")
 * @ORM\Entity(repositoryClass="MyCp\mycpBundle\Entity\generalReservationRepository")
 */
class generalReservation {

    /**
     * All allowed statuses
     */
    const STATUS_NONE = -1;
    const STATUS_PENDING = 0;
    const STATUS_AVAILABLE = 1;
    const STATUS_RESERVED = 2;
    const STATUS_NOT_AVAILABLE = 3;
    const STATUS_PARTIAL_AVAILABLE = 4;
    const STATUS_PARTIAL_RESERVED = 5;
    const STATUS_CANCELLED = 6;

    /**
     * Contains all possible statuses
     *
     * @var array
     */
    private $statuses = array(
        self::STATUS_NONE,
        self::STATUS_PENDING,
        self::STATUS_AVAILABLE,
        self::STATUS_RESERVED,
        self::STATUS_NOT_AVAILABLE,
        self::STATUS_PARTIAL_AVAILABLE,
        self::STATUS_PARTIAL_RESERVED,
        self::STATUS_CANCELLED
    );

    /**
     * @var integer
     *
     * @ORM\Column(name="gen_res_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $gen_res_id;

    /**
     * @ORM\ManyToOne(targetEntity="user")
     * @ORM\JoinColumn(name="gen_res_user_id",referencedColumnName="user_id")
     */
    private $gen_res_user_id;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="gen_res_date", type="date")
     */
    private $gen_res_date;

    /**
     * @var integer
     *
     * @ORM\Column(name="gen_res_status", type="integer")
     */
    private $gen_res_status;

    /**
     * @var boolean
     *
     * @ORM\Column(name="gen_res_saved", type="boolean")
     */
    private $gen_res_saved;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="gen_res_status_date", type="date")
     */
    private $gen_res_status_date;

    /**
     * @var \integer
     *
     * @ORM\Column(name="gen_res_hour", type="integer", nullable=true)
     */
    private $gen_res_hour;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="gen_res_from_date", type="date")
     */
    private $gen_res_from_date;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="gen_res_to_date", type="date")
     */
    private $gen_res_to_date;

    /**
     * @ORM\ManyToOne(targetEntity="ownership",inversedBy="own_general_reservations")
     * @ORM\JoinColumn(name="gen_res_own_id",referencedColumnName="own_id")
     */
    private $gen_res_own_id;

    /**
     * @var integer
     *
     * @ORM\Column(name="gen_res_total_in_site", type="float")
     */
    private $gen_res_total_in_site;

    /**
     * @OneToMany(targetEntity="ownershipReservation", mappedBy="own_res_gen_res_id")
     */
    private $own_reservations;

    /**
     * @var string
     *
     * @ORM\Column(name="gen_res_arrival_hour", type="text",nullable=true)
     */
    private $gen_res_arrival_hour;

    /**
     * @var boolean
     *
     * @ORM\Column(name="gen_res_sync_st", type="integer")
     */
    private $gen_res_sync_st;


    /**
     * Constructor
     */
    public function __construct() {
        $this->gen_res_sync_st = SyncStatuses::ADDED;
        $this->own_reservations = new ArrayCollection();
        $this->gen_res_status = generalReservation::STATUS_PENDING;
        $this->gen_res_status_date = new \DateTime();
    }

    /**
     * Get gen_res_id
     *
     * @return integer
     */
    public function getGenResId() {
        return $this->gen_res_id;
    }

    /**
     * Set gen_res_user_id
     *
     * @param \MyCp\mycpBundle\Entity\user $genResUserId
     * @return generalReservation
     */
    public function setGenResUserId(\MyCp\mycpBundle\Entity\user $genResUserId = null) {
        $this->gen_res_user_id = $genResUserId;

        return $this;
    }

    /**
     * Get gen_res_user_id
     *
     * @return \MyCp\mycpBundle\Entity\user
     */
    public function getGenResUserId() {
        return $this->gen_res_user_id;
    }

    /**
     * Set gen_res_date
     *
     * @param DateTime $genResDate
     * @return generalReservation
     */
    public function setGenResDate($genResDate) {
        $this->gen_res_date = $genResDate;

        return $this;
    }

    /**
     * Get gen_res_date
     *
     * @return DateTime
     */
    public function getGenResDate() {
        return $this->gen_res_date;
    }

    /**
     * Set gen_res_status
     *
     * @param integer $genResStatus
     * @return generalReservation
     */
    public function setGenResStatus($genResStatus) {
        if (!in_array($genResStatus, $this->statuses)) {
            throw new \InvalidArgumentException("Status $genResStatus not allowed");
        }

        $this->gen_res_status = $genResStatus;
        return $this;
    }

    /**
     * Get gen_res_status
     *
     * @return integer
     */
    public function getGenResStatus() {
        return $this->gen_res_status;
    }

    /**
     * Set gen_res_status_date
     *
     * @param DateTime $genResStatusDate
     * @return generalReservation
     */
    public function setGenResStatusDate($genResStatusDate) {
        $this->gen_res_status_date = $genResStatusDate;

        return $this;
    }

    /**
     * Get gen_res_status_date
     *
     * @return DateTime
     */
    public function getGenResStatusDate() {
        return $this->gen_res_status_date;
    }

    /**
     * Set gen_res_saved
     *
     * @param boolean $genResSaved
     * @return generalReservation
     */
    public function setGenResSaved($genResSaved) {
        $this->gen_res_saved = $genResSaved;

        return $this;
    }

    /**
     * Get gen_res_saved
     *
     * @return boolean
     */
    public function getGenResSaved() {
        return $this->gen_res_saved;
    }

    /**
     * Set gen_res_from_date
     *
     * @param DateTime $genResFromDate
     * @return generalReservation
     */
    public function setGenResFromDate($genResFromDate) {
        $this->gen_res_from_date = $genResFromDate;

        return $this;
    }

    /**
     * Get gen_res_from_date
     *
     * @return DateTime
     */
    public function getGenResFromDate() {
        return $this->gen_res_from_date;
    }

    /**
     * Set gen_res_to_date
     *
     * @param DateTime $genResToDate
     * @return generalReservation
     */
    public function setGenResToDate($genResToDate) {
        $this->gen_res_to_date = $genResToDate;

        return $this;
    }

    /**
     * Get gen_res_to_date
     *
     * @return DateTime
     */
    public function getGenResToDate() {
        return $this->gen_res_to_date;
    }

    /**
     * Set gen_res_own_id
     *
     * @param \MyCp\mycpBundle\Entity\ownership $genResOwnId
     * @return generalReservation
     */
    public function setGenResOwnId(\MyCp\mycpBundle\Entity\ownership $genResOwnId = null) {
        $this->gen_res_own_id = $genResOwnId;

        return $this;
    }

    /**
     * Get gen_res_own_id
     *
     * @return \MyCp\mycpBundle\Entity\ownership
     */
    public function getGenResOwnId() {
        return $this->gen_res_own_id;
    }

    /**
     * Set gen_res_total_in_site
     *
     * @param float $genResTotalInSite
     * @return generalReservation
     */
    public function setGenResTotalInSite($genResTotalInSite) {
        $this->gen_res_total_in_site = $genResTotalInSite;

        return $this;
    }

    /**
     * Get gen_res_total_in_site
     *
     * @return float
     */
    public function getGenResTotalInSite() {
        return $this->gen_res_total_in_site;
    }

    /**
     * Set gen_res_hour
     *
     * @param integer $genResHour
     * @return generalReservation
     */
    public function setGenResHour($genResHour) {
        $this->gen_res_hour = $genResHour;

        return $this;
    }

    /**
     * Get gen_res_hour
     *
     * @return integer
     */
    public function getGenResHour() {
        return $this->gen_res_hour;
    }

    /**
     * Set gen_res_arrival_hour
     *
     * @param string $genResArrivalHour
     * @return generalReservation
     */
    public function setGenResArrivalHour($genResArrivalHour) {
        $this->gen_res_arrival_hour = $genResArrivalHour;

        return $this;
    }

    /**
     * Get gen_res_arrival_hour
     *
     * @return string
     */
    public function getGenResArrivalHour() {
        return $this->gen_res_arrival_hour;
    }

    public function getOwn_reservations() {
        return $this->own_reservations;
    }

    public function setOwn_reservations($own_reservations) {
        $this->own_reservations = $own_reservations;
    }

    public function getGenResSyncSt() {
        return $this->gen_res_sync_st;
    }

    public function setGenResSyncSt($sync) {
        $this->gen_res_sync_st = $sync;
    }

    // <editor-fold defaultstate="collapsed" desc="Logic Methods">
    public function getRoomsCount() {
        return count($this->own_reservations);
    }

    public function getAdultsCount() {
        $adults_count = 0;
        foreach ($this->own_reservations as $own_reservation) {
            $adults_count += $own_reservation->getOwnResCountAdults();
        }
        return $adults_count;
    }

    public function getKidsCount() {
        $kids_count = 0;
        foreach ($this->own_reservations as $own_reservation) {
            $kids_count += $own_reservation->getOwnResCountChildrens();
        }
        return $kids_count;
    }

// </editor-fold>

    /**
     * Add own_reservations
     *
     * @param \MyCp\mycpBundle\Entity\ownershipReservation $ownReservations
     * @return generalReservation
     */
    public function addOwnReservation(\MyCp\mycpBundle\Entity\ownershipReservation $ownReservations)
    {
        $this->own_reservations[] = $ownReservations;

        return $this;
    }

    /**
     * Remove own_reservations
     *
     * @param \MyCp\mycpBundle\Entity\ownershipReservation $ownReservations
     */
    public function removeOwnReservation(\MyCp\mycpBundle\Entity\ownershipReservation $ownReservations)
    {
        $this->own_reservations->removeElement($ownReservations);
    }

    /**
     * Get own_reservations
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getOwnReservations()
    {
        return $this->own_reservations;
    }

    /**
     * Checks if the generalReservation has the status "available".
     *
     * @return bool Returns true if the status is "available", false if not.
     */
    public function hasStatusAvailable()
    {
        $status = $this->getGenResStatus();

        return (self::STATUS_AVAILABLE === $status || self::STATUS_PARTIAL_AVAILABLE === $status);
    }
    
    public function hasStatusReserved()
    {
        $status = $this->getGenResStatus();

        return (self::STATUS_RESERVED === $status || self::STATUS_PARTIAL_RESERVED === $status);
    }
}
