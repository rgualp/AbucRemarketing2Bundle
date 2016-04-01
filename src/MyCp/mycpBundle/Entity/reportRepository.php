<?php

namespace MyCp\mycpBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * reportRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class reportRepository extends EntityRepository
{
    /**
     * Get categories with reports
     * @return array
     */
    function getExistingReportCategories()
    {
        $em = $this->getEntityManager();
        $spanish = $em->getRepository("mycpBundle:lang")->findOneBy(array("lang_code" => "ES"));
        $queryString = "SELECT DISTINCT
        category.nom_id as categoryId,
        (select min(nom.nom_lang_description) from mycpBundle:nomenclatorLang nom where nom.nom_lang_id_nomenclator = category.nom_id AND nom.nom_lang_id_lang = :spanishId) as categoryName
        FROM mycpBundle:report r
        JOIN r.report_category category
        WHERE r.published = 1";

        $query = $em->createQuery($queryString);
        return $query->setParameter('spanishId', $spanish->getLangId())->getResult();
    }

    function rpDailyInPlaceClients($date, $dateRangeFrom, $dateRangeTo, $timer)
    {
        $em = $this->getEntityManager();
        $queryString = "SELECT u.user_id as clientId,
        u.user_user_name as clientName,
        u.user_last_name as clientLastName,
        co.co_name as clientCountry,
        co.co_code as clientCountryCode,
        u.user_email as clientEmail,
        own.own_id as ownId,
        own.own_mcp_code as ownCode,
        prov.prov_phone_code as phoneCode,
        own.own_phone_number as ownPhoneNumber,
        own.own_mobile_number as ownMobile,
        own.own_homeowner_1 as owner1,
        own.own_homeowner_2 as owner2,
        '' as itinerary,
        (SELECT SUM(owr1.own_res_nights) FROM mycpBundle:ownershipReservation owr1 JOIN owr1.own_res_gen_res_id gres1 JOIN owr1.own_res_reservation_booking b1 WHERE owr1.own_res_status = :status
             AND u.user_id = gres1.gen_res_user_id AND ((owr1.own_res_reservation_from_date >= :dateRangeFrom AND owr1.own_res_reservation_to_date <= :dateRangeTo) OR
              (owr1.own_res_reservation_from_date <= :dateRangeFrom AND owr1.own_res_reservation_to_date >= :dateRangeFrom) OR (owr1.own_res_reservation_from_date <= :dateRangeTo AND owr1.own_res_reservation_from_date >= :dateRangeTo))) as bookedNights,
        (SELECT COUNT(DISTINCT own2.own_address_province) FROM mycpBundle:ownershipReservation owr2 JOIN owr2.own_res_gen_res_id gres2 JOIN owr2.own_res_reservation_booking b2 JOIN gres2.gen_res_own_id own2  WHERE owr2.own_res_status = :status
             AND u.user_id = gres2.gen_res_user_id AND ((owr2.own_res_reservation_from_date >= :dateRangeFrom AND owr2.own_res_reservation_to_date <= :dateRangeTo) OR
              (owr2.own_res_reservation_from_date <= :dateRangeFrom AND owr2.own_res_reservation_to_date >= :dateRangeFrom) OR (owr2.own_res_reservation_from_date <= :dateRangeTo AND owr2.own_res_reservation_from_date >= :dateRangeTo))) as bookedDestinations,
        (SELECT MIN(owr3.own_res_reservation_from_date) FROM mycpBundle:ownershipReservation owr3 JOIN owr3.own_res_gen_res_id gres3 JOIN owr3.own_res_reservation_booking b3 WHERE owr3.own_res_status = :status
             AND u.user_id = gres3.gen_res_user_id AND ((owr3.own_res_reservation_from_date >= :dateRangeFrom AND owr3.own_res_reservation_to_date <= :dateRangeTo) OR
              (owr3.own_res_reservation_from_date <= :dateRangeFrom AND owr3.own_res_reservation_to_date >= :dateRangeFrom) OR (owr3.own_res_reservation_from_date <= :dateRangeTo AND owr3.own_res_reservation_from_date >= :dateRangeTo))) as arrivalDate,
        (SELECT MAX(owr4.own_res_reservation_to_date) FROM mycpBundle:ownershipReservation owr4 JOIN owr4.own_res_gen_res_id gres4 JOIN owr4.own_res_reservation_booking b4 WHERE owr4.own_res_status = :status
             AND u.user_id = gres4.gen_res_user_id AND ((owr4.own_res_reservation_from_date >= :dateRangeFrom AND owr4.own_res_reservation_to_date <= :dateRangeTo) OR
              (owr4.own_res_reservation_from_date <= :dateRangeFrom AND owr4.own_res_reservation_to_date >= :dateRangeFrom) OR (owr4.own_res_reservation_from_date <= :dateRangeTo AND owr4.own_res_reservation_from_date >= :dateRangeTo))) as leavingDate
        FROM mycpBundle:ownershipReservation owr
        JOIN owr.own_res_gen_res_id gres
        JOIN gres.gen_res_user_id u
        JOIN gres.gen_res_own_id own
        JOIN u.user_country co
        JOIN owr.own_res_reservation_booking b
        JOIN own.own_address_province prov
        WHERE (select count(p) FROM mycpBundle:payment p WHERE p.booking = b.booking_id) > 0
          AND owr.own_res_status = :status
          AND owr.own_res_reservation_from_date <= :date AND owr.own_res_reservation_to_date > :date
       GROUP BY owr.own_res_gen_res_id
       ORDER BY bookedDestinations ASC, u.user_user_name";

        $query = $em->createQuery($queryString)
                    ->setParameters(array(
                        'date' => $date,
                        'status' => ownershipReservation::STATUS_RESERVED,
                        'dateRangeFrom' => $dateRangeFrom,
                        'dateRangeTo' => $dateRangeTo
                    ));
        $content = $query->getArrayResult();


        for($i= 0; $i < count($content); $i++)
        {
            $content[$i]["itinerary"] = $this->calculateItinerary($content[$i]["clientId"], $dateRangeFrom, $dateRangeTo);

            //if($content[$i]["bookedNights"] == null || $content[$i]["bookedNights"] == 0)
                $content[$i]["bookedNights"] = $timer->nights($content[$i]["arrivalDate"], $content[$i]["leavingDate"]);
        }

        return $content;
    }

