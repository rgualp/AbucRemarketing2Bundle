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
     * All allowed statuses adding text
     */

    const OPERATION_INSERT = 1;
    const OPERATION_UPDATE = 2;
    const OPERATION_DELETE= 3;
    const OPERATION_VISIT = 4;
    const OPERATION_LOGIN = 5;
    const OPERATION_LOGOUT = 6;

    /**
     * Contains all possible statuses
     *
     * @var array
     */
    private $operations = array(
        self::OPERATION_INSERT,
        self::OPERATION_UPDATE,
        self::OPERATION_DELETE,
        self::OPERATION_VISIT,
        self::OPERATION_LOGIN,
        self::OPERATION_LOGOUT
    );

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
     * @ORM\Column(name="log_description", type="text")
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
     * @var string
     *
     * @ORM\Column(name="operation", type="integer", nullable=true)
     */
    private $operation;

    /**
     * @var string
     *
     * @ORM\Column(name="db_table", type="string", length=255, nullable=true)
     */
    private $db_table;

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

    /**
     * @return string
     */
    public function getDbTable()
    {
        return $this->db_table;
    }

    /**
     * @param string $db_table
     * @return log
     */
    public function setDbTable($db_table)
    {
        $this->db_table = $db_table;
        return $this;
    }

    /**
     * @return string
     */
    public function getOperation()
    {
        return $this->operation;
    }

    /**
     * @param string $operation
     * @return log
     */
    public function setOperation($operation)
    {
        $this->operation = $operation;
        return $this;
    }

    public static function getOperationName($operation)
    {
        switch($operation){
            case self::OPERATION_DELETE: return "Eliminar";
            case self::OPERATION_INSERT: return "Insertar";
            case self::OPERATION_LOGIN: return "Autenticar";
            case self::OPERATION_LOGOUT: return "Cerrar sesión";
            case self::OPERATION_UPDATE: return "Actualizar";
            case self::OPERATION_VISIT: return "Visita";
            default: return "(No especificada)";
        }
    }

}