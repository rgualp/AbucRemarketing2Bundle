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
                $dateBefore = date_modify($reservation["own_res_reservation_from_date"], "-1 day");

                if($startDate != $dateBefore)
                    $intervals[] = array("start" => $startDate, "end" => $dateBefore);

                if($reservation["own_res_reservation_to_date"] != $endDate)
                    $intervals[] = array("start" => $reservation["own_res_reservation_to_date"], "end" => $endDate);
                $startDate = $endDate;
            }
            //La fecha de inicio de la reserva esta en el rango pero la fecha fin de la reserva es mayor que la fecha fin del rango
            elseif($reservation["own_res_reservation_to_date"] > $endDate && $reservation["own_res_reservation_from_date"] >= $startDate && $reservation["own_res_reservation_from_date"] <= $endDate)
            {
                $dateBefore = date_modify($reservation["own_res_reservation_from_date"], "-1 day");

                if($startDate != $dateBefore)
                    $intervals[] = array("start" => $startDate, "end" => $dateBefore);
                $startDate = $reservation["own_res_reservation_from_date"];
            }
            else
                $startDate = $reservation["own_res_reservation_to_date"];

        }

        if(count($reservations) == 0)
            $intervals[] = array("start" => $date_from, "end" => $date_to);

        //var_dump($intervals); die;

        $unavailabilityDetail["date_range"] = $intervals;
        //Utils::debug($unavailabilityDetail); die;

        $this->em->getRepository('mycpBundle:unavailabilityDetails')->addAvailableRoomByRange($unavailabilityDetail, $id_room);

    }
}
