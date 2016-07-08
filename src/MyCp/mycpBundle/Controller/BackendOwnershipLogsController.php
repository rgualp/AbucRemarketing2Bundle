<?php

namespace MyCp\mycpBundle\Controller;

use MyCp\mycpBundle\Entity\batchType;
use MyCp\mycpBundle\Entity\ownershipReservation;
use MyCp\mycpBundle\Entity\room;
use MyCp\mycpBundle\Helpers\DataBaseTables;
use MyCp\mycpBundle\Helpers\FileIO;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Email;
use MyCp\mycpBundle\Entity\ownership;
use MyCp\mycpBundle\Entity\log;
use MyCp\mycpBundle\Entity\photo;
use MyCp\mycpBundle\Entity\user;
use MyCp\mycpBundle\Entity\photoLang;
use MyCp\mycpBundle\Entity\ownershipPhoto;
use MyCp\mycpBundle\Helpers\OwnershipStatuses;
use MyCp\mycpBundle\Entity\ownershipStatus;
use MyCp\mycpBundle\Helpers\BackendModuleName;
use MyCp\mycpBundle\Helpers\UserMails;
use MyCp\mycpBundle\Helpers\Operations;
use Symfony\Component\Validator\Constraints\RegexValidator;

class BackendOwnershipLogsController extends Controller {

    public function logsAction($items_per_page, Request $request) {
        /*$service_security = $this->get('Secure');
        $service_security->verifyAccess();*/
        $page = 1;
        $filter_user = $request->get('filter_user');
        $filter_status = $request->get('filter_status');
        $filter_date = $request->get('filter_date');
        $filter_created = $request->get('filter_created');
        $filter_description = $request->get('filter_description');
        if ($request->getMethod() == 'POST' && $filter_user == 'null' && $filter_status == 'null' && $filter_date == 'null' && $filter_created == 'null' && $filter_description == 'null') {
            $message = 'Debe llenar al menos un campo para filtrar.';
            $this->get('session')->getFlashBag()->add('message_error_local', $message);
            return $this->redirect($this->generateUrl('mycp_list_ownerships'));
        }

        if ($filter_user == 'null')
            $filter_user = '';
        if ($filter_status == 'null')
            $filter_status = '';
        if ($filter_date == 'null')
            $filter_date = '';
        if ($filter_created == 'null')
            $filter_created = '';
        if ($filter_description == 'null')
            $filter_description = '';
        if (isset($_GET['page']))
            $page = $_GET['page'];

        $em = $this->getDoctrine()->getManager();
        $paginator = $this->get('ideup.simple_paginator');
        $paginator->setItemsPerPage($items_per_page);
        $ownerships = $paginator->paginate($em->getRepository('mycpBundle:ownership')->logs($filter_user, $filter_status, $filter_date, $filter_created, $filter_description))->getResult();

        $service_log = $this->get('log');
        $service_log->saveLog('Visit', BackendModuleName::MODULE_OWNERSHIP);

        return $this->render('mycpBundle:ownership:logs.html.twig', array(
            'logs' => $ownerships,
            'items_per_page' => $items_per_page,
            'current_page' => $page,
            'total_items' => $paginator->getTotalItems(),
            'filter_user' => $filter_user,
            'filter_status' => $filter_status,
            'filter_date' => $filter_date,
            'filter_created' => $filter_created,
            'filter_description' => $filter_description
        ));
    }

}
