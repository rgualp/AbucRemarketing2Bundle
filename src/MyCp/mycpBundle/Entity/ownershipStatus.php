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
}