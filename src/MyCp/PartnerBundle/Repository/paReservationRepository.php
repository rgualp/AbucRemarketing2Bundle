<?php

namespace MyCp\PartnerBundle\Repository;

use Doctrine\ORM\EntityRepository;
use MyCp\PartnerBundle\Entity\paClient;
use MyCp\PartnerBundle\Entity\paTravelAgency;

/**
 * paReservationRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class paReservationRepository extends EntityRepository {

    /*public function search($destination = null, $fromDate = null, $toDate = null, $guests = null, $hasBabyFacilities = null, $rooms = null)
    {
        $qb = $this->createQueryBuilder()
            ->
            ->from("mycpBundle:ownership", "own")
            ->join("own.data", "data")
            ->leftJoin("own.awards", "award")
    }*/

    public function getOpenReservationsList($agency){
        return $this->createQueryBuilder("query")
            ->select("res.adults, res.number, res.id, res.children")
            ->from("PartnerBundle:paReservation", "res")
            ->join("res.client", "client")
            ->where("client.travelAgency = :travelAgency")
            ->andWhere("res.closed = 0")
            ->setParameter("travelAgency", $agency->getId())
            ->getQuery()->getResult();
    }

    public function newReservation($agency, $clientName, $adults, $children, $dateFrom, $dateTo)
    {
        $em = $this->getEntityManager();
        $client = $this->createQueryBuilder("query")
            ->from("PartnerBundle:paClient", "client")
            ->where("client.name LIKE :fullname")
            ->andWhere("client.travelAgency = :travelAgencyId")
            ->setParameter("fullname", "%".$clientName."%")
            ->setParameter("travelAgencyId", $agency->getId())
            ->getQuery()->getOneOrNullResult();

        if($client == null)
        {
            $client = new paClient();
            $client->setFullName($clientName)
                ->setTravelAgency($agency);

            $em->persist($client);


        }

    }
}
