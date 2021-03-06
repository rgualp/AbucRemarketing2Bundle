<?php

namespace MyCp\mycpBundle\Entity;

use Doctrine\ORM\EntityRepository;
use MyCp\mycpBundle\Entity\album;
use MyCp\mycpBundle\Entity\albumLang;
use MyCp\mycpBundle\Helpers\BackendModuleName;
use MyCp\mycpBundle\Helpers\DataBaseTables;
use MyCp\mycpBundle\Helpers\OrderByHelper;
use MyCp\mycpBundle\Helpers\OwnershipStatuses;

/**
 * accommodationCalendarModalityRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class accommodationModalityFrequencyRepository extends EntityRepository
{
    public function addFrequency($idAccommodation, $source = "Módulo Casa")
    {
        $em = $this->getEntityManager();
        $date = new \DateTime();

        $frequency = $this->getFrequency($idAccommodation, $date);

        if($frequency == null)
        {
            $accommodation = $em->getRepository("mycpBundle:ownership")->find($idAccommodation);

            $frequency = new accommodationCalendarFrequency();
            $frequency->setUpdatedDate($date)
                ->setAccommodation($accommodation)
                ->setSource($source);

            $em->persist($frequency);
            $em->flush();

            return true;
        }

        return false;
    }

    public function addFrequencyByRoom($idRoom, $source = "Módulo Casa")
    {
        $em = $this->getEntityManager();
        $room = $em->getRepository("mycpBundle:room")->find($idRoom);

        return $this->addFrequency($room->getRoomOwnership()->getOwnId(), $source);
    }

    public function getFrequency($idAccommodation, $date)
    {
        $em = $this->getEntityManager();

        $qb = $em->createQueryBuilder()
            ->from("mycpBundle:accommodationCalendarFrequency", "freq")
            ->select("freq")
            ->join("freq.accommodation", "accommodation")
            ->where("accommodation.own_id = :accommodationId")
            ->andWhere("DATE_DIFF(:date, freq.updatedDate) = 0")
            ->setParameter("date", $date)
            ->setParameter("accommodationId", $idAccommodation)
        ;

        return $qb->getQuery()->getOneOrNullResult();
    }
}
