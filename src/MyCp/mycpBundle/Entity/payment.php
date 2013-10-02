<?php

namespace MyCp\mycpBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;

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
     * @ORM\ManyToOne(targetEntity="booking",inversedBy="")
     * @ORM\JoinColumn(name="booking_id",referencedColumnName="booking_id")
     */
    private $booking;

    /**
     * @ORM\ManyToOne(targetEntity="currency",inversedBy="")
     * @ORM\JoinColumn(name="currency_id",referencedColumnName="curr_id", nullable=true)
     */
    private $currency = null;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created", type="datetime", nullable=true)
     */
    private $created = null;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="modified", type="datetime", nullable=true)
     */
    private $modified = null;

    /**
     * @var integer
     *
     * @ORM\Column(name="payed_amount", type="integer", nullable=true)
     */
    private $payed_amount = null;

    /**
     * @var integer
     *
     * @ORM\Column(name="status", type="integer", nullable=true)
     */
    private $status = null;


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
     * @param integer $payedAmount
     * @return payment
     */
    public function setPayedAmount($payedAmount)
    {
        $this->payed_amount = $payedAmount;
    
        return $this;
    }

    /**
     * Set payed_amount by a string with the form
     * \d+\.?(\d*)
     *
     * @param string $payedAmountString
     * @throws InvalidArgumentException
     * @return payment
     */
    public function setPayedAmountFromString($payedAmountString)
    {
        $decimalPointPosition = strpos($payedAmountString, '.');

        if($decimalPointPosition !== false) {
            $fraction = substr($payedAmountString, $decimalPointPosition+1);
            $length = strlen($fraction);

            if($length === 1) {
                $payedAmountString .= '0';
            } else if ($length !== 2) {
                throw new InvalidArgumentException();
            }
            $payedAmountString = str_replace('.', '', $payedAmountString);
        } else {
            $payedAmountString = $payedAmountString . '00';
        }

        $payedAmount = (int)$payedAmountString;
        $this->payed_amount = $payedAmount;

        return $this;
    }

    /**
     * Get payed_amount
     *
     * @return integer
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
     * Set booking
     *
     * @param \MyCp\mycpBundle\Entity\booking $booking
     * @return payment
     */
    public function setBooking(\MyCp\mycpBundle\Entity\booking $booking = null)
    {
        $this->booking = $booking;
    
        return $this;
    }

    /**
     * Get booking
     *
     * @return \MyCp\mycpBundle\Entity\booking
     */
    public function getBooking()
    {
        return $this->booking;
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