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
    }

}

?>
