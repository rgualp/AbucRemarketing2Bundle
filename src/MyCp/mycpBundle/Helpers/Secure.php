<?php

namespace MyCp\mycpBundle\Helpers;

use MyCp\mycpBundle\Entity\user;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

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

    public function onKernelResponse(FilterResponseEvent $event)
    {
        $token = $this->security_context->getToken();
        if ($token != null && $token->getUser() != 'anon.') {
            $user = $this->security_context->getToken()->getUser();
            if ($user->getUserEnabled() != 1 || $this->security_context->isGranted('ROLE_CLIENT_TOURIST')) {
                $this->security_context->setToken(null);
                $this->container->get('request')->getSession()->invalidate();
                $login_route = $this->container->get('router')->generate('backend_login');
                $this->container->get('session')->getFlashBag()->add('message_error_local', 'NOT_ENABLED_USER');
                $event->setResponse(new RedirectResponse($login_route));
            } else {
                if ($this->security_context->isGranted('ROLE_CLIENT_CASA')) {
                    $route = $this->container->get('router')->generate('mycp_lodging_front');
                    $event->setResponse(new RedirectResponse($route));
                } else {
                    $route = $this->container->get('router')->generate('mycp_backend_front');
                    $event->setResponse(new RedirectResponse($route));
                }
            }
        }
    }

    public function onSecurityInteractiveLogin(InteractiveLoginEvent $event)
    {
        $permissions = array();
        if ($this->security_context->isGranted('IS_AUTHENTICATED_REMEMBERED')) {

            $user = $this->security_context->getToken()->getUser();
            $role_id = $user->getUserSubrole()->getRoleId();
            $role_permissions = $this->em->getRepository('mycpBundle:rolePermission')->findBy(array('rp_role' => $role_id));

            foreach ($role_permissions as $permission) {
                array_push($permissions, $permission->getRpPermission()->getPermRoute());
            }
        }
        $this->container->get('session')->set('permissions', $permissions);
    }

    public function verifyAccess()
    {
        if ($this->container->get('session')->get('permissions')) {
            $route = $this->container->get('request')->attributes->all();

            $route = $route['_route'];
            $access = false;
            foreach ($this->container->get('session')->get('permissions') as $permission) {
                if ($route == $permission) {
                    $access = true;
                    break;
                }
            }

            if ($access == false) {
                $templating = $this->container->get('templating');
                echo $templating->render('mycpBundle:utils:access_denied.html.twig');
                exit();
            }
        } else {
            $templating = $this->container->get('templating');
            echo $templating->render('mycpBundle:utils:access_denied.html.twig');
            exit();
        }
    }

    public function getEncodedUserString(user $user)
    {
        return $this->encodeString($user->getUserEmail() . '///' . $user->getUserId());
    }

    public function encodeString($string)
    {
        $key = $this->container->getParameter('encode.key');
        $result = '';
        for ($i = 0; $i < strlen($string); $i++) {
            $char = substr($string, $i, 1);
            $keychar = substr($key, ($i % strlen($key)) - 1, 1);
            $char = chr(ord($char) + ord($keychar));
            $result .= $char;
        }
        return base64_encode($result);
    }

    public function decodeString($string)
    {
        $key = $this->container->getParameter('encode.key');
        $result = '';
        $string = base64_decode($string);
        for ($i = 0; $i < strlen($string); $i++) {
            $char = substr($string, $i, 1);
            $keychar = substr($key, ($i % strlen($key)) - 1, 1);
            $char = chr(ord($char) - ord($keychar));
            $result .= $char;
        }
        return $result;
    }

}
