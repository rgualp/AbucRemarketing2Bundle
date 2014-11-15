<?php

namespace MyCp\mycpBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * userHistory
 *
 * @ORM\Table(name="cart")
 * @ORM\Entity(repositoryClass="MyCp\mycpBundle\Entity\cartRepository")
 *
 */
class cart
{
    /**
     * @var integer
     *
     * @ORM\Column(name="cart_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $cart_id;

    /**
     * @ORM\ManyToOne(targetEntity="user",inversedBy="")
     * @ORM\JoinColumn(name="cart_user",referencedColumnName="user_id", nullable=true)
     */
    private $cart_user;

    /**
     * @var string
     *
     * @ORM\Column(name="cart_session_id", type="string", length=255, nullable=true)
     */
    private $cart_session_id;

    /**
     * @ORM\ManyToOne(targetEntity="room",inversedBy="")
     * @ORM\JoinColumn(name="cart_room",referencedColumnName="room_id")
     */
    private $cart_room;

    /**
     * @var integer
     *
     * @ORM\Column(name="cart_count_adults", type="integer")
     */
    private $cart_count_adults;

    /**
     * @var integer
     *
     * @ORM\Column(name="cart_count_children", type="integer")
     */
    private $cart_count_children;

    /**
     * @var timestamp
     *
     * @ORM\Column(name="cart_date_from", type="bigint")
     */
    private $cart_date_from;

    /**
     * @var timestamp
     *
     * @ORM\Column(name="cart_date_to", type="bigint")
     */
    private $cart_date_to;


     /**
     * @var datetime
     *
     * @ORM\Column(name="cart_created_date", type="datetime")
     */
    private $cart_created_date;

    /**
     * Constructor
     */
    public function __construct() {
        $this->cart_created_date = new \DateTime();
    }


    /**
     * Get cart_id
     *
     * @return integer
     */
    public function getCartId()
    {
        return $this->cart_id;
    }

    /**
     * Set cart_count_adults
     *
     * @param integer $value
     * @return cart
     */
    public function setCartCountAdults($value)
    {
        $this->cart_count_adults = $value;

        return $this;
    }

    /**
     * Get cart_count_adults
     *
     * @return integer
     */
    public function getCartCountAdults()
    {
        return $this->cart_count_adults;
    }

    /**
     * Set cart_count_children
     *
     * @param integer $value
     * @return cart
     */
    public function setCartCountChildren($value)
    {
        $this->cart_count_children = $value;

        return $this;
    }

    /**
     * Get cart_count_children
     *
     * @return integer
     */
    public function getCartCountChildren()
    {
        return $this->cart_count_children;
    }

    /**
     * Set cart_session_id
     *
     * @param string $value
     * @return cart
     */
    public function setCartSessionId($value = null)
    {
        if($value != null)
           $this->cart_session_id = $value;

        return $this;
    }

    /**
     * Get cart_session_id
     *
     * @return string
     */
    public function getCartSessionId()
    {
        return $this->cart_session_id;
    }

    /**
     * Set cart_room
     *
     * @param \MyCp\mycpBundle\Entity\room $value
     * @return cart
     */
    public function setCartRoom(\MyCp\mycpBundle\Entity\room $value)
    {
        $this->cart_room = $value;
        return $this;
    }

    /**
     * Get cart_room
     *
     * @return \MyCp\mycpBundle\Entity\room
     */
    public function getCartRoom()
    {
        return $this->cart_room;
    }

    /**
     * Set cart_user
     *
     * @param \MyCp\mycpBundle\Entity\user $value
     * @return cart
     */
    public function setCartUser(\MyCp\mycpBundle\Entity\user $value = null)
    {
        if($value != null)
           $this->cart_user = $value;

        return $this;
    }

    /**
     * Get cart_user
     *
     * @return \MyCp\mycpBundle\Entity\user
     */
    public function getCartUser()
    {
        return $this->cart_user;
    }

    /**
     * Set cart_date_from
     *
     * @param bigint $value
     * @return cart
     */
    public function setCartDateFrom($value)
    {
        $this->cart_date_from = $value;
        return $this;
    }

    /**
     * Get cart_date_from
     *
     * @return bigint
     */
    public function getCartDateFrom()
    {
        return $this->cart_date_from;
    }

    /**
     * Set cart_date_to
     *
     * @param bigint $value
     * @return cart
     */
    public function setCartDateTo($value)
    {
        $this->cart_date_to = $value;
        return $this;
    }

    /**
     * Get cart_date_to
     *
     * @return bigint
     */
    public function getCartDateTo()
    {
        return $this->cart_date_to;
    }

    /**
     * Set cart_created_date
     *
     * @param DateTime $value
     * @return cart
     */
    public function setCartCreatedDate($value)
    {
        $this->cart_created_date = $value;
        return $this;
    }

    /**
     * Get cart_created_date
     *
     * @return DateTime
     */
    public function getCartCreatedDate()
    {
        return $this->cart_created_date;
    }
}