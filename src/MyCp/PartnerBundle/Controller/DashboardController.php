<?php

namespace MyCp\PartnerBundle\Controller;

use MyCp\FrontEndBundle\Helpers\Utils;
use MyCp\mycpBundle\Entity\booking;
use MyCp\mycpBundle\Entity\generalReservation;
use MyCp\mycpBundle\Entity\ownershipReservation;
use MyCp\PartnerBundle\Entity\paCancelPayment;
use MyCp\PartnerBundle\Entity\paPendingPaymentAccommodation;
use MyCp\PartnerBundle\Entity\paPendingPaymentAgency;
use MyCp\PartnerBundle\Form\FilterOwnershipType;
use MyCp\PartnerBundle\Form\paReservationExtendedType;
use MyCp\PartnerBundle\Form\paReservationType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use MyCp\mycpBundle\Entity\cart;
use Abuc\RemarketingBundle\Event\JobEvent;
use MyCp\mycpBundle\Helpers\Dates;

class DashboardController extends Controller
{
    const SUCCESS = 'success';

    const MSG_RESERVAS_PENDIENTES = 'Vista del listado de reservas PENDIENTES';

    const VAR_TOUR_OPERATOR = "tourOperator";

    const VAR_IS_SPECIAL = 'isSpecial';


    /**
     * BookingPending
     */

