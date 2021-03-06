<?php

namespace MyCp\mycpBundle\Entity;

use DateTime as DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use MyCp\mycpBundle\Helpers\SyncStatuses;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * ownership
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="MyCp\mycpBundle\Entity\ownershipRepository")
 * @ORM\EntityListeners({"MyCp\mycpBundle\Listener\OwnershipListener"})
 */
class ownership
{
    /**
     * All allowed statuses
     */
    const ACCOMMODATION_CATEGORY_ECONOMIC = "Económica";
    const ACCOMMODATION_CATEGORY_MIDDLE_RANGE = "Rango medio";
    const ACCOMMODATION_CATEGORY_PREMIUM = "Premium";

    /**
     * All allowed rental type
     */
    const ACCOMMODATION_RENTAL_TYPE_FULL = "Propiedad completa";
    const ACCOMMODATION_RENTAL_TYPE_PER_ROOMS = "Por habitaciones";


    /**
     * All allowed modality reservation
     */
    const MODALITY_IMMEDIATE_BOOKING = "Reserva Inmediata";
    const MODALITY_QUICKLY_BOOKING = "Reserva Rapida";
    const MODALITY_DEFAULT_BOOKING = "Reserva por Solicitudes";

    /**
     * Contains all possible statuses
     *
     * @var array
     */
    private $categories = array(
        self::ACCOMMODATION_CATEGORY_ECONOMIC,
        self::ACCOMMODATION_CATEGORY_MIDDLE_RANGE,
        self::ACCOMMODATION_CATEGORY_PREMIUM
    );
    /**
     * Contains all possible statuses
     *
     * @var array
     */
    private $rentalTypes = array(
        self::ACCOMMODATION_RENTAL_TYPE_FULL,
        self::ACCOMMODATION_RENTAL_TYPE_PER_ROOMS

    );

    /**
     * @var integer
     *
     * @ORM\Column(name="own_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $own_id;

    /**
     * @var string
     *
     * @ORM\Column(name="own_name", type="string", length=255, nullable=true)
     */
    private $own_name;

    /**
     * @var string
     *
     * @ORM\Column(name="own_old_name", type="string", length=255, nullable=true)
     */
    private $own_old_name;

    /**
     * @var string
     *
     * @ORM\Column(name="own_licence_number", type="string", length=255, nullable=true)
     */
    private $own_licence_number;

    /**
     * @var string
     *
     * @ORM\Column(name="own_mcp_code", type="string", length=255)
     */
    private $own_mcp_code;

    /**
     * @var string
     *
     * @ORM\Column(name="own_address_street", type="string", length=255, nullable=true)
     */
    private $own_address_street;

    /**
     * @var string
     *
     * @ORM\Column(name="own_address_number", type="string", length=255, nullable=true)
     */
    private $own_address_number;

    /**
     * @var string
     *
     * @ORM\Column(name="own_address_between_street_1", type="string", length=255, nullable=true)
     */
    private $own_address_between_street_1;

    /**
     * @var string
     *
     * @ORM\Column(name="own_address_between_street_2", type="string", length=255, nullable=true)
     */
    private $own_address_between_street_2;

    /**
     * @ORM\ManyToOne(targetEntity="province")
     * @ORM\JoinColumn(name="own_address_province", referencedColumnName="prov_id")
     */
    private $own_address_province;

    /**
     * @ORM\ManyToOne(targetEntity="municipality")
     * @ORM\JoinColumn(name="own_address_municipality", referencedColumnName="mun_id")
     */
    private $own_address_municipality;

    /**
     * @ORM\ManyToOne(targetEntity="destination")
     * @ORM\JoinColumn(name="own_destination", referencedColumnName="des_id", nullable=true)
     */
    private $own_destination;

    /**
     * @var string
     *
     * @ORM\Column(name="own_mobile_number", type="string", length=255, nullable=true)
     * @Assert\Regex("/^5[0-9]{7}$/")
     */
    private $own_mobile_number;

    /**
     * @var string
     *
     * @ORM\Column(name="own_homeowner_1", type="string", length=255, nullable=true)
     */
    private $own_homeowner_1;

    /**
     * @var string
     *
     * @ORM\Column(name="own_homeowner_2", type="string", length=255, nullable=true)
     */
    private $own_homeowner_2;

    /**
     * @var string
     *
     * @ORM\Column(name="own_phone_code", type="string", length=255, nullable=true)
     */
    private $own_phone_code;

    /**
     * @var string
     *
     * @ORM\Column(name="own_phone_number", type="string", length=255, nullable=true)
     */
    private $own_phone_number;

    /**
     * @var string
     *
     * @ORM\Column(name="own_email_1", type="string", length=255, nullable=true)
     */
    private $own_email_1;

    /**
     * @var string
     *
     * @ORM\Column(name="own_email_2", type="string", length=255, nullable=true)
     */
    private $own_email_2;

    /**
     * @var string
     *
     * @ORM\Column(name="own_category", type="string", length=255, nullable=true)
     */
    private $own_category;

    /**
     * @var string
     *
     * @ORM\Column(name="own_rental_type", type="string", length=255, nullable=true)
     */
    private $own_rental_type;

    /**
     * @var string
     *
     * @ORM\Column(name="own_type", type="string", length=255, nullable=true)
     */
    private $own_type;

    /**
     * @ORM\OneToMany(targetEntity="room",mappedBy="room_ownership")
     */
    private $own_rooms;

    /**
     * @ORM\OneToMany(targetEntity="notification",mappedBy="ownership")
     */
    private $notifications;

    /**
     * @ORM\OneToOne(targetEntity="accommodationBookingModality",mappedBy="accommodation")
     */
    private $bookingModality;

    /**
     * @var boolean
     *
     * @ORM\Column(name="own_facilities_breakfast", type="boolean")
     */
    private $own_facilities_breakfast;

    /**
     * @var string
     *
     * @ORM\Column(name="own_facilities_breakfast_price", type="string", length=255, nullable=true)
     */
    private $own_facilities_breakfast_price;


    /**
     * @var boolean
     *
     * @ORM\Column(name="own_facilities_breakfast_include", type="boolean" )
     */
    private $own_facilities_breakfast_include;

    /**
     * @var boolean
     *
     * @ORM\Column(name="own_facilities_dinner", type="boolean")
     */
    private $own_facilities_dinner;

    /**
     * @var string
     *
     * @ORM\Column(name="own_facilities_dinner_price_from", type="string", length=255, nullable=true)
     */
    private $own_facilities_dinner_price_from;

    /**
     * @var string
     *
     * @ORM\Column(name="own_facilities_dinner_price_to", type="string", length=255, nullable=true)
     */
    private $own_facilities_dinner_price_to;

    /**
     * @var boolean
     *
     * @ORM\Column(name="own_facilities_parking", type="boolean")
     */
    private $own_facilities_parking;

    /**
     * @var string
     *
     * @ORM\Column(name="own_facilities_parking_price", type="string", length=255, nullable=true)
     */
    private $own_facilities_parking_price;

    /**
     * @var string
     *
     * @ORM\Column(name="own_facilities_notes", type="text", nullable=true)
     */
    private $own_facilities_notes;

    /**
     * @var string
     *
     * @ORM\Column(name="own_langs", type="string", length=4, nullable=true)
     */
    private $own_langs;

    /**
     * @var boolean
     *
     * @ORM\Column(name="own_water_jacuzee", type="boolean",nullable=true)
     */
    private $own_water_jacuzee;

    /**
     * @var boolean
     *
     * @ORM\Column(name="own_water_sauna", type="boolean",nullable=true)
     */
    private $own_water_sauna;

    /**
     * @var boolean
     *
     * @ORM\Column(name="own_water_piscina", type="boolean",nullable=true)
     */
    private $own_water_piscina;

    /**
     * @var boolean
     *
     * @ORM\Column(name="own_description_bicycle_parking", type="boolean")
     */
    private $own_description_bicycle_parking;

    /**
     * @var boolean
     *
     * @ORM\Column(name="own_description_pets", type="boolean")
     */
    private $own_description_pets;

    /**
     * @var boolean
     *
     * @ORM\Column(name="own_description_laundry", type="boolean")
     */
    private $own_description_laundry;

    /**
     * @var boolean
     *
     * @ORM\Column(name="own_description_internet", type="boolean")
     */
    private $own_description_internet;

    /**
     * @var string
     *
     * @ORM\Column(name="own_geolocate_x", type="string", length=255, nullable=true)
     */
    private $own_geolocate_x;

    /**
     * @var string
     *
     * @ORM\Column(name="own_geolocate_y", type="string", length=255, nullable=true)
     */
    private $own_geolocate_y;

