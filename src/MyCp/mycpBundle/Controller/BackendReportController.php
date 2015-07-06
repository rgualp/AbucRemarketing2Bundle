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
      //to do
    }

    public function ownershipVsReservationsStatsExcelAction($location_full, $report)
    {
       //to do
    }

    public function getByCategoryCallbackAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $category = $request->get("category");
        $reports = $em->getRepository('mycpBundle:report')->findBy(array('report_category'=>$category));
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
}
