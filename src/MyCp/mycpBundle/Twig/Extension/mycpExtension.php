<?php

namespace MyCp\mycpBundle\Twig\Extension;

use MyCp\mycpBundle\Helpers\BackendModuleName;
use MyCp\mycpBundle\Entity\season;
use MyCp\mycpBundle\Entity\ownershipReservation;

class mycpExtension extends \Twig_Extension {
    private $em;
    private $timer;

    public function __construct($em, $timer ) {
        $this->em = $em;
        $this->timer = $timer;
    }

    public function getName() {
        return "utils";
    }

    public function getFilters() {
        return array(
            new \Twig_SimpleFilter('moduleName', array($this, 'moduleName')),
            new \Twig_SimpleFilter('seasonType', array($this, 'seasonType')),
            new \Twig_SimpleFilter('ownershipReservationStatusType', array($this, 'ownershipReservationStatusType')),
            new \Twig_SimpleFilter('mailListFunction', array($this, 'mailListFunction')),
            new \Twig_SimpleFilter('season', array($this, 'season')),
        );
    }

    public function getFunctions() {
        return array(
        );
    }

    public function moduleName($module_number) {
        switch ($module_number) {
            case BackendModuleName::MODULE_DESTINATION: return "Destination";
            case BackendModuleName::MODULE_FAQS: return "FAQ";
            case BackendModuleName::MODULE_ALBUM: return "Album";
            case BackendModuleName::MODULE_OWNERSHIP: return "Accommodation";
            case BackendModuleName::MODULE_CURRENCY: return "Currency";
            case BackendModuleName::MODULE_LANGUAGE: return "Language";
            case BackendModuleName::MODULE_RESERVATION: return "Reservation";
            case BackendModuleName::MODULE_USER: return "User";
            case BackendModuleName::MODULE_GENERAL_INFORMATION: return "General Information";
            case BackendModuleName::MODULE_COMMENT: return "Comment";
            case BackendModuleName::MODULE_UNAVAILABILITY_DETAILS: return "Unavailability Details";
            case BackendModuleName::MODULE_METATAGS: return "Meta Tags";
            case BackendModuleName::MODULE_MUNICIPALITY: return "Municipality";
            case BackendModuleName::MODULE_SEASON: return "Season";
            case BackendModuleName::MODULE_LODGING_RESERVATION: return "Lodging - Reservations";
            case BackendModuleName::MODULE_LODGING_COMMENT: return "Lodging - Comments";
            case BackendModuleName::MODULE_LODGING_OWNERSHIP: return "Lodging - MyCasa";
            case BackendModuleName::MODULE_LODGING_USER: return "Lodging - User Profile";
            case BackendModuleName::MODULE_MAIL_LIST: return "Mail List";
            case BackendModuleName::MODULE_BATCH_PROCESS: return "Batch Process";
            case BackendModuleName::MODULE_CLIENT_MESSAGES: return "Messages to Clients";
            case BackendModuleName::MODULE_CLIENT_COMMENTS: return "Comments of Clients";

            default: return "MyCP";
        }
    }

    public function seasonType($season_type) {
        switch ($season_type) {
            case season::SEASON_TYPE_HIGH: return "Alta";
            case season::SEASON_TYPE_SPECIAL: return "Especial";
            default: return "Baja";
        }
    }

    public function ownershipReservationStatusType($status) {
        switch ($status) {
            case ownershipReservation::STATUS_PENDING: return "PENDING";
            case ownershipReservation::STATUS_AVAILABLE: return "AVAILABLE";
            case ownershipReservation::STATUS_AVAILABLE2: return "AVAILABLE";
            case ownershipReservation::STATUS_NOT_AVAILABLE: return "NOT_AVAILABLE";
            case ownershipReservation::STATUS_CANCELLED: return "CANCELLED";
            case ownershipReservation::STATUS_RESERVED: return "RESERVE_SINGULAR";
            default: return "PENDING";
        }
    }
    public function mailListFunction($function) {
        return \MyCp\mycpBundle\Entity\mailList::getMailFunctionName($function);
    }

    public function season($date, $minDate, $maxDate, $idDestination)
    {
        $seasons = $this->em->getRepository("mycpBundle:season")->getSeasons($minDate, $maxDate, $idDestination);
        return $this->timer->seasonTypeByDate($seasons, $date->getTimestamp());
    }

}
