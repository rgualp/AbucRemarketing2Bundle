<?php

namespace MyCp\mycpBundle\Entity;

use Doctrine\ORM\EntityRepository;
use MyCp\mycpBundle\Helpers\SyncStatuses;

/**
 * ud_sync_st
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class unavailabilityDetailsRepository extends EntityRepository {

    public function getNotSynchronized() {
        $em = $this->getEntityManager();
        $query_string = "SELECT o
                        FROM mycpBundle:unavailabilityDetails o
                        WHERE o.ud_sync_st<>" . SyncStatuses::SYNC;

        return $em->createQuery($query_string)->getResult();
    }

    public function getRoomDetails($id_room) {
        $em = $this->getEntityManager();
        $query_string = "SELECT o
                        FROM mycpBundle:unavailabilityDetails o
                        JOIN o.room r
                        WHERE o.ud_sync_st<>" . SyncStatuses::DELETED.
                        " AND r.room_id = :id_room
                        ORDER BY o.ud_from_date DESC";

        return $em->createQuery($query_string)->setParameter("id_room", $id_room)->getResult();
    }

    public function getRoomDetailsByRoomAndDates($id_room, $dateFrom, $dateTo) {
        $em = $this->getEntityManager();
        $query_string = "SELECT o
                        FROM mycpBundle:unavailabilityDetails o
                        JOIN o.room r
                        WHERE o.ud_sync_st<>" . SyncStatuses::DELETED."
                        AND r.room_id = :id_room
                        AND ((o.ud_from_date >= :start AND o.ud_from_date <= :end) OR
                             (o.ud_to_date >= :start AND o.ud_to_date <= :end) OR
                             (o.ud_from_date <= :start AND o.ud_to_date >= :end))
                        ORDER BY o.ud_from_date DESC";

        return $em->createQuery($query_string)
            ->setParameter("id_room", $id_room)
            ->setParameter('start', $dateFrom)
            ->setParameter('end', $dateTo)
            ->getResult();
    }

    public function getRoomDetailsForCalendar($id_room) {
        $em = $this->getEntityManager();
        $query_string = "SELECT o
                        FROM mycpBundle:unavailabilityDetails o
                        JOIN o.room r
                        WHERE o.ud_sync_st<>" . SyncStatuses::DELETED.
                        " AND r.room_id = :id_room
                        AND (o.ud_from_date >= :dateFrom OR (o.ud_from_date < :dateFrom AND o.ud_to_date >= :dateFrom))
                        ORDER BY o.ud_from_date DESC";

        $dateFrom = date("Y")."-01-01";

        return $em->createQuery($query_string)
                  ->setParameter("id_room", $id_room)
                  ->setParameter("dateFrom", $dateFrom)
                  ->getResult();
    }

    public function getRoomDetailsForCasaModuleCalendar($id_room, $date_from, $date_to) {
        $em = $this->getEntityManager();
        $query_string = "SELECT o
                        FROM mycpBundle:unavailabilityDetails o
                        JOIN o.room r
                        WHERE o.ud_sync_st<>" . SyncStatuses::DELETED.
                        " AND r.room_id = :id_room
                        AND (o.ud_from_date >= :dateFrom AND o.ud_from_date <= :dateTo OR (o.ud_from_date<:dateFrom AND o.ud_to_date >= :dateTo))
                        ORDER BY o.ud_from_date DESC";

//        $dateFrom = date("Y")."-01-01";

        return $em->createQuery($query_string)
                  ->setParameter("id_room", $id_room)
                  ->setParameter("dateFrom", $date_from)
                  ->setParameter("dateTo", $date_to)
                  ->getResult();
    }

    public function getAllNotDeletedByDate($start, $end){
        $em = $this->getEntityManager();
        $query_string = "SELECT o
                        FROM mycpBundle:unavailabilityDetails o
                        WHERE o.ud_sync_st<>" . SyncStatuses::DELETED .
            " AND ((o.ud_from_date >= '$start' AND o.ud_to_date <= '$end') OR
                (o.ud_to_date >= '$start' AND o.ud_to_date <= '$end') OR
                (o.ud_from_date <= '$end' AND o.ud_from_date >= '$start'))" .
                        " ORDER BY o.ud_from_date DESC";

        return $em->createQuery($query_string)->getResult();
    }

    public function getAllNotDeletedByDateAndOwnership($ownership, $start, $end){
        $em = $this->getEntityManager();
        $query_string = "SELECT o
                        FROM mycpBundle:unavailabilityDetails o JOIN o.room ro JOIN ro.room_ownership own
                        WHERE o.ud_sync_st<>" . SyncStatuses::DELETED .
            " AND ((o.ud_from_date >= '$start' AND o.ud_to_date <= '$end') OR
                (o.ud_to_date >= '$start' AND o.ud_to_date <= '$end') OR
                (o.ud_from_date <= '$end' AND o.ud_from_date >= '$start'))" .
            " AND own.own_id = $ownership" .
            " ORDER BY o.ud_from_date DESC";

        return $em->createQuery($query_string)->getResult();
    }

    public function getAllNotDeletedByDateAndRoom($idRoom, $start, $end){
        $em = $this->getEntityManager();
        $query_string = "SELECT o
                        FROM mycpBundle:unavailabilityDetails o JOIN o.room ro
                        WHERE o.ud_sync_st<>" . SyncStatuses::DELETED .
            " AND ((o.ud_from_date >= '$start' AND o.ud_to_date <= '$end') OR
                (o.ud_to_date >= '$start' AND o.ud_to_date <= '$end') OR
                (o.ud_from_date <= '$end' AND o.ud_from_date >= '$start'))" .
            " AND ro.room_id = $idRoom" .
            " ORDER BY o.ud_from_date ASC";

        return $em->createQuery($query_string)->getResult();
    }

    public function getOldDetails($date){
        $em=$this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->select('ud')
            ->from("mycpBundle:unavailabilityDetails", "ud")
            ->where('ud.ud_to_date <= :date')
            ->setParameter("date", $date)
            ->orderBy("ud.ud_to_date", "DESC");

        return $qb->getQuery()->getResult();
    }

    public function existByDateAndRoom($idRoom, $start, $end){
        $em = $this->getEntityManager();
        $start = $start->format('Y-m-d');
        $end = $end->format('Y-m-d');
        $query_string = "SELECT count(o)
                        FROM mycpBundle:unavailabilityDetails o JOIN o.room ro
                        WHERE o.ud_sync_st<>" . SyncStatuses::DELETED .
            " AND o.ud_from_date <= '$start' AND o.ud_to_date >= '$end'" .
            " AND ro.room_id = $idRoom" .
            " ORDER BY o.ud_from_date DESC";

        return $em->createQuery($query_string)->getSingleScalarResult();
    }

    public function existInDateAndRoom($idRoom, $start, $end){
        $em = $this->getEntityManager();
        $start = $start->format('Y-m-d');
        $end = $end->format('Y-m-d');

        $query_string = "SELECT COUNT(o) FROM mycpBundle:unavailabilityDetails o JOIN o.room ro
WHERE ro.room_id = :room_id AND o.ud_sync_st <> :ud_sync_st AND ((o.ud_from_date <= :start AND o.ud_to_date >= :start) OR (o.ud_from_date <= :end AND o.ud_to_date >= :end))";

        return $em->createQuery($query_string)
            ->setParameter("room_id", $idRoom)
            ->setParameter("start", $start)
            ->setParameter("end", $end)
            ->setParameter("ud_sync_st", SyncStatuses::DELETED)
            ->getSingleScalarResult()
            /*->getResult()*/;
    }

    public function addAvailableRoomByRange($request, $idRoom){
        $em = $this->getEntityManager();
        $conn = $em->getConnection();
        $start =  $request["start"];
        $end = $request["end"];

        /*Obtener la no disponibilidad que el start cae dentro de la no disponibilidad*/
        $query = "SELECT unavailabilitydetails.ud_id, unavailabilitydetails.ud_to_date, unavailabilitydetails.ud_sync_st,  unavailabilitydetails.ud_reason FROM unavailabilitydetails";
        $whereAndLimit = " WHERE room_id = :room_id AND ud_from_date < :start AND ud_to_date >= :start LIMIT 1";
        $stmt = $conn->prepare($query.$whereAndLimit);
        $stmt->bindValue('room_id', $idRoom);
        $stmt->bindValue('start', $start);
        $stmt->execute();
        $unavailability = $stmt->fetch();

        if($unavailability != false){
            $ud_to_date_new = (new \DateTime($start))->modify('-1 day')->format('Y-m-d');
            $query = "UPDATE unavailabilitydetails SET  ud_to_date = :ud_to_date";
            $stmt = $conn->prepare($query.$whereAndLimit);
            $stmt->bindValue('ud_to_date', $ud_to_date_new);
            $stmt->bindValue('room_id', $idRoom);
            $stmt->bindValue('start', $start);
            $stmt->execute();

            if((new \DateTime($unavailability['ud_to_date'])) > (new \DateTime($end))){
                $ud_from_date = (new \DateTime($end))->modify('+1 day')->format('Y-m-d');

                $query = "INSERT INTO unavailabilitydetails (room_id, ud_sync_st, ud_from_date, ud_to_date, ud_reason) VALUE (:room_id, :ud_sync_st, :ud_from_date, :ud_to_date, :ud_reason)";
                $stmt = $conn->prepare($query);
                $stmt->bindValue('room_id', $idRoom);
                $stmt->bindValue('ud_sync_st', $unavailability['ud_sync_st']);
                $stmt->bindValue('ud_from_date', $ud_from_date);
                $stmt->bindValue('ud_to_date', $unavailability['ud_to_date']);
                $stmt->bindValue('ud_reason', $unavailability['ud_reason']);
                $stmt->execute();
            }
        }

        /*Actualizar la no disponibilidades que el end cae dentro de la no disponibilidad*/
        $ud_from_date = (new \DateTime($end))->modify('+1 day')->format('Y-m-d');

        $query = "UPDATE unavailabilitydetails SET ud_from_date = :ud_from_date WHERE room_id = :room_id AND ud_from_date <= :end AND ud_to_date > :end LIMIT 1";
        $stmt = $conn->prepare($query);
        $stmt->bindValue('ud_from_date', $ud_from_date);
        $stmt->bindValue('room_id', $idRoom);
        $stmt->bindValue('end', $end);
        $stmt->execute();

        /*elimino las que estan en el rango*/
        $query = "DELETE FROM unavailabilitydetails WHERE room_id = :room_id AND ud_from_date >= :start AND ud_from_date <= :end AND ud_to_date >= :start AND ud_to_date <= :end ";
        $stmt = $conn->prepare($query);
        $stmt->bindValue('room_id', $idRoom);
        $stmt->bindValue('start', $start);
        $stmt->bindValue('end', $end);
        $stmt->execute();

        /*incerto las no disponibilidades new*/
        $unavailabilities=$request['date_range'];
        $reason = $request['reason'];
        $reason = isset($reason) ? $reason : '';
        foreach ($unavailabilities as $unavailability) {
            $uStart = $unavailability["start"];
            $uStart = $uStart->format('Y-m-d');

            $uEnd = $unavailability["end"];
            $uEnd = $uEnd->format('Y-m-d');

            $conn->insert('unavailabilitydetails', array('room_id' => $idRoom, 'ud_sync_st' => 0, 'ud_from_date' => $uStart, 'ud_to_date' => $uEnd, 'ud_reason' => $reason));
        }

        return true;
    }

    function getUDetailsByRoomAndDate($idRoom, $startParam, $endParam) {
        $em = $this->getEntityManager();
        $query = $em->createQuery("SELECT u
            FROM mycpBundle:unavailabilityDetails u
            JOIN u.room ro
        WHERE u.ud_sync_st<>" . SyncStatuses::DELETED .
        "AND ((u.ud_from_date >= '$startParam' AND u.ud_to_date <= '$endParam')
         OR (u.ud_to_date >= '$startParam' AND u.ud_to_date <= '$endParam')
         OR (u.ud_from_date <= '$endParam' AND u.ud_from_date >= '$startParam')
         OR (u.ud_from_date <= '$startParam' AND u.ud_to_date >= '$endParam'))
        AND ro.room_id = $idRoom
        ORDER BY u.ud_from_date");

        //dump($query->getDQL());exit;
        return $query->getResult();
    }
}
