<?php

namespace MyCp\mycpBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use MyCp\frontEndBundle\Helpers\SkrillHelper;

/**
 * skrillPayment
 *
 * @ORM\Table(name="skrillpayment")
 * @ORM\Entity
 */
class skrillPayment
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
     * @ORM\Column(name="merchant_email", type="string", length=60, nullable=true)
     */
    private $merchant_email = null;

    /**
     * @var string
     *
     * @ORM\Column(name="customer_email", type="string", length=110, nullable=true)
     */
    private $customer_email = null;

    /**
     * @var string
     *
     * @ORM\Column(name="merchant_id", type="string", length=50, nullable=true)
     */
    private $merchant_id = null;

    /**
     * @var string
     *
     * @ORM\Column(name="customer_id", type="string", length=50, nullable=true)
     */
    private $customer_id = null;

    /**
     * @var string
     *
     * @ORM\Column(name="merchant_transaction_id", type="string", length=30, nullable=true)
     */
    private $merchant_transaction_id = null;

    /**
     * @var string
     *
     * @ORM\Column(name="skrill_transaction_id", type="string", length=30, nullable=true)
     */
    private $skrill_transaction_id = null;

    /**
     * @var string
     *
     * @ORM\Column(name="payed_amount", type="string", length=30, nullable=true)
     */
    private $payed_amount = null;

    /**
     * @var string
     *
     * @ORM\Column(name="skrill_currency", type="string", length=5, nullable=true)
     */
    private $skrill_currency = null;

    /**
     * @var string
     *
     * @ORM\Column(name="status", type="string", length=3, nullable=true)
     */
    private $status = null;

    /**
     * @var string
     *
     * @ORM\Column(name="failed_reason_code", type="string", length=5, nullable=true)
     */
    private $failed_reason_code = null;

    /**
     * @var string
     *
     * @ORM\Column(name="failed_reason_description", type="string", length=255, nullable=true)
     */
    private $failed_reason_description = null;

    /**
     * @var string
     *
     * @ORM\Column(name="md5_signature", type="string", length=64, nullable=true)
     */
    private $md5_signature = null;

    /**
     * @var string
     *
     * @ORM\Column(name="merchant_amount", type="string", length=30, nullable=true)
     */
    private $merchant_amount = null;

    /**
     * @var string
     *
     * @ORM\Column(name="merchant_currency", type="string", length=5, nullable=true)
     */
    private $merchant_currency = null;

    /**
     * @var string
     *
     * @ORM\Column(name="payment_type", type="string", length=5, nullable=true)
     */
    private $payment_type = null;

    /**
     * @var string
     *
     * @ORM\Column(name="merchant_fields", type="string", length=255, nullable=true)
     */
    private $merchant_fields = null;

    public function __construct(array $skrillPayment = array())
    {
        $this->setCreated(new \DateTime());

        if(!empty($skrillPayment)) {
            $this->initializeWithSkrillResponse($skrillPayment);
        }
    }

    /**
     * Takes the http POST status request array transmitted by the Skrill service
     * and fills the members.
     *
     * @param array $skrillStatusRequest The Skrill status request.
     */
    public function initializeWithSkrillResponse(array $skrillStatusRequest = array())
    {
        if(isset($skrillStatusRequest['pay_to_email'])) {
            $this->setMerchantEmail($skrillStatusRequest['pay_to_email']);
        }

        if(isset($skrillStatusRequest['pay_from_email'])) {
            $this->setCustomerEmail($skrillStatusRequest['pay_from_email']);
        }

        if(isset($skrillStatusRequest['merchant_id'])) {
            $this->setMerchantId($skrillStatusRequest['merchant_id']);
        }

        if(isset($skrillStatusRequest['customer_id'])) {
            $this->setCustomerId($skrillStatusRequest['customer_id']);
        }

        if(isset($skrillStatusRequest['transaction_id'])) {
            $this->setMerchantTransactionId($skrillStatusRequest['transaction_id']);
        }

        if(isset($skrillStatusRequest['mb_transaction_id'])) {
            $this->setSkrillTransactionId($skrillStatusRequest['mb_transaction_id']);
        }

        if(isset($skrillStatusRequest['mb_amount'])) {
            $this->setPayedAmount($skrillStatusRequest['mb_amount']);
        }

        if(isset($skrillStatusRequest['mb_currency'])) {
            $this->setSkrillCurrency($skrillStatusRequest['mb_currency']);
        }

        if(isset($skrillStatusRequest['status'])) {
            $this->setStatus($skrillStatusRequest['status']);
        }

        if(isset($skrillStatusRequest['failed_reason_code'])) {
            $failedReasonCode = trim($skrillStatusRequest['failed_reason_code']);
            $this->setFailedReasonCode($failedReasonCode);
            $this->setFailedReasonDescription(SkrillHelper::getSkrillFailureDescription($failedReasonCode));
        }

        if(isset($skrillStatusRequest['md5sig'])) {
            $this->setMd5Signature($skrillStatusRequest['md5sig']);
        }

        if(isset($skrillStatusRequest['amount'])) {
            $this->setMerchantAmount($skrillStatusRequest['amount']);
        }

        if(isset($skrillStatusRequest['currency'])) {
            $this->setMerchantCurrency($skrillStatusRequest['currency']);
        }

        if(isset($skrillStatusRequest['payment_type'])) {
            $this->setPaymentType($skrillStatusRequest['payment_type']);
        }

        if(isset($skrillStatusRequest['merchant_fields'])) {
            // TODO: split to array
            $this->setMerchantFields($skrillStatusRequest['merchant_fields']);
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
     * Set merchant_email
     *
     * @param string $merchantEmail
     * @return skrillPayment
     */
    public function setMerchantEmail($merchantEmail)
    {
        $this->merchant_email = $merchantEmail;
    
        return $this;
    }

    /**
     * Get merchant_email
     *
     * @return string 
     */
    public function getMerchantEmail()
    {
        return $this->merchant_email;
    }

    /**
     * Set customer_email
     *
     * @param string $customerEmail
     * @return skrillPayment
     */
    public function setCustomerEmail($customerEmail)
    {
        $this->customer_email = $customerEmail;
    
        return $this;
    }

    /**
     * Get customer_email
     *
     * @return string 
     */
    public function getCustomerEmail()
    {
        return $this->customer_email;
    }

    /**
     * Set merchant_id
     *
     * @param string $merchantId
     * @return skrillPayment
     */
    public function setMerchantId($merchantId)
    {
        $this->merchant_id = $merchantId;
    
        return $this;
    }

    /**
     * Get merchant_id
     *
     * @return string 
     */
    public function getMerchantId()
    {
        return $this->merchant_id;
    }

    /**
     * Set customer_id
     *
     * @param string $customerId
     * @return skrillPayment
     */
    public function setCustomerId($customerId)
    {
        $this->customer_id = $customerId;
    
        return $this;
    }

    /**
     * Get customer_id
     *
     * @return string 
     */
    public function getCustomerId()
    {
        return $this->customer_id;
    }

    /**
     * Set merchant_transaction_id
     *
     * @param string $merchantTransactionId
     * @return skrillPayment
     */
    public function setMerchantTransactionId($merchantTransactionId)
    {
        $this->merchant_transaction_id = $merchantTransactionId;
    
        return $this;
    }

    /**
     * Get merchant_transaction_id
     *
     * @return string 
     */
    public function getMerchantTransactionId()
    {
        return $this->merchant_transaction_id;
    }

    /**
     * Set skrill_transaction_id
     *
     * @param string $skrillTransactionId
     * @return skrillPayment
     */
    public function setSkrillTransactionId($skrillTransactionId)
    {
        $this->skrill_transaction_id = $skrillTransactionId;
    
        return $this;
    }

    /**
     * Get skrill_transaction_id
     *
     * @return string 
     */
    public function getSkrillTransactionId()
    {
        return $this->skrill_transaction_id;
    }

    /**
     * Set payed_amount
     *
     * @param string $payedAmount
     * @return skrillPayment
     */
    public function setPayedAmount($payedAmount)
    {
        $this->payed_amount = $payedAmount;
    
        return $this;
    }

    /**
     * Get payed_amount
     *
     * @return string 
     */
    public function getPayedAmount()
    {
        return $this->payed_amount;
    }

    /**
     * Set skrill_currency
     *
     * @param string $skrillCurrency
     * @return skrillPayment
     */
    public function setSkrillCurrency($skrillCurrency)
    {
        $this->skrill_currency = $skrillCurrency;
    
        return $this;
    }

    /**
     * Get skrill_currency
     *
     * @return string 
     */
    public function getSkrillCurrency()
    {
        return $this->skrill_currency;
    }

    /**
     * Set status
     *
     * @param string $status
     * @return skrillPayment
     */
    public function setStatus($status)
    {
        $this->status = $status;
    
        return $this;
    }

    /**
     * Get status
     *
     * @return string 
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set failed_reason_code
     *
     * @param string $failedReasonCode
     * @return skrillPayment
     */
    public function setFailedReasonCode($failedReasonCode)
    {
        $this->failed_reason_code = $failedReasonCode;
    
        return $this;
    }

    /**
     * Get failed_reason_code
     *
     * @return string 
     */
    public function getFailedReasonCode()
    {
        return $this->failed_reason_code;
    }

    /**
     * Set failed_reason_description
     *
     * @param string $failedReasonDescription
     * @return skrillPayment
     */
    public function setFailedReasonDescription($failedReasonDescription)
    {
        $this->failed_reason_description = $failedReasonDescription;
    
        return $this;
    }

    /**
     * Get failed_reason_description
     *
     * @return string 
     */
    public function getFailedReasonDescription()
    {
        return $this->failed_reason_description;
    }

    /**
     * Set md5_signature
     *
     * @param string $md5Signature
     * @return skrillPayment
     */
    public function setMd5Signature($md5Signature)
    {
        $this->md5_signature = $md5Signature;
    
        return $this;
    }

    /**
     * Get md5_signature
     *
     * @return string 
     */
    public function getMd5Signature()
    {
        return $this->md5_signature;
    }

    /**
     * Set merchant_amount
     *
     * @param string $merchantAmount
     * @return skrillPayment
     */
    public function setMerchantAmount($merchantAmount)
    {
        $this->merchant_amount = $merchantAmount;
    
        return $this;
    }

    /**
     * Get merchant_amount
     *
     * @return string 
     */
    public function getMerchantAmount()
    {
        return $this->merchant_amount;
    }

    /**
     * Set merchant_currency
     *
     * @param string $merchantCurrency
     * @return skrillPayment
     */
    public function setMerchantCurrency($merchantCurrency)
    {
        $this->merchant_currency = $merchantCurrency;
    
        return $this;
    }

    /**
     * Get merchant_currency
     *
     * @return string 
     */
    public function getMerchantCurrency()
    {
        return $this->merchant_currency;
    }

    /**
     * Set payment_type
     *
     * @param string $paymentType
     * @return skrillPayment
     */
    public function setPaymentType($paymentType)
    {
        $this->payment_type = $paymentType;
    
        return $this;
    }

    /**
     * Get payment_type
     *
     * @return string 
     */
    public function getPaymentType()
    {
        return $this->payment_type;
    }

    /**
     * Set merchant_fields
     *
     * @param string $merchantFields
     * @return skrillPayment
     */
    public function setMerchantFields($merchantFields)
    {
        $this->merchant_fields = $merchantFields;
    
        return $this;
    }

    /**
     * Get merchant_fields
     *
     * @return string 
     */
    public function getMerchantFields()
    {
        return $this->merchant_fields;
    }

    /**
     * Set payment
     *
     * @param \MyCp\mycpBundle\Entity\payment $payment
     * @return skrillPayment
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