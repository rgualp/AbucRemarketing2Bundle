<?php


namespace MyCp\PartnerBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * paClientReservation
 *
 * @ORM\Table(name="pa_client_reservation")
 * @ORM\Entity(repositoryClass="MyCp\PartnerBundle\Repository\paClientReservationRepository")
 *
 */
class paClientReservation extends baseEntity
{

    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="paClient",inversedBy="travelAgencyClientReservations")
     * @ORM\JoinColumn(name="client",referencedColumnName="id")
     */
    private $client;

    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="MyCp\mycpBundle\Entity\generalReservation",inversedBy="travelAgencyClientReservations")
     * @ORM\JoinColumn(name="reservation",referencedColumnName="gen_res_id")
     */
    private $reservation;


    public function __construct() {
        parent::__construct();

    }

    /**
     * @return mixed
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * @param mixed $client
     * @return mixed
     */
    public function setClient($client)
    {
        $this->client = $client;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getReservation()
    {
        return $this->reservation;
    }

    /**
     * @param mixed $reservation
     * @return mixed
     */
    public function setReservation($reservation)
    {
        $this->reservation = $reservation;
        return $this;
    }


}