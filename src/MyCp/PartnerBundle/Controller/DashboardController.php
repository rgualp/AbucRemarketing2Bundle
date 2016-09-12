<?php

namespace MyCp\PartnerBundle\Controller;

use MyCp\FrontEndBundle\Helpers\Utils;
use MyCp\mycpBundle\Entity\generalReservation;
use MyCp\PartnerBundle\Form\paReservationType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

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


        $date = new \DateTime();
        $date_a = $date->format('d-m-Y');
        $date->modify('+5 day');
        $date_b = $date->format('d-m-Y');
        $filters['from_between'] = array($date_a, $date_b);

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

        $form = $this->createForm(new paReservationType($this->get('translator')));

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


}
