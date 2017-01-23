<?php

namespace MyCp\mycpBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * cancelPayment
 *
 * @ORM\Table(name="cancel_payment")
 * @ORM\Entity(repositoryClass="MyCp\mycpBundle\Entity\cancelPaymentRepository")
 */
class cancelPayment
{
    /**
     * @var integer
     *
     * @ORM\Column(name="cancel_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $cancel_id;

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
     * @ORM\ManyToOne(targetEntity="cancelType")
     * @ORM\JoinColumn(name="type", referencedColumnName="cancel_id", nullable=false)
     */
    private $type;


    /**
     * @ORM\ManyToOne(targetEntity="booking")
     * @ORM\JoinColumn(name="booking", referencedColumnName="booking_id", nullable=false)
     */
    private $booking;

    /**
     * @ORM\ManyToOne(targetEntity="user")
     * @ORM\JoinColumn(name="user", referencedColumnName="user_id", nullable=false)
     */
    private $user;


    /**
     * @var boolean
     *
     * @ORM\Column(name="give_tourist", type="boolean")
     */
    private $give_tourist;

    /**
     * @ORM\ManyToMany(targetEntity="ownershipReservation")
     * @ORM\JoinTable(name="cpayment_ownreservation",
     * joinColumns={@ORM\JoinColumn(name="cancel" , referencedColumnName="cancel_id")},
     * inverseJoinColumns={@ORM\JoinColumn(name="ownreservation", referencedColumnName="own_res_id")})
     */
    private $ownreservations;


    /**
     * @ORM\OneToMany(targetEntity="pendingPaytourist", mappedBy="cancel_id")
     */
    private $pending_tourist;


    /**
     * @ORM\OneToMany(targetEntity="pendingPayown", mappedBy="cancel_id")
     */
    private $pending_own;


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
    public function getCancelId()
    {
        return $this->cancel_id;
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
     * Set give_tourist
     *
     * @param boolean $giveTourist
     * @return cancelPayment
     */
    public function setGiveTourist($giveTourist)
    {
        $this->give_tourist = $giveTourist;

        return $this;
    }

    /**
     * Get give_tourist
     *
     * @return boolean
     */
    public function getGiveTourist()
    {
        return $this->give_tourist;
    }





    /**
     * Add pendingTourist
     *
     * @param \MyCp\mycpBundle\Entity\pendingPaytourist $pendingTourist
     *
     * @return user
     */
    public function addPendingTourist(\MyCp\mycpBundle\Entity\pendingPaytourist $pendingTourist)
    {
        $this->pending_tourist[] = $pendingTourist;

        return $this;
    }

    /**
     * Remove pendingTourist
     *
     * @param \MyCp\mycpBundle\Entity\pendingPaytourist $pendingTourist
     */
    public function removePendingTourist(\MyCp\mycpBundle\Entity\userCasa $userUserCasa)
    {
        $this->user_user_casa->removeElement($userUserCasa);
    }

    /**
     * Get userUserCasa
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getUserUserCasa()
    {
        return $this->user_user_casa;
    }

}
