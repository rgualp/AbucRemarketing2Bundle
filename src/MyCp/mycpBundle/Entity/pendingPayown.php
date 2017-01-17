<?php

namespace MyCp\mycpBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * pendingPayown
 *
 * @ORM\Table(name="pending_paytourist")
 * @ORM\Entity(repositoryClass="MyCp\mycpBundle\Entity\pendingPayownRepository")
 */
class pendingPayown
{
    /**
     * @var integer
     *
     * @ORM\Column(name="pending_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $pending_id;

    /**
     * @ORM\ManyToOne(targetEntity="userCasa")
     * @ORM\JoinColumn(name="user_casa", referencedColumnName="user_casa_id", nullable=false)
     */
    private $user_casa;


    /**
     * @ORM\ManyToOne(targetEntity="cancelPayment")
     * @ORM\JoinColumn(name="cancel_id", referencedColumnName="cancel_id", nullable=false)
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
     * @ORM\Column(name="payment_date", type="datetime")
     */
    private $payment_date;

    /**
     * @var float
     *
     * @ORM\Column(name="pay_amount", type="float", precision=2, nullable=true)
     */
    private $pay_amount;



    /**
     * Get pending_id
     *
     * @return integer 
     */
    public function getPendingId()
    {
        return $this->pending_id;
    }

    /**
     * Get user_casa
     *
     * @return userCasa
     */
    public function getUserCasa() {
        return $this->user_casa;
    }

    /**
     * Set user_casa
     *
     * @param userCasa $user_casa
     * @return pendingPayown
     */
    public function setUserCasa($user_casa) {
        $this->user_casa = $user_casa;
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
     * @return pendingPayown
     */
    public function setCancelId($cancel_id) {
        $this->cancel_id = $cancel_id;
        return $this;
    }

    /**
     * Set reason
     *
     * @param string $reason
     * @return pendingPayown
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
     * @return pendingPayown
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
     * @return float
     */
    public function getPayAmount()
    {
        return $this->pay_amount;
    }

    public function setPayAmount($pay_amount)
    {
        $this->pay_amount = $pay_amount;
        return $this;
    }

}
