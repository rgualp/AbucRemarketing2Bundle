<?php

namespace MyCp\MobileFrontendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class MobileController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('MyCpMobileFrontendBundle:Default:index.html.twig', array('name' => $name));
    }

    public function topNavAction($route, $routeParams = null)
    {
        $routeParams = empty($routeParams) ? array() : $routeParams;

        $em = $this->getDoctrine()->getManager();
        $user_ids = $em->getRepository('mycpBundle:user')->getIds($this);

        $countItems = $em->getRepository('mycpBundle:favorite')->getTotal($user_ids['user_id'],$user_ids['session_id']);
        $response = $this->render('@MyCpMobileFrontend/menus/topnav.html.twig', array(
            'route' => $route,
            'routeParams' => $routeParams,
            'count_fav'=>$countItems
        ));

        return $response;
    }

    public function langCurrAction($route, $routeParams = null)
    {
        $routeParams = empty($routeParams) ? array() : $routeParams;

        $em = $this->getDoctrine()->getManager();
        $user_ids = $em->getRepository('mycpBundle:user')->getIds($this);

        $countItems = $em->getRepository('mycpBundle:favorite')->getTotal($user_ids['user_id'],$user_ids['session_id']);
        $response = $this->render('@MyCpMobileFrontend/menus/language_currency.html.twig', array(
            'route' => $route,
            'routeParams' => $routeParams,
            'count_fav'=>$countItems
        ));

        return $response;
    }

    public function homeCarrouselAction() {
        $em = $this->getDoctrine()->getManager();
        $user_ids = $em->getRepository('mycpBundle:user')->getIds($this);

        $popular_destinations_list = $em->getRepository('mycpBundle:destination')->getPopularDestinations(12, $user_ids['user_id'], $user_ids['session_id']);

        return $this->render('@MyCpMobileFrontend/destination/homeCarrousel.html.twig', array(
            'popular_places' => $popular_destinations_list
        ));
    }
}
