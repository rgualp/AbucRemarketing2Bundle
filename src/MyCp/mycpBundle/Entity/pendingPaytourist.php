<?php

namespace MyCp\mycpBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * pendingPaytourist
 *
 * @ORM\Table(name="pending_paytourist")
 * @ORM\Entity(repositoryClass="MyCp\mycpBundle\Entity\pendingPaytouristRepository")
 */
class pendingPaytourist
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
     * @ORM\ManyToOne(targetEntity="userTourist")
     * @ORM\JoinColumn(name="user_tourist", referencedColumnName="user_tourist_id", nullable=false)
     */
    private $user_tourist;


    /**
     * @ORM\ManyToOne(targetEntity="cancelPayment",cascade={"persist"})
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
     * @var datetime
     *
     * @ORM\Column(name="register_date", type="datetime")
     */
    private $register_date;

    /**
     * @var float
     *
     * @ORM\Column(name="pay_amount", type="float", precision=2, nullable=true)
     */
    private $pay_amount;

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
     * Get pending_id
     *
     * @return integer 
     */
    public function getPendingId()
    {
        return $this->pending_id;
    }

    /**
     * Get user_tourist
     *
     * @return userTourist
     */
    public function getUserTourist() {
        return $this->user_tourist;
    }

    /**
     * Set user_tourist
     *
     * @param userTourist $user_tourist
     * @return pendingPaytourist
     */
    public function setUserTourist($user_tourist) {
        $this->user_tourist = $user_tourist;
        return $this;
    }

    /**
     * Get cancel_id
     *
     * @return pendingPaytourist
     */
    public function getCancelId() {
        return $this->cancel_id;
    }

    /**
     * Set cancel_id
     *
     * @param cancelPayment $cancel_id
     * @return pendingPaytourist
     */
    public function setCancelId($cancel_id) {
        $this->cancel_id = $cancel_id;
        return $this;
    }

    /**
     * Set reason
     *
     * @param string $reason
     * @return pendingPaytourist
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
     * Set register_date
     *
     * @param \DateTime $registerDate
     * @return pendingPaytourist
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
    public function getPayAmount()
    {
        return $this->pay_amount;
    }

    public function setPayAmount($pay_amount)
    {
        $this->pay_amount = $pay_amount;
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
     * @return pendingPaytourist
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

}
