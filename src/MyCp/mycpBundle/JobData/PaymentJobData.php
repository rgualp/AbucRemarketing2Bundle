<?php

namespace MyCp\mycpBundle\JobData;

use Abuc\RemarketingBundle\JobData\JobData;

class PaymentJobData extends JobData
{
    /**
     * @var int
     */
    private $paymentId;

    /**
     * @param int|null $paymentId
     */
    public function __construct($paymentId = null)
    {
        $this->paymentId = $paymentId;
    }

    /**
     * Set payment ID.
     *
     * @param $paymentId
     * @return $this For a fluent interface.
     */
    public function setPaymentId($paymentId)
    {
        $this->paymentId = $paymentId;

        return $this;
    }

    /**
     * Returns payment ID.
     *
     * @return int
     */
    public function getPaymentId()
    {
        return $this->paymentId;
    }
}