    public function indexBookingPendingAction()
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $tourOperator = $em->getRepository("PartnerBundle:paTourOperator")->findOneBy(array(self::VAR_TOUR_OPERATOR => $user->getUserId()));
        $travelAgency = $tourOperator->getTravelAgency();
        $isSpecial = $travelAgency->getAgencyPackages()[0]->getPackage()->isSpecial();
        return new JsonResponse(array(
            self::SUCCESS => true,
            'id' => 'id_dashboard_booking_pending',
            'html' => $this->renderView('PartnerBundle:Dashboard:booking_pending.html.twig', array(self::VAR_IS_SPECIAL => $isSpecial)),
            'msg' => self::MSG_RESERVAS_PENDIENTES
        ));
    }

    /**
     * Pendientes
     */
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
        $curr = $this->getCurr($request);
        foreach ($reservations as $reservation) {
            $arrTmp = array();
            $arrTmp['id'] = $reservation->getGenResId();
            $arrTmp['data'] = array(
                'id' => $reservation->getGenResId(),
                'cas' => '' . $reservation->getGenResId(),
                'from' => $reservation->getGenResFromDate()->format('d-m-Y'),
                'to' => $reservation->getGenResToDate()->format('d-m-Y'),
                'own_mcp_code' => $reservation->getGenResOwnId()->getOwnMcpCode(),
                'own_name' => $reservation->getGenResOwnId()->getOwnName(),
                'destination' => $reservation->getGenResOwnId()->getOwnDestination()->getDesName(),
                'client_dates' => $reservation->getTravelAgencyDetailReservations()->first()->getReservation()->getClient()->getFullName(),
                'reference' => $reservation->getTravelAgencyDetailReservations()->first()->getReservation()->getReference(),

                'date' => $reservation->getGenResDate()->format('d-m-Y'));

            $ownReservations = $reservation->getOwn_reservations();
            $arrTmp['data']['rooms'] = array();
            if (!$ownReservations->isEmpty()) {
                $ownReservation = $ownReservations->first();
                $timeService = $this->get('time');

                do {

                    $nights = $timeService->nights($ownReservation->getOwnResReservationFromDate()->getTimestamp(), $ownReservation->getOwnResReservationToDate()->getTimestamp());
                    $totalPrice = 0;
                    if ($ownReservation->getOwnResNightPrice() > 0) {
                        $totalPrice += $ownReservation->getOwnResNightPrice() * $nights;
                    } else {
                        $totalPrice += $ownReservation->getOwnResTotalInSite();
                    }
                    $arrTmp['data']['rooms'][] = array(
                        'type' => $ownReservation->getOwnResRoomType(),
                        'adults' => $ownReservation->getOwnResCountAdults(),
                        'totalPrice' => ($totalPrice * $curr['change']),
                        'childrens' => $ownReservation->getOwnResCountChildrens(),
                        'curr_code' => $curr['code']
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

    public function getReservationsData($filters, $start, $limit, $draw, $status)
    {
        $user = $this->getUser();

        $em = $this->getDoctrine()->getManager();
        $repository = $em->getRepository('mycpBundle:generalReservation');
        $userrepo = $em->getRepository('mycpBundle:user');
        $touroperators = array();
        $touroperators = $userrepo->getAllTourOperators($touroperators, $user);

        #region PAGINADO
        $paginator = $repository->getReservationsPartner($user->getUserId(), $status, $filters, $start, $limit, $touroperators);;
        $reservations = $paginator['data'];
        #endregion PAGINADO

        $data = array();
        $data['draw'] = $draw;
        $data['iTotalRecords'] = $paginator['count'];
        $data['iTotalDisplayRecords'] = $paginator['count'];
        $data['aaData'] = $reservations;

        return $data;
    }

    public function getCurr(Request $request)
    {
        $session = $request->getSession();
        $a = $session->get("curr_rate");
        $b = $session->get("curr_symbol");
        $c = $session->get("curr_acronym");

        return array("change" => $a, "code" => $c);
    }
    /*End BookingAvailability*/

    /*BookingNotavailability*/

    public function indexBookingAvailabilityAction()
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $tourOperator = $em->getRepository("PartnerBundle:paTourOperator")->findOneBy(array(self::VAR_TOUR_OPERATOR => $user->getUserId()));
        $travelAgency = $tourOperator->getTravelAgency();
        $isSpecial = $travelAgency->getAgencyPackages()[0]->getPackage()->isSpecial();
        return new JsonResponse(array(
            self::SUCCESS => true,
            'id' => 'id_dashboard_booking_availability',
            'html' => $this->renderView('PartnerBundle:Dashboard:booking_availability.html.twig', array(self::VAR_IS_SPECIAL => $isSpecial)),
            'msg' => 'Vista del listado de reservas DISPONIBLES'
        ));
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
                'id' => $reservation->getGenResId(),
                'user_id' => $reservation->getGenResUserId()->getUsername(),
                'cas' => '' . $reservation->getGenResId(),
                'from' => $reservation->getGenResFromDate()->format('d-m-Y'),
                'to' => $reservation->getGenResToDate()->format('d-m-Y'),
                'own_mcp_code' => $reservation->getGenResOwnId()->getOwnMcpCode(),
                'destination' => $reservation->getGenResOwnId()->getOwnDestination()->getDesName(),
                'date' => $reservation->getGenResDate()->format('d-m-Y'),
                'reference' => $reservation->getTravelAgencyDetailReservations()->first()->getReservation()->getReference(),

                'client_dates' => $reservation->getTravelAgencyDetailReservations()->first()->getReservation()->getClient()->getFullName(),
                'own_name' => $reservation->getGenResOwnId()->getOwnName());

            $ownReservations = $reservation->getOwn_reservations();
            $arrTmp['data']['rooms'] = array();
            if (!$ownReservations->isEmpty()) {
                $ownReservation = $ownReservations->first();
                $curr = $this->getCurr($request);
                do {
                    $nights = $timeService->nights($ownReservation->getOwnResReservationFromDate()->getTimestamp(), $ownReservation->getOwnResReservationToDate()->getTimestamp());
                    $totalPrice = 0;
                    if ($ownReservation->getOwnResNightPrice() > 0) {
                        $totalPrice += $ownReservation->getOwnResNightPrice() * $nights;
                    } else {
                        $totalPrice += $ownReservation->getOwnResTotalInSite();
                    }

                    $arrTmp['data']['rooms'][] = array(
                        'type' => $ownReservation->getOwnResRoomType(),
                        'adults' => $ownReservation->getOwnResCountAdults(),
                        'childrens' => $ownReservation->getOwnResCountChildrens(),
                        'totalPrice' => ($totalPrice * $curr['change']),
                        'curr_code' => $curr['code']
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

    public function indexBookingNotavailabilityAction()
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $tourOperator = $em->getRepository("PartnerBundle:paTourOperator")->findOneBy(array(self::VAR_TOUR_OPERATOR => $user->getUserId()));
        $travelAgency = $tourOperator->getTravelAgency();
        $isSpecial = $travelAgency->getAgencyPackages()[0]->getPackage()->isSpecial();
        return new JsonResponse(array(
            self::SUCCESS => true,
            'id' => 'id_dashboard_booking_notavailability',
            'html' => $this->renderView('PartnerBundle:Dashboard:booking_notavailability.html.twig', array(self::VAR_IS_SPECIAL => $isSpecial)),
            'msg' => 'Vista del listado de reservas NO DISPONIBLES'
        ));
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
        $curr = $this->getCurr($request);
        $timeService = $this->get('time');

        foreach ($reservations as $reservation) {
            $arrTmp = array();
            $arrTmp['id'] = $reservation->getGenResId();
            $arrTmp['data'] = array(
                'id' => $reservation->getGenResId(),
                'user_id' => $reservation->getGenResUserId()->getUsername(),
                'cas' => '' . $reservation->getGenResId(),
                'from' => $reservation->getGenResFromDate()->format('d-m-Y'),
                'to' => $reservation->getGenResToDate()->format('d-m-Y'),
                'own_mcp_code' => $reservation->getGenResOwnId()->getOwnMcpCode(),
                'destination' => $reservation->getGenResOwnId()->getOwnDestination()->getDesName(),
                'date' => $reservation->getGenResDate()->format('d-m-Y'),
                'client_dates' => $reservation->getTravelAgencyDetailReservations()->first()->getReservation()->getClient()->getFullName(),
                'reference' => $reservation->getTravelAgencyDetailReservations()->first()->getReservation()->getReference(),

                'own_name' => $reservation->getGenResOwnId()->getOwnName());

            $ownReservations = $reservation->getOwn_reservations();
            $arrTmp['data']['rooms'] = array();
            if (!$ownReservations->isEmpty()) {
                $ownReservation = $ownReservations->first();

                do {
                    $nights = $timeService->nights($ownReservation->getOwnResReservationFromDate()->getTimestamp(), $ownReservation->getOwnResReservationToDate()->getTimestamp());
                    $totalPrice = 0;
                    if ($ownReservation->getOwnResNightPrice() > 0) {
                        $totalPrice += $ownReservation->getOwnResNightPrice() * $nights;
                    } else {
                        $totalPrice += $ownReservation->getOwnResTotalInSite();
                    }

                    $arrTmp['data']['rooms'][] = array(
                        'type' => $ownReservation->getOwnResRoomType(),
                        'adults' => $ownReservation->getOwnResCountAdults(),
                        'childrens' => $ownReservation->getOwnResCountChildrens(),
                        'totalPrice' => ($totalPrice * $curr['change']),
                        'curr_code' => $curr['code']
                    );
                    $ownReservation = $ownReservations->next();
                } while ($ownReservation);
            }

            $data['aaData'][] = $arrTmp;
        }

        return new JsonResponse($data);
    }
    /*End BookingReserved*/

    /*Pending payment*/

    public function indexBookingReservedAction()
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $tourOperator = $em->getRepository("PartnerBundle:paTourOperator")->findOneBy(array(self::VAR_TOUR_OPERATOR => $user->getUserId()));
        $travelAgency = $tourOperator->getTravelAgency();
        $isSpecial = $travelAgency->getAgencyPackages()[0]->getPackage()->isSpecial();

        return new JsonResponse(array(
            self::SUCCESS => true,
            'id' => 'id_dashboard_booking_reserved',
            'html' => $this->renderView('PartnerBundle:Dashboard:booking_reserved.html.twig', array(self::VAR_IS_SPECIAL => $isSpecial)),
            'msg' => self::MSG_RESERVAS_PENDIENTES));
    }

    public function listBookingReservedAction(Request $request)
    {
        $filters = $request->get('booking_reserved_filter_form');
        $filters = (isset($filters)) ? ($filters) : (array());
        $curr = $this->getCurr($request);
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
                'id' => $reservation->getGenResId(),
                'user_id' => $reservation->getGenResUserId()->getUsername(),
                'cas' => '' . $reservation->getGenResId(),
                'from' => $reservation->getGenResFromDate()->format('d-m-Y'),
                'to' => $reservation->getGenResToDate()->format('d-m-Y'),
                'canBeCanceled' => ($reservation->getGenResFromDate() > new \DateTime()),
                'own_mcp_code' => $reservation->getGenResOwnId()->getOwnMcpCode(),
                'destination' => $reservation->getGenResOwnId()->getOwnDestination()->getDesName(),
                'date' => $reservation->getGenResDate()->format('d-m-Y'),
                'client_dates' => $reservation->getTravelAgencyDetailReservations()->first()->getReservation()->getClient()->getFullName(),
                'own_name' => $reservation->getGenResOwnId()->getOwnName(),
                'reference' => $reservation->getTravelAgencyDetailReservations()->first()->getReservation()->getReference()
            );

            $ownReservations = $reservation->getOwn_reservations();
            $arrTmp['data']['rooms'] = array();

            if (!$ownReservations->isEmpty()) {
                $ownReservation = $ownReservations->first();

                do {
                    $nights = $timeService->nights($ownReservation->getOwnResReservationFromDate()->getTimestamp(), $ownReservation->getOwnResReservationToDate()->getTimestamp());
                    $totalPrice = 0;
                    if ($ownReservation->getOwnResNightPrice() > 0) {
                        $totalPrice += $ownReservation->getOwnResNightPrice() * $nights;
                    } else {
                        $totalPrice += $ownReservation->getOwnResTotalInSite();
                    }

                    $arrTmp['data']['rooms'][] = array(
                        'type' => $ownReservation->getOwnResRoomType(),
                        'adults' => $ownReservation->getOwnResCountAdults(),
                        'childrens' => $ownReservation->getOwnResCountChildrens(),
                        'totalPrice' => ($totalPrice * $curr['change']),
                        'curr_code' => $curr['code'],
                        'booking' => array(
                            'code' => $ownReservation->getOwnResReservationBooking()->getBookingId(),
                            'date' => $ownReservation->getOwnResReservationBooking()->getPayments()->first()->getCreated()->format('d-m-Y')
                        )
                    );
                    $ownReservation = $ownReservations->next();
                } while ($ownReservation);
            }

            $data['aaData'][] = $arrTmp;

        }
        $datas = $this->getReservationsData($filters, 1, false, $draw, generalReservation::STATUS_RESERVED);
        $reservationes = $datas['aaData'];
        $totalprices = 0;

        foreach ($reservationes as $reserva) {
            $id = $reserva->getGenResId();
            $em = $this->getDoctrine()->getManager();
            $reservation = $em->getRepository('mycpBundle:generalReservation')->find($id);
            $ownership_reservations = $reservation->getOwnReservations();
            $service_time = $this->get('time');
            $price_temp = 0;
            foreach ($ownership_reservations as $res) {
                $total_price = $res->getPriceTotal($service_time);
                $price_temp += $total_price;
            }
            $totalprices += round((($price_temp + ($price_temp * 0.1) + ($price_temp + ($price_temp * 0.1)) * 0.1) * $curr['change']), 2);

        }

        $data['prices'] = $totalprices . ' ' . $curr['code'];

        return new JsonResponse($data);
    }
    /*End BookingReserved*/

    /*BookingBeaten*/

    public function indexBookingPendingPaymentAction()
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $tourOperator = $em->getRepository("PartnerBundle:paTourOperator")->findOneBy(array(self::VAR_TOUR_OPERATOR => $user->getUserId()));
        $travelAgency = $tourOperator->getTravelAgency();
        $isSpecial = $travelAgency->getAgencyPackages()[0]->getPackage()->isSpecial();
        return new JsonResponse(array(
            self::SUCCESS => true,
            'id' => 'id_dashboard_booking_pending_payment',
            'html' => $this->renderView('PartnerBundle:Dashboard:booking_pending_payment.html.twig', array(self::VAR_IS_SPECIAL => $isSpecial)),
            'msg' => 'Vista del listado de reservas PENDIENTES DE PAGO'
        ));
    }

    public function listBookingPendingPaymentAction(Request $request)
    {
        $filters = $request->get('booking_pending_payment_filter_form');
        $filters = (isset($filters)) ? ($filters) : (array());

        #region PAGINADO
        $start = $request->get('start', 0);
        $limit = $request->get('length', 10);
        $draw = $request->get('draw') + 1;
        #endregion PAGINADO

        $data = $this->getReservationsData($filters, $start, $limit, $draw, generalReservation::STATUS_PENDING_PAYMENT_PARTNER);
        $reservations = $data['aaData'];
        $data['aaData'] = array();

        $timeService = $this->get('time');

        foreach ($reservations as $reservation) {
            $arrTmp = array();
            $arrTmp['id'] = $reservation->getGenResId();
            $arrTmp['data'] = array(
                'id' => $reservation->getGenResId(),
                'user_id' => $reservation->getGenResUserId()->getUsername(),
                'cas' => '' . $reservation->getGenResId(),
                'from' => $reservation->getGenResFromDate()->format('d-m-Y'),
                'to' => $reservation->getGenResToDate()->format('d-m-Y'),
                'canBeCanceled' => ($reservation->getGenResFromDate() > new \DateTime()),
                'own_mcp_code' => $reservation->getGenResOwnId()->getOwnMcpCode(),
                'destination' => $reservation->getGenResOwnId()->getOwnDestination()->getDesName(),
                'date' => $reservation->getGenResDate()->format('d-m-Y'),
                'client_dates' => $reservation->getTravelAgencyDetailReservations()->first()->getReservation()->getClient()->getFullName(),
                'own_name' => $reservation->getGenResOwnId()->getOwnName(),
                'reference' => $reservation->getTravelAgencyDetailReservations()->first()->getReservation()->getReference()
            );

            $ownReservations = $reservation->getOwn_reservations();
            $arrTmp['data']['rooms'] = array();
            if (!$ownReservations->isEmpty()) {
                $ownReservation = $ownReservations->first();
                $curr = $this->getCurr($request);
                do {
                    $nights = $timeService->nights($ownReservation->getOwnResReservationFromDate()->getTimestamp(), $ownReservation->getOwnResReservationToDate()->getTimestamp());
                    $totalPrice = 0;
                    if ($ownReservation->getOwnResNightPrice() > 0) {
                        $totalPrice += $ownReservation->getOwnResNightPrice() * $nights;
                    } else {
                        $totalPrice += $ownReservation->getOwnResTotalInSite();
                    }

                    $arrTmp['data']['rooms'][] = array(
                        'type' => $ownReservation->getOwnResRoomType(),
                        'adults' => $ownReservation->getOwnResCountAdults(),
                        'childrens' => $ownReservation->getOwnResCountChildrens(),
                        'totalPrice' => (($totalPrice + ($totalPrice * 0.1) + (($totalPrice + ($totalPrice * 0.1)) * 0.1)) * $curr['change']),
                        'curr_code' => $curr['code'],
                        'booking' => array(
                            'code' => $ownReservation->getOwnResReservationBooking()->getBookingId(),
                            'date' => $ownReservation->getOwnResReservationBooking()->getPayments()->first()->getCreated()->format('d-m-Y')
                        )
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

    public function indexBookingBeatenAction()
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $tourOperator = $em->getRepository("PartnerBundle:paTourOperator")->findOneBy(array(self::VAR_TOUR_OPERATOR => $user->getUserId()));
        $travelAgency = $tourOperator->getTravelAgency();
        $isSpecial = $travelAgency->getAgencyPackages()[0]->getPackage()->isSpecial();
        return new JsonResponse(array(
            self::SUCCESS => true,
            'id' => 'id_dashboard_booking_beaten',
            'html' => $this->renderView('PartnerBundle:Dashboard:booking_beaten.html.twig', array(self::VAR_IS_SPECIAL => $isSpecial)),
            'msg' => 'Vista del listado de reservas VENCIDAS'
        ));
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
                'id' => $reservation->getGenResId(),
                'user_id' => $reservation->getGenResUserId()->getUsername(),
                'cas' => '' . $reservation->getGenResId(),
                'from' => $reservation->getGenResFromDate()->format('d-m-Y'),
                'to' => $reservation->getGenResToDate()->format('d-m-Y'),
                'own_mcp_code' => $reservation->getGenResOwnId()->getOwnMcpCode(),
                'destination' => $reservation->getGenResOwnId()->getOwnDestination()->getDesName(),
                'date' => $reservation->getGenResDate()->format('d-m-Y'),
                'client_dates' => $reservation->getTravelAgencyDetailReservations()->first()->getReservation()->getClient()->getFullName(),
                'reference' => $reservation->getTravelAgencyDetailReservations()->first()->getReservation()->getReference(),

                'own_name' => $reservation->getGenResOwnId()->getOwnName());

            $ownReservations = $reservation->getOwn_reservations();
            $arrTmp['data']['rooms'] = array();
            if (!$ownReservations->isEmpty()) {
                $ownReservation = $ownReservations->first();
                $curr = $this->getCurr($request);
                do {
                    $nights = $timeService->nights($ownReservation->getOwnResReservationFromDate()->getTimestamp(), $ownReservation->getOwnResReservationToDate()->getTimestamp());
                    $totalPrice = 0;
                    if ($ownReservation->getOwnResNightPrice() > 0) {
                        $totalPrice += $ownReservation->getOwnResNightPrice() * $nights;
                    } else {
                        $totalPrice += $ownReservation->getOwnResTotalInSite();
                    }

                    $arrTmp['data']['rooms'][] = array(
                        'type' => $ownReservation->getOwnResRoomType(),
                        'totalPrice' => ($totalPrice * $curr['change']),
                        'curr_code' => $curr['code']/*,
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
    /*End BookingCanceled*/

    /*BookingCheckin*/

    public function indexBookingCanceledAction()
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $tourOperator = $em->getRepository("PartnerBundle:paTourOperator")->findOneBy(array(self::VAR_TOUR_OPERATOR => $user->getUserId()));
        $travelAgency = $tourOperator->getTravelAgency();
        $isSpecial = $travelAgency->getAgencyPackages()[0]->getPackage()->isSpecial();
        return new JsonResponse(array(
            self::SUCCESS => true,
            'id' => 'id_dashboard_booking_canceled',
            'html' => $this->renderView('PartnerBundle:Dashboard:booking_canceled.html.twig', array(self::VAR_IS_SPECIAL => $isSpecial)),
            'msg' => 'Vista del listado de reservas CANCELADAS'
        ));
    }

    public function listBookingCanceledAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
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
        $curr = $this->getCurr($request);
        foreach ($reservations as $reservation) {
            $arrTmp = array();
            $arrTmp['id'] = $reservation->getGenResId();
            $pendingPayment = (count($reservation->getPendingPayments()) > 0) ? $reservation->getPendingPayments()->first() : null;
            $payment = ($pendingPayment != null) ? $pendingPayment->getBooking()->getPayments()->first() : null;
            $paymentCurrencyChangeRate = ($payment != null) ? $payment->getCurrentCucChangeRate() : null;
            $currency = ($pendingPayment != null) ? $pendingPayment->getBooking()->getPayments()->first()->getCurrency() : null;
            $currencyEntity = ($currency != null) ? $em->getRepository("mycpBundle:currency")->find($currency) : null;


            $arrTmp['data'] = array(
                'id' => $reservation->getGenResId(),
                'user_id' => $reservation->getGenResUserId()->getUsername(),
                'cas' => '' . $reservation->getGenResId(),
                'from' => $reservation->getGenResFromDate()->format('d-m-Y'),
                'to' => $reservation->getGenResToDate()->format('d-m-Y'),
                'own_mcp_code' => $reservation->getGenResOwnId()->getOwnMcpCode(),
                'destination' => $reservation->getGenResOwnId()->getOwnDestination()->getDesName(),
                'date' => $reservation->getGenResDate()->format('d-m-Y'),
                'client_dates' => $reservation->getTravelAgencyDetailReservations()->first()->getReservation()->getClient()->getFullName(),
                'reference' => ($reservation->getTravelAgencyDetailReservations()->first()->getReservation()->getReference() != null && $reservation->getTravelAgencyDetailReservations()->first()->getReservation()->getReference() != '') ? $reservation->getTravelAgencyDetailReservations()->first()->getReservation()->getReference() : '-',

                'own_name' => $reservation->getGenResOwnId()->getOwnName(),
                'cancelDate' => ($reservation != null) ? $reservation->getGenResStatusDate()->format('d-m-Y') : '-',
                'cancelAmount' => ($pendingPayment != null && $paymentCurrencyChangeRate != null) ? number_format($pendingPayment->getAmount() * $paymentCurrencyChangeRate, 2) : 0,
                'cancelCurrencyCode' => ($currencyEntity != null) ? $currencyEntity->getCurrCode() : 'EUR'
            );

            $ownReservations = $reservation->getOwn_reservations();
            $arrTmp['data']['rooms'] = array();
            if (!$ownReservations->isEmpty()) {
                $ownReservation = $ownReservations->first();

                do {
                    $nights = $timeService->nights($ownReservation->getOwnResReservationFromDate()->getTimestamp(), $ownReservation->getOwnResReservationToDate()->getTimestamp());
                    $totalPrice = 0;
                    if ($ownReservation->getOwnResNightPrice() > 0) {
                        $totalPrice += $ownReservation->getOwnResNightPrice() * $nights;
                        //$initialPayment += $res->getOwnResNightPrice() * $nights * $comission;
                    } else {
                        $totalPrice += $ownReservation->getOwnResTotalInSite();
                        //$initialPayment += $res->getOwnResTotalInSite() * $comission;
                    }
                    if ($ownReservation->getOwnResReservationBooking() == null) {
                        $booking_code = '';
                        $booking_date = '';
                        $type = '';
                    } else {
                        $booking_code = $ownReservation->getOwnResReservationBooking()->getBookingId();
                        $booking_date = $ownReservation->getOwnResReservationBooking()->getPayments()->first()->getCreated()->format('d-m-Y');
                        $type = $ownReservation->getOwnResRoomType();
                    }


                    $arrTmp['data']['rooms'][] = array(
                        'type' => $type,
                        'totalPrice' => ($totalPrice * $curr['change']),
                        'curr_code' => $curr['code'],
                        'booking' => array(
                            'code' => $booking_code,
                            'date' => $booking_date
                        )
                        /*,
                        'booking'=>array(
                            'code'=>$ownReservation->getOwnResReservationBooking()->getBookingId(),
                        )*/
                    );
                    $ownReservation = $ownReservations->next();
                } while ($ownReservation);
            }

            $data['aaData'][] = $arrTmp;
        }
        $datas = $this->getReservationsData($filters, 1, false, $draw, generalReservation::STATUS_CANCELLED);
        $reservationes = $datas['aaData'];
        $totalprices = 0;

        foreach ($reservationes as $reserva) {
            $id = $reserva->getGenResId();
            $em = $this->getDoctrine()->getManager();
            $reservation = $em->getRepository('mycpBundle:generalReservation')->find($id);
            $ownership_reservations = $reservation->getOwnReservations();
            $service_time = $this->get('time');
            $price_temp = 0;
            foreach ($ownership_reservations as $res) {
                $total_price = $res->getPriceTotal($service_time);
                $price_temp += $total_price;
            }
            $totalprices += round((($price_temp + ($price_temp * 0.1) + ($price_temp + ($price_temp * 0.1)) * 0.1) * $curr['change']), 2);
        }

        $data['prices'] = $totalprices . ' ' . $curr['code'];;
        return new JsonResponse($data);
    }

    public function indexBookingCheckinAction()
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $tourOperator = $em->getRepository("PartnerBundle:paTourOperator")->findOneBy(array(self::VAR_TOUR_OPERATOR => $user->getUserId()));
        $travelAgency = $tourOperator->getTravelAgency();
        $isSpecial = $travelAgency->getAgencyPackages()[0]->getPackage()->isSpecial();
        return new JsonResponse(array(
            self::SUCCESS => true,
            'id' => 'id_dashboard_booking_checkin',
            'html' => $this->renderView('PartnerBundle:Dashboard:booking_checkin.html.twig', array(self::VAR_IS_SPECIAL => $isSpecial)),
            'msg' => 'Vista del listado de reservas reservadas Checkin'
        ));
    }
    /*End BookingCheckin*/

    /*BookingProccess*/

    public function listBookingCheckinAction(Request $request)
    {
        $filters = $request->get('booking_checkin_filter_form');
        $filters = (isset($filters)) ? ($filters) : (array());

        $from = (array_key_exists('from', $filters) && isset($filters['from']));
        $to = (array_key_exists('to', $filters) && isset($filters['to']));
        if (!$from && !$to) {
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
                'id' => $reservation->getGenResId(),
                'user_id' => $reservation->getGenResUserId()->getUsername(),
                'cas' => '' . $reservation->getGenResId(),
                'from' => $reservation->getGenResFromDate()->format('d-m-Y') . " $h",
                'to' => $reservation->getGenResToDate()->format('d-m-Y'),
                'own_mcp_code' => $reservation->getGenResOwnId()->getOwnMcpCode(),
                'destination' => $reservation->getGenResOwnId()->getOwnDestination()->getDesName(),
                'date' => $reservation->getGenResDate()->format('d-m-Y'),
                'client_dates' => $reservation->getTravelAgencyDetailReservations()->first()->getReservation()->getClient()->getFullName(),
                'own_name' => $reservation->getGenResOwnId()->getOwnName(),
                'reference' => $reservation->getTravelAgencyDetailReservations()->first()->getReservation()->getReference()

                /*,
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
                        'type' => $ownReservation->getOwnResRoomType(),
                        'totalPrice' => ($totalPrice * $curr['change']),
                        'totalPriceInHome' => $ownReservation->getPricePerInHome($timeService) * $curr['change'],
                        'totalPriceInHomeX' => $ownReservation->getPricePerInHome($timeService),
                        'curr_code' => $curr['code'],
                        'adults' => $ownReservation->getOwnResCountAdults(),
                        'childrens' => $ownReservation->getOwnResCountChildrens(),
                        'booking' => array(
                            'code' => $ownReservation->getOwnResReservationBooking()->getBookingId(),
                            'date' => $ownReservation->getOwnResReservationBooking()->getPayments()->first()->getCreated()->format('d-m-Y')
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
        if (!$from && !$to) {
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

        if (count($reservations)) {
            $exporter = $this->get("mycp.service.export_to_excel");
            return $exporter->exportReservationsCheckinAg($reservations, new \DateTime(), $curr);
        }
    }

    public function indexBookingProccessAction()
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $tourOperator = $em->getRepository("PartnerBundle:paTourOperator")->findOneBy(array(self::VAR_TOUR_OPERATOR => $user->getUserId()));
        $travelAgency = $tourOperator->getTravelAgency();
        $isSpecial = $travelAgency->getAgencyPackages()[0]->getPackage()->isSpecial();
        return new JsonResponse(array(
            self::SUCCESS => true,
            'id' => 'id_dashboard_booking_proccess',
            'html' => $this->renderView('PartnerBundle:Dashboard:booking_proccess.html.twig', array(self::VAR_IS_SPECIAL => $isSpecial)),
            'msg' => 'Vista del listado de reservas en Proccess'
        ));
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
                'id' => $reservation->getId(),
                'from' => $reservation->getDateFrom()->format('d-m-Y'),
                'to' => $reservation->getDateTo()->format('d-m-Y'),
                'own_mcp_code' => $reservation->getAccommodation()->getOwnMcpCode(),
                'own_name' => $reservation->getAccommodation()->getOwnName(),
                'destination' => $reservation->getAccommodation()->getOwnDestination()->getDesName(),
                'client_dates' => $reservation->getTravelAgencyOpenReservationsDetails()->first()->getReservation()->getClient()->getFullName(),
                'date' => $reservation->getCreationDate()->format('d-m-Y'));

            $ownReservations = $reservation->getPaOwnershipReservations();
            $arrTmp['data']['rooms'] = array();
            if (!$ownReservations->isEmpty()) {
                $ownReservation = $ownReservations->first();
                do {
                    $arrTmp['data']['rooms'][] = array(
                        'adults' => $ownReservation->getAdults(),
                        'childrens' => $ownReservation->getChildren()
                    );
                    $ownReservation = $ownReservations->next();
                } while ($ownReservation);
            }

            $data['aaData'][] = $arrTmp;
        }

        return new JsonResponse($data);
    }

    public function getReservationsProccessData($filters, $start, $limit, $draw)
    {
        $user = $this->getUser();

        $em = $this->getDoctrine()->getManager();
        $repository = $em->getRepository('PartnerBundle:paGeneralReservation');
        $userrepo = $em->getRepository('mycpBundle:user');
        $touroperators = array();

        $touroperators = $userrepo->getAllTourOperators($touroperators, $user);

        #region PAGINADO
        $page = ($start > 0) ? $start / $limit + 1 : 1;
        $paginator = $repository->getReservationsPartner($user->getUserId(), $filters, $start, $limit, $touroperators);
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

            $total_price = ($res->getTotalPrice() * $curr['change']) . $curr['code'];
            array_push($array_total_prices, $total_price);
        }

        return new JsonResponse(array(
            self::SUCCESS => true,
            'id' => 'id_dashboard_booking_proccess_detail_' . $id_reservation,
            'html' => $this->renderView('PartnerBundle:Dashboard:details_proccess.html.twig', array(
                'id_res' => $id_reservation,
                'cas' => "$id_reservation",
                'reservation' => $reservation,
                'reservations' => $ownership_reservations,
                'rooms' => $rooms,
                'nights' => $array_nights,
                'total_prices' => $array_total_prices
            )),
            'msg' => 'Vista del detalle de una reserva en proceso'
        ));
    }

    public function closeReservationAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $id_reservation = $request->get("id");
        $paGeneralReservation = $em->getRepository('PartnerBundle:paGeneralReservation')->find($id_reservation);
        $r = $this->closeReservation($paGeneralReservation);

        return new JsonResponse(array(
            self::SUCCESS => true,
            'id' => 'id_dashboard_booking_proccess_detail_' . $r,
            'message' => ""
        ));
    }

    /*End BookingProccess*/

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
        $content = $this->render('PartnerBundle:Mail:newAvailabilityCheck.html.twig', array(
            "reservations" => $paGeneralReservation->getTravelAgencyOpenReservationsDetails(),
            'user_locale' => strtolower($user->getUserLanguage()->getLangCode()),
            'currency' => strtoupper($user->getUserCurrency()->getCurrCode()),
            'currency_symbol' => $user->getUserCurrency()->getCurrSymbol(),
            'currency_rate' => $user->getUserCurrency()->getCurrCucChange()
        ));
        $service_email->sendTemplatedEmailPartner($subject, 'partner@mycasaparticular.com', $user->getUserEmail(), $content);

        $tourOperator = $em->getRepository("PartnerBundle:paTourOperator")->findOneBy(array(self::VAR_TOUR_OPERATOR => $user->getUserId()));
        $travelAgency = $tourOperator->getTravelAgency();
        $contacts = $travelAgency->getContacts();
        $phone_contact = (count($contacts)) ? $contacts[0]->getPhone() . ', ' . $contacts[0]->getMobile() : ' ';
        $completePayment = $travelAgency->getAgencyPackages()[0]->getPackage()->getCompletePayment();
        //Send email team reservations
        $content = $this->render('PartnerBundle:Mail:newAvailabilityCheckReservations.html.twig', array(
            "reservations" => $paGeneralReservation->getTravelAgencyOpenReservationsDetails(),
            "rooms" => $paGeneralReservation->getPaOwnershipReservations(),
            'user_locale' => 'es',
            'currency' => strtoupper($user->getUserCurrency()->getCurrCode()),
            'currency_symbol' => $user->getUserCurrency()->getCurrSymbol(),
            'currency_rate' => $user->getUserCurrency()->getCurrCucChange(),
            'travelAgency' => $travelAgency,
            'agency_resp' => (count($contacts)) ? $contacts[0]->getName() : '',
            'phone_contact' => $phone_contact
        ));
        $service_email->sendTemplatedEmailPartner($subject, 'partner@mycasaparticular.com', 'solicitud.partner@mycasaparticular.com', $content);

        $generalReservation = $paGeneralReservation->createReservation($completePayment);

        $paOwnershipReservations = $paGeneralReservation->getPaOwnershipReservations(); //a eliminar una a una
        //Pasar los paOwnershipReservation a ownershipReservation
        foreach ($paOwnershipReservations as $paORes) {
            $ownershipReservation = $paORes->createReservation();
            $ownershipReservation->setOwnResGenResId($generalReservation);

            $em->remove($paORes); //Eliminar paOwnershipReservation
            $em->persist($ownershipReservation);
        }

        $travelAgencyOpenReservationsDetails = $paGeneralReservation->getTravelAgencyOpenReservationsDetails();
        foreach ($travelAgencyOpenReservationsDetails as $detail) {
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

    public function closeAllReservationAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $id_reservations = $request->get("ids");
        foreach ($id_reservations as $id_reservation) {
            $paGeneralReservation = $em->getRepository('PartnerBundle:paGeneralReservation')->find($id_reservation);
            $this->closeReservation($paGeneralReservation);
        }

        return new JsonResponse(array(
            self::SUCCESS => true,
            'message' => ""
        ));
    }

    /**
     * @param $own_name
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function pageDetailAction($own_name, Request $request)
    {

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

        if ($this->get('request')->getMethod() == 'POST') {
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
                        $end_timestamp >= $reservation->getOwnResReservationToDate()->getTimestamp() && $reservation->getOwnResStatus() == ownershipReservation::STATUS_RESERVED
                    ) {

                        $array_no_available[$room->getRoomId()] = $room->getRoomId();
                    }

                    if ($start_timestamp >= $reservation->getOwnResReservationFromDate()->getTimestamp() &&
                        $start_timestamp <= $reservation->getOwnResReservationToDate()->getTimestamp() &&
                        $end_timestamp >= $reservation->getOwnResReservationToDate()->getTimestamp() && $reservation->getOwnResStatus() == ownershipReservation::STATUS_RESERVED
                    ) {

                        $array_no_available[$room->getRoomId()] = $room->getRoomId();
                    }

                    if ($start_timestamp <= $reservation->getOwnResReservationFromDate()->getTimestamp() &&
                        $end_timestamp <= $reservation->getOwnResReservationToDate()->getTimestamp() &&
                        $end_timestamp >= $reservation->getOwnResReservationFromDate()->getTimestamp() && $reservation->getOwnResStatus() == ownershipReservation::STATUS_RESERVED
                    ) {

                        $array_no_available[$room->getRoomId()] = $room->getRoomId();
                    }

                    if ($start_timestamp >= $reservation->getOwnResReservationFromDate()->getTimestamp() &&
                        $end_timestamp <= $reservation->getOwnResReservationToDate()->getTimestamp() && $reservation->getOwnResStatus() == ownershipReservation::STATUS_RESERVED
                    ) {

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
        if ($this->get('request')->getMethod() != 'POST') {
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
                $price_subtotal += $array_prices[$flag_room];
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
        $tourOperator = $em->getRepository("PartnerBundle:paTourOperator")->findOneBy(array(self::VAR_TOUR_OPERATOR => $user->getUserId()));
        $travelAgency = $tourOperator->getTravelAgency();
        $packageService = $this->get("mycp.partner.package.service");
        $isSpecialPackage = $packageService->isSpecialPackageFromAgency($travelAgency);

        $form = ($isSpecialPackage) ? $this->createForm(new paReservationExtendedType($this->get('translator'), $travelAgency)) : $this->createForm(new paReservationType($this->get('translator'), $travelAgency));

        $curr = $this->getCurr($request);

        if (isset($ownership_array['ownFacilitiesDinner']) && $ownership_array['ownFacilitiesDinner']) {
            $ownership_array['ownFacilitiesDinnerPriceTo'] = ($ownership_array['ownFacilitiesDinnerPriceTo'] * $curr['change']) . ' ' . $curr['code'];
        }

        if (isset($ownership_array['ownFacilitiesBreakfast']) && $ownership_array['ownFacilitiesBreakfast']) {
            $ownership_array['ownFacilitiesBreakfastPrice'] = ($ownership_array['ownFacilitiesBreakfastPrice'] * $curr['change']) . ' ' . $curr['code'];
        }

        if (isset($ownership_array['ownFacilitiesParking']) && $ownership_array['ownFacilitiesParking']) {
            $ownership_array['ownFacilitiesParkingPrice'] = ($ownership_array['ownFacilitiesParkingPrice'] * $curr['change']) . ' ' . $curr['code'];
        }

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
            'currentServiceFee' => $currentServiceFee,
            "isSpecialPackage" => $isSpecialPackage
        ));

    }

    public function addFavoritesAction()
    {
        $request = $this->get('request');
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

    public function removeFavoritesAction()
    {
        $request = $this->get('request');
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

    #Detalles 1
    public function indexBookingDetailAction($id_reservation, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $reservation = $em->getRepository('mycpBundle:generalReservation')->find($id_reservation);

        $ownership_reservations = $reservation->getOwnReservations();

        $service_time = $this->get('time');
        $array_nights = array();
        $array_total_prices = array();
        $rooms = array();
        $array_complete_prices = 0;
        $canBeCanceled = array();
        $curr = $this->getCurr($request);
        $today = new \DateTime();
        $oneCanBeCanceled = false;
        $temp_price = 0;
        $booking = array();


        foreach ($ownership_reservations as $res) {
            $nights = $res->getNights($service_time);
            array_push($rooms, $em->getRepository('mycpBundle:room')->find($res->getOwnResSelectedRoomId()));
            array_push($array_nights, $nights);

            if ($res->getOwnResReservationBooking() != null) {
                array_push($booking, $res->getOwnResReservationBooking()->getBookingId());
            } else {
                array_push($booking, '-');

            }

            $canCancel = ($res->getOwnResReservationFromDate() > $today && $res->getOwnResStatus() == ownershipReservation::STATUS_RESERVED);
            array_push($canBeCanceled, $canCancel);

            if (!$oneCanBeCanceled)
                $oneCanBeCanceled = $canCancel;

            if ($res->getOwnResStatus() != ownershipReservation::STATUS_CANCELLED) {
                $total_price = round(($res->getPriceTotal($service_time) * $curr['change']), 2) . $curr['code'];
                $temp_price += $res->getPriceTotal($service_time);
            }
            array_push($array_total_prices, $total_price);


        }

        $array_complete_prices += round((($temp_price + ($temp_price * 0.1) + ($temp_price + ($temp_price * 0.1)) * 0.1) * $curr['change']), 2);


        if ($reservation->getGenResStatus() == 10) {
            return new JsonResponse(array(self::SUCCESS => true,
                'id' => 'id_dashboard_booking_detail_' . $id_reservation,
                'html' => $this->renderView('PartnerBundle:Dashboard:details.html.twig', array(
                    'id_res' => $id_reservation,
                    'cas' => "CAS.$id_reservation",
                    'reservation' => $reservation,
                    'user' => $reservation->getGenResUserId()->getUsername(),
                    'agency' => $reservation->getTravelAgencyDetailReservations()->first()->getReservation()->getClient()->getTravelAgency()->getName(),
                    'invoice' => $reservation->getInvoice()->getFilename(),
                    'invoice_date' => $reservation->getInvoice()->getInvoicedate(),
                    'array_complete_prices' => round($array_complete_prices, 2) . $curr['code'],
                    'reservations' => $ownership_reservations,
                    'rooms' => $rooms,
                    'nights' => $array_nights,
                    'total_prices' => $array_total_prices,
                    'canBeCanceled' => $canBeCanceled,
                    'oneCanBeCanceled' => $oneCanBeCanceled,
                    'booking' => $booking[0],
                    'reference' => $reservation->getTravelAgencyDetailReservations()->first()->getReservation()->getReference()

                )),
                'msg' => 'Vista del detalle de una reserva'));
        } else {
            return new JsonResponse(array(self::SUCCESS => true,
                'id' => 'id_dashboard_booking_detail_' . $id_reservation,
                'html' => $this->renderView('PartnerBundle:Dashboard:details.html.twig', array(
                    'id_res' => $id_reservation,
                    'cas' => "CAS.$id_reservation",
                    'reservation' => $reservation,
                    'user' => $reservation->getGenResUserId()->getUsername(),
                    'agency' => $reservation->getTravelAgencyDetailReservations()->first()->getReservation()->getClient()->getTravelAgency()->getName(),
                    'invoice' => '',
                    'invoice_date' => '',
                    'array_complete_prices' => round($array_complete_prices, 2) . $curr['code'],
                    'reservations' => $ownership_reservations,
                    'rooms' => $rooms,
                    'nights' => $array_nights,
                    'total_prices' => $array_total_prices,
                    'canBeCanceled' => $canBeCanceled,
                    'oneCanBeCanceled' => $oneCanBeCanceled,
                    'booking' => $booking[0],
                    'reference' => $reservation->getTravelAgencyDetailReservations()->first()->getReservation()->getReference()

                )),
                'msg' => 'Vista del detalle de una reserva'));
        }

    }

    public function indexBookingDetailCancelAction($id_reservation, $date, $amount, $currency, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $reservation = $em->getRepository('mycpBundle:generalReservation')->find($id_reservation);
        $ownership_reservations = $reservation->getOwnReservations();

        $service_time = $this->get('time');
        $array_nights = array();
        $array_total_prices = array();
        $rooms = array();
        $canBeCanceled = array();
        $curr = $this->getCurr($request);
        $today = new \DateTime();
        $oneCanBeCanceled = false;

        foreach ($ownership_reservations as $res) {
            $nights = $res->getNights($service_time);
            array_push($rooms, $em->getRepository('mycpBundle:room')->find($res->getOwnResSelectedRoomId()));
            array_push($array_nights, $nights);

            $canCancel = ($res->getOwnResReservationFromDate() > $today && $res->getOwnResStatus() == ownershipReservation::STATUS_RESERVED);
            array_push($canBeCanceled, $canCancel);

            if (!$oneCanBeCanceled)
                $oneCanBeCanceled = $canCancel;

            $total_price = ($res->getPriceTotal($service_time) * $curr['change']) . $curr['code'];
            array_push($array_total_prices, $total_price);
        }

        return new JsonResponse(array(
            self::SUCCESS => true,
            'id' => 'id_dashboard_booking_detail_' . $id_reservation,
            'html' => $this->renderView('PartnerBundle:Dashboard:detailsCancel.html.twig', array(
                'id_res' => $id_reservation,
                'cas' => "CAS.$id_reservation",
                'reservation' => $reservation,
                'reservations' => $ownership_reservations,
                'rooms' => $rooms,
                'nights' => $array_nights,
                'total_prices' => $array_total_prices,
                'canBeCanceled' => $canBeCanceled,
                'oneCanBeCanceled' => $oneCanBeCanceled,
                'cancelDate' => $date,
                'reference' => $reservation->getTravelAgencyDetailReservations()->first()->getReservation()->getReference(),

                'cancelAmount' => $amount . " " . $currency
            )),
            'msg' => 'Vista del detalle de una reserva'
        ));
    }

    public function cancelFromAgencyAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $reservationId = $request->get("reservationId");

        $generalReservation = $em->getRepository('mycpBundle:generalReservation')->find($reservationId);
        $ownershipReservations = $generalReservation->getOwn_reservations();
        foreach ($ownershipReservations as $ownRes) {
            $ownRes->setOwnResStatus(ownershipReservation::STATUS_CANCELLED);
            $em->persist($ownRes);
        }
        $generalReservation->setGenResStatus(generalReservation::STATUS_CANCELLED);
        $generalReservation->setGenResStatusDate(new \DateTime());
        $generalReservation->setCanceledBy($this->getUser());
        $em->persist($generalReservation);

        $em->flush();

        return new JsonResponse(array(
            self::SUCCESS => true,
            'message' => "Cancelado correctamente"
        ));
    }

    public function cancelReservationAction($idReservation, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $trans = $this->get('translator');
        $generalReservation = $em->getRepository('mycpBundle:generalReservation')->find($idReservation);

        $user = $this->getUser();
        $currentTourOperator = $em->getRepository("PartnerBundle:paTourOperator")->findOneBy(array(self::VAR_TOUR_OPERATOR => $user->getUserId()));
        $currentTravelAgency = $currentTourOperator->getTravelAgency();

        $reservationUser = $generalReservation->getGenResUserId();
        $reservationTourOperator = $em->getRepository("PartnerBundle:paTourOperator")->findOneBy(array(self::VAR_TOUR_OPERATOR => $reservationUser->getUserId()));
        $reservationTravelAgency = $reservationTourOperator->getTravelAgency();

        if (($reservationUser->getUserRole() == "ROLE_CLIENT_PARTNER" || $reservationUser->getUserRole() == "ROLE_ECONOMY_PARTNER" || $reservationUser->getUserRole() == "ROLE_CLIENT_PARTNER_TOUROPERATOR") && $currentTravelAgency->getId() == $reservationTravelAgency->getId()) {
            //Comprobar si la reserva fue hecha por la agencia a la que pertenece ese usuario

            if ($this->get('request')->getMethod() == 'POST') {
                $ownershipReservations = $generalReservation->getOwn_reservations();
                foreach ($ownershipReservations as $ownRes) {
                    $ownRes->setOwnResStatus(ownershipReservation::STATUS_CANCELLED);
                    $em->persist($ownRes);
                }
                $generalReservation->setGenResStatus(generalReservation::STATUS_CANCELLED);
                $generalReservation->setGenResStatusDate(new \DateTime());
                $generalReservation->setCanceledBy($user);
                $em->persist($generalReservation);

                $em->flush();

                $message = $trans->trans('cancel.reservation.successful.alert');
                $this->get('session')->getFlashBag()->add('message_global_success', $message);

                return $this->redirect($this->generateUrl("backend_partner_dashboard"));
            }

            return $this->render('PartnerBundle:Dashboard:cancelReservation.html.twig', array(
                "casReservation" => "CAS." . $idReservation,
                "idReservation" => $idReservation
            ));
        } else {
            $message = $trans->trans('cancel.reservation.error.alert');
            $this->get('session')->getFlashBag()->add('message_global_error', $message);

            return $this->redirect($this->generateUrl("backend_partner_dashboard"));
        }
    }

    public function cancelPayedReservationAction($idReservation, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $trans = $this->get('translator');
        $ownershipReservation = $em->getRepository('mycpBundle:ownershipReservation')->find($idReservation);
        $generalReservation = $ownershipReservation->getOwnResGenResId();

        $user = $this->getUser();
        $currentTourOperator = $em->getRepository("PartnerBundle:paTourOperator")->findOneBy(array(self::VAR_TOUR_OPERATOR => $user->getUserId()));
        $currentTravelAgency = $currentTourOperator->getTravelAgency();

        $reservationUser = $generalReservation->getGenResUserId();
        $reservationTourOperator = $em->getRepository("PartnerBundle:paTourOperator")->findOneBy(array(self::VAR_TOUR_OPERATOR => $reservationUser->getUserId()));
        $reservationTravelAgency = $reservationTourOperator->getTravelAgency();

        if (($reservationUser->getUserRole() == "ROLE_CLIENT_PARTNER" || $reservationUser->getUserRole() == "ROLE_ECONOMY_PARTNER" || $reservationUser->getUserRole() == "ROLE_CLIENT_PARTNER_TOUROPERATOR") && $currentTravelAgency->getId() == $reservationTravelAgency->getId()) {
            //Comprobar si la reserva fue hecha por la agencia a la que pertenece ese usuario

            //Calcular el dinero a devolver
            $hasToRefund = ($generalReservation->getGenResStatus() == generalReservation::STATUS_RESERVED);


            $ownershipReservations = $generalReservation->getOwn_reservations();
            foreach ($ownershipReservations as $ownRes) {
                $ownRes->setOwnResStatus(ownershipReservation::STATUS_CANCELLED);
                $em->persist($ownRes);

                if ($hasToRefund) {
                }
            }

            $generalReservation->setGenResStatus(generalReservation::STATUS_CANCELLED);
            $generalReservation->setGenResStatusDate(new \DateTime());
            $generalReservation->setCanceledBy($user);
            $em->persist($generalReservation);


            $em->flush();

            $message = $trans->trans('cancel.reservation.successful.alert');
            $this->get('session')->getFlashBag()->add('message_global_success', $message);

            return $this->redirect($this->generateUrl("backend_partner_dashboard"));

        } else {
            $message = $trans->trans('cancel.reservation.error.alert');
            $this->get('session')->getFlashBag()->add('message_global_error', $message);
        }
    }

    public function getThumbnailForSearcherAction($photo, $title)
    {
        list($width, $height) = getimagesize(realpath("uploads/ownershipImages/" . $photo));

        return $this->render('PartnerBundle:ownership:searchImage.html.twig', array(
            'title' => $title,
            'photo' => $photo,
            'taller' => ($height > $width)
        ));
    }

    public function downloadVoucherAction($bookingId)
    {
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
        $pdfservice = $this->get("front_end.services.booking");
        $user = $pdfservice->getUserFromBooking($booking);
        $clients = $em->getRepository("PartnerBundle:paClient")->getClientsFromBooking($bookingId, $user);

        $currentTourOperator = $em->getRepository("PartnerBundle:paTourOperator")->findOneBy(array(self::VAR_TOUR_OPERATOR => $user->getUserId()));
        $currentTravelAgency = $currentTourOperator->getTravelAgency();

        $name = 'voucher' . $booking->getBookingUserId() . '_' . $booking->getBookingId() . '.pdf';
        $name_client = 'voucher' . $user->getUserId() . '_' . $bookingId . '_' . $clients[0]['id'] . '.pdf';
        $nameZip = 'vouchers_' . $booking->getBookingId() . '_' . $booking->getBookingUserId() . '.zip';

        if (file_exists(realpath($pathToFile . $nameZip)) && file_exists(realpath($pathToFile . $name)) && file_exists(realpath($pathToFile . $name_client))) {

            $response = new BinaryFileResponse($pathToFile . $nameZip);
            $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $nameZip);

        } else {

            $pdfFilePath = $pdfservice->createBookingVoucherIfNotExistingPartner($bookingId, $user, true);
            $pdfClientFilePaths = $pdfservice->createBookingVoucherForClientsIfNotExistsPartner($bookingId, $user, $currentTravelAgency, true);

            $filePaths = array_merge(array($pdfFilePath), $pdfClientFilePaths);
            $zipFileName = $pdfservice->zipService->createZipFile("vouchers_" . $bookingId . "_" . $user->getUserId() . ".zip", $filePaths, $pdfservice->voucherDirectoryPath);

            $response = new BinaryFileResponse($pathToFile . $nameZip);
            $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $nameZip);
        }

        return $response;
    }


    public function downloadInvoiceAction($reservationID)
    {
        $pathToFile = $this->container->getParameter("configuration.dir.invoice");

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
        $gr = $em->getRepository('mycpBundle:generalReservation')->find($reservationID);

        $name = $gr->getInvoice()->getFilename() . '.pdf';


        if (file_exists(realpath($pathToFile . $name))) {
            $response = new BinaryFileResponse($pathToFile . $name);
            $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $name);
        } else {
            $response = new BinaryFileResponse($pathToFile . $name);
            $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $name);
        }

        return $response;
    }

    public function getReservationCalendarAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $user = $this->getUser();

        $currentTourOperator = $em->getRepository("PartnerBundle:paTourOperator")->findOneBy(array(self::VAR_TOUR_OPERATOR => $user->getUserId()));
        $currentTravelAgency = $currentTourOperator->getTravelAgency();
        $agencyPackage = $currentTravelAgency->getAgencyPackages()[0];

        $from = $request->get('from');
        $to = $request->get('to');
        $fromBackend = $request->get('backend');
        $timer = $this->get("Time");
        $fromBackend = ($fromBackend != "" && $fromBackend);

        $reservation_from = explode('/', $from);
        $dateFrom = new \DateTime();
        $start_timestamp = mktime(0, 0, 0, $reservation_from[1], $reservation_from[0], $reservation_from[2]);
        $dateFrom->setTimestamp($start_timestamp);

        $reservation_to = explode('/', $to);
        $dateTo = new \DateTime();
        $end_timestamp = mktime(0, 0, 0, $reservation_to[1], $reservation_to[0], $reservation_to[2]);
        $dateTo->setTimestamp($end_timestamp);
        $owner_id = $request->get('own_id');

        $nights = $timer->nights($dateFrom->getTimestamp(), $dateTo->getTimestamp());
        $dateTo->setTimestamp(strtotime("-1 day", $end_timestamp));
        if (!$owner_id) {
            throw $this->createNotFoundException();
        }

        $em = $this->getDoctrine()->getManager();

        $ownership = $em->getRepository('mycpBundle:ownership')->find($owner_id);

        /*$general_reservations = $em->getRepository('mycpBundle:generalReservation')->findBy(array('gen_res_own_id' => $owner_id));
        $reservations = array();
        foreach ($general_reservations as $gen_res) {
            $own_reservations = $em->getRepository('mycpBundle:ownershipReservation')->getReservationReservedByGeneralAndDate($gen_res->getGenResId(), $dateFrom, $dateTo);//findBy(array('own_res_gen_res_id' => $gen_res->getGenResId()));
            foreach ($own_reservations as $own_res) {
                array_push($reservations, $own_res);
            }
        }*/

        $rooms = $em->getRepository('mycpBundle:room')->findBy(array('room_ownership' => $owner_id, 'room_active' => true));

        $service_time = $this->get('Time');
        $array_dates = $service_time->datesBetween($start_timestamp, $end_timestamp);

        $array_no_available = array();
        $no_available_days = array();
        $no_available_days_ready = array();
        $array_prices = array();
        $prices_dates = array();


        foreach ($rooms as $room) {
            $temp_local = array();
            $unavailable_room = $em->getRepository('mycpBundle:unavailabilityDetails')->getRoomDetailsByRoomAndDates($room->getRoomId(), $dateFrom, $dateTo);
            $flag = 0;
            if ($unavailable_room) {
                foreach ($unavailable_room as $ur) {
                    $unavailable_days = $service_time->datesBetween($ur->getUdFromDate()->getTimestamp(), $ur->getUdToDate()->getTimestamp());
                    // unavailable details
                    $array_no_available[$room->getRoomId()] = $room->getRoomId();
                    $flag = 1;
                    /*if ($start_timestamp <= $ur->getUdFromDate()->getTimestamp() &&
                            $end_timestamp >= $ur->getUdToDate()->getTimestamp()) {
                        $array_no_available[$room->getRoomId()] = $room->getRoomId();
                        $flag = 1;
                    }

                    if ($start_timestamp >= $ur->getUdFromDate()->getTimestamp() &&
                            $start_timestamp <= $ur->getUdToDate()->getTimestamp() &&
                            $end_timestamp >= $ur->getUdToDate()->getTimestamp()) {
                        $array_no_available[$room->getRoomId()] = $room->getRoomId();
                        $flag = 1;
                    }

                    if ($start_timestamp <= $ur->getUdFromDate()->getTimestamp() &&
                            $end_timestamp <= $ur->getUdToDate()->getTimestamp() &&
                            $end_timestamp >= $ur->getUdFromDate()->getTimestamp()) {

                        $array_no_available[$room->getRoomId()] = $room->getRoomId();
                        $flag = 1;
                    }

                    if ($start_timestamp >= $ur->getUdFromDate()->getTimestamp() &&
                            $end_timestamp <= $ur->getUdToDate()->getTimestamp()) {
                        $array_no_available[$room->getRoomId()] = $room->getRoomId();
                        $flag = 1;
                    }*/
                    $temp = array();
                    foreach ($unavailable_days as $unav_date) {
                        for ($s = 0; $s < count($array_dates) - 1; $s++) {
                            if ($array_dates[$s] == $unav_date) {
                                array_push($temp, $s + 1);
                            }
                        }
                    }
                    if ($flag == 1) {
                        $temp_local = array_merge($temp_local, $temp);
                        $no_available_days_ready[$room->getRoomId()] = $temp_local;
                    }
                }
            }

            $reservations = $em->getRepository('mycpBundle:ownershipReservation')->getReservationReservedByRoomAndDateForCalendar($room->getRoomId(), $dateFrom, $dateTo);
            foreach ($reservations as $reservation) {
                $reservationStartDate = $reservation->getOwnResReservationFromDate()->getTimestamp();
                $reservationEndDate = $reservation->getOwnResReservationToDate()->getTimestamp();
                $date = new \DateTime();
                $date->setTimestamp(strtotime("-1 day", $reservationEndDate));
                $reservationEndDate = $date->getTimestamp();

                $array_no_available[$room->getRoomId()] = $room->getRoomId();

                /*if ($start_timestamp <= $reservationStartDate && $end_timestamp >= $reservationEndDate) {

                    $array_no_available[$room->getRoomId()] = $room->getRoomId();
                }

                if ($start_timestamp >= $reservationStartDate && $start_timestamp <= $reservationEndDate &&
                        $end_timestamp >= $reservationEndDate) {

                    $array_no_available[$room->getRoomId()] = $room->getRoomId();
                }

                if ($start_timestamp <= $reservationStartDate && $end_timestamp <= $reservationEndDate &&
                        $end_timestamp >= $reservationStartDate) {

                    $array_no_available[$room->getRoomId()] = $room->getRoomId();
                }

                if ($start_timestamp >= $reservationStartDate && $end_timestamp <= $reservationEndDate) {

                    $array_no_available[$room->getRoomId()] = $room->getRoomId();
                }*/

                $array_numbers_check = array();
                $cont_numbers = 1;
                foreach ((array)$array_dates as $date) {

                    if ($date >= $reservationStartDate && $date <= $reservationEndDate && $date != $dateTo->getTimestamp()) {
                        array_push($array_numbers_check, $cont_numbers);
                    }
                    $cont_numbers++;
                }
                array_push($no_available_days, array(
                    $room->getRoomId() => $room->getRoomId(),
                    'check' => $array_numbers_check
                ));
            }


            $total_price_room = 0;
            $prices_dates_temp = array();
            $x = 1;
            /* if ($request->getMethod() != 'POST') {
              //$x = 2;
              } */
            $destination_id = ($ownership->getOwnDestination() != null) ? $ownership->getOwnDestination()->getDesId() : null;
            $seasons = $em->getRepository("mycpBundle:season")->getSeasons($from, $to, $destination_id);
            for ($a = 0; $a < count($array_dates) - $x; $a++) {

                $season_type = $service_time->seasonTypeByDate($seasons, $array_dates[$a]);
                $roomPrice = $room->getPriceBySeasonType($season_type);

                $total_price_room += $roomPrice;
                array_push($prices_dates_temp, $roomPrice);
            }
            array_push($array_prices, $total_price_room);
            array_push($prices_dates, $prices_dates_temp);
        }

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
                $price_subtotal += $array_prices[$flag_room];
                array_push($available_rooms, $room_2->getRoomId());
                array_push($avail_array_prices, $array_prices[$flag_room]);
            }
            $do_operation = true;
            $flag_room++;
        }
        $packageService = $this->get("mycp.partner.package.service");
        $isSpecial = $packageService->isSpecialPackage();
        $isBasic = $packageService->isBasicPackage();
        $isEconomic = $packageService->isEconomicPackage();
        $currentServiceFee = $em->getRepository("mycpBundle:serviceFee")->getCurrent();
        //$no_available_days_ready[351]=array(11,12,13,14,15,21,22);
        return $this->render('FrontEndBundle:ownership:ownershipReservationCalendar.html.twig', array(
            'array_dates' => $array_dates_keys,
            'currentServiceFee' => $currentServiceFee,
            'rooms' => $rooms,
            'array_prices' => $array_prices,
            'ownership' => $ownership,
            'no_available_days' => $no_available_days_ready,
            'prices_dates' => $prices_dates,
            'reservations' => $array_no_available,
            'fromBackend' => $fromBackend,
            'fromPartner' => true,
            'commissionAgency' => $currentTravelAgency->getCommission(),
            'completePayment' => $agencyPackage->getPackage()->getCompletePayment(),
            'nights' => $nights,
            "tripleChargeRoom" => $this->container->getParameter('configuration.triple.room.charge'),
            'isCompletePayment' => false,
            self::VAR_IS_SPECIAL => $isSpecial,
            "isBasic" => $isBasic,
            "isEconomic" => $isEconomic
        ));
    }

    public function addToCartAction($id_ownership, Request $request)
    {

        $check_dispo = $request->get('check_dispo');
        $em = $this->getDoctrine()->getManager();
        $user1 = $this->getUser();


        $tourOperator = $em->getRepository("PartnerBundle:paTourOperator")->findOneBy(array(self::VAR_TOUR_OPERATOR => $user1->getUserId()));
        $travelAgency = $tourOperator->getTravelAgency();
        $completePayment = $travelAgency->getAgencyPackages()[0]->getPackage()->getCompletePayment();

        if (!$request->get('data_reservation'))
            throw $this->createNotFoundException();
        $data = $request->get('data_reservation');
        $data = explode('/', $data);

        $from_date = $data[0];
        $to_date = $data[1];
        $ids_rooms = $data[2];
        $count_guests = $data[3];
        $count_kids = $data[4];

        $count_kidsAge_1 = (count($data) >= 6) ? $data[5] : 0;
        $count_kidsAge_2 = (count($data) >= 7) ? $data[6] : 0;
        $count_kidsAge_3 = (count($data) >= 8) ? $data[7] : 0;

        $array_ids_rooms = explode('&', $ids_rooms);
        array_shift($array_ids_rooms);
        $array_count_guests = explode('&', $count_guests);
        array_shift($array_count_guests);
        $array_count_kids = explode('&', $count_kids);
        array_shift($array_count_kids);

        $array_count_kidsAge_1 = explode('&', $count_kidsAge_1);
        array_shift($array_count_kidsAge_1);

        $array_count_kidsAge_2 = explode('&', $count_kidsAge_2);
        array_shift($array_count_kidsAge_2);

        $array_count_kidsAge_3 = explode('&', $count_kidsAge_3);
        array_shift($array_count_kidsAge_3);

        $reservation_date_from = $from_date;
        $reservation_date_from = explode('&', $reservation_date_from);

        $start_timestamp = mktime(0, 0, 0, $reservation_date_from[1], $reservation_date_from[0], $reservation_date_from[2]);

        $reservation_date_to = $to_date;
        $reservation_date_to = explode('&', $reservation_date_to);
        $end_timestamp = mktime(0, 0, 0, $reservation_date_to[1], $reservation_date_to[0], $reservation_date_to[2]);

        $user_ids = $em->getRepository('mycpBundle:user')->getIds($this);
        $cartItems = $em->getRepository('mycpBundle:cart')->getCartItems($user_ids);

        if (isset($check_dispo) && $check_dispo != '' && ($check_dispo == 1 || $check_dispo == 2)) {
            $ownerShip = $em->getRepository('mycpBundle:generalReservation')->getOwnShipReserByUser($user_ids);
        }


        $showError = false;
        $showErrorOwnExist = false;
        $showErrorItem = '';
        $arrayIdCart = array();
        for ($a = 0; $a < count($array_ids_rooms); $a++) {
            $insert = 1;
            foreach ($cartItems as $item) {
                $cartDateFrom = $item->getCartDateFrom()->getTimestamp();
                $cartDateTo = $item->getCartDateTo()->getTimestamp();
                $date = new \DateTime();
                $date->setTimestamp(strtotime("-1 day", $cartDateTo));
                $cartDateTo = $date->getTimestamp();

                if (isset($array_count_guests[$a]) && isset($array_count_kids[$a]) &&
                    (($cartDateFrom <= $start_timestamp && $cartDateTo >= $start_timestamp) ||
                        ($cartDateFrom <= $end_timestamp && $cartDateTo >= $end_timestamp)) &&
                    $item->getCartRoom() == $array_ids_rooms[$a] && $check_dispo != 2
                ) {
                    $insert = 0;
                    $showError = true;
                    $showErrorItem = $item;
                }
            }
            if (isset($check_dispo) && $check_dispo != '' && ($check_dispo == 1 || $check_dispo == 2)) {
                if (count($ownerShip)) {
                    foreach ($ownerShip as $item) {
                        $ownDateFrom = $item->getOwnResReservationFromDate()->getTimestamp();
                        $ownDateTo = $item->getOwnResReservationToDate()->getTimestamp();
                        $date = new \DateTime();
                        $date->setTimestamp(strtotime("-1 day", $ownDateTo));
                        $ownDateTo = $date->getTimestamp();
                        if ((($ownDateFrom <= $start_timestamp && $ownDateTo >= $start_timestamp) ||
                                ($ownDateFrom <= $end_timestamp && $ownDateTo >= $end_timestamp)) &&
                            $item->getOwnResSelectedRoomId() == $array_ids_rooms[$a]
                        ) {
//                            $insert = 0;
                            $showError = true;
//                            $showErrorOwnExist = true;
                        }
                    }
                }
            }
            if ($insert == 1) {
                $room = $em->getRepository('mycpBundle:room')->find($array_ids_rooms[$a]);
                if ($room != null) {
                    $serviceFee = $em->getRepository("mycpBundle:serviceFee")->getCurrent();
                    $cart = new cart();
                    $fromDate = new \DateTime();
                    $fromDate->setTimestamp($start_timestamp);
                    $cart->setCartDateFrom($fromDate);

                    $toDate = new \DateTime();
                    $toDate->setTimestamp($end_timestamp);
                    $cart->setCartDateTo($toDate);
                    $cart->setCartRoom($room);
                    $cart->setServiceFee($serviceFee);
                    if (isset($check_dispo) && $check_dispo != '' && $check_dispo == 1 && $user_ids["user_id"] == null) {
                        $cart->setCheckAvailable(true);
                    }
                    if (isset($check_dispo) && $check_dispo != '' && $check_dispo == 2 && $user_ids["user_id"] == null) {
                        $cart->setInmediateBooking(true);
                    }
                    if (isset($array_count_guests[$a]))
                        $cart->setCartCountAdults($array_count_guests[$a]);
                    else
                        $cart->setCartCountAdults(1);

                    if (isset($array_count_kids[$a]))
                        $cart->setCartCountChildren($array_count_kids[$a]);
                    else
                        $cart->setCartCountChildren(0);

                    $kidsAge = array();

                    if (isset($array_count_kidsAge_1[$a]) && $array_count_kidsAge_1[$a] != -1)
                        $kidsAge["FirstKidAge"] = $array_count_kidsAge_1[$a];

                    if (isset($array_count_kidsAge_2[$a]) && $array_count_kidsAge_2[$a] != -1)
                        $kidsAge["SecondKidAge"] = $array_count_kidsAge_2[$a];

                    if (isset($array_count_kidsAge_3[$a]) && $array_count_kidsAge_3[$a] != -1)
                        $kidsAge["ThirdKidAge"] = $array_count_kidsAge_3[$a];

                    if (count($kidsAge))
                        $cart->setChildrenAges($kidsAge);

                    $cart->setCartCreatedDate(new \DateTime(date('Y-m-d')));
                    if ($user_ids["user_id"] != null) {
                        $user = $em->getRepository("mycpBundle:user")->find($user_ids["user_id"]);
                        $cart->setCartUser($user);
                    } else if ($user_ids["session_id"] != null)
                        $cart->setCartSessionId($user_ids["session_id"]);

                    $hasCompleteReservation = false;
                    $cart->setCompleteReservationMode($hasCompleteReservation);
                    $em->persist($cart);
                    $em->flush();
                    $arrayIdCart[] = $cart->getCartId();
                    if ($user_ids["user_id"] != null || $user_ids["session_id"] != null) {
                        // inform listeners that a reservation was sent out
                        $dispatcher = $this->get('event_dispatcher');
                        $eventData = new \MyCp\mycpBundle\JobData\UserJobData($user_ids["user_id"], $user_ids["session_id"]);
                        $dispatcher->dispatch('mycp.event.cart.full', new JobEvent($eventData));
                    }
                }
            }
        }

        $own_ids = array();
        if ($user_ids["user_id"] != null) {
            if (isset($check_dispo) && $check_dispo != '' && $check_dispo == 1 && !$showErrorOwnExist) {
                //Es que el usuario mando a consultar la disponibilidad
                $this->checkDispo($arrayIdCart, $request, false, $id_ownership, $completePayment);
            } elseif (isset($check_dispo) && $check_dispo != '' && $check_dispo == 2 && !$showErrorOwnExist) {
                //Es que el usuario mando a hacer una reserva

                $own_ids = $this->checkDispo($arrayIdCart, $request, true, $id_ownership, $completePayment);

            } else {
                if (!$request->isXmlHttpRequest()) {
                    $message = $this->get('translator')->trans("ADD_TO_CEST_ERROR");
                    $this->get('session')->getFlashBag()->add('message_global_error', $message);
                }
            }
        }
        //If ajax
        if ($request->isXmlHttpRequest()) {

            if (isset($check_dispo) && $check_dispo != '' && $check_dispo == 1 && !$showErrorOwnExist) {
                $response = new Response(1);
            } elseif (isset($check_dispo) && $check_dispo != '' && $check_dispo == 2 && !$showErrorOwnExist) {
                $response = new Response(2);
            } else
                $response = new Response(0);

            return $response;
        } else {
            if (isset($check_dispo) && $check_dispo != '' && $check_dispo == 2 && !$showErrorOwnExist) {
                if ($user_ids["user_id"] != null) {
                    $request->getSession()->set('reservation_own_ids', $own_ids);
                    return $this->redirect($this->generateUrl('partner_dashboard_cart'));
                } else {
                    $message = $this->get('translator')->trans("VOUCHER_PREHEADER_NEWMSG");
                    $this->get('session')->getFlashBag()->add('message_global_success', $message);
                    return $this->redirect($this->generateUrl('partner_dashboard_cart'));
                }

            } elseif ($showErrorOwnExist)
                return $this->redirect($this->generateUrl('backend_partner_dashboard'));
            else
                return $this->redirect($this->generateUrl('partner_dashboard_cart'));
        }
    }

    /**
     * @param $id_car
     * @param $request
     * @return bool|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function checkDispo($arrayIdCart, $request, $inmediatily_booking, $id_ownership, $completePayment)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $reservations = array();
        $own_ids = array();
        $array_photos = array();
        $user_ids = $em->getRepository('mycpBundle:user')->getIds($this);
        $cartItems = array();
        foreach ($arrayIdCart as $temp) {
            $cartItem = $em->getRepository('mycpBundle:cart')->find($temp);
            $cartItems[] = $cartItem;
        }

        $min_date = null;
        $max_date = null;
        $generalReservations = array();

        if (count($cartItems) > 0) {
            $res_array = array();
            $own_visited = array();
            foreach ($cartItems as $item) {

                if ($min_date == null)
                    $min_date = $item->getCartDateFrom();
                else if ($item->getCartDateFrom() < $min_date)
                    $min_date = $item->getCartDateFrom();

                if ($max_date == null)
                    $max_date = $item->getCartDateTo();
                else if ($item->getCartDateTo() > $max_date)
                    $max_date = $item->getCartDateTo();

                $res_own_id = $item->getCartRoom()->getRoomOwnership()->getOwnId();

                $array_group_by_own_id = array();
                $flag = 1;
                foreach ($own_visited as $own) {
                    if ($own == $res_own_id) {
                        $flag = 0;
                    }
                }
                if ($flag == 1)
                    foreach ($cartItems as $item) {
                        if ($res_own_id == $item->getCartRoom()->getRoomOwnership()->getOwnId()) {
                            array_push($array_group_by_own_id, $item);
                        }
                    }
                array_push($res_array, $array_group_by_own_id);
                array_push($own_visited, $res_own_id);
            }
            $service_time = $this->get('Time');
            $nigths = array();
            foreach ($res_array as $resByOwn) {

                if (isset($resByOwn[0])) {
                    $ownership = $em->getRepository('mycpBundle:ownership')->find($resByOwn[0]->getCartRoom()->getRoomOwnership()->getOwnId());

                    $serviceFee = $em->getRepository("mycpBundle:serviceFee")->getCurrent();
                    $general_reservation = new generalReservation();
                    $general_reservation->setGenResUserId($user);
                    $general_reservation->setGenResDate(new \DateTime(date('Y-m-d')));
                    $general_reservation->setGenResStatusDate(new \DateTime(date('Y-m-d')));
                    $general_reservation->setGenResHour(date('G'));
                    $general_reservation->setCompletePayment($completePayment);
                    if ($inmediatily_booking) {
                        $general_reservation->setGenResStatus(generalReservation::STATUS_AVAILABLE);
                    } else
                        $general_reservation->setGenResStatus(generalReservation::STATUS_PENDING);
                    $general_reservation->setGenResFromDate($min_date);
                    $general_reservation->setGenResToDate($max_date);
                    $general_reservation->setGenResSaved(0);
                    $general_reservation->setGenResOwnId($ownership);
                    $general_reservation->setCompleteReservationMode($ownership->getCompleteReservationMode());
                    $general_reservation->setGenResDateHour(new \DateTime(date('H:i:s')));
                    $general_reservation->setServiceFee($serviceFee);
                    $general_reservation->setServicedinner($request->get('servicedinner'));
                    $general_reservation->setServicefast($request->get('servicefast'));


                    $total_price = 0;
                    $partial_total_price = array();
                    $destination_id = ($ownership->getOwnDestination() != null) ? $ownership->getOwnDestination()->getDesId() : null;
                    foreach ($resByOwn as $item) {
                        $triple_room_recharge = ($item->getTripleRoomCharged()) ? $this->container->getParameter('configuration.triple.room.charge') : 0;
                        $array_dates = $service_time->datesBetween($item->getCartDateFrom()->getTimestamp(), $item->getCartDateTo()->getTimestamp());
                        $temp_price = 0;
                        $seasons = $em->getRepository("mycpBundle:season")->getSeasons($item->getCartDateFrom()->getTimestamp(), $item->getCartDateTo()->getTimestamp(), $destination_id);

                        for ($a = 0; $a < count($array_dates) - 1; $a++) {
                            $seasonType = $service_time->seasonTypeByDate($seasons, $array_dates[$a]);
                            $roomPrice = $item->getCartRoom()->getPriceBySeasonType($seasonType);
                            $total_price += $roomPrice + $triple_room_recharge;
                            $temp_price += $roomPrice + $triple_room_recharge;
                        }
                        array_push($partial_total_price, $temp_price);
                    }
                    $general_reservation->setGenResTotalInSite($total_price);
                    $em->persist($general_reservation);

                    $arrayKidsAge = array();

                    $flag_1 = 0;
                    foreach ($resByOwn as $item) {
                        $ownership_reservation = $item->createReservation($general_reservation, $partial_total_price[$flag_1]);
                        if ($inmediatily_booking)
                            $ownership_reservation->setOwnResStatus(ownershipReservation::STATUS_AVAILABLE);


                        array_push($reservations, $ownership_reservation);

                        $ownership_photo = $em->getRepository('mycpBundle:ownership')->getOwnershipPhoto($ownership_reservation->getOwnResGenResId()->getGenResOwnId()->getOwnId());
                        array_push($array_photos, $ownership_photo);

                        $nightsCount = $service_time->nights($ownership_reservation->getOwnResReservationFromDate()->getTimestamp(), $ownership_reservation->getOwnResReservationToDate()->getTimestamp());
                        array_push($nigths, $nightsCount);

                        if ($item->getChildrenAges() != null) {
                            $arrayKidsAge[$item->getCartRoom()->getRoomNum()] = $item->getChildrenAges();
                        }

                        $em->persist($ownership_reservation);
                        $em->flush();
                        array_push($own_ids, $ownership_reservation->getOwnResId());
                        $flag_1++;
                    }
                    $general_reservation->setChildrenAges($arrayKidsAge);
                    $em->flush();
                    $this->createReservedPartner($general_reservation, $request, $min_date, $max_date, $id_ownership);
                    //update generalReservation dates
                    $em->getRepository("mycpBundle:generalReservation")->updateDates($general_reservation);
                    array_push($generalReservations, $general_reservation->getGenResId());

                    if ($general_reservation->getGenResOwnId()->getOwnInmediateBooking()) {
                        $smsService = $this->get("mycp.notification.service");
                        $smsService->sendInmediateBookingSMSNotification($general_reservation);

                    }

                    if ($inmediatily_booking) {
                        $session = $request->getSession();
                        $session->set("ri_ids", array($general_reservation->getGenResId()));
                    }

                }
            }
        } else {
            return false;
        }
        $locale = $this->get('translator')->getLocale();
        // Enviando mail al cliente
        if (!$inmediatily_booking) {
            $body = $this->render('FrontEndBundle:mails:email_check_available.html.twig', array(
                'user' => $user,
                'reservations' => $reservations,
                'ids' => $own_ids,
                'nigths' => $nigths,
                'photos' => $array_photos,
                'user_locale' => $locale
            ));

            if ($user != null) {
                $locale = $this->get('translator');
                $subject = $locale->trans('REQUEST_SENT');
                $service_email = $this->get('Email');
                $service_email->sendEmail(
                    $subject, 'reservation@mycasaparticular.com', 'MyCasaParticular.com', $user->getUserEmail(), $body
                );
            }
        }

        if (!$inmediatily_booking) {
            //Enviando mail al reservation team
            foreach ($generalReservations as $genResId) {
                //Enviando correo a solicitud@mycasaparticular.com
                \MyCp\FrontEndBundle\Helpers\ReservationHelper::sendingEmailToReservationTeamPartner($genResId, $em, $this, $service_email, $service_time, $request, 'solicitud.partner@mycasaparticular.com', 'no-reply@mycasaparticular.com');
            }
        }
        foreach ($arrayIdCart as $temp) {
            $cartItem = $em->getRepository('mycpBundle:cart')->find($temp);
            $em->remove($cartItem);
        }
        $em->flush();
        if (!$inmediatily_booking) //esta consultando la disponibilidad
            return true;
        else                      //esta haciendo una reserva
            return $own_ids;
    }

    public function createReservedPartner($general_reservation, $request, $min_date, $max_date, $id_ownership)
    {
        $em = $this->getDoctrine()->getManager();

        //Adding new reservation
        $clientName = $request->get("clientName");
        if ($clientName == "")
            $clientName = $request->get("partnerClientName");

        $clientId = $request->get("clientId");
        if ($clientId == "")
            $clientId = $request->get("partnerClientId");

        $clientCountry = $request->get("clientCountry");
        if ($clientCountry == "")
            $clientCountry = $request->get("partnerClientCountry");

        $clientComments = $request->get("clientComments");
        if ($clientComments == "")
            $clientComments = $request->get("partnerClientComments");

        $reservationNumber = $request->get("reservationNumber");
        if ($reservationNumber == "")
            $reservationNumber = $request->get("partnerReservationReference");

        $client = array(
            "clientName" => $clientName,
            "clientCountry" => $clientCountry,
            "clientId" => $clientId,
            "clientComments" => $clientComments,
        );

        $dateFrom = $min_date;
        $dateTo = $max_date;
//        $adults = $request->get("adults");
//        $children = $request->get("children");


        $accommodationId = $id_ownership;
        //$roomType = $request->get("roomType");
        //$roomsTotal = $request->get("roomsTotal");
        //$clientEmail= $request->get("clientEmail");


        $user = $this->getUser();
        $tourOperator = $em->getRepository("PartnerBundle:paTourOperator")->findOneBy(array(self::VAR_TOUR_OPERATOR => $user->getUserId()));
        $travelAgency = $tourOperator->getTravelAgency();
        $accommodation = $em->getRepository("mycpBundle:ownership")->find($accommodationId);

        $translator = $this->get('translator');
        $returnedObject = $em->getRepository("PartnerBundle:paReservation")->newReservation($general_reservation, $travelAgency, $client, $adults = 0, $children = 0, $dateFrom, $dateTo, $accommodation, $user, $this->container, $translator, $reservationNumber, null, null/*,$clientEmail*/);
        return true;
    }

    public function reservationsToCancelAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $timer = $this->get("Time");
        $user = $this->getUser();

        $currentTourOperator = $em->getRepository("PartnerBundle:paTourOperator")->findOneBy(array(self::VAR_TOUR_OPERATOR => $user->getUserId()));
        $currentTravelAgency = $currentTourOperator->getTravelAgency();
        $agencyPackage = $currentTravelAgency->getAgencyPackages()[0];
        $nomCancelFromAgency = $em->getRepository('mycpBundle:nomenclator')->findOneBy(array("nom_name" => "acpt_from_agency", "nom_category" => "agencyCancelPaymentType"));
        $tripleChargeRoom = $this->container->getParameter('configuration.triple.room.charge');

        //Todas las reservas pertenecen al mismo generalReservation
        $idsArray = $request->get('checkValues');
        $ownershipReservations = $em->getRepository("mycpBundle:ownershipReservation")->getByIds($idsArray);
        $generalReservation = $ownershipReservations[0]->getOwnResGenResId();
        $booking = $ownershipReservations[0]->getOwnResReservationBooking();

        $totalRooms = count($ownershipReservations);
        $totalNights = 0;
        $totalInSite = 0;
        $ch = 0;

        if ($agencyPackage->getPackage()->getCompletePayment()) { //Solo se cancela si la agencia tiene el paquete con pago completo

            $cancelPayment = new paCancelPayment();
            $cancelPayment->setBooking($ownershipReservations[0]->getOwnResReservationBooking());
            $cancelPayment->setCancelDate(new \DateTime());
            $cancelPayment->setGiveAgency(true);
            $cancelPayment->setUser($this->getUser());
            $cancelPayment->setType($nomCancelFromAgency);

            foreach ($ownershipReservations as $reservation) {
                $totalNights += $timer->nights($reservation->getOwnResReservationFromDate()->getTimestamp(), $reservation->getOwnResReservationToDate()->getTimestamp());
                $totalInSite += $reservation->getOwnResTotalInSite();
                $ch += $reservation->getOwnResTotalInSite();

                if ($reservation->getTripleRoomCharged())
                    $ch -= $tripleChargeRoom;

                $cancelPayment->addOwnershipReservation($reservation);
                $reservation->setOwnResStatus(ownershipReservation::STATUS_CANCELLED);
                $em->persist($reservation);
            }

            $em->persist($cancelPayment);
            $em->flush();

            $em->getRepository("mycpBundle:generalReservation")->changeReservationStatus($generalReservation);
            $totalNights = $totalNights / $totalRooms;
            $ch = $ch / $totalNights;

            //Calcular montos a devolver
            $cancelReservationService = $this->get("mycp.partner.cancelreservation.service");
            $cancelReservationService->calculatePendingPayments(true, new \DateTime(), $currentTravelAgency, $generalReservation, $booking, $ch, $totalInSite, $totalNights, $totalRooms, $cancelPayment);


            return new JsonResponse(array(
                self::SUCCESS => true,
                'id' => 'id_dashboard_booking_reserved',
                'html' => $this->renderView('PartnerBundle:Dashboard:booking_reserved.html.twig', array()),
                'msg' => self::MSG_RESERVAS_PENDIENTES
            ));
        } else {
            return new JsonResponse(
                array(self::SUCCESS => false,
                    'html' => null,
                    'message' => ""
                ));
        }
    }
}


