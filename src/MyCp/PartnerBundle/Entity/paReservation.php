<?php


namespace MyCp\PartnerBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * paReservation
 *
 * @ORM\Table(name="pa_reservation")
 * @ORM\Entity(repositoryClass="MyCp\PartnerBundle\Repository\paReservationRepository")
 *
 */
class paReservation extends baseEntity
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
     * @var integer
     *
     * @ORM\Column(name="number", type="integer")
     */
    private $number;

    /**
     * @var time
     *
     * @ORM\Column(name="arrivalHour", type="time")
     */
    private $arrivalHour;

    /**
     * @var integer
     *
     * @ORM\Column(name="adults", type="integer")
     */
    private $adults;

    /**
     * @var integer
     *
     * @ORM\Column(name="infants", type="integer")
     */
    private $infants;

    /**
     * @var integer
     *
     * @ORM\Column(name="children", type="integer")
     */
    private $children;


    /**
     * @ORM\ManyToOne(targetEntity="paClient",inversedBy="reservations")
     * @ORM\JoinColumn(name="client",referencedColumnName="id", nullable=true)
     */
    private $client;

    /**
     * @ORM\OneToMany(targetEntity="paReservationDetail", mappedBy="client")
     */
    private $details;


    public function __construct() {
        parent::__construct();

        $this->details = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getAdults()
    {
        return $this->adults;
    }

    /**
     * @param int $adults
     * @return mixed
     */
    public function setAdults($adults)
    {
        $this->adults = $adults;
        return $this;
    }

    /**
     * @return time
     */
    public function getArrivalHour()
    {
        return $this->arrivalHour;
    }

    /**
     * @param time $arrivalHour
     * @return mixed
     */
    public function setArrivalHour($arrivalHour)
    {
        $this->arrivalHour = $arrivalHour;
        return $this;
    }

    /**
     * @return int
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * @param int $children
     * @return mixed
     */
    public function setChildren($children)
    {
        $this->children = $children;
        return $this;
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
     * @return int
     */
    public function getInfants()
    {
        return $this->infants;
    }

    /**
     * @param int $infants
     * @return mixed
     */
    public function setInfants($infants)
    {
        $this->infants = $infants;
        return $this;
    }

    /**
     * @return int
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * @param int $number
     * @return mixed
     */
    public function setNumber($number)
    {
        $this->number = $number;
        return $this;
    }



    /**
     * @return mixed
     */
    public function getDetails()
    {
        return $this->details;
    }

    /**
     * @param mixed $details
     * @return mixed
     */
    public function setDetails($details)
    {
        $this->details = $details;
        return $this;
    }

    /**
     * Add paReservationDetail
     *
     * @param paReservationDetail $detail
     *
     * @return mixed
     */
    public function addTravelAgencyClientReservation(paReservationDetail $detail)
    {
        $this->details[] = $detail;

        return $this;
    }

    /**
     * Remove paReservationDetail
     *
     * @param paReservationDetail $detail
     */
    public function removeTravelAgencyClientReservation(paReservationDetail $detail)
    {
        $this->details->removeElement($detail);
    }

}