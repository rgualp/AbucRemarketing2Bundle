<?php

namespace MyCp\PartnerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use MyCp\PartnerBundle\Form\FilterOwnershipType;

class BackendController extends Controller
{
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
}
