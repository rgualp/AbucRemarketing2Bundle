<?php

namespace MyCp\mycpBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * pendingPayment
 *
 * @ORM\Table(name="pending_payment")
 * @ORM\Entity(repositoryClass="MyCp\mycpBundle\Entity\pendingPaymentRepository")
 */
class pendingPayment
{
    /**
     * @var integer
     *
     * @ORM\Column(name="pending_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="generalreservation")
     * @ORM\JoinColumn(name="reservation", referencedColumnName="gen_res_id")
     */
    private $reservation;

    /**
     * @ORM\ManyToOne(targetEntity="booking")
     * @ORM\JoinColumn(name="booking", referencedColumnName="booking_id")
     */
    private $booking;


    /**
     * @ORM\ManyToOne(targetEntity="cancelPayment",cascade={"persist"})
     * @ORM\JoinColumn(name="cancel_id", referencedColumnName="cancel_id", nullable=true)
     */
    private $cancel_id;


    /**
     * @var string
     *
     * @ORM\Column(name="reason", type="string", length=500, nullable=true)
     */
    private $reason;


    /**
     * @var datetime
     *
     * @ORM\Column(name="payment_date", type="datetime")
     */
    private $payment_date;

    /**
     * @var datetime
     *
     * @ORM\Column(name="register_date", type="datetime")
     */
    private $register_date;

    /**
     * @var float
     *
     * @ORM\Column(name="amount", type="float", precision=2, nullable=true)
     */
    private $amount;

    /**
     * @ORM\ManyToOne(targetEntity="user")
     * @ORM\JoinColumn(name="user", referencedColumnName="user_id", nullable=false)
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity="nomenclator",inversedBy="")
     * @ORM\JoinColumn(name="type",referencedColumnName="nom_id")
     */
    private $type;

    /**
     * @ORM\ManyToOne(targetEntity="nomenclator",inversedBy="")
     * @ORM\JoinColumn(name="status",referencedColumnName="nom_id")
     */
    private $status;

    /**
     * Get pending_id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get reservation
     *
     * @return ownership
     */
    public function getReservation() {
        return $this->reservation;
    }

    /**
     * Set accommodation
     *
     * @param generalReservation $reservation
     * @return pendingPayment
     */
    public function setReservation($reservation) {
        $this->reservation = $reservation;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getBooking()
    {
        return $this->booking;
    }

    /**
     * @param booking $booking
     * @return pendingPayment
     */
    public function setBooking($booking)
    {
        $this->booking = $booking;
        return $this;
    }



    /**
     * Get cancel_id
     *
     * @return cancelPayment
     */
    public function getCancelId() {
        return $this->cancel_id;
    }

    /**
     * Set cancel_id
     *
     * @param cancelPayment $cancel_id
     * @return pendingPayment
     */
    public function setCancelId($cancel_id) {
        $this->cancel_id = $cancel_id;
        return $this;
    }

    /**
     * Set reason
     *
     * @param string $reason
     * @return pendingPayment
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
     * Set payment_date
     *
     * @param \DateTime $paymentDate
     * @return pendingPayment
     */
    public function setPaymentDate($paymentDate)
    {
        $this->payment_date = $paymentDate;

        return $this;
    }

    /**
     * Get payment_date
     *
     * @return datetime
     */
    public function getPaymentDate()
    {
        return $this->payment_date;
    }

    /**
     * Set register_date
     *
     * @param \DateTime $registerDate
     * @return pendingPayment
     */
    public function setRegisterDate($registerDate)
    {
        $this->register_date = $registerDate;

        return $this;
    }

    /**
     * Get register_date
     *
     * @return datetime
     */
    public function getRegisterDate()
    {
        return $this->register_date;
    }

    /**
     * @return float
     */
    public function getAmount()
    {
        return $this->amount;
    }

    public function setAmount($amount)
    {
        $this->amount = $amount;
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
     * Set type
     *
     * @param nomenclator $type
     * @return pendingPayment
     */
    public function setType(nomenclator $type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return nomenclator
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param mixed $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }


}
