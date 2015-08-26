<?php

namespace MyCp\mycpBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;



class BackendReportController extends Controller
{
    function reportsAction()
    {
        $em = $this->getDoctrine()->getEntityManager();
        $existingCategories = $em->getRepository("mycpBundle:report")->getExistingReportCategories();

        return $this->render('mycpBundle:reports:report.html.twig', array(
            "categories" => $existingCategories
        ));
    }

    function dailyInPlaceClientsAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $date = $request->get("dateParam");
        $errorText = "";
        $content = array();

        if($date == null || $date == "null")
        {
            $errorText = "Seleccione una fecha para generar el reporte";
        }
        else
        {
            $timer = $this->get('time');
            $dateRangeFrom = $timer->add("-30 days",$date, "Y-m-d");
            $dateRangeTo = $timer->add("+30 days",$date, "Y-m-d");

            $timer = $this->container->get("Time");

            $content = $em->getRepository("mycpBundle:report")->rpDailyInPlaceClients($date, $dateRangeFrom, $dateRangeTo, $timer);
        }

        return $this->render('mycpBundle:reports:dailyInPlaceClients.html.twig', array(
            'content' => $content,
            'errorText' => $errorText
        ));
    }

    public function dailyInPlaceClientsExcelAction($date, $report)
    {
        $em = $this->getDoctrine()->getManager();
        if($date != null && $date != "null" && $report != null && $report != "null")
        {
            $timer = $this->get('time');
            $dateRangeFrom = $timer->add("-30 days",$date, "Y-m-d");
            $dateRangeTo = $timer->add("+30 days",$date, "Y-m-d");

            $exporter = $this->get("mycp.service.export_to_excel");
            return $exporter->exportRpDailyInPlaceClients($date, $dateRangeFrom, $dateRangeTo, $report,$timer);
        }
    }

   public function ownershipGeneralStatsAction(Request $request)
   {
       $em = $this->getDoctrine()->getManager();
       $provinceId = $request->get("filter_province");
       $munId = $request->get("filter_municipality");
       $province = null;
       $municipality = null;
        $errorText = "";

        $location='el país';
        if(($provinceId == null || $provinceId == "null")&&($munId == null || $munId == "null")){
            $resp=$em->getRepository('mycpBundle:ownershipStat')->getBulb();
        }
       else if($munId == null || $munId == "null"){
           $province = $em->getRepository('mycpBundle:province')->find($provinceId);
           $resp=$em->getRepository('mycpBundle:ownershipStat')->getBulb(null, $province,null);
           $location=$province->getProvName();
        }
       else{
            $municipality = $em->getRepository('mycpBundle:municipality')->find($munId);
           $province = $municipality->getMunProvId();
            $resp=$em->getRepository('mycpBundle:ownershipStat')->getBulb(null, null, $municipality);
            $location=$municipality->getMunName();

        }

        return $this->render('mycpBundle:reports:ownershipGeneralStats.html.twig', array(
            'content' => $resp,
            'location' => $location,
            'errorText' => $errorText,
            'province' => $province,
            'municipality' => $municipality
        ));
    }

    public function ownershipGeneralStatsLinkAction(Request $request, $nomenclator, $province, $municipality)
    {
        $em = $this->getDoctrine()->getManager();
        $nomenclator = $em->getRepository('mycpBundle:nomenclatorStat')->find($nomenclator);
        $province = $em->getRepository('mycpBundle:province')->find($province);
        $municipality = $em->getRepository('mycpBundle:municipality')->find($municipality);

        $reportListContent = $em->getRepository('mycpBundle:ownershipStat')->getOwnershipReportListContent($nomenclator, $province, $municipality);

        return $this->render('mycpBundle:reports:ownershipReportList.html.twig', array(
            'content' => $reportListContent,
            'nomenclator' => $nomenclator,
            'province' => $province,
            'municipality' => $municipality
        ));
    }

    public function ownershipGeneralStatsExcelAction(Request $request, $report, $province, $municipality)
    {
        $exporter = $this->get("mycp.service.export_to_excel");
        return $exporter->exportOwnershipGeneralStats($request, $report, $province, $municipality);
    }

    public function ownershipVsReservationsStatsAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $provinceId = $request->get("filter_province");
        $munId = $request->get("filter_municipality");
        $desId = $request->get("filter_destination");
        $dateFrom = $request->get("dateRangeFrom");
        $dateTo = $request->get("dateRangeTo");
        $province = null;
        $municipality = null;
        $destination = null;
        $errorText = "";

        $location='el país';
        if(($provinceId == null || $provinceId == "null")&&($munId == null || $munId == "null")){
            $resp=$em->getRepository('mycpBundle:ownershipReservationStat')->getAccommodations(null, null,null, null, $dateFrom, $dateTo);
        }
        else if($desId != null && $desId != "null"){
            $destination = $em->getRepository('mycpBundle:destination')->find($desId);
            $resp=$em->getRepository('mycpBundle:ownershipReservationStat')->getAccommodations(null, null,null, $destination, $dateFrom, $dateTo);
            $location=$destination->getDesName();
        }
        else if($munId == null || $munId == "null"){
            $province = $em->getRepository('mycpBundle:province')->find($provinceId);
            $resp=$em->getRepository('mycpBundle:ownershipReservationStat')->getAccommodations(null, $province,null, null, $dateFrom, $dateTo);
            $location=$province->getProvName();
        }
        else{
            $municipality = $em->getRepository('mycpBundle:municipality')->find($munId);
            $province = $municipality->getMunProvId();
            $resp=$em->getRepository('mycpBundle:ownershipReservationStat')->getAccommodations(null, null, $municipality, null, $dateFrom, $dateTo);
            $location=$municipality->getMunName();

        }

        return $this->render('mycpBundle:reports:ownershipReservationStats.html.twig', array(
            'content' => $resp,
            'location' => $location,
            'errorText' => $errorText,
            'province' => $province,
            'municipality' => $municipality,
            'destination' => $destination,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo
        ));
    }

    public function ownershipVsReservationsStatsTotalAction($filter_province, $filter_municipality, $filter_destination, $dateFrom, $dateTo, Request $request)
{
    $em = $this->getDoctrine()->getManager();
    $province = null;
    $municipality = null;
    $destination = null;
    $errorText = "";

    $location='el país';
    if(($filter_province == null || $filter_province == "null")&&($filter_municipality == null || $filter_municipality == "null")){
        $resp=$em->getRepository('mycpBundle:ownershipReservationStat')->getBulb(null, null,null, null, $dateFrom, $dateTo);
    }
    else if($filter_destination != null && $filter_destination != "null"){
        $destination = $em->getRepository('mycpBundle:destination')->find($filter_destination);
        $resp=$em->getRepository('mycpBundle:ownershipReservationStat')->getBulb(null, null,null, $destination, $dateFrom, $dateTo);
        $location=$destination->getDesName();
    }
    else if($filter_municipality == null || $filter_municipality == "null"){
        $province = $em->getRepository('mycpBundle:province')->find($filter_province);
        $resp=$em->getRepository('mycpBundle:ownershipReservationStat')->getBulb(null, $province,null, null, $dateFrom, $dateTo);
        $location=$province->getProvName();
    }
    else{
        $municipality = $em->getRepository('mycpBundle:municipality')->find($filter_municipality);
        $province = $municipality->getMunProvId();
        $resp=$em->getRepository('mycpBundle:ownershipReservationStat')->getBulb(null, null, $municipality, null, $dateFrom, $dateTo);
        $location=$municipality->getMunName();

    }

    $content = $this->renderView('mycpBundle:reports:ownershipReservationStatsTotal.html.twig', array(
        'content' => $resp,
        'location' => $location,
        'errorText' => $errorText,
        'province' => $province,
        'municipality' => $municipality,
        'destination' => $destination,
    ));

    return new Response($content, 200);
}

    public function ownershipVsReservationsStatsAccommodationAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $filter_province = $request->get("filter_province");
        $filter_municipality = $request->get("filter_municipality");
        $filter_destination = $request->get("filter_destination");
        $dateFrom = $request->get("dateFrom");
        $dateTo = $request->get("dateTo");
        $ownership = $request->get("ownership");
        $province = null;
        $municipality = null;
        $destination = null;
        $errorText = "";

        $location='el país';
        if(($filter_province == null || $filter_province == "null")&&($filter_municipality == null || $filter_municipality == "null")){
            $resp=$em->getRepository('mycpBundle:ownershipReservationStat')->getBulb(null, null,null, null, $dateFrom, $dateTo, $ownership);
        }
        else if($filter_destination != null && $filter_destination != "null"){
            $destination = $em->getRepository('mycpBundle:destination')->find($filter_destination);
            $resp=$em->getRepository('mycpBundle:ownershipReservationStat')->getBulb(null, null,null, $destination, $dateFrom, $dateTo, $ownership);
            $location=$destination->getDesName();
        }
        else if($filter_municipality == null || $filter_municipality == "null"){
            $province = $em->getRepository('mycpBundle:province')->find($filter_province);
            $resp=$em->getRepository('mycpBundle:ownershipReservationStat')->getBulb(null, $province,null, null, $dateFrom, $dateTo, $ownership);
            $location=$province->getProvName();
        }
        else{
            $municipality = $em->getRepository('mycpBundle:municipality')->find($filter_municipality);
            $province = $municipality->getMunProvId();
            $resp=$em->getRepository('mycpBundle:ownershipReservationStat')->getBulb(null, null, $municipality, null, $dateFrom, $dateTo, $ownership);
            $location=$municipality->getMunName();

        }

        $content = $this->renderView('mycpBundle:reports:ownershipReservationStatsTotal.html.twig', array(
            'content' => $resp,
            'location' => $location,
            'errorText' => $errorText,
            'province' => $province,
            'municipality' => $municipality,
            'destination' => $destination,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo
        ));

        return new Response($content, 200);
    }

    public function ownershipVsReservationsStatsExcelAction($report, $from_date, $to_date, $location_full)
    {
       //to do
    }

    public function getByCategoryCallbackAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $category = $request->get("category");
        $reports = $em->getRepository('mycpBundle:report')->findBy(array('report_category'=>$category, "published"=> true));
        $reportContent =  $this->renderView('mycpBundle:utils:listReports.html.twig', array('reports' => $reports));
        return new Response($reportContent, 200);
    }

    public function getParametersCallbackAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $reportId = $request->get("report");
        $report = $em->getRepository("mycpBundle:report")->find($reportId);
        $parameters = $em->getRepository('mycpBundle:reportParameter')->findBy(array('parameter_report'=>$reportId), array("parameter_order"=>"ASC"));
        $content =  $this->renderView('mycpBundle:reports:parameters.html.twig', array('report' => $report, 'parameters' => $parameters));
        return new Response($content, 200);
    }

    public function ownershipGeneralListExcelAction($nomenclator, $municipality, $province)
    {
        $exporter = $this->get("mycp.service.export_to_excel");
        return $exporter->exportOwnershipGeneralList($nomenclator, $province, $municipality);
    }
}
