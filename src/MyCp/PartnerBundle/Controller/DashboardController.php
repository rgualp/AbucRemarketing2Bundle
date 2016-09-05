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

        #region PAGINADO
        $start = $request->get('start', 0);
        $limit = $request->get('length', 10);
        $draw = $request->get('draw') + 1;
        #endregion PAGINADO

        $data = $this->getReservationsData($filters, $start, $limit, $draw, generalReservation::STATUS_PENDING);
        $reservations = $data['aaData'];
        $data['aaData'] = array();

        foreach ($reservations as $reservation) {
            $arrTmp = array();
            $arrTmp['id'] = $reservation->getGenResId();
            $arrTmp['data'] = array(
                'cas'=>'CAS'.$reservation->getGenResId(),
                'from'=>$reservation->getGenResFromDate()->format('d-m-Y'),
                'to'=>$reservation->getGenResToDate()->format('d-m-Y'),
                'own_mcp_code'=>$reservation->getGenResOwnId()->getOwnMcpCode(),
                'own_name'=>$reservation->getGenResOwnId()->getOwnName(),
                'destination'=>$reservation->getGenResOwnId()->getOwnDestination()->getDesName(),
                'date'=>$reservation->getGenResDate()->format('d-m-Y'));

            $ownReservations = $reservation->getOwn_reservations();
            $arrTmp['data']['rooms'] = array();
            if (!$ownReservations->isEmpty()) {
                $ownReservation = $ownReservations->first();
                do {
                    $arrTmp['data']['rooms'][] = array(
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
        $filters = $request->get('booking_availability_filter_form');
        $filters = (isset($filters)) ? ($filters) : (array());

        #region PAGINADO
        $start = $request->get('start', 0);
        $limit = $request->get('length', 10);
        $draw = $request->get('draw') + 1;
        #endregion PAGINADO

        $data = $this->getReservationsData($filters, $start, $limit, $draw, generalReservation::STATUS_AVAILABLE);
        $reservations = $data['aaData'];
        $data['aaData'] = array();

        $timeService = $this->get('time');

        foreach ($reservations as $reservation) {
            $arrTmp = array();
            $arrTmp['id'] = $reservation->getGenResId();
            $arrTmp['data'] = array(
                'cas'=>'CAS'.$reservation->getGenResId(),
                'from'=>$reservation->getGenResFromDate()->format('d-m-Y'),
                'to'=>$reservation->getGenResToDate()->format('d-m-Y'),
                'own_mcp_code'=>$reservation->getGenResOwnId()->getOwnMcpCode(),
                'destination'=>$reservation->getGenResOwnId()->getOwnDestination()->getDesName(),
                'date'=>$reservation->getGenResDate()->format('d-m-Y'),
                'own_name'=>$reservation->getGenResOwnId()->getOwnName());

            $ownReservations = $reservation->getOwn_reservations();
            $arrTmp['data']['rooms'] = array();
            if (!$ownReservations->isEmpty()) {
                $ownReservation = $ownReservations->first();
                do {
                    $nights = $timeService->nights($ownReservation->getOwnResReservationFromDate()->getTimestamp(), $ownReservation->getOwnResReservationToDate()->getTimestamp());
                    $totalPrice = 0;
                    if($ownReservation->getOwnResNightPrice() > 0){
                        $totalPrice += $ownReservation->getOwnResNightPrice() * $nights;
                        //$initialPayment += $res->getOwnResNightPrice() * $nights * $comission;
                    }
                    else{
                        $totalPrice += $ownReservation->getOwnResTotalInSite();
                        //$initialPayment += $res->getOwnResTotalInSite() * $comission;
                    }

                    $arrTmp['data']['rooms'][] = array(
                        'type'=>$ownReservation->getOwnResRoomType(),
                        'adults'=>$ownReservation->getOwnResCountAdults(),
                        'childrens'=>$ownReservation->getOwnResCountChildrens(),
                        'totalPrice'=>$totalPrice/*,
                        'booking'=>array(
                            'prepay'=>$ownReservation->getOwnResReservationBooking()->getBookingPrepay()
                        )*/
                    );
                    $ownReservation = $ownReservations->next();
                } while ($ownReservation);
            }

            $data['aaData'][] = $arrTmp;
        }

        return new JsonResponse($data);
    }

    public function indexBookingNotavailabilityAction()
    {
        return new JsonResponse([
            'success' => true,
            'id' => 'id_dashboard_booking_notavailability',
            'html' => $this->renderView('PartnerBundle:Dashboard:booking_notavailability.html.twig', array()),
            'msg' => 'Vista del listado de reservas NO DISPONIBLES']);
    }
    public function listBookingNotavailabilityAction(Request $request)
    {
        $filters = $request->get('booking_notavailability_filter_form');
        $filters = (isset($filters)) ? ($filters) : (array());

        #region PAGINADO
        $start = $request->get('start', 0);
        $limit = $request->get('length', 10);
        $draw = $request->get('draw') + 1;
        #endregion PAGINADO

        $data = $this->getReservationsData($filters, $start, $limit, $draw, generalReservation::STATUS_NOT_AVAILABLE);
        $reservations = $data['aaData'];
        $data['aaData'] = array();

        $timeService = $this->get('time');

        foreach ($reservations as $reservation) {
            $arrTmp = array();
            $arrTmp['id'] = $reservation->getGenResId();
            $arrTmp['data'] = array(
                'cas'=>'CAS'.$reservation->getGenResId(),
                'from'=>$reservation->getGenResFromDate()->format('d-m-Y'),
                'to'=>$reservation->getGenResToDate()->format('d-m-Y'),
                'own_mcp_code'=>$reservation->getGenResOwnId()->getOwnMcpCode(),
                'destination'=>$reservation->getGenResOwnId()->getOwnDestination()->getDesName(),
                'date'=>$reservation->getGenResDate()->format('d-m-Y'),
                'own_name'=>$reservation->getGenResOwnId()->getOwnName());

            $ownReservations = $reservation->getOwn_reservations();
            $arrTmp['data']['rooms'] = array();
            if (!$ownReservations->isEmpty()) {
                $ownReservation = $ownReservations->first();
                do {
                    $nights = $timeService->nights($ownReservation->getOwnResReservationFromDate()->getTimestamp(), $ownReservation->getOwnResReservationToDate()->getTimestamp());
                    $totalPrice = 0;
                    if($ownReservation->getOwnResNightPrice() > 0){
                        $totalPrice += $ownReservation->getOwnResNightPrice() * $nights;
                        //$initialPayment += $res->getOwnResNightPrice() * $nights * $comission;
                    }
                    else{
                        $totalPrice += $ownReservation->getOwnResTotalInSite();
                        //$initialPayment += $res->getOwnResTotalInSite() * $comission;
                    }

                    $arrTmp['data']['rooms'][] = array(
                        'type'=>$ownReservation->getOwnResRoomType(),
                        'adults'=>$ownReservation->getOwnResCountAdults(),
                        'childrens'=>$ownReservation->getOwnResCountChildrens(),
                        'totalPrice'=>$totalPrice
                    );
                    $ownReservation = $ownReservations->next();
                } while ($ownReservation);
            }

            $data['aaData'][] = $arrTmp;
        }

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
        $filters = $request->get('booking_reserved_filter_form');
        $filters = (isset($filters)) ? ($filters) : (array());

        #region PAGINADO
        $start = $request->get('start', 0);
        $limit = $request->get('length', 10);
        $draw = $request->get('draw') + 1;
        #endregion PAGINADO

        $data = $this->getReservationsData($filters, $start, $limit, $draw, generalReservation::STATUS_RESERVED);
        $reservations = $data['aaData'];
        $data['aaData'] = array();

        $timeService = $this->get('time');

        foreach ($reservations as $reservation) {
            $arrTmp = array();
            $arrTmp['id'] = $reservation->getGenResId();
            $arrTmp['data'] = array(
                'cas'=>'CAS'.$reservation->getGenResId(),
                'from'=>$reservation->getGenResFromDate()->format('d-m-Y'),
                'to'=>$reservation->getGenResToDate()->format('d-m-Y'),
                'own_mcp_code'=>$reservation->getGenResOwnId()->getOwnMcpCode(),
                'destination'=>$reservation->getGenResOwnId()->getOwnDestination()->getDesName(),
                'date'=>$reservation->getGenResDate()->format('d-m-Y'),
                'own_name'=>$reservation->getGenResOwnId()->getOwnName()/*,
                'client_name'=>$reservation->getTravelAgencyDetailReservations()->getReservation()->getClient()->getFullName()*/
            );

            $ownReservations = $reservation->getOwn_reservations();
            $arrTmp['data']['rooms'] = array();
            if (!$ownReservations->isEmpty()) {
                $ownReservation = $ownReservations->first();
                do {
                    $nights = $timeService->nights($ownReservation->getOwnResReservationFromDate()->getTimestamp(), $ownReservation->getOwnResReservationToDate()->getTimestamp());
                    $totalPrice = 0;
                    if($ownReservation->getOwnResNightPrice() > 0){
                        $totalPrice += $ownReservation->getOwnResNightPrice() * $nights;
                        //$initialPayment += $res->getOwnResNightPrice() * $nights * $comission;
                    }
                    else{
                        $totalPrice += $ownReservation->getOwnResTotalInSite();
                        //$initialPayment += $res->getOwnResTotalInSite() * $comission;
                    }

                    $arrTmp['data']['rooms'][] = array(
                        'type'=>$ownReservation->getOwnResRoomType(),
                        'totalPrice'=>$totalPrice,
                        'booking'=>array(
                            'code'=>$ownReservation->getOwnResReservationBooking()->getBookingId(),
                            'date'=>$ownReservation->getOwnResReservationBooking()->getPayments()->first()->getCreated()->format('d-m-Y')
                        )
                    );
                    $ownReservation = $ownReservations->next();
                } while ($ownReservation);
            }

            $data['aaData'][] = $arrTmp;
        }

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
        $filters = $request->get('booking_beaten_filter_form');
        $filters = (isset($filters)) ? ($filters) : (array());

        #region PAGINADO
        $start = $request->get('start', 0);
        $limit = $request->get('length', 10);
        $draw = $request->get('draw') + 1;
        #endregion PAGINADO

        $data = $this->getReservationsData($filters, $start, $limit, $draw, generalReservation::STATUS_OUTDATED);
        $reservations = $data['aaData'];
        $data['aaData'] = array();

        $timeService = $this->get('time');

        foreach ($reservations as $reservation) {
            $arrTmp = array();
            $arrTmp['id'] = $reservation->getGenResId();
            $arrTmp['data'] = array(
                'cas'=>'CAS'.$reservation->getGenResId(),
                'from'=>$reservation->getGenResFromDate()->format('d-m-Y'),
                'to'=>$reservation->getGenResToDate()->format('d-m-Y'),
                'own_mcp_code'=>$reservation->getGenResOwnId()->getOwnMcpCode(),
                'destination'=>$reservation->getGenResOwnId()->getOwnDestination()->getDesName(),
                'date'=>$reservation->getGenResDate()->format('d-m-Y'),
                'own_name'=>$reservation->getGenResOwnId()->getOwnName());

            $ownReservations = $reservation->getOwn_reservations();
            $arrTmp['data']['rooms'] = array();
            if (!$ownReservations->isEmpty()) {
                $ownReservation = $ownReservations->first();
                do {
                    $nights = $timeService->nights($ownReservation->getOwnResReservationFromDate()->getTimestamp(), $ownReservation->getOwnResReservationToDate()->getTimestamp());
                    $totalPrice = 0;
                    if($ownReservation->getOwnResNightPrice() > 0){
                        $totalPrice += $ownReservation->getOwnResNightPrice() * $nights;
                        //$initialPayment += $res->getOwnResNightPrice() * $nights * $comission;
                    }
                    else{
                        $totalPrice += $ownReservation->getOwnResTotalInSite();
                        //$initialPayment += $res->getOwnResTotalInSite() * $comission;
                    }

                    $arrTmp['data']['rooms'][] = array(
                        'type'=>$ownReservation->getOwnResRoomType(),
                        'totalPrice'=>$totalPrice/*,
                        'booking'=>array(
                            'client_name'=>$ownReservation->getOwnResReservationBooking()->getBookingUserDates()
                        )*/
                    );
                    $ownReservation = $ownReservations->next();
                } while ($ownReservation);
            }

            $data['aaData'][] = $arrTmp;
        }

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
        $filters = $request->get('booking_Canceled_filter_form');
        $filters = (isset($filters)) ? ($filters) : (array());

        #region PAGINADO
        $start = $request->get('start', 0);
        $limit = $request->get('length', 10);
        $draw = $request->get('draw') + 1;
        #endregion PAGINADO

        $data = $this->getReservationsData($filters, $start, $limit, $draw, generalReservation::STATUS_CANCELLED);
        $reservations = $data['aaData'];
        $data['aaData'] = array();

        $timeService = $this->get('time');

        foreach ($reservations as $reservation) {
            $arrTmp = array();
            $arrTmp['id'] = $reservation->getGenResId();
            $arrTmp['data'] = array(
                'cas'=>'CAS'.$reservation->getGenResId(),
                'from'=>$reservation->getGenResFromDate()->format('d-m-Y'),
                'to'=>$reservation->getGenResToDate()->format('d-m-Y'),
                'own_mcp_code'=>$reservation->getGenResOwnId()->getOwnMcpCode(),
                'destination'=>$reservation->getGenResOwnId()->getOwnDestination()->getDesName(),
                'date'=>$reservation->getGenResDate()->format('d-m-Y'),
                'own_name'=>$reservation->getGenResOwnId()->getOwnName());

            $ownReservations = $reservation->getOwn_reservations();
            $arrTmp['data']['rooms'] = array();
            if (!$ownReservations->isEmpty()) {
                $ownReservation = $ownReservations->first();
                do {
                    $nights = $timeService->nights($ownReservation->getOwnResReservationFromDate()->getTimestamp(), $ownReservation->getOwnResReservationToDate()->getTimestamp());
                    $totalPrice = 0;
                    if($ownReservation->getOwnResNightPrice() > 0){
                        $totalPrice += $ownReservation->getOwnResNightPrice() * $nights;
                        //$initialPayment += $res->getOwnResNightPrice() * $nights * $comission;
                    }
                    else{
                        $totalPrice += $ownReservation->getOwnResTotalInSite();
                        //$initialPayment += $res->getOwnResTotalInSite() * $comission;
                    }

                    $arrTmp['data']['rooms'][] = array(
                        'type'=>$ownReservation->getOwnResRoomType(),
                        'totalPrice'=>$totalPrice/*,
                        'booking'=>array(
                            'client_name'=>$ownReservation->getOwnResReservationBooking()->getBookingUserDates()
                        )*/
                    );
                    $ownReservation = $ownReservations->next();
                } while ($ownReservation);
            }

            $data['aaData'][] = $arrTmp;
        }

        return new JsonResponse($data);
    }

    public function getReservationsData($filters, $start, $limit, $draw, $status){
        $user=$this->getUser();

        $em = $this->getDoctrine()->getManager();
        $repository = $em->getRepository(generalReservation::class);

        #region PAGINADO
        $page = ($start > 0) ? $start / $limit + 1 : 1;
        $paginator = $repository->getReservationsPartner($user->getUserId(), $status, $filters, $start, $limit);;
        $reservations = $paginator['data'];
        #endregion PAGINADO

        $data = array();
        $data['draw'] = $draw;
        $data['iTotalRecords'] = $paginator['count'];
        $data['iTotalDisplayRecords'] = $paginator['count'];
        $data['aaData'] = $reservations;

        return $data;
    }
}
