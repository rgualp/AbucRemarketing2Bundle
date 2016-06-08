<?php

namespace MyCp\CasaModuleBundle\Controller;

use MyCp\CasaModuleBundle\Form\ownershipStepPhotosType;
use MyCp\mycpBundle\Entity\ownershipPhoto;
use MyCp\mycpBundle\Helpers\BackendModuleName;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use MyCp\CasaModuleBundle\Form\ownershipStep1Type;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ReservationController extends Controller
{
    public function reservationsAction($items_per_page, Request $request){
        $em=$this->getDoctrine()->getManager();
        $user=$this->getUser();
        $page = 1;
        if(empty($user->getUserUserCasa()))
            return new NotFoundHttpException('El usuario no es usuario casa');

        $filter_date_reserve = $request->get('filter_date_reserve');
        $filter_offer_number = $request->get('filter_offer_number');
        $filter_reference = $request->get('filter_reference');
        $filter_date_from = $request->get('filter_date_from');
        $filter_date_to = $request->get('filter_date_to');
        $filter_status = $request->get('filter_status');
        $price = 0;
        $sort_by = $request->get('sort_by');

        $filter_date_reserve = str_replace('_', '/', $filter_date_reserve);
        $filter_date_from = str_replace('_', '/', $filter_date_from);
        $filter_date_to = str_replace('_', '/', $filter_date_to);

        if ($filter_date_reserve == 'null')
            $filter_date_reserve = '';
        if ($filter_offer_number == 'null')
            $filter_offer_number = '';
        if ($filter_reference == 'null')
            $filter_reference = '';
        if ($filter_date_from == 'null')
            $filter_date_from = '';
        if ($filter_date_to == 'null')
            $filter_date_to = '';
        if ($sort_by == 'null')
            $sort_by = '';
        if ($filter_status == 'null')
            $filter_status = '';

        $paginator = $this->get('ideup.simple_paginator');
        $paginator->setItemsPerPage($items_per_page);


        if ($user->getUserRole() != 'ROLE_CLIENT_CASA')
            $reser = $em->getRepository('mycpBundle:generalReservation')->getAll($filter_date_reserve, $filter_offer_number, $filter_reference, $filter_date_from, $filter_date_to, $sort_by, "", $filter_status);
        else {
            $userCasa = $em->getRepository('mycpBundle:userCasa')->getByUser($user->getUserId());
            $reser = $em->getRepository('mycpBundle:generalReservation')->getByUserCasa($filter_date_reserve, $filter_offer_number, $filter_reference, $filter_date_from, $filter_date_to, $sort_by, "", $userCasa, $filter_status);
        }

        $reservations = $paginator->paginate($reser)->getResult();
        $filter_date_reserve_twig = str_replace('/', '_', $filter_date_reserve);
        $filter_date_from_twig = str_replace('/', '_', $filter_date_from);
        $filter_date_to_twig = str_replace('/', '_', $filter_date_to);
        $ownership=  $user->getUserUserCasa()[0]->getUserCasaOwnership();

        return $this->render('MyCpCasaModuleBundle:reservation:list.html.twig',array(
            'ownership'=>$ownership,
            'reservations' => $reservations,
            'filter_date_reserve' => $filter_date_reserve,
            'filter_offer_number' => $filter_offer_number,
            'filter_reference' => $filter_reference,
            'filter_date_from' => $filter_date_from,
            'filter_date_to' => $filter_date_to,
            'sort_by' => $sort_by,
            'filter_status' => $filter_status,
            'filter_date_reserve_twig' => $filter_date_reserve_twig,
            'filter_date_from_twig' => $filter_date_from_twig,
            'filter_date_to_twig' => $filter_date_to_twig,
            'items_per_page' => $items_per_page,
            'current_page' => $page,
            'total_items' => $paginator->getTotalItems(),
            'dashboard'=>true
        ));

    }

    public function detailAction(Request $request) {
        $id_reservation = $request->get("idReservation");
        $em = $this->getDoctrine()->getManager();
        $reservation = $em->getRepository('mycpBundle:generalReservation')->find($id_reservation);
        $ownership_reservations = $em->getRepository('mycpBundle:ownershipReservation')->findBy(array('own_res_gen_res_id' => $id_reservation));

        $service_time = $this->get('time');
        $dates = $service_time->datesBetween($reservation->getGenResFromDate()->format('Y-m-d'), $reservation->getGenResToDate()->format('Y-m-d'));


        $user = $em->getRepository('mycpBundle:userTourist')->findOneBy(array('user_tourist_user' => $reservation->getGenResUserId()));
        $array_nights = array();
        $rooms = array();
        foreach ($ownership_reservations as $res) {
            $nights = $service_time->nights($res->getOwnResReservationFromDate()->getTimestamp(), $res->getOwnResReservationToDate()->getTimestamp());
            array_push($rooms, $em->getRepository('mycpBundle:room')->find($res->getOwnResSelectedRoomId()));
            array_push($array_nights, $nights);
        }

        array_pop($dates);
        $content = $this->renderView('MyCpCasaModuleBundle:reservation:detail.html.twig', array(
            'reservation' => $reservation,
            'user' => $user,
            'reservations' => $ownership_reservations,
            'rooms' => $rooms,
            'nights' => $array_nights,
            'id_reservation' => $id_reservation));

        return new Response($content, 200);
    }

    public function clientsAction($items_per_page, Request $request)
    {
        $page = 1;
        $filter_user_name = $request->get('filter_user_name');
        $filter_user_email = $request->get('filter_user_email');
        $filter_user_country = $request->get('filter_user_country');

        $sort_by = $request->get('sort_by');
        if ($request->getMethod() == 'POST' && ($sort_by == "" || $sort_by == "null" || $sort_by == "0") && $filter_user_name == 'null' && $filter_user_email == 'null' &&
            $filter_user_country == 'null') {
            $message = 'Debe llenar al menos un campo para filtrar o seleccionar un criterio de ordenación.';
            $this->get('session')->getFlashBag()->add('message_error_local', $message);
            return $this->redirect($this->generateUrl('mycp_list_readonly_reservations'));
        }
        if ($filter_user_name == 'null')
            $filter_user_name = '';
        if ($filter_user_email == 'null')
            $filter_user_email = '';
        if ($filter_user_country == 'null')
            $filter_user_country = '';
        if ($sort_by == 'null')
            $sort_by = '';

        if (isset($_GET['page']))
            $page = $_GET['page'];

        $em = $this->getDoctrine()->getEntityManager();
        $paginator = $this->get('ideup.simple_paginator');
        $paginator->setItemsPerPage($items_per_page);

        $user = $this->get('security.context')->getToken()->getUser();

        if ($user->getUserRole() != 'ROLE_CLIENT_CASA')
            $reser = $em->getRepository('mycpBundle:generalReservation')->getUsers($filter_user_name, $filter_user_email, '', $filter_user_country, $sort_by);
        else {
            $userCasa = $em->getRepository('mycpBundle:userCasa')->getByUser($user->getUserId());
            $reser = $em->getRepository('mycpBundle:generalReservation')->getUsers($filter_user_name, $filter_user_email, '', $filter_user_country, $sort_by, $userCasa->getUserCasaOwnership()->getOwnId());
        }

        $reservations = $paginator->paginate($reser)->getResult();

        $service_log = $this->get('log');
        $service_log->saveLog('Visit', BackendModuleName::MODULE_RESERVATION);

        $ownership=  $user->getUserUserCasa()[0]->getUserCasaOwnership();

        return $this->render('MyCpCasaModuleBundle:reservation:clients.html.twig', array(
            'ownership'=>$ownership,
            'reservations' => $reservations,
            'items_per_page' => $items_per_page,
            'current_page' => $page,
            'total_items' => $paginator->getTotalItems(),
            'filter_user_name' => $filter_user_name,
            'filter_user_email' => $filter_user_email,
            'filter_user_country' => $filter_user_country,
            'sort_by' => $sort_by,
            'dashboard'=>true
        ));
    }

    public function clientDetailAction(Request $request) {
        $id_client = $request->get("idClient");
        $service_time = $this->get('time');

        $em = $this->getDoctrine()->getManager();
        $client = $em->getRepository('mycpBundle:user')->find($id_client);
        $user = $this->get('security.context')->getToken()->getUser();
        $userCasa = $em->getRepository('mycpBundle:userCasa')->getByUser($user->getUserId());
        $reservations = $em->getRepository('mycpBundle:generalReservation')->getByUser($id_client, $userCasa->getUserCasaOwnership()->getOwnId(), true);
        $userTourist = $em->getRepository('mycpBundle:userTourist')->findBy(array('user_tourist_user' => $id_client));
        $price = 0;
        $total_nights = array();

        foreach ($reservations as $reservation) {
            $temp_total_nights = 0;
            $owns_res = $em->getRepository('mycpBundle:ownershipReservation')->findBy(array('own_res_gen_res_id' => $reservation['gen_res_id']));

            foreach ($owns_res as $own) {
                $nights = $service_time->nights($own->getOwnResReservationFromDate()->getTimestamp(), $own->getOwnResReservationToDate()->getTimestamp());
                $temp_total_nights+=$nights;
            }
            array_push($total_nights, $temp_total_nights);
        }

        $content = $this->renderView('MyCpCasaModuleBundle:reservation:clientDetail.html.twig', array(
            'total_nights' => $total_nights,
            'reservations' => $reservations,
            'client' => $client,
            'errors' => '',
            'tourist' => $userTourist[0]));

        return new Response($content, 200);
    }

}
