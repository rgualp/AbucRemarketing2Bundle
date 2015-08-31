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

    public function getAllNotDeletedByDate($start, $end){
        $em = $this->getEntityManager();
        $query_string = "SELECT o
                        FROM mycpBundle:unavailabilityDetails o
                        WHERE o.ud_sync_st<>" . SyncStatuses::DELETED .
                        " AND o.ud_from_date >= '$start' AND o.ud_to_date <= '$end'" .
                        " ORDER BY o.ud_from_date DESC";

        return $em->createQuery($query_string)->getResult();
    }

    public function getAllNotDeletedByDateAndOwnership($ownership, $start, $end){
        $em = $this->getEntityManager();
        $query_string = "SELECT o
                        FROM mycpBundle:unavailabilityDetails o JOIN o.room ro JOIN ro.room_ownership own
                        WHERE o.ud_sync_st<>" . SyncStatuses::DELETED .
            " AND o.ud_from_date >= '$start' AND o.ud_to_date <= '$end'" .
            " AND own.own_id = $ownership" .
            " ORDER BY o.ud_from_date DESC";

        return $em->createQuery($query_string)->getResult();
    }

    public function getAllNotDeletedByDateAndRoom($idRoom, $start, $end){
        $em = $this->getEntityManager();
        $query_string = "SELECT o
                        FROM mycpBundle:unavailabilityDetails o JOIN o.room ro
                        WHERE o.ud_sync_st<>" . SyncStatuses::DELETED .
            " AND o.ud_from_date >= '$start' AND o.ud_to_date <= '$end'" .
            " AND ro.room_id = $idRoom" .
            " ORDER BY o.ud_from_date DESC";

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

}
