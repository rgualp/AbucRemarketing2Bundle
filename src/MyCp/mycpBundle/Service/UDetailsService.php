<?php

namespace MyCp\mycpBundle\Service;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManager;
use MyCp\FrontEndBundle\Helpers\ReservationHelper;
use MyCp\FrontEndBundle\Helpers\Time;
use MyCp\mycpBundle\Entity\booking;
use MyCp\mycpBundle\Entity\generalReservation;
use MyCp\mycpBundle\Entity\ownershipReservation;
use MyCp\mycpBundle\Entity\unavailabilityDetails;
use MyCp\mycpBundle\Entity\user;
use MyCp\mycpBundle\Helpers\BackendModuleName;
use MyCp\mycpBundle\Helpers\SyncStatuses;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class UDetailsService extends Controller
{
    /**
     * @var ObjectManager
     */
    private $em;

    protected $container;

    private $conn;

    public function __construct(ObjectManager $em, $container)
    {
        $this->em = $em;
        $this->container = $container;
        $this->conn = $em->getConnection();
    }

    private function addNewUdetail($roomId, $start, $end, $reason){
        $this->conn->insert('unavailabilitydetails', array('room_id' => $roomId, 'ud_sync_st' => 0, 'ud_from_date' => $start, 'ud_to_date' => $end, 'ud_reason' => $reason));
        return $this->conn->lastInsertId();
    }

    private function existUdetails($roomId, $start, $end){
        /*Verifico la existencia de una q contenga ya este limite de fechas*/
        $query = "SELECT ud_id FROM unavailabilitydetails WHERE room_id = :room_id AND ud_from_date < :start AND ud_to_date > :end";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue('room_id', $roomId);
        $stmt->bindValue('start', $start);
        $stmt->bindValue('end', $end);
        $stmt->execute();
        $existOne = $stmt->rowCount() > 0;
        if($existOne){
            return true;
        }

        /*elimino la disponibilidades si caen dentro*/
        $query = "DELETE FROM unavailabilitydetails WHERE room_id = :room_id AND ud_from_date >= :start AND ud_to_date <= :end";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue('room_id', $roomId);
        $stmt->bindValue('start', $start);
        $stmt->bindValue('end', $end);
        $stmt->execute();
        /*$deleteOne = $stmt->rowCount() > 0;
        if($deleteOne){
            return false;
        }*/

        /*Actualizar la no disponibilidad que el start cae dentro de la no disponibilidad*/
        $ud_to_date_new = (new \DateTime($start))->modify('-1 day')->format('Y-m-d');
        $query = "UPDATE unavailabilitydetails SET  ud_to_date = :ud_to_date WHERE room_id = :room_id AND ud_from_date < :start AND ud_to_date >= :start";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue('ud_to_date', $ud_to_date_new);
        $stmt->bindValue('room_id', $roomId);
        $stmt->bindValue('start', $start);
        $stmt->execute();
        /*$modifOne = $stmt->rowCount() > 0;
        if($modifOne){
            $aa = 1;
        }*/

        /*Actualizar la no disponibilidades que el end cae dentro de la no disponibilidad*/
        $ud_from_date_new = (new \DateTime($end))->modify('+1 day')->format('Y-m-d');
        $query = "UPDATE unavailabilitydetails SET ud_from_date = :ud_from_date WHERE room_id = :room_id AND ud_from_date <= :xend AND ud_to_date > :xend";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue('ud_from_date', $ud_from_date_new);
        $stmt->bindValue('room_id', $roomId);
        $stmt->bindValue('xend', $end);
        $stmt->execute();
        /*$modifOne = $stmt->rowCount() > 0;
        if($modifOne){
            $aa = 1;
        }*/

        return false;
    }

    public function addUdetailFromICal($roomId, $start, $end, $reason){
        $existUdetails = $this->existUdetails($roomId, $start, $end);
        if(!$existUdetails){
            $this->addNewUdetail($roomId, $start, $end, $reason);
        }
    }

    public function addUDetail($id_room, $date_from, $date_to, $reason)
    {
        $unavailabilityDetail = array();
        $unavailabilityDetail["start"] = date('Y-m-d',$date_from->getTimestamp());
        $unavailabilityDetail["end"] = date('Y-m-d', $date_to->getTimestamp());
        $unavailabilityDetail["reason"] = $reason;

        $intervals = array();
        $reservations = $this->em->getRepository('mycpBundle:ownershipReservation')->getReservationReservedByRoomAndDate($id_room, $date_from->format('Y-m-d'), $date_to->format('Y-m-d'), true);

        //OwnerShip Availability Update
        $room = $this->em->getRepository('mycpBundle:room')->find($id_room);
        if ($room){
            $ownership = $room->getRoomOwnership();
            if ($ownership){
                $now = new \DateTime();
                $ownership->setOwnAvailabilityUpdate($now);
                $this->em->persist($ownership);
                $this->em->flush();
            }
        }

        $index = 0;
        $startDate = $date_from;
        //$endDate = $date_to;
        foreach($reservations as $reservation)
        {
            $index++;
            //La fecha fin del intervalo sera la inicial de la proxima reserva o
            $endDate = (count($reservations) > $index) ? $reservations[$index]["own_res_reservation_from_date"] : $date_to;

            //La fecha de inicio de la reserva es menor que la de inicio del rango y la fecha fin de la reserva esta en el rango
            if($reservation["own_res_reservation_from_date"] < $startDate && $reservation["own_res_reservation_to_date"] >= $startDate && $reservation["own_res_reservation_to_date"] < $endDate)
            {
                if($reservation["own_res_reservation_to_date"] != $endDate)
                    $intervals[] = array("start" => $reservation["own_res_reservation_to_date"] , "end" => $endDate);
                $startDate = $endDate;
            }
            //si la reserva esta dentro del rango
            elseif($reservation["own_res_reservation_from_date"] >= $startDate && $reservation["own_res_reservation_to_date"] <= $endDate)
            {
                $dateBefore = $reservation["own_res_reservation_from_date"];
                $dateBefore = date_modify($dateBefore, "-1 day");

                if ($startDate != $dateBefore && $startDate < $dateBefore)
                    $intervals[] = array("start" => $startDate, "end" => $dateBefore);

                if ($reservation["own_res_reservation_to_date"] != $endDate)
                    $intervals[] = array("start" => $reservation["own_res_reservation_to_date"], "end" => $endDate);

                $startDate = $endDate;
            }
            //La fecha de inicio de la reserva esta en el rango pero la fecha fin de la reserva es mayor que la fecha fin del rango
            elseif($reservation["own_res_reservation_to_date"] > $endDate && $reservation["own_res_reservation_from_date"] >= $startDate && $reservation["own_res_reservation_from_date"] <= $endDate)
            {
                $dateBefore = $reservation["own_res_reservation_from_date"];
                $dateBefore = date_modify($dateBefore, "-1 day");

                if($startDate != $dateBefore && $startDate < $dateBefore)
                    $intervals[] = array("start" => $startDate, "end" => $dateBefore);
                $startDate = $reservation["own_res_reservation_from_date"];
            }
            elseif($reservation["own_res_reservation_to_date"] == $startDate)
            {
                $intervals[] = array("start" => $startDate, "end" => $endDate);
            }
            else {
                $startDate = $reservation["own_res_reservation_to_date"];
            }

        }

        if(count($reservations) == 0)
            $intervals[] = array("start" => $date_from, "end" => $date_to);

        $unavailabilityDetail["date_range"] = $intervals;
        $this->em->getRepository('mycpBundle:unavailabilityDetails')->addAvailableRoomByRange($unavailabilityDetail, $id_room);

        $this->updateICal($id_room);
    }

    public function removeUDetail($id_room, $date_from, $date_to, $reason)
    {
        /*$unavailabilityDetail = array();
        $unavailabilityDetail["start"] = date('Y-m-d',$date_from->getTimestamp());
        $unavailabilityDetail["end"] = date('Y-m-d', $date_to->getTimestamp());*/

        $uDetails = $this->em->getRepository('mycpBundle:unavailabilityDetails')->getUDetailsByRoomAndDate($id_room, $date_from->format('Y-m-d'), $date_to->format('Y-m-d'));

        foreach($uDetails as $uDetail)
        {
            //La no disponibilidad tiene un rango mayor al seleccionado. SE crean dos no disponibilidades
            if($uDetail->getUdFromDate() <= $date_from && $uDetail->getUdToDate() >= $date_to)
            {
//                var_dump($uDetail->getUdFromDate()->format("Y-m-d"));
//                var_dump($date_from->format("Y-m-d"));
//                var_dump($uDetail->getUdToDate()->format("Y-m-d"));
//                var_dump($date_to->format("Y-m-d"));
//                var_dump($uDetail->getUdFromDate() != $date_from || $uDetail->getUdToDate() != $date_to);

                if($uDetail->getUdFromDate() != $date_from || $uDetail->getUdToDate() != $date_to)
                {
                    $room = $this->em->getRepository("mycpBundle:room")->find($id_room);
                    $dateBefore = $date_from;
                    $dateBefore = date_modify($dateBefore, "-1 day");
                    $dateAfter = $date_to;
                    $dateAfter = date_modify($dateAfter, "+1 day");

//                    dump($uDetail->getUdFromDate());
//                    dump($dateBefore);
//                    dump($uDetail->getUdToDate());
//                    dump($dateAfter);

                    if ($uDetail->getUdFromDate() != $dateBefore && $uDetail->getUdFromDate() < $dateBefore) {
                        $newDetail = new unavailabilityDetails();
                        $newDetail->setRoom($room)
                            ->setUdFromDate($uDetail->getUdFromDate())
                            ->setUdToDate($dateBefore)
                            ->setUdReason($reason)
                            ->setUdSyncSt(SyncStatuses::ADDED);

                        $this->em->persist($newDetail);
                    }

                    elseif ($uDetail->getUdToDate() != $dateAfter && $dateAfter < $uDetail->getUdToDate()) {
                        $newDetail = new unavailabilityDetails();
                        $newDetail->setRoom($room)
                            ->setUdFromDate($dateAfter)
                            ->setUdToDate($uDetail->getUdToDate())
                            ->setUdReason($reason)
                            ->setUdSyncSt(SyncStatuses::ADDED);

                        $this->em->persist($newDetail);
                    }
                    else{
                        $newDetail = new unavailabilityDetails();
                        $newDetail->setRoom($room)
                            ->setUdFromDate($uDetail->getUdFromDate())
                            ->setUdToDate($date_from)
                            ->setUdReason($reason)
                            ->setUdSyncSt(SyncStatuses::ADDED);
                        $this->em->persist($newDetail);

                        $newDetail = new unavailabilityDetails();
                        $newDetail->setRoom($room)
                            ->setUdFromDate($date_to)
                            ->setUdToDate($uDetail->getUdToDate())
                            ->setUdReason($reason)
                            ->setUdSyncSt(SyncStatuses::ADDED);
                        $this->em->persist($newDetail);
                    }
                }
                $this->em->remove($uDetail);
            }
            //La fecha de inicio de otra no disponibilidad es menor que la de inicio del rango y la fecha fin de la no disponibilidad esta en el rango
            elseif($uDetail->getUdFromDate() < $date_from && $uDetail->getUdToDate() >= $date_from && $uDetail->getUdToDate() < $date_to)
            {
//                var_dump("Caso 2");
//                var_dump(date("d-M-Y H:i:s", $uDetail->getUdFromDate()->getTimestamp()));
//                var_dump(date("d-M-Y H:i:s", $date_from->getTimestamp()));
//                var_dump(date("d-M-Y", $uDetail->getUdToDate()->getTimestamp()));
//                var_dump($uDetail->getUdFromDate() < $date_from);
//                die;
                $dateBefore = $date_from;
                $dateBefore = date_modify($dateBefore, "-1 day");

                if($dateBefore != $uDetail->getUdFromDate() && $uDetail->getUdFromDate() < $dateBefore) {
                    $uDetail->setUdToDate($dateBefore);
                    $this->em->persist($uDetail);
                }
                else
                    $this->em->remove($uDetail);
            }
            //si la no disponibilidad esta dentro del rango
            elseif($uDetail->getUdFromDate() >= $date_from && $uDetail->getUdToDate() <= $date_to)
            {
//                var_dump("Caso 3");
////                var_dump(date("d-M-Y", $uDetail->getUdFromDate()->getTimestamp()));
////                var_dump(date("d-M-Y", $uDetail->getUdToDate()->getTimestamp()));
//                die;
                $this->em->remove($uDetail);
            }
            //La fecha de inicio de otra no disponibilidad esta en el rango pero la fecha fin de la no disponibilidad es mayor que la fecha fin del rango
            elseif($uDetail->getUdToDate() > $date_to && $uDetail->getUdFromDate() >= $date_from && $uDetail->getUdFromDate() <= $date_to)
            {
//                var_dump("Caso 4");
//                var_dump(date("d-M-Y", $uDetail->getUdFromDate()->getTimestamp()));
//                var_dump(date("d-M-Y", $uDetail->getUdToDate()->getTimestamp()));
//                die;

                $dateAfter = $date_to;
                $dateAfter = date_modify($dateAfter, "+1 day");

                if($uDetail->getUdToDate() != $dateAfter) {
                    $uDetail->setUdFromDate($dateAfter);
                    $this->em->persist($uDetail);
                }
                else
                    $this->em->remove($uDetail);
            }

//            var_dump("Fecha inicio: ".date('Y-m-d',$uDetail->getUdFromDate()->getTimestamp())."<br/>");
//            var_dump("Fecha fin: ".date('Y-m-d',$uDetail->getUdToDate()->getTimestamp())."<br/>");

        }

        $this->em->flush();

        $this->updateICal($id_room);
    }

    private function updateICal($id_room) {
        try {
            $calendarService = $this->container->get('mycp.service.calendar');
            $calendarService->createICalForRoom($id_room);
            return "Se actualizó satisfactoriamente el fichero .ics asociado a esta habitación.";
        } catch (\Exception $e) {
            return "Ha ocurrido un error mientras se actualizaba el fichero .ics de la habitación. Error: " . $e->getMessage();
        }
    }
}
