<?php

namespace MyCp\mycpBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\OneToMany;
use MyCp\mycpBundle\Helpers\SyncStatuses;

/**
 * room
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="MyCp\mycpBundle\Entity\roomRepository")
 */
class room {

    /**
     * @var integer
     *
     * @ORM\Column(name="room_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $room_id;

    /**
     * @var string
     *
     * @ORM\Column(name="room_num", type="integer")
     */
    private $room_num;

    /**
     * @var string
     *
     * @ORM\Column(name="room_type", type="string", length=255)
     */
    private $room_type;

    /**
     * @var integer
     *
     * @ORM\Column(name="room_beds", type="integer")
     */
    private $room_beds;

    /**
     * @var string
     *
     * @ORM\Column(name="room_price_up_from", type="string", length=255, nullable=true)
     */
    private $room_price_up_from;

    /**
     * @var string
     *
     * @ORM\Column(name="room_price_up_to", type="string", length=255)
     */
    private $room_price_up_to;

    /**
     * @var string
     *
     * @ORM\Column(name="room_price_down_from", type="string", length=255, nullable=true)
     */
    private $room_price_down_from;

    /**
     * @var string
     *
     * @ORM\Column(name="room_price_down_to", type="string", length=255)
     */
    private $room_price_down_to;

    /**
     * @var string
     *
     * @ORM\Column(name="room_price_special", type="string", length=255, nullable=true)
     */
    private $room_price_special;

    /**
     * @var string
     *
     * @ORM\Column(name="room_climate", type="string", length=255)
     */
    private $room_climate;

    /**
     * @var string
     *
     * @ORM\Column(name="room_audiovisual", type="string", length=255)
     */
    private $room_audiovisual;

    /**
     * @var boolean
     *
     * @ORM\Column(name="room_smoker", type="boolean")
     */
    private $room_smoker;

    /**
     * @var boolean
     *
     * @ORM\Column(name="room_active", type="boolean")
     */
    private $room_active;

    /**
     * @var boolean
     *
     * @ORM\Column(name="room_safe", type="boolean")
     */
    private $room_safe;

    /**
     * @var boolean
     *
     * @ORM\Column(name="room_baby", type="boolean")
     */
    private $room_baby;

    /**
     * @var string
     *
     * @ORM\Column(name="room_bathroom", type="string", length=255)
     */
    private $room_bathroom;

    /**
     * @var boolean
     *
     * @ORM\Column(name="room_stereo", type="boolean")
     */
    private $room_stereo;

    /**
     * @var integer
     *
     * @ORM\Column(name="room_windows", type="integer")
     */
    private $room_windows;

    /**
     * @var integer
     *
     * @ORM\Column(name="room_balcony", type="integer")
     */
    private $room_balcony;

    /**
     * @var boolean
     *
     * @ORM\Column(name="room_terrace", type="boolean")
     */
    private $room_terrace;

    /**
     * @var boolean
     *
     * @ORM\Column(name="room_yard", type="boolean")
     */
    private $room_yard;

    /**
     * @var string
     * @ORM\Column(name="room_sync_st", type="integer")
     */
    private $room_sync_st;

    /**
     * @ORM\ManyToOne(targetEntity="ownership",inversedBy="own_rooms")
     * @ORM\JoinColumn(name="room_ownership",referencedColumnName="own_id")
     */
    private $room_ownership;

    /**
     * @OneToMany(targetEntity="unavailabilityDetails", mappedBy="room")
     */
    private $own_unavailability_details;

    public function __construct() {
        $this->own_unavailability_details = new ArrayCollection();
        $this->room_sync_st = SyncStatuses::ADDED;
        $this->room_active = true;
    }

    /**
     * Get room_id
     *
     * @return integer
     */
    public function getRoomId() {
        return $this->room_id;
    }

    public function getRoomNum() {
        return $this->room_num;
    }

