<?php

namespace MyCp\mycpBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use MyCp\mycpBundle\Entity\generalReservation;

/**
 * availabilityOwner
 *
 * @ORM\Table(name="availability_owner")
 * @ORM\Entity(repositoryClass="MyCp\mycpBundle\Entity\availabilityOwnerRepository")
 */
class availabilityOwner
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="generalReservation",inversedBy="")
     * @ORM\JoinColumn(name="reservation",referencedColumnName="gen_res_id")
     */
    private $reservation;

    /**
     * @var integer
     *
     * @ORM\Column(name="res_status", type="integer")
     */
    private $resStatus;

    /**
     * @var boolean
     *
     * @ORM\Column(name="active", type="integer")
     */
    private $active;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set resStatus
     *
     * @param integer $resStatus
     *
     * @return availabilityOwner
     */
    public function setResStatus($resStatus)
    {
        $this->resStatus = $resStatus;

        return $this;
    }

    /**
     * Get resStatus
     *
     * @return integer
     */
    public function getResStatus()
    {
        return $this->resStatus;
    }

    /**
     * Set read
     *
     * @param integer $active
     *
     * @return availabilityOwner
     */
    public function setActive($active)
    {
        $this->active = $active;

        return $this;
    }

    /**
     * Get read
     *
     * @return integer
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * Set reservation
     *
     * @param generalReservation $reservation
     *
     * @return availabilityOwner
     */
    public function setReservation(generalReservation $reservation = null)
    {
        $this->reservation = $reservation;

        return $this;
    }

    /**
     * Get reservation
     *
     * @return generalReservation
     */
    public function getReservation()
    {
        return $this->reservation;
    }
}
