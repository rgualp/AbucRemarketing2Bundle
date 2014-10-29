<?php

namespace MyCp\mycpBundle\Twig\Extension;

use MyCp\mycpBundle\Helpers\BackendModuleName;
use MyCp\mycpBundle\Entity\season;
use MyCp\mycpBundle\Entity\ownershipReservation;

class mycpExtension extends \Twig_Extension {
    /* private $session;
      private $entity_manager; */

    public function __construct(/* $session, $entity_manager */) {
        /* $this->session = $session;
          $this->entity_manager = $entity_manager; */
    }

    public function getName() {
        return "utils";
    }

    public function getFilters() {
        return array(
            new \Twig_SimpleFilter('moduleName', array($this, 'moduleName')),
            new \Twig_SimpleFilter('seasonType', array($this, 'seasonType')),
            new \Twig_SimpleFilter('ownershipReservationStatusType', array($this, 'ownershipReservationStatusType')),
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
            case BackendModuleName::MODULE_LODGING_RESERVATION: return "Modulo Casa - Reservaciones";
            case BackendModuleName::MODULE_LODGING_COMMENT: return "Modulo Casa - Comentarios";
            case BackendModuleName::MODULE_LODGING_OWNERSHIP: return "Modulo Casa - Alojamiento";
            case BackendModuleName::MODULE_LODGING_USER: return "Modulo Casa - Usuario";

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

}
