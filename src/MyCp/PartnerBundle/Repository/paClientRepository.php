<?php

namespace MyCp\PartnerBundle\Repository;

use Doctrine\ORM\EntityRepository;
use MyCp\mycpBundle\Entity\generalReservation;

/**
 * paClientRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class paClientRepository extends EntityRepository {

    public function getClientsFromBooking($idBooking, $idAgencyUser){
        $em=$this->getEntityManager();
        $qb = $em->createQueryBuilder()
            ->from("mycpBundle:generalReservation", "r")
            ->select("DISTINCT client.id, client.fullname")
            ->join('r.gen_res_user_id', 'u')
            ->andWhere('u.user_id = :user_id')
            ->setParameter('user_id', $idAgencyUser)
            ->join('r.travelAgencyDetailReservations', 'pard')
            ->join('pard.reservation', 'par')
            ->join('par.client', 'client');

        $qb->andWhere('(r.gen_res_status = :gen_res_status or r.gen_res_status = :gen_res_status1 or r.gen_res_status = :gen_res_status2)');
        $qb->setParameter('gen_res_status1', generalReservation::STATUS_PARTIAL_RESERVED);
        $qb->setParameter('gen_res_status', generalReservation::STATUS_RESERVED);
        $qb->setParameter('gen_res_status2', generalReservation::STATUS_PENDING_PAYMENT_PARTNER);

        $subSelect = "SELECT COUNT(owres_1) FROM mycpBundle:ownershipReservation AS owres_1
                      JOIN owres_1.own_res_reservation_booking AS b WHERE owres_1.own_res_gen_res_id = r.gen_res_id
                      AND b.booking_id = :booking_id";
        $subSelect = "(" . $subSelect . ") > 0";
        $qb->andWhere($subSelect);
        $qb->setParameter('booking_id', $idBooking);

        return $qb->getQuery()->execute();

    }

}