    public function setRoomNum($room_num) {
        $this->room_num = $room_num;
    }

    /**
     * Set room_type
     *
     * @param string $roomType
     * @return room
     */
    public function setRoomType($roomType) {
        $this->room_type = $roomType;

        return $this;
    }

    /**
     * Get room_type
     *
     * @return string
     */
    public function getRoomType() {
        return $this->room_type;
    }

    /**
     * Set room_beds
     *
     * @param integer $roomBeds
     * @return room
     */
    public function setRoomBeds($roomBeds) {
        $this->room_beds = $roomBeds;

        return $this;
    }

    /**
     * Get room_beds
     *
     * @return integer
     */
    public function getRoomBeds() {
        return $this->room_beds;
    }

    /**
     * Set room_price_up_from
     *
     * @param string $roomPriceUpFrom
     * @return room
     */
    public function setRoomPriceUpFrom($roomPriceUpFrom) {
        $this->room_price_up_from = $roomPriceUpFrom;

        return $this;
    }

    /**
     * Get room_price_up_from
     *
     * @return string
     */
    public function getRoomPriceUpFrom() {
        return $this->room_price_up_from;
    }

    /**
     * Set room_price_up_to
     *
     * @param string $roomPriceUpTo
     * @return room
     */
    public function setRoomPriceUpTo($roomPriceUpTo) {
        $this->room_price_up_to = $roomPriceUpTo;

        return $this;
    }

    /**
     * Get room_price_up_to
     *
     * @return string
     */
    public function getRoomPriceUpTo() {
        return $this->room_price_up_to;
    }

    /**
     * Set room_price_down_from
     *
     * @param string $roomPriceDownFrom
     * @return room
     */
    public function setRoomPriceDownFrom($roomPriceDownFrom) {
        $this->room_price_down_from = $roomPriceDownFrom;

        return $this;
    }

    /**
     * Get room_price_down_from
     *
     * @return string
     */
    public function getRoomPriceDownFrom() {
        return $this->room_price_down_from;
    }

    /**
     * Set room_price_down_to
     *
     * @param string $roomPriceDownTo
     * @return room
     */
    public function setRoomPriceDownTo($roomPriceDownTo) {
        $this->room_price_down_to = $roomPriceDownTo;

        return $this;
    }

    /**
     * Get room_price_down_to
     *
     * @return string
     */
    public function getRoomPriceDownTo() {
        return $this->room_price_down_to;
    }

    /**
     * Set room_price_special
     *
     * @param string $roomPriceSpecial
     * @return room
     */
    public function setRoomPriceSpecial($roomPriceSpecial) {
        $this->room_price_special = $roomPriceSpecial;

        return $this;
    }

    /**
     * Get room_price_special
     *
     * @return string
     */
    public function getRoomPriceSpecial() {
        return $this->room_price_special;
    }

    /**
     * Set room_climate
     *
     * @param string $roomClimate
     * @return room
     */
    public function setRoomClimate($roomClimate) {
        $this->room_climate = $roomClimate;

        return $this;
    }

    /**
     * Get room_climate
     *
     * @return string
     */
    public function getRoomClimate() {
        return $this->room_climate;
    }

    /**
     * Set room_audiovisual
     *
     * @param string $roomAudiovisual
     * @return room
     */
    public function setRoomAudiovisual($roomAudiovisual) {
        $this->room_audiovisual = $roomAudiovisual;

        return $this;
    }

    /**
     * Get room_audiovisual
     *
     * @return string
     */
    public function getRoomAudiovisual() {
        return $this->room_audiovisual;
    }

    /**
     * Set room_smoker
     *
     * @param boolean $roomSmoker
     * @return room
     */
    public function setRoomSmoker($roomSmoker) {
        $this->room_smoker = $roomSmoker;

        return $this;
    }

    /**
     * Get room_smoker
     *
     * @return boolean
     */
    public function getRoomSmoker() {
        return $this->room_smoker;
    }

