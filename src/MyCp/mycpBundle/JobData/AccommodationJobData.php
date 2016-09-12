<?php

namespace MyCp\mycpBundle\JobData;

use Abuc\RemarketingBundle\JobData\JobData;

class AccommodationJobData extends JobData
{
    /**
     * @var int
     */
    private $accommodationId;

    /**
     * @param int|null $accommodationId
     */
    public function __construct($accommodationId = null)
    {
        $this->accommodationId = $accommodationId;
    }

    /**
     * Set accommodation ID.
     *
     * @param $accommodationId
     * @return $this For a fluent interface.
     */
    public function setAccommodationId($accommodationId)
    {
        $this->accommodationId = $accommodationId;

        return $this;
    }

    /**
     * Returns accommodation ID.
     *
     * @return int
     */
    public function getAccommodationId()
    {
        return $this->accommodationId;
    }
}
