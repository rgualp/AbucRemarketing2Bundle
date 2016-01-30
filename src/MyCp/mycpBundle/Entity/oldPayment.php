<?php


namespace MyCp\mycpBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * oldPayment
 *
 * @ORM\Table(name="old_payment")
 * @ORM\Entity
 *
 */
class oldPayment
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
     * @var string
     *
     * @ORM\Column(name="creation_date", type="string", nullable=true)
     */
    private $creation_date;

    /**
     * @var string
     *
     * @ORM\Column(name="reservation_code", type="string", nullable=true)
     */
    private $reservation_code;

    /**
     * @var string
     *
     * @ORM\Column(name="accommodation_code", type="string", nullable=true)
     */
    private $accommodation_code;

    /**
     * @var string
     *
     * @ORM\Column(name="tourist_full_name", type="string", nullable=true)
     */
    private $tourist_full_name;

    /**
     * @var string
     *
     * @ORM\Column(name="tourist_email", type="string", nullable=true)
     */
    private $tourist_email;

    /**
     * @var string
     *
     * @ORM\Column(name="tourist_country", type="string", nullable=true)
     */
    private $tourist_country;

    /**
     * @var string
     *
     * @ORM\Column(name="currency_code", type="string", nullable=true)
     */
    private $currency_code;

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
     * @var string
     *
     * @ORM\Column(name="arrival_date", type="string", nullable=true)
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
     * @var float
     *
     * @ORM\Column(name="pay_at_accommodation", type="float", precision=2, nullable=true)
     */
    private $pay_at_accommodation;

    /**
     * @var float
     *
     * @ORM\Column(name="prepay_amount", type="float", precision=2, nullable=true)
     */
    private $prepay_amount;

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
    public function getAccommodationCode()
    {
        return $this->accommodation_code;
    }

    public function setAccommodationCode($accommodation_code)
    {
        $this->accommodation_code = $accommodation_code;
        return $this;
    }

    /**
     * @return int
     */
    public function getAdults()
    {
        return $this->adults;
    }

    public function setAdults($adults)
    {
        $this->adults = $adults;
        return $this;
    }

    /**
     * @return string
     */
    public function getArrivalDate()
    {
        return $this->arrival_date;
    }

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

    public function setChildren($children)
    {
        $this->children = $children;
        return $this;
    }

    /**
     * @return string
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
     * @return string
     */
    public function getCurrencyCode()
    {
        return $this->currency_code;
    }

    public function setCurrencyCode($currency_code)
    {
        $this->currency_code = $currency_code;
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
     * @return float
     */
    public function getPayAtAccommodation()
    {
        return $this->pay_at_accommodation;
    }

    public function setPayAtAccommodation($pay_at_accommodation)
    {
        $this->pay_at_accommodation = $pay_at_accommodation;
        return $this;
    }

    /**
     * @return float
     */
    public function getPrepayAmount()
    {
        return $this->prepay_amount;
    }

    public function setPrepayAmount($prepay_amount)
    {
        $this->prepay_amount = $prepay_amount;
        return $this;
    }

    /**
     * @return string
     */
    public function getReservationCode()
    {
        return $this->reservation_code;
    }

    public function setReservationCode($reservation_code)
    {
        $this->reservation_code = $reservation_code;
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
    public function getTouristFullName()
    {
        return $this->tourist_full_name;
    }

    public function setTouristFullName($tourist_full_name)
    {
        $this->tourist_full_name = $tourist_full_name;
        return $this;
    }


}