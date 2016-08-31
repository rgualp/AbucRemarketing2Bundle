<?php

namespace MyCp\PartnerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class DashboardController extends Controller
{
    public function indexAction()
    {
        return new JsonResponse([
            'success' => true,
            'html' => $this->renderView('PartnerBundle:Dashboard:requests.html.twig', array()),
            'msg' => 'Vista del listado de reservas pendientes']);
    }

    public function listAction(Request $request)
    {
        /*$filters = $request->get('bussiness_filter');
        $filters = (isset($filters)) ? ($filters) : (array());

        $businesses = $this->repository->findByFilters($filters);
        //$businesses = $this->repository->findAll();

        #region PAGINADO
        $start = $request->get('start', 0);
        $limit = $request->get('length', 10);
        $page = ($start > 0) ? $start / $limit + 1 : 1;
        $paginator = $this->get('knp_paginator');
        $list = $paginator->paginate($businesses, $page, $limit);
        #endregion PAGINADO

        $data = array();
        $data['draw'] = $request->get('draw') + 1;
        $data['iTotalRecords'] = $list->getTotalItemCount();
        $data['iTotalDisplayRecords'] = $list->getTotalItemCount();
        $data['aaData'] = array();*/

        //foreach ($list as $item) {
            $arrTmp = array();
            $arrTmp['id'] = 1;
            $arrTmp['data'] = array('code'=>'CH002', 'arrival'=>'08/08/2016', 'exit'=>'08/08/2016', 'name'=>'Casa Miguelito', 'client'=>'Andy el Trucha', 'room'=>'Doble', 'price'=>'20.00cuc', 'payment'=>'60.00cuc');

            $data['aaData'][] = $arrTmp;
        //}

        $arrTmp = array();
        $arrTmp['id'] = 2;
        $arrTmp['data'] = array('code'=>'CH001', 'arrival'=>'08/08/2016', 'exit'=>'08/08/2016', 'name'=>'Casa Candida', 'client'=>'Emilito el Trinca', 'room'=>'Doble', 'price'=>'20.00cuc', 'payment'=>'60.00cuc');

        $data['aaData'][] = $arrTmp;

        return new JsonResponse($data);
    }
}
