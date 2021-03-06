<?php

namespace MyCp\mycpBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use MyCp\mycpBundle\Entity\unavailabilityDetails;
use MyCp\mycpBundle\Form\lodgingUnavailabilityDetailsType;
use Symfony\Component\Validator\Constraints\NotBlank;
use MyCp\mycpBundle\Helpers\SyncStatuses;
use MyCp\mycpBundle\Helpers\Dates;
use MyCp\mycpBundle\Helpers\BackendModuleName;

class LodgingUnavailabilityDetailsController extends Controller {

    public function get_ud_as_jsonAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $user = $this->get('security.context')->getToken()->getUser();
        $startParam = $request->get('start');
        $endParam = $request->get('end');
        $unDet = array();
        $reser = array();


        if ($user->getUserRole() != 'ROLE_CLIENT_CASA') {
            $unDet = $em->getRepository('mycpBundle:unavailabilityDetails')->getAllNotDeletedByDate($startParam, $endParam);
            $reser = $em->getRepository('mycpBundle:ownershipReservation')->getReservationReserved($startParam, $endParam);
        } else {
            $user_casa = $em->getRepository('mycpBundle:userCasa')->getByUser($user->getUserId());
            $ownership = $user_casa->getUserCasaOwnership();
            $unDet = $em->getRepository('mycpBundle:unavailabilityDetails')->getAllNotDeletedByDateAndOwnership($ownership->getOwnId(), $startParam, $endParam);
            $reser = $em->getRepository('mycpBundle:ownershipReservation')->getReservationReservedByOwnershipAndDate($ownership->getOwnId(), $startParam, $endParam);
        }

        $unDetCounter = count($unDet);
        $reservationCounter = count($reser);

        $now = new \DateTime();

