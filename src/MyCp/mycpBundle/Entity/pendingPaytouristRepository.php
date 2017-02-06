<?php

namespace MyCp\mycpBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * pendingPaytouristRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class pendingPaytouristRepository extends EntityRepository {

    /**
     * @param string $filter_number
     * @param string $filter_code
     * @param string $filter_method
     * @param string $filter_payment_date_from
     * @param string $filter_payment_date_to
     * @return \Doctrine\ORM\Query
     */
    function findAllByFilters($filter_number="", $filter_code="",  $filter_method="", $filter_payment_date_from="", $filter_payment_date_to="")
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder()
            ->select("op")
            ->from("mycpBundle:pendingPaytourist", "op")
            ->join("op.user_tourist", "acc")
            ->join("acc.user_tourist_user", "us")
            ->join("op.type", "type")
            ->orderBy("op.pending_id", "DESC");

        if($filter_number != null && $filter_number != "" && $filter_number != "null")
        {
            $qb->andWhere("op.pending_id LIKE :pending_id")
                ->setParameter("pending_id", '%'.$filter_number.'%');
        }
        if($filter_code != null && $filter_code != "" && $filter_code != "null")
        {
            $qb->andWhere("us.user_user_name LIKE :code")
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
