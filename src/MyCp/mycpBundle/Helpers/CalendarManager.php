<?php

/**
 * Description of CalendarManager
 *
 * @author Yanet Morales
 */

namespace MyCp\mycpBundle\Helpers;

use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\Response;
use Sabre\VObject;
use MyCp\mycpBundle\Entity\ownershipReservation;
use MyCp\mycpBundle\Entity\ownershipStatus;

class CalendarManager{

    /**
     * 'doctrine.orm.entity_manager' service
     *
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;
    private $container;

    /**
     * @var string
     */
    private $directoryPath;

    public function __construct(EntityManager $em, $container, $directoryPath) {
        $this->em = $em;
        $this->container = $container;
        $this->directoryPath = $directoryPath;
    }

    public function createICalForAllAccommodations()
    {
        $ownerships = $this->em->getRepository("mycpBundle:ownership")->findBy(array("own_status" => ownershipStatus::STATUS_ACTIVE));

        foreach($ownerships as $own)
        {
            $this->createICalForAccommodation($own->getOwnId());
        }
    }

    public function createICalForAccommodation($ownId)
    {
        $rooms = $this->em->getRepository("mycpBundle:room")->findBy(array("room_ownership" => $ownId));

        foreach($rooms as $room)
        {
            $this->createICalForRoom($room->getRoomId(), $room->getRoomCode());
        }
    }

    public function createICalForRoom($roomId, $roomCode) {
        $unavailabilyDetails = $this->em->getRepository("mycpBundle:unavailabilityDetails")->getRoomDetailsForCalendar($roomId);
        $reservations = $this->em->getRepository("mycpBundle:room")->getReservationsForCalendar($roomId);

        $calendar = new VObject\Component\VCalendar();

        foreach ($unavailabilyDetails as $event) {
            $calendar->add('VEVENT', [
                'SUMMARY' => 'No disponible',
                'DTSTART' => $event->getUdFromDate(),
                'DTEND' => $event->getUdToDate(),
            ]);
        }

        foreach ($reservations as $event) {
            $startDate = $event->getOwnResReservationFromDate();
            $endDate = $event->getOwnResReservationToDate();
            $calendar->add('VEVENT', [
                'SUMMARY' => ($event->getOwnResStatus() == ownershipReservation::STATUS_RESERVED) ? "Reservada en MyCasaParticular - ".$event->getOwnResGenResId()->getCASId() : "Reserva no disponible",
                'DTSTART' => $startDate,
                'DTEND' => $endDate->modify("-1 day"),
            ]);
        }

        $fileContent = $calendar->serialize();
        $this->save($roomCode.".ics",$fileContent);
    }

    private function export($fileName) {
        $content = file_get_contents($this->directoryPath . $fileName);
        return new Response(
                $content, 200, array(
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"'
                )
        );
    }

    private function save($fileName, $content) {

        FileIO::createDirectoryIfNotExist($this->directoryPath);
        $fp = fopen($this->directoryPath . "/".$fileName, "wb");
        fwrite($fp, $content);
        fclose($fp);
    }

}

?>
