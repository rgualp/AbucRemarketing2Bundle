<?php
/**
 * Description of Utils
 *
 * @author Luis
 */
namespace MyCp\frontEndBundle\Helpers;

use Symfony\Component\HttpKernel\HttpKernelInterface;


class RequestListener {

    private $em;
    private $container;

    public function __construct($entity_manager, $container) {
        $this->em = $entity_manager;
        $this->container = $container;
    }

    public function onKernelRequest($event)
    {
        $attr=$this->container->get('request')->attributes->all();
        if(isset($attr['_locale']))
        {
            $lang=$this->em->getRepository('mycpBundle:lang')->findOneBy(array('lang_code'=>$attr['_locale']));
            $this->container->get('session')->set('app_lang_name',$lang->getLangName());
            $this->container->get('session')->set('app_lang_code',$lang->getLangCode());
        }
        if(isset($attr['_route']) && $attr['_route']!='_wdt' && $attr['_route']!='_internal' && !strpos($attr['_route'], '_callback'))
        {
            $this->container->get('session')->set('app_last_route',$attr['_route']);
            $this->container->get('session')->set('app_last_route_params',$attr['_route_params']);

            $lang = isset($lang) ? $lang : 'es';

            if(isset($_SERVER["HTTP_ACCEPT_LANGUAGE"])) {
                $lang =substr($_SERVER["HTTP_ACCEPT_LANGUAGE"],0,2);
                $lang=strtolower($lang);
            }

            if($this->container->get('session')->get('browser_lang')!= null && $this->container->get('session')->get('browser_lang')!=strtolower($lang))
            {
                $lang_db=$this->em->getRepository('mycpBundle:lang')->findOneBy(array('lang_code'=>$lang,'lang_active'=>1));
                if(!$lang_db)
                {
                    $langs_db=$this->em->getRepository('mycpBundle:lang')->findBy(array('lang_active'=>1));
                    $lang= strtolower($langs_db[0]->getLangCode());
                }
                if($lang!=$this->container->get('session')->get('browser_lang'))
                {
                    $this->container->get('session')->set('browser_lang',$lang);
                    $last_route=$this->container->get('session')->get('app_last_route');
                    $last_route_params=$this->container->get('session')->get('app_last_route_params');
                    $last_route_params['_locale']=$lang;
                    $last_route_params['locale']=$lang;
                    $new_route=$this->container->get('router')->generate($last_route,$last_route_params);
                    $event->setResponse(new RedirectResponse($new_route));
                }


            }
        }


    }

}

?>
