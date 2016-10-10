<?php

namespace MyCp\MobileFrontendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class SearchController extends Controller
{
    public function orangeSearchBarAction() {
        $em = $this->getDoctrine()->getManager();
        $session = $this->getRequest()->getSession();

        return $this->render('MyCpMobileFrontendBundle:search:orangeSearchBar.html.twig', array(
            'locale' => $this->get('translator')->getLocale(),
            'autocomplete_text_list' => $em->getRepository('mycpBundle:ownership')->autocompleteTextList(),
            'arrival_date' => $session->get("search_arrival_date"),
            'departure_date' => $session->get("search_departure_date")
        ));
    }
}
