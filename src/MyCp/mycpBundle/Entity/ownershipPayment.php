<?php

namespace MyCp\mycpBundle\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * ownershipPayment
 *
 * @ORM\Table(name="ownershippayment")
 * @ORM\Entity(repositoryClass="MyCp\mycpBundle\Entity\ownershipPaymentRepository")
 * @ORM\EntityListeners({"MyCp\mycpBundle\Listener\OwnershipPaymentListener"})
 */
class ownershipPayment {

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="ownership",inversedBy="payments")
     * @ORM\JoinColumn(name="accommodation",referencedColumnName="own_id")
     */
    private $accommodation;

    /**
     * @ORM\ManyToOne(targetEntity="mycpService",inversedBy="payments")
     * @ORM\JoinColumn(name="service",referencedColumnName="id")
     */
    private $service;

    /**
     * @ORM\ManyToOne(targetEntity="nomenclator",inversedBy="payments")
     * @ORM\JoinColumn(name="method",referencedColumnName="nom_id")
     */
    private $method;

    /**
     * @var string
     *
     * @ORM\Column(name="number", type="string")
     */
    private $number;

    /**
     * @var decimal
     *
     * @ORM\Column(name="payed_amount", type="decimal")
     */
    private $payed_amount;

    /**
     * @var datetime
     *
     * @ORM\Column(name="payment_date", type="datetime")
     */
    private $payment_date;

    /**
     * @var datetime
     *
     * @ORM\Column(name="creation_dat", type="datetime")
     */
    private $creation_date;

    public function __construct() {
        $this->creation_date = new \DateTime();
    }

    /**
     * @return mixed
     */
    public function getAccommodation()
    {
        return $this->accommodation;
    }

    /**
     * @param mixed $accommodation
     * @return mixed
     */
    public function setAccommodation($accommodation)
    {
        $this->accommodation = $accommodation;
        return $this;
    }

    /**
     * @return DateTime
     */
    public function getCreationDate()
    {
        return $this->creation_date;
    }

    /**
     * @param DateTime $creation_date
     * @return mixed
     */
    public function setCreationDate($creation_date)
    {
        $this->creation_date = $creation_date;
        return $this;
    }

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
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @param mixed $method
     * @return mixed
     */
    public function setMethod($method)
    {
        $this->method = $method;
        return $this;
    }

    /**
     * @return decimal
     */
    public function getPayedAmount()
    {
        return $this->payed_amount;
    }

    /**
     * @param decimal $payed_amount
     * @return mixed
     */
    public function setPayedAmount($payed_amount)
    {
        $this->payed_amount = $payed_amount;
        return $this;
    }

    /**
     * @return DateTime
     */
    public function getPaymentDate()
    {
        return $this->payment_date;
    }

    /**
     * @param DateTime $payment_date
     * @return mixed
     */
    public function setPaymentDate($payment_date)
    {
        $this->payment_date = $payment_date;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getService()
    {
        return $this->service;
    }

    /**
     * @param mixed $service
     * @return mixed
     */
    public function setService($service)
    {
        $this->service = $service;
        return $this;
    }



}
