<?php

namespace MyCp\mycpBundle\Service;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManager;
use MyCp\FrontEndBundle\Helpers\ReservationHelper;
use MyCp\FrontEndBundle\Helpers\Time;
use MyCp\mycpBundle\Entity\booking;
use MyCp\mycpBundle\Entity\generalReservation;
use MyCp\mycpBundle\Entity\ownershipReservation;
use MyCp\mycpBundle\Entity\user;
use MyCp\mycpBundle\Helpers\BackendModuleName;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class GeneralReservationService extends Controller
{
    /**
     * @var ObjectManager
     */
    private $em;

    private $timer;

    protected $tripleRoomCharge;

    private $calendarService;

    private $logger;

    public function __construct(ObjectManager $em, Time $timer, $tripleRoomCharge, $calendarService, $logger)
    {
        $this->em = $em;
        $this->timer = $timer;
        $this->tripleRoomCharge = $tripleRoomCharge;
        $this->calendarService = $calendarService;
        $this->logger = $logger;
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
        $totalNights = 0;
        $partialTotalNights = 0;

        for ($i = 0; $i < count($array_ids_rooms); $i++) {
            $room = $this->em->getRepository('mycpBundle:room')->find($array_ids_rooms[$i]);
            $count_adults = (isset($array_count_kids[$i])) ? $array_count_guests[$i] : 1;
            $count_children = (isset($array_count_kids[$i])) ? $array_count_kids[$i] : 0;

            $array_dates = $this->timer->datesBetween($start_timestamp, $end_timestamp);
            $partialTotalNights = $this->timer->nights($start_timestamp, $end_timestamp);
            $totalNights += $partialTotalNights;
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
            $ownership_reservation->setOwnResNights($partialTotalNights);
            $general_reservation->setGenResTotalInSite($total_price);
            $general_reservation->setGenResSaved(1);
            $this->em->persist($ownership_reservation);
            array_push($reservations, $ownership_reservation);
        }

        $general_reservation->setGenResNights($totalNights);
        $this->em->persist($general_reservation);
        $this->em->flush();

        $nights = array();
        foreach ($reservations as $nReservation) {
            $nights[$nReservation->getOwnResId()] = count($array_dates) - 1;
        }

        $general_reservation->setGenResNights($totalNights);
        $this->em->persist($general_reservation);
        $this->em->flush();


        return array('reservations' => $reservations, 'nights' => $nights, 'generalReservation' => $general_reservation);
    }

    public function updateReservationFromRequest($post, $reservation, $ownership_reservations)
    {
        $process = $this->processPost($post);
        $errors = $process["errors"];
        $details_total = $process["detailsTotal"];
        $available_total = $process["available"];
        $non_available_total = $process["nonAvailable"];
        $cancelled_total = $process["cancelled"];
        $outdated_total = $process["outdated"];

        if (count($errors) == 0) {
            $totalPrice = 0;
            $nights = 0;
            foreach ($ownership_reservations as $ownership_reservation) {
                $start = explode('/', $post['date_from_' . $ownership_reservation->getOwnResId()]);
                $end = explode('/', $post['date_to_' . $ownership_reservation->getOwnResId()]);
                $start_timestamp = mktime(0, 0, 0, $start[1], $start[0], $start[2]);
                $end_timestamp = mktime(0, 0, 0, $end[1], $end[0], $end[2]);

                $ownership_reservation->setOwnResReservationFromDate(new \DateTime(date("Y-m-d H:i:s", $start_timestamp)));
                $ownership_reservation->setOwnResReservationToDate(new \DateTime(date("Y-m-d H:i:s", $end_timestamp)));

                if (isset($post['service_room_price_' . $ownership_reservation->getOwnResId()]) && $post['service_room_price_' . $ownership_reservation->getOwnResId()] != "" && $post['service_room_price_' . $ownership_reservation->getOwnResId()] != "0") {
                    $ownership_reservation->setOwnResNightPrice($post['service_room_price_' . $ownership_reservation->getOwnResId()]);
                }
                else
                    $ownership_reservation->setOwnResNightPrice(0);


                $ownership_reservation->setOwnResCountAdults($post['service_room_count_adults_' . $ownership_reservation->getOwnResId()]);
                $ownership_reservation->setOwnResCountChildrens($post['service_room_count_childrens_' . $ownership_reservation->getOwnResId()]);
                $ownership_reservation->setOwnResStatus($post['service_own_res_status_' . $ownership_reservation->getOwnResId()]);

                $partialTotalPrice = ReservationHelper::getTotalPrice($this->em, $this->timer, $ownership_reservation, $this->tripleRoomCharge);

                $totalPrice+=$partialTotalPrice;
                $ownership_reservation->setOwnResTotalInSite($partialTotalPrice);

                $partialNights = $this->timer->nights($start_timestamp, $end_timestamp);
                $nights+=$partialNights;
                $ownership_reservation->setOwnResNights($partialNights);

                if ($post['service_own_res_status_' . $ownership_reservation->getOwnResId()] == ownershipReservation::STATUS_RESERVED) {
                    $this->updateICal($ownership_reservation->getOwnResSelectedRoomId());
                }

                $this->em->persist($ownership_reservation);
            }

            $reservation->setGenResTotalInSite($totalPrice);
            $reservation->setGenResSaved(1);
            $reservation->setGenResNights($nights);
            if ($reservation->getGenResStatus() != generalReservation::STATUS_RESERVED) {
                if ($non_available_total > 0 && $non_available_total == $details_total) {
                    $reservation->setGenResStatus(generalReservation::STATUS_NOT_AVAILABLE);
                } else if ($available_total > 0 && $available_total == $details_total) {
                    $reservation->setGenResStatus(generalReservation::STATUS_AVAILABLE);
                } else if ($non_available_total > 0 && $available_total > 0)
                    $reservation->setGenResStatus(generalReservation::STATUS_PARTIAL_AVAILABLE);

                else if ($cancelled_total > 0 && $cancelled_total != $details_total) {
                    $reservation->setGenResStatus(generalReservation::STATUS_PARTIAL_CANCELLED);
                } else if ($outdated_total > 0 && $outdated_total == $details_total)
                    $reservation->setGenResStatus(generalReservation::STATUS_OUTDATED);
            }
            if ($cancelled_total > 0 && $cancelled_total == $details_total) {
                $reservation->setGenResStatus(generalReservation::STATUS_CANCELLED);
            }
            $this->em->persist($reservation);
            $this->em->flush();
            $this->logger->saveLog('Edit entity for ' . $reservation->getCASId(), BackendModuleName::MODULE_RESERVATION);
        }

        return $errors;
    }

    private function processPost($post)
    {
        $errors = array();
        $details_total = 0;
        $available_total = 0;
        $non_available_total = 0;
        $cancelled_total = 0;
        $outdated_total = 0;

        $keys = array_keys($post);

        foreach ($keys as $key) {
            $splitted = explode("_", $key);
            $own_res_id = $splitted[count($splitted) - 1];
            if (strpos($key, 'service_room_price') !== false) {

                if (!is_numeric($post[$key])) {
                    $errors[$key] = 1;
                }
            }
            if (strpos($key, 'service_own_res_status') !== false) {
                $details_total++;
                switch ($post[$key]) {
                    case ownershipReservation::STATUS_NOT_AVAILABLE: $non_available_total++;
                        break;
                    case ownershipReservation::STATUS_AVAILABLE: $available_total++;
                        break;
                    case ownershipReservation::STATUS_CANCELLED: $cancelled_total++;
                        break;
                    case ownershipReservation::STATUS_OUTDATED: $outdated_total++;
                        break;
                }
            }

            if (strpos($key, 'date_from') !== false) {
                $start = explode('/', $post['date_from_' . $own_res_id]);
                $end = explode('/', $post['date_to_' . $own_res_id]);
                $start_timestamp = mktime(0, 0, 0, $start[1], $start[0], $start[2]);
                $end_timestamp = mktime(0, 0, 0, $end[1], $end[0], $end[2]);

                if ($start_timestamp > $end_timestamp) {
                    $errors[$key] = 1;
                    $errors["date_from"] = 1;
                }
            }

            if(strpos($key, 'service_room_count_adults') !== false)
            {
                $adults =  $post['service_room_count_adults_' . $own_res_id];
                $children =  $post['service_room_count_childrens_' . $own_res_id];

                if($adults + $children == 0)
                {
                    $errors["guest_number_" . $own_res_id] = 1;
                    $errors["guest_number"] = 1;
                }
            }
        }

        return array(
            "errors" => $errors,
            "detailsTotal" => $details_total,
            "available" => $available_total,
            "nonAvailable" => $non_available_total,
            "cancelled" => $cancelled_total,
            "outdated" => $outdated_total
        );
    }

    private function updateICal($roomId) {
        try {
            $room = $this->em->getRepository("mycpBundle:room")->find($roomId);
            $this->calendarService->createICalForRoom($room->getRoomId(), $room->getRoomCode());
            return "Se actualizó satisfactoriamente el fichero .ics asociado a esta habitación.";
        } catch (\Exception $e) {
            var_dump( "Ha ocurrido un error mientras se actualizaba el fichero .ics de la habitación. Error: " . $e->getMessage());
            exit;
        }
    }
}
