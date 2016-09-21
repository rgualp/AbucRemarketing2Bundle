<?php


namespace MyCp\PartnerBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * paReservationDetail
 *
 * @ORM\Table(name="pa_reservation_detail")
 * @ORM\Entity(repositoryClass="MyCp\PartnerBundle\Repository\paReservationDetailRepository")
 * @ORM\EntityListeners({"MyCp\PartnerBundle\Listener\BaseEntityListener"})
 *
 */
class paReservationDetail extends baseEntity
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
     * @ORM\ManyToOne(targetEntity="paReservation",inversedBy="details")
     * @ORM\JoinColumn(name="reservation",referencedColumnName="id")
     */
    private $reservation;

    /**
     * @ORM\ManyToOne(targetEntity="MyCp\mycpBundle\Entity\generalReservation",inversedBy="travelAgencyDetailReservations")
     * @ORM\JoinColumn(name="reservationDetail",referencedColumnName="gen_res_id", nullable=true)
     */
    private $reservationDetail;

    /**
     * @ORM\ManyToOne(targetEntity="MyCp\PartnerBundle\Entity\paGeneralReservation",inversedBy="travelAgencyOpenReservationsDetails")
     * @ORM\JoinColumn(name="openReservationDetail",referencedColumnName="id", nullable=true)
     */
    private $openReservationDetail;

    public function __construct()
    {
        parent::__construct();

    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
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
    public function getOpenReservationDetail()
    {
        return $this->openReservationDetail;
    }

    /**
     * @param mixed $reservationDetail
     * @return mixed
     */
    public function setOpenReservationDetail($reservationDetail)
    {
        $this->openReservationDetail = $reservationDetail;
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