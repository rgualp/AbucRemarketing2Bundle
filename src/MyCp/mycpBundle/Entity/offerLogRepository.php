<?php

namespace MyCp\mycpBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * offerLogRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class offerLogRepository extends EntityRepository
{
    public function getLogs($idReservation)
    {
        $result = array();

        $result = $this->getLogsRecursive($result, $idReservation);

       // dump($result); die;
        return $result;
    }

    private function getLogsRecursive($results, $idReservation)
    {
        $em=$this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->select('ol')
            ->from("mycpBundle:offerLog", "ol")
            ->orderBy('ol.log_date', 'DESC')
            ->where('ol.log_offer_reservation = :reservation')
            ->setParameter("reservation", $idReservation)
        ;

        $offerLog = $qb->getQuery()->getOneOrNullResult();

        if($offerLog != null)
        {
            array_push($results, $offerLog);
            return $this->getLogsRecursive($results, $offerLog->getLogFromReservation());
        }

        else return $results;

    }
}
