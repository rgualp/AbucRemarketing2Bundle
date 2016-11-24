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

    public function __construct(ObjectManager $em)
    {
        $this->em = $em;
    }

    public function addUDetail($id_room, $date_from, $date_to, $reason)
    {
        $unavailabilityDetail = array();
        $unavailabilityDetail["start"] = date('Y-m-d',$date_from->getTimestamp());
        $unavailabilityDetail["end"] = date('Y-m-d', $date_to->getTimestamp());
        $unavailabilityDetail["reason"] = $reason;

        $intervals = array();
        $reservations = $this->em->getRepository('mycpBundle:ownershipReservation')->getReservationReservedByRoomAndDate($id_room, $date_from->format('Y-m-d'), $date_to->format('Y-m-d'), true);

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

    }
}
