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
     * @ORM\ManyToOne(targetEntity="currency",inversedBy="")
     * @ORM\JoinColumn(name="booking_currency",referencedColumnName="curr_id")
     */
    private $booking_currency;

    /**
     * @var string
     *
     * @ORM\Column(name="booking_user_dates", type="string", length=255)
     */

    private $booking_user_dates;

    /**
     * @var integer
     *
     * @ORM\Column(name="booking_user_id", type="integer")
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
     * Set booking_user_dates
     *
     * @param string $bookingUserDates
     * @return booking
     */
    public function setBookingUserDates($bookingUserDates)
    {
        $this->booking_user_dates = $bookingUserDates;
    
        return $this;
    }

    /**
     * Get booking_user_dates
     *
     * @return string 
     */
    public function getBookingUserDates()
    {
        return $this->booking_user_dates;
    }

    /**
     * Set booking_user_id
     *
     * @param integer $bookingUserId
     * @return booking
     */
    public function setBookingUserId($bookingUserId)
    {
        $this->booking_user_id = $bookingUserId;
    
        return $this;
    }

    /**
     * Get booking_user_id
     *
     * @return integer 
     */
    public function getBookingUserId()
    {
        return $this->booking_user_id;
    }

    /**
     * Set booking_currency
     *
     * @param \MyCp\mycpBundle\Entity\currency $bookingCurrency
     * @return booking
     */
    public function setBookingCurrency(\MyCp\mycpBundle\Entity\currency $bookingCurrency = null)
    {
        $this->booking_currency = $bookingCurrency;
    
        return $this;
    }

    /**
     * Get booking_currency
     *
     * @return \MyCp\mycpBundle\Entity\currency 
     */
    public function getBookingCurrency()
    {
        return $this->booking_currency;
    }
}