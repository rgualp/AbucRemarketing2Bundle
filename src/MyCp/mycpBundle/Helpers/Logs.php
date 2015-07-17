<?php
namespace MyCp\mycpBundle\Helpers;
use MyCp\mycpBundle\Entity\log;
use MyCp\mycpBundle\Entity\offerLog;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\HttpFoundation\Request;
use MyCp\mycpBundle\Helpers\BackendModuleName;


class Logs
{
    private $em;
    private $container;
    private $security_context;

    public function __construct($entity_manager, $container, $security_context)
    {
        $this->em = $entity_manager;
        $this->container = $container;
        $this->security_context = $security_context;

    }

    public function saveLog($description,$id_module)
    {
        $user = $this->security_context->getToken()->getUser();
        $log= new log();
        $log->setLogUser($user);
        $log->setLogDescription($description);
        $log->setLogDate(new \DateTime(date('Y-m-d')));
        $log->setLogModule($id_module);
        $log->setLogTime(strftime("%I:%M %p"));
        $this->em->persist($log);
        $this->em->flush();
    }

    public function saveNewOfferLog($newReservation, $fromReservation, $isForChangeDates)
    {
        $log = new offerLog();
        $log->setLogDate(new \DateTime());
        $log->setLogFromReservation($fromReservation);
        $log->setLogOfferReservation($newReservation);

        $reason = null;
        if($isForChangeDates)
            $reason = $this->em->getRepository("mycpBundle:nomenclatorStat")->findOneBy(array("nom_name" => "Modificación de fechas"));
        else
            $reason = $this->em->getRepository("mycpBundle:nomenclatorStat")->findOneBy(array("nom_name" => "Ofrecer nuevo alojamiento"));

        $log->setLogReason($reason);
        $this->em->persist($log);
        $this->em->flush();
    }

    public function onSecurityInteractiveLogin(InteractiveLoginEvent $event)
    {

        $session=$event->getRequest()->getSession()->all();
        if(isset($session['_security.secured_area.target_path']))
        {
            $string_url=explode('backend/',$session['_security.secured_area.target_path']);
            if(isset($string_url[1]))
            {
                $module=explode('/',$string_url[1]);
                $module=$module[0];
                $module_number='';

                switch($module){
                    case 'destination':
                        $module_number= BackendModuleName::MODULE_DESTINATION;
                        break;
                    case 'faqs':
                        $module_number=BackendModuleName::MODULE_FAQS;
                        break;
                    case 'album':
                        $module_number=BackendModuleName::MODULE_ALBUM;
                        break;
                    case 'ownership':
                        $module_number=BackendModuleName::MODULE_OWNERSHIP;
                        break;
                    case 'currency':
                        $module_number=BackendModuleName::MODULE_CURRENCY;
                        break;
                    case 'language':
                        $module_number=BackendModuleName::MODULE_LANGUAGE;
                        break;
                    case 'reservation':
                        $module_number=BackendModuleName::MODULE_RESERVATION;
                        break;
                    case 'user':
                        $module_number=BackendModuleName::MODULE_USER;
                        break;
                    case 'general information':
                        $module_number=BackendModuleName::MODULE_GENERAL_INFORMATION;
                        break;
                    case 'comment':
                        $module_number=BackendModuleName::MODULE_COMMENT;
                        break;
                    case 'unavailability details':
                        $module_number=BackendModuleName::MODULE_UNAVAILABILITY_DETAILS;
                        break;
                    case 'municipality':
                        $module_number=BackendModuleName::MODULE_MUNICIPALITY;
                        break;
                    case 'season':
                        $module_number=BackendModuleName::MODULE_SEASON;
                        break;
                    case 'mailList':
                        $module_number=BackendModuleName::MODULE_MAIL_LIST;
                        break;
                    case 'batch process':
                        $module_number=BackendModuleName::MODULE_BATCH_PROCESS;
                        break;
                    case 'client messages':
                        $module_number=BackendModuleName::MODULE_CLIENT_MESSAGES;
                        break;
                    case 'client comments':
                        $module_number=BackendModuleName::MODULE_CLIENT_COMMENTS;
                        break;
                }
            }
            else
                $module_number=BackendModuleName::NO_MODULE;
        }
        else{
            $module_number=BackendModuleName::NO_MODULE;
        }

        $this->saveLog('Login',$module_number);

    }

}