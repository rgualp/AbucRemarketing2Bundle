<?php

namespace MyCp\PartnerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * paPendingPaymentAgency
 *
 * @ORM\Table(name="pa_pending_payment_agency")
 * @ORM\Entity(repositoryClass="MyCp\PartnerBundle\Repository\paPendingPaymentAgencyRepository")
 */
class paPendingPaymentAgency
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
     * @ORM\ManyToOne(targetEntity="MyCp\mycpBundle\Entity\generalReservation")
     * @ORM\JoinColumn(name="reservation", referencedColumnName="gen_res_id", nullable=false)
     */
    private $reservation;

    /**
     * @ORM\ManyToOne(targetEntity="MyCp\mycpBundle\Entity\booking")
     * @ORM\JoinColumn(name="booking", referencedColumnName="booking_id", nullable=true)
     */
    private $booking;

    /**
     * @ORM\ManyToOne(targetEntity="paTravelAgency")
     * @ORM\JoinColumn(name="agency", referencedColumnName="id", nullable=false)
     */
    private $agency;


    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_date", type="datetime")
     */
    private $createdDate;


    /**
     * @var \DateTime
     *
     * @ORM\Column(name="pay_date", type="datetime", nullable=true)
     */
    private $pay_date;

    /**
     * @var float
     *
     * @ORM\Column(name="amount", type="float", precision=2, nullable=true)
     */
    private $amount;

    /**
     * @ORM\ManyToOne(targetEntity="MyCp\mycpBundle\Entity\nomenclator")
     * @ORM\JoinColumn(name="status",referencedColumnName="nom_id")
     */
    private $status;

    /**
     * @ORM\ManyToOne(targetEntity="MyCp\mycpBundle\Entity\nomenclator")
     * @ORM\JoinColumn(name="type",referencedColumnName="nom_id")
     */
    private $type;

    /**
     * @ORM\ManyToOne(targetEntity="MyCp\mycpBundle\Entity\user")
     * @ORM\JoinColumn(name="user", referencedColumnName="user_id", nullable=true)
     */
    private $user;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="register_date", type="datetime", nullable=true)
     */
    private $register_date;

    /**
     * @var string
     *
     * @ORM\Column(name="reason", type="text", nullable=true)
     */
    private $reason;

    /**
     * @ORM\ManyToOne(targetEntity="paCancelPayment")
     * @ORM\JoinColumn(name="cancel_payment", referencedColumnName="id", nullable=true)
     */
    private $cancelPayment;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getAgency()
    {
        return $this->agency;
    }

    /**
     * @param mixed $agency
     * @return mixed
     */
    public function setAgency($agency)
    {
        $this->agency = $agency;
        return $this;
    }

    /**
     * @return float
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @param float $amount
     * @return mixed
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedDate()
    {
        return $this->createdDate;
    }

    /**
     * @param \DateTime $createdDate
     * @return mixed
     */
    public function setCreatedDate($createdDate)
    {
        $this->createdDate = $createdDate;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getPayDate()
    {
        return $this->pay_date;
    }

    /**
     * @param \DateTime $pay_date
     * @return mixed
     */
    public function setPayDate($pay_date)
    {
        $this->pay_date = $pay_date;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getReservation()
    {
        return $this->reservation;
    }

    /**
     * @param mixed $reservation
     * @return mixed
     */
    public function setReservation($reservation)
    {
        $this->reservation = $reservation;
        return $this;
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
     * @return mixed
     */
    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param mixed $type
     * @return mixed
     */
    public function setType($type)
    {
        $this->type = $type;
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
     * @param mixed $booking
     * @return mixed
     */
    public function setBooking($booking)
    {
        $this->booking = $booking;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getRegisterDate()
    {
        return $this->register_date;
    }

    /**
     * @param \DateTime $register_date
     * @return mixed
     */
    public function setRegisterDate($register_date)
    {
        $this->register_date = $register_date;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param mixed $user
     * @return mixed
     */
    public function setUser($user)
    {
        $this->user = $user;
        return $this;
    }

    /**
     * @return string
     */
    public function getReason()
    {
        return $this->reason;
    }

    /**
     * @param string $reason
     * @return mixed
     */
    public function setReason($reason)
    {
        $this->reason = $reason;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCancelPayment()
    {
        return $this->cancelPayment;
    }

    /**
     * @param mixed $cancelPayment
     * @return mixed
     */
    public function setCancelPayment($cancelPayment)
    {
        $this->cancelPayment = $cancelPayment;
        return $this;
    }

}
