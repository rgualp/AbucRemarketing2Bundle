<?php

namespace MyCp\mycpBundle\Twig\Extension;

use MyCp\mycpBundle\Entity\log;
use MyCp\mycpBundle\Helpers\BackendModuleName;
use MyCp\mycpBundle\Entity\season;
use MyCp\mycpBundle\Entity\ownershipReservation;
use MyCp\mycpBundle\Entity\generalReservation;

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
            new \Twig_SimpleFilter('generalReservationStatusType', array($this, 'generalReservationStatusType')),
            new \Twig_SimpleFilter('mailListFunction', array($this, 'mailListFunction')),
            new \Twig_SimpleFilter('season', array($this, 'season')),
            new \Twig_SimpleFilter('user', array($this, 'user')),
            new \Twig_SimpleFilter('logOperationName', array($this, 'logOperationName')),
        );
    }

    public function getFunctions() {
        return array(
            'nights' => new \Twig_Function_Method($this, 'nights'),
        );
    }

    public function logOperationName($logOperationName) {
        return log::getOperationName($logOperationName);
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

    public function generalReservationStatusType($status) {
        return generalReservation::getStatusName($status);
    }

    public function mailListFunction($function) {
        return \MyCp\mycpBundle\Entity\mailList::getMailFunctionName($function);
    }

    public function season($date, $minDate, $maxDate, $idDestination)
    {
        $seasons = $this->em->getRepository("mycpBundle:season")->getSeasons($minDate, $maxDate, $idDestination);
        return $this->timer->seasonTypeByDate($seasons, $date->getTimestamp());
    }

    public function nights($minDate, $maxDate)
    {
        return $this->timer->nights($minDate, $maxDate);
    }

    public function user($userId)
    {
        $user = $this->em->getRepository("mycpBundle:user")->find($userId);
        return ($user != null) ? $user->getUserCompleteName() : "Usuario no registrado o eliminado";
    }

}
