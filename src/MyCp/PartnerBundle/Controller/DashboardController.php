<?php

namespace MyCp\PartnerBundle\Controller;

use MyCp\FrontEndBundle\Helpers\Utils;
use MyCp\mycpBundle\Entity\generalReservation;
use MyCp\mycpBundle\Entity\ownershipReservation;
use MyCp\PartnerBundle\Form\FilterOwnershipType;
use MyCp\PartnerBundle\Form\paReservationType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class DashboardController extends Controller
{
    /*BookingPending*/
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
                'id'=>$reservation->getGenResId(),
                'cas'=>''.$reservation->getGenResId(),
                'from'=>$reservation->getGenResFromDate()->format('d-m-Y'),
                'to'=>$reservation->getGenResToDate()->format('d-m-Y'),
                'own_mcp_code'=>$reservation->getGenResOwnId()->getOwnMcpCode(),
                'own_name'=>$reservation->getGenResOwnId()->getOwnName(),
                'destination'=>$reservation->getGenResOwnId()->getOwnDestination()->getDesName(),
                'client_dates'=>$reservation->getTravelAgencyDetailReservations()->first()->getReservation()->getClient()->getFullName(),
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
    /*End BookingPending*/

    /*BookingAvailability*/
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
                'id'=>$reservation->getGenResId(),
                'cas'=>''.$reservation->getGenResId(),
                'from'=>$reservation->getGenResFromDate()->format('d-m-Y'),
                'to'=>$reservation->getGenResToDate()->format('d-m-Y'),
                'own_mcp_code'=>$reservation->getGenResOwnId()->getOwnMcpCode(),
                'destination'=>$reservation->getGenResOwnId()->getOwnDestination()->getDesName(),
                'date'=>$reservation->getGenResDate()->format('d-m-Y'),
                'client_dates'=>$reservation->getTravelAgencyDetailReservations()->first()->getReservation()->getClient()->getFullName(),
                'own_name'=>$reservation->getGenResOwnId()->getOwnName());

            $ownReservations = $reservation->getOwn_reservations();
            $arrTmp['data']['rooms'] = array();
            if (!$ownReservations->isEmpty()) {
                $ownReservation = $ownReservations->first();
                $curr = $this->getCurr($request);
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
                        'totalPrice'=>($totalPrice * $curr['change']),
                        'curr_code'=>$curr['code']
                        /*'booking'=>array(
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
    /*End BookingAvailability*/

    /*BookingNotavailability*/
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
                'id'=>$reservation->getGenResId(),
                'cas'=>''.$reservation->getGenResId(),
                'from'=>$reservation->getGenResFromDate()->format('d-m-Y'),
                'to'=>$reservation->getGenResToDate()->format('d-m-Y'),
                'own_mcp_code'=>$reservation->getGenResOwnId()->getOwnMcpCode(),
                'destination'=>$reservation->getGenResOwnId()->getOwnDestination()->getDesName(),
                'date'=>$reservation->getGenResDate()->format('d-m-Y'),
                'client_dates'=>$reservation->getTravelAgencyDetailReservations()->first()->getReservation()->getClient()->getFullName(),
                'own_name'=>$reservation->getGenResOwnId()->getOwnName());

            $ownReservations = $reservation->getOwn_reservations();
            $arrTmp['data']['rooms'] = array();
            if (!$ownReservations->isEmpty()) {
                $ownReservation = $ownReservations->first();
                $curr = $this->getCurr($request);
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
                        'totalPrice'=>($totalPrice * $curr['change']),
                        'curr_code'=>$curr['code']
                    );
                    $ownReservation = $ownReservations->next();
                } while ($ownReservation);
            }

            $data['aaData'][] = $arrTmp;
        }

        return new JsonResponse($data);
    }
    /*End BookingNotavailability*/

    /*BookingReserved*/
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
                'id'=>$reservation->getGenResId(),
                'cas'=>''.$reservation->getGenResId(),
                'from'=>$reservation->getGenResFromDate()->format('d-m-Y'),
                'to'=>$reservation->getGenResToDate()->format('d-m-Y'),
                'own_mcp_code'=>$reservation->getGenResOwnId()->getOwnMcpCode(),
                'destination'=>$reservation->getGenResOwnId()->getOwnDestination()->getDesName(),
                'date'=>$reservation->getGenResDate()->format('d-m-Y'),
                'client_dates'=>$reservation->getTravelAgencyDetailReservations()->first()->getReservation()->getClient()->getFullName(),
                'own_name'=>$reservation->getGenResOwnId()->getOwnName()/*,
                'client_name'=>$reservation->getTravelAgencyDetailReservations()->getReservation()->getClient()->getFullName()*/
            );

            $ownReservations = $reservation->getOwn_reservations();
            $arrTmp['data']['rooms'] = array();
            if (!$ownReservations->isEmpty()) {
                $ownReservation = $ownReservations->first();
                $curr = $this->getCurr($request);
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
                        'totalPrice'=>($totalPrice * $curr['change']),
                        'curr_code'=>$curr['code'],
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
    /*End BookingReserved*/

    /*BookingBeaten*/
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
                'id'=>$reservation->getGenResId(),
                'cas'=>''.$reservation->getGenResId(),
                'from'=>$reservation->getGenResFromDate()->format('d-m-Y'),
                'to'=>$reservation->getGenResToDate()->format('d-m-Y'),
                'own_mcp_code'=>$reservation->getGenResOwnId()->getOwnMcpCode(),
                'destination'=>$reservation->getGenResOwnId()->getOwnDestination()->getDesName(),
                'date'=>$reservation->getGenResDate()->format('d-m-Y'),
                'client_dates'=>$reservation->getTravelAgencyDetailReservations()->first()->getReservation()->getClient()->getFullName(),
                'own_name'=>$reservation->getGenResOwnId()->getOwnName());

            $ownReservations = $reservation->getOwn_reservations();
            $arrTmp['data']['rooms'] = array();
            if (!$ownReservations->isEmpty()) {
                $ownReservation = $ownReservations->first();
                $curr = $this->getCurr($request);
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
                        'totalPrice'=>($totalPrice * $curr['change']),
                        'curr_code'=>$curr['code']/*,
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
    /*End BookingBeaten*/

    /*BookingCanceled*/
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
        $filters = $request->get('booking_canceled_filter_form');
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
                'id'=>$reservation->getGenResId(),
                'cas'=>''.$reservation->getGenResId(),
                'from'=>$reservation->getGenResFromDate()->format('d-m-Y'),
                'to'=>$reservation->getGenResToDate()->format('d-m-Y'),
                'own_mcp_code'=>$reservation->getGenResOwnId()->getOwnMcpCode(),
                'destination'=>$reservation->getGenResOwnId()->getOwnDestination()->getDesName(),
                'date'=>$reservation->getGenResDate()->format('d-m-Y'),
                'client_dates'=>$reservation->getTravelAgencyDetailReservations()->first()->getReservation()->getClient()->getFullName(),
                'own_name'=>$reservation->getGenResOwnId()->getOwnName());

            $ownReservations = $reservation->getOwn_reservations();
            $arrTmp['data']['rooms'] = array();
            if (!$ownReservations->isEmpty()) {
                $ownReservation = $ownReservations->first();
                $curr = $this->getCurr($request);
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
                        'totalPrice'=>($totalPrice * $curr['change']),
                        'curr_code'=>$curr['code']/*,
                        'booking'=>array(
                            'code'=>$ownReservation->getOwnResReservationBooking()->getBookingId(),
                        )*/
                    );
                    $ownReservation = $ownReservations->next();
                } while ($ownReservation);
            }

            $data['aaData'][] = $arrTmp;
        }

        return new JsonResponse($data);
    }
    /*End BookingCanceled*/

    /*BookingCheckin*/
    public function indexBookingCheckinAction()
    {
        return new JsonResponse([
            'success' => true,
            'id' => 'id_dashboard_booking_checkin',
            'html' => $this->renderView('PartnerBundle:Dashboard:booking_checkin.html.twig', array()),
            'msg' => 'Vista del listado de reservas reservadas Checkin']);
    }

    public function listBookingCheckinAction(Request $request)
    {
        $filters = $request->get('booking_checkin_filter_form');
        $filters = (isset($filters)) ? ($filters) : (array());

        $from = (array_key_exists('from', $filters) && isset($filters['from']));
        $to = (array_key_exists('to', $filters) && isset($filters['to']));
        if(!$from && !$to){
            $date = new \DateTime();
            $date_a = $date->format('d-m-Y');
            $date->modify('+5 day');
            $date_b = $date->format('d-m-Y');
            $filters['from_between'] = array($date_a, $date_b);
        }

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
            $h = $reservation->getGenResArrivalHour();
            $arrTmp['data'] = array(
                'id'=>$reservation->getGenResId(),
                'cas'=>''.$reservation->getGenResId(),
                'from'=>$reservation->getGenResFromDate()->format('d-m-Y')." $h",
                'to'=>$reservation->getGenResToDate()->format('d-m-Y'),
                'own_mcp_code'=>$reservation->getGenResOwnId()->getOwnMcpCode(),
                'destination'=>$reservation->getGenResOwnId()->getOwnDestination()->getDesName(),
                'date'=>$reservation->getGenResDate()->format('d-m-Y'),
                'client_dates'=>$reservation->getTravelAgencyDetailReservations()->first()->getReservation()->getClient()->getFullName(),
                'own_name'=>$reservation->getGenResOwnId()->getOwnName()/*,
                'client_name'=>$reservation->getTravelAgencyDetailReservations()->getReservation()->getClient()->getFullName()*/
            );

            $ownReservations = $reservation->getOwn_reservations();
            $arrTmp['data']['rooms'] = array();
            if (!$ownReservations->isEmpty()) {
                $ownReservation = $ownReservations->first();
                $curr = $this->getCurr($request);
                do {
                    $totalPrice = $ownReservation->getPriceTotal($timeService);

                    $arrTmp['data']['rooms'][] = array(
                        'type'=>$ownReservation->getOwnResRoomType(),
                        'totalPrice'=>($totalPrice * $curr['change']),
                        'totalPriceInHome'=>$ownReservation->getPricePerInHome($timeService) * $curr['change'],
                        'totalPriceInHomeX'=>$ownReservation->getPricePerInHome($timeService),
                        'curr_code'=>$curr['code'],
                        'adults'=>$ownReservation->getOwnResCountAdults(),
                        'childrens'=>$ownReservation->getOwnResCountChildrens(),
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

    public function listExportBookingCheckinAction(Request $request)
    {
        $filters = $request->get('booking_checkin_filter_form');
        $filters = (isset($filters)) ? ($filters) : (array());

        $from = (array_key_exists('from', $filters) && isset($filters['from']));
        $to = (array_key_exists('to', $filters) && isset($filters['to']));
        if(!$from && !$to){
            $date = new \DateTime();
            $date_a = $date->format('d-m-Y');
            $date->modify('+5 day');
            $date_b = $date->format('d-m-Y');
            $filters['from_between'] = array($date_a, $date_b);
        }

        #region PAGINADO
        $start = $request->get('start', 0);
        $limit = false;
        $draw = $request->get('draw') + 1;
        #endregion PAGINADO

        $data = $this->getReservationsData($filters, $start, $limit, $draw, generalReservation::STATUS_RESERVED);
        $reservations = $data['aaData'];
        $data['aaData'] = array();

        $curr = $this->getCurr($request);

        if(count($reservations)) {
            $exporter = $this->get("mycp.service.export_to_excel");
            return $exporter->exportReservationsCheckinAg($reservations, new \DateTime(), $curr);
        }
    }
    /*End BookingCheckin*/

    /*BookingProccess*/
    public function indexBookingProccessAction()
    {
        return new JsonResponse([
            'success' => true,
            'id' => 'id_dashboard_booking_proccess',
            'html' => $this->renderView('PartnerBundle:Dashboard:booking_proccess.html.twig', array()),
            'msg' => 'Vista del listado de reservas en Proccess']);
    }

    public function listBookingProccessAction(Request $request)
    {
        $filters = $request->get('booking_proccess_filter_form');
        $filters = (isset($filters)) ? ($filters) : (array());

        #region PAGINADO
        $start = $request->get('start', 0);
        $limit = $request->get('length', 10);
        $draw = $request->get('draw') + 1;
        #endregion PAGINADO

        $data = $this->getReservationsProccessData($filters, $start, $limit, $draw);
        $reservations = $data['aaData'];
        $data['aaData'] = array();

        foreach ($reservations as $reservation) {
            $arrTmp = array();
            $arrTmp['id'] = $reservation->getId();
            $arrTmp['data'] = array(
                'id'=>$reservation->getId(),
                'from'=>$reservation->getDateFrom()->format('d-m-Y'),
                'to'=>$reservation->getDateTo()->format('d-m-Y'),
                'own_mcp_code'=>$reservation->getAccommodation()->getOwnMcpCode(),
                'own_name'=>$reservation->getAccommodation()->getOwnName(),
                'destination'=>$reservation->getAccommodation()->getOwnDestination()->getDesName(),
                'client_dates'=>$reservation->getTravelAgencyOpenReservationsDetails()->first()->getReservation()->getClient()->getFullName(),
                'date'=>$reservation->getCreationDate()->format('d-m-Y'));

            $ownReservations = $reservation->getPaOwnershipReservations();
            $arrTmp['data']['rooms'] = array();
            if (!$ownReservations->isEmpty()) {
                $ownReservation = $ownReservations->first();
                do {
                    $arrTmp['data']['rooms'][] = array(
                        'adults'=>$ownReservation->getAdults(),
                        'childrens'=>$ownReservation->getChildren()
                    );
                    $ownReservation = $ownReservations->next();
                } while ($ownReservation);
            }

            $data['aaData'][] = $arrTmp;
        }

        return new JsonResponse($data);
    }

    public function getReservationsProccessData($filters, $start, $limit, $draw){
        $user=$this->getUser();

        $em = $this->getDoctrine()->getManager();
        $repository = $em->getRepository('PartnerBundle:paGeneralReservation');

        #region PAGINADO
        $page = ($start > 0) ? $start / $limit + 1 : 1;
        $paginator = $repository->getReservationsPartner($user->getUserId(), $filters, $start, $limit);;
        $reservations = $paginator['data'];
        #endregion PAGINADO

        $data = array();
        $data['draw'] = $draw;
        $data['iTotalRecords'] = $paginator['count'];
        $data['iTotalDisplayRecords'] = $paginator['count'];
        $data['aaData'] = $reservations;

        return $data;
    }

    public function indexBookingProccessDetailAction($id_reservation, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $reservation = $em->getRepository('PartnerBundle:paGeneralReservation')->find($id_reservation);
        $ownership_reservations = $reservation->getPaOwnershipReservations();

        $array_nights = array();
        $array_total_prices = array();
        $rooms = array();
        $curr = $this->getCurr($request);

        foreach ($ownership_reservations as $res) {
            $nights = $res->getNights();
            array_push($rooms, $res->getRoom());
            array_push($array_nights, $nights);

            $total_price = ($res->getTotalPrice() * $curr['change']).$curr['code'];
            array_push($array_total_prices, $total_price);
        }

        return new JsonResponse([
            'success' => true,
            'id' => 'id_dashboard_booking_proccess_detail_'.$id_reservation,
            'html' => $this->renderView('PartnerBundle:Dashboard:details_proccess.html.twig', array(
                'id_res'=>$id_reservation,
                'cas'=>"$id_reservation",
                'reservation'=>$reservation,
                'reservations' => $ownership_reservations,
                'rooms' => $rooms,
                'nights' => $array_nights,
                'total_prices'=>$array_total_prices
            )),
            'msg' => 'Vista del detalle de una reserva en proceso']);
    }

    public function closeReservationAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $id_reservation = $request->get("id");
        $paGeneralReservation = $em->getRepository('PartnerBundle:paGeneralReservation')->find($id_reservation);
        $r = $this->closeReservation($paGeneralReservation);

        return new JsonResponse([
            'success' => true,
            'id' => 'id_dashboard_booking_proccess_detail_'.$r,
            'message' => ""
        ]);
    }

    public function closeAllReservationAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $id_reservations = $request->get("ids");
        foreach ($id_reservations as $id_reservation) {
            $paGeneralReservation = $em->getRepository('PartnerBundle:paGeneralReservation')->find($id_reservation);
            $this->closeReservation($paGeneralReservation);
        }

        return new JsonResponse([
            'success' => true,
            'message' => ""
        ]);
    }

    public function closeReservation($paGeneralReservation)
    {
        $em = $this->getDoctrine()->getManager();
        //Send email
        $user = $this->getUser();
        //Configuration service send new availability check
        $service_email = $this->get('Email');
        $translator = $this->get('translator');

        //Send email user new availability check
        $subject = $translator->trans('subject.email.partner.new.availability.check', array(), "messages", strtolower($user->getUserLanguage()->getLangCode()));
        $content=$this->render('PartnerBundle:Mail:newAvailabilityCheck.html.twig', array(
            "reservations" => $paGeneralReservation->getTravelAgencyOpenReservationsDetails(),
            'user_locale'=> strtolower($user->getUserLanguage()->getLangCode()),
            'currency'=> strtoupper($user->getUserCurrency()->getCurrCode()),
            'currency_symbol'=>$user->getUserCurrency()->getCurrSymbol(),
            'currency_rate'=>$user->getUserCurrency()->getCurrCucChange()
        ));
        $service_email->sendTemplatedEmailPartner($subject, 'partner@mycasaparticular.com', $user->getUserEmail(), $content);

        $tourOperator = $em->getRepository("PartnerBundle:paTourOperator")->findOneBy(array("tourOperator" => $user->getUserId()));
        $travelAgency = $tourOperator->getTravelAgency();
        $contacts=$travelAgency->getContacts();
        $phone_contact = (count($contacts)) ? $contacts[0]->getPhone() . ', ' . $contacts[0]->getMobile() : ' ';
        //Send email team reservations
        $content=$this->render('PartnerBundle:Mail:newAvailabilityCheckReservations.html.twig', array(
            "reservations" => $paGeneralReservation->getTravelAgencyOpenReservationsDetails(),
            "rooms"=> $paGeneralReservation->getPaOwnershipReservations(),
            'user_locale'=> strtolower($user->getUserLanguage()->getLangCode()),
            'currency'=> strtoupper($user->getUserCurrency()->getCurrCode()),
            'currency_symbol'=>$user->getUserCurrency()->getCurrSymbol(),
            'currency_rate'=>$user->getUserCurrency()->getCurrCucChange(),
            'travelAgency'=>$travelAgency,
            'agency_resp'=>(count($contacts))?$contacts[0]->getName():'',
            'phone_contact'=>$phone_contact
        ));
        $service_email->sendTemplatedEmailPartner($subject, 'partner@mycasaparticular.com', 'solicitud.partner@mycasaparticular.com', $content);

        $generalReservation = $paGeneralReservation->createReservation();

        $paOwnershipReservations = $paGeneralReservation->getPaOwnershipReservations(); //a eliminar una a una
        //Pasar los paOwnershipReservation a ownershipReservation
        foreach($paOwnershipReservations as $paORes){
            $ownershipReservation = $paORes->createReservation();
            $ownershipReservation->setOwnResGenResId($generalReservation);

            $em->remove($paORes); //Eliminar paOwnershipReservation
            $em->persist($ownershipReservation);
        }

        $travelAgencyOpenReservationsDetails = $paGeneralReservation->getTravelAgencyOpenReservationsDetails();
        foreach($travelAgencyOpenReservationsDetails as $detail){
            //Eliminar el OpenReservationDetail y actualizar el ReservationDetail
            $detail->setOpenReservationDetail(null);
            $detail->setReservationDetail($generalReservation);

            $paReservation = $detail->getReservation();
            $paReservation->setClosed(true);
            $em->persist($paReservation);

            $em->persist($detail);
        }

        $em->persist($generalReservation);
        $em->remove($paGeneralReservation); //Eliminar paGeneralReservation
        $em->flush();

        return $paGeneralReservation->getId();
    }
    /*End BookingProccess*/

    public function getReservationsData($filters, $start, $limit, $draw, $status){
        $user=$this->getUser();

        $em = $this->getDoctrine()->getManager();
        $repository = $em->getRepository('mycpBundle:generalReservation');

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

    public function getCurr(Request $request){
        $session = $request->getSession();
        $a = $session->get("curr_rate");
        $b = $session->get("curr_symbol");
        $c = $session->get("curr_acronym");

        return array("change"=>$a, "code"=>$c);
    }

    /**
     * @param $own_name
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function pageDetailAction($own_name, Request $request) {

        $em = $this->getDoctrine()->getManager();
        $user_ids = $em->getRepository('mycpBundle:user')->getIds($this);
        $locale = $this->get('translator')->getLocale();


        $own_name = str_replace('-', ' ', $own_name);
        $own_name = str_replace('  ', '-', $own_name);
        //$own_name = str_replace("nn", "ñ", $own_name);

        $ownership_array = $em->getRepository('mycpBundle:ownership')->getDetails($own_name, $locale, $user_ids["user_id"], $user_ids["session_id"]);
        if ($ownership_array == null) {
            throw $this->createNotFoundException();
        }

        $langs_array = array();
        if ($ownership_array['english'] == 1)
            $langs_array[] = $this->get('translator')->trans("LANG_ENGLISH");

        if ($ownership_array['french'] == 1)
            $langs_array[] = $this->get('translator')->trans("LANG_FRENCH");

        if ($ownership_array['german'] == 1)
            $langs_array[] = $this->get('translator')->trans("LANG_GERMAN");

        if ($ownership_array['italian'] == 1)
            $langs_array[] = $this->get('translator')->trans("LANG_ITALIAN");

        $languages = $this->get('translator')->trans('LANG_SPANISH');

        foreach ($langs_array as $lang)
            $languages .= ", " . $lang;

        $owner_id = $ownership_array['own_id'];
        $reservations = $em->getRepository('mycpBundle:generalReservation')->getReservationsByIdAccommodation($owner_id);

        // $similar_houses = $em->getRepository('mycpBundle:ownership')->getByCategory($ownership_array['category'], null, $owner_id, $user_ids["user_id"], $user_ids["session_id"]);
        // $total_similar_houses = count($similar_houses);

        $paginator = $this->get('ideup.simple_paginator');
        $items_per_page = 5;
        $paginator->setItemsPerPage($items_per_page);
        $total_comments = $em->getRepository('mycpBundle:comment')->getByOwnership($owner_id);
        $comments = $paginator->paginate($total_comments)->getResult();
        $page = 1;
        if (isset($_GET['page']))
            $page = $_GET['page'];

        $rooms = $em->getRepository('mycpBundle:room')->findBy(array('room_ownership' => $owner_id, 'room_active' => true));
        $friends = array();
        $own_photos = $em->getRepository('mycpBundle:ownership')->getPhotosAndDescription($owner_id, $locale);

        $session = $this->get('session');
        $post = $request->request->getIterator()->getArrayCopy();
        $start_date = (isset($post['top_reservation_filter_date_from'])) ? ($post['top_reservation_filter_date_from']) : (($session->get('search_arrival_date') != null) ? $session->get('search_arrival_date') : 'now');
        $end_date = (isset($post['top_reservation_filter_date_to'])) ? ($post['top_reservation_filter_date_to']) : (($session->get('search_departure_date') != null) ? $session->get('search_departure_date') : '+2 day');

        $start_timestamp = strtotime($start_date);
        $end_timestamp = strtotime($end_date);


        if (isset($post['top_reservation_filter_date_from'])) {
            $post['reservation_filter_date_from'] = $post['top_reservation_filter_date_from'];
            $post['reservation_filter_date_to'] = $post['top_reservation_filter_date_to'];
        }

        if ($this->getRequest()->getMethod() == 'POST') {
            if (isset($post['reservation_filter_date_from']) && isset($post['reservation_filter_date_to'])) {
                $reservation_filter_date_from = $post['reservation_filter_date_from'];
                $reservation_filter_date_from = explode('/', $reservation_filter_date_from);
                $start_timestamp = mktime(0, 0, 0, $reservation_filter_date_from[1], $reservation_filter_date_from[0], $reservation_filter_date_from[2]);

                $reservation_filter_date_to = $post['reservation_filter_date_to'];
                $reservation_filter_date_to = explode('/', $reservation_filter_date_to);
                $end_timestamp = mktime(0, 0, 0, $reservation_filter_date_to[1], $reservation_filter_date_to[0], $reservation_filter_date_to[2]);
            }
        } else {

        }

        $service_time = $this->get('Time');
        $array_dates = $service_time->datesBetween($start_timestamp, $end_timestamp);

        $array_no_available = array();
        $no_available_days = array();

        $array_prices = array();
        $prices_dates = array();

        foreach ($rooms as $room) {
            foreach ($reservations as $reservation) {

                if ($reservation->getOwnResSelectedRoomId() == $room->getRoomId()) {

                    if ($start_timestamp <= $reservation->getOwnResReservationFromDate()->getTimestamp() &&
                        $end_timestamp >= $reservation->getOwnResReservationToDate()->getTimestamp() && $reservation->getOwnResStatus() == ownershipReservation::STATUS_RESERVED) {

                        $array_no_available[$room->getRoomId()] = $room->getRoomId();
                    }

                    if ($start_timestamp >= $reservation->getOwnResReservationFromDate()->getTimestamp() &&
                        $start_timestamp <= $reservation->getOwnResReservationToDate()->getTimestamp() &&
                        $end_timestamp >= $reservation->getOwnResReservationToDate()->getTimestamp() && $reservation->getOwnResStatus() == ownershipReservation::STATUS_RESERVED) {

                        $array_no_available[$room->getRoomId()] = $room->getRoomId();
                    }

                    if ($start_timestamp <= $reservation->getOwnResReservationFromDate()->getTimestamp() &&
                        $end_timestamp <= $reservation->getOwnResReservationToDate()->getTimestamp() &&
                        $end_timestamp >= $reservation->getOwnResReservationFromDate()->getTimestamp() && $reservation->getOwnResStatus() == ownershipReservation::STATUS_RESERVED) {

                        $array_no_available[$room->getRoomId()] = $room->getRoomId();
                    }

                    if ($start_timestamp >= $reservation->getOwnResReservationFromDate()->getTimestamp() &&
                        $end_timestamp <= $reservation->getOwnResReservationToDate()->getTimestamp() && $reservation->getOwnResStatus() == ownershipReservation::STATUS_RESERVED) {

                        $array_no_available[$room->getRoomId()] = $room->getRoomId();
                    }

                    $array_numbers_check = array();
                    $cont_numbers = 1;
                    foreach ($array_dates as $date) {

                        if ($date >= $reservation->getOwnResReservationFromDate()->getTimestamp() && $date <= $reservation->getOwnResReservationToDate()->getTimestamp() && $reservation->getOwnResStatus() == ownershipReservation::STATUS_RESERVED) {
                            array_push($array_numbers_check, $cont_numbers);
                        }
                        $cont_numbers++;
                    }
                    array_push($no_available_days, array(
                        $room->getRoomId() => $room->getRoomId(),
                        'check' => $array_numbers_check
                    ));
                }
            }
            $total_price_room = 0;
            $prices_dates_temp = array();
            $x = 1;
            /* if ($request->getMethod() != 'POST') {
              //$x = 2;
              } */
            $seasons = $em->getRepository("mycpBundle:season")->getSeasons($start_date, $end_date, $ownership_array['des_id']);
            for ($a = 0; $a < count($array_dates) - $x; $a++) {

                $season_type = $service_time->seasonTypeByDate($seasons, $array_dates[$a]);
                $roomPrice = $room->getPriceBySeasonType($season_type);
                $total_price_room += $roomPrice;
                array_push($prices_dates_temp, $roomPrice);
            }
            array_push($array_prices, $total_price_room);
            array_push($prices_dates, $prices_dates_temp);
        }
        $no_available_days_ready = array();
        foreach ($no_available_days as $item) {
            $keys = array_keys($item);
            if (!isset($no_available_days_ready[$item[$keys[0]]]))
                $no_available_days_ready[$item[$keys[0]]] = array();
            $no_available_days_ready[$item[$keys[0]]] = array_merge($no_available_days_ready[$item[$keys[0]]], $item['check']);
        }

        $array_dates_keys = array();
        $count = 1;
        foreach ($array_dates as $date) {
            $array_dates_keys[$count] = array('day_number' => date('d', $date), 'day_name' => date('D', $date));
            $count++;
        }
        if ($this->getRequest()->getMethod() != 'POST') {
            // array_pop($array_dates_keys);
        }

        $flag_room = 0;
        $price_subtotal = 0;
        $do_operation = true;
        $available_rooms = array();
        $avail_array_prices = array();
        foreach ($rooms as $room_2) {
            foreach ($array_no_available as $no_avail) {
                if ($room_2->getRoomId() == $no_avail) {
                    $do_operation = false;
                }
            }
            if ($do_operation == true) {
                $price_subtotal+=$array_prices[$flag_room];
                array_push($available_rooms, $room_2->getRoomId());
                array_push($avail_array_prices, $array_prices[$flag_room]);
            }
            $do_operation = true;
            $flag_room++;
        }
        //exit();

        /* YANET */
        $em->getRepository('mycpBundle:userHistory')->insert(true, $owner_id, $user_ids);

        $real_category = "";
        if ($ownership_array['category'] == 'Económica')
            $real_category = 'economy';
        else if ($ownership_array['category'] == 'Rango medio')
            $real_category = 'mid_range';
        else if ($ownership_array['category'] == 'Premium')
            $real_category = 'premium';

        $brief_description = Utils::removeNewlines($ownership_array['brief_description']);

        $currentServiceFee = $em->getRepository("mycpBundle:serviceFee")->getCurrent();

        $user = $this->getUser();
        $tourOperator = $em->getRepository("PartnerBundle:paTourOperator")->findOneBy(array("tourOperator" => $user->getUserId()));
        $travelAgency = $tourOperator->getTravelAgency();

        $form = $this->createForm(new paReservationType($this->get('translator'), $travelAgency));

        return $this->render('@Partner/Dashboard/accomodation_detail.html.twig', array(
            'form' => $form->createView(),
            'avail_array_prices' => $avail_array_prices,
            'available_rooms' => $available_rooms,
            'price_subtotal' => $price_subtotal,
            'avail_array_prices' => $avail_array_prices,
            'array_prices' => $array_prices,
            'prices_dates' => $prices_dates,
            'ownership' => $ownership_array,
            'description' => $ownership_array['description'],
            'brief_description' => $brief_description,
            'automaticTranslation' => $ownership_array['autotomaticTranslation'],
            //'similar_houses' => array_slice($similar_houses, 0, 5),
            //'total_similar_houses' => $total_similar_houses,
            'comments' => $comments,
            'friends' => $friends,
            'show_comments_and_friends' => count($total_comments) + count($friends),
            'rooms' => $rooms,
            'gallery_photos' => $own_photos,
            'is_in_favorite' => $ownership_array['is_in_favorites'],
            'array_dates' => $array_dates_keys,
            'post' => $post,
            'reservations' => $array_no_available,
            'no_available_days' => $no_available_days_ready,
            'comments_items_per_page' => $items_per_page,
            'comments_total_items' => $paginator->getTotalItems(),
            'comments_current_page' => $page,
            'can_comment' => $em->getRepository("mycpBundle:comment")->canComment($user_ids["user_id"], $owner_id),
            'can_public_comment' => $em->getRepository("mycpBundle:comment")->canPublicComment($user_ids["user_id"], $owner_id),
            'locale' => $locale,
            'real_category' => $real_category,
            'languages' => $languages,
            'keywords' => $ownership_array['keywords'],
            'locale' => $locale,
            'currentServiceFee' => $currentServiceFee
        ));

    }

    public function addFavoritesAction() {
        $request = $this->getRequest();
        $em = $this->getDoctrine()->getManager();

        $user_ids = $em->getRepository('mycpBundle:user')->getIds($this);
        $favorite_type = $request->request->get("favorite_type");
        $element_id = $request->request->get("element_id");

        $data = array();
        $data['favorite_user_id'] = $user_ids["user_id"];
        $data['favorite_session_id'] = $user_ids["session_id"];
        $data['favorite_ownership_id'] = ($favorite_type == "ownership") ? $element_id : null;
        $data['favorite_destination_id'] = ($favorite_type == "destination") ? $element_id : null;

        $em->getRepository('mycpBundle:favorite')->insert($data);

        $response = $this->renderView('FrontEndBundle:favorite:detailsFavorite.html.twig', array(
            'is_in_favorite' => $em->getRepository('mycpBundle:favorite')->isInFavorite($element_id, ($favorite_type == "ownership"), $user_ids["user_id"], $user_ids["session_id"]),
            'favorite_type' => $favorite_type,
            'element_id' => $element_id
        ));

        return new Response($response, 200);
    }

    public function removeFavoritesAction() {
        $request = $this->getRequest();
        $em = $this->getDoctrine()->getManager();

        $user_ids = $em->getRepository('mycpBundle:user')->getIds($this);
        $favorite_type = $request->request->get("favorite_type");
        $element_id = $request->request->get("element_id");

        $data = array();
        $data['favorite_user_id'] = $user_ids["user_id"];
        $data['favorite_session_id'] = $user_ids["session_id"];
        $data['favorite_ownership_id'] = ($favorite_type == "ownership") ? $element_id : null;
        $data['favorite_destination_id'] = ($favorite_type == "destination") ? $element_id : null;

        $em->getRepository('mycpBundle:favorite')->delete($data);

        $response = $this->renderView('FrontEndBundle:favorite:detailsFavorite.html.twig', array(
            'is_in_favorite' => $em->getRepository('mycpBundle:favorite')->isInFavorite($element_id, ($favorite_type == "ownership"), $user_ids["user_id"], $user_ids["session_id"]),
            'favorite_type' => $favorite_type,
            'element_id' => $element_id
        ));

        return new Response($response, 200);
    }

    public function indexBookingDetailAction($id_reservation, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $reservation = $em->getRepository('mycpBundle:generalReservation')->find($id_reservation);
        $ownership_reservations = $reservation->getOwnReservations();

        $service_time = $this->get('time');
        $array_nights = array();
        $array_total_prices = array();
        $rooms = array();
        $curr = $this->getCurr($request);

        foreach ($ownership_reservations as $res) {
            $nights = $res->getNights($service_time);
            array_push($rooms, $em->getRepository('mycpBundle:room')->find($res->getOwnResSelectedRoomId()));
            array_push($array_nights, $nights);

            $total_price = ($res->getPriceTotal($service_time) * $curr['change']).$curr['code'];
            array_push($array_total_prices, $total_price);
        }

        return new JsonResponse([
            'success' => true,
            'id' => 'id_dashboard_booking_detail_'.$id_reservation,
            'html' => $this->renderView('PartnerBundle:Dashboard:details.html.twig', array(
                'id_res'=>$id_reservation,
                'cas'=>"CAS.$id_reservation",
                'reservation'=>$reservation,
                'reservations' => $ownership_reservations,
                'rooms' => $rooms,
                'nights' => $array_nights,
                'total_prices'=>$array_total_prices
            )),
            'msg' => 'Vista del detalle de una reserva']);
    }

    public function cancelFromAgencyAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $reservationId = $request->get("reservationId");

        $generalReservation = $em->getRepository('mycpBundle:generalReservation')->find($reservationId);
        $ownershipReservations = $generalReservation->getOwn_reservations();
        foreach($ownershipReservations as $ownRes){
            $ownRes->setOwnResStatus(ownershipReservation::STATUS_CANCELLED);
            $em->persist($ownRes);
        }
        $generalReservation->setGenResStatus(generalReservation::STATUS_CANCELLED);
        $generalReservation->setGenResStatusDate(new \DateTime());
        $generalReservation->setCanceledBy($this->getUser());
        $em->persist($generalReservation);

        $em->flush();

        return new JsonResponse([
            'success' => true,
            'message' => "Cancelado correctamente"
        ]);
    }

    public function cancelReservationAction($idReservation, Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        /*$generalReservation = $em->getRepository('mycpBundle:generalReservation')->find($idReservation);
        $ownershipReservations = $generalReservation->getOwn_reservations();
        foreach($ownershipReservations as $ownRes){
            $ownRes->setOwnResStatus(ownershipReservation::STATUS_CANCELLED);
            $em->persist($ownRes);
        }
        $generalReservation->setGenResStatus(generalReservation::STATUS_CANCELLED);
        $generalReservation->setGenResStatusDate(new \DateTime());
        $generalReservation->setCanceledBy($this->getUser());
        $em->persist($generalReservation);

        $em->flush();

        return $this->redirect($this->generateUrl());*/
        $results = $em->getRepository('mycpBundle:ownership')->getSearchNumbers();

        $categories_own_list = $results["categories"];
        $types_own_list = $results["types"];
        $prices_own_list = $results["prices"];
        $statistics_own_list = $em->getRepository('mycpBundle:ownership')->getSearchStatistics();

        $user = $this->getUser();
        $tourOperator = $em->getRepository("PartnerBundle:paTourOperator")->findOneBy(array("tourOperator" => $user->getUserId()));
        $travelAgency = $tourOperator->getTravelAgency();

        $form = $this->createForm(new paReservationType($this->get('translator'), $travelAgency));
        $formFilterOwnerShip = $this->createForm(new FilterOwnershipType($this->get('translator'), array()));
        return $this->render('PartnerBundle:Backend:index.html.twig', array(
            "locale" => "es",
            "owns_categories" => null,
            "autocomplete_text_list" => null,
            "owns_prices" => $prices_own_list,
            "formFilterOwnerShip"=>$formFilterOwnerShip->createView(),
            'form'=>$form->createView()
        ));
    }

    public function getThumbnailForSearcherAction($photo, $title){
        list($width, $height) = getimagesize(realpath("uploads/ownershipImages/" . $photo));

        return $this->render('PartnerBundle:ownership:searchImage.html.twig', array(
            'title' => $title,
            'photo' => $photo,
            'taller' => ($height > $width)
        ));
    }

    public function downloadVoucherAction($bookingId){
        $pathToFile = $this->container->getParameter("configuration.dir.vouchers");

        /*$pathToCont = $pathToFile."download_cont.txt";
        $file = fopen($pathToCont,"a");
        fclose($file);
        if (is_writeable($pathToCont)){
            $arrayFile=file($pathToCont);
            $arrayFile[0] = (count($arrayFile) <= 0) ? (1) : (++$arrayFile[0]);
            $file=fopen($pathToCont,"w");
            fwrite($file,$arrayFile[0]);
            fclose($file);
        }*/

        $em = $this->getDoctrine()->getManager();
        $booking = $em->getRepository('mycpBundle:booking')->find($bookingId);

        $name = 'voucher'.$booking->getBookingUserId().'_'.$booking->getBookingId().'.pdf';
        $response = new BinaryFileResponse($pathToFile.$name);
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT,$name);
        return $response;
    }
}
