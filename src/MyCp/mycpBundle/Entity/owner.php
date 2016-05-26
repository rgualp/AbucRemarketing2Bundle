<?php

namespace MyCp\mycpBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * faq
 *
 * @ORM\Table(name="owner")
 * @ORM\Entity(repositoryClass="MyCp\mycpBundle\Entity\ownerRepository")
 */
class owner
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
     * @ORM\Column(name="full_name", type="string")
     */
    private $full_name;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string")
     */
    private $email;

    /**
     * @var string
     *
     * @ORM\Column(name="email_2", type="string", nullable=true)
     */
    private $email_2;

    /**
     * @var string
     *
     * @ORM\Column(name="mobile", type="string", nullable=true)
     */
    private $mobile;

    /**
     * @var string
     *
     * @ORM\Column(name="phone", type="string", nullable=true)
     */
    private $phone;

    /**
     * @var string
     *
     * @ORM\Column(name="address_main_street", type="string", nullable=true)
     */
    private $address_main_street;

    /**
     * @var string
     *
     * @ORM\Column(name="address_street_number", type="string", nullable=true)
     */
    private $address_street_number;

    /**
     * @var string
     *
     * @ORM\Column(name="address_between_1", type="string", nullable=true)
     */
    private $address_between_1;

    /**
     * @var string
     *
     * @ORM\Column(name="address_between_2", type="string", nullable=true)
     */
    private $address_between_2;

    /**
     * @ORM\ManyToOne(targetEntity="municipality")
     * @ORM\JoinColumn(name="municipality",referencedColumnName="mun_id", nullable=true)
     */
    private $municipality;

    /**
     * @ORM\ManyToOne(targetEntity="province")
     * @ORM\JoinColumn(name="province",referencedColumnName="prov_id", nullable=true)
     */
    private $province;

    /**
     * @ORM\OneToMany(targetEntity="ownerAccommodation",mappedBy="owner")
     */
    private $accommodations;

    /**
     * Constructor
     */
    public function __construct() {
        $this->accommodations = new ArrayCollection();
    }

    /**
     * @return string
     */
    public function getAddressBetween1()
    {
        return $this->address_between_1;
    }

    /**
     * @param string $address_between_1
     * @return mixed
     */
    public function setAddressBetween1($address_between_1)
    {
        $this->address_between_1 = $address_between_1;
        return $this;
    }

    /**
     * @return string
     */
    public function getAddressBetween2()
    {
        return $this->address_between_2;
    }

    /**
     * @param string $address_between_2
     * @return mixed
     */
    public function setAddressBetween2($address_between_2)
    {
        $this->address_between_2 = $address_between_2;
        return $this;
    }

    /**
     * @return string
     */
    public function getAddressMainStreet()
    {
        return $this->address_main_street;
    }

    /**
     * @param string $address_main_street
     * @return mixed
     */
    public function setAddressMainStreet($address_main_street)
    {
        $this->address_main_street = $address_main_street;
        return $this;
    }

    /**
     * @return string
     */
    public function getAddressStreetNumber()
    {
        return $this->address_street_number;
    }

    /**
     * @param string $address_street_number
     * @return mixed
     */
    public function setAddressStreetNumber($address_street_number)
    {
        $this->address_street_number = $address_street_number;
        return $this;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string $email
     * @return mixed
     */
    public function setEmail($email)
    {
        $this->email = $email;
        return $this;
    }

    /**
     * @return string
     */
    public function getEmail2()
    {
        return $this->email_2;
    }

    /**
     * @param string $email_2
     * @return mixed
     */
    public function setEmail2($email_2)
    {
        $this->email_2 = $email_2;
        return $this;
    }

    /**
     * @return string
     */
    public function getFullName()
    {
        return $this->full_name;
    }

    /**
     * @param string $full_name
     * @return mixed
     */
    public function setFullName($full_name)
    {
        $this->full_name = $full_name;
        return $this;
    }

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
    public function getMobile()
    {
        return $this->mobile;
    }

    /**
     * @param string $mobile
     * @return mixed
     */
    public function setMobile($mobile)
    {
        $this->mobile = $mobile;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getMunicipality()
    {
        return $this->municipality;
    }

    /**
     * @param mixed $municipality
     * @return mixed
     */
    public function setMunicipality($municipality)
    {
        $this->municipality = $municipality;
        return $this;
    }

    /**
     * @return string
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * @param string $phone
     * @return mixed
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getProvince()
    {
        return $this->province;
    }

    /**
     * @param mixed $province
     * @return mixed
     */
    public function setProvince($province)
    {
        $this->province = $province;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getAccommodations()
    {
        return $this->accommodations;
    }

    /**
     * @param mixed $accommodations
     * @return mixed
     */
    public function setAccommodations($accommodations)
    {
        $this->accommodations = $accommodations;
        return $this;
    }
    


}