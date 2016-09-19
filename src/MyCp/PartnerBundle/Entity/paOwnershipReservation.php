<?php

namespace MyCp\PartnerBundle\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use MyCp\mycpBundle\Entity\ownershipReservation;
use MyCp\mycpBundle\Helpers\SyncStatuses;

/**
 * pa_ownershipreservation
 *
 * @ORM\Table(name="pa_ownershipreservation")
 * @ORM\Entity(repositoryClass="MyCp\PartnerBundle\Repository\paOwnershipReservationRepository")
 */
class paOwnershipReservation {

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="paGeneralReservation",inversedBy="paOwnershipReservations")
     * @ORM\JoinColumn(name="gen_res_id",referencedColumnName="id")
     */
    private $paGenRes;

    /**
     * @ORM\ManyToOne(targetEntity="MyCp\mycpBundle\Entity\room")
     * @ORM\JoinColumn(name="room",referencedColumnName="room_id")
     */
    private $room;

    /**
     * @var string
     *
     * @ORM\Column(name="room_price_up", type="string", length=255, nullable=true)
     */
    private $roomPriceUp;

    /**
     * @var string
     *
     * @ORM\Column(name="room_price_down", type="string", length=255, nullable=true)
     */
    private $roomPriceDown;

    /**
     * @var string
     *
     * @ORM\Column(name="room_price_special", type="string", length=255, nullable=true)
     */
    private $roomPriceSpecial;

    /**
     * @var integer
     *
     * @ORM\Column(name="adults", type="integer")
     */
    private $adults;

    /**
     * @var integer
     *
     * @ORM\Column(name="children", type="integer")
     */
    private $children;

    /**
     * @var string
     *
     * @ORM\Column(name="room_type", type="string", length=255, nullable=true)
     */
    private $roomType;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="date_from", type="date")
     */
    private $dateFrom;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="date_to", type="date")
     */
    private $dateTo;

    /**
     * @var float
     *
     * @ORM\Column(name="total_price", type="float", precision=2)
     */
    private $totalPrice;


    /**
     * @var integer
     *
     * @ORM\Column(name="nights", type="integer", nullable=true)
     */
    private $nights;


    /**
     * Get own_res_id
     *
     * @return integer
     */
    public function getId() {
        return $this->id;
    }


    /**
     * Set own_res_nights
     *
     * @param integer $ownResNights
     * @return ownershipReservation
     */
    public function setNights($ownResNights = null) {
        $this->nights = $ownResNights;

        return $this;
    }

    /**
     * Get own_res_nights
     *
     * @return integer
     */
    public function getNights() {
        return $this->nights;
    }

    /**
     * Set own_res_selected_room_id
     *
     * @param integer $ownResSelectedRoomId
     * @return ownershipReservation
     */
    public function setRoom($ownResSelectedRoomId) {
        $this->room = $ownResSelectedRoomId;

        return $this;
    }

    /**
     * Get own_res_selected_room_id
     *
     * @return integer
     */
    public function getRoom() {
        return $this->room;
    }

    /**
     * Set roomPriceDown
     *
     * @param integer $ownResRoomPriceDown
     * @return ownershipReservation
     */
    public function setRoomPriceDown($ownResRoomPriceDown) {
        $this->roomPriceDown = $ownResRoomPriceDown;

        return $this;
    }

    /**
     * Get roomPriceDown
     *
     * @return string
     */
    public function getRoomPriceDown() {
        return $this->roomPriceDown;
    }

    /**
     * Set roomPriceUp
     *
     * @param integer $ownResRoomPriceUp
     * @return ownershipReservation
     */
    public function setRoomPriceUp($ownResRoomPriceUp) {
        $this->roomPriceUp = $ownResRoomPriceUp;

        return $this;
    }

    /**
     * Get roomPriceUp
     *
     * @return string
     */
    public function getRoomPriceUp() {
        return $this->roomPriceUp;
    }

    /**
     * Set roomPriceSpecial
     *
     * @param integer $ownResRoomPriceSpecial
     * @return ownershipReservation
     */
    public function setRoomPriceSpecial($ownResRoomPriceSpecial) {
        $this->roomPriceSpecial = $ownResRoomPriceSpecial;

        return $this;
    }

    /**
     * Get own_res_room_price_special
     *
     * @return string
     */
    public function getRoomPriceSpecial() {
        return $this->roomPriceSpecial;
    }

    /**
     * Set adults
     *
     * @param integer $ownResCountAdults
     * @return ownershipReservation
     */
    public function setAdults($ownResCountAdults) {
        $this->adults = $ownResCountAdults;

        return $this;
    }

    /**
     * Get adults
     *
     * @return integer
     */
    public function getAdults() {
        return $this->adults;
    }

    /**
     * Set children
     *
     * @param integer $ownResCountChildrens
     * @return ownershipReservation
     */
    public function setChildren($ownResCountChildrens) {
        $this->children = $ownResCountChildrens;

        return $this;
    }

    /**
     * Get children
     *
     * @return integer
     */
    public function getChildren() {
        return $this->children;
    }

    /**
     * Set own_res_gen_res_id
     *
     * @param paGeneralReservation $ownResGenResId
     * @return ownershipReservation
     */
    public function setPaGenResId(paGeneralReservation $ownResGenResId = null) {
        $this->paGenRes = $ownResGenResId;

        return $this;
    }

    /**
     * Get own_res_gen_res_id
     *
     * @return paGeneralReservation
     */
    public function getGenResId() {
        return $this->paGenRes;
    }

    /**
     * Set roomType
     *
     * @param string $ownResRoomType
     * @return ownershipReservation
     */
    public function setRoomType($ownResRoomType) {
        $this->roomType = $ownResRoomType;

        return $this;
    }

    /**
     * Get roomType
     *
     * @return string
     */
    public function getRoomType() {
        return $this->roomType;
    }

    /**
     * Set own_res_reservation_from_date
     *
     * @param DateTime $ownResReservationFromDate
     * @return ownershipReservation
     */
    public function setDateFrom($ownResReservationFromDate) {
        $this->dateFrom = $ownResReservationFromDate;

        return $this;
    }

    /**
     * Get own_res_reservation_from_date
     *
     * @return DateTime
     */
    public function getDateFrom() {
        return $this->dateFrom;
    }

    /**
     * Set dateTo
     *
     * @param DateTime $ownResReservationToDate
     * @return ownershipReservation
     */
    public function setDateTo($ownResReservationToDate) {
        $this->dateTo = $ownResReservationToDate;

        return $this;
    }

    /**
     * Get dateTo
     *
     * @return DateTime
     */
    public function getDateTo() {
        return $this->dateTo;
    }

    /**
     * Set totalPrice
     *
     * @param float $ownResTotalInSite
     * @return ownershipReservation
     */
    public function setTotalPrice($ownResTotalInSite) {
        $this->totalPrice = $ownResTotalInSite;

        return $this;
    }

    /**
     * Get totalPrice
     *
     * @return float
     */
    public function getTotalPrice() {
        return $this->totalPrice;
    }


    public function createReservation()
    {
        $ownRes = new ownershipReservation();
        $ownRes->setOwnResCountAdults($this->adults);
        $ownRes->setOwnResCountChildrens($this->children);
        $ownRes->setOwnResReservationFromDate($this->dateFrom);
        $ownRes->setOwnResReservationToDate($this->dateTo);
        $ownRes->setOwnResRoomPriceDown($this->roomPriceDown);
        $ownRes->setOwnResRoomPriceSpecial($this->roomPriceSpecial);
        $ownRes->setOwnResRoomPriceUp($this->roomPriceUp);
        $ownRes->setOwnResRoomType($this->roomType);
        $ownRes->setOwnResSelectedRoomId($this->room);
        $ownRes->setOwnResStatus(ownershipReservation::STATUS_PENDING);
        $ownRes->setOwnResSyncSt(SyncStatuses::ADDED);
        $ownRes->setOwnResTotalInSite($this->totalPrice);
        $ownRes->setOwnResNights($this->nights);
        $ownRes->setOwnResNightPrice(0);

        return $ownRes;
    }


}
