<?php

namespace MyCp\mycpBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * unavailabilityDetails
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class unavailabilityDetails {

    /**
     * @var integer
     *
     * @ORM\Column(name="ud_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $ud_id;

    /**
     * @var integer
     *
     * @ORM\Column(name="ud_room_num", type="integer")
     */
    private $ud_room_num;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="ud_from_date", type="date")
     */
    private $ud_from_date;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="ud_to_date", type="date")
     */
    private $ud_to_date;

    /**
     * @var text
     *
     * @ORM\Column(name="ud_reason", type="text")
     */
    private $ud_reason;

    /**
     * @ORM\ManyToOne(targetEntity="ownership")
     * @ORM\JoinColumn(name="ownership_id",referencedColumnName="own_id")
     */
    private $ownership_id;

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId() {
        return $this->ud_id;
    }

    /**
     * Set ud_id
     *
     * @param integer $udId
     * @return unavailabilityDetails
     */
    public function setUdId($udId) {
        $this->ud_id = $udId;

        return $this;
    }

    /**
     * Get ud_id
     *
     * @return integer 
     */
    public function getUdId() {
        return $this->ud_id;
    }

    /**
     * Set room_num
     *
     * @param integer $roomNum
     * @return unavailabilityDetails
     */
    public function setRoomNum($roomNum) {
        $this->ud_room_num = $roomNum;

        return $this;
    }

    /**
     * Get room_num
     *
     * @return integer 
     */
    public function getRoomNum() {
        return $this->ud_room_num;
    }

    /**
     * Set ud_from_date
     *
     * @param \DateTime $udFromDate
     * @return unavailabilityDetails
     */
    public function setUdFromDate($udFromDate) {
        $this->ud_from_date = $udFromDate;

        return $this;
    }

    /**
     * Get ud_from_date
     *
     * @return \DateTime 
     */
    public function getUdFromDate() {
        return $this->ud_from_date;
    }

    /**
     * Set ud_to_date
     *
     * @param \DateTime $udToDate
     * @return unavailabilityDetails
     */
    public function setUdToDate($udToDate) {
        $this->ud_to_date = $udToDate;

        return $this;
    }

    /**
     * Get ud_to_date
     *
     * @return \DateTime 
     */
    public function getUdToDate() {
        return $this->ud_to_date;
    }

    /**
     * Set ud_reason
     *
     * @param string $udReason
     * @return unavailabilityDetails
     */
    public function setUdReason($udReason) {
        $this->ud_reason = $udReason;

        return $this;
    }

    /**
     * Get ud_reason
     *
     * @return string 
     */
    public function getUdReason() {
        return $this->ud_reason;
    }

    /**
     * Set ownership_id
     *
     * @param integer $ownershipId
     * @return unavailabilityDetails
     */
    public function setOwnership($ownershipId) {
        $this->ownership_id = $ownershipId;

        return $this;
    }

    /**
     * Get ownership_id
     *
     * @return integer 
     */
    public function getOwnership() {
        return $this->ownership_id;
    }

    /**
     * Set ud_room_num
     *
     * @param integer $udRoomNum
     * @return unavailabilityDetails
     */
    public function setUdRoomNum($udRoomNum) {
        $this->ud_room_num = $udRoomNum;

        return $this;
    }

    /**
     * Get ud_room_num
     *
     * @return integer 
     */
    public function getUdRoomNum() {
        return $this->ud_room_num;
    }

    // <editor-fold defaultstate="collapsed" desc="Logic Methods">
    public function isPast() {
        $dateUnixSeconds = strtotime(date($this->ud_to_date->format('Y-m-d H:m:s')));
        return $dateUnixSeconds < strtotime("now");
    }

// </editor-fold>
}