    /**
     * Set room_active
     *
     * @param boolean $roomActive
     * @return room
     */
    public function setRoomActive($roomActive) {
        $this->room_active = $roomActive;

        return $this;
    }

    /**
     * Get room_active
     *
     * @return boolean
     */
    public function getRoomActive() {
        return $this->room_active;
    }

    /**
     * Set room_safe
     *
     * @param boolean $roomSafe
     * @return room
     */
    public function setRoomSafe($roomSafe) {
        $this->room_safe = $roomSafe;

        return $this;
    }

    /**
     * Get room_safe
     *
     * @return boolean
     */
    public function getRoomSafe() {
        return $this->room_safe;
    }

    /**
     * Set room_baby
     *
     * @param boolean $roomBaby
     * @return room
     */
    public function setRoomBaby($roomBaby) {
        $this->room_baby = $roomBaby;

        return $this;
    }

    /**
     * Get room_baby
     *
     * @return boolean
     */
    public function getRoomBaby() {
        return $this->room_baby;
    }

    /**
     * Set room_bathroom
     *
     * @param string $roomBathroom
     * @return room
     */
    public function setRoomBathroom($roomBathroom) {
        $this->room_bathroom = $roomBathroom;

        return $this;
    }

    /**
     * Get room_bathroom
     *
     * @return string
     */
    public function getRoomBathroom() {
        return $this->room_bathroom;
    }

    /**
     * Set room_stereo
     *
     * @param boolean $roomStereo
     * @return room
     */
    public function setRoomStereo($roomStereo) {
        $this->room_stereo = $roomStereo;

        return $this;
    }

    /**
     * Get room_stereo
     *
     * @return boolean
     */
    public function getRoomStereo() {
        return $this->room_stereo;
    }

    /**
     * Set room_windows
     *
     * @param integer $roomWindows
     * @return room
     */
    public function setRoomWindows($roomWindows) {
        $this->room_windows = $roomWindows;

        return $this;
    }

    /**
     * Get room_windows
     *
     * @return integer
     */
    public function getRoomWindows() {
        return $this->room_windows;
    }

    /**
     * Set room_balcony
     *
     * @param integer $roomBalcony
     * @return room
     */
    public function setRoomBalcony($roomBalcony) {
        $this->room_balcony = $roomBalcony;

        return $this;
    }

    /**
     * Get room_balcony
     *
     * @return integer
     */
    public function getRoomBalcony() {
        return $this->room_balcony;
    }

    /**
     * Set room_terrace
     *
     * @param boolean $roomTerrace
     * @return room
     */
    public function setRoomTerrace($roomTerrace) {
        $this->room_terrace = $roomTerrace;

        return $this;
    }

    /**
     * Get room_terrace
     *
     * @return boolean
     */
    public function getRoomTerrace() {
        return $this->room_terrace;
    }

    /**
     * Set room_yard
     *
     * @param boolean $roomYard
     * @return room
     */
    public function setRoomYard($roomYard) {
        $this->room_yard = $roomYard;

        return $this;
    }

    /**
     * Get room_yard
     *
     * @return boolean
     */
    public function getRoomYard() {
        return $this->room_yard;
    }

    /**
     * Set room_ownership
     *
     * @param \MyCp\mycpBundle\Entity\ownership $roomOwnership
     * @return room
     */
    public function setRoomOwnership(\MyCp\mycpBundle\Entity\ownership $roomOwnership = null) {
        $this->room_ownership = $roomOwnership;

        return $this;
    }

    /**
     * Get room_ownership
     *
     * @return \MyCp\mycpBundle\Entity\ownership
     */
    public function getRoomOwnership() {
        return $this->room_ownership;
    }

    public function setRoomId($id) {
        $this->room_id = $id;
    }

    public function getSyncSt() {
        return $this->room_sync_st;
    }

