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
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var boolean
     *
     * @ORM\Column(name="booking_cancel_protection", type="boolean")
     */
    private $booking_cancel_protection;

    /**
     * @var float
     *
     * @ORM\Column(name="booking_prepay", type="float")
     */
    private $booking_prepay;

    /**
     * @var string
     *
     * @ORM\Column(name="booking_currency_symbol", type="string", length=5)
     */
    private $booking_currency_symbol;


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
     * Set booking_currency_symbol
     *
     * @param string $bookingCurrencySymbol
     * @return booking
     */
    public function setBookingCurrencySymbol($bookingCurrencySymbol)
    {
        $this->booking_currency_symbol = $bookingCurrencySymbol;
    
        return $this;
    }

    /**
     * Get booking_currency_symbol
     *
     * @return string 
     */
    public function getBookingCurrencySymbol()
    {
        return $this->booking_currency_symbol;
    }
}
