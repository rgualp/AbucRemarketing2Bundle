<?php

namespace MyCp\frontEndBundle\Helpers;

final class SkrillHelper
{
    private static $statusCodeMap = array(
        '2' => 'Processed',
        '0' => 'Pending',
        '-1' => 'Cancelled',
        '-2' => 'Failed',
        '-3' => 'Chargeback'
    );

    private static $statusSkrillToInternal = array(
        '2' => PaymentHelper::STATUS_SUCCESS,
        '0' => PaymentHelper::STATUS_PENDING,
        '-1' => PaymentHelper::STATUS_CANCELLED,
        '-2' => PaymentHelper::STATUS_FAILED,
        '-3' => PaymentHelper::STATUS_FAILED
    );

    private static $failureCodeMap = array(
        '01' => 'Referred',
        '02' => 'Invalid Merchant Number',
        '03' => 'Pick-up card',
        '04' => 'Authorisation Declined',
        '05' => 'Other Error',
        '06' => 'CVV is mandatory, but not set or invalid',
        '07' => 'Approved authorisation, honour with identification',
        '08' => 'Delayed Processing',
        '09' => 'Invalid Transaction',
        '10' => 'Invalid Currency',
        '11' => 'Invalid Amount/Available Limit Exceeded/Amount too high',
        '12' => 'Invalid credit card or bank account',
        '13' => 'Invalid Card Issuer',
        '14' => 'Annulation by client',
        '15' => 'Duplicate transaction',
        '16' => 'Acquirer Error',
        '17' => 'Reversal not processed, matching authorisation not found',
        '18' => 'File Transfer not available/unsuccessful',
        '19' => 'Reference number error',
        '20' => 'Access Denied',
        '21' => 'File Transfer failed',
        '22' => 'Format Error',
        '23' => 'Unknown Acquirer',
        '24' => 'Card expired',
        '25' => 'Fraud Suspicion',
        '26' => 'Security code expired',
        '27' => 'Requested function not available',
        '28' => 'Lost/Stolen card',
        '29' => 'Stolen card, Pick up',
        '30' => 'Duplicate Authorisation',
        '31' => 'Limit Exceeded',
        '32' => 'Invalid Security Code',
        '33' => 'Unknown or Invalid Card/Bank account',
        '34' => 'Illegal Transaction',
        '35' => 'Transaction Not Permitted',
        '36' => 'Card blocked in local blacklist',
        '37' => 'Restricted card/bank account',
        '38' => 'Security Rules Violation',
        '39' => 'The transaction amount of the referencing transaction is higher than the transaction amount of the original transaction Transaction frequency limit exceeded, override is possible',
        '40' => 'Transaction frequency limit exceeded, override is possible',
        '41' => 'Incorrect usage count in the Authorisation System exceeded',
        '42' => 'Card blocked',
        '43' => 'Rejected by Credit Card Issuer',
        '44' => 'Card Issuing Bank or Network is not available',
        '45' => 'The card type is not processed by the authorisation centre / Authorisation System has determined incorrect Routing Processing temporarily not possible',
        '46' => 'undefined',
        '47' => 'Processing temporarily not possible',
        '48' => 'Security Breach',
        '49' => 'Date / time not plausible, trace-no. not increasing',
        '50' => 'Error in PAC encryption detected',
        '51' => 'System Error',
        '52' => 'MB Denied - potential fraud',
        '53' => 'Mobile verification failed',
        '54' => 'Failed due to internal security restrictions',
        '55' => 'Communication or verification problem',
        '56' => '3D verification failed',
        '57' => 'AVS check failed',
        '58' => 'Invalid bank code',
        '59' => 'Invalid account code',
        '60' => 'Card not authorised',
        '61' => 'No credit worthiness',
        '62' => 'Communication error',
        '63' => 'Transaction not allowed for cardholder',
        '64' => 'Invalid Data in Request',
        '65' => 'Blocked bank code',
        '66' => 'CVV2/CVC2 Failure',
        '99' => 'General error',
    );


    /**
     * Hide constructor as the class only contains static methods.
     */
    private function __construct()
    {

    }

    public static function getInternalStatusCodeFrom($skrillStatusCode)
    {
        if(isset(self::$statusSkrillToInternal[$skrillStatusCode])) {
            return self::$statusSkrillToInternal[$skrillStatusCode];
        }

        return PaymentHelper::STATUS_FAILED;
    }

    /**
     * Returns a description for a Skrill status code.
     *
     * @param string $skrillStatusCode The status code.
     * @return string The description.
     */
    public static function getSkrillStatusCodeDescrition($skrillStatusCode)
    {
        if(isset(self::$statusCodeMap[$skrillStatusCode])) {
            return self::$statusCodeMap[$skrillStatusCode];
        }

        return 'unknown status';
    }

