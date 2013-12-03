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
        }
        if(isset($attr['_route']) && $attr['_route']!='frontend_change_language' && $attr['_route']!='_wdt' && $attr['_route']!='_internal')
        {
            $this->container->get('session')->set('app_last_route',$attr['_route']);
            $this->container->get('session')->set('app_last_route_params',$attr['_route_params']);
        }
        //var_dump($this->container->get('session')->get('app_last_route'));


       /* $lang_name=$this->container->get('Request')->getSession()->get('app_lang_name');
        $user = $this->container->get('security.context')->getToken()->getUser();
        if($user!='anon.')
        {
            //var_dump($user);
        }*/
    }

}

?>
