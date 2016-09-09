<?php

namespace MyCp\PartnerBundle\Controller;

use MyCp\PartnerBundle\Form\paReservationType;
use MyCp\PartnerBundle\Form\paTravelAgencyType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use MyCp\PartnerBundle\Form\FilterOwnershipType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use MyCp\PartnerBundle\Entity\paTravelAgency;

class BackendController extends Controller
{
    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
        $results = $em->getRepository('mycpBundle:ownership')->getSearchNumbers();

        $categories_own_list = $results["categories"];
        $types_own_list = $results["types"];
        $prices_own_list = $results["prices"];
        $statistics_own_list = $em->getRepository('mycpBundle:ownership')->getSearchStatistics();


        $form = $this->createForm(new paReservationType($this->get('translator')));
        $formFilterOwnerShip = $this->createForm(new FilterOwnershipType($this->get('translator'), array()));
        return $this->render('PartnerBundle:Backend:index.html.twig', array(
            "locale" => "es",
            "owns_categories" => null,
            "autocomplete_text_list" => null,
            "owns_prices" => $prices_own_list,
            "formFilterOwnerShip"=>$formFilterOwnerShip->createView(),
            'form'=>$form->createView()
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
        return new JsonResponse(array('response_twig'=>$response,'response_json'=>$list));
    }

    public function openReservationsListAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $paginator = $this->get('ideup.simple_paginator');
        $items_per_page = 4;
        $paginator->setItemsPerPage($items_per_page);
        $user = $this->getUser();
        $tourOperator = $em->getRepository("PartnerBundle:paTourOperator")->findOneBy(array("tourOperator" => $user->getUserId()));
        $list = $paginator->paginate($em->getRepository('PartnerBundle:paReservation')->getOpenReservationsList($tourOperator->getTravelAgency()))->getResult();
        $page = 1;


        $response = $this->renderView('PartnerBundle:Modal:open-reservations-list.html.twig', array(
            'list' => $list,
            'items_per_page' => $items_per_page,
            'total_items' => $paginator->getTotalItems(),
            'current_page' => $page,
            'show_paginator' => true
        ));

        return new Response($response, 200);
    }

    /**
     * @return JsonResponse
     */
    public function profileAgencyAction(){
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $tourOperator = $em->getRepository("PartnerBundle:paTourOperator")->findOneBy(array("tourOperator" => $user->getUserId()));
        $form = $this->createForm(new paTravelAgencyType($this->get('translator')),$tourOperator->getTravelAgency());
        return new JsonResponse([
            'success' => true,
            'id' => 'id_dashboard_profile_agency',
            'html' => $this->renderView('PartnerBundle:Dashboard:profile_agency.html.twig', array( 'form'=>$form->createView())),

          ]);
    }
}
