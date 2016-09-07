<?php

namespace MyCp\PartnerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use MyCp\PartnerBundle\Form\FilterOwnershipType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class BackendController extends Controller
{
    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
        $results = $em->getRepository('mycpBundle:ownership')->getSearchNumbers();

        $categories_own_list = $results["categories"];//$em->getRepository('mycpBundle:ownership')->getOwnsCategories();
        $types_own_list = $results["types"];//$em->getRepository('mycpBundle:ownership')->getOwnsTypes();
        $prices_own_list = $results["prices"];//$em->getRepository('mycpBundle:ownership')->getOwnsPrices();
        $statistics_own_list = $em->getRepository('mycpBundle:ownership')->getSearchStatistics();

        $formFilterOwnerShip = $this->createForm(new FilterOwnershipType($this->get('translator'), array()));
        return $this->render('PartnerBundle:Backend:index.html.twig', array(
            "locale" => "es",
            "owns_categories" => null,
            "autocomplete_text_list" => null,
            "owns_prices" => $prices_own_list,
            "formFilterOwnerShip"=>$formFilterOwnerShip->createView()
        ));
    }

    /**
     * @param Request $request
     */
    public function searchAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $paginator = $this->get('ideup.simple_paginator');
        $items_per_page = 8;
        $paginator->setItemsPerPage($items_per_page);
        $filters= $request->get('requests_ownership_filter');
        $start=$request->request->get('start');
        $limit=$request->request->get('limit');
        $list =$em->getRepository('mycpBundle:ownership')->searchOwnership($this,$filters,$start,$limit);
        $response = $this->renderView('PartnerBundle:Search:result.html.twig', array(
            'list' => $list
        ));

        return new Response($response, 200);
    }
}
