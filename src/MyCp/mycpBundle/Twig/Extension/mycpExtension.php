<?php

namespace MyCp\mycpBundle\Twig\Extension;
use MyCp\mycpBundle\Helpers\BackendModuleName;

class mycpExtension extends \Twig_Extension {

    /*private $session;
    private $entity_manager;*/

    public function __construct(/*$session, $entity_manager*/) {
       /* $this->session = $session;
        $this->entity_manager = $entity_manager;*/
    }

    public function getName() {
        return "utils";
    }

    public function getFilters() {
        return array(
            new \Twig_SimpleFilter('moduleName', array($this, 'moduleName')),
        );
    }

    public function getFunctions() {
        return array(
        );
    }

    public function moduleName($module_number)
    {
        switch($module_number)
        {
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
            default: return "MyCP";
        }
    }
}
