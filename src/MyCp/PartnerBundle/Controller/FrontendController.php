<?php

namespace MyCp\PartnerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

class FrontendController extends Controller
{

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(){
        return $this->render('PartnerBundle:Frontend:index.html.twig',array(
            'remoteLogin'=>true
        ));
    }

    /**
     * @param $route
     * @param null $routeParams
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function navBarAction($route, $routeParams = null){
        $routeParams = empty($routeParams) ? array() : $routeParams;

        $em = $this->getDoctrine()->getManager();
        $user_ids = $em->getRepository('mycpBundle:user')->getIds($this);

        $countItems = $em->getRepository('mycpBundle:favorite')->getTotal($user_ids['user_id'],$user_ids['session_id']);
        $response = $this->render('PartnerBundle:Layout:language-currency.html.twig', array(
            'route' => $route,
            'routeParams' => $routeParams,
            'count_fav'=>$countItems
        ));

        return $response;
    }

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function contentLoginAction(Request $request)
    {
        return $this->render('LayoutBundle:Security:login-content.html.twig', array());
    }

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function contentRegisterAction(Request $request)
    {
       // $form = $this->container->get('fos_user.registration.form');
        return $this->render('LayoutBundle:Security:register-content.html.twig', array());
    }
}
