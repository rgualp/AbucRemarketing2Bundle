<?php

namespace MyCp\PartnerBundle\Controller;

use MyCp\mycpBundle\Entity\generalReservation;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class DashboardController extends Controller
{
    public function indexBookingPendingAction()
    {
        return new JsonResponse([
            'success' => true,
            'id' => 'id_dashboard_booking_pending',
            'html' => $this->renderView('PartnerBundle:Dashboard:booking_pending.html.twig', array()),
            'msg' => 'Vista del listado de reservas PENDIENTES']);
    }

    public function listBookingPendingAction(Request $request)
    {
        $filters = $request->get('booking_pending_filter_form');
        $filters = (isset($filters)) ? ($filters) : (array());

        $user=$this->getUser();

        $em = $this->getDoctrine()->getManager();
        $repository = $em->getRepository(generalReservation::class);

        #region PAGINADO
        $start = $request->get('start', 0);
        $limit = $request->get('length', 10);
        $page = ($start > 0) ? $start / $limit + 1 : 1;
        $paginator = $repository->getReservationsPartner($user->getUserId(), generalReservation::STATUS_PENDING, $filters, $start, $limit);;
        $list = $paginator['data'];
        #endregion PAGINADO

        $data = array();
        $data['draw'] = $request->get('draw') + 1;
        $data['iTotalRecords'] = $paginator['count'];
        $data['iTotalDisplayRecords'] = $paginator['count'];
        $data['aaData'] = array();

        foreach ($list as $reservation) {
            $arrTmp = array();
            $arrTmp['id'] = $reservation->getGenResId();
            $arrTmp['datax'] = array(
                'cas'=>'CAS'.$reservation->getGenResId(),
                'from'=>$reservation->getGenResFromDate()->format('d-m-Y'),
                'to'=>$reservation->getGenResToDate()->format('d-m-Y'),
                'own_name'=>$reservation->getGenResOwnId()->getOwnName());

            $ownReservations = $reservation->getOwn_reservations();
            $arrTmp['datax']['rooms'] = array();
            if (!$ownReservations->isEmpty()) {
                $ownReservation = $ownReservations->first();
                do {
                    $arrTmp['datax']['rooms'][] = array(
                        'type'=>$ownReservation->getOwnResRoomType(),
                        'adults'=>$ownReservation->getOwnResCountAdults(),
                        'childrens'=>$ownReservation->getOwnResCountChildrens()
                    );
                    $ownReservation = $ownReservations->next();
                } while ($ownReservation);
            }

            $data['aaData'][] = $arrTmp;
        }

        return new JsonResponse($data);
    }

    public function indexBookingAvailabilityAction()
    {
        return new JsonResponse([
            'success' => true,
            'id' => 'id_dashboard_booking_availability',
            'html' => $this->renderView('PartnerBundle:Dashboard:booking_availability.html.twig', array()),
            'msg' => 'Vista del listado de reservas DISPONIBLES']);
    }

    public function listBookingAvailabilityAction(Request $request)
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
        $arrTmp['data'] = array(
            'code'=>'CH002',
            'arrival'=>'08/08/2016',
            'exit'=>'08/08/2016',
            'name'=>'Casa Miguelito',
            'client'=>'Andy el Trucha',
            'room'=>'Doble',
            'price'=>'20.00cuc',
            'payment'=>'60.00cuc');
        $data['aaData'][] = $arrTmp;
        //}

        return new JsonResponse($data);
    }

    public function indexBookingBeatenAction()
    {
        return new JsonResponse([
            'success' => true,
            'id' => 'id_dashboard_booking_beaten',
            'html' => $this->renderView('PartnerBundle:Dashboard:booking_beaten.html.twig', array()),
            'msg' => 'Vista del listado de reservas VENCIDAS']);
    }

    public function listBookingBeatenAction(Request $request)
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

        $arrTmp = array();
        $arrTmp['id'] = 2;
        $arrTmp['data'] = array('code'=>'CH001', 'arrival'=>'08/08/2016', 'exit'=>'08/08/2016', 'name'=>'Casa Candida', 'client'=>'Emilito el Trinca', 'room'=>'Doble', 'price'=>'20.00cuc', 'payment'=>'60.00cuc');
        $data['aaData'][] = $arrTmp;

        return new JsonResponse($data);
    }

    public function indexBookingReservedAction()
    {
        return new JsonResponse([
            'success' => true,
            'id' => 'id_dashboard_booking_reserved',
            'html' => $this->renderView('PartnerBundle:Dashboard:booking_reserved.html.twig', array()),
            'msg' => 'Vista del listado de reservas PENDIENTES']);
    }

    public function listBookingReservedAction(Request $request)
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

        $arrTmp = array();
        $arrTmp['id'] = 2;
        $arrTmp['data'] = array('code'=>'CH001', 'arrival'=>'08/08/2016', 'exit'=>'08/08/2016', 'name'=>'Casa Candida', 'client'=>'Emilito el Trinca', 'room'=>'Doble', 'price'=>'20.00cuc', 'payment'=>'60.00cuc');
        $data['aaData'][] = $arrTmp;

        return new JsonResponse($data);
    }

    public function indexBookingCanceledAction()
    {
        return new JsonResponse([
            'success' => true,
            'id' => 'id_dashboard_booking_canceled',
            'html' => $this->renderView('PartnerBundle:Dashboard:booking_canceled.html.twig', array()),
            'msg' => 'Vista del listado de reservas CANCELADAS']);
    }

    public function listBookingCanceledAction(Request $request)
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

        $arrTmp = array();
        $arrTmp['id'] = 2;
        $arrTmp['data'] = array('code'=>'CH001', 'arrival'=>'08/08/2016', 'exit'=>'08/08/2016', 'name'=>'Casa Candida', 'client'=>'Emilito el Trinca', 'room'=>'Doble', 'price'=>'20.00cuc', 'payment'=>'60.00cuc');
        $data['aaData'][] = $arrTmp;

        return new JsonResponse($data);
    }
}
