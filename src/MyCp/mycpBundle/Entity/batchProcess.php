<?php

namespace MyCp\mycpBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

class batchType
{
    const BATCH_TYPE_ACCOMMODATION = 1;
    const BATCH_TYPE_PHOTO = 2;
}

class batchStatus
{
    const BATCH_STATUS_SUCCESS = 1;
    const BATCH_STATUS_WITH_ERRORS_ERROR = 2;
    const BATCH_STATUS_INCOMPLETE = 3;
}

/**
 * config
 *
 * @ORM\Table(name="batchprocess")
 * @ORM\Entity(repositoryClass="MyCp\mycpBundle\Entity\batchProcessRepository")
 */
class batchProcess
{
    /**
     * @var integer
     *
     * @ORM\Column(name="batch_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $batch_id;

    /**
     * @var integer
     *
     * @ORM\Column(name="batch_status", type="integer")
     */
    private $batch_status;

    /**
     * @var integer
     *
     * @ORM\Column(name="batch_type", type="integer")
     */
    private $batch_type;

    /**
     * @var integer
     *
     * @ORM\Column(name="batch_errors_count", type="integer")
     */
    private $batch_errors_count;

    /**
     * @var integer
     *
     * @ORM\Column(name="batch_elements_count", type="integer")
     */
    private $batch_elements_count;

    /**
     * @var integer
     *
     * @ORM\Column(name="batch_saved_elements_count", type="integer")
     */
    private $batch_saved_elements_count;

    /**
     * @var text
     *
     * @ORM\Column(name="batch_error_messages", type="text")
     */
    private $batch_error_messages;

    /**
     * @var datetime
     *
     * @ORM\Column(name="batch_start_date", type="datetime")
     */
    private $batch_start_date;

    /**
     * @var datetime
     *
     * @ORM\Column(name="batch_end_date", type="datetime")
     */
    private $batch_end_date;

    /**
     * Get conf_id
     *
     * @return integer
     */
    public function getBatchId()
    {
        return $this->batch_id;
    }

    /**
     * Set batch_elements_count
     *
     * @param integr $value
     * @return batchProcess
     */
    public function setBatchElementsCount($value)
    {
        $this->batch_elements_count = $value;

        return $this;
    }

    /**
     * Get batch_elements_count
     *
     * @return integer
     */
    public function getBatchElementsCount()
    {
        return $this->batch_elements_count;
    }

    /**
     * Set batch_end_date
     *
     * @param datetime $value
     * @return batchProcess
     */
    public function setBatchEndDate($value)
    {
        $this->batch_end_date = $value;

        return $this;
    }

    /**
     * Get batch_end_date
     *
     * @return datetime
     */
    public function getBatchEndDate()
    {
        return $this->batch_end_date;
    }

    /**
     * Set batch_error_messages
     *
     * @param string $value
     * @return batchProcess
     */
    public function setBatchErrorMessages($value)
    {
        $this->batch_error_messages = $value;

        return $this;
    }

    /**
     * Get batch_error_messages
     *
     * @return string
     */
    public function getBatchErrorMessages()
    {
        return $this->batch_error_messages;
    }

    /**
     * Set batch_errors_count
     *
     * @param integer $value
     * @return batchProcess
     */
    public function setBatchErrorsCount($value)
    {
        $this->batch_errors_count = $value;

        return $this;
    }

    /**
     * Get batch_errors_count
     *
     * @return integer
     */
    public function getBatchErrorsCount()
    {
        return $this->batch_errors_count;
    }

    /**
     * Set batch_saved_elements_count
     *
     * @param integer $value
     * @return batchProcess
     */
    public function setBatchSavedElementsCount($value)
    {
        $this->batch_saved_elements_count = $value;

        return $this;
    }

    /**
     * Get batch_saved_elements_count
     *
     * @return integer
     */
    public function getBatchSavedElementsCount()
    {
        return $this->batch_saved_elements_count;
    }

    /**
     * Set batch_start_date
     *
     * @param datetime $value
     * @return batchProcess
     */
    public function setBatchStartDate($value)
    {
        $this->batch_start_date = $value;

        return $this;
    }

    /**
     * Get batch_start_date
     *
     * @return datetime
     */
    public function getBatchStartDate()
    {
        return $this->batch_start_date;
    }

    /**
     * Set batch_status
     *
     * @param integer $value
     * @return batchProcess
     */
    public function setBatchStatus($value)
    {
        $this->batch_status = $value;

        return $this;
    }

    /**
     * Get batch_status
     *
     * @return integer
     */
    public function getBatchStatus()
    {
        return $this->batch_status;
    }

    /**
     * Set batch_type
     *
     * @param integer $value
     * @return batchProcess
     */
    public function setBatchType($value)
    {
        $this->batch_type = $value;

        return $this;
    }

    /**
     * Get batch_type
     *
     * @return integer
     */
    public function getBatchType()
    {
        return $this->batch_type;
    }
}