<?php

namespace MyCp\CasaModuleBundle\Controller;

use MyCp\CasaModuleBundle\Form\ownershipStepPhotosType;
use MyCp\mycpBundle\Entity\ownershipPhoto;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use MyCp\CasaModuleBundle\Form\ownershipStep1Type;
use Symfony\Component\HttpFoundation\Request;

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
        ));

    }

}
