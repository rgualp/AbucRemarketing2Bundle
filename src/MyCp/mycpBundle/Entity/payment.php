<?php

namespace MyCp\mycpBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * payment
 *
 * @ORM\Table(name="payment")
 * @ORM\Entity
 */
class payment
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
     * @ORM\ManyToOne(targetEntity="generalReservation",inversedBy="")
     * @ORM\JoinColumn(name="general_reservation_id",referencedColumnName="gen_res_id")
     */
    private $general_reservation;

    /**
     * @ORM\ManyToOne(targetEntity="currency",inversedBy="")
     * @ORM\JoinColumn(name="currency_id",referencedColumnName="curr_id")
     */
    private $currency;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created", type="datetime")
     */
    private $created;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="modified", type="datetime")
     */
    private $modified;

    /**
     * @var float
     *
     * @ORM\Column(name="payed_amount", type="decimal")
     */
    private $payed_amount;

    /**
     * @var integer
     *
     * @ORM\Column(name="status", type="integer")
     */
    private $status;


    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     * @return payment
     */
    public function setCreated($created)
    {
        $this->created = $created;
    
        return $this;
    }

    /**
     * Get created
     *
     * @return \DateTime 
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set modified
     *
     * @param \DateTime $modified
     * @return payment
     */
    public function setModified($modified)
    {
        $this->modified = $modified;
    
        return $this;
    }

    /**
     * Get modified
     *
     * @return \DateTime 
     */
    public function getModified()
    {
        return $this->modified;
    }

    /**
     * Set payed_amount
     *
     * @param float $payedAmount
     * @return payment
     */
    public function setPayedAmount($payedAmount)
    {
        $this->payed_amount = $payedAmount;
    
        return $this;
    }

    /**
     * Get payed_amount
     *
     * @return float 
     */
    public function getPayedAmount()
    {
        return $this->payed_amount;
    }

    /**
     * Set status
     *
     * @param integer $status
     * @return payment
     */
    public function setStatus($status)
    {
        $this->status = $status;
    
        return $this;
    }

    /**
     * Get status
     *
     * @return integer 
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set general_reservation
     *
     * @param \MyCp\mycpBundle\Entity\generalReservation $generalReservation
     * @return payment
     */
    public function setGeneralReservation(\MyCp\mycpBundle\Entity\generalReservation $generalReservation = null)
    {
        $this->general_reservation = $generalReservation;
    
        return $this;
    }

    /**
     * Get general_reservation
     *
     * @return \MyCp\mycpBundle\Entity\generalReservation 
     */
    public function getGeneralReservation()
    {
        return $this->general_reservation;
    }

    /**
     * Set currency
     *
     * @param \MyCp\mycpBundle\Entity\currency $currency
     * @return payment
     */
    public function setCurrency(\MyCp\mycpBundle\Entity\currency $currency = null)
    {
        $this->currency = $currency;
    
        return $this;
    }

    /**
     * Get currency
     *
     * @return \MyCp\mycpBundle\Entity\currency 
     */
    public function getCurrency()
    {
        return $this->currency;
    }
}