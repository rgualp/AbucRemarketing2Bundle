<?php

namespace MyCp\mycpBundle\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use MyCp\mycpBundle\Helpers\SyncStatuses;

/**
 * unavailabilityDetails
 *
 * @ORM\Table(name="unavailabilitydetails")
 * @ORM\Entity(repositoryClass="MyCp\mycpBundle\Entity\unavailabilityDetailsRepository")
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
     * @var DateTime
     *
     * @ORM\Column(name="ud_from_date", type="date")
     */
    private $ud_from_date;

    /**
     * @var DateTime
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
     * @var integer
     * @ORM\Column(name="ud_sync_st", type="integer")
     */
    private $ud_sync_st;

    /**
     * @ORM\ManyToOne(targetEntity="room")
     * @ORM\JoinColumn(name="room_id",referencedColumnName="room_id")
     */
    private $room;

    public function __construct() {
        $this->ud_sync_st = SyncStatuses::ADDED;
    }

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
     * Set ud_from_date
     *
     * @param DateTime $udFromDate
     * @return unavailabilityDetails
     */
    public function setUdFromDate($udFromDate) {
        $this->ud_from_date = $udFromDate;

        return $this;
    }

    /**
     * Get ud_from_date
     *
     * @return DateTime 
     */
    public function getUdFromDate() {
        return $this->ud_from_date;
    }

    /**
     * Set ud_to_date
     *
     * @param DateTime $udToDate
     * @return unavailabilityDetails
     */
    public function setUdToDate($udToDate) {
        $this->ud_to_date = $udToDate;

        return $this;
    }

    /**
     * Get ud_to_date
     *
     * @return DateTime 
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

    public function getSyncSt() {
        return $this->ud_sync_st;
    }

    public function setSyncSt($ud_sync_st) {
        $this->ud_sync_st = $ud_sync_st;
    }
    
    public function getRoom() {
        return $this->room;
    }

    public function setRoom($room) {
        $this->room = $room;
    }
    
}