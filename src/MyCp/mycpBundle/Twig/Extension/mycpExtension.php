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
        return BackendModuleName::getModuleName($module_number);
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
