<?php


namespace MyCp\frontEndBundle\Helpers;

final class PaymentHelper
{
    /**
     * The possible payment statuses.
     */
    const STATUS_PENDING = 0;
    const STATUS_SUCCESS = 1;
    const STATUS_CANCELLED = 2;
    const STATUS_FAILED = 3;
}