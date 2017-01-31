<?php

namespace MyCp\PartnerBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * paPendingPaymentAccommodationRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class paPendingPaymentAccommodationRepository extends EntityRepository {

    function findAllByFilters($filter_number="", $filter_code="",  $filter_method="", $filter_payment_date_from="", $filter_payment_date_to="", $filter_agency = "", $filter_booking = "", $filter_destination = "", $filter_type = "")
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder()
            ->select("op")
            ->from("PartnerBundle:paPendingPaymentAccommodation", "op")
            ->join("op.reservation", "reservation")
            ->join("reservation.gen_res_own_id", "acc")
            ->join("acc.own_destination", "destination")
            ->join("op.booking", "booking")
            ->join("op.agency", "agency")
            ->join("op.type", "type")
            ->join("op.status", "status")
            ->orderBy("op.id", "DESC")
            ->orderBy("op.pay_date", "DESC");

        if($filter_number != null && $filter_number != "" && $filter_number != "null")
        {
            $qb->andWhere("reservation.gen_res_id LIKE :id")
                ->setParameter("id", '%'.$filter_number.'%');
        }
        if($filter_code != null && $filter_code != "" && $filter_code != "null")
        {
            $qb->andWhere("acc.own_mcp_code LIKE :code")
                ->setParameter("code", '%'.$filter_code.'%');
        }
        if($filter_agency != null && $filter_agency != "" && $filter_agency != "null")
        {
            $qb->andWhere("agency.name LIKE :agency")
                ->setParameter("agency", '%'.$filter_agency.'%');
        }
        if($filter_booking != null && $filter_booking != "" && $filter_booking != "null")
        {
            $qb->andWhere("booking.booking_id LIKE :booking")
                ->setParameter("booking", '%'.$filter_booking.'%');
        }
        if($filter_type != null && $filter_type != "" && $filter_type != "null")
        {
            $qb->andWhere("type.nom_id = :type")
                ->setParameter("type", $filter_type);
        }
        if($filter_method != null && $filter_method != "" && $filter_method != "null")
        {
            $qb->andWhere("status.nom_id = :status")
                ->setParameter("status", $filter_method);
        }
        if($filter_payment_date_from != null && $filter_payment_date_from != "" && $filter_payment_date_from != "null")
        {
            $qb->andWhere("op.pay_date >= :dateFrom")
                ->setParameter("dateFrom", $filter_payment_date_from);
        }

        if($filter_payment_date_to != null && $filter_payment_date_to != "" && $filter_payment_date_to != "null")
        {
            $qb->andWhere("op.pay_date <= :dateTo")
                ->setParameter("dateTo", $filter_payment_date_to);
        }
        if($filter_destination!= null && $filter_destination != "" && $filter_destination != "null")
        {
            $qb->andWhere("destination.des_name = :destination")
                ->setParameter("destination", $filter_destination);
        }
        return $qb->getQuery();

    }
}