    /**
     * Returns the failure description belonging to a Skrill failure code.
     * (Taken from the Moneybookers Gateway Manual, v.6.18)
     *
     * @param string $skrillFailureCode The Skrill failure code.
     * @return string The failure code description.
     */
    public static function getSkrillFailureDescription($skrillFailureCode)
    {
        if(isset(self::$failureCodeMap[$skrillFailureCode])) {
            return self::$failureCodeMap[$skrillFailureCode];
        }

        return 'unknown failure code';
    }

}
//
//class SkrillStatusResponse
//{
//    private $merchant_email;
//    private $customer_email;
//    private $merchant_id;
//    private $customer_id;
//    private $merchant_transaction_id;
//    private $skrill_transaction_id;
//    private $payed_amount;
//    private $skrill_currency;
//    private $status;
//    private $failed_reason_code;
//    private $failed_reason_description;
//    private $md5_signature;
//    private $merchant_amount;
//    private $merchant_currency;
//    private $payment_type;
//    private $merchant_fields;
//
//
//    public function __construct(array $skrillStatusResponse = array())
//    {
//        $this->initializeWithSkrillResponse($skrillStatusResponse);
//    }
//
//    /**
//     * Takes the http POST status request array transmitted by the Skrill service
//     * and fills the members.
//     *
//     * @param array $skrillStatusRequest The Skrill status request.
//     */
//    public function initializeWithSkrillResponse(array $skrillStatusRequest = array())
//    {
//        if(isset($skrillStatusRequest['pay_to_email'])) {
//            $this->setMerchantEmail($skrillStatusRequest['pay_to_email']);
//        }
//
//        if(isset($skrillStatusRequest['pay_from_email'])) {
//            $this->setCustomerEmail($skrillStatusRequest['pay_from_email']);
//        }
//
//        if(isset($skrillStatusRequest['merchant_id'])) {
//            $this->setMerchantId($skrillStatusRequest['merchant_id']);
//        }
//
//        if(isset($skrillStatusRequest['customer_id'])) {
//            $this->setCustomerId($skrillStatusRequest['customer_id']);
//        }
//
//        if(isset($skrillStatusRequest['transaction_id'])) {
//            $this->setMerchantTransactionId($skrillStatusRequest['transaction_id']);
//        }
//
//        if(isset($skrillStatusRequest['mb_transaction_id'])) {
//            $this->setSkrillTransactionId($skrillStatusRequest['mb_transaction_id']);
//        }
//
//        if(isset($skrillStatusRequest['mb_amount'])) {
//            $this->setPayedAmount($skrillStatusRequest['mb_amount']);
//        }
//
//        if(isset($skrillStatusRequest['mb_currency'])) {
//            $this->setSkrillCurrency($skrillStatusRequest['mb_currency']);
//        }
//
//        if(isset($skrillStatusRequest['status'])) {
//            $this->setStatus($skrillStatusRequest['status']);
//        }
//
//        if(isset($skrillStatusRequest['failed_reason_code'])) {
//            $failedReasonCode = trim($skrillStatusRequest['failed_reason_code']);
//            $this->setFailedReasonCode($failedReasonCode);
//            $this->setFailedReasonDescription(SkrillHelper::getSkrillFailureDescription($failedReasonCode));
//        }
//
//        if(isset($skrillStatusRequest['md5sig'])) {
//            $this->setMd5Signature($skrillStatusRequest['md5sig']);
//        }
//
//        if(isset($skrillStatusRequest['amount'])) {
//            $this->setMerchantAmount($skrillStatusRequest['amount']);
//        }
//
//        if(isset($skrillStatusRequest['currency'])) {
//            $this->setMerchantCurrency($skrillStatusRequest['currency']);
//        }
//
//        if(isset($skrillStatusRequest['payment_type'])) {
//            $this->setPaymentType($skrillStatusRequest['payment_type']);
//        }
//
//        if(isset($skrillStatusRequest['merchant_fields'])) {
//            // TODO: split to array
//            $this->setMerchantFields($skrillStatusRequest['merchant_fields']);
//        }
//    }
//
//    /**
//     * @param mixed $failed_reason_code
//     */
//    public function setFailedReasonCode($failed_reason_code)
//    {
//        $this->failed_reason_code = $failed_reason_code;
//    }
//
//    /**
//     * @return mixed
//     */
//    public function getFailedReasonCode()
//    {
//        return $this->failed_reason_code;
//    }
//
//    /**
//     * @param string $failed_reason_description
//     */
//    public function setFailedReasonDescription($failed_reason_description)
//    {
//        $this->failed_reason_description = $failed_reason_description;
//    }
//
//    /**
//     * @return string
//     */
//    public function getFailedReasonDescription()
//    {
//        return $this->failed_reason_description;
//    }
//    /**
//     * @param mixed $md5_signature
//     */
//    public function setMd5Signature($md5_signature)
//    {
//        $this->md5_signature = $md5_signature;
//    }
//
//    /**
//     * @return mixed
//     */
//    public function getMd5Signature()
//    {
//        return $this->md5_signature;
//    }
//
//    /**
//     * @param mixed $merchant_amount
//     */
//    public function setMerchantAmount($merchant_amount)
//    {
//        $this->merchant_amount = $merchant_amount;
//    }
//
//    /**
//     * @return mixed
//     */
//    public function getMerchantAmount()
//    {
//        return $this->merchant_amount;
//    }
//
//    /**
//     * @param mixed $merchant_currency
//     */
//    public function setMerchantCurrency($merchant_currency)
//    {
//        $this->merchant_currency = $merchant_currency;
//    }
//
//    /**
//     * @return mixed
//     */
//    public function getMerchantCurrency()
//    {
//        return $this->merchant_currency;
//    }
//
//    /**
//     * @param mixed $merchant_fields
//     */
//    public function setMerchantFields($merchant_fields)
//    {
//        $this->merchant_fields = $merchant_fields;
//    }
//
//    /**
//     * @return mixed
//     */
//    public function getMerchantFields()
//    {
//        return $this->merchant_fields;
//    }
//
//    /**
//     * @param mixed $merchant_id
//     */
//    public function setMerchantId($merchant_id)
//    {
//        $this->merchant_id = $merchant_id;
//    }
//
//    /**
//     * @return mixed
//     */
//    public function getMerchantId()
//    {
//        return $this->merchant_id;
//    }
//
//    /**
//     * @param mixed $merchant_transaction_id
//     */
//    public function setMerchantTransactionId($merchant_transaction_id)
//    {
//        $this->merchant_transaction_id = $merchant_transaction_id;
//    }
//
//    /**
//     * @return mixed
//     */
//    public function getMerchantTransactionId()
//    {
//        return $this->merchant_transaction_id;
//    }
//
//    /**
//     * @param mixed $pay_from_email
//     */
//    public function setCustomerEmail($pay_from_email)
//    {
//        $this->customer_email = $pay_from_email;
//    }
//
//    /**
//     * @return mixed
//     */
//    public function getCustomerEmail()
//    {
//        return $this->customer_email;
//    }
//
//    /**
//     * @param mixed $payed_amount
//     */
//    public function setPayedAmount($payed_amount)
//    {
//        $this->payed_amount = $payed_amount;
//    }
//
//    /**
//     * @return mixed
//     */
//    public function getPayedAmount()
//    {
//        return $this->payed_amount;
//    }
//
//    /**
//     * @param mixed $payment_type
//     */
//    public function setPaymentType($payment_type)
//    {
//        $this->payment_type = $payment_type;
//    }
//
//    /**
//     * @return mixed
//     */
//    public function getPaymentType()
//    {
//        return $this->payment_type;
//    }
//
//    /**
//     * @param mixed $skrill_currency
//     */
//    public function setSkrillCurrency($skrill_currency)
//    {
//        $this->skrill_currency = $skrill_currency;
//    }
//
//    /**
//     * @return mixed
//     */
//    public function getSkrillCurrency()
//    {
//        return $this->skrill_currency;
//    }
//
//    /**
//     * @param mixed $skrill_transaction_id
//     */
//    public function setSkrillTransactionId($skrill_transaction_id)
//    {
//        $this->skrill_transaction_id = $skrill_transaction_id;
//    }
//
//    /**
//     * @return mixed
//     */
//    public function getSkrillTransactionId()
//    {
//        return $this->skrill_transaction_id;
//    }
//
//    /**
//     * @param mixed $status
//     */
//    public function setStatus($status)
//    {
//        $this->status = $status;
//    }
//
//    /**
//     * @return mixed
//     */
//    public function getStatus()
//    {
//        return $this->status;
//    }
//
//    /**
//     * @param mixed $customer_id
//     */
//    public function setCustomerId($customer_id)
//    {
//        $this->customer_id = $customer_id;
//    }
//
//    /**
//     * @return mixed
//     */
//    public function getCustomerId()
//    {
//        return $this->customer_id;
//    }
//
//
//    /**
//     * @param mixed $pay_to_email
//     */
//    public function setMerchantEmail($pay_to_email)
//    {
//        $this->merchant_email = $pay_to_email;
//    }
//
//    /**
//     * @return mixed
//     */
//    public function getMerchantEmail()
//    {
//        return $this->merchant_email;
//    }
//}
