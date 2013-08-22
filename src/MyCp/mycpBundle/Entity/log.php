<?php

namespace MyCp\mycpBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * log
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="MyCp\mycpBundle\Entity\logRepository")
 */
class log
{
    /**
     * @var integer
     *
     * @ORM\Column(name="log_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $log_id;

    /**
     * @ORM\ManyToOne(targetEntity="user",inversedBy="")
     * @ORM\JoinColumn(name="log_user",referencedColumnName="user_id")
     */
    private $log_user;

    /**
     * @var string
     *
     * @ORM\Column(name="log_module", type="integer")
     */
    private $log_module;

    /**
     * @var string
     *
     * @ORM\Column(name="log_description", type="string", length=255)
     */
    private $log_description;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="log_date", type="date")
     */
    private $log_date;

    /**
     * @var string
     *
     * @ORM\Column(name="log_time", type="string", length=255)
     */
    private $log_time;

    /**
     * Get log_id
     *
     * @return integer 
     */
    public function getLogId()
    {
        return $this->log_id;
    }

    /**
     * Set log_module
     *
     * @param integer $logModule
     * @return log
     */
    public function setLogModule($logModule)
    {
        $this->log_module = $logModule;
    
        return $this;
    }

    /**
     * Get log_module
     *
     * @return integer 
     */
    public function getLogModule()
    {
        return $this->log_module;
    }

    /**
     * Set log_description
     *
     * @param string $logDescription
     * @return log
     */
    public function setLogDescription($logDescription)
    {
        $this->log_description = $logDescription;
    
        return $this;
    }

    /**
     * Get log_description
     *
     * @return string 
     */
    public function getLogDescription()
    {
        return $this->log_description;
    }

    /**
     * Set log_date
     *
     * @param \DateTime $logDate
     * @return log
     */
    public function setLogDate($logDate)
    {
        $this->log_date = $logDate;
    
        return $this;
    }

    /**
     * Get log_date
     *
     * @return \DateTime 
     */
    public function getLogDate()
    {
        return $this->log_date;
    }

    /**
     * Set log_user
     *
     * @param \MyCp\mycpBundle\Entity\user $logUser
     * @return log
     */
    public function setLogUser(\MyCp\mycpBundle\Entity\user $logUser = null)
    {
        $this->log_user = $logUser;
    
        return $this;
    }

    /**
     * Get log_user
     *
     * @return \MyCp\mycpBundle\Entity\user 
     */
    public function getLogUser()
    {
        return $this->log_user;
    }

    /**
     * Set log_time
     *
     * @param string $logTime
     * @return log
     */
    public function setLogTime($logTime)
    {
        $this->log_time = $logTime;
    
        return $this;
    }

    /**
     * Get log_time
     *
     * @return string 
     */
    public function getLogTime()
    {
        return $this->log_time;
    }
}