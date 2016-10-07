<?php

namespace MyCp\PartnerBundle\Repository;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use MyCp\mycpBundle\Entity\ownershipReservation;
use MyCp\PartnerBundle\Entity\paClient;
use MyCp\PartnerBundle\Entity\paReservation;
use MyCp\PartnerBundle\Entity\paReservationDetail;
use MyCp\PartnerBundle\Entity\paTravelAgency;
use MyCp\mycpBundle\Entity\generalReservation;

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
            ->select("res", "client")
            ->from("PartnerBundle:paReservation", "res")
            ->join("res.client", "client")
            ->join("client.travelAgency", "agency")
            ->where("agency.id = :travelAgency")
            ->andWhere("res.closed = 0")
            ->setParameter("travelAgency", $agency->getId())
            ->getQuery()->getResult();
    }

    public function newReservation($agency, $clientName, $adults, $children, $dateFrom, $dateTo, $accommodation, $user, $container, $translator,$clientId, $roomType = null, $roomsTotal= null/*,$clientEmail*/)
    {
        $em = $this->getEntityManager();
        if($clientId!=''){
            $client = $em->getRepository('PartnerBundle:paClient')->find($clientId);
            $client->setFullName(trim(strtolower($clientName)));
            //$client->setEmail(trim(strtolower($clientEmail)));
            $em->persist($client);
            $em->flush();
        }
        else{
            $client = $this->createQueryBuilder("query")
                ->select("client")
                ->from("PartnerBundle:paClient", "client")
                //->join("client.travelAgency", "agency")
            ->where("client.fullname = :fullname")
                //->andWhere("agency.id = :travelAgencyId")
                ->setMaxResults(1)
            ->setParameter("fullname", $clientName)
                //->setParameter("travelAgencyId", $agency->getId())
                ->getQuery()->getOneOrNullResult();

            if($client == null)
            {
                $client = new paClient();
                $client->setFullName(trim(strtolower($clientName)))
                    ->setTravelAgency($agency);
                    //->setEmail(trim(strtolower($clientEmail)));
                $em->persist($client);
            }
        }

        //Buscar a ver si el cliente tiene una reserva abierta
        $openReservation = $this->createQueryBuilder("query")
            ->from("PartnerBundle:paReservation", "reservation")
            ->select("reservation")
            ->join("reservation.client", "client")
            ->join("client.travelAgency", "agency")
            ->where("agency.id = :travelAgencyId")
            ->andWhere("client.id = :idClient")
            ->andWhere("reservation.closed = 0")
            ->setParameter("travelAgencyId", $agency->getId())
            ->setParameter("idClient", $client->getId())
            ->getQuery()->getOneOrNullResult();

        if($openReservation == null)
        {
            $openReservation = new paReservation();
            $openReservation->setClient($client);
        }

        $openReservation->setAdults($adults)
            ->setChildren($children);

        if($this->canCreateReservation($openReservation, $accommodation, $dateFrom, $dateTo)) {
            //Actualizar total de ubicados
            $openReservation->setAdultsWithAccommodation($openReservation->getAdultsWithAccommodation() + $adults)
                ->setChildrenWithAccommodation($openReservation->getChildrenWithAccommodation() + $children);

            //Agregar un generalReservation por casa
            $returnedObject = $em->getRepository("PartnerBundle:paGeneralReservation")->createReservationForPartner($user, $accommodation, $dateFrom, $dateTo, $adults, $children, $container,$translator, null, $roomType, $roomsTotal);

            if ($returnedObject["successful"]) {
                $detail = new paReservationDetail();
                $detail->setReservation($openReservation)->setOpenReservationDetail($returnedObject["reservation"]);

                $em->persist($detail);

                $openReservation->addDetail($detail);
                $em->persist($openReservation);

                $em->flush();
            }

            return $returnedObject;
        }

        return null;
    }

    public function canCreateReservation($reservation, $accommodation, $dateFrom, $dateTo)
    {
        $em = $this->getEntityManager();
        $countReservations = $em->createQueryBuilder()
            ->from("PartnerBundle:paGeneralReservation", "pa_gres")
            ->join("pa_gres.accommodation", "accommodation")
            ->join("pa_gres.travelAgencyOpenReservationsDetails", "paDetail")
            ->leftJoin("paDetail.reservation", "reservation")
            ->select("count(pa_gres)")
            ->where("accommodation.own_id = :ownId")
            ->andWhere("reservation.id = :reservationId")
            ->andWhere("pa_gres.dateFrom = :dateFrom")
            ->andWhere("pa_gres.dateTo = :dateTo")
            ->setParameters(array("dateFrom" => $dateFrom, "dateTo" => $dateTo, "ownId" => $accommodation->getOwnId(), "reservationId" => $reservation->getId()))
            ->setMaxResults(1)->getQuery()->getSingleScalarResult();

        return ($countReservations == 0);
    }

    public function getCartItems($travelAgency, $idsGeneralReservation = array())
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder()
            ->select("DISTINCT client.fullname, paReservation.id,
            (SELECT count(rDetail) FROM PartnerBundle:paReservationDetail rDetail JOIN rDetail.reservationDetail gres WHERE gres.gen_res_status = :availableStatus AND rDetail.reservation = paReservation.id) as available,
            (SELECT count(rDetail1) FROM PartnerBundle:paReservationDetail rDetail1 JOIN rDetail1.reservationDetail gres1 WHERE rDetail1.reservation = paReservation.id) as detailsCount,
            (IF(genRes.gen_res_id IN (:ids), 1, 0)) as showOpened")
            ->from("PartnerBundle:paReservation", "paReservation")
            ->join("paReservation.client", "client")
            ->join("paReservation.details", "detail")
            ->join("detail.reservationDetail", "genRes")
            ->where("paReservation.closed = 1")
            ->andWhere("client.travelAgency = :travelAgency")
            ->andWhere("genRes.gen_res_status = :availableStatus1")
            ->setParameter("travelAgency", $travelAgency->getId())
            ->setParameter("availableStatus", generalReservation::STATUS_AVAILABLE)
            ->setParameter("availableStatus1", generalReservation::STATUS_AVAILABLE)
            ->setParameter("ids", $idsGeneralReservation)
        ;
        return $qb->getQuery()->getResult();
    }

    public function getReservationsInCart($travelAgency)
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder()
            ->select("paReservation")
            ->from("PartnerBundle:paReservation", "paReservation")
            ->join("paReservation.client", "client")
            ->join("paReservation.details", "detail")
            ->join("detail.reservationDetail", "genRes")
            ->where("paReservation.closed = 1")
            ->andWhere("client.travelAgency = :travelAgency")
            ->andWhere("genRes.gen_res_status = :availableStatus")
            ->setParameter("travelAgency", $travelAgency->getId())
            ->setParameter("availableStatus", generalReservation::STATUS_AVAILABLE)
        ;
        return $qb->getQuery()->getResult();
    }

    public function getDetails($reservationId, $idsGeneralReservation = array()){
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder()
            ->select("room.room_num,
            room.room_type,
            ownRes.own_res_reservation_from_date,
            ownRes.own_res_reservation_to_date,
            accommodation.own_name,
            accommodation.own_mcp_code,
            des.des_name,
            prov.prov_name,
            ownRes.own_res_count_adults as adults,
            ownRes.own_res_count_childrens as children,
            genRes.gen_res_id,
            ownRes.own_res_total_in_site as totalInSite,
            reservation.id as idReservation,
            ownRes.own_res_id,
            (IF(genRes.gen_res_id IN (:ids), 1, 0)) as showChecked
            ")
            ->from("mycpBundle:ownershipReservation", "ownRes")
            ->join('mycpBundle:room', 'room', Join::WITH, 'ownRes.own_res_selected_room_id = room.room_id')
            ->join("ownRes.own_res_gen_res_id", "genRes")
            ->join("genRes.gen_res_own_id", "accommodation")
            ->join("accommodation.own_destination", "des")
            ->join("accommodation.own_address_province", "prov")
            ->join("genRes.travelAgencyDetailReservations", "detail")
            ->join("detail.reservation", "reservation")
            ->join("reservation.client", "client")
            ->where("reservation.id = :reservationId")
            ->andWhere("ownRes.own_res_status = :availableStatus")
            ->setParameter("reservationId", $reservationId)
            ->setParameter("availableStatus", ownershipReservation::STATUS_AVAILABLE)
            ->setParameter("ids", $idsGeneralReservation)
        ;
        return $qb->getQuery()->getResult();
    }

    public function getDetailsByIds($reservationIds){
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder()
            ->select("MIN(ownRes.own_res_reservation_from_date) as own_res_reservation_from_date,
            accommodation.own_name,
            accommodation.own_mcp_code,
            accommodation.own_id,
            prov.prov_name,
            SUM(ownRes.own_res_count_adults) as adults,
            SUM(ownRes.own_res_count_childrens) as children,
            genRes.gen_res_id,
            SUM(ownRes.own_res_total_in_site) as totalInSite,
            accommodation.own_commission_percent as commission,
            reservation.id as idReservation,
            client.fullname
            ")
            ->from("mycpBundle:ownershipReservation", "ownRes")
            ->join("ownRes.own_res_gen_res_id", "genRes")
            ->join("genRes.gen_res_own_id", "accommodation")
            ->join("accommodation.own_address_province", "prov")
            ->join("genRes.travelAgencyDetailReservations", "detail")
            ->join("detail.reservation", "reservation")
            ->join("reservation.client", "client")
            ->where('ownRes.own_res_id IN (:reservationIds)')
            ->andWhere("ownRes.own_res_status = :availableStatus")
            ->setParameter("reservationIds", $reservationIds, Connection::PARAM_STR_ARRAY)
            ->setParameter("availableStatus", ownershipReservation::STATUS_AVAILABLE)
            ->groupBy("genRes.gen_res_id")
            ->orderBy("reservation.id")
            ->addOrderBy("own_res_reservation_from_date")
        ;
        return $qb->getQuery()->getResult();
    }
}
