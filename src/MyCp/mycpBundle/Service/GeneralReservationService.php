<?php

namespace MyCp\mycpBundle\Service;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManager;
use MyCp\FrontEndBundle\Helpers\Time;
use MyCp\mycpBundle\Entity\booking;
use MyCp\mycpBundle\Entity\generalReservation;
use MyCp\mycpBundle\Entity\ownershipReservation;
use MyCp\mycpBundle\Entity\user;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class GeneralReservationService extends Controller
{
    /**
     * @var ObjectManager
     */
    private $em;

    private $timer;

    protected $tripleRoomCharge;

    public function __construct(ObjectManager $em, Time $timer, $tripleRoomCharge)
    {
        $this->em = $em;
        $this->timer = $timer;
        $this->tripleRoomCharge = $tripleRoomCharge;
    }

    public function createAvailableOfferFromRequest($request, user $user)
    {
        return $this->createOfferFromRequest($request, $user, generalReservation::STATUS_AVAILABLE, ownershipReservation::STATUS_AVAILABLE);
    }

    public function createReservedOfferFromRequest($request, user $user, booking $booking)
    {
        return $this->createOfferFromRequest($request, $user, generalReservation::STATUS_RESERVED, ownershipReservation::STATUS_RESERVED, $booking);
    }

    private function createOfferFromRequest($request, user $user, $generalReservationStatus, $ownReservationsStatus, booking $booking = null)
    {
        $id_ownership = $request->get('data_ownership');
        $reservations = array();

        $this->em = $this->getDoctrine()->getManager();
        $ownership = $this->em->getRepository('mycpBundle:ownership')->find($id_ownership);

        if (!$request->get('data_reservation'))
            throw $this->createNotFoundException();
        $data = $request->get('data_reservation');

        $data = explode('/', $data);

        $from_date = $data[0];
        $to_date = $data[1];
        $ids_rooms = $data[2];

        $count_guests = $data[3];
        $count_kids = $data[4];
        $array_ids_rooms = explode('&', $ids_rooms);
        array_shift($array_ids_rooms);
        $array_count_guests = explode('&', $count_guests);
        array_shift($array_count_guests);
        $array_count_kids = explode('&', $count_kids);
        array_shift($array_count_kids);

        $reservation_date_from = $from_date;
        $reservation_date_from = explode('&', $reservation_date_from);

        $start_timestamp = mktime(0, 0, 0, $reservation_date_from[1], $reservation_date_from[0], $reservation_date_from[2]);

        $reservation_date_to = $to_date;
        $reservation_date_to = explode('&', $reservation_date_to);
        $end_timestamp = mktime(0, 0, 0, $reservation_date_to[1], $reservation_date_to[0], $reservation_date_to[2]);

        $general_reservation = new generalReservation();
        $general_reservation->setGenResUserId($user);
        $general_reservation->setGenResDate(new \DateTime(date('Y-m-d')));
        $general_reservation->setGenResStatusDate(new \DateTime(date('Y-m-d')));
        $general_reservation->setGenResHour(date('G'));
        $general_reservation->setGenResStatus($generalReservationStatus);
        $general_reservation->setGenResFromDate(new \DateTime(date("Y-m-d H:i:s", $start_timestamp)));
        $general_reservation->setGenResToDate(new \DateTime(date("Y-m-d H:i:s", $end_timestamp)));
        $general_reservation->setGenResSaved(0);
        $general_reservation->setGenResOwnId($ownership);
        $this->em->persist($general_reservation);

        $total_price = 0;
        $destination_id = ($ownership->getOwnDestination() != null) ? $ownership->getOwnDestination()->getDesId() : null;
        $count_adults = 0;
        $count_children = 0;

        for ($i = 0; $i < count($array_ids_rooms); $i++) {
            $room = $this->em->getRepository('mycpBundle:room')->find($array_ids_rooms[$i]);
            $count_adults = (isset($array_count_kids[$i])) ? $array_count_guests[$i] : 1;
            $count_children = (isset($array_count_kids[$i])) ? $array_count_kids[$i] : 0;

            $array_dates = $this->timer->datesBetween($start_timestamp, $end_timestamp);
            $temp_price = 0;
            $triple_room_recharge = ($room->isTriple() && $count_adults + $count_children >= 3) ? $this->tripleRoomCharge : 0;
            $seasons = $this->em->getRepository("mycpBundle:season")->getSeasons($start_timestamp, $end_timestamp, $destination_id);
            for ($a = 0; $a < count($array_dates) - 1; $a++) {
                $seasonType = $this->timer->seasonTypeByDate($seasons, $array_dates[$a]);
                $roomPrice = $room->getPriceBySeasonType($seasonType);
                $total_price += $roomPrice + $triple_room_recharge;
                $temp_price += $roomPrice + $triple_room_recharge;
            }

            $ownership_reservation = new ownershipReservation();
            $ownership_reservation->setOwnResCountAdults($count_adults);
            $ownership_reservation->setOwnResCountChildrens($count_children);
            $ownership_reservation->setOwnResNightPrice(0);
            $ownership_reservation->setOwnResStatus($ownReservationsStatus);
            $ownership_reservation->setOwnResReservationFromDate(new \DateTime(date("Y-m-d H:i:s", $start_timestamp)));
            $ownership_reservation->setOwnResReservationToDate(new \DateTime(date("Y-m-d H:i:s", $end_timestamp)));
            $ownership_reservation->setOwnResSelectedRoomId($room);
            $ownership_reservation->setOwnResRoomPriceDown($room->getRoomPriceDownTo());
            $ownership_reservation->setOwnResRoomPriceUp($room->getRoomPriceUpTo());
            $specialPrice = ($room->getRoomPriceSpecial() != null && $room->getRoomPriceSpecial() > 0) ? $room->getRoomPriceSpecial() : $room->getRoomPriceUpTo();
            $ownership_reservation->setOwnResRoomPriceSpecial($specialPrice);
            $ownership_reservation->setOwnResGenResId($general_reservation);
            $ownership_reservation->setOwnResRoomType($room->getRoomType());
            $ownership_reservation->setOwnResTotalInSite($temp_price);
            $ownership_reservation->setOwnResReservationBooking($booking);
            $general_reservation->setGenResTotalInSite($total_price);
            $general_reservation->setGenResSaved(1);
            $this->em->persist($ownership_reservation);
            array_push($reservations, $ownership_reservation);

        }

        $this->em->flush();

        $nights = array();

        foreach ($reservations as $nReservation) {
            $nights[$nReservation->getOwnResId()] = count($array_dates) - 1;
        }

        return array('reservations' => $reservations, 'nights' => $nights, 'generalReservation' => $general_reservation);
    }

}
