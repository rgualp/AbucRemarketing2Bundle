<?php


namespace MyCp\PartnerBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * paReservationDetail
 *
 * @ORM\Table(name="pa_reservation_detail")
 * @ORM\Entity(repositoryClass="MyCp\PartnerBundle\Repository\paReservationDetailRepository")
 *
 */
class paReservationDetail extends baseEntity
{

    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="paReservation",inversedBy="details")
     * @ORM\JoinColumn(name="reservation",referencedColumnName="id")
     */
    private $reservation;

    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="MyCp\mycpBundle\Entity\generalReservation",inversedBy="travelAgencyDetailReservations")
     * @ORM\JoinColumn(name="reservationDetail",referencedColumnName="gen_res_id")
     */
    private $reservationDetail;


    public function __construct() {
        parent::__construct();

    }

    /**
     * @return mixed
     */
    public function getReservationDetail()
    {
        return $this->reservationDetail;
    }

    /**
     * @param mixed $reservationDetail
     * @return mixed
     */
    public function setReservationDetail($reservationDetail)
    {
        $this->reservationDetail = $reservationDetail;
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