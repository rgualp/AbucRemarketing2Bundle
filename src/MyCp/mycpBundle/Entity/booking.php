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
     * @ORM\OneToMany(targetEntity="ownershipReservation",mappedBy="own_res_reservation_booking")
     */
    private $booking_own_reservations;

    /**
     * @ORM\OneToMany(targetEntity="payment",mappedBy="booking")
     */
    private $payments;

    /**
     * @var integer
     *
     * @ORM\Column(name="payAtService", type="decimal", precision=10, scale=2 )
     */
    private $payAtService;

    /**
     * @var integer
     *
     * @ORM\Column(name="complete_payment", type="boolean")
     */
    private $complete_payment;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->booking_own_reservations = new \Doctrine\Common\Collections\ArrayCollection();
        $this->payments = new \Doctrine\Common\Collections\ArrayCollection();
        $this->complete_payment = false;
    }

    /**
     * Get booking_id
     *
     * @return integer 
     */
    public function getBookingId()
    {
        return $this->booking_id;
    }
    
    public function getBookingOwnReservations(){
        return $this->booking_own_reservations;
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

    /**
     * @return mixed
     */
    public function getPayments()
    {
        return $this->payments;
    }

    /**
     * @param mixed $payments
     */
    public function setPayments($payments)
    {
        $this->payments = $payments;
    }

    public function getPayedAmount()
    {
        $payments = $this->getPayments();
        $payedAmount = 0;

        foreach($payments as $payment){
            $payedAmount += $payment->getPayedAmountInCurrency();
        }

        return $payedAmount;
    }

    /**
     * @return int
     */
    public function getPayAtService()
    {
        return $this->payAtService;
    }

    /**
     * @param int $payAtService
     * @return mixed
     */
    public function setPayAtService($payAtService)
    {
        $this->payAtService = $payAtService;
        return $this;
    }

    /**
     * @return int
     */
    public function getCompletePayment()
    {
        return $this->complete_payment;
    }

    /**
     * @param int $complete_payment
     * @return mixed
     */
    public function setCompletePayment($complete_payment)
    {
        $this->complete_payment = $complete_payment;
        return $this;
    }
}