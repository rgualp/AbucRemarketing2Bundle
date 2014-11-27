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
class cart {

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
     * @ORM\Column(name="cart_date_from", type="datetime")
     */
    private $cart_date_from;

    /**
     * @var timestamp
     *
     * @ORM\Column(name="cart_date_to", type="datetime")
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
    public function getCartId() {
        return $this->cart_id;
    }

    /**
     * Set cart_count_adults
     *
     * @param integer $value
     * @return cart
     */
    public function setCartCountAdults($value) {
        $this->cart_count_adults = $value;

        return $this;
    }

    /**
     * Get cart_count_adults
     *
     * @return integer
     */
    public function getCartCountAdults() {
        return $this->cart_count_adults;
    }

    /**
     * Set cart_count_children
     *
     * @param integer $value
     * @return cart
     */
    public function setCartCountChildren($value) {
        $this->cart_count_children = $value;

        return $this;
    }

    /**
     * Get cart_count_children
     *
     * @return integer
     */
    public function getCartCountChildren() {
        return $this->cart_count_children;
    }

    /**
     * Set cart_session_id
     *
     * @param string $value
     * @return cart
     */
    public function setCartSessionId($value = null) {
        $this->cart_session_id = $value;

        return $this;
    }

    /**
     * Get cart_session_id
     *
     * @return string
     */
    public function getCartSessionId() {
        return $this->cart_session_id;
    }

    /**
     * Set cart_room
     *
     * @param \MyCp\mycpBundle\Entity\room $value
     * @return cart
     */
    public function setCartRoom(\MyCp\mycpBundle\Entity\room $value) {
        $this->cart_room = $value;
        return $this;
    }

    /**
     * Get cart_room
     *
     * @return \MyCp\mycpBundle\Entity\room
     */
    public function getCartRoom() {
        return $this->cart_room;
    }

    /**
     * Set cart_user
     *
     * @param \MyCp\mycpBundle\Entity\user $value
     * @return cart
     */
    public function setCartUser(\MyCp\mycpBundle\Entity\user $value = null) {
        if ($value != null)
            $this->cart_user = $value;

        return $this;
    }

    /**
     * Get cart_user
     *
     * @return \MyCp\mycpBundle\Entity\user
     */
    public function getCartUser() {
        return $this->cart_user;
    }

    /**
     * Set cart_date_from
     *
     * @param datetime $value
     * @return cart
     */
    public function setCartDateFrom($value) {
        $this->cart_date_from = $value;
        return $this;
    }

    /**
     * Get cart_date_from
     *
     * @return datetime
     */
    public function getCartDateFrom() {
        return $this->cart_date_from;
    }

    /**
     * Set cart_date_to
     *
     * @param datetime $value
     * @return cart
     */
    public function setCartDateTo($value) {
        $this->cart_date_to = $value;
        return $this;
    }

    /**
     * Get cart_date_to
     *
     * @return datetime
     */
    public function getCartDateTo() {
        return $this->cart_date_to;
    }

    /**
     * Set cart_created_date
     *
     * @param DateTime $value
     * @return cart
     */
    public function setCartCreatedDate($value) {
        $this->cart_created_date = $value;
        return $this;
    }

    /**
     * Get cart_created_date
     *
     * @return DateTime
     */
    public function getCartCreatedDate() {
        return $this->cart_created_date;
    }

    public function getTripleRoomCharged() {
        return ($this->cart_room->isTriple()) &&
                ($this->cart_count_adults + $this->cart_count_children >= 3);
    }

    public function getClone() {
        $clone = new cart();
        $clone->setCartCountAdults($this->cart_count_adults);
        $clone->setCartCountChildren($this->cart_count_children);
        $clone->setCartCreatedDate(new \DateTime());
        $clone->setCartDateFrom($this->cart_date_from);
        $clone->setCartDateTo($this->cart_date_to);
        $clone->setCartRoom($this->cart_room);
        $clone->setCartSessionId($this->cart_session_id);
        $clone->setCartUser($this->cart_user);

        return $clone;
    }

    public function createReservation($generalReservation, $calculatedPrice = 0, $calculateTotalPrice = false) {
        $ownership_reservation = new ownershipReservation();
        $ownership_reservation->setOwnResCountAdults($this->cart_count_adults);
        $ownership_reservation->setOwnResCountChildrens($this->cart_count_children);
        $ownership_reservation->setOwnResNightPrice(0);
        $ownership_reservation->setOwnResStatus(ownershipReservation::STATUS_PENDING);
        $ownership_reservation->setOwnResReservationFromDate($this->cart_date_from);
        $ownership_reservation->setOwnResReservationToDate($this->cart_date_to);
        $ownership_reservation->setOwnResSelectedRoomId($this->cart_room);
        $ownership_reservation->setOwnResRoomPriceDown($this->cart_room->getRoomPriceDownTo());
        $ownership_reservation->setOwnResRoomPriceUp($this->cart_room->getRoomPriceUpTo());
        $ownership_reservation->setOwnResRoomPriceSpecial($this->cart_room->getRoomPriceSpecial());
        $ownership_reservation->setOwnResGenResId($generalReservation);
        $ownership_reservation->setOwnResRoomType($this->cart_room->getRoomType());

        if ($calculateTotalPrice)
            $ownership_reservation->setOwnResTotalInSite(0); //TODO: Calcular segun los cambios de estaciones
        else
            $ownership_reservation->setOwnResTotalInSite($calculatedPrice);

        return $ownership_reservation;
    }

    public function calculatePrice($entity_manager, $service_time, $triple_charge, $service_feed)
    {
        $total_price = 0;

        $destination_id = ($this->cart_room->getRoomOwnership()->getOwnDestination() != null) ? $this->cart_room->getRoomOwnership()->getOwnDestination()->getDesId(): null;
        $seasons = $entity_manager->getRepository("mycpBundle:season")->getSeasons($this->cart_date_from, $this->cart_date_to, $destination_id);
        $array_dates = $service_time->datesBetween($this->cart_date_from->getTimestamp(), $this->cart_date_to->getTimestamp());

        $triple_feed = ($this->getTripleRoomCharged()) ? $triple_charge : 0;

        for ($i = 0; $i < count($array_dates) - 1; $i++) {
                $seasonType = $service_time->seasonTypeByDate($seasons, $array_dates[$i]);
                $total_price += $this->cart_room->getPriceBySeasonType($seasonType) + $triple_feed;
        }
        $prices = array(
            'totalPrice' => $total_price,
            'initialPayment' => ($total_price * $this->cart_room->getRoomOwnership()->getOwnCommissionPercent()/100) + $service_feed
        );
        return $prices;
    }

}