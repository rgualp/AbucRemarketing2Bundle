<?php

namespace MyCp\mycpBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * mycpService
 *
 * @ORM\Table(name="mycpservice")
 * @ORM\Entity()
 */
class mycpService
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
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var decimal
     *
     * @ORM\Column(name="price", type="decimal", nullable=true)
     */
    private $price;

    /**
     * @ORM\OneToMany(targetEntity="ownershipPayment",mappedBy="service")
     */
    private $payments;


    public function __construct()
    {
        $this->payments = new ArrayCollection();
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
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return decimal
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @param decimal $price
     * @return mixed
     */
    public function setPrice($price)
    {
        $this->price = $price;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPayments()
    {
        return $this->payments;
    }

    /**
     * @param mixed $payments
     * @return mixed
     */
    public function setPayments($payments)
    {
        $this->payments = $payments;
        return $this;
    }


}