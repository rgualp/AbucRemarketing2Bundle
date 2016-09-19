<?php

namespace MyCp\PartnerBundle\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\OneToMany;
use MyCp\mycpBundle\Entity\generalReservation;
use MyCp\mycpBundle\Helpers\SyncStatuses;
use MyCp\PartnerBundle\Entity\paClientReservation;

/**
 * pa_generalreservation
 *
 * @ORM\Table(name="pa_generalreservation")
 * @ORM\Entity(repositoryClass="MyCp\PartnerBundle\Repository\paGeneralReservationRepository")
 */
class paGeneralReservation {

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="MyCp\mycpBundle\Entity\user")
     * @ORM\JoinColumn(name="user",referencedColumnName="user_id")
     */
    private $user;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="date", type="date")
     */
    private $date;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="hour", type="time", nullable=true)
     */
    private $hour;

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
     * @ORM\ManyToOne(targetEntity="MyCp\mycpBundle\Entity\ownership")
     * @ORM\JoinColumn(name="accommodation",referencedColumnName="own_id")
     */
    private $accommodation;

    /**
     * @var integer
     *
     * @ORM\Column(name="total_price", type="float")
     */
    private $totalPrice;

    /**
     * @OneToMany(targetEntity="paOwnershipReservation", mappedBy="gen_res_id")
     */
    private $paOwnershipReservations;

    /**
     * @var string
     *
     * @ORM\Column(name="arrival_hour", type="text",nullable=true)
     */
    private $arrival_hour;

    /**
     * @var integer
     *
     * @ORM\Column(name="nights", type="integer", nullable=true)
     */
    private $nights;

    /**
     * @var array
     *
     * @ORM\Column(name="childrenAges", type="array", nullable=true)
     */
    private $childrenAges;

    /**
     * @ORM\ManyToOne(targetEntity="MyCp\mycpBundle\Entity\serviceFee")
     * @ORM\JoinColumn(name="service_fee",referencedColumnName="id", nullable=true)
     */
    private $service_fee;

    /**
     * Constructor
     */
    public function __construct() {
        $this->paOwnershipReservations = new ArrayCollection();
    }

    /**
     * Get gen_res_id
     *
     * @return integer
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Set gen_res_nights
     *
     * @param integer $genResNights
     * @return generalReservation
     */
    public function setNights($genResNights = null) {
        $this->nights = $genResNights;

        return $this;
    }

    /**
     * Get gen_res_nights
     *
     * @return integer
     */
    public function getNights() {
        return $this->nights;
    }


    /**
     * Set gen_res_user_id
     *
     * @param \MyCp\mycpBundle\Entity\user $genResUserId
     * @return generalReservation
     */
    public function setUser(\MyCp\mycpBundle\Entity\user $genResUserId = null) {
        $this->user = $genResUserId;

        return $this;
    }

    /**
     * Get gen_res_user_id
     *
     * @return \MyCp\mycpBundle\Entity\user
     */
    public function getUser() {
        return $this->user;
    }

    /**
     * Set gen_res_date
     *
     * @param DateTime $genResDate
     * @return generalReservation
     */
    public function setDate($genResDate) {
        $this->date = $genResDate;

        return $this;
    }

    /**
     * Get gen_res_date
     *
     * @return DateTime
     */
    public function getDate() {
        return $this->date;
    }

    /**
     * Set gen_res_from_date
     *
     * @param DateTime $genResFromDate
     * @return generalReservation
     */
    public function setDateFrom($genResFromDate) {
        $this->dateFrom = $genResFromDate;

        return $this;
    }

    /**
     * Get gen_res_from_date
     *
     * @return DateTime
     */
    public function getDateFrom() {
        return $this->dateFrom;
    }

    /**
     * Set gen_res_to_date
     *
     * @param DateTime $genResToDate
     * @return generalReservation
     */
    public function setDateTo($genResToDate) {
        $this->dateTo = $genResToDate;

        return $this;
    }

    /**
     * Get gen_res_to_date
     *
     * @return DateTime
     */
    public function getDateTo() {
        return $this->dateTo;
    }

    /**
     * Set gen_res_own_id
     *
     * @param \MyCp\mycpBundle\Entity\ownership $genResOwnId
     * @return generalReservation
     */
    public function setAccommodation(\MyCp\mycpBundle\Entity\ownership $genResOwnId = null) {
        $this->accommodation = $genResOwnId;

        return $this;
    }

    /**
     * Get gen_res_own_id
     *
     * @return \MyCp\mycpBundle\Entity\ownership
     */
    public function getAccommodation() {
        return $this->accommodation;
    }

    /**
     * Set totalPrice
     *
     * @param float $genResTotalInSite
     * @return generalReservation
     */
    public function setTotalPrice($genResTotalInSite) {
        $this->totalPrice = $genResTotalInSite;

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

    /**
     * Set gen_res_hour
     *
     * @param integer $genResHour
     * @return generalReservation
     */
    public function setHour($genResHour) {
        $this->hour = $genResHour;

        return $this;
    }

    /**
     * Get gen_res_hour
     *
     * @return integer
     */
    public function getHour() {
        return $this->hour;
    }

    /**
     * Set gen_res_arrival_hour
     *
     * @param string $genResArrivalHour
     * @return generalReservation
     */
    public function setArrivalHour($genResArrivalHour) {
        $this->arrival_hour = $genResArrivalHour;

        return $this;
    }

    /**
     * Get gen_res_arrival_hour
     *
     * @return string
     */
    public function getArrivalHour() {
        return $this->arrival_hour;
    }

    public function getPaOwnershipReservations() {
        return $this->paOwnershipReservations;
    }

    public function setPaOwnershipReservations($own_reservations) {
        $this->paOwnershipReservations = $own_reservations;
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
     * Add paOwnershipReservations
     *
     * @param \MyCp\PartnerBundle\Entity\paOwnershipReservation $ownReservations
     * @return generalReservation
     */
    public function addPaOwnershipReservation(\MyCp\PartnerBundle\Entity\paOwnershipReservation $ownReservations) {
        $this->paOwnershipReservations[] = $ownReservations;

        return $this;
    }

    /**
     * Remove paOwnershipReservations
     *
     * @param \MyCp\PartnerBundle\Entity\paOwnershipReservation $ownReservations
     */
    public function removePaOwnershipReservation(\MyCp\PartnerBundle\Entity\paOwnershipReservation $ownReservations) {
        $this->paOwnershipReservations->removeElement($ownReservations);
    }


    public function createReservation() {
        $genRes = new generalReservation();
        $genRes->setGenResArrivalHour($this->hour);
        $genRes->setGenResDate(new \DateTime(date('Y-m-d')));
        $genRes->setGenResFromDate($this->dateFrom);
        $genRes->setGenResHour(date('G'));
        $genRes->setGenResOwnId($this->accommodation);
        $genRes->setGenResSaved(false);
        $genRes->setGenResStatus(generalReservation::STATUS_PENDING);
        $genRes->setGenResStatusDate(new \DateTime(date('Y-m-d')));
        $genRes->setGenResSyncSt(SyncStatuses::ADDED);
        $genRes->setGenResToDate($this->dateTo);
        $genRes->setGenResTotalInSite($this->totalPrice);
        $genRes->setGenResUserId($this->user);
        $genRes->setGenResDateHour(new \DateTime(date('H:i:s')));
        $genRes->setServiceFee($this->service_fee);
        $genRes->setChildrenAges($this->childrenAges);
        $genRes->setGenResNights($this->nights);

        return $genRes;
    }


    /**
     * @return array
     */
    public function getChildrenAges()
    {
        return $this->childrenAges;
    }

    /**
     * @param array $childrenAges
     * @return this
     */
    public function setChildrenAges($childrenAges)
    {
        $this->childrenAges = $childrenAges;
        return $this;
    }


    /**
     * @return mixed
     */
    public function getServiceFee()
    {
        return $this->service_fee;
    }

    /**
     * @param mixed $service_fee
     * @return mixed
     */
    public function setServiceFee($service_fee)
    {
        $this->service_fee = $service_fee;
        return $this;
    }


}
