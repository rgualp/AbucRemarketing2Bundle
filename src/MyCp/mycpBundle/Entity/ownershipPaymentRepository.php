<?php

namespace MyCp\mycpBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * ownershipPaymentRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class ownershipPaymentRepository extends EntityRepository {

    function findAllByCreationDate($filter_number="", $filter_code="", $filter_service="", $filter_method="", $filter_payment_date_from="", $filter_payment_date_to="")
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder()
            ->from("mycpBundle:ownershipPayment", "op")
            ->join("op.accommodation", "acc")
            ->join("op.method", "method")
            ->join("op.service", "service")
            ->select("op")
            ->orderBy("op.creation_date", "DESC")
            ->addOrderBy("op.number", "DESC")
            ->addOrderBy("length(op.number)", "ASC");

        if($filter_number != null && $filter_number != "" && $filter_number != "null")
        {
            $qb->andWhere("op.number = :number")
                ->setParameter("number", $filter_number);
        }

        if($filter_code != null && $filter_code != "" && $filter_code != "null")
        {
            $qb->andWhere("acc.own_mcp_code = :code")
                ->setParameter("code", $filter_code);
        }

        if($filter_service != null && $filter_service != "" && $filter_service != "null")
        {
            $qb->andWhere("service.id = :service")
                ->setParameter("service", $filter_service);
        }

        if($filter_method != null && $filter_method != "" && $filter_method != "null")
        {
            $qb->andWhere("method.nom_id = :method")
                ->setParameter("method", $filter_method);
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
