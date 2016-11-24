<?php

namespace MyCp\mycpBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use MyCp\FrontEndBundle\Helpers\PaymentHelper;
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
     * @ORM\ManyToOne(targetEntity="booking",inversedBy="payments")
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
     * @ORM\Column(name="payed_amount", type="decimal", precision=10, scale=2, nullable=true)
     */
    private $payed_amount = null;

    /**
     * @var integer
     *
     * @ORM\Column(name="status", type="integer", nullable=true)
     */
    private $status = null;

    /**
     * @var decimal
     *
     * @ORM\Column(name="current_cuc_change_rate", type="decimal", precision=10, scale=2, nullable=true)
     */
    private $current_cuc_change_rate;

    /**
     * @var boolean
     *
     * @ORM\Column(name="processed", type="boolean")
     */
    private $processed = false;


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

    /**
     * Set current_cuc_change_rate
     *
     * @param float $currentCucChangeRate
     * @return payment
     */
    public function setCurrentCucChangeRate($currentCucChangeRate)
    {
        $this->current_cuc_change_rate = $currentCucChangeRate;

        return $this;
    }

    /**
     * Get current_cuc_change_rate
     *
     * @return float
     */
    public function getCurrentCucChangeRate()
    {
        return $this->current_cuc_change_rate;
    }

    /**
     * Set processed
     *
     * @param boolean $processed
     * @return payment
     */
    public function setProcessed($processed)
    {
        $this->processed = $processed;

        return $this;
    }

    /**
     * Get processed
     *
     * @return boolean
     */
    public function getProcessed()
    {
        return $this->processed;
    }

    public function getPayedAmountInCurrency()
    {
        if($this->status == PaymentHelper::STATUS_SUCCESS || $this->status == PaymentHelper::STATUS_PROCESSED)
        {
            $currencyRate = ($this->current_cuc_change_rate != null) ? $this->current_cuc_change_rate : $this->getCurrency()->getCurrCucChange();
            return $this->getPayedAmount() / $currencyRate;
        }
        return 0;
    }

    public function generateEcomerceTracking($em, $controller){
        $booking = $this->getBooking();

        if ($booking){
            $id_booking = $booking = $this->getBooking()->getBookingId();
            $reservations = $em->getRepository('mycpBundle:ownershipReservation')->findBy(array('own_res_reservation_booking' => $id_booking, 'own_res_status' => ownershipReservation::STATUS_RESERVED), array('own_res_gen_res_id' => 'ASC'));

            if ($reservations){
                $casId = 0;
                $acomodations = array();
                $total = 0;
                $prepago = 0;
                $tarifa = 0;
                $habitaciones = 0;

                foreach ($reservations as $reservation){
                    $tarifa = $reservation->getOwnResGenResId()->getGenResOwnId()->getOwnCommissionPercent();
                    if ($casId === 0){
                        $casId = $reservation->getOwnResGenResId()->getCASId();
                    }
                    if ($reservation->getOwnResGenResId()->getCASId() === $casId){
                        $habitaciones++;
                        $total += ($reservation->getOwnResTotalInSite() * ($tarifa / 100)) * $this->getCurrentCucChangeRate() ;
                        $acomodations[$casId] = array(
                            'total' => $total,
                            'cant_hab' => $habitaciones,
                            'idCasa' => "CAS".$reservation->getOwnResGenResId()->getGenResOwnId()->getOwnId(),
                            'nombreCasa' => $reservation->getOwnResGenResId()->getGenResOwnId()->getOwnName(),
                            'destino' => $reservation->getOwnResGenResId()->getGenResOwnId()->getOwnDestination()->getDesName()
                        );
                    }else{
                        $prepago += $total;
                        $casId = $reservation->getOwnResGenResId()->getCASId();
                        $total = 0;
                        $habitaciones = 0;
                        $total += ($reservation->getOwnResTotalInSite() * ($tarifa / 100)) * $this->getCurrentCucChangeRate();
                        $habitaciones++;
                        $acomodations[$casId] = array(
                            'total' => $total,
                            'cant_hab' => $habitaciones,
                            'idCasa' => "CAS".$reservation->getOwnResGenResId()->getGenResOwnId()->getOwnId(),
                            'nombreCasa' => $reservation->getOwnResGenResId()->getGenResOwnId()->getOwnName(),
                            'destino' => $reservation->getOwnResGenResId()->getGenResOwnId()->getOwnDestination()->getDesName()
                        );
                    }
                }
                $prepago += $total;

                $tarifaFija =  ($this->getBooking()->getBookingPrepay() - $prepago) / count($acomodations);

                $booking = $this->getBooking();
                $transaction = new \AntiMattr\GoogleBundle\Analytics\Transaction();
                $transaction->setOrderNumber($booking->getBookingId());
                $transaction->setAffiliation('MyCasaParticular.com');
                $transaction->setTotal($booking->getBookingPrepay());
                $transaction->setCurrency($this->getCurrency()->getCurrCode());
                $controller->get('google.analytics')->setTransaction($transaction);

                foreach ($acomodations as $key => $acomodation){

//            $total = $acomodations[$key]['total'];
//            $acomodations[$key]['total'] = $total + $tarifaFija;

                    $item = new \AntiMattr\GoogleBundle\Analytics\Item();
                    $item->setOrderNumber($booking->getBookingId());
                    $item->setSku($acomodations[$key]['idCasa']);
                    $item->setName($acomodations[$key]['nombreCasa']);
                    $item->setCategory($acomodations[$key]['destino']);
                    $item->setPrice($acomodations[$key]['total'] + $tarifaFija);
                    $item->setQuantity($acomodations[$key]['cant_hab']);
                    $item->setCurrency($this->getCurrency()->getCurrCode());
                    $controller->get('google.analytics')->addItem($item);


                }
            }
        }
//        $payment = $em->getRepository('mycpBundle:payment')->findOneBy(array("booking" => $id_booking));
//
//        if ($payment){
//
//        }
    }
}