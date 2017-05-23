<?php


namespace MyCp\mycpBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * accommodationAward
 *
 * @ORM\Table(name="accommodation_booking_modality")
 * @ORM\Entity(repositoryClass="MyCp\mycpBundle\Entity\accommodationBookingModalityRepository")
 *
 */
class accommodationBookingModality
{
    /**
     * @ORM\Id
     * @ORM\OneToOne(targetEntity="ownership")
     * @ORM\JoinColumn(name="accommodation",referencedColumnName="own_id")
     */
    private $accommodation;

    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="bookingModality")
     * @ORM\JoinColumn(name="bookingModality",referencedColumnName="id")
     */
    private $bookingModality;

    /**
     * @ORM\Id
     * @ORM\Column(name="price", type="decimal", scale=2, nullable=true)
     */
    private $price;

    /**
     * @return mixed
     */
    public function getAccommodation()
    {
        return $this->accommodation;
    }

    /**
     * @param mixed $accommodation
     * @return mixed
     */
    public function setAccommodation($accommodation)
    {
        $this->accommodation = $accommodation;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getBookingModality()
    {
        return $this->bookingModality;
    }

    /**
     * @param mixed $bookingModality
     * @return mixed
     */
    public function setBookingModality($bookingModality)
    {
        $this->bookingModality = $bookingModality;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @param mixed $price
     * @return mixed
     */
    public function setPrice($price)
    {
        $this->price = $price;
        return $this;
    }


}