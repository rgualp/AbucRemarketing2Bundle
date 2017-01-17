<?php

namespace MyCp\mycpBundle\Controller;

use Abuc\RemarketingBundle\Event\JobEvent;
use MyCp\mycpBundle\Entity\booking;
use MyCp\mycpBundle\Entity\payment;
use MyCp\mycpBundle\Form\cancelPaymentType;
use MyCp\mycpBundle\Helpers\DataBaseTables;
use MyCp\mycpBundle\Helpers\Operations;
use MyCp\mycpBundle\Helpers\OwnershipStatuses;
use MyCp\mycpBundle\Helpers\Reservation;
use MyCp\mycpBundle\JobData\GeneralReservationJobData;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use MyCp\mycpBundle\Entity\ownershipReservation;
use MyCp\mycpBundle\Entity\generalReservation;
use MyCp\mycpBundle\Entity\log;
use Symfony\Component\Validator\Constraints\Date;
use Symfony\Component\Validator\Constraints\NotBlank;
use MyCp\mycpBundle\Form\reservationType;
use MyCp\mycpBundle\Helpers\BackendModuleName;
use MyCp\mycpBundle\Helpers\VoucherHelper;
use Symfony\Component\HttpFoundation\JsonResponse;
use MyCp\mycpBundle\Form\emailDestinationType;
use MyCp\mycpBundle\Entity\cancelPayment;


class BackendPendingPayOwnController extends Controller {
    /**
     * @param $items_per_page
     * @param Request $request
     * @return Response
     */

    function listAction($items_per_page, Request $request) {
        $service_security = $this->get('Secure');
        $service_security->verifyAccess();
        $em = $this->getDoctrine()->getManager();

        $paginator = $this->get('ideup.simple_paginator');
        $paginator->setItemsPerPage($items_per_page);
        $payments = $paginator->paginate($em->getRepository('mycpBundle:pendingPayown')->findAllByFilters())->getResult();
        $page = 1;
        if (isset($_GET['page']))
            $page = $_GET['page'];
        return $this->render('mycpBundle:pendingOwn:list.html.twig', array(
                'list' => $payments,
                'items_per_page' => $items_per_page,
                'total_items' => $paginator->getTotalItems(),
                'current_page' => $page
            ));
    }
}