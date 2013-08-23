<?php
namespace MyCp\mycpBundle\Helpers;
use MyCp\mycpBundle\Entity\log;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\HttpFoundation\Request;


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

    public function save_log($description,$id_module)
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
                        $module_number=1;
                        break;
                    case 'faqs':
                        $module_number=2;
                        break;
                    case 'album':
                        $module_number=3;
                        break;
                    case 'ownership':
                        $module_number=4;
                        break;
                    case 'currency':
                        $module_number=5;
                        break;
                    case 'language':
                        $module_number=6;
                        break;
                    case 'reservation':
                        $module_number=7;
                        break;
                    case 'user':
                        $module_number=8;
                        break;
                    case 'general information':
                        $module_number=9;
                        break;
                    case 'comment':
                        $module_number=10;
                        break;
                }
            }
            else
                $module_number=0;
        }
        else{
            $module_number=0;
        }

        $this->save_log('Login',$module_number);

    }

}