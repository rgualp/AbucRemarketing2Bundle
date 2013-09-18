<?php

namespace MyCp\mycpBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * userHistory
 *
 * @ORM\Table(name="userhistory")
 * @ORM\Entity(repositoryClass="MyCp\mycpBundle\Entity\userHistoryRepository")
 *
 */
class userHistory
{
    /**
     * @var integer
     *
     * @ORM\Column(name="user_history_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $user_history_id;
    
    /**
     * @ORM\ManyToOne(targetEntity="user",inversedBy="")
     * @ORM\JoinColumn(name="user_history_user",referencedColumnName="user_id", nullable=true)
     */
    private $user_history_user;

    /**
     * @var string
     *
     * @ORM\Column(name="user_history_session_id", type="string", length=255, nullable=true)
     */
    private $user_history_session_id;
    
    /**
     * @ORM\ManyToOne(targetEntity="ownership",inversedBy="")
     * @ORM\JoinColumn(name="user_history_ownership",referencedColumnName="own_id", nullable=true)
     */
    private $user_history_ownership;
    
    /**
     * @ORM\ManyToOne(targetEntity="destination",inversedBy="")
     * @ORM\JoinColumn(name="user_history_destination",referencedColumnName="des_id", nullable=true)
     */
    private $user_history_destination;
    
     /**
     * @var datetime
     *
     * @ORM\Column(name="user_history_visit_date", type="datetime", nullable=true)
     */
    private $user_history_visit_date;
    
    /**
     * @var integer
     *
     * @ORM\Column(name="user_history_visit_count", type="integer")
     */
    private $user_history_visit_count;

    /**
     * Get user_history_id
     *
     * @return integer 
     */
    public function getUserHistoryId()
    {
        return $this->user_history_id;
    }
    
    /**
     * Set user_history_visit_count
     *
     * @param integer $value
     * @return userHistory
     */
    public function setUserHistoryVisitCount($value)
    {
        $this->user_history_visit_count = $value;
    
        return $this;
    }

    /**
     * Get user_history_visit_count
     *
     * @return \DateTime 
     */
    public function getUserHistoryVisitCount()
    {
        return $this->user_history_visit_count;
    }
    
    /**
     * Set user_history_visit_date
     *
     * @param \DateTime $value
     * @return userHistory
     */
    public function setUserHistoryVisitDate($value)
    {
        $this->user_history_visit_date = $value;
    
        return $this;
    }

    /**
     * Get user_history_visit_date
     *
     * @return \DateTime 
     */
    public function getUserHistoryVisitDate()
    {
        return $this->user_history_visit_date;
    }

    /**
     * Set user_history_session_id
     *
     * @param string $value
     * @return userHistory
     */
    public function setUserHistorySessionId($value = null)
    {
        if($value != null)
           $this->user_history_session_id = $value;
    
        return $this;
    }

    /**
     * Get user_history_session_id
     *
     * @return string 
     */
    public function getUserHistorySessionId()
    {
        return $this->user_history_session_id;
    }

    /**
     * Set user_history_ownership
     *
     * @param \MyCp\mycpBundle\Entity\ownership $value
     * @return userHistory
     */
    public function setUserHistoryOwnership(\MyCp\mycpBundle\Entity\ownership $value = null)
    {
        if($value != null)
           $this->user_history_ownership = $value;
    
        return $this;
    }

    /**
     * Get user_history_ownership
     *
     * @return \MyCp\mycpBundle\Entity\ownership 
     */
    public function getUserHistoryOwnership()
    {
        return $this->user_history_ownership;
    }

    /**
     * Set user_history_destination
     *
     * @param \MyCp\mycpBundle\Entity\destination $value
     * @return userHistory
     */
    public function setUserHistoryDestination(\MyCp\mycpBundle\Entity\destination $value = null)
    {
        if($value != null)
           $this->user_history_destination = $value;
    
        return $this;
    }

    /**
     * Get user_history_destination
     *
     * @return \MyCp\mycpBundle\Entity\destination 
     */
    public function getUserHistoryDestination()
    {
        return $this->user_history_destination;
    }

    /**
     * Set favorite_user
     *
     * @param \MyCp\mycpBundle\Entity\user $value
     * @return userHistory
     */
    public function setUserHistoryUser(\MyCp\mycpBundle\Entity\user $value = null)
    {
        if($value != null)
           $this->user_history_user = $value;
    
        return $this;
    }

    /**
     * Get favorite_user
     *
     * @return \MyCp\mycpBundle\Entity\user 
     */
    public function getUserHistoryUser()
    {
        return $this->user_history_user;
    }
}