    /**
     * @var boolean
     *
     * @ORM\Column(name="own_top_20", type="boolean")
     */
    private $own_top_20;

    /**
     * @ORM\ManyToOne(targetEntity="ownershipStatus",cascade={"persist"})
     * @ORM\JoinColumn(name="own_status", referencedColumnName="status_id")
     */
    private $own_status;

    /**
     * @var integer
     *
     * @ORM\Column(name="own_commission_percent", type="integer", nullable=true)
     */
    private $own_commission_percent;

    /**
     * @var integer
     *
     * @ORM\Column(name="own_ranking", type="float", nullable=true)
     */
    private $own_ranking;

    /**
     * @var string
     *
     * @ORM\Column(name="own_comment", type="string", length=255, nullable=true)
     */
    private $own_comment;

    /**
     * @var boolean
     *
     * @ORM\Column(name="own_not_recommendable", type="boolean")
     */
    private $own_not_recommendable;

    /**
     * @var boolean
     *
     * @ORM\Column(name="own_sended_to_team", type="boolean", nullable=true)
     */
    private $own_sended_to_team;

    /**
     * @var string
     *
     * @ORM\Column(name="own_saler", type="string", length=255, nullable=true)
     */
    private $own_saler;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="own_visit_date", type="datetime", nullable=true)
     */
    private $own_visit_date;

    /**
     * @var datetime
     *
     * @ORM\Column(name="own_creation_date", type="datetime", nullable=true)
     */
    private $own_creation_date;

    /**
     * @var datetime
     *
     * @ORM\Column(name="own_availability_update", type="datetime", nullable=true)
     */
    private $own_availability_update;

    /**
     * @var datetime
     *
     * @ORM\Column(name="own_publish_date", type="datetime", nullable=true)
     */
    private $own_publish_date;

    /**
     * @var datetime
     *
     * @ORM\Column(name="own_last_update", type="datetime", nullable=true)
     */
    private $own_last_update;

    /**
     * @var float
     *
     * @ORM\Column(name="own_rating", type="decimal", nullable=true)
     */
    private $own_rating;

    /**
     * @var integer
     *
     * @ORM\Column(name="own_maximun_number_guests", type="integer", nullable=true)
     */
    private $own_maximun_number_guests;

    /**
     * @var integer
     *
     * @ORM\Column(name="own_minimum_price", type="decimal", precision=10, scale=2, nullable=true)
     */
    private $own_minimum_price;

    /**
     * @var integer
     *
     * @ORM\Column(name="own_maximum_price", type="decimal", precision=10, scale=2, nullable=true)
     */
    private $own_maximum_price;

    /**
     * @var integer
     *
     * @ORM\Column(name="own_comments_total", type="integer", nullable=true)
     */
    private $own_comments_total;

    /**
     * @var integer
     *
     * @ORM\Column(name="own_rooms_total", type="integer", nullable=true)
     */
    private $own_rooms_total;

    /**
     * @var string
     * @ORM\Column(name="own_sync_st", type="integer", nullable=true)
     */
    private $own_sync_st;

    /**
     * @var boolean
     *
     * @ORM\Column(name="own_selection", type="boolean")
     */
    private $own_selection;

    /**
     * @var boolean
     *
     * @ORM\Column(name="own_inmediate_booking", type="boolean")
     */
    private $own_inmediate_booking;

    /**
     * @var boolean
     *
     * @ORM\Column(name="own_inmediate_booking_2", type="boolean")
     */
    private $own_inmediate_booking_2;

    /**
     * @ORM\ManyToOne(targetEntity="photo",inversedBy="")
     * @ORM\JoinColumn(name="own_owner_photo",referencedColumnName="pho_id")
     */
    private $own_owner_photo;

    /**
     * @ORM\OneToMany(targetEntity="ownershipDescriptionLang",mappedBy="odl_ownership")
     */
    private $own_description_langs;

    /**
     * @ORM\OneToMany(targetEntity="generalReservation",mappedBy="gen_res_own_id")
     */
    private $own_general_reservations;

    /**
     * @ORM\OneToMany(targetEntity="ownershipKeywordLang",mappedBy="okl_ownership")
     */
    private $ownershipKeywordOwnership;

    /**
     * @var string
     *
     * @ORM\Column(name="own_automatic_mcp_code", type="integer", nullable=true)
     */
    private $own_automatic_mcp_code;

    /**
     * @var string
     *
     * @ORM\Column(name="own_mcp_code_generated", type="string", nullable=true)
     */
    private $own_mcp_code_generated;

    /**
     * @var boolean
     *
     * @ORM\Column(name="own_cubacoupon", type="boolean")
     */
    private $own_cubacoupon;

    /**
     * @ORM\OneToMany(targetEntity="accommodationAward", mappedBy="accommodation")
     */
    private $awards;

    /**
     * @ORM\OneToMany(targetEntity="ownershipPhoto", mappedBy="own_pho_own")
     */
    private $photos;

    /**
     * @ORM\OneToMany(targetEntity="comment", mappedBy="com_ownership")
     */
    private $comments;

    /**
     * @var boolean
     *
     * @ORM\Column(name="own_sms_notifications", type="boolean")
     */
    private $own_sms_notifications;

    /**
     * @ORM\OneToMany(targetEntity="ownerAccommodation",mappedBy="accommodation")
     */
    private $owners;

    /**
     * @var boolean
     *
     * @ORM\Column(name="waiting_for_revision", type="boolean")
     */
    private $waiting_for_revision;

    /**
     * @ORM\OneToMany(targetEntity="ownershipStatistics",mappedBy="accommodation")
     */
    private $ownershipLogs;

    /**
     * @ORM\OneToMany(targetEntity="ownershipPayment",mappedBy="accommodation")
     */
    private $payments;

    /**
     * @ORM\OneToOne(targetEntity="ownershipData", mappedBy="accommodation")
     */
    private $data;

    /**
     * @var int
     *
     * @ORM\Column(name="insertedInCasaModule", type="boolean", nullable=true)
     */
    private $insertedInCasaModule;

    /**
     * @ORM\OneToMany(targetEntity="ownershipRankingExtra", mappedBy="accommodation")
     */
    private $rankingExtras;

    /**
     * @ORM\OneToMany(targetEntity="penalty", mappedBy="accommodation")
     */
    private $penalties;

    /**
     * @ORM\OneToMany(targetEntity="failure", mappedBy="accommodation")
     */
    private $failures;

    /**
     * @ORM\OneToMany(targetEntity="accommodationCalendarFrequency", mappedBy="accommodation")
     */
    private $calendarUpdateFrequency;

    /**
     * @var integer
     * @ORM\Column(name="count_visits", type="integer")
     */
    private $count_visits;

    /**
     * @var boolean
     *
     * @ORM\Column(name="confidence", type="boolean")
     */
    private $confidence;

    /**
     * @ORM\OneToMany(targetEntity="accommodationModalityFrequency", mappedBy="accommodation")
     */
    private $modalityUpdateFrequency;

    /**
     * @var int
     *
     * @ORM\Column(name="modifying", type="boolean", nullable=true)
     */
    private $modifying;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="own_hot_date", type="datetime", nullable=true)
     */
    private $own_hot_date;

    /**
     * @ORM\OneToMany(targetEntity="transferMethodPayment", mappedBy="accommodation")
     */
    private $transferMethodsPayment;

    /**
     * @ORM\OneToMany(targetEntity="effectiveMethodPayment", mappedBy="accommodation")
     */
    private $effectiveMethodsPayment;

    /**
     * @var int
     *
     * @ORM\Column(name="good_picture", type="boolean", nullable=true)
     */
    private $goodPicture;

    /**
     * @var int
     *
     * @ORM\Column(name="with_ical", type="boolean", nullable=true)
     */
    private $withIcal;

    /**
     * @var boolean
     *
     * @ORM\Column(name="own_agency_work", type="boolean", nullable=true)
     */
    private $own_agencyWork;


    /**
     * @var string
     *
     * @ORM\Column(name="own_modality_reservation", type="string", nullable=true)
     */
    private $own_modalityReservation;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->own_rooms = new ArrayCollection();
        $this->own_description_langs = new ArrayCollection();
        $this->own_general_reservations = new ArrayCollection();
        $this->ownershipKeywordOwnership = new ArrayCollection();
        $this->own_sync_st = SyncStatuses::ADDED;
        $this->own_sended_to_team = false;
        $this->waiting_for_revision = false;
        $this->own_cubacoupon = false;
        $this->awards = new ArrayCollection();
        $this->photos = new ArrayCollection();
        $this->comments = new ArrayCollection();
        $this->ownershipLogs = new ArrayCollection();
        $this->own_creation_date = new \DateTime();
        $this->own_availability_update = new \DateTime();
        $this->own_sms_notifications = true;
        $this->own_inmediate_booking = false;
        $this->own_inmediate_booking_2 = false;
        $this->owners = new ArrayCollection();
        $this->payments = new ArrayCollection();
        $this->insertedInCasaModule = false;
        $this->penalties = new ArrayCollection();
        $this->failures = new ArrayCollection();
        $this->calendarUpdateFrequency = new ArrayCollection();
        $this->rankingExtras = new ArrayCollection();
        $this->confidence = false;
        $this->calendarModalityFrequency = new ArrayCollection();
        $this->notifications = new ArrayCollection();
        $this->transferMethodsPayment = new ArrayCollection();
        $this->effectiveMethodsPayment = new ArrayCollection();
        $this->count_visits = 0;
        $this->withIcal = false;
        $this->own_facilities_breakfast_include = false;
    }

    /**
     * Get own_id
     *
     * @return integer
     */
    public function getOwnId()
    {
        return $this->own_id;
    }

    public function getOwnDescriptionLangs()
    {
        return $this->own_description_langs;
    }

    public function getOwnGeneralReservations()
    {
        return $this->own_general_reservations;
    }

    /**
     * Get own_last_update
     *
     * @return datetime
     */
    public function getOwnLastUpdate()
    {
        return $this->own_last_update;
    }

    /**
     * Set own_last_update
     *
     * @param datetime $ownLastUpdate
     * @return ownership
     */
    public function setOwnLastUpdate($ownLastUpdate)
    {
        $this->own_last_update = $ownLastUpdate;

        return $this;
    }

    /**
     * @return DateTime
     */
    public function getOwnAvailabilityUpdate()
    {
        return $this->own_availability_update;
    }

    /**
     * @param DateTime $own_availability_update
     */
    public function setOwnAvailabilityUpdate($own_availability_update)
    {
        $this->own_availability_update = $own_availability_update;
    }

    /**
     * Get own_destination
     *
     * @return destination
     */
    public function getOwnDestination()
    {
        return $this->own_destination;
    }

    /**
     * Set own_destination
     *
     * @param destination $ownDestination
     * @return ownership
     */
    public function setOwnDestination($ownDestination)
    {
        $this->own_destination = $ownDestination;

        return $this;
    }

    /**
     * Get own_ranking
     *
     * @return integer
     */
    public function getOwnRanking()
    {
        return $this->own_ranking;
    }

    /**
     * Set own_ranking
     *
     * @param integer $ownRanking
     * @return ownership
     */
    public function setOwnRanking($ownRanking)
    {
        $this->own_ranking = $ownRanking;

        return $this;
    }

    /**
     * Get own_creation_date
     *
     * @return datetime
     */
    public function getOwnCreationDate()
    {
        return $this->own_creation_date;
    }

    /**
     * Set own_creation_date
     *
     * @param DateTime $ownCreationDate
     * @return ownership
     */
    public function setOwnCreationDate($ownCreationDate)
    {
        $this->own_creation_date = $ownCreationDate;

        return $this;
    }

    /**
     * Get own_visit_date
     *
     * @return datetime
     */
    public function getOwnVisitDate()
    {
        return $this->own_visit_date;
    }

    /**
     * Set own_visit_date
     *
     * @param datetime $ownVisitDate
     * @return ownership
     */
    public function setOwnVisitDate($ownVisitDate)
    {
        $this->own_visit_date = $ownVisitDate;

        return $this;
    }

    /**
     * Get own_saler
     *
     * @return string
     */
    public function getOwnSaler()
    {
        return $this->own_saler;
    }

    /**
     * Set own_saler
     *
     * @param string $ownSaler
     * @return ownership
     */
    public function setOwnSaler($ownSaler)
    {
        $this->own_saler = $ownSaler;

        return $this;
    }

    /**
     * Get own_not_recommendable
     *
     * @return boolean
     */
    public function getOwnNotRecommendable()
    {
        return $this->own_not_recommendable;
    }

    /**
     * Set own_not_recommendable
     *
     * @param string $ownNotRecommendable
     * @return ownership
     */
    public function setOwnNotRecommendable($ownNotRecommendable)
    {
        $this->own_not_recommendable = $ownNotRecommendable;

        return $this;
    }

    /**
     * Get own_licence_number
     *
     * @return string
     */
    public function getOwnLicenceNumber()
    {
        return $this->own_licence_number;
    }

    /**
     * Set own_licence_number
     *
     * @param string $ownLicenceNumber
     * @return ownership
     */
    public function setOwnLicenceNumber($ownLicenceNumber)
    {
        $this->own_licence_number = $ownLicenceNumber;

        return $this;
    }

    /**
     * Get own_address_street
     *
     * @return string
     */
    public function getOwnAddressStreet()
    {
        return $this->own_address_street;
    }

    /**
     * Set own_address_street
     *
     * @param string $ownAddressStreet
     * @return ownership
     */
    public function setOwnAddressStreet($ownAddressStreet)
    {
        $this->own_address_street = $ownAddressStreet;

        return $this;
    }

    /**
     * Get own_address_number
     *
     * @return string
     */
    public function getOwnAddressNumber()
    {
        return $this->own_address_number;
    }

    /**
     * Set own_address_number
     *
     * @param string $ownAddressNumber
     * @return ownership
     */
    public function setOwnAddressNumber($ownAddressNumber)
    {
        $this->own_address_number = $ownAddressNumber;

        return $this;
    }

    /**
     * Get own_address_between_street_1
     *
     * @return string
     */
    public function getOwnAddressBetweenStreet1()
    {
        return $this->own_address_between_street_1;
    }

    /**
     * Set own_address_between_street_1
     *
     * @param string $ownAddressBetweenStreet1
     * @return ownership
     */
    public function setOwnAddressBetweenStreet1($ownAddressBetweenStreet1)
    {
        $this->own_address_between_street_1 = $ownAddressBetweenStreet1;

        return $this;
    }

    /**
     * Get own_address_between_street_2
     *
     * @return string
     */
    public function getOwnAddressBetweenStreet2()
    {
        return $this->own_address_between_street_2;
    }

    /**
     * Set own_address_between_street_2
     *
     * @param string $ownAddressBetweenStreet2
     * @return ownership
     */
    public function setOwnAddressBetweenStreet2($ownAddressBetweenStreet2)
    {
        $this->own_address_between_street_2 = $ownAddressBetweenStreet2;

        return $this;
    }

    /**
     * Get own_mobile_number
     *
     * @return string
     */
    public function getOwnMobileNumber()
    {
        return $this->own_mobile_number;
    }

    /**
     * Set own_mobile_number
     *
     * @param string $ownMobileNumber
     * @return ownership
     */
    public function setOwnMobileNumber($ownMobileNumber)
    {
        $this->own_mobile_number = $ownMobileNumber;

        return $this;
    }

    /**
     * Get own_homeowner_1
     *
     * @return string
     */
    public function getOwnHomeowner1()
    {
        return $this->own_homeowner_1;
    }

    /**
     * Set own_homeowner_1
     *
     * @param string $ownHomeowner1
     * @return ownership
     */
    public function setOwnHomeowner1($ownHomeowner1)
    {
        $this->own_homeowner_1 = $ownHomeowner1;

        return $this;
    }

    /**
     * Get own_homeowner_2
     *
     * @return string
     */
    public function getOwnHomeowner2()
    {
        return $this->own_homeowner_2;
    }

    /**
     * Set own_homeowner_2
     *
     * @param string $ownHomeowner2
     * @return ownership
     */
    public function setOwnHomeowner2($ownHomeowner2)
    {
        $this->own_homeowner_2 = $ownHomeowner2;

        return $this;
    }

    /**
     * Get own_phone_number
     *
     * @return string
     */
    public function getOwnPhoneNumber()
    {
        return $this->own_phone_number;
    }

    /**
     * Set own_phone_number
     *
     * @param string $ownPhoneNumber
     * @return ownership
     */
    public function setOwnPhoneNumber($ownPhoneNumber)
    {
        $this->own_phone_number = $ownPhoneNumber;

        return $this;
    }

    /**
     * Get own_email_1
     *
     * @return string
     */
    public function getOwnEmail1()
    {
        return $this->own_email_1;
    }

    /**
     * Set own_email_1
     *
     * @param string $ownEmail1
     * @return ownership
     */
    public function setOwnEmail1($ownEmail1)
    {
        $this->own_email_1 = $ownEmail1;

        return $this;
    }

    /**
     * Get own_email_2
     *
     * @return string
     */
    public function getOwnEmail2()
    {
        return $this->own_email_2;
    }

    /**
     * Set own_email_2
     *
     * @param string $ownEmail2
     * @return ownership
     */
    public function setOwnEmail2($ownEmail2)
    {
        $this->own_email_2 = $ownEmail2;

        return $this;
    }

    /**
     * Get own_category
     *
     * @return string
     */
    public function getOwnCategory()
    {
        return $this->own_category;
    }

    /**
     * Set own_category
     *
     * @param string $ownCategory
     * @return ownership
     */
    public function setOwnCategory($ownCategory)
    {
        $this->own_category = $ownCategory;

        return $this;
    }

    /**
     * Get own_type
     *
     * @return string
     */
    public function getOwnType()
    {
        return $this->own_type;
    }

    /**
     * Set own_type
     *
     * @param string $ownType
     * @return ownership
     */
    public function setOwnType($ownType)
    {
        $this->own_type = $ownType;

        return $this;
    }

    /**
     * Get own_facilities_breakfast
     *
     * @return boolean
     */
    public function getOwnFacilitiesBreakfast()
    {
        return $this->own_facilities_breakfast;
    }

    /**
     * Set own_facilities_breakfast
     *
     * @param boolean $ownFacilitiesBreakfast
     * @return ownership
     */
    public function setOwnFacilitiesBreakfast($ownFacilitiesBreakfast)
    {
        $this->own_facilities_breakfast = $ownFacilitiesBreakfast;

        return $this;
    }

    /**
     * Get own_facilities_breakfast_price
     *
     * @return string
     */
    public function getOwnFacilitiesBreakfastPrice()
    {
        return $this->own_facilities_breakfast_price;
    }

    /**
     * Set own_facilities_breakfast_price
     *
     * @param string $ownFacilitiesBreakfastPrice
     * @return ownership
     */
    public function setOwnFacilitiesBreakfastPrice($ownFacilitiesBreakfastPrice)
    {
        $this->own_facilities_breakfast_price = $ownFacilitiesBreakfastPrice;

        return $this;
    }

    /**
     * Get own_facilities_dinner
     *
     * @return boolean
     */
    public function getOwnFacilitiesDinner()
    {
        return $this->own_facilities_dinner;
    }

    /**
     * Set own_facilities_dinner
     *
     * @param boolean $ownFacilitiesDinner
     * @return ownership
     */
    public function setOwnFacilitiesDinner($ownFacilitiesDinner)
    {
        $this->own_facilities_dinner = $ownFacilitiesDinner;

        return $this;
    }

    /**
     * Get own_facilities_dinner_price_from
     *
     * @return string
     */
    public function getOwnFacilitiesDinnerPriceFrom()
    {
        return $this->own_facilities_dinner_price_from;
    }

    /**
     * Set own_facilities_dinner_price_from
     *
     * @param string $ownFacilitiesDinnerPriceFrom
     * @return ownership
     */
    public function setOwnFacilitiesDinnerPriceFrom($ownFacilitiesDinnerPriceFrom)
    {
        $this->own_facilities_dinner_price_from = $ownFacilitiesDinnerPriceFrom;

        return $this;
    }

    /**
     * Get own_facilities_dinner_price_to
     *
     * @return string
     */
    public function getOwnFacilitiesDinnerPriceTo()
    {
        return $this->own_facilities_dinner_price_to;
    }

    /**
     * Set own_facilities_dinner_price_to
     *
     * @param string $ownFacilitiesDinnerPriceTo
     * @return ownership
     */
    public function setOwnFacilitiesDinnerPriceTo($ownFacilitiesDinnerPriceTo)
    {
        $this->own_facilities_dinner_price_to = $ownFacilitiesDinnerPriceTo;

        return $this;
    }

    /**
     * Get own_facilities_parking
     *
     * @return boolean
     */
    public function getOwnFacilitiesParking()
    {
        return $this->own_facilities_parking;
    }

    /**
     * Set own_facilities_parking
     *
     * @param boolean $ownFacilitiesParking
     * @return ownership
     */
    public function setOwnFacilitiesParking($ownFacilitiesParking)
    {
        $this->own_facilities_parking = $ownFacilitiesParking;

        return $this;
    }

    /**
     * Get own_facilities_parking_price
     *
     * @return string
     */
    public function getOwnFacilitiesParkingPrice()
    {
        return $this->own_facilities_parking_price;
    }

    /**
     * Set own_facilities_parking_price
     *
     * @param string $ownFacilitiesParkingPrice
     * @return ownership
     */
    public function setOwnFacilitiesParkingPrice($ownFacilitiesParkingPrice)
    {
        $this->own_facilities_parking_price = $ownFacilitiesParkingPrice;

        return $this;
    }

    /**
     * Get own_water_jacuzee
     *
     * @return boolean
     */
    public function getOwnWaterJacuzee()
    {
        return $this->own_water_jacuzee;
    }

    /**
     * Set own_water_jacuzee
     *
     * @param boolean $ownWaterJacuzee
     * @return ownership
     */
    public function setOwnWaterJacuzee($ownWaterJacuzee)
    {
        $this->own_water_jacuzee = $ownWaterJacuzee;

        return $this;
    }

    /**
     * Get own_water_sauna
     *
     * @return boolean
     */
    public function getOwnWaterSauna()
    {
        return $this->own_water_sauna;
    }

    /**
     * Set own_water_sauna
     *
     * @param boolean $ownWaterSauna
     * @return ownership
     */
    public function setOwnWaterSauna($ownWaterSauna)
    {
        $this->own_water_sauna = $ownWaterSauna;

        return $this;
    }

    /**
     * Get own_water_piscina
     *
     * @return boolean
     */
    public function getOwnWaterPiscina()
    {
        return $this->own_water_piscina;
    }

    /**
     * Set own_water_piscina
     *
     * @param boolean $ownWaterPiscina
     * @return ownership
     */
    public function setOwnWaterPiscina($ownWaterPiscina)
    {
        $this->own_water_piscina = $ownWaterPiscina;

        return $this;
    }

    /**
     * Get own_description_bicycle_parking
     *
     * @return boolean
     */
    public function getOwnDescriptionBicycleParking()
    {
        return $this->own_description_bicycle_parking;
    }

    /**
     * Set own_description_bicycle_parking
     *
     * @param boolean $ownDescriptionBicycleParking
     * @return ownership
     */
    public function setOwnDescriptionBicycleParking($ownDescriptionBicycleParking)
    {
        $this->own_description_bicycle_parking = $ownDescriptionBicycleParking;

        return $this;
    }

    /**
     * Get own_description_pets
     *
     * @return boolean
     */
    public function getOwnDescriptionPets()
    {
        return $this->own_description_pets;
    }

    /**
     * Set own_description_pets
     *
     * @param boolean $ownDescriptionPets
     * @return ownership
     */
    public function setOwnDescriptionPets($ownDescriptionPets)
    {
        $this->own_description_pets = $ownDescriptionPets;

        return $this;
    }

    /**
     * Get own_description_laundry
     *
     * @return boolean
     */
    public function getOwnDescriptionLaundry()
    {
        return $this->own_description_laundry;
    }

    /**
     * Set own_description_laundry
     *
     * @param boolean $ownDescriptionLaundry
     * @return ownership
     */
    public function setOwnDescriptionLaundry($ownDescriptionLaundry)
    {
        $this->own_description_laundry = $ownDescriptionLaundry;

        return $this;
    }

    /**
     * Get own_description_internet
     *
     * @return boolean
     */
    public function getOwnDescriptionInternet()
    {
        return $this->own_description_internet;
    }

    /**
     * Set own_description_internet
     *
     * @param boolean $ownDescriptionInternet
     * @return ownership
     */
    public function setOwnDescriptionInternet($ownDescriptionInternet)
    {
        $this->own_description_internet = $ownDescriptionInternet;

        return $this;
    }

    /**
     * Get own_address_province
     *
     * @return province
     */
    public function getOwnAddressProvince()
    {
        return $this->own_address_province;
    }

    /**
     * Set own_address_province
     *
     * @param province $ownAddressProvince
     * @return ownership
     */
    public function setOwnAddressProvince(province $ownAddressProvince = null)
    {
        $this->own_address_province = $ownAddressProvince;

        return $this;
    }

    /**
     * Get own_address_municipality
     *
     * @return municipality
     */
    public function getOwnAddressMunicipality()
    {
        return $this->own_address_municipality;
    }

    /**
     * Set own_address_municipality
     *
     * @param municipality $ownAddressMunicipality
     * @return ownership
     */
    public function setOwnAddressMunicipality(municipality $ownAddressMunicipality = null)
    {
        $this->own_address_municipality = $ownAddressMunicipality;

        return $this;
    }

    /**
     * Add own_rooms
     *
     * @param room $ownRooms
     * @return ownership
     */
    public function addOwnRoom(room $ownRooms)
    {
        $this->own_rooms[] = $ownRooms;
        $this->own_rooms_total++;
        return $this;
    }

    /**
     * Remove own_rooms
     *
     * @param room $ownRooms
     */
    public function removeOwnRoom(room $ownRooms)
    {
        $this->own_rooms->removeElement($ownRooms);
        $this->own_rooms_total--;
    }

    /**
     * Get own_rooms
     *
     * @return Collection
     */
    public function getOwnRooms()
    {
        return $this->own_rooms;
    }

    /**
     * Get own_top_20
     *
     * @return boolean
     */
    public function getOwnTop20()
    {
        return $this->own_top_20;
    }

    /**
     * Set own_top_20
     *
     * @param boolean $ownTop20
     * @return ownership
     */
    public function setOwnTop20($ownTop20)
    {
        $this->own_top_20 = $ownTop20;

        return $this;
    }

    /**
     * Get own_selection
     *
     * @return boolean
     */
    public function getOwnSelection()
    {
        return $this->own_selection;
    }

    /**
     * Set own_selection
     *
     * @param boolean $ownSelection
     * @return ownership
     */
    public function setOwnSelection($ownSelection)
    {
        $this->own_selection = $ownSelection;

        return $this;
    }

    /**
     * Get own_rating
     *
     * @return float
     */
    public function getOwnRating()
    {
        return $this->own_rating;
    }

    /**
     * Set own_rating
     *
     * @param float $ownRating
     * @return ownership
     */
    public function setOwnRating($ownRating)
    {
        $this->own_rating = $ownRating;

        return $this;
    }

    /**
     * Set own_maximun_number_guests
     *
     * @param integer $ownMaximumNumberGuests
     * @return ownership
     */
    public function setOwnMaximumNumberGuests($ownMaximumNumberGuests)
    {
        $this->own_maximun_number_guests = $ownMaximumNumberGuests;

        return $this;
    }

    /**
     * Get own_maximun_number_guests
     *
     * @return integer
     */
    public function getOwnMaximumNumberGuests()
    {
        return $this->own_maximun_number_guests;
    }

    /**
     * Get own_minimum_price
     *
     * @return float
     */
    public function getOwnMinimumPrice()
    {
        return $this->own_minimum_price;
    }

    /**
     * Set own_minimum_price
     *
     * @param float $ownMinimumPrice
     * @return ownership
     */
    public function setOwnMinimumPrice($ownMinimumPrice)
    {
        $this->own_minimum_price = $ownMinimumPrice;

        return $this;
    }

    /**
     * Get own_maximum_price
     *
     * @return float
     */
    public function getOwnMaximumPrice()
    {
        return $this->own_maximum_price;
    }

    /**
     * Set own_maximum_price
     *
     * @param float $ownMaximumPrice
     * @return ownership
     */
    public function setOwnMaximumPrice($ownMaximumPrice)
    {
        $this->own_maximum_price = $ownMaximumPrice;

        return $this;
    }

    /**
     * Get own_comments_total
     *
     * @return integer
     */
    public function getOwnCommentsTotal()
    {
        return $this->own_comments_total;
    }

    /**
     * Set own_comments_total
     *
     * @param integer $ownCommentsTotal
     * @return ownership
     */
    public function setOwnCommentsTotal($ownCommentsTotal)
    {
        $this->own_comments_total = $ownCommentsTotal;

        return $this;
    }

    /**
     * Retur object as string
     * @return string
     */
    public function __toString()
    {
        return $this->getOwnMcpCode() . " - " . $this->getOwnName();
    }

    /**
     * Get own_mcp_code
     *
     * @return string
     */
    public function getOwnMcpCode()
    {
        return $this->own_mcp_code;
    }

    /**
     * Set own_mcp_code
     *
     * @param string $ownMcpCode
     * @return ownership
     */
    public function setOwnMcpCode($ownMcpCode)
    {
        $this->own_mcp_code = $ownMcpCode;

        return $this;
    }

    /**
     * Get own_name
     *
     * @return string
     */
    public function getOwnName()
    {
        return $this->own_name;
    }

    /**
     * Set own_name
     *
     * @param string $ownName
     * @return ownership
     */
    public function setOwnName($ownName)
    {
        $this->own_name = $ownName;

        return $this;
    }

    /**
     * Codigo Yanet - Fin
     */

    /**
     * Get own_maximun_number_guests
     *
     * @return integer
     */
    public function getOwnMaximunNumberGuests()
    {
        return $this->own_maximun_number_guests;
    }

    /**
     * Set own_maximun_number_guests
     *
     * @param integer $ownMaximunNumberGuests
     * @return ownership
     */
    public function setOwnMaximunNumberGuests($ownMaximunNumberGuests)
    {
        $this->own_maximun_number_guests = $ownMaximunNumberGuests;

        return $this;
    }

    /**
     * Get own_facilities_notes
     *
     * @return string
     */
    public function getOwnFacilitiesNotes()
    {
        return $this->own_facilities_notes;
    }

    /**
     * Set own_facilities_notes
     *
     * @param string $ownFacilitiesNotes
     * @return ownership
     */
    public function setOwnFacilitiesNotes($ownFacilitiesNotes)
    {
        $this->own_facilities_notes = $ownFacilitiesNotes;

        return $this;
    }

    /**
     * Get own_langs
     *
     * @return string
     */
    public function getOwnLangs()
    {
        return $this->own_langs;
    }

    /**
     * Set own_langs
     *
     * @param string $ownLangs
     * @return ownership
     */
    public function setOwnLangs($ownLangs)
    {
        $this->own_langs = $ownLangs;

        return $this;
    }

    /**
     * Get own_geolocate_x
     *
     * @return string
     */
    public function getOwnGeolocateX()
    {
        return $this->own_geolocate_x;
    }

    /**
     * Set own_geolocate_x
     *
     * @param string $ownGeolocateX
     * @return ownership
     */
    public function setOwnGeolocateX($ownGeolocateX)
    {
        $this->own_geolocate_x = $ownGeolocateX;

        return $this;
    }

    /**
     * Get own_geolocate_y
     *
     * @return string
     */
    public function getOwnGeolocateY()
    {
        return $this->own_geolocate_y;
    }

    /**
     * Set own_geolocate_y
     *
     * @param string $ownGeolocateY
     * @return ownership
     */
    public function setOwnGeolocateY($ownGeolocateY)
    {
        $this->own_geolocate_y = $ownGeolocateY;

        return $this;
    }

    /**
     * Get own_phone_code
     *
     * @return string
     */
    public function getOwnPhoneCode()
    {
        return $this->own_phone_code;
    }

    /**
     * Set own_phone_code
     *
     * @param string $ownPhoneCode
     * @return ownership
     */
    public function setOwnPhoneCode($ownPhoneCode)
    {
        $this->own_phone_code = $ownPhoneCode;

        return $this;
    }

    /**
     * Get own_comment
     *
     * @return string
     */
    public function getOwnComment()
    {
        return $this->own_comment;
    }

    /**
     * Set own_comment
     *
     * @param string $ownComment
     * @return ownership
     */
    public function setOwnComment($ownComment)
    {
        $this->own_comment = $ownComment;

        return $this;
    }

    /**
     * Get own_commission_percent
     *
     * @return integer
     */
    public function getOwnCommissionPercent()
    {
        return $this->own_commission_percent;
    }

    /**
     * Set own_commission_percent
     *
     * @param integer $ownCommissionPercent
     * @return ownership
     */
    public function setOwnCommissionPercent($ownCommissionPercent)
    {
        $this->own_commission_percent = $ownCommissionPercent;

        return $this;
    }

    public function getSyncSt()
    {
        return $this->own_sync_st;
    }

    public function setSyncSt($own_sync_st)
    {
        $this->own_sync_st = $own_sync_st;
    }

    /**
     * Get count_total rooms
     *
     * @return integer
     */
    public function getOwnRoomsTotal()
    {
        return count($this->own_rooms);
    }

    /**
     * Set own_rooms_total
     *
     * @param integer $ownRoomsTotal
     * @return ownership
     */
    public function setOwnRoomsTotal($ownRoomsTotal)
    {
        $this->own_rooms_total = $ownRoomsTotal;

        return $this;
    }

    public function getFullAddress()
    {
        return $this->own_address_street . ", No." . $this->own_address_number . ", " . $this->own_address_municipality . ", " . $this->own_address_province;
    }