        return $this->render('mycpBundle:unavailabilityDetails:ud_as_event.json.twig', array("details" => $unDet, "reservations" => $reser, "detailCount" => $unDetCounter, 'reservationCount' => $reservationCounter, 'now' => $now));
    }

    public function getRoomMiniCalendarJSONAction($idRoom,Request $request) {
        $em = $this->getDoctrine()->getManager();
        $user = $this->get('security.context')->getToken()->getUser();
        $now = new \DateTime();
        $year = $request->get('year');
        $month = $request->get('month');

        $startParam = $year.'-'.$month.'-'.'01';
        $endParam = date("Y-m-t", strtotime($startParam));

        $unDet = $em->getRepository('mycpBundle:unavailabilityDetails')->getAllNotDeletedByDateAndRoom($idRoom, $startParam, $endParam);
        $reser = $em->getRepository('mycpBundle:ownershipReservation')->getReservationReservedByRoomAndDate($idRoom, $startParam, $endParam);


        $unDetCounter = count($unDet);
        $reservationCounter = count($reser);

        $reservations = array();
        foreach($reser as $reservation) {
            $start = $reservation["own_res_reservation_from_date"];
            $end = $reservation["own_res_reservation_to_date"];
            $interval = new \DateInterval("P1D");

            $range = new \DatePeriod($start, $interval, $end);

            foreach ($range as $date) {
                $reservations[] = array(
                    "date" => $date->format("Y-m-d"),
                    'status' => $reservation["own_res_status"]
                );
            }
        }

        $uDetails = array();
        foreach($unDet as $uDetail) {
            $start = $uDetail->getUdFromDate();
            $end = date_modify($uDetail->getUdToDate(), "+1 day");
            $interval = new \DateInterval("P1D");

            $range = new \DatePeriod($start, $interval, $end);

            foreach ($range as $date) {
                $uDetails[] = $date->format("Y-m-d");
            }
        }

        return $this->render('mycpBundle:unavailabilityDetails:roomMiniCalendar.json.twig',
            array("details" => $uDetails,
                "reservations" => $reservations,
                "detailCount" => $unDetCounter,
                'reservationCount' => $reservationCounter,
                'now' => $now));
    }

    public function get_calendarAction($edit_detail, Request $request) {
        $service_security = $this->get('Secure');
        $service_security->verifyAccess();
        $em = $this->getDoctrine()->getManager();
        $user = $this->get('security.context')->getToken()->getUser();
        $data = array();
        $user_casa = $em->getRepository('mycpBundle:userCasa')->getByUser($user->getUserId());
        $data['ownership'] = $user_casa->getUserCasaOwnership()->getOwnId();
        $rooms = $em->getRepository("mycpBundle:room")->findBy(array("room_ownership" => $data["ownership"]));


        $data["today"] = new \DateTime();
        $hasError = false;

        if ($request->getQueryString() != null && $request->getQueryString() != "") {
            $qValue = explode("=", $request->getQueryString());
            $edit_detail = $qValue[1];
        }

        if ($edit_detail != null) {
            $uDetails = $em->getRepository("mycpBundle:unavailabilityDetails")->find($edit_detail);
            $idRoom = $uDetails->getRoom()->getRoomId();
            $uDetails->setRoom($idRoom);
        }
        else
            $uDetails = new unavailabilityDetails();
        $form = $this->createForm(new lodgingUnavailabilityDetailsType($rooms), $uDetails);
        if ($request->getMethod() == 'POST') {
            $hasError = !$this->manageData($request, $form, $em, $uDetails, ($edit_detail != null));

            if (!$hasError)
                return $this->redirect($this->generateUrl('mycp_lodging_unavailabilityDetails_calendar'));
        }


        if ($edit_detail != null)
            return $this->render('mycpBundle:unavailabilityDetails:calendar_view.html.twig', array('data' => $data,
                        "hasError" => $hasError,
                        'form' => $form->createView(), "edit_detail" => $edit_detail));
        else
            return $this->render('mycpBundle:unavailabilityDetails:calendar_view.html.twig', array('data' => $data,
                        "hasError" => $hasError,
                        'form' => $form->createView()));
    }

    public function newAction($id_room, Request $request) {
        $service_security = $this->get('Secure');
        $service_security->verifyAccess();

        $em = $this->getDoctrine()->getManager();
        $room = $em->getRepository('mycpBundle:room')->find($id_room);
        $ownership = $room->getRoomOwnership();
        $num_room = $room->getRoomNum();

        $uDetails = new unavailabilityDetails();
        $form = $this->createForm(new lodgingUnavailabilityDetailsType, $uDetails);
        if ($request->getMethod() == 'POST') {
            $post_form = $request->get('mycp_mycpbundle_unavailabilitydetailstype');
            $form->handleRequest($request);
            if ($form->isValid()) {
                $date_from = Dates::createFromString($post_form['ud_from_date']);
                $date_to = Dates::createFromString($post_form['ud_to_date']);

                if ($date_from > $date_to) {
                    $this->get('session')->getFlashBag()->add('message_error_main', "La fecha Desde tiene que ser menor o igual que la fecha Hasta");
                } else {
                    $uDetails->setUdFromDate($date_from)
                            ->setUdToDate($date_to)
                            ->setUdReason($post_form['ud_reason'])
                            ->setRoom($room);
                    $em->persist($uDetails);
                    $em->flush();
                    $message = 'Detalle de no disponibilidad añadido satisfactoriamente';
                    $this->get('session')->getFlashBag()->add('message_ok', $message);

                    $service_log = $this->get('log');
                    $service_log->saveLog('Create unavailable detaile from ' . $post_form['ud_from_date'] . ' to ' . $post_form['ud_to_date'], BackendModuleName::MODULE_UNAVAILABILITY_DETAILS);

                    return $this->redirect($this->generateUrl('mycp_lodging_unavailabilityDetails_calendar'));
                }
            }
        }

        return $this->render('mycpBundle:unavailabilityDetails:lodging_form.html.twig', array(
                    'room' => $room,
                    'num_room' => $num_room,
                    'id_room' => $id_room,
                    'ownership' => $ownership,
                    'form' => $form->createView()
        ));
    }

    public function editAction($id_detail, Request $request) {
        $service_security = $this->get('Secure');
        $service_security->verifyAccess();

        $em = $this->getDoctrine()->getManager();
        $uDetails = $em->getRepository('mycpBundle:unavailabilityDetails')->find($id_detail);
        $room = $uDetails->getRoom();
        $num_room = $room->getRoomNum();
        $ownership = $room->getRoomOwnership();

        $form = $this->createForm(new lodgingUnavailabilityDetailsType, $uDetails);
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
                $message = 'Detalle de no disponibilidad modificado satisfactoriamente';
                $this->get('session')->getFlashBag()->add('message_ok', $message);

                $service_log = $this->get('log');
                $service_log->saveLog('Update unavailable detaile from ' . $post_form['ud_from_date'] . ' to ' . $post_form['ud_to_date'], BackendModuleName::MODULE_UNAVAILABILITY_DETAILS);

                return $this->redirect($this->generateUrl('mycp_lodging_unavailabilityDetails_calendar'));
            }
        }

        return $this->render('mycpBundle:unavailabilityDetails:lodging_form.html.twig', array(
                    'room' => $room,
                    'num_room' => $num_room,
                    'id_room' => $room->getRoomId(),
                    'ownership' => $ownership,
                    'form' => $form->createView(),
                    'edit_detail' => $uDetails->getUdId()
        ));
    }

    public function deleteAction($id_detail) {
        $service_security = $this->get('Secure');
        $service_security->verifyAccess();
        $em = $this->getDoctrine()->getManager();
        $uDetails = $em->getRepository('mycpBundle:unavailabilityDetails')->find($id_detail);
        $room = $em->getRepository('mycpBundle:room')->find($uDetails->getRoom());

        $uDetails->setRoom($room)
                ->setSyncSt(SyncStatuses::DELETED);
        $em->persist($uDetails);
        $em->flush();
        $message = 'Detalle de no disponibilidad eliminado satisfactoriamente';
        $this->get('session')->getFlashBag()->add('message_ok', $message);

        $service_log = $this->get('log');
        $service_log->saveLog('Delete unavailable detail from ' . $uDetails->getUdFromDate()->format('d/M/Y') . ' to ' . $uDetails->getUdToDate()->format('d/M/Y'), BackendModuleName::MODULE_UNAVAILABILITY_DETAILS);

        return $this->redirect($this->generateUrl('mycp_lodging_unavailabilityDetails_calendar'));
    }

    private function manageData($request, $form, $em, $uDetails, $inEditMode = false) {
        if ($request->getMethod() == 'POST') {
            $post_form = $request->get('mycp_mycpbundle_unavailabilitydetailstype');
            $form->handleRequest($request);
            if ($form->isValid() && $post_form['ud_from_date'] != "" && $post_form["ud_to_date"] != "" &&
                    $post_form["room"] != "" && $request->get("status") != "") {
                $date_from = Dates::createFromString($post_form['ud_from_date']);
                $date_to = Dates::createFromString($post_form['ud_to_date']);
                $room = $em->getRepository('mycpBundle:room')->find($post_form['room']);

                if ($date_from > $date_to) {
                    $this->get('session')->getFlashBag()->add('message_error_main', "La fecha Desde tiene que ser menor o igual que la fecha Hasta");
                } else {
                    if ($request->get("status") == 0) {
                        $uDetails->setUdFromDate($date_from)
                                ->setUdToDate($date_to)
                                ->setUdReason("Insertada mediante el módulo casa")
                                ->setRoom($room);

                        if ($inEditMode)
                            $uDetails->setSyncSt(SyncStatuses::UPDATED);

                        $em->persist($uDetails);
                        $em->flush();
                        $message = ($inEditMode) ? 'Detalle de no disponibilidad actualizado satisfactoriamente' : 'Detalle de no disponibilidad añadido satisfactoriamente';
                        $this->get('session')->getFlashBag()->add('message_ok', $message);

                        $service_log = $this->get('log');
                        $service_log->saveLog('Create unavailable detaile from ' . $post_form['ud_from_date'] . ' to ' . $post_form['ud_to_date'], BackendModuleName::MODULE_UNAVAILABILITY_DETAILS);

                        return true;
                    } else if ($request->get("status") == 1) {
                        $room = $em->getRepository('mycpBundle:room')->find($uDetails->getRoom());

                        $uDetails->setRoom($room)
                                ->setSyncSt(SyncStatuses::DELETED);
                        $em->persist($uDetails);
                        $em->flush();
                        $message = 'Detalle de no disponibilidad eliminado satisfactoriamente';
                        $this->get('session')->getFlashBag()->add('message_ok', $message);

                        $service_log = $this->get('log');
                        $service_log->saveLog('Delete unavailable detail from ' . $uDetails->getUdFromDate()->format('d/M/Y') . ' to ' . $uDetails->getUdToDate()->format('d/M/Y'), BackendModuleName::MODULE_UNAVAILABILITY_DETAILS);

                        return true;
                    }
                }
            }
            return false;
        }
    }

}
