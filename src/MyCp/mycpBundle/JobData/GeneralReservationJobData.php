<?php

namespace MyCp\mycpBundle\JobData;

use Abuc\RemarketingBundle\JobData\JobData;


class GeneralReservationJobData extends JobData
{
    /**
     * @var int
     */
    private $reservationId;

    /**
     * @param int|null $paymentId
     */
    public function __construct($paymentId = null)
    {
        $this->reservationId = $paymentId;
    }

    /**
     * Set payment ID.
     *
     * @param $paymentId
     * @return $this For a fluent interface.
     */
    public function setReservationId($paymentId)
    {
        $this->reservationId = $paymentId;

        return $this;
    }

    /**
     * Returns payment ID.
     *
     * @return int
     */
    public function getReservationId()
    {
        return $this->reservationId;
    }
}
