<?php

namespace MyCp\MobileFrontendBundle\Controller;

use MyCp\mycpBundle\Entity\ownership;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use MyCp\FrontEndBundle\Helpers\Utils;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use MyCp\mycpBundle\Entity\ownershipReservation;
use MyCp\mycpBundle\Entity\season;
use MyCp\mycpBundle\Helpers\OrderByHelper;

class OwnershipController extends Controller {

    public function topRatedCallbackAction() {
        $request = $this->getRequest();
        $session = $request->getSession();
        $em = $this->getDoctrine()->getManager();
        $locale = $this->get('translator')->getLocale();

        $statistics = $em->getRepository("mycpBundle:ownership")->top20Statistics();

        $category = $session->get("top_rated_category");
        $paginator = $this->get('ideup.simple_paginator');
        $items_per_page = 2;
        $paginator->setItemsPerPage($items_per_page);
        $list = $em->getRepository('mycpBundle:ownership')->top20($locale, ((strtolower($category) != "todos") ? $category : null));
        $own_top20_list = $paginator->paginate($list)->getResult();
        $page = 1;
        if ($request->get('page'))
            $page = $request->get('page');

        $response = $this->renderView('@MyCpMobileFrontend/ownership/homeTopRatedList.html.twig', array(
            'own_top20_list' => $own_top20_list,
            'top_rated_items_per_page' => $items_per_page,
            'top_rated_total_items' => $paginator->getTotalItems(),
            'current_page' => $page,
            'premium_total' => $statistics['premium_total'],
            'midrange_total' => $statistics['midrange_total'],
            'economic_total' => $statistics['economic_total']
        ));

        return new Response($response, 200);
    }

}
