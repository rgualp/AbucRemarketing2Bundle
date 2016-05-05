<?php

namespace MyCp\mycpBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * faq
 *
 * @ORM\Table(name="offerlog")
 * @ORM\Entity(repositoryClass="MyCp\mycpBundle\Entity\offerLogRepository")
 */
class offerLog
{
    /**
     * @var integer
     *
     * @ORM\Column(name="nom_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $log_id;

    /**
     * @ORM\ManyToOne(targetEntity="generalReservation")
     * @ORM\JoinColumn(name="log_offer_reservation",referencedColumnName="gen_res_id")
     */
    private $log_offer_reservation;

    /**
     * @ORM\ManyToOne(targetEntity="generalReservation")
     * @ORM\JoinColumn(name="log_from_reservation",referencedColumnName="gen_res_id", nullable=true)
     */
    private $log_from_reservation;

    /**
     * @ORM\ManyToOne(targetEntity="nomenclatorStat")
     * @ORM\JoinColumn(name="log_reason",referencedColumnName="nom_id", nullable=true)
     */
    private $log_reason;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="log_date", type="datetime")
     */
    private $log_date;

    /**
     * @ORM\ManyToOne(targetEntity="user")
     * @ORM\JoinColumn(name="log_created_by",referencedColumnName="user_id", nullable=true)
     */
    private $log_created_by;
    
    /**
     * Get log_id
     *
     * @return integer 
     */
    public function geLogId()
    {
        return $this->log_id;
    }

    /**
     * Set log_offer_reservation
     *
     * @param generalReservation $value
     * @return this
     */
    public function setLogOfferReservation($value)
    {
        $this->log_offer_reservation = $value;
    
        return $this;
    }

    /**
     * Get log_offer_reservation
     *
     * @return generalReservation
     */
    public function getLogOfferReservation()
    {
        return $this->log_offer_reservation;
    }

    /**
     * Set log_from_reservation
     *
     * @param generalReservation $value
     * @return this
     */
    public function setLogFromReservation($value)
    {
        $this->log_from_reservation = $value;
    
        return $this;
    }

    /**
     * Get log_from_reservation
     *
     * @return generalReservation
     */
    public function getLogFromReservation()
    {
        return $this->log_from_reservation;
    }

    /**
     * Set log_reason
     *
     * @param nomenclatorStat $value
     * @return this
     */
    public function setLogReason($value)
    {
        $this->log_reason = $value;

        return $this;
    }

    /**
     * Get log_reason
     *
     * @return nomenclatorStat
     */
    public function getLogReason()
    {
        return $this->log_reason;
    }

    /**
     * Set log_date
     *
     * @param \DateTime $value
     * @return this
     */
    public function setLogDate($value)
    {
        $this->log_date = $value;

        return $this;
    }

    /**
     * Get log_date
     *
     * @return DateTime
     */
    public function getLogDate()
    {
        return $this->log_date;
    }

    /**
     * @return mixed
     */
    public function getLogCreatedBy()
    {
        return $this->log_created_by;
    }

    /**
     * @param mixed $log_created_by
     * @return mixed
     */
    public function setLogCreatedBy($log_created_by)
    {
        $this->log_created_by = $log_created_by;
        return $this;
    }


}