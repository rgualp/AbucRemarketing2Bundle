<?php


namespace MyCp\PartnerBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * paClient
 *
 * @ORM\Table(name="pa_client")
 * @ORM\Entity(repositoryClass="MyCp\PartnerBundle\Repository\paClientRepository")
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
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="lastname", type="string", length=255)
     */
    private $lastname;


    /**
     * @ORM\ManyToOne(targetEntity="MyCP\mycpBundle\Entity\country",inversedBy="travelAgencyClients")
     * @ORM\JoinColumn(name="country",referencedColumnName="co_id")
     */
    private $country;

    /**
     * @ORM\OneToMany(targetEntity="paClientReservation", mappedBy="client")
     */
    private $travelAgencyClientReservations;


    public function __construct() {
        parent::__construct();

        $this->travelAgencyClientReservations = new ArrayCollection();
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
     * @return string
     */
    public function getLastname()
    {
        return $this->lastname;
    }

    /**
     * @param string $lastname
     * @return mixed
     */
    public function setLastname($lastname)
    {
        $this->lastname = $lastname;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     * @return mixed
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getFullName()
    {
        return $this->name. " " .$this->lastname;
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
    public function getTravelAgencyClientReservations()
    {
        return $this->travelAgencyClientReservations;
    }

    /**
     * @param mixed $clientReservations
     * @return mixed
     */
    public function setTravelAgencyClientReservations($clientReservations)
    {
        $this->travelAgencyClientReservations = $clientReservations;
        return $this;
    }

    /**
     * Add paClientReservation
     *
     * @param paClientReservation $clientReservations
     *
     * @return mixed
     */
    public function addTravelAgencyClientReservation(paClientReservation $clientReservations)
    {
        $this->travelAgencyClientReservations[] = $clientReservations;

        return $this;
    }

    /**
     * Remove paClientReservation
     *
     * @param paClientReservation $clientReservations
     */
    public function removeTravelAgencyClientReservation(paClientReservation $clientReservations)
    {
        $this->travelAgencyClientReservations->removeElement($clientReservations);
    }

}