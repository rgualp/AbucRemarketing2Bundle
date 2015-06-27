<?php

namespace MyCp\mycpBundle\Controller;

use MyCp\mycpBundle\Helpers\FileIO;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use MyCp\mycpBundle\Entity\albumCategory;
use MyCp\mycpBundle\Entity\albumCategoryLang;
use MyCp\mycpBundle\Entity\album;
use MyCp\mycpBundle\Entity\albumPhoto;
use MyCp\mycpBundle\Entity\photo;
use MyCp\mycpBundle\Entity\photoLang;
use MyCp\mycpBundle\Form\categoryType;
use Symfony\Component\Validator\Constraints\NotBlank;
use MyCp\mycpBundle\Helpers\BackendModuleName;

class BackendRevisionController extends Controller {

    public function listAction($items_per_page, Request $request)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $page = 1;
        $filter_active = $request->get('filter_active');

        $filter_province = $request->get('filter_province');

        $filter_destination = $request->get('filter_destination');
        $filter_name = $request->get('filter_name');
        $filter_municipality = $request->get('filter_municipality');
        $filter_type = $request->get('filter_type');
        $filter_category = $request->get('filter_category');
        $filter_code = $request->get('filter_code');
        $filter_saler = $request->get('filter_saler');
        $filter_visit_date = $request->get('filter_visit_date');
        $filter_other = $request->get('filter_other');
        $filter_commission = $request->get('filter_commission');
        if ($request->getMethod() == 'POST' && $filter_name == 'null' && $filter_active == 'null' && $filter_province == 'null' && $filter_municipality == 'null' &&
            $filter_type == 'null' && $filter_category == 'null' && $filter_code == 'null' && $filter_saler == 'null' && $filter_visit_date == 'null' && $filter_destination == 'null' && $filter_other == 'null' &&
            $filter_commission == 'null'
        ) {
            $message = 'Debe llenar al menos un campo para filtrar.';
            $this->get('session')->getFlashBag()->add('message_error_local', $message);
            return $this->redirect($this->generateUrl('mycp_list_ownerships'));
        }

        if ($filter_code == 'null')
            $filter_code = '';
        if ($filter_name == 'null')
            $filter_name = '';
        if ($filter_saler == 'null')
            $filter_saler = '';
        if ($filter_visit_date == 'null')
            $filter_visit_date = '';
        if ($filter_destination == 'null')
            $filter_destination = '';
        if ($filter_other == 'null')
            $filter_other = '';
        if ($filter_commission == 'null')
            $filter_commission = '';
        if (isset($_GET['page']))
            $page = $_GET['page'];

        $em = $this->getDoctrine()->getEntityManager();
        $paginator = $this->get('ideup.simple_paginator');
        $paginator->setItemsPerPage($items_per_page);
        $ownerships = $paginator->paginate($em->getRepository('mycpBundle:ownership')->getAll(
            $filter_code, $filter_active, $filter_category, $filter_province, $filter_municipality, $filter_destination, $filter_type, $filter_name, $filter_saler, $filter_visit_date, $filter_other, $filter_commission
        ))->getResult();

        $service_log = $this->get('log');
        $service_log->saveLog('Visit', BackendModuleName::MODULE_OWNERSHIP);
        return $this->render('mycpBundle:revision:list.html.twig', array(
            'ownerships' => $ownerships,
            //'photo_count' => $data,
            'items_per_page' => $items_per_page,
            'current_page' => $page,
            'total_items' => $paginator->getTotalItems(),
            'filter_code' => $filter_code,
            'filter_name' => $filter_name,
            'filter_active' => $filter_active,
            'filter_category' => $filter_category,
            'filter_province' => $filter_province,
            'filter_municipality' => $filter_municipality,
            'filter_type' => $filter_type,
            'filter_saler' => $filter_saler,
            'filter_visit_date' => $filter_visit_date,
            'filter_destination' => $filter_destination,
            'filter_other' => $filter_other,
            'filter_commission' => $filter_commission
        ));
    }

}
