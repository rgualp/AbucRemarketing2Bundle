<?php

namespace MyCp\mycpBundle\Controller;

use MyCp\mycpBundle\Helpers\FileIO;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use MyCp\mycpBundle\Entity\unavailabilityDetails;
use MyCp\mycpBundle\Form\unavailabilityDetailsType;
use Symfony\Component\Validator\Constraints\NotBlank;
use MyCp\mycpBundle\Helpers\SyncStatuses;
use MyCp\mycpBundle\Helpers\Dates;
use MyCp\mycpBundle\Helpers\BackendModuleName;

class BackendUnavailabilityDetailsController extends Controller {

    public function listAction($items_per_page, Request $request) {
        $service_security = $this->get('Secure');
        $service_security->verifyAccess();
        $page = 1;
        $filter_active = $request->get('filter_active');

        $filter_province = $request->get('filter_province');
        $filter_name = $request->get('filter_name');
        $filter_municipality = $request->get('filter_municipality');
        $filter_type = $request->get('filter_type');
        $filter_category = $request->get('filter_category');
        $filter_code = $request->get('filter_code');
        $filter_destination = $request->get('filter_destination');
        if ($request->getMethod() == 'POST' && $filter_name == 'null' && $filter_active == 'null' && $filter_province == 'null' && $filter_municipality == 'null' &&
                $filter_type == 'null' && $filter_category == 'null' && $filter_code == 'null' && $filter_destination == 'null'
        ) {
            $message = 'Debe llenar al menos un campo para filtrar.';
            $this->get('session')->getFlashBag()->add('message_error_local', $message);
            return $this->redirect($this->generateUrl('mycp_list_ownerships'));
        }
        if ($filter_code == 'null')
            $filter_code = '';
        if ($filter_name == 'null')
            $filter_name = '';
        if (isset($_GET['page']))
            $page = $_GET['page'];

        $em = $this->getDoctrine()->getEntityManager();
        $paginator = $this->get('ideup.simple_paginator');
        $paginator->setItemsPerPage($items_per_page);
        $ownerships = $paginator->paginate($em->getRepository('mycpBundle:ownership')->getAll(
                                $filter_code, $filter_active, $filter_category, $filter_province, $filter_municipality, $filter_destination, $filter_type, $filter_name
                ))->getResult();

        $service_log = $this->get('log');
        $service_log->saveLog('Visit', BackendModuleName::MODULE_UNAVAILABILITY_DETAILS);

        return $this->render('mycpBundle:unavailabilityDetails:list.html.twig', array(
                    'ownerships' => $ownerships,
                    'items_per_page' => $items_per_page,
                    'current_page' => $page,
                    'total_items' => $paginator->getTotalItems(),
                    'filter_code' => $filter_code,
                    'filter_name' => $filter_name,
                    'filter_active' => $filter_active,
                    'filter_category' => $filter_category,
                    'filter_province' => $filter_province,
                    'filter_municipality' => $filter_municipality,
                    'filter_type' => $filter_type,
                    'filter_destination' => $filter_destination,
        ));
    }

    public function list_roomsAction($id_ownership, $items_per_page, Request $request) {
        $service_security = $this->get('Secure');
        $service_security->verifyAccess();
        $page = 1;
        if (isset($_GET['page']))
            $page = $_GET['page'];
        $em = $this->getDoctrine()->getEntityManager();
        $paginator = $this->get('ideup.simple_paginator');
        $paginator->setItemsPerPage($items_per_page);
        $rooms = $paginator->paginate($em->getRepository('mycpBundle:room')->findBy(array('room_ownership' => $id_ownership, "room_active" => true)))->getResult();

        $ownership = $em->getRepository('mycpBundle:ownership')->find($id_ownership);
        return $this->render('mycpBundle:unavailabilityDetails:roomsList.html.twig', array(
                    'rooms' => $rooms,
                    'id_ownership' => $id_ownership,
                    'ownership' => $ownership,
                    'items_per_page' => $items_per_page,
                    'current_page' => $page,
                    'total_items' => $paginator->getTotalItems(),
        ));
    }

    public function getUnavailabilityDetailsJSONAction($idRoom,Request $request) {
        $em = $this->getDoctrine()->getManager();
        $user = $this->get('security.context')->getToken()->getUser();
        $startParam = $request->get('start');
        $endParam = $request->get('end');

        $unDet = $em->getRepository('mycpBundle:unavailabilityDetails')->getAllNotDeletedByDateAndRoom($idRoom,$startParam, $endParam);
        $reser = $em->getRepository('mycpBundle:ownershipReservation')->getReservationReservedByRoom($idRoom,$startParam, $endParam);

        $unDetCounter = count($unDet);
        $reservationCounter = count($reser);

        $now = new \DateTime();

        return $this->render('mycpBundle:unavailabilityDetails:room_calendar.json.twig', array("details" => $unDet, "reservations" => $reser, "detailCount" => $unDetCounter, 'reservationCount' => $reservationCounter, 'now' => $now));
    }

