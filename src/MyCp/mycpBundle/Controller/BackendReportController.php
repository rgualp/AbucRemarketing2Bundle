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
            return $exporter->exportRpDailyInPlaceClients($date, $dateRangeFrom, $dateRangeTo, $report);
        }
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
