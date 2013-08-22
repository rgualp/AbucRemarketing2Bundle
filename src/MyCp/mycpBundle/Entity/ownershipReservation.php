<?php

namespace MyCp\mycpBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ownershipreservation
 *
 * @ORM\Table(name="ownershipreservation")
 * @ORM\Entity(repositoryClass="MyCp\mycpBundle\Entity\ownershipReservationRepository")
 */
class ownershipReservation
{
    /**
     * @var integer
     *
     * @ORM\Column(name="own_res_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $own_res_id;

    /**
     * @ORM\ManyToOne(targetEntity="generalReservation",inversedBy="ownResGeneralReservation")
     * @ORM\JoinColumn(name="own_res_gen_res_id",referencedColumnName="gen_res_id")
     */
    private $own_res_gen_res_id;

    /**
     * @ORM\ManyToOne(targetEntity="ownership",inversedBy="ownResOwnership")
     * @ORM\JoinColumn(name="own_res_own_id",referencedColumnName="own_id")
     */
    private $own_res_own_id;

    /**
     * @var integer
     *
     * @ORM\Column(name="own_res_selected_room", type="integer")
     */
    private $own_res_selected_room;

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
     * @var \DateTime
     *
     * @ORM\Column(name="own_res_reservation_date", type="date")
     */
    private $own_res_reservation_date;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="own_res_reservation_from_date", type="date")
     */
    private $own_res_reservation_from_date;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="own_res_reservation_to_date", type="date")
     */
    private $own_res_reservation_to_date;

    /**
     * @var integer
     *
     * @ORM\Column(name="own_res_reservation_status", type="integer")
     */
    private $own_res_reservation_status;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="own_res_reservation_status_date", type="date")
     */
    private $own_res_reservation_status_date;

    /**
     * @var integer
     *
     * @ORM\Column(name="own_res_night_price", type="integer")
     */
    private $own_res_night_price;

    /**
     * @var integer
     *
     * @ORM\Column(name="own_res_commission_percent", type="integer")
     */
    private $own_res_commission_percent;



   

    /**
     * Get own_res_id
     *
     * @return integer 
     */
    public function getOwnResId()
    {
        return $this->own_res_id;
    }

    /**
     * Set own_res_selected_room
     *
     * @param integer $ownResSelectedRoom
     * @return ownershipReservation
     */
    public function setOwnResSelectedRoom($ownResSelectedRoom)
    {
        $this->own_res_selected_room = $ownResSelectedRoom;
    
        return $this;
    }

    /**
     * Get own_res_selected_room
     *
     * @return integer 
     */
    public function getOwnResSelectedRoom()
    {
        return $this->own_res_selected_room;
    }

    /**
     * Set own_res_count_adults
     *
     * @param integer $ownResCountAdults
     * @return ownershipReservation
     */
    public function setOwnResCountAdults($ownResCountAdults)
    {
        $this->own_res_count_adults = $ownResCountAdults;
    
        return $this;
    }

    /**
     * Get own_res_count_adults
     *
     * @return integer 
     */
    public function getOwnResCountAdults()
    {
        return $this->own_res_count_adults;
    }

    /**
     * Set own_res_count_childrens
     *
     * @param integer $ownResCountChildrens
     * @return ownershipReservation
     */
    public function setOwnResCountChildrens($ownResCountChildrens)
    {
        $this->own_res_count_childrens = $ownResCountChildrens;
    
        return $this;
    }

    /**
     * Get own_res_count_childrens
     *
     * @return integer 
     */
    public function getOwnResCountChildrens()
    {
        return $this->own_res_count_childrens;
    }

    /**
     * Set own_res_reservation_date
     *
     * @param \DateTime $ownResReservationDate
     * @return ownershipReservation
     */
    public function setOwnResReservationDate($ownResReservationDate)
    {
        $this->own_res_reservation_date = $ownResReservationDate;
    
        return $this;
    }

    /**
     * Get own_res_reservation_date
     *
     * @return \DateTime 
     */
    public function getOwnResReservationDate()
    {
        return $this->own_res_reservation_date;
    }

    /**
     * Set own_res_reservation_from_date
     *
     * @param \DateTime $ownResReservationFromDate
     * @return ownershipReservation
     */
    public function setOwnResReservationFromDate($ownResReservationFromDate)
    {
        $this->own_res_reservation_from_date = $ownResReservationFromDate;
    
        return $this;
    }

    /**
     * Get own_res_reservation_from_date
     *
     * @return \DateTime 
     */
    public function getOwnResReservationFromDate()
    {
        return $this->own_res_reservation_from_date;
    }

    /**
     * Set own_res_reservation_to_date
     *
     * @param \DateTime $ownResReservationToDate
     * @return ownershipReservation
     */
    public function setOwnResReservationToDate($ownResReservationToDate)
    {
        $this->own_res_reservation_to_date = $ownResReservationToDate;
    
        return $this;
    }

    /**
     * Get own_res_reservation_to_date
     *
     * @return \DateTime 
     */
    public function getOwnResReservationToDate()
    {
        return $this->own_res_reservation_to_date;
    }

    /**
     * Set own_res_reservation_status
     *
     * @param integer $ownResReservationStatus
     * @return ownershipReservation
     */
    public function setOwnResReservationStatus($ownResReservationStatus)
    {
        $this->own_res_reservation_status = $ownResReservationStatus;
    
        return $this;
    }

    /**
     * Get own_res_reservation_status
     *
     * @return integer 
     */
    public function getOwnResReservationStatus()
    {
        return $this->own_res_reservation_status;
    }

    /**
     * Set own_res_reservation_status_date
     *
     * @param \DateTime $ownResReservationStatusDate
     * @return ownershipReservation
     */
    public function setOwnResReservationStatusDate($ownResReservationStatusDate)
    {
        $this->own_res_reservation_status_date = $ownResReservationStatusDate;
    
        return $this;
    }

    /**
     * Get own_res_reservation_status_date
     *
     * @return \DateTime 
     */
    public function getOwnResReservationStatusDate()
    {
        return $this->own_res_reservation_status_date;
    }

    /**
     * Set own_res_night_price
     *
     * @param integer $ownResNightPrice
     * @return ownershipReservation
     */
    public function setOwnResNightPrice($ownResNightPrice)
    {
        $this->own_res_night_price = $ownResNightPrice;
    
        return $this;
    }

    /**
     * Get own_res_night_price
     *
     * @return integer 
     */
    public function getOwnResNightPrice()
    {
        return $this->own_res_night_price;
    }

    /**
     * Set own_res_commission_percent
     *
     * @param integer $ownResCommissionPercent
     * @return ownershipReservation
     */
    public function setOwnResCommissionPercent($ownResCommissionPercent)
    {
        $this->own_res_commission_percent = $ownResCommissionPercent;
    
        return $this;
    }

    /**
     * Get own_res_commission_percent
     *
     * @return integer 
     */
    public function getOwnResCommissionPercent()
    {
        return $this->own_res_commission_percent;
    }

    /**
     * Set own_res_gen_res_id
     *
     * @param \MyCp\mycpBundle\Entity\generalReservation $ownResGenResId
     * @return ownershipReservation
     */
    public function setOwnResGenResId(\MyCp\mycpBundle\Entity\generalReservation $ownResGenResId = null)
    {
        $this->own_res_gen_res_id = $ownResGenResId;
    
        return $this;
    }

    /**
     * Get own_res_gen_res_id
     *
     * @return \MyCp\mycpBundle\Entity\generalReservation 
     */
    public function getOwnResGenResId()
    {
        return $this->own_res_gen_res_id;
    }

    /**
     * Set own_res_own_id
     *
     * @param \MyCp\mycpBundle\Entity\ownership $ownResOwnId
     * @return ownershipReservation
     */
    public function setOwnResOwnId(\MyCp\mycpBundle\Entity\ownership $ownResOwnId = null)
    {
        $this->own_res_own_id = $ownResOwnId;
    
        return $this;
    }

    /**
     * Get own_res_own_id
     *
     * @return \MyCp\mycpBundle\Entity\ownership 
     */
    public function getOwnResOwnId()
    {
        return $this->own_res_own_id;
    }
}