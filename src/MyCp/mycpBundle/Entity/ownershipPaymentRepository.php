<?php

namespace MyCp\mycpBundle\Entity;

use Doctrine\ORM\EntityRepository;
use MyCp\mycpBundle\Helpers\BackendModuleName;
use MyCp\mycpBundle\Helpers\DataBaseTables;
use MyCp\mycpBundle\Helpers\OwnershipStatuses;

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
            $qb->andWhere("op.number LIKE :number")
                ->setParameter("number", '%'.$filter_number.'%');
        }

        if($filter_code != null && $filter_code != "" && $filter_code != "null")
        {
            $qb->andWhere("acc.own_mcp_code LIKE :code")
                ->setParameter("code", '%'.$filter_code.'%');
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

    function accommodationsNoInscriptionPayment($timeRangeRestriction = false, $filter_name="", $filter_code="", $filter_destination="", $filter_creation_date_from="", $filter_creation_date_to="")
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder()
            ->from("mycpBundle:ownership", "o")
            ->where("o.own_id NOT IN (select ac.own_id from mycpBundle:ownershipPayment op JOIN op.accommodation ac JOIN op.service s where s.name LIKE '%inscripción%')")
            ->select("o")
            ->orderBy("o.own_creation_date", "DESC")
            ->addOrderBy("o.own_mcp_code", "ASC")
            ->addOrderBy("length(o.own_mcp_code)", "ASC")
            ->andWhere("o.own_status = :activeStatus")
            ->setParameter("activeStatus", OwnershipStatuses::ACTIVE);

        if($filter_name != null && $filter_name != "" && $filter_name != "null")
        {
            $qb->andWhere("o.own_name LIKE :name")
                ->setParameter("name", '%'.$filter_name.'%');
        }

        if($filter_code != null && $filter_code != "" && $filter_code != "null")
        {
            $qb->andWhere("o.own_mcp_code LIKE :code")
                ->setParameter("code", '%'.$filter_code.'%');
        }

        if($filter_destination != null && $filter_destination != "" && $filter_destination != "null")
        {
            $qb->andWhere("o.own_destination = :destination")
                ->setParameter("destination", $filter_destination);
        }

        if($filter_creation_date_from != null && $filter_creation_date_from != "" && $filter_creation_date_from != "null")
        {
            $qb->andWhere("o.own_creation_date >= :creationDateFrom")
                ->setParameter("creationDateFrom", $filter_creation_date_from);
        }

        if($filter_creation_date_to != null && $filter_creation_date_to != "" && $filter_creation_date_to != "null")
        {
            $qb->andWhere("o.own_creation_date <= :creationDateTo")
                ->setParameter("creationDateTo", $filter_creation_date_to);
        }

        if($timeRangeRestriction)
        {
            $qb->andWhere("(o.own_creation_date IS NULL or DATE_DIFF(CURRENT_DATE(),o.own_creation_date) >= 15)");
        }

        return ($timeRangeRestriction) ? $qb->getQuery()->getResult() : $qb->getQuery();

    }

    public function setAccommodationPayment($accommodations_ids, $service, $method, $amount, $paymentDate, $serviceLog) {
        $em = $this->getEntityManager();
        $paymentDate = \DateTime::createFromFormat("Y-m-d", $paymentDate);
        $service = $em->getRepository("mycpBundle:mycpService")->find($service);
        $method = $em->getRepository("mycpBundle:nomenclator")->find($method);

        foreach ($accommodations_ids as $accommodation_id) {
            $existPayment = $em->getRepository("mycpBundle:ownershipPayment")->findOneBy(array("accommodation" => $accommodation_id, "service" => $service, "method" => $method, "payed_amount" => $amount, "payment_date" => $paymentDate));

            if($existPayment == null) {
                $accommodation = $em->getRepository('mycpBundle:ownership')->find($accommodation_id);
                $payment = new ownershipPayment();
                $payment->setAccommodation($accommodation)
                    ->setService($service)
                    ->setMethod($method)
                    ->setPayedAmount($amount)
                    ->setPaymentDate($paymentDate)
                ;
                $em->persist($payment);
                $em->flush();
            }
        }

        $serviceLog->saveLog("Pago por lote otorgado a ".count($accommodations_ids)." alojamientos", BackendModuleName::MODULE_ACCOMMODATION_PAYMENT, log::OPERATION_INSERT, DataBaseTables::ACCOMMODATION_PAYMENT);

    }


}