//-----------------------------------------------------------------------------
    // <editor-fold defaultstate="collapsed" desc="Logic Methods">

    public function getRoom($room_num)
    {
        foreach ($this->own_rooms as $room) {
            if ($room->getRoomNum() == $room_num) {
                return $room;
            }
        }
        return null;
    }

    /**
     * Get own_sync_st
     *
     * @return integer
     */
    public function getOwnSyncSt()
    {
        return $this->own_sync_st;
    }

// </editor-fold>

    /**
     * Set own_sync_st
     *
     * @param integer $ownSyncSt
     * @return ownership
     */
    public function setOwnSyncSt($ownSyncSt)
    {
        $this->own_sync_st = $ownSyncSt;

        return $this;
    }

    /**
     * Get own_owner_photo
     *
     * @return photo
     */
    public function getOwnOwnerPhoto()
    {
        return $this->own_owner_photo;
    }

    /**
     * Set own_owner_photo
     *
     * @param photo $ownPhoto
     * @return ownership
     */
    public function setOwnOwnerPhoto($ownPhoto)
    {
        $this->own_owner_photo = $ownPhoto;

        return $this;
    }

    /**
     * Get own_sended_to_team
     *
     * @return boolean
     */
    public function getOwnSendedToTeam()
    {
        return $this->own_sended_to_team;
    }

    /**
     * Set own_sended_to_team
     *
     * @param boolean $ownSended
     * @return ownership
     */
    public function setOwnSendedToTeam($ownSended)
    {
        $this->own_sended_to_team = $ownSended;

        return $this;
    }

    /**
     * Get own_publish_date
     *
     * @return datetime
     */
    public function getOwnPublishDate()
    {
        return $this->own_publish_date;
    }

    /**
     * Set own_publish_date
     *
     * @param datetime $publishDate
     * @return ownership
     */
    public function setOwnPublishDate($publishDate)
    {
        $this->own_publish_date = $publishDate;

        return $this;
    }

    /**
     * Get own_inmediate_booking
     *
     * @return boolean
     */
    public function getOwnInmediateBooking()
    {
        return $this->own_inmediate_booking;
    }

    /**
     * Set own_inmediate_booking
     *
     * @param boolean $inmediateBooking
     * @return ownership
     */
    public function setOwnInmediateBooking($inmediateBooking)
    {
        $this->own_inmediate_booking = $inmediateBooking;

        return $this;
    }

    /**
     * Get own_automatic_mcp_code
     *
     * @return int
     */
    public function getOwnAutomaticMcpCode()
    {

        return $this->own_automatic_mcp_code;
    }

    /**
     * Set own_automatic_mcp_code
     *
     * @param int $ownAutomaticMcpCode
     * @return ownership
     */
    public function setOwnAutomaticMcpCode($ownAutomaticMcpCode)
    {
        $this->own_automatic_mcp_code = $ownAutomaticMcpCode;

        return $this;
    }

    /**
     * Get own_mcp_code_generated
     *
     * @return string
     */
    public function getOwnMcpCodeGenerated()
    {

        return $this->own_mcp_code_generated;
    }

    /**
     * Set own_mcp_code_generated
     *
     * @param string $ownMcpCodeGenerated
     * @return ownership
     */
    public function setOwnMcpCodeGenerated($ownMcpCodeGenerated)
    {
        $this->own_mcp_code_generated = $ownMcpCodeGenerated;

        return $this;
    }

    /**
     * Get own_cubacoupon
     *
     * @return boolean
     */
    public function getOwnCubaCoupon()
    {

        return $this->own_cubacoupon;
    }

    /**
     * Set own_cubacoupon
     *
     * @param boolean $ownCubaCoupon
     * @return ownership
     */
    public function setOwnCubaCoupon($ownCubaCoupon)
    {
        $this->own_cubacoupon = $ownCubaCoupon;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getAwards()
    {
        return $this->awards;
    }

    /**
     * @param mixed $awards
     * @return mixed
     */
    public function setAwards($awards)
    {
        $this->awards = $awards;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getOwnershipKeywordOwnership()
    {
        return $this->ownershipKeywordOwnership;
    }

    /**
     * @param mixed $ownershipKeywordOwnership
     * @return mixed
     */
    public function setOwnershipKeywordOwnership($ownershipKeywordOwnership)
    {
        $this->ownershipKeywordOwnership = $ownershipKeywordOwnership;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getComments()
    {
        return $this->comments;
    }

    /**
     * @param mixed $comments
     * @return mixed
     */
    public function setComments($comments)
    {
        $this->comments = $comments;
        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getPhotos()
    {
        return $this->photos;
    }

    /**
     * @param mixed $photos
     * @return mixed
     */
    public function setPhotos($photos)
    {
        $this->photos = $photos;
        return $this;
    }

    public function addPhoto($photo)
    {
        $this->photos->add($photo);
    }

    public function removePhoto($photo)
    {
        $this->photos->removeElement($photo);
    }

    /**
     * @return boolean
     */
    public function getOwnSmsNotifications()
    {
        return $this->own_sms_notifications;
    }

    /**
     * @param boolean $own_sms_notifications
     * @return mixed
     */
    public function setOwnSmsNotifications($own_sms_notifications)
    {
        $this->own_sms_notifications = $own_sms_notifications;
        return $this;
    }

    public function getLogDescription()
    {
        return "Alojamiento " . $this->getOwnMcpCode();
    }

    /**
     * @return mixed
     */
    public function getOwners()
    {
        return $this->owners;
    }


    /*Logs functions*/

    /**
     * @param mixed $owners
     * @return mixed
     */
    public function setOwners($owners)
    {
        $this->owners = $owners;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isWaitingForRevision()
    {
        return $this->waiting_for_revision;
    }

    /**
     * Get waitingForRevision
     *
     * @return boolean
     */
    public function getWaitingForRevision()
    {
        return $this->waiting_for_revision;
    }

    /**
     * @param boolean $waiting_for_revision
     * @return mixed
     */
    public function setWaitingForRevision($waiting_for_revision)
    {
        $this->waiting_for_revision = $waiting_for_revision;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getOwnershipLogs()
    {
        return $this->ownershipLogs;
    }

    /**
     * @param mixed $ownershipLogs
     * @return mixed
     */
    public function setOwnershipLogs($ownershipLogs)
    {
        $this->ownershipLogs = $ownershipLogs;
        return $this;
    }

    /**
     * @return array
     */
    public function getCategories()
    {
        return $this->categories;
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

    public function getCodeAndName()
    {
        return $this->getOwnMcpCode() . " - " . $this->getOwnName();
    }

    /**
     * Add ownDescriptionLang
     *
     * @param \MyCp\mycpBundle\Entity\ownershipDescriptionLang $ownDescriptionLang
     *
     * @return ownership
     */
    public function addOwnDescriptionLang(\MyCp\mycpBundle\Entity\ownershipDescriptionLang $ownDescriptionLang)
    {
        $this->own_description_langs[] = $ownDescriptionLang;

        return $this;
    }

    /**
     * Remove ownDescriptionLang
     *
     * @param \MyCp\mycpBundle\Entity\ownershipDescriptionLang $ownDescriptionLang
     */
    public function removeOwnDescriptionLang(\MyCp\mycpBundle\Entity\ownershipDescriptionLang $ownDescriptionLang)
    {
        $this->own_description_langs->removeElement($ownDescriptionLang);
    }

    /**
     * Add ownGeneralReservation
     *
     * @param \MyCp\mycpBundle\Entity\generalReservation $ownGeneralReservation
     *
     * @return ownership
     */
    public function addOwnGeneralReservation(\MyCp\mycpBundle\Entity\generalReservation $ownGeneralReservation)
    {
        $this->own_general_reservations[] = $ownGeneralReservation;

        return $this;
    }

    /**
     * Remove ownGeneralReservation
     *
     * @param \MyCp\mycpBundle\Entity\generalReservation $ownGeneralReservation
     */
    public function removeOwnGeneralReservation(\MyCp\mycpBundle\Entity\generalReservation $ownGeneralReservation)
    {
        $this->own_general_reservations->removeElement($ownGeneralReservation);
    }

    /**
     * Add ownershipKeywordOwnership
     *
     * @param \MyCp\mycpBundle\Entity\ownershipKeywordLang $ownershipKeywordOwnership
     *
     * @return ownership
     */
    public function addOwnershipKeywordOwnership(\MyCp\mycpBundle\Entity\ownershipKeywordLang $ownershipKeywordOwnership)
    {
        $this->ownershipKeywordOwnership[] = $ownershipKeywordOwnership;

        return $this;
    }

    /**
     * Remove ownershipKeywordOwnership
     *
     * @param \MyCp\mycpBundle\Entity\ownershipKeywordLang $ownershipKeywordOwnership
     */
    public function removeOwnershipKeywordOwnership(\MyCp\mycpBundle\Entity\ownershipKeywordLang $ownershipKeywordOwnership)
    {
        $this->ownershipKeywordOwnership->removeElement($ownershipKeywordOwnership);
    }

    /**
     * Add award
     *
     * @param \MyCp\mycpBundle\Entity\accommodationAward $award
     *
     * @return ownership
     */
    public function addAward(\MyCp\mycpBundle\Entity\accommodationAward $award)
    {
        $this->awards[] = $award;

        return $this;
    }

    /**
     * Remove award
     *
     * @param \MyCp\mycpBundle\Entity\accommodationAward $award
     */
    public function removeAward(\MyCp\mycpBundle\Entity\accommodationAward $award)
    {
        $this->awards->removeElement($award);
    }

    /**
     * Add comment
     *
     * @param \MyCp\mycpBundle\Entity\comment $comment
     *
     * @return ownership
     */
    public function addComment(\MyCp\mycpBundle\Entity\comment $comment)
    {
        $this->comments[] = $comment;

        return $this;
    }

    /**
     * Remove comment
     *
     * @param \MyCp\mycpBundle\Entity\comment $comment
     */
    public function removeComment(\MyCp\mycpBundle\Entity\comment $comment)
    {
        $this->comments->removeElement($comment);
    }

    /**
     * Add owner
     *
     * @param \MyCp\mycpBundle\Entity\ownerAccommodation $owner
     *
     * @return ownership
     */
    public function addOwner(\MyCp\mycpBundle\Entity\ownerAccommodation $owner)
    {
        $this->owners[] = $owner;

        return $this;
    }

    /**
     * Remove owner
     *
     * @param \MyCp\mycpBundle\Entity\ownerAccommodation $owner
     */
    public function removeOwner(\MyCp\mycpBundle\Entity\ownerAccommodation $owner)
    {
        $this->owners->removeElement($owner);
    }

    /**
     * Add ownershipLog
     *
     * @param \MyCp\mycpBundle\Entity\ownershipStatistics $ownershipLog
     *
     * @return ownership
     */
    public function addOwnershipLog(\MyCp\mycpBundle\Entity\ownershipStatistics $ownershipLog)
    {
        $this->ownershipLogs[] = $ownershipLog;

        return $this;
    }

    /**
     * Remove ownershipLog
     *
     * @param \MyCp\mycpBundle\Entity\ownershipStatistics $ownershipLog
     */
    public function removeOwnershipLog(\MyCp\mycpBundle\Entity\ownershipStatistics $ownershipLog)
    {
        $this->ownershipLogs->removeElement($ownershipLog);
    }

    /**
     * Add payment
     *
     * @param \MyCp\mycpBundle\Entity\ownershipPayment $payment
     *
     * @return ownership
     */
    public function addPayment(\MyCp\mycpBundle\Entity\ownershipPayment $payment)
    {
        $this->payments[] = $payment;

        return $this;
    }

    /**
     * Remove payment
     *
     * @param \MyCp\mycpBundle\Entity\ownershipPayment $payment
     */
    public function removePayment(\MyCp\mycpBundle\Entity\ownershipPayment $payment)
    {
        $this->payments->removeElement($payment);
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;

    }

    /**
     * @param mixed $data
     * @return mixed
     */
    public function setData($data)
    {
        $this->data = $data;
        return $this;
    }

    /**
     * @return int
     */
    public function getInsertedInCasaModule()
    {
        return $this->insertedInCasaModule;
    }

    /**
     * @param int $insertedInCasaModule
     * @return mixed
     */
    public function setInsertedInCasaModule($insertedInCasaModule)
    {
        $this->insertedInCasaModule = $insertedInCasaModule;
        return $this;
    }

    public function isActive()
    {
        return $this->getOwnStatus()->getStatusId() == ownershipStatus::STATUS_ACTIVE;
    }

    /**
     * Get own_status
     *
     * @return ownershipStatus
     */
    public function getOwnStatus()
    {
        return $this->own_status;
    }

    /**
     * Set own_status
     *
     * @param ownershipStatus $ownStatus
     * @return ownership
     */
    public function setOwnStatus(ownershipStatus $ownStatus = null)
    {
        $this->own_status = $ownStatus;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getRankingExtras()
    {
        return $this->rankingExtras;
    }

    /**
     * @param mixed $rankingExtras
     * @return mixed
     */
    public function setRankingExtras($rankingExtras)
    {
        $this->rankingExtras = $rankingExtras;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isOwnInmediateBooking2()
    {
        return $this->own_inmediate_booking_2;
    }

    /**
     * @return boolean
     */
    public function getOwnInmediateBooking2()
    {
        return $this->own_inmediate_booking_2;
    }

    /**
     * @param boolean $own_inmediate_booking_2
     * @return mixed
     */
    public function setOwnInmediateBooking2($own_inmediate_booking_2)
    {
        $this->own_inmediate_booking_2 = $own_inmediate_booking_2;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPenalties()
    {
        return $this->penalties;
    }

    /**
     * @param mixed $penalties
     * @return mixed
     */
    public function setPenalties($penalties)
    {
        $this->penalties = $penalties;
        return $this;
    }

    /**
     * Add created penalty
     *
     * @param \MyCp\mycpBundle\Entity\penalty $penalty
     *
     * @return user
     */
    public function addPenalty(penalty $penalty)
    {
        $this->penalties[] = $penalty;
        return $this;
    }

    /**
     * Remove created penalty
     *
     * @param \MyCp\mycpBundle\Entity\penalty $penalty
     */
    public function removePenalty(penalty $penalty)
    {
        $this->penalties->removeElement($penalty);
    }

    /**
     * @return mixed
     */
    public function getFailures()
    {
        return $this->failures;
    }

    /**
     * @param mixed $failures
     * @return mixed
     */
    public function setFailures($failures)
    {
        $this->failures = $failures;
        return $this;
    }

    /**
     * Add created failure
     *
     * @param \MyCp\mycpBundle\Entity\failure $failure
     *
     * @return user
     */
    public function addFailure(failure $failure)
    {
        $this->failures[] = $failure;
        return $this;
    }

    /**
     * Remove created failure
     *
     * @param \MyCp\mycpBundle\Entity\failure $failure
     */
    public function removeFailure(failure $failure)
    {
        $this->failures->removeElement($failure);
    }

    /**
     * @return mixed
     */
    public function getCalendarUpdateFrequency()
    {
        return $this->calendarUpdateFrequency;
    }

    /**
     * @param mixed $calendarUpdateFrequency
     * @return mixed
     */
    public function setCalendarUpdateFrequency($calendarUpdateFrequency)
    {
        $this->calendarUpdateFrequency = $calendarUpdateFrequency;
        return $this;
    }

    /**
     * Add created calendarUpdateFrequency
     *
     * @param \MyCp\mycpBundle\Entity\accommodationCalendarFrequency $calendarUpdateFrequency
     *
     * @return user
     */
    public function addCalendarUpdateFrequency(accommodationCalendarFrequency $calendarUpdateFrequency)
    {
        $this->calendarUpdateFrequency[] = $calendarUpdateFrequency;
        return $this;
    }

    /**
     * Remove created calendarUpdateFrequency
     *
     * @param \MyCp\mycpBundle\Entity\accommodationCalendarFrequency $calendarUpdateFrequency
     */
    public function removeCalendarUpdateFrequency(accommodationCalendarFrequency $calendarUpdateFrequency)
    {
        $this->calendarUpdateFrequency->removeElement($calendarUpdateFrequency);
    }

    /**
     * @return int
     */
    public function getCountVisits()
    {
        return $this->count_visits;
    }

    /**
     * @param int $count_visits
     * @return mixed
     */
    public function setCountVisits($count_visits)
    {
        $this->count_visits = $count_visits;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isConfidence()
    {
        return $this->confidence;
    }

    /**
     * Get confidence
     *
     * @return boolean
     */
    public function getConfidence()
    {
        return $this->confidence;
    }

    /**
     * @param boolean $confidence
     * @return mixed
     */
    public function setConfidence($confidence)
    {
        $this->confidence = $confidence;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getModalityUpdateFrequency()
    {
        return $this->modalityUpdateFrequency;
    }

    /**
     * @param mixed $modalityUpdateFrequency
     * @return mixed
     */
    public function setModalityUpdateFrequency($modalityUpdateFrequency)
    {
        $this->modalityUpdateFrequency = $modalityUpdateFrequency;
        return $this;
    }

    /**
     * Add created modalityUpdateFrequency
     *
     * @param \MyCp\mycpBundle\Entity\accommodationModalityFrequency $modalityUpdateFrequency
     *
     * @return user
     */
    public function addModalityUpdateFrequency(accommodationModalityFrequency $modalityUpdateFrequency)
    {
        $this->modalityUpdateFrequency[] = $modalityUpdateFrequency;
        return $this;
    }

    /**
     * Remove created accommodationModalityFrequency
     *
     * @param \MyCp\mycpBundle\Entity\accommodationModalityFrequency $modalityUpdateFrequency
     */
    public function removeModalityUpdateFrequency(accommodationModalityFrequency $modalityUpdateFrequency)
    {
        $this->modalityUpdateFrequency->removeElement($modalityUpdateFrequency);
    }

    /**
     * Add notification
     *
     * @param \MyCp\mycpBundle\Entity\notification $notification
     *
     * @return ownership
     */
    public function addNotification(\MyCp\mycpBundle\Entity\notification $notification)
    {
        $this->notifications[] = $notification;

        return $this;
    }

    /**
     * Remove notification
     *
     * @param \MyCp\mycpBundle\Entity\notification $notification
     */
    public function removeNotification(\MyCp\mycpBundle\Entity\notification $notification)
    {
        $this->notifications->removeElement($notification);
    }

    /**
     * Get notifications
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getNotifications()
    {
        return $this->notifications;
    }

    /**
     * Add rankingExtra
     *
     * @param \MyCp\mycpBundle\Entity\ownershipRankingExtra $rankingExtra
     *
     * @return ownership
     */
    public function addRankingExtra(\MyCp\mycpBundle\Entity\ownershipRankingExtra $rankingExtra)
    {
        $this->rankingExtras[] = $rankingExtra;

        return $this;
    }

    /**
     * Remove rankingExtra
     *
     * @param \MyCp\mycpBundle\Entity\ownershipRankingExtra $rankingExtra
     */
    public function removeRankingExtra(\MyCp\mycpBundle\Entity\ownershipRankingExtra $rankingExtra)
    {
        $this->rankingExtras->removeElement($rankingExtra);
    }

    /**
     * @return int
     */
    public function getModifying()
    {
        return $this->modifying;
    }

    /**
     * @param int $modifying
     */
    public function setModifying($modifying)
    {
        $this->modifying = $modifying;
    }

    /**
     * @return DateTime
     */
    public function getOwnHotDate()
    {
        return $this->own_hot_date;
    }

    /**
     * @param DateTime $own_hot_date
     */
    public function setOwnHotDate($own_hot_date)
    {
        $this->own_hot_date = $own_hot_date;
    }

    /**
     * @return mixed
     */
    public function getTransferMethodsPayment()
    {
        return $this->transferMethodsPayment;
    }

    /**
     * @param mixed $transferMethodsPayment
     */
    public function setTransferMethodsPayment($transferMethodsPayment)
    {
        $this->transferMethodsPayment = $transferMethodsPayment;
    }

    /**
     * Add transferMethodPayment
     *
     * @param transferMethodPayment $transferMethodPayment
     *
     * @return ownership
     */
    public function addTransferMethodPayment(transferMethodPayment $transferMethodPayment)
    {
        $this->transferMethodsPayment[] = $transferMethodPayment;

        return $this;
    }

    /**
     * Remove transferMethodPayment
     *
     * @param transferMethodPayment $transferMethodPayment
     */
    public function removeTransferMethodPayment(transferMethodPayment $transferMethodPayment)
    {
        $this->transferMethodsPayment->removeElement($transferMethodPayment);
    }

    /**
     * @return mixed
     */
    public function getEffectiveMethodsPayment()
    {
        return $this->effectiveMethodsPayment;
    }

    /**
     * @param mixed $effectiveMethodsPayment
     */
    public function setEffectiveMethodsPayment($effectiveMethodsPayment)
    {
        $this->effectiveMethodsPayment = $effectiveMethodsPayment;
    }

    /**
     * Add effectiveMethodPayment
     *
     * @param effectiveMethodPayment $effectiveMethodPayment
     *
     * @return ownership
     */
    public function addEffectiveMethodPayment(effectiveMethodPayment $effectiveMethodPayment)
    {
        $this->effectiveMethodsPayment[] = $effectiveMethodPayment;

        return $this;
    }

    /**
     * Remove effectiveMethodPayment
     *
     * @param effectiveMethodPayment $effectiveMethodPayment
     */
    public function removeEffectiveMethodPayment(effectiveMethodPayment $effectiveMethodPayment)
    {
        $this->effectiveMethodsPayment->removeElement($effectiveMethodPayment);
    }

    /**
     * @return string
     */
    public function getOwnOldName()
    {
        return $this->own_old_name;
    }

    /**
     * @param string $own_old_name
     * @return mixed
     */
    public function setOwnOldName($own_old_name)
    {
        $this->own_old_name = $own_old_name;
        return $this;
    }

    /**
     * @return int
     */
    public function getGoodPicture()
    {
        return $this->goodPicture;
    }

    /**
     * @param int $goodPicture
     * @return mixed
     */
    public function setGoodPicture($goodPicture)
    {
        $this->goodPicture = $goodPicture;
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
     * Get withIcal
     *
     * @return boolean
     */
    public function getWithIcal()
    {
        return $this->withIcal;
    }

    /**
     * Get withIcal
     * @param boolean $withIcal
     * @return mixed
     */
    public function setWithIcal($withIcal)
    {
        $this->withIcal = $withIcal;

        return $this;
    }

    public function getCompleteReservationMode()
    {
        return ($this->bookingModality != null && $this->bookingModality->getBookingModality()->getName() == bookingModality::COMPLETE_RESERVATION_BOOKING);
    }

    public function isRentalTypeFull()
    {
        return self::ACCOMMODATION_RENTAL_TYPE_FULL == $this->getOwnRentalType() ? true : false;
    }

    /**
     * Get ownRentalType
     *
     * @return string
     */
    public function getOwnRentalType()
    {
        return $this->own_rental_type;
    }

    /**
     * Set ownRentalType
     *
     * @param string $ownRentalType
     *
     * @return ownership
     */
    public function setOwnRentalType($ownRentalType)
    {
        $this->own_rental_type = $ownRentalType;

        return $this;
    }

    public function getPrinicipalPhoto()
    {
        if (!is_null($this->data) && !is_null($this->data->getPrincipalPhoto())) {
            return $this->data->getPrincipalPhoto()->getOwnPhoPhoto();
        }
        return null;
    }

    /**
     * @return bool
     */
    public function isOwnAgencyWork()
    {
        return $this->own_agencyWork;
    }

    /**
     * @param bool $own_agencyWork
     */
    public function setOwnAgencyWork($own_agencyWork)
    {
        $this->own_agencyWork = $own_agencyWork;
    }


    /**
     * @return string
     */
    public function getOwnModalityReservation()
    {
        return $this->own_modalityReservation;
    }

    /**
     * @param string $own_modalityReservation
     */
    public function setOwnModalityReservation($own_modalityReservation)
    {
        /**
         * Se intenta garantizar que se sincronice la informacion del la modalidad de reserva.
         */
        $this->own_modalityReservation = $own_modalityReservation;
        if ($own_modalityReservation == self::MODALITY_IMMEDIATE_BOOKING) {
            $this->own_inmediate_booking = false;
            $this->own_inmediate_booking_2 = true;
        } else {
            if ($own_modalityReservation == self::MODALITY_QUICKLY_BOOKING) {
                $this->own_inmediate_booking_2 = false;
                $this->own_inmediate_booking = true;
            } else {
                $this->own_inmediate_booking = false;
                $this->own_inmediate_booking_2 = false;
            }
        }
    }

    /**
     * @return bool
     */
    public function isOwnFacilitiesBreakfastInclude()
    {
        return $this->own_facilities_breakfast_include;
    }

    /**
     * @param bool $own_facilities_breakfast_include
     * @return ownership
     */
    public function setOwnFacilitiesBreakfastInclude($own_facilities_breakfast_include)
    {
        $this->own_facilities_breakfast_include = $own_facilities_breakfast_include;

        return $this;
    }


}

