<?php

namespace MyCp\mycpBundle\Controller;

use MyCp\mycpBundle\Entity\batchType;
use MyCp\mycpBundle\Entity\ownershipReservation;
use MyCp\mycpBundle\Entity\room;
use MyCp\mycpBundle\Helpers\DataBaseTables;
use MyCp\mycpBundle\Helpers\FileIO;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Email;
use MyCp\mycpBundle\Entity\ownership;
use MyCp\mycpBundle\Entity\log;
use MyCp\mycpBundle\Entity\photo;
use MyCp\mycpBundle\Entity\user;
use MyCp\mycpBundle\Entity\photoLang;
use MyCp\mycpBundle\Entity\ownershipPhoto;
use MyCp\mycpBundle\Helpers\OwnershipStatuses;
use MyCp\mycpBundle\Entity\ownershipStatus;
use MyCp\mycpBundle\Helpers\BackendModuleName;
use MyCp\mycpBundle\Helpers\UserMails;
use MyCp\mycpBundle\Helpers\Operations;
use Symfony\Component\Validator\Constraints\RegexValidator;

class BackendAgencyController extends Controller {

    public function list_AgencyAction($items_per_page, Request $request) {
        //$service_security = $this->get('Secure');
        //$service_security->verifyAccess();
        $page = 1;
        $filter_active = $request->get('filter_active');
        $filter_country = $request->get('filter_country');
        $filter_name = $request->get('filter_name');
        $filter_owner = $request->get('filter_owner');
        $filter_email = $request->get('filter_email');
        $filter_package = $request->get('filter_package');
        $filter_date_created = $request->get('filter_code');
        if ($request->getMethod() == 'POST' && $filter_name == 'null' && $filter_active == 'null' && $filter_country == 'null' && $filter_package == 'null' &&
                $filter_email == 'null' && $filter_date_created == 'null'
        ) {
            $message = 'Debe llenar al menos un campo para filtrar.';
            $this->get('session')->getFlashBag()->add('message_error_local', $message);
            return $this->redirect($this->generateUrl('mycp_list_ownerships'));
        }

        if ($filter_active == 'null')
            $filter_active = '';
        if ($filter_name == 'null')
            $filter_name = '';
        if ($filter_country == 'null')
            $filter_country = '';
        if ($filter_owner == 'null')
            $filter_owner = '';
        if ($filter_email == 'null')
            $filter_email = '';
        if ($filter_date_created == 'null')
            $filter_date_created = '';
        if (isset($_GET['page']))
            $page = $_GET['page'];

        $em = $this->getDoctrine()->getManager();
        $paginator = $this->get('ideup.simple_paginator');
        $paginator->setItemsPerPage($items_per_page);
        $agencys = $paginator->paginate($em->getRepository('PartnerBundle:paTravelAgency')->getAll(
            $filter_active,
            $filter_name,
            $filter_country,
            $filter_owner,
            $filter_email,
            $filter_date_created
        ))->getResult();

        return $this->render('mycpBundle:agency:list.html.twig', array(
                    'agencys' => $agencys,
                    'items_per_page' => $items_per_page,
                    'current_page' => $page,
                    'total_items' => $paginator->getTotalItems(),
                    'filter_name' => $filter_name,
                    'filter_active' => $filter_active,
                    'filter_country' => $filter_country,
                    'filter_owner' => $filter_owner,
                    'filter_email' => $filter_email,
                    'filter_package' => $filter_package,
                    'filter_date_created' => $filter_date_created,
        ));
    }

    public function activeRoomAction($id_room, $activate) {
        $service_security = $this->get('Secure');
        $service_security->verifyAccess();
        $em = $this->getDoctrine()->getManager();
        $service_log = $this->get('log');

        $ro = $em->getRepository('mycpBundle:room')->find($id_room);
        $own = $ro->getRoomOwnership();

        $ro->setRoomActive($activate);
        $em->persist($ro);
        $em->flush();

        $service_log->saveLog($own->getLogDescription().' (Activar / Desactivar '.$ro->getLogDescription().')', BackendModuleName::MODULE_OWNERSHIP, log::OPERATION_UPDATE, DataBaseTables::ROOM);

        $rooms = $em->getRepository('mycpBundle:room')->findBy(array('room_ownership' => $own->getOwnId()));
        $count = count($rooms);
        $count_active = 0;
        $maximum_guests = 0;
        foreach ($rooms as $room) {
            if (!$room->getRoomActive())
                $count--;
            else {
                $count_active++;
                $maximum_guests += $room->getMaximumNumberGuests();
            }
        }

        $own->setOwnMaximumNumberGuests($maximum_guests);
        $own->setOwnRoomsTotal($count_active);
        if ($count <= 0) {
            $status = $em->getRepository('mycpBundle:ownershipstatus')->find(ownershipStatus::STATUS_INACTIVE);
            $own->setOwnStatus($status);
            $em->persist($own);
            $service_log->saveLog($own->getLogDescription()." (Estado Inactivo por desactivar todas las habitaciones)", BackendModuleName::MODULE_OWNERSHIP, log::OPERATION_UPDATE, DataBaseTables::OWNERSHIP);
        }

        $em->flush();

        return $this->redirect($this->generateUrl('mycp_edit_ownership', array('id_ownership' => $own->getOwnId())));
    }


    public function get_agency_namesAction() {
        $em = $this->getDoctrine()->getManager();
        $agencys = $em->getRepository('PartnerBundle:paTravelAgency')->findAll();
        return $this->render('mycpBundle:utils:agencys_names.html.twig', array('agencys' => $agencys));
    }

    public function get_owner_namesAction(){
        $em = $this->getDoctrine()->getManager();
        $owners = $em->getRepository('PartnerBundle:paTourOperator')->getOwners();
        return $this->render('mycpBundle:utils:agency_owner_names.html.twig', array('owners' => $owners));
    }

    public function get_packagesAction($post){
        $em = $this->getDoctrine()->getManager();
        $packages = $em->getRepository('PartnerBundle:paPackage')->findAll();

        $selected = '';
        if (!is_array($post))
            $selected = $post;

        if (isset($post['filter_package']))
            $selected = $post['filter_package'];
        /* else
          $selected = ownershipStatus::STATUS_IN_PROCESS; */

        return $this->render('mycpBundle:utils:agency_package.html.twig', array('packages' => $packages, 'selected' => $selected, 'post' => $post));

    }

}
