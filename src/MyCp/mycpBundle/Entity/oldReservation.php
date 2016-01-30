<?php


namespace MyCp\mycpBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * oldReservation
 *
 * @ORM\Table(name="old_reservation")
 * @ORM\Entity
 *
 */
class oldReservation
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
     * @ORM\Column(name="ref_id", type="string")
     */
    private $ref_id;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="creation_date", type="date", nullable=true)
     */
    private $creation_date;

    /**
     * @var string
     *
     * @ORM\Column(name="accommodation_code", type="string", nullable=true)
     */
    private $accommodation_code;

    /**
     * @var string
     *
     * @ORM\Column(name="tourist_name", type="string", nullable=true)
     */
    private $tourist_name;


    /**
     * @var string
     *
     * @ORM\Column(name="tourist_lastname", type="string", nullable=true)
     */
    private $tourist_lastname;

    /**
     * @var string
     *
     * @ORM\Column(name="tourist_email", type="string", nullable=true)
     */
    private $tourist_email;

    /**
     * @var string
     *
     * @ORM\Column(name="tourist_address", type="string", nullable=true)
     */
    private $tourist_address;

    /**
     * @var string
     *
     * @ORM\Column(name="tourist_postal_code", type="string", nullable=true)
     */
    private $tourist_postal_code;

    /**
     * @var string
     *
     * @ORM\Column(name="tourist_city", type="string", nullable=true)
     */
    private $tourist_city;

    /**
     * @var string
     *
     * @ORM\Column(name="tourist_country", type="string", nullable=true)
     */
    private $tourist_country;

    /**
     * @var string
     *
     * @ORM\Column(name="tourist_phone", type="string", nullable=true)
     */
    private $tourist_phone;

    /**
     * @var string
     *
     * @ORM\Column(name="tourist_language", type="string", nullable=true)
     */
    private $tourist_language;

    /**
     * @var string
     *
     * @ORM\Column(name="tourist_currency", type="string", nullable=true)
     */
    private $tourist_currency;

    /**
     * @var integer
     *
     * @ORM\Column(name="adults", type="integer", nullable=true)
     */
    private $adults;

    /**
     * @var integer
     *
     * @ORM\Column(name="children", type="integer", nullable=true)
     */
    private $children;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="arrival_date", type="date", nullable=true)
     */
    private $arrival_date;

    /**
     * @var integer
     *
     * @ORM\Column(name="nights", type="integer", nullable=true)
     */
    private $nights;

    /**
     * @var integer
     *
     * @ORM\Column(name="rooms", type="integer", nullable=true)
     */
    private $rooms;

    /**
     * @var string
     *
     * @ORM\Column(name="comments", type="text", nullable=true)
     */
    private $comments;

    /**
     * @var string
     *
     * @ORM\Column(name="accommodation_name", type="string", nullable=true)
     */
    private $accommodation_name;

    /**
     * @var string
     *
     * @ORM\Column(name="accommodation_owners", type="string", nullable=true)
     */
    private $accommodation_owners;

    /**
     * @var string
     *
     * @ORM\Column(name="accommodation_address", type="string", nullable=true)
     */
    private $accommodation_address;

    /**
     * @var string
     *
     * @ORM\Column(name="accommodation_phone", type="string", nullable=true)
     */
    private $accommodation_phone;

    /**
     * @var string
     *
     * @ORM\Column(name="accommodation_cellphone", type="string", nullable=true)
     */
    private $accommodation_cellphone;

    /**
     * @var string
     *
     * @ORM\Column(name="reservation_code", type="string", nullable=true)
     */
    private $reservation_code;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getRefId()
    {
        return $this->ref_id;
    }

    public function setRefId($ref_id)
    {
        $this->ref_id = $ref_id;
        return $this;
    }


    /**
     * @return string
     */
    public function getAccommodationAddress()
    {
        return $this->accommodation_address;
    }

    /**
     * @param string $accommodation_address
     * @return $this
     */
    public function setAccommodationAddress($accommodation_address)
    {
        $this->accommodation_address = $accommodation_address;
        return $this;
    }

    /**
     * @return string
     */
    public function getAccommodationCellphone()
    {
        return $this->accommodation_cellphone;
    }

    /**
     * @param string $accommodation_cellphone
     * @return $this
     */
    public function setAccommodationCellphone($accommodation_cellphone)
    {
        $this->accommodation_cellphone = $accommodation_cellphone;
        return $this;
    }

    /**
     * @return string
     */
    public function getAccommodationCode()
    {
        return $this->accommodation_code;
    }

    /**
     * @param string $accommodation_code
     * @return $this
     */
    public function setAccommodationCode($accommodation_code)
    {
        $this->accommodation_code = $accommodation_code;
        return $this;
    }

    /**
     * @return string
     */
    public function getAccommodationName()
    {
        return $this->accommodation_name;
    }

    /**
     * @param string $accommodation_name
     * @return $this
     */
    public function setAccommodationName($accommodation_name)
    {
        $this->accommodation_name = $accommodation_name;
        return $this;
    }

    /**
     * @return string
     */
    public function getAccommodationOwners()
    {
        return $this->accommodation_owners;
    }

    /**
     * @param string $accommodation_owners     *
     * @return $this
     */
    public function setAccommodationOwners($accommodation_owners)
    {
        $this->accommodation_owners = $accommodation_owners;
        return $this;
    }

    /**
     * @return string
     */
    public function getAccommodationPhone()
    {
        return $this->accommodation_phone;
    }

    /**
     * @param string $accommodation_phone
     * @return $this
     */
    public function setAccommodationPhone($accommodation_phone)
    {
        $this->accommodation_phone = $accommodation_phone;
        return $this;
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
     * @return $this
     */
    public function setAdults($adults)
    {
        $this->adults = $adults;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getArrivalDate()
    {
        return $this->arrival_date;
    }

    /**
     * @param \DateTime $arrival_date
     * @return $this
     */
    public function setArrivalDate($arrival_date)
    {
        $this->arrival_date = $arrival_date;
        return $this;
    }

    /**
     * @return int
     */
    public function getChildren()
    {
        return $this->children;
    }

    function setChildren($children)
    {
        $this->children = $children;
        return $this;
    }

    /**
     * @return string
     */
    public function getComments()
    {
        return $this->comments;
    }

    public function setComments($comments)
    {
        $this->comments = $comments;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getCreationDate()
    {
        return $this->creation_date;
    }

    public function setCreationDate($creation_date)
    {
        $this->creation_date = $creation_date;
        return $this;
    }

    /**
     * @return int
     */
    public function getNights()
    {
        return $this->nights;
    }

    public function setNights($nights)
    {
        $this->nights = $nights;
        return $this;
    }

    /**
     * @return int
     */
    public function getRooms()
    {
        return $this->rooms;
    }

    public function setRooms($rooms)
    {
        $this->rooms = $rooms;
        return $this;
    }

    /**
     * @return string
     */
    public function getTouristAddress()
    {
        return $this->tourist_address;
    }

    public function setTouristAddress($tourist_address)
    {
        $this->tourist_address = $tourist_address;
        return $this;
    }

    /**
     * @return string
     */
    public function getTouristCity()
    {
        return $this->tourist_city;
    }

    public function setTouristCity($tourist_city)
    {
        $this->tourist_city = $tourist_city;
        return $this;
    }

    /**
     * @return string
     */
    public function getTouristCountry()
    {
        return $this->tourist_country;
    }

    public function setTouristCountry($tourist_country)
    {
        $this->tourist_country = $tourist_country;
        return $this;
    }

    /**
     * @return string
     */
    public function getTouristCurrency()
    {
        return $this->tourist_currency;
    }

    public function setTouristCurrency($tourist_currency)
    {
        $this->tourist_currency = $tourist_currency;
        return $this;
    }

    /**
     * @return string
     */
    public function getTouristEmail()
    {
        return $this->tourist_email;
    }

    public function setTouristEmail($tourist_email)
    {
        $this->tourist_email = $tourist_email;
        return $this;
    }

    /**
     * @return string
     */
    public function getTouristLanguage()
    {
        return $this->tourist_language;
    }

    public function setTouristLanguage($tourist_language)
    {
        $this->tourist_language = $tourist_language;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getTouristLastname()
    {
        return $this->tourist_lastname;
    }

    public function setTouristLastname($tourist_lastname)
    {
        $this->tourist_lastname = $tourist_lastname;
        return $this;
    }

    /**
     * @return string
     */
    public function getTouristName()
    {
        return $this->tourist_name;
    }

    public function setTouristName($tourist_name)
    {
        $this->tourist_name = $tourist_name;
        return $this;
    }

    /**
     * @return string
     */
    public function getTouristPhone()
    {
        return $this->tourist_phone;
    }

    public function setTouristPhone($tourist_phone)
    {
        $this->tourist_phone = $tourist_phone;
        return $this;
    }

    /**
     * @return string
     */
    public function getTouristPostalCode()
    {
        return $this->tourist_postal_code;
    }

    public function setTouristPostalCode($tourist_postal_code)
    {
        $this->tourist_postal_code = $tourist_postal_code;
        return $this;
    }

    /**
     * @return string
     */
    public function getReservationCode()
    {
        return $this->reservation_code;
    }

    /**
     * @param string $reservation_code
     */
    public function setReservationCode($reservation_code)
    {
        $this->reservation_code = $reservation_code;
    }



}