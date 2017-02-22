<?php

namespace MyCp\mycpBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\ORM\QueryBuilder;
use MyCp\mycpBundle\Entity\ownership;
use MyCp\mycpBundle\Entity\ownershipDescriptionLang;
use MyCp\mycpBundle\Entity\ownershipGeneralLang;
use MyCp\mycpBundle\Entity\ownershipKeywordLang;
use MyCp\mycpBundle\Entity\room;
use MyCp\mycpBundle\Entity\userCasa;
use MyCp\mycpBundle\Helpers\AccommodationModality;
use MyCp\mycpBundle\Helpers\CompareOption;
use MyCp\mycpBundle\Helpers\OrderByHelper;
use MyCp\mycpBundle\Helpers\OwnershipStatuses;
use MyCp\mycpBundle\Helpers\SyncStatuses;
use MyCp\mycpBundle\Entity\ownershipStatus;
use MyCp\mycpBundle\Helpers\Dates;
use MyCp\mycpBundle\Helpers\SearchUtils;
use MyCp\mycpBundle\Helpers\FilterHelper;
use MyCp\mycpBundle\Service\TranslatorResponseStatusCode;

// SUM(ownre.xxx) xxx
class ownershipRankingExtraRepository extends EntityRepository {
    function getOfYear($idOwnership, $year) {
        $em = $this->getEntityManager();

        //$year = (new \DateTime())->format('Y');
        $month = (new \DateTime())->format('m');
        if($year != (new \DateTime())->format('Y')){
           $month = '12';
        }

        //El simbolo ! delante indica que no tenga en cuenta la hora
        //$start = \DateTime::createFromFormat('!d/m/Y', $request->get('date_from'));

        $start = \DateTime::createFromFormat('Y-m-d', $year.'-01-01')->modify('-1 month');
        $end = \DateTime::createFromFormat('Y-m-d', $year.'-'.$month.'-01');

        $qb = $em->createQueryBuilder();
        $qb->select("COUNT(ownre.id) cant, SUM(ownre.place) place, SUM(ownre.destinationPlace) destination_place, SUM(ownre.ranking) ranking_points")
            ->from("mycpBundle:ownershipRankingExtra", "ownre");
        $qb->where('ownre.accommodation = :accommodation')->setParameter('accommodation', $idOwnership);
        $qb->andWhere('ownre.startDate >= :start')->setParameter('start', $start->format('Y-m-d'))
            ->andWhere('ownre.startDate < :end')->setParameter('end', $end->format('Y-m-d'));
        $r = $qb->getQuery()->getResult();
        if(count($r) > 0){
            $r = $r[0];
        }


        $qb1 = $em->createQueryBuilder();
        $qb1->select("SUM(ownre.visits) visits, SUM(ownre.totalAvailableRooms) available, SUM(ownre.totalNonAvailableRooms) unavailable,
        SUM(ownre.totalAvailableFacturation) available_facturation, SUM(ownre.totalNonAvailableFacturation) unavailable_facturation,
        SUM(ownre.totalReservedRooms) total_reserved, SUM(ownre.totalFacturation) total_facturation, SUM(ownre.currentMonthFacturation) current_month_facturation")
            ->from("mycpBundle:ownershipRankingExtra", "ownre");
        $qb1->where('ownre.accommodation = :accommodation')->setParameter('accommodation', $idOwnership);
        $qb1->andWhere('YEAR(ownre.startDate) = :year')->setParameter('year', $year);
        $r1 = $qb1->getQuery()->getResult();
        if(count($r1) > 0){
            $r1 = $r1[0];
        }

        $m = array_merge($r, $r1);
        return $m;
    }

    function getList($startDate, $accommodationCode = "", $accommodationName = "", $destination = "", $modality = "", $pointTypes = "", $pointFrom = "", $pointTo = "")
    {
        $em = $this->getEntityManager();

        $qb = $em->createQueryBuilder()
            ->select("extra")
            ->from("mycpBundle:ownershipRankingExtra", "extra")
            ->where("extra.startDate = :startDate")
            ->andWhere("extra.place is not null")
            ->join("extra.accommodation", "accommodation")
            ->orderBy("extra.place", "ASC")
            ->addOrderBy("extra.destinationPlace", "ASC")
            ->setParameter("startDate", $startDate)
        ;

        if($accommodationCode != "" && $accommodationCode != "null" && $accommodationCode != null)
        {
            $qb->andWhere("accommodation.own_mcp_code = :accommodationCode")
                ->setParameter("accommodationCode", $accommodationCode);
        }

        if($accommodationName != "" && $accommodationName != "null" && $accommodationName != null)
        {
            $qb->andWhere("accommodation.own_name LIKE :accommodationName")
                ->setParameter("accommodationName", "%".$accommodationName."%");
        }

        if($destination != "" && $destination != "null" && $destination != null)
        {
            $qb->leftJoin("accommodation.own_destination", "destination")
                ->andWhere("destination.des_id = :destination")
                ->setParameter("destination", $destination);
        }

        if($modality != "" && $modality != "null" && $modality != null)
        {
            if($modality == AccommodationModality::RR_INT)
                $qb->andWhere("extra.rr = 5");
            elseif($modality == AccommodationModality::RI_INT)
                $qb->andWhere("extra.ri = 5");
            elseif($modality == AccommodationModality::NORMAL_INT)
                $qb->andWhere("extra.ri = 0 AND extra.rr = 0");
        }

        if($pointTypes != "" && $pointTypes != "null" && $pointTypes != null)
        {
            if($pointTypes == CompareOption::LESS_THAN){
                if($pointFrom != "" && $pointFrom != "null" && $pointFrom != null){
                    $qb->andWhere("extra.ranking <= :pointFrom")
                       ->setParameter("pointFrom", $pointFrom);
                }
            }
            elseif($pointTypes == CompareOption::MORE_THAN){
                if($pointFrom != "" && $pointFrom != "null" && $pointFrom != null){
                    $qb->andWhere("extra.ranking >= :pointFrom")
                        ->setParameter("pointFrom", $pointFrom);
                }
            }
            elseif($pointTypes == CompareOption::BETWEEN)
            {
                if($pointFrom != "" && $pointFrom != "null" && $pointFrom != null){
                    $qb->andWhere("extra.ranking >= :pointFrom")
                        ->setParameter("pointFrom", $pointFrom);
                }

                if($pointTo != "" && $pointTo != "null" && $pointTo != null){
                    $qb->andWhere("extra.ranking <= :pointTo")
                        ->setParameter("pointTo", $pointTo);
                }
            }

        }

        return $qb->getQuery()->getResult();
    }

    function getLastDates()
    {
        $em = $this->getEntityManager();

        $qb = $em->createQueryBuilder()
            ->select("extra.startDate, extra.endDate")
            ->from("mycpBundle:ownershipRankingExtra", "extra")
            ->where("extra.place is not null")
            ->orderBy("extra.startDate", "DESC")
            ->setMaxResults(1);

        return $qb->getQuery()->getOneOrNullResult();
    }

}
