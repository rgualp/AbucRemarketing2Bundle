<?php

namespace MyCp\mycpBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use MyCp\FrontEndBundle\Helpers\SkrillHelper;

/**
 * postfinancePayment
 *
 * @ORM\Table(name="postfinance_payment")
 * @ORM\Entity
 */
class postfinancePayment
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
     * @ORM\ManyToOne(targetEntity="payment",inversedBy="")
     * @ORM\JoinColumn(name="payment_id",referencedColumnName="id")
     */
    private $payment;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created", type="datetime", nullable=true)
     */
    private $created = null;

    /**
     * @var string
     *
     * @ORM\Column(name="order_id", type="string", nullable=true)
     */
    private $order_id = null;

    /**
     * @var string
     *
     * @ORM\Column(name="amount", type="string", length=30, nullable=true)
     */
    private $amount = null;

    /**
     * @var string
     *
     * @ORM\Column(name="currency", type="string", length=5, nullable=true)
     */
    private $currency = null;

    /**
     * @var string
     *
     * @ORM\Column(name="payment_method", type="string", length=30, nullable=true)
     */
    private $payment_method = null;

    /**
     * @var string
     *
     * @ORM\Column(name="acceptance", type="string", length=50, nullable=true)
     */
    private $acceptance = null;

    /**
     * @var string
     *
     * @ORM\Column(name="status", type="string", length=30, nullable=true)
     */
    private $status = null;

    /**
     * @var string
     *
     * @ORM\Column(name="masked_card_number", type="string", length=30, nullable=true)
     */
    private $masked_card_number = null;

    /**
     * @var string
     *
     * @ORM\Column(name="payment_reference_id", type="string", length=10, nullable=true)
     */
    private $payment_reference_id = null;

    /**
     * @var string
     *
     * @ORM\Column(name="error_code", type="string", length=30, nullable=true)
     */
    private $error_code = null;

    /**
     * @var string
     *
     * @ORM\Column(name="card_brand", type="string", length=50, nullable=true)
     */
    private $card_brand = null;

    /**
     * @var string
     *
     * @ORM\Column(name="sha_out_signature", type="string", length=255, nullable=true)
     */
    private $sha_out_signature = null;


    public function __construct(array $postfinancePayment = array())
    {
        $this->setCreated(new \DateTime());

        if(!empty($postfinancePayment)) {
            $this->initializeWithPostfinanceResponse($postfinancePayment);
        }
    }

    /**
     * Takes the http POST status request array transmitted by the Skrill service
     * and fills the members.
     *
     * @param array $postfinanceStatusRequest The Postfinance status request.
     */
    public function initializeWithPostfinanceResponse(array $postfinanceStatusRequest = array())
    {
        if(isset($postfinanceStatusRequest['orderID'])) {
            $this->setOrderId($postfinanceStatusRequest['orderID']);
        }

        if(isset($postfinanceStatusRequest['amount'])) {
            $this->setAmount($postfinanceStatusRequest['amount']);
        }

        if(isset($postfinanceStatusRequest['currency'])) {
            $this->setCurrency($postfinanceStatusRequest['currency']);
        }

        if(isset($postfinanceStatusRequest['PM'])) {
            $this->setPaymentMethod($postfinanceStatusRequest['PM']);
        }

        if(isset($postfinanceStatusRequest['ACCEPTANCE'])) {
            $this->setAcceptance($postfinanceStatusRequest['ACCEPTANCE']);
        }

        if(isset($postfinanceStatusRequest['STATUS'])) {
            $this->setStatus($postfinanceStatusRequest['STATUS']);
        }

        if(isset($postfinanceStatusRequest['CARDNO'])) {
            $this->setMaskedCardNumber($postfinanceStatusRequest['CARDNO']);
        }

        if(isset($postfinanceStatusRequest['PAYID'])) {
            $this->setPaymentReferenceId($postfinanceStatusRequest['PAYID']);
        }

        if(isset($postfinanceStatusRequest['NCERROR'])) {
            $this->setErrorCode($postfinanceStatusRequest['NCERROR']);
        }

        if(isset($postfinanceStatusRequest['BRAND'])) {
            $this->setCardBrand($postfinanceStatusRequest['BRAND']);
        }

        if(isset($postfinanceStatusRequest['SHASIGN'])) {
            $this->setShaOutSignature($postfinanceStatusRequest['SHASIGN']);
        }
    }

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
     * @return skrillPayment
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
     * @return string
     */
    public function getAcceptance()
    {
        return $this->acceptance;
    }

    /**
     * @param string $acceptance
     * @return postfinancePayment
     */
    public function setAcceptance($acceptance)
    {
        $this->acceptance = $acceptance;
        return $this;
    }

    /**
     * @return string
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @param string $amount
     * @return postfinancePayment
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
        return $this;
    }

    /**
     * @return string
     */
    public function getCardBrand()
    {
        return $this->card_brand;
    }

    /**
     * @param string $card_brand
     * @return postfinancePayment
     */
    public function setCardBrand($card_brand)
    {
        $this->card_brand = $card_brand;
        return $this;
    }

    /**
     * @return string
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * @param string $currency
     * @return postfinancePayment
     */
    public function setCurrency($currency)
    {
        $this->currency = $currency;
        return $this;
    }

    /**
     * @return string
     */
    public function getErrorCode()
    {
        return $this->error_code;
    }

    /**
     * @param string $error_code
     * @return postfinancePayment
     */
    public function setErrorCode($error_code)
    {
        $this->error_code = $error_code;
        return $this;
    }

    /**
     * @return string
     */
    public function getMaskedCardNumber()
    {
        return $this->masked_card_number;
    }

    /**
     * @param string $masked_card_number
     * @return postfinancePayment
     */
    public function setMaskedCardNumber($masked_card_number)
    {
        $this->masked_card_number = $masked_card_number;
        return $this;
    }

    /**
     * @return string
     */
    public function getOrderId()
    {
        return $this->order_id;
    }

    /**
     * @param string $order_id
     * @return postfinancePayment
     */
    public function setOrderId($order_id)
    {
        $this->order_id = $order_id;
        return $this;
    }

    /**
     * @return string
     */
    public function getPaymentMethod()
    {
        return $this->payment_method;
    }

    /**
     * @param string $payment_method
     * @return postfinancePayment
     */
    public function setPaymentMethod($payment_method)
    {
        $this->payment_method = $payment_method;
        return $this;
    }

    /**
     * @return string
     */
    public function getPaymentReferenceId()
    {
        return $this->payment_reference_id;
    }

    /**
     * @param string $payment_reference_id
     * @return postfinancePayment
     */
    public function setPaymentReferenceId($payment_reference_id)
    {
        $this->payment_reference_id = $payment_reference_id;
        return $this;
    }

    /**
     * @return string
     */
    public function getShaOutSignature()
    {
        return $this->sha_out_signature;
    }

    /**
     * @param string $sha_out_signature
     * @return postfinancePayment
     */
    public function setShaOutSignature($sha_out_signature)
    {
        $this->sha_out_signature = $sha_out_signature;
        return $this;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param string $status
     * @return postfinancePayment
     */
    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }



    /**
     * Set payment
     *
     * @param \MyCp\mycpBundle\Entity\payment $payment
     * @return postfinancePayment
     */
    public function setPayment(\MyCp\mycpBundle\Entity\payment $payment = null)
    {
        $this->payment = $payment;

        return $this;
    }

    /**
     * Get payment
     *
     * @return \MyCp\mycpBundle\Entity\payment
     */
    public function getPayment()
    {
        return $this->payment;
    }
}
