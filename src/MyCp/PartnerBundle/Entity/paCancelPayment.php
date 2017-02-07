<?php

namespace MyCp\PartnerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * paCancelPayment
 *
 * @ORM\Table(name="pa_cancel_payment")
 * @ORM\Entity(repositoryClass="MyCp\PartnerBundle\Repository\paCancelPaymentRepository")
 */
class paCancelPayment
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
     * @var string
     *
     * @ORM\Column(name="reason", type="string", length=500)
     */
    private $reason;


    /**
     * @var datetime
     *
     * @ORM\Column(name="cancel_date", type="datetime")
     */
    private $cancel_date;

    /**
     * @ORM\ManyToOne(targetEntity="MyCp\mycpBundle\Entity\nomenclator")
     * @ORM\JoinColumn(name="type", referencedColumnName="nom_id", nullable=false)
     */
    private $type;


    /**
     * @ORM\ManyToOne(targetEntity="MyCp\mycpBundle\Entity\booking")
     * @ORM\JoinColumn(name="booking", referencedColumnName="booking_id", nullable=false)
     */
    private $booking;

    /**
     * @ORM\ManyToOne(targetEntity="MyCp\mycpBundle\Entity\user")
     * @ORM\JoinColumn(name="user", referencedColumnName="user_id", nullable=false)
     */
    private $user;


    /**
     * @var boolean
     *
     * @ORM\Column(name="give_agency", type="boolean")
     */
    private $give_agency;

    /**
     * @ORM\ManyToMany(targetEntity="MyCp\mycpBundle\Entity\ownershipReservation")
     * @ORM\JoinTable(name="agency_cancel_payment_ownreservation",
     * joinColumns={@ORM\JoinColumn(name="cancel" , referencedColumnName="id")},
     * inverseJoinColumns={@ORM\JoinColumn(name="ownreservation", referencedColumnName="own_res_id")})
     */
    private $ownreservations;
    /**
     * Constructor
     */
    public function __construct() {
        $this->ownreservations = new ArrayCollection();
    }
    /**
     * Add ownreservations
     *
     * @param \MyCp\mycpBundle\Entity\ownershipReservation $ownreservation
     * @return cancelPayment
     */
    public function addOwnershipReservation(\MyCp\mycpBundle\Entity\ownershipReservation $ownreservation)
    {
        $this->ownreservations[] = $ownreservation;

        return $this;
    }

    /**
     * Remove ownreservations
     *
     * @param \MyCp\mycpBundle\Entity\ownershipReservation $ownreservation
     */
    public function removeOwnershipReservation(\MyCp\mycpBundle\Entity\ownershipReservation $ownreservation)
    {
        $this->ownreservations->removeElement($ownreservation);
    }

    /**
     * Get ownreservations
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getOwnreservations()
    {
        return $this->ownreservations;
    }


    /**
     * Get cancel_id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set reason
     *
     * @param string $reason
     * @return cancelPayment
     */
    public function setReason($reason)
    {
        $this->reason = $reason;
    
        return $this;
    }

    /**
     * Get reason
     *
     * @return string 
     */
    public function getReason()
    {
        return $this->reason;
    }

    /**
     * Set cancel_date
     *
     * @param \DateTime $cancelDate
     * @return cancelPayment
     */
    public function setCancelDate($cancelDate)
    {
        $this->cancel_date = $cancelDate;

        return $this;
    }

    /**
     * Get cancel_date
     *
     * @return datetime
     */
    public function getCancelDate()
    {
        return $this->cancel_date;
    }

    /**
     * Get type
     *
     * @return cancelType
     */
    public function getType() {
        return $this->type;
    }

    /**
     * Set type
     *
     * @param cancelType $type
     * @return cancelPayment
     */
    public function setType($type) {
        $this->type = $type;
        return $this;
    }

    /**
     * Get booking
     *
     * @return booking
     */
    public function getBooking() {
        return $this->booking;
    }

    /**
     * Set type
     *
     * @param booking $booking
     * @return cancelPayment
     */
    public function setBooking($booking) {
        $this->booking = $booking;
        return $this;
    }

    /**
     * Get user
     *
     * @return user
     */
    public function getUser() {
        return $this->user;
    }

    /**
     * Set user
     *
     * @param user $user
     * @return cancelPayment
     */
    public function setUser($user) {
        $this->user = $user;
        return $this;
    }
    /**
     * Set give_agency
     *
     * @param boolean $giveAgency
     * @return mixed
     */
    public function setGiveAgency($giveAgency)
    {
        $this->give_agency = $giveAgency;

        return $this;
    }

    /**
     * Get give_agency
     *
     * @return boolean
     */
    public function getGiveAgency()
    {
        return $this->give_agency;
    }

}
