<?php

namespace MyCp\mycpBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * pendingPaymentRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class pendingPaymentRepository extends EntityRepository {

    function findAllByFilters($filter_number="", $filter_code="",  $filter_method="", $filter_payment_date_from="", $filter_payment_date_to="")
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder()
            ->select("op")
            ->from("mycpBundle:pendingPayment", "op")
            ->join("op.type", "type")
            ->join("op.reservation", "r")
            ->join("r.gen_res_own_id", "own")
            ->orderBy("op.id", "DESC");

        if($filter_number != null && $filter_number != "" && $filter_number != "null")
        {
            $qb->andWhere("op.id LIKE :pending_id")
                ->setParameter("pending_id", '%'.$filter_number.'%');
        }
        if($filter_code != null && $filter_code != "" && $filter_code != "null")
        {
            $qb->andWhere("own.own_mcp_code LIKE :code")
                ->setParameter("code", '%'.$filter_code.'%');
        }
        if($filter_method != null && $filter_method != "" && $filter_method != "null")
        {
            $qb->andWhere("type.nom_id = :type")
                ->setParameter("type", $filter_method);
        }
        if($filter_payment_date_from != null && $filter_payment_date_from != "" && $filter_payment_date_from != "null")
        {
            $qb->andWhere("op.payment_date >= :dateFrom")
                ->setParameter("dateFrom", $filter_payment_date_from);
        }

        if($filter_payment_date_to != null && $filter_payment_date_to != "" && $filter_payment_date_to != "null")
        {
            $qb->andWhere("op.payment_date <= :dateTo")
                ->setParameter("dateTo", $filter_payment_date_to);
        }
        return $qb->getQuery();

    }
}
