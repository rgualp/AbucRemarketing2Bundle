<?php

namespace MyCp\mycpBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * booking
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="MyCp\mycpBundle\Entity\bookingRepository")
 */
class booking
{
    /**
     * @var integer
     *
     * @ORM\Column(name="booking_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $booking_id;

    /**
     * @var boolean
     *
     * @ORM\Column(name="booking_cancel_protection", type="boolean")
     */
    private $booking_cancel_protection;

    /**
     * @var float
     *
     * @ORM\Column(name="booking_prepay", type="decimal", precision=10, scale=2)
     */
    private $booking_prepay;

    /**
     * @ORM\ManyToOne(targetEntity="currency",inversedBy="bookingCurrency")
     * @ORM\JoinColumn(name="booking_currency_id",referencedColumnName="curr_id")
     */
    private $booking_currency;

    /**
     * @ORM\ManyToOne(targetEntity="user",inversedBy="bookingUser")
     * @ORM\JoinColumn(name="booking_user_id",referencedColumnName="user_id")
     */
    private $booking_user_id;

    /**
     * Get booking_id
     *
     * @return integer 
     */
    public function getBookingId()
    {
        return $this->booking_id;
    }

    /**
     * Set booking_cancel_protection
     *
     * @param boolean $bookingCancelProtection
     * @return booking
     */
    public function setBookingCancelProtection($bookingCancelProtection)
    {
        $this->booking_cancel_protection = $bookingCancelProtection;
    
        return $this;
    }

    /**
     * Get booking_cancel_protection
     *
     * @return boolean 
     */
    public function getBookingCancelProtection()
    {
        return $this->booking_cancel_protection;
    }

    /**
     * Set booking_prepay
     *
     * @param float $bookingPrepay
     * @return booking
     */
    public function setBookingPrepay($bookingPrepay)
    {
        $this->booking_prepay = $bookingPrepay;
    
        return $this;
    }

    /**
     * Get booking_prepay
     *
     * @return float 
     */
    public function getBookingPrepay()
    {
        return $this->booking_prepay;
    }

    /**
     * Set booking_currency
     *
     * @param currency $bookingCurrency
     * @return booking
     */
    public function setBookingCurrency($bookingCurrency)
    {
        $this->booking_currency = $bookingCurrency;
    
        return $this;
    }

    /**
     * Get booking_currency
     *
     * @return currency
     */
    public function getBookingCurrency()
    {
        return $this->booking_currency;
    }

    /**
     * Set booking_user_id
     *
     * @param \MyCp\mycpBundle\Entity\user $bookingUserId
     * @return booking
     */
    public function setBookingUserId(\MyCp\mycpBundle\Entity\user $bookingUserId = null)
    {
        $this->booking_user_id = $bookingUserId;
    
        return $this;
    }

    /**
     * Get booking_user_id
     *
     * @return \MyCp\mycpBundle\Entity\user 
     */
    public function getBookingUserId()
    {
        return $this->booking_user_id;
    }
}