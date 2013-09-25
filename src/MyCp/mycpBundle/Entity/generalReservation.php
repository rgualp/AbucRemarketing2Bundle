<?php

namespace MyCp\mycpBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * generalreservation
 *
 * @ORM\Table(name="generalreservation")
 * @ORM\Entity(repositoryClass="MyCp\mycpBundle\Entity\generalReservationRepository")
 */
class generalReservation
{
    /**
     * @var integer
     *
     * @ORM\Column(name="gen_res_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $gen_res_id;

    /**
     * @ORM\ManyToOne(targetEntity="user",inversedBy="genResUser")
     * @ORM\JoinColumn(name="gen_res_user_id",referencedColumnName="user_id")
     */
    private $gen_res_user_id;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="gen_res_date", type="date")
     */
    private $gen_res_date;

    /**
     * @var integer
     *
     * @ORM\Column(name="gen_res_status", type="integer")
     */
    private $gen_res_status;

    /**
     * @var boolean
     *
     * @ORM\Column(name="gen_res_saved", type="boolean")
     */
    private $gen_res_saved;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="gen_res_status_date", type="date")
     */
    private $gen_res_status_date;



    /**
     * @var \DateTime
     *
     * @ORM\Column(name="gen_res_from_date", type="date")
     */
    private $gen_res_from_date;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="gen_res_to_date", type="date")
     */
    private $gen_res_to_date;

    /**
     * Get gen_res_id
     *
     * @return integer 
     */
    public function getGenResId()
    {
        return $this->gen_res_id;
    }

    /**
     * Set gen_res_user_id
     *
     * @param \MyCp\mycpBundle\Entity\user $genResUserId
     * @return generalReservation
     */
    public function setGenResUserId(\MyCp\mycpBundle\Entity\user $genResUserId = null)
    {
        $this->gen_res_user_id = $genResUserId;
    
        return $this;
    }

    /**
     * Get gen_res_user_id
     *
     * @return \MyCp\mycpBundle\Entity\user 
     */
    public function getGenResUserId()
    {
        return $this->gen_res_user_id;
    }

    /**
     * Set gen_res_date
     *
     * @param \DateTime $genResDate
     * @return generalReservation
     */
    public function setGenResDate($genResDate)
    {
        $this->gen_res_date = $genResDate;
    
        return $this;
    }

    /**
     * Get gen_res_date
     *
     * @return \DateTime 
     */
    public function getGenResDate()
    {
        return $this->gen_res_date;
    }


    /**
     * Set gen_res_status
     *
     * @param integer $genResStatus
     * @return generalReservation
     */
    public function setGenResStatus($genResStatus)
    {
        $this->gen_res_status = $genResStatus;
    
        return $this;
    }

    /**
     * Get gen_res_status
     *
     * @return integer 
     */
    public function getGenResStatus()
    {
        return $this->gen_res_status;
    }

    /**
     * Set gen_res_status_date
     *
     * @param \DateTime $genResStatusDate
     * @return generalReservation
     */
    public function setGenResStatusDate($genResStatusDate)
    {
        $this->gen_res_status_date = $genResStatusDate;
    
        return $this;
    }

    /**
     * Get gen_res_status_date
     *
     * @return \DateTime 
     */
    public function getGenResStatusDate()
    {
        return $this->gen_res_status_date;
    }

    /**
     * Set gen_res_saved
     *
     * @param boolean $genResSaved
     * @return generalReservation
     */
    public function setGenResSaved($genResSaved)
    {
        $this->gen_res_saved = $genResSaved;
    
        return $this;
    }

    /**
     * Get gen_res_saved
     *
     * @return boolean 
     */
    public function getGenResSaved()
    {
        return $this->gen_res_saved;
    }

    /**
     * Set gen_res_from_date
     *
     * @param \DateTime $genResFromDate
     * @return generalReservation
     */
    public function setGenResFromDate($genResFromDate)
    {
        $this->gen_res_from_date = $genResFromDate;
    
        return $this;
    }

    /**
     * Get gen_res_from_date
     *
     * @return \DateTime 
     */
    public function getGenResFromDate()
    {
        return $this->gen_res_from_date;
    }

    /**
     * Set gen_res_to_date
     *
     * @param \DateTime $genResToDate
     * @return generalReservation
     */
    public function setGenResToDate($genResToDate)
    {
        $this->gen_res_to_date = $genResToDate;
    
        return $this;
    }

    /**
     * Get gen_res_to_date
     *
     * @return \DateTime 
     */
    public function getGenResToDate()
    {
        return $this->gen_res_to_date;
    }
}