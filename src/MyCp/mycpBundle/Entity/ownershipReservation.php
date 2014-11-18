<?php

namespace MyCp\mycpBundle\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use MyCp\mycpBundle\Helpers\SyncStatuses;

/**
 * ownershipreservation
 *
 * @ORM\Table(name="ownershipreservation")
 * @ORM\Entity(repositoryClass="MyCp\mycpBundle\Entity\ownershipReservationRepository")
 */
class ownershipReservation {
    /**
     * All allowed statuses
     */

    const STATUS_PENDING = 0;
    const STATUS_AVAILABLE = 1;
    const STATUS_AVAILABLE2 = 2;
    const STATUS_NOT_AVAILABLE = 3;
    const STATUS_CANCELLED = 4;
    const STATUS_RESERVED = 5;

    /**
     * Contains all possible statuses
     *
     * @var array
     */
    private $statuses = array(
        self::STATUS_PENDING,
        self::STATUS_AVAILABLE,
        self::STATUS_AVAILABLE2,
        self::STATUS_NOT_AVAILABLE,
        self::STATUS_CANCELLED,
        self::STATUS_RESERVED,
    );

    /**
     * @var integer
     *
     * @ORM\Column(name="own_res_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $own_res_id;

    /**
     * @ORM\ManyToOne(targetEntity="generalReservation",inversedBy="own_reservations")
     * @ORM\JoinColumn(name="own_res_gen_res_id",referencedColumnName="gen_res_id")
     */
    private $own_res_gen_res_id;

    /**
     * @var integer
     *
     * @ORM\Column(name="own_res_selected_room_id", type="integer")
     */
    private $own_res_selected_room_id;

    /**
     * @var string
     *
     * @ORM\Column(name="own_res_room_price_up", type="string", length=255, nullable=true)
     */
    private $own_res_room_price_up;

    /**
     * @var string
     *
     * @ORM\Column(name="own_res_room_price_down", type="string", length=255, nullable=true)
     */
    private $own_res_room_price_down;

    /**
     * @var string
     *
     * @ORM\Column(name="own_res_room_price_special", type="string", length=255, nullable=true)
     */
    private $own_res_room_price_special;

    /**
     * @var integer
     *
     * @ORM\Column(name="own_res_count_adults", type="integer")
     */
    private $own_res_count_adults;

    /**
     * @var integer
     *
     * @ORM\Column(name="own_res_count_childrens", type="integer")
     */
    private $own_res_count_childrens;

    /**
     * @var floats
     *
     * @ORM\Column(name="own_res_night_price", type="float", precision=2)
     */
    private $own_res_night_price;

    /**
     * @var integer
     *
     * @ORM\Column(name="own_res_status", type="integer")
     */
    private $own_res_status;

    /**
     * @var string
     *
     * @ORM\Column(name="own_res_room_type", type="string", length=255, nullable=true)
     */
    private $own_res_room_type;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="own_res_reservation_from_date", type="date")
     */
    private $own_res_reservation_from_date;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="own_res_reservation_to_date", type="date")
     */
    private $own_res_reservation_to_date;

    /**
     * @ORM\ManyToOne(targetEntity="booking",inversedBy="booking_own_reservations")
     * @ORM\JoinColumn(name="own_res_reservation_booking",referencedColumnName="booking_id", nullable=true)
     */
    private $own_res_reservation_booking;

    /**
     * @var float
     *
     * @ORM\Column(name="own_res_total_in_site", type="float", precision=2)
     */
    private $own_res_total_in_site;

    /**
     * @var boolean
     *
     * @ORM\Column(name="own_res_sync_st", type="integer")
     */
    private $own_res_sync_st;

    /**
     * Constructor
     */
    public function __construct() {
        $this->own_res_sync_st = SyncStatuses::ADDED;
        $this->own_res_status = ownershipReservation::STATUS_PENDING;
    }

    /**
     * Get own_res_id
     *
     * @return integer
     */
    public function getOwnResId() {
        return $this->own_res_id;
    }

    /**
     * Set own_res_reservation_hour
     *
     * @param varchar $ownResHour
     * @return ownershipReservation
     */
    public function setOwnResHour($ownResHour = null) {
        $this->own_res_reservation_hour = $ownResHour;

        return $this;
    }

    /**
     * Get own_res_reservation_hour
     *
     * @return varchar
     */
    public function getOwnResHour() {
        return $this->own_res_reservation_hour;
    }

    /**
     * Set own_res_selected_room_id
     *
     * @param integer $ownResSelectedRoomId
     * @return ownershipReservation
     */
    public function setOwnResSelectedRoomId($ownResSelectedRoomId) {
        $this->own_res_selected_room_id = $ownResSelectedRoomId;

        return $this;
    }

    /**
     * Get own_res_selected_room_id
     *
     * @return integer
     */
    public function getOwnResSelectedRoomId() {
        return $this->own_res_selected_room_id;
    }

    /**
     * Set own_res_room_price_down
     *
     * @param integer $ownResRoomPriceDown
     * @return ownershipReservation
     */
    public function setOwnResRoomPriceDown($ownResRoomPriceDown) {
        $this->own_res_room_price_down = $ownResRoomPriceDown;

        return $this;
    }

    /**
     * Get own_res_room_price_down
     *
     * @return string
     */
    public function getOwnResRoomPriceDown() {
        return $this->own_res_room_price_down;
    }

    /**
     * Set own_res_room_price_up
     *
     * @param integer $ownResRoomPriceUp
     * @return ownershipReservation
     */
    public function setOwnResRoomPriceUp($ownResRoomPriceUp) {
        $this->own_res_room_price_up = $ownResRoomPriceUp;

        return $this;
    }

    /**
     * Get own_res_room_price_up
     *
     * @return string
     */
    public function getOwnResRoomPriceUp() {
        return $this->own_res_room_price_up;
    }

    /**
     * Set own_res_room_price_special
     *
     * @param integer $ownResRoomPriceSpecial
     * @return ownershipReservation
     */
    public function setOwnResRoomPriceSpecial($ownResRoomPriceSpecial) {
        $this->own_res_room_price_special = $ownResRoomPriceSpecial;

        return $this;
    }

    /**
     * Get own_res_room_price_special
     *
     * @return string
     */
    public function getOwnResRoomPriceSpecial() {
        return $this->own_res_room_price_special;
    }

    /**
     * Set own_res_count_adults
     *
     * @param integer $ownResCountAdults
     * @return ownershipReservation
     */
    public function setOwnResCountAdults($ownResCountAdults) {
        $this->own_res_count_adults = $ownResCountAdults;

        return $this;
    }

    /**
     * Get own_res_count_adults
     *
     * @return integer
     */
    public function getOwnResCountAdults() {
        return $this->own_res_count_adults;
    }

    /**
     * Set own_res_count_childrens
     *
     * @param integer $ownResCountChildrens
     * @return ownershipReservation
     */
    public function setOwnResCountChildrens($ownResCountChildrens) {
        $this->own_res_count_childrens = $ownResCountChildrens;

        return $this;
    }

    /**
     * Get own_res_count_childrens
     *
     * @return integer
     */
    public function getOwnResCountChildrens() {
        return $this->own_res_count_childrens;
    }

    /**
     * Set own_res_night_price
     *
     * @param integer $ownResNightPrice
     * @return ownershipReservation
     */
    public function setOwnResNightPrice($ownResNightPrice) {
        $this->own_res_night_price = $ownResNightPrice;

        return $this;
    }

    /**
     * Get own_res_night_price
     *
     * @return integer
     */
    public function getOwnResNightPrice() {
        return $this->own_res_night_price;
    }

    /**
     * Set own_res_gen_res_id
     *
     * @param \MyCp\mycpBundle\Entity\generalReservation $ownResGenResId
     * @return ownershipReservation
     */
    public function setOwnResGenResId(\MyCp\mycpBundle\Entity\generalReservation $ownResGenResId = null) {
        $this->own_res_gen_res_id = $ownResGenResId;

        return $this;
    }

    /**
     * Get own_res_gen_res_id
     *
     * @return \MyCp\mycpBundle\Entity\generalReservation
     */
    public function getOwnResGenResId() {
        return $this->own_res_gen_res_id;
    }

    /**
     * Set own_res_status
     *
     * @param integer $ownResStatus
     * @return ownershipReservation
     * @throws \InvalidArgumentException
     */
    public function setOwnResStatus($ownResStatus) {
        if (!in_array($ownResStatus, $this->statuses)) {
            throw new \InvalidArgumentException("Status $ownResStatus not allowed");
        }

        $this->own_res_status = $ownResStatus;

        return $this;
    }

    /**
     * Get own_res_status
     *
     * @return integer
     */
    public function getOwnResStatus() {
        return $this->own_res_status;
    }

    /**
     * Set own_res_room_type
     *
     * @param string $ownResRoomType
     * @return ownershipReservation
     */
    public function setOwnResRoomType($ownResRoomType) {
        $this->own_res_room_type = $ownResRoomType;

        return $this;
    }

    /**
     * Get own_res_room_type
     *
     * @return string
     */
    public function getOwnResRoomType() {
        return $this->own_res_room_type;
    }

    /**
     * Set own_res_reservation_from_date
     *
     * @param DateTime $ownResReservationFromDate
     * @return ownershipReservation
     */
    public function setOwnResReservationFromDate($ownResReservationFromDate) {
        $this->own_res_reservation_from_date = $ownResReservationFromDate;

        return $this;
    }

    /**
     * Get own_res_reservation_from_date
     *
     * @return DateTime
     */
    public function getOwnResReservationFromDate() {
        return $this->own_res_reservation_from_date;
    }

    /**
     * Set own_res_reservation_to_date
     *
     * @param DateTime $ownResReservationToDate
     * @return ownershipReservation
     */
    public function setOwnResReservationToDate($ownResReservationToDate) {
        $this->own_res_reservation_to_date = $ownResReservationToDate;

        return $this;
    }

    /**
     * Get own_res_reservation_to_date
     *
     * @return DateTime
     */
    public function getOwnResReservationToDate() {
        return $this->own_res_reservation_to_date;
    }

    /**
     * Set own_res_total_in_site
     *
     * @param float $ownResTotalInSite
     * @return ownershipReservation
     */
    public function setOwnResTotalInSite($ownResTotalInSite) {
        $this->own_res_total_in_site = $ownResTotalInSite;

        return $this;
    }

    /**
     * Get own_res_total_in_site
     *
     * @return float
     */
    public function getOwnResTotalInSite() {
        return $this->own_res_total_in_site;
    }

    /**
     * Set own_res_reservation_booking
     *
     * @param \MyCp\mycpBundle\Entity\booking $ownResReservationBooking
     * @return ownershipReservation
     */
    public function setOwnResReservationBooking(\MyCp\mycpBundle\Entity\booking $ownResReservationBooking = null) {
        $this->own_res_reservation_booking = $ownResReservationBooking;

        return $this;
    }

    /**
     * Get own_res_reservation_booking
     *
     * @return \MyCp\mycpBundle\Entity\booking
     */
    public function getOwnResReservationBooking() {
        return $this->own_res_reservation_booking;
    }

    public function getOwnResSyncSt() {
        return $this->own_res_sync_st;
    }

    public function setOwnResSyncSt($own_sync_st) {
        $this->own_res_sync_st = $own_sync_st;
    }

    /**
     * Checks if the ownershipReservation has the status "available".
     *
     * @return bool Returns true if the status is "available", false if not.
     */
    public function hasStatusAvailable() {
        $status = $this->getOwnResStatus();

        return self::STATUS_AVAILABLE === $status || self::STATUS_AVAILABLE2 === $status;
    }

    public function getTripleRoomCharged() {
        return ($this->own_res_room_type == "Habitación Triple") &&
                ($this->own_res_count_adults + $this->own_res_count_childrens >= 3);
    }

}