    public function setSyncSt($room_sync_st) {
        $this->room_sync_st = $room_sync_st;
    }

    public function getOwn_unavailability_details() {
        return $this->own_unavailability_details;
    }

    public function setOwn_unavailability_details($own_unavailability_details) {
        $this->own_unavailability_details = $own_unavailability_details;
    }

    // <editor-fold defaultstate="collapsed" desc="Logic Methods">
    public function getUd($dateFrom, $dateTo) {
        foreach ($this->own_unavailability_details as $ud) {
            if ($ud->getUdFromDate()->format('Y-m-d') === $dateFrom && $ud->getUdToDate()->format('Y-m-d') === $dateTo) {
                return $ud;
            }
        }
        return null;
    }

// </editor-fold>

    public function __toString() {
        return (string) $this->room_id;
    }

    public function getMaximumNumberGuests() {
        return room::getTotalGuests($this->room_type);
    }

    public static function getTotalGuests($roomType)
    {
         switch ($roomType) {
            case "Habitación individual": return 1;
            case "Habitación doble":
            case "Habitación doble (Dos camas)": return 2;
            case "Habitación Triple": return 3;
        }
        return 0;
    }

    public function getPriceBySeasonType($seasonType)
    {
        switch($seasonType)
        {
            case \MyCp\mycpBundle\Entity\season::SEASON_TYPE_HIGH: return $this->room_price_up_to;
            case \MyCp\mycpBundle\Entity\season::SEASON_TYPE_SPECIAL: return ($this->room_price_special != null && $this->room_price_special > 0) ? $this->room_price_special: $this->room_price_up_to;
            default: return $this->room_price_down_to;
        }
    }

    /**
     * Set room_sync_st
     *
     * @param integer $roomSyncSt
     * @return room
     */
    public function setRoomSyncSt($roomSyncSt) {
        $this->room_sync_st = $roomSyncSt;

        return $this;
    }

    /**
     * Get room_sync_st
     *
     * @return integer
     */
    public function getRoomSyncSt() {
        return $this->room_sync_st;
    }

    /**
     * Add own_unavailability_details
     *
     * @param \MyCp\mycpBundle\Entity\unavailabilityDetails $ownUnavailabilityDetails
     * @return room
     */
    public function addOwnUnavailabilityDetail(\MyCp\mycpBundle\Entity\unavailabilityDetails $ownUnavailabilityDetails) {
        $this->own_unavailability_details[] = $ownUnavailabilityDetails;

        return $this;
    }

    /**
     * Remove own_unavailability_details
     *
     * @param \MyCp\mycpBundle\Entity\unavailabilityDetails $ownUnavailabilityDetails
     */
    public function removeOwnUnavailabilityDetail(\MyCp\mycpBundle\Entity\unavailabilityDetails $ownUnavailabilityDetails) {
        $this->own_unavailability_details->removeElement($ownUnavailabilityDetails);
    }

    /**
     * Get own_unavailability_details
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getOwnUnavailabilityDetails() {
        return $this->own_unavailability_details;
    }

    public function isTriple()
    {
        return $this->room_type == "Habitación Triple";
    }


    public function getRoomCode()
    {
        return $this->getRoomOwnership()->getOwnMcpCode()."-".$this->room_num;
    }

    public function getICalUrl($controller)
    {
        return self::getCalendarUrl($this->getRoomCode(), $controller->getRequest());
    }

    public function getICalUrlFromRequest($request)
    {
        return self::getCalendarUrl($this->getRoomCode(), $request);
    }

    public static function getCalendarUrl($roomCode, $request)
    {
        $url = $request->getUriForPath('/web/calendars/' . $roomCode . ".ics");

        if (strpos($url, "/web/app_dev.php") !== false)
            $url = str_replace("/web/app_dev.php", "", $url);
        else if (strpos($url, "/app_dev.php") !== false)
            $url = str_replace("/app_dev.php", "", $url);

        return $url;
    }

}