    function calculateItinerary($userId, $dateRangeFrom, $dateRangeTo)
    {
        $em = $this->getEntityManager();
        $queryString = "SELECT DISTINCT own.own_mcp_code, prov.prov_name, prov.prov_code, gres.gen_res_id FROM mycpBundle:ownershipReservation owr
        JOIN owr.own_res_gen_res_id gres
        JOIN gres.gen_res_own_id own
        JOIN owr.own_res_reservation_booking b
        JOIN own.own_address_province prov
        WHERE owr.own_res_status = :status
          AND gres.gen_res_user_id = :userId
          AND ((owr.own_res_reservation_from_date >= :dateRangeFrom AND owr.own_res_reservation_to_date <= :dateRangeTo) OR
              (owr.own_res_reservation_from_date <= :dateRangeFrom AND owr.own_res_reservation_to_date >= :dateRangeFrom) OR (owr.own_res_reservation_from_date <= :dateRangeTo AND owr.own_res_reservation_from_date >= :dateRangeTo))
        ORDER BY owr.own_res_reservation_from_date ASC";

        $query = $em->createQuery($queryString)
            ->setParameters(array(
                'userId' => $userId,
                'status' => ownershipReservation::STATUS_RESERVED,
                'dateRangeFrom' => $dateRangeFrom,
                'dateRangeTo' => $dateRangeTo
            ));

        return $query->getResult();
    }

    function reservationsByClientsSummary($dateRangeFrom, $dateRangeTo){
//        die(dump($dateRangeFrom));
        $em=$this->getEntityManager();
        $qb="SELECT user.user_name,
        user.user_last_name,
        user.user_id,
       (SELECT COUNT(generalreservation.gen_res_id) FROM mycpBundle:generalReservation generalreservation WHERE generalreservation.gen_res_user_id=user.user_id AND generalreservation.gen_res_date >= :datefrom AND generalreservation.gen_res_date <=:dateto) AS solicitudes,
       (SELECT COUNT(generalreservation2.gen_res_id) FROM mycpBundle:generalReservation generalreservation2 WHERE generalreservation2.gen_res_user_id=user.user_id AND generalreservation2.gen_res_status=1 AND generalreservation2.gen_res_date >= :datefrom AND generalreservation2.gen_res_date <=:dateto) AS disponibles,
       (SELECT COUNT(generalreservation3.gen_res_id) FROM mycpBundle:generalReservation generalreservation3 WHERE generalreservation3.gen_res_user_id=user.user_id AND generalreservation3.gen_res_status=3 AND generalreservation3.gen_res_date >= :datefrom AND generalreservation3.gen_res_date <=:dateto) AS no_disponibles,
       (SELECT COUNT(generalreservation4.gen_res_id) FROM mycpBundle:generalReservation generalreservation4 WHERE generalreservation4.gen_res_user_id=user.user_id AND generalreservation4.gen_res_status=0 AND generalreservation4.gen_res_date >= :datefrom AND generalreservation4.gen_res_date <=:dateto) AS pendientes,
       (SELECT COUNT(generalreservation5.gen_res_id) FROM mycpBundle:generalReservation generalreservation5 WHERE generalreservation5.gen_res_user_id=user.user_id AND generalreservation5.gen_res_status=2 AND generalreservation5.gen_res_date >= :datefrom AND generalreservation5.gen_res_date <=:dateto) AS reservas,
       (SELECT COUNT(generalreservation6.gen_res_id) FROM mycpBundle:generalReservation generalreservation6 WHERE generalreservation6.gen_res_user_id=user.user_id AND generalreservation6.gen_res_status=8 AND generalreservation6.gen_res_date >= :datefrom AND generalreservation6.gen_res_date <=:dateto) AS vencidas
       FROM mycpBundle:user user WHERE user.user_role='ROLE_CLIENT_TOURIST'
       HAVING solicitudes>0
       ORDER BY user.user_name ASC";
        $query = $em->createQuery($qb)
            ->setParameters(array(
                'datefrom' => $dateRangeFrom,
                'dateto' => $dateRangeTo
            ));
        $content = $query->getArrayResult();
       return $content;
    }
    function bookingsSummary($dateRangeFrom, $dateRangeTo){
        $em=$this->getEntityManager();
        $qb="SELECT COUNT(owr1.own_res_id) FROM mycpBundle:ownershipReservation owr1 JOIN owr1.own_res_gen_res_id generalreservation JOIN owr1.own_res_reservation_booking booking WHERE generalreservation.gen_res_date BETWEEN :datefrom AND :dateto";
        $query = $em->createQuery($qb)
            ->setParameters(array(
                'datefrom' => $dateRangeFrom,
                'dateto' => $dateRangeTo
            ));
        $content = $query->getSingleResult();
        return $content;
    }
}
