<?php

namespace MyCp\mycpBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints\NotBlank;

class BackendRankingController extends Controller {

    function listAction(Request $request)
    {
        /*$service_security = $this->get('Secure');
        $service_security->verifyAccess();*/
        $em = $this->getDoctrine()->getManager();

        $dates = $em->getRepository("mycpBundle:ownershipRankingExtra")->getLastDates();
        $startDate = $dates["startDate"];
        $endDate = $dates["endDate"];


        $filter_code = $request->get("filter_code");
        $filter_name = $request->get("filter_name");
        $filter_destination = $request->get("filter_destination");
        $filter_modality = $request->get("filter_modality");
        $filter_month = $request->get("filter_month");
        $filter_year = $request->get("filter_year");
        $filter_point_option = $request->get("filter_point_option");
        $filter_point_from = $request->get("filter_point_from");
        $filter_point_to = $request->get("filter_point_to");

        if ($filter_month == null || $filter_month == "null" || $filter_month == "" || $filter_year == null || $filter_year == "null" || $filter_year == ""){
            $filter_selected_date = $startDate;
        }
        else{
            $filter_selected_date = \DateTime::createFromFormat("Y-m-d", $filter_year."-".$filter_month.'-'.'01');
        }

        $filter_code = ($filter_code == "null") ? "" : $filter_code;
        $filter_name = ($filter_name == "null") ? "" : $filter_name;

        $items = $em->getRepository("mycpBundle:ownershipRankingExtra")->getList($filter_selected_date->format("Y-m-d"), $filter_code, $filter_name,
            $filter_destination, $filter_modality, $filter_point_option, $filter_point_from, $filter_point_to);


        return $this->render('mycpBundle:ranking:list.html.twig', array(
            'list' => $items,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'filter_code' => $filter_code,
            'filter_name' => $filter_name,
            'filter_destination' => $filter_destination,
            'filter_modality' => $filter_modality,
            'filter_selected_date' => $filter_selected_date,
            'filter_point_option' => $filter_point_option,
            'filter_point_from' => $filter_point_from,
            'filter_point_to' => $filter_point_to
        ));
    }

}
