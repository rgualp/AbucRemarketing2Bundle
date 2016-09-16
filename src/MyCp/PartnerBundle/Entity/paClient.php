<?php


namespace MyCp\PartnerBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * paClient
 *
 * @ORM\Table(name="pa_client")
 * @ORM\Entity(repositoryClass="MyCp\PartnerBundle\Repository\paClientRepository")
 * @ORM\EntityListeners({"MyCp\PartnerBundle\Listener\BaseEntityListener"})
 *
 */
class paClient extends baseEntity
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
     * @var string
     *
     * @ORM\Column(name="passport", type="string", length=255, nullable=true)
     */
    private $passport;

    /**
     * @var string
     *
     * @ORM\Column(name="fullname", type="string", length=255)
     */
    private $fullname;

    /**
     * @ORM\ManyToOne(targetEntity="MyCp\mycpBundle\Entity\country",inversedBy="travelAgencyClients")
     * @ORM\JoinColumn(name="country",referencedColumnName="co_id", nullable=true)
     */
    private $country;

    /**
     * @ORM\ManyToOne(targetEntity="paTravelAgency",inversedBy="clients")
     * @ORM\JoinColumn(name="travelAgency",referencedColumnName="id")
     */
    private $travelAgency;

    /**
     * @ORM\OneToMany(targetEntity="paReservation", mappedBy="client")
     */
    private $reservations;

    public function __construct() {
        parent::__construct();

        $this->reservations = new ArrayCollection();
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
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * @param mixed $country
     * @return mixed
     */
    public function setCountry($country)
    {
        $this->country = $country;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getFullName()
    {
        return $this->fullname;
    }

    /**
     * @param mixed $name
     * @return mixed
     */
    public function setFullName($name)
    {
        $this->fullname = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getPassport()
    {
        return $this->passport;
    }

    /**
     * @param string $passport
     * @return mixed
     */
    public function setPassport($passport)
    {
        $this->passport = $passport;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getTravelAgency()
    {
        return $this->travelAgency;
    }

    /**
     * @param mixed $travelAgency
     * @return mixed
     */
    public function setTravelAgency($travelAgency)
    {
        $this->travelAgency = $travelAgency;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getReservations()
    {
        return $this->reservations;
    }

    /**
     * @param mixed $reservations
     * @return mixed
     */
    public function setReservations($reservations)
    {
        $this->reservations = $reservations;
        return $this;
    }

    /**
     * Add paReservation
     *
     * @param paReservation $reservation
     *
     * @return mixed
     */
    public function addReservation(paReservation $reservation)
    {
        $this->reservations[] = $reservation;

        return $this;
    }

    /**
     * Remove paReservation
     *
     * @param paReservation $reservation
     */
    public function removeReservation(paReservation $reservation)
    {
        $this->reservations->removeElement($reservation);
    }


}