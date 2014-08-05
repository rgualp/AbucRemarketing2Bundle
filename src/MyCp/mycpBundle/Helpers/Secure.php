<?php
namespace MyCp\mycpBundle\Helpers;
use MyCp\mycpBundle\Entity\log;
use MyCp\mycpBundle\Entity\user;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\HttpFoundation\Request;


class Secure
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

    public function onSecurityInteractiveLogin(InteractiveLoginEvent $event)
    {
        $user = $this->security_context->getToken()->getUser();
        $role_id=$user->getUserSubrole()->getRoleId();
        $role_permissions=$this->em->getRepository('mycpBundle:rolePermission')->findBy(array('rp_role'=>$role_id));

        $permissions=array();
        foreach($role_permissions as $permission)
        {
            array_push($permissions,$permission->getRpPermission()->getPermRoute());

        }
        $this->container->get('session')->set('permissions',$permissions);
    }

    public function verify_access()
    {
        if($this->container->get('session')->get('permissions'))
        {
            $route=$this->container->get('request')->attributes->all();

            $route=$route['_route'];
            $access=false;
            foreach($this->container->get('session')->get('permissions') as $permission)
            {
                if($route==$permission)
                {
                    $access=true;
                    break;
                }
            }

            if($access==false)
            {
                $templating=$this->container->get('templating');
                echo $templating->render('mycpBundle:utils:access_denied.html.twig');
                exit();
            }

        }
    }

    public function getEncodedUserString(user $user)
    {
        return $this->encode_string($user->getUserEmail() . '///' . $user->getUserId());
    }

    public function encode_string($string)
    {
        $key=$this->container->getParameter('encode.key');
        $result = '';
        for($i=0; $i<strlen($string); $i++) {
            $char = substr($string, $i, 1);
            $keychar = substr($key, ($i % strlen($key))-1, 1);
            $char = chr(ord($char)+ord($keychar));
            $result.=$char;
        }
        return base64_encode($result);
    }

    public function decode_string($string)
    {
        $key=$this->container->getParameter('encode.key');
        $result = '';
        $string = base64_decode($string);
        for($i=0; $i<strlen($string); $i++) {
            $char = substr($string, $i, 1);
            $keychar = substr($key, ($i % strlen($key))-1, 1);
            $char = chr(ord($char)-ord($keychar));
            $result.=$char;
        }
        return $result;
    }

}
