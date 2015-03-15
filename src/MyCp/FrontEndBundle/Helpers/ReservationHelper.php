<?php

namespace MyCp\FrontEndBundle\Helpers;

use MyCp\mycpBundle\Entity\season;

class ReservationHelper {

    public static function getTotalPrice($em, $service_time, $reservation, $triple_room_charge) {
        $total_price = 0;
        $destination_id = ($reservation->getOwnResGenResId()->getGenResOwnId()->getOwnDestination() != null) ? $reservation->getOwnResGenResId()->getGenResOwnId()->getOwnDestination()->getDesId() : null;
        $seasons = $em->getRepository("mycpBundle:season")->getSeasons($reservation->getOwnResReservationFromDate(), $reservation->getOwnResReservationToDate(), $destination_id);
        $array_dates = $service_time->datesBetween($reservation->getOwnResReservationFromDate()->getTimestamp(), $reservation->getOwnResReservationToDate()->getTimestamp());

        if ($reservation->getOwnResNightPrice() == 0) {
            for ($i = 0; $i < count($array_dates) - 1; $i++) {
                $seasonType = $service_time->seasonTypeByDate($seasons, $array_dates[$i]);
                $total_price += ReservationHelper::reservationPriceBySeason($reservation, $seasonType);
            }
        } else {
            $total_price += $reservation->getOwnResNightPrice() * (count($array_dates) - 1);
        }

        if ($reservation->getTripleRoomCharged())
            $total_price += $triple_room_charge * (count($array_dates) - 1);

        return $total_price;
    }

    public static function roomPriceBySeason($room, $seasonType) {
        return $room->getPriceBySeasonType($seasonType);
    }

    public static function reservationPriceBySeason($reservation, $seasonType) {
        switch ($seasonType) {
            case \MyCp\mycpBundle\Entity\season::SEASON_TYPE_HIGH: return $reservation->getOwnResRoomPriceUp();
            case \MyCp\mycpBundle\Entity\season::SEASON_TYPE_SPECIAL: return ($reservation->getOwnResRoomPriceSpecial() != null && $reservation->getOwnResRoomPriceSpecial() > 0) ? $reservation->getOwnResRoomPriceSpecial() : $reservation->getOwnResRoomPriceUp();
            default: return $reservation->getOwnResRoomPriceDown();
        }
    }

    public static function sendingEmailToReservationTeamBody($genResId, $em, $controller, $service_time, $request) {
        $generalReservation = $em
                        ->getRepository('mycpBundle:generalReservation')->find($genResId);

        $user = $generalReservation->getGenResUserId();
        $user_tourist = $em->getRepository('mycpBundle:userTourist')->findOneBy(array('user_tourist_user' => $user->getUserId()));

        $ownershipReservations = $em
                ->getRepository('mycpBundle:generalReservation')
                ->getOwnershipReservations($generalReservation);

        $arrayNights = array();
        $roomNums = array();

        foreach ($ownershipReservations as $ownershipReservation) {
            $array_dates = $service_time
                    ->datesBetween(
                    $ownershipReservation->getOwnResReservationFromDate()->getTimestamp(), $ownershipReservation->getOwnResReservationToDate()->getTimestamp()
            );

            array_push($arrayNights, count($array_dates) - 1);

            $room = $em->getRepository("mycpBundle:room")->find($ownershipReservation->getOwnResSelectedRoomId());
            array_push($roomNums, $room->getRoomNum());
        }
        $results = array();
        $results[] = $controller->render('FrontEndBundle:mails:rt_email_check_available.html.twig', array(
            'user' => $user,
            'user_tourist' => $user_tourist,
            'reservations' => $ownershipReservations,
            'nigths' => $arrayNights,
            'comment' => $request->getSession()->get('message_cart'),
            'roomNums' => $roomNums
        ));

        $results[] = "MyCasaParticular Reservas - " . strtoupper($user_tourist->getUserTouristLanguage()->getLangCode());

        return $results;
    }

    public static function sendingEmailToReservationTeam($genResId, $em, $controller, $service_email, $service_time, $request, $toAddress, $fromAddress) {

        $texts = ReservationHelper::sendingEmailToReservationTeamBody($genResId, $em, $controller, $service_time, $request);
        $subject = $texts[1];
        $body = $texts[0];

        $service_email->sendEmail(
                $subject, $fromAddress, $subject, $toAddress, $body
        );
    }

}

?>