    public function room_detailsAction($id_room, $num_room, $items_per_page, Request $request) {
        $service_security = $this->get('Secure');
        $service_security->verifyAccess();
        $page = 1;
        if (isset($_GET['page']))
            $page = $_GET['page'];
        $em = $this->getDoctrine()->getEntityManager();
        $paginator = $this->get('ideup.simple_paginator');
        $paginator->setItemsPerPage($items_per_page);
        $details = $paginator->paginate($em->getRepository('mycpBundle:unavailabilityDetails')->getRoomDetails($id_room))->getResult();
        $room = $em->getRepository('mycpBundle:room')->find($id_room);
        $ownership = $room->getRoomOwnership();
        return $this->render('mycpBundle:unavailabilityDetails:detailsList.html.twig', array(
                    'room' => $room,
                    'details' => $details,
                    'num_room' => $num_room,
                    'id_room' => $id_room,
                    'ownership' => $ownership,
                    'items_per_page' => $items_per_page,
                    'current_page' => $page,
                    'total_items' => $paginator->getTotalItems(),
        ));
    }

    public function newAction($id_room, $num_room, Request $request) {
        $service_security = $this->get('Secure');
        $service_security->verifyAccess();

        $em = $this->getDoctrine()->getEntityManager();
        $room = $em->getRepository('mycpBundle:room')->find($id_room);
        $ownership = $room->getRoomOwnership();

        $uDetails = new unavailabilityDetails();
        $form = $this->createForm(new unavailabilityDetailsType, $uDetails);
        if ($request->getMethod() == 'POST') {
            $post_form = $request->get('mycp_mycpbundle_unavailabilitydetailstype');
            $form->handleRequest($request);
            if ($form->isValid()) {
                $date_from = Dates::createFromString($post_form['ud_from_date']);
                $date_to = Dates::createFromString($post_form['ud_to_date']);

                if ($date_from > $date_to) {
                    $this->get('session')->getFlashBag()->add('message_error_main', "La fecha Desde tiene que ser menor o igual que la fecha Hasta");
                } else {
                    $dExist = $em->getRepository('mycpBundle:unavailabilityDetails')->findBy(array(
                        "ud_from_date" => $date_from,
                        "ud_to_date" => $date_to,
                        "room" => $id_room
                    ));

                    if (count($dExist) == 0) {
                        $uDetails->setUdFromDate($date_from)
                                ->setUdToDate($date_to)
                                ->setUdReason($post_form['ud_reason'])
                                ->setRoom($room);
                        $em->persist($uDetails);
                        $em->flush();

                        //Update iCal of selected room
                        $message = $this->updateICal($room);

                        $message = 'Detalle de no disponibilidad añadido satisfactoriamente. ' . $message;
                        $this->get('session')->getFlashBag()->add('message_ok', $message);

                        $service_log = $this->get('log');
                        $service_log->saveLog('Create unavailable detaile from ' . $post_form['ud_from_date'] . ' to ' . $post_form['ud_to_date'], BackendModuleName::MODULE_UNAVAILABILITY_DETAILS);
                    }
                    else
                    {
                        $message = 'Ya existe no disponibilidad para las fechas '.$date_from->format("d/m/Y")." al ".$date_to->format("d/m/Y");
                        $this->get('session')->getFlashBag()->add('message_ok', $message);
                    }

                    return $this->redirect($this->generateUrl('mycp_list_room_details_unavailabilityDetails', array('id_room' => $id_room, 'num_room' => $num_room)));
                }
            }
        }

        return $this->render('mycpBundle:unavailabilityDetails:new.html.twig', array(
                    'room' => $room,
                    'num_room' => $num_room,
                    'id_room' => $id_room,
                    'ownership' => $ownership,
                    'form' => $form->createView()
        ));
    }

    public function editAction($id_detail, $num_room, Request $request) {
        $service_security = $this->get('Secure');
        $service_security->verifyAccess();

        $em = $this->getDoctrine()->getEntityManager();
        $uDetails = $em->getRepository('mycpBundle:unavailabilityDetails')->find($id_detail);
        $room = $uDetails->getRoom();
        $ownership = $room->getRoomOwnership();

        $form = $this->createForm(new unavailabilityDetailsType, $uDetails);
        if ($request->getMethod() == 'POST') {
            $post_form = $request->get('mycp_mycpbundle_unavailabilitydetailstype');
            $form->handleRequest($request);
            if ($form->isValid()) {
                $uDetails->setUdFromDate(Dates::createFromString($post_form['ud_from_date']))
                        ->setUdToDate(Dates::createFromString($post_form['ud_to_date']))
                        ->setUdReason($post_form['ud_reason'])
                        ->setRoom($room);
                //       ->setSyncSt(SyncStatuses::UPDATED);
                $em->persist($uDetails);
                $em->flush();

                //Update iCal of selected room
                $message = $this->updateICal($room);

                $message = 'Detalle de no disponibilidad modificado satisfactoriamente. ' . $message;
                $this->get('session')->getFlashBag()->add('message_ok', $message);

                $service_log = $this->get('log');
                $service_log->saveLog('Update unavailable detaile from ' . $post_form['ud_from_date'] . ' to ' . $post_form['ud_to_date'], BackendModuleName::MODULE_UNAVAILABILITY_DETAILS);

                return $this->redirect($this->generateUrl('mycp_list_room_details_unavailabilityDetails', array('id_room' => $room->getRoomId(), 'num_room' => $num_room)));
            }
        }

        return $this->render('mycpBundle:unavailabilityDetails:new.html.twig', array(
                    'room' => $room,
                    'num_room' => $num_room,
                    'id_room' => $room->getRoomId(),
                    'ownership' => $ownership,
                    'form' => $form->createView(),
                    'edit_detail' => $uDetails->getUdId()
        ));
    }

