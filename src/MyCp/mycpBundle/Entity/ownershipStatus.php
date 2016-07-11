<?php

namespace MyCp\mycpBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ownershipStatus
 *
 * @ORM\Table(name="ownershipstatus")
 * @ORM\Entity()
 */
class ownershipStatus
{
    /**
     * All allowed statuses
     */
    const NO_STATUS = 0;
    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 2;
    const STATUS_IN_PROCESS = 3;
    const STATUS_DELETED = 4;
    const STATUS_BATCH_PROCESS = 5;
    const STATUS_INSERTED_BY_OWNER = 6;

    /**
     * Contains all possible statuses
     *
     * @var array
     */
    private $statuses = array(
        self::NO_STATUS,
        self::STATUS_ACTIVE,
        self::STATUS_INACTIVE,
        self::STATUS_IN_PROCESS,
        self::STATUS_DELETED,
        self::STATUS_BATCH_PROCESS,
        self::STATUS_INSERTED_BY_OWNER
    );


    /**
     * @var integer
     *
     * @ORM\Column(name="status_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $status_id;

    /**
     * @var string
     *
     * @ORM\Column(name="status_name", type="string", length=255)
     */
    private $status_name;

    /**
     * Get status_id
     *
     * @return integer
     */
    public function getStatusId()
    {
        return $this->status_id;
    }

    /**
     * Set status_name
     *
     * @param string $statusName
     * @return ownershipStatus
     */
    public function setStatusName($statusName)
    {
        $this->status_name = $statusName;

        return $this;
    }

    /**
     * Get status_name
     *
     * @return string
     */
    public function getStatusName()
    {
        return $this->status_name;
    }

    public static function statusName($status_id)
    {
        switch($status_id)
        {
            case ownershipStatus::STATUS_ACTIVE: return "OWN_STATUS_ACTIVE";
            case ownershipStatus::STATUS_DELETED: return "OWN_STATUS_DELETED";
            case ownershipStatus::STATUS_INACTIVE: return "OWN_STATUS_INACTIVE";
            case ownershipStatus::STATUS_IN_PROCESS: return "OWN_STATUS_IN_PROCESS";
            case ownershipStatus::STATUS_INSERTED_BY_OWNER: return "OWN_STATUS_INSERTED_BY_OWNER";
            default: return "OWN_NO_STATUS";
        }
    }
}