<?php

namespace MyCp\CasaModuleBundle\Controller;

use Abuc\RemarketingBundle\Event\JobEvent;
use MyCp\CasaModuleBundle\Form\ownershipStepPhotosType;
use MyCp\mycpBundle\Entity\generalReservation;
use MyCp\mycpBundle\Entity\notification;
use MyCp\mycpBundle\Entity\ownershipPhoto;
use MyCp\mycpBundle\Entity\ownershipReservation;
use MyCp\mycpBundle\Helpers\BackendModuleName;
use MyCp\mycpBundle\JobData\GeneralReservationJobData;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use MyCp\CasaModuleBundle\Form\ownershipStep1Type;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class NotificationController extends Controller
{
    public function notificationsActivesAction($items_per_page, Request $request){
        $em=$this->getDoctrine()->getManager();
        $user=$this->getUser();
        $page = 1;
        if(empty($user->getUserUserCasa()))
            return new NotFoundHttpException('El usuario no es usuario casa');

        $paginator = $this->get('ideup.simple_paginator');
        $paginator->setItemsPerPage($items_per_page);

        $filters = $request->get('filter');

        if ($user->getUserRole() == 'ROLE_CLIENT_CASA'){
            $ownership =  $user->getUserUserCasa()[0]->getUserCasaOwnership();

            $notifications = $em->getRepository('mycpBundle:notification')->getActives($ownership->getOwnId(), $filters);
        }
        /*else {
            $userCasa = $em->getRepository('mycpBundle:userCasa')->getByUser($user->getUserId());
            $reser = $em->getRepository('mycpBundle:generalReservation')->getByUserCasa($filter_date_reserve, $filter_offer_number, $filter_reference, $filter_date_from, $filter_date_to, $sort_by, "", $userCasa, $filter_status);
        }*/

        $notifications = $paginator->paginate($notifications)->getResult();

        if(!isset($filters)){
            $filters = array();
        }

        return $this->render('MyCpCasaModuleBundle:notifications:list.html.twig',array(
            'ownership'=>$ownership,
            'filters'=>$filters,
            'notifications' => $notifications,
            'items_per_page' => $items_per_page,
            'current_page' => $page,
            'total_items' => $paginator->getTotalItems(),
            'dashboard'=>true
        ));

    }

    public function notificationsInactivesAction($items_per_page, Request $request){
        $em=$this->getDoctrine()->getManager();
        $user=$this->getUser();
        $page = 1;
        if(empty($user->getUserUserCasa()))
            return new NotFoundHttpException('El usuario no es usuario casa');

        $paginator = $this->get('ideup.simple_paginator');
        $paginator->setItemsPerPage($items_per_page);

        $filters = $request->get('filter');

        if ($user->getUserRole() == 'ROLE_CLIENT_CASA'){
            $ownership =  $user->getUserUserCasa()[0]->getUserCasaOwnership();
            $notifications = $em->getRepository('mycpBundle:notification')->getInactives($ownership->getOwnId(), $filters);
        }
        /*else {
            $userCasa = $em->getRepository('mycpBundle:userCasa')->getByUser($user->getUserId());
            $reser = $em->getRepository('mycpBundle:generalReservation')->getByUserCasa($filter_date_reserve, $filter_offer_number, $filter_reference, $filter_date_from, $filter_date_to, $sort_by, "", $userCasa, $filter_status);
        }*/

        $notifications = $paginator->paginate($notifications)->getResult();

        return $this->render('MyCpCasaModuleBundle:notifications:listin.html.twig',array(
            'ownership'=>$ownership,
            'notifications' => $notifications,
            'filters'=>$filters,
            'items_per_page' => $items_per_page,
            'current_page' => $page,
            'total_items' => $paginator->getTotalItems(),
            'dashboard'=>true
        ));

    }

    public function notificationrespAction($id, $act, Request $request){
        $em=$this->getDoctrine()->getManager();
        $user=$this->getUser();
        if(empty($user->getUserUserCasa()))
            return new NotFoundHttpException('El usuario no es usuario casa');

        if ($user->getUserRole() == 'ROLE_CLIENT_CASA'){
            $notification = $em->getRepository('mycpBundle:notification')->find($id);

            switch($act){
                case "1":
                    $notification->setActionResponse(notification::ACTION_RESPONSE_AVAILABLE);
                    $em->persist($notification);
                    $this->notificationresp($notification, 1);
                    break;
                case "2":
                    $notification->setActionResponse(notification::ACTION_RESPONSE_UNAVAILABLE);
                    $em->persist($notification);
                    $this->notificationresp($notification, 0);
                    break;
                case "3":
                    $notification->setActionResponse(notification::ACTION_RESPONSE_CLOSE);
                    $em->persist($notification);
                    break;
                case "4":
                    $em->remove($notification);
                    break;
            }

            $em->flush();
        }

        if($act == "4"){
            return $this->redirect($this->generateUrl('my_casa_module_inactives_notifications'));
        }
        return $this->redirect($this->generateUrl('my_casa_module_actives_notifications'));
    }

    public function notificationresp(notification $notification, $availability){
        $em=$this->getDoctrine()->getManager();
        $generalReservation = $notification->getReservation();
        $ownership_reservations = $generalReservation->getOwnReservations();
        foreach ($ownership_reservations as $ownership_reservation) {
            if($availability == 1){
                $ownership_reservation->setOwnResStatus(ownershipReservation::STATUS_AVAILABLE);
            }
            else{
                $ownership_reservation->setOwnResStatus(ownershipReservation::STATUS_NOT_AVAILABLE);
            }
            $em->persist($ownership_reservation);
            $em->flush();
        }

        if($availability == 1) {
            $generalReservation->setGenResStatus(generalReservation::STATUS_AVAILABLE);
        }
        else {
            $generalReservation->setGenResStatus(generalReservation::STATUS_NOT_AVAILABLE);
        }

        /*Envio de Email*/
        $container = $this;
        $id_reservation = $generalReservation->getGenResId();
        $service_email = $container->get('Email');
        $service_email->sendReservation($id_reservation, "Disponibilidad dada por propietario desde MyCasa Renta", false);

        if ($generalReservation->getGenResStatus() == generalReservation::STATUS_AVAILABLE){
            // inform listeners that a reservation was sent out
            $dispatcher = $container->get('event_dispatcher');
            $eventData = new GeneralReservationJobData($id_reservation);
            $dispatcher->dispatch('mycp.event.reservation.sent_out', new JobEvent($eventData));
        }
    }
}
