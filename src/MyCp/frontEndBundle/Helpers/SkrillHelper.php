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

    // TODO: This map has to be tested!
    private static $localeToLanguageMap = array(
        'en' => 'EN',
        'es' => 'ES',
        'de' => 'DE',
        'it' => 'IT'
    );

    private static $currencyMap = array(

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
    public static function getSkrillStatusCodeDescription($skrillStatusCode)
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

    /**
     * Returns the language code used by Skrill according to the locale.
     *
     * @param $locale
     * @return string
     */
    public static function getSkrillLanguageFromLocale($locale)
    {
        if(isset(self::$localeToLanguageMap[$locale])) {
            return self::$localeToLanguageMap[$locale];
        }

        return '';
    }
}

