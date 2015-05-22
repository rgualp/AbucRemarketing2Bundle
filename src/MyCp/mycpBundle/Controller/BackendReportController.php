<?php

namespace MyCp\mycpBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;



class BackendReportController extends Controller
{
    function reportsAction()
    {
        return $this->render('mycpBundle:reports:layout.html.twig', array(
        ));
    }

    function dailyInPlaceClientsAction($date)
    {
        return $this->render('mycpBundle:reports:layout.html.twig', array(
        ));
    }

    public function dailyInPlaceClientsExcelAction($date)
    {
        throw \Exception("Under construction");
    }
}
