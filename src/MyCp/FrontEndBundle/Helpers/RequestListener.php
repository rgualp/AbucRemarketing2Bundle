<?php
/**
 * Description of Utils
 *
 * @author Luis
 */
namespace MyCp\frontEndBundle\Helpers;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Routing\Exception\RouteNotFoundException;


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
            if($lang)
            {
                $this->container->get('session')->set('app_lang_name',$lang->getLangName());
                $this->container->get('session')->set('app_lang_code',$lang->getLangCode());
            }


        }

        if(isset($attr['_route']) && $attr['_route']!='frontend_payment_skrill_status' && $attr['_route']!='mycp_sitemap' && $attr['_route']!='_wdt' && $attr['_route']!='_internal' && !strpos($attr['_route'], '_callback'))
        {
            $this->container->get('session')->set('app_last_route',$attr['_route']);
            $this->container->get('session')->set('app_last_route_params',$attr['_route_params']);

            if($attr['_route']=='mycp_main')
                $this->container->get('session')->set('browser_lang',null);

            if(isset($_SERVER["HTTP_ACCEPT_LANGUAGE"])) {
                $lang = substr($_SERVER["HTTP_ACCEPT_LANGUAGE"], 0, 2);
                $lang = strtolower($lang);
            }

            if($this->container->get('session')->get('browser_lang')== null && $attr['_route']!='mycp_main')
            {

                $lang_db=$this->em->getRepository('mycpBundle:lang')->findOneBy(array('lang_code'=>$lang,'lang_active'=>1));
                if($lang_db)
                {
                    $last_route=$this->container->get('session')->get('app_last_route');
                    $route_array=explode('.',$last_route);
                    $last_route=$route_array[0];
                    $last_route_params=$this->container->get('session')->get('app_last_route_params');
                    $last_route_params['_locale']=$lang;
                    $last_route_params['locale']=$lang;
                    try{
                        $new_route=$this->container->get('router')->generate($last_route,$last_route_params);
                        $this->container->get('session')->set('browser_lang',$lang);
                        $event->setResponse(new RedirectResponse($new_route));
                    }catch (RouteNotFoundException $e){}
                }
            }

        }

        // currency by default (Config in backend)
        //var_dump($this->container->get('session')->get('curr_acronym'));
        if(!$this->container->get('session')->get('curr_acronym'))
        {
            $curr_by_default=$this->em->getRepository('mycpBundle:currency')->findOneBy(array('curr_default'=>1));
            
            if(isset($curr_by_default))
            {
                $this->container->get('session')->set("curr_rate", $curr_by_default->getCurrCucChange());
                $this->container->get('session')->set("curr_symbol",  $curr_by_default->getCurrSymbol());
                $this->container->get('session')->set("curr_acronym", $curr_by_default->getCurrCode());
            }
        }


    }

}

?>