    public function deleteAction($id_detail, $num_room) {
        $service_security = $this->get('Secure');
        $service_security->verifyAccess();
        $em = $this->getDoctrine()->getEntityManager();
        $uDetails = $em->getRepository('mycpBundle:unavailabilityDetails')->find($id_detail);
        $room = $uDetails->getRoom();

        $uDetails->setSyncSt(SyncStatuses::DELETED);
        $em->persist($uDetails);
        $em->flush();

        //Update iCal of selected room
        $message = $this->updateICal($room);

        $message = 'Detalle de no disponibilidad eliminado satisfactoriamente. ' . $message;
        $this->get('session')->getFlashBag()->add('message_ok', $message);

        $service_log = $this->get('log');
        $service_log->saveLog('Delete unavailable detail from ' . $uDetails->getUdFromDate()->format('d/M/Y') . ' to ' . $uDetails->getUdToDate()->format('d/M/Y'), BackendModuleName::MODULE_UNAVAILABILITY_DETAILS);

        return $this->redirect($this->generateUrl('mycp_list_room_details_unavailabilityDetails', array('id_room' => $room->getRoomId(), 'num_room' => $num_room)));
    }

    function downloadFileAction($fileName){
        $filePath = $this->container->getParameter("configuration.dir.udetails");
        return FileIO::download($filePath, $fileName);
    }

    private function updateICal($room) {
        try {
            $calendarService = $this->get('mycp.service.calendar');
            $calendarService->createICalForRoom($room->getRoomId(), $room->getRoomCode());
            return "Se actualizó satisfactoriamente el fichero .ics asociado a esta habitación.";
        } catch (\Exception $e) {
            return "Ha ocurrido un error mientras se actualizaba el fichero .ics de la habitación. Error: " . $e->getMessage();
        }
    }

    public function createEventCallbackAction(Request $request) {
        /*$service_security = $this->get('Secure');
        $service_security->verifyAccess();*/

        $em = $this->getDoctrine()->getEntityManager();
        $idRoom = $request->get("idRoom");
        $start = $request->get("start");
        $end = $request->get("end");
        $reason = $request->get("reason");
        $room = $em->getRepository('mycpBundle:room')->find($idRoom);
        $errorMessage = "";
        $eventId = 0;
        //$ownership = $room->getRoomOwnership();

        $date_from = Dates::createFromString($start);
        $date_to = Dates::createFromString($end);

        $uDetails = $em->getRepository('mycpBundle:unavailabilityDetails')->findOneBy(array(
            "ud_from_date" => $date_from,
            "ud_to_date" => $date_to,
            "room" => $idRoom
        ));

        if($uDetails == null)
            $uDetails = new unavailabilityDetails();

        $uDetails->setUdFromDate($date_from)
            ->setUdToDate($date_to)
            ->setUdReason($reason)
            ->setRoom($room);
        $em->persist($uDetails);
        $em->flush();

        $eventId = $uDetails->getId();

        //Update iCal of selected room
        $message = $this->updateICal($room);

        $service_log = $this->get('log');
        $service_log->saveLog('Create unavailable detaile from ' . $date_from->format("d/m/Y") . ' to ' . $date_to->format("d/m/Y"), BackendModuleName::MODULE_UNAVAILABILITY_DETAILS);

        $response = array(
            "errorMessage" => $errorMessage,
            "eventId" => "1-".$eventId,
            "title" => "Hab. #".$room->getRoomNum()." - No disponible",
            "url" => $this->generateUrl("mycp_edit_unavailabilityDetails", array("id_detail" => $eventId, "num_room" => $room->getRoomNum()))
        );


        return new Response(json_encode($response), 200);
    }

    public function filesAction()
    {
        $uDetailsDirectory = $this->container->getParameter("configuration.dir.udetails");

        $files = FileIO::getFilesInDirectory($uDetailsDirectory);

        return $this->render('mycpBundle:unavailabilityDetails:files.html.twig', array(
            'files' => $files
        ));
    }

}
