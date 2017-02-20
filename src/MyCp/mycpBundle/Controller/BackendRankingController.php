<?php

namespace MyCp\mycpBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints\NotBlank;

class BackendRankingController extends Controller {

    function listAction($startDate, $endDate, Request $request) {
        /*$service_security = $this->get('Secure');
        $service_security->verifyAccess();*/
        $em = $this->getDoctrine()->getManager();

        if($startDate == "" && $endDate == "")
        {
            $dates = $em->getRepository("mycpBundle:ownershipRankingExtra")->getLastDates();
            $startDate = $dates["startDate"];
            $endDate = $dates["endDate"];
        }

//        dump($startDate);
//        dump($endDate);
//        die;

        $items = $em->getRepository("mycpBundle:ownershipRankingExtra")->getList($startDate, $endDate);

        return $this->render('mycpBundle:ranking:list.html.twig', array(
            'list' => $items,
            'startDate' => $startDate,
            'endDate' => $endDate
        ));
    }

}
