<?php

namespace MyCp\FrontEndBundle\Helpers;

use MyCp\mycpBundle\Entity\season;

class ReservationHelper {

    public static function getTotalPrice($em, $service_time, $reservation) {
        $total_price = 0;
        $seasons = $em->getRepository("mycpBundle:season")->getSeasons($reservation->getOwnResReservationFromDate(), $reservation->getOwnResReservationToDate());
        $array_dates = $service_time->datesBetween($reservation->getOwnResReservationFromDate()->getTimestamp(), $reservation->getOwnResReservationToDate()->getTimestamp());

        if ($reservation->getOwnResNightPrice() == 0) {
            for ($i = 0; $i < count($array_dates) - 1; $i++) {
                switch ($service_time->seasonTypeByDate($seasons, $array_dates[$i])) {
                    case season::SEASON_TYPE_HIGH: $total_price += $reservation->getOwnResRoomPriceUp();
                        break;
                    case season::SEASON_TYPE_SPECIAL: $total_price += $reservation->getOwnResRoomPriceSpecial();
                        break;
                    default: $total_price += $reservation->getOwnResRoomPriceDown();
                        break;
                }
            }
        } else {
            $total_price += $reservation->getOwnResNightPrice() * (count($array_dates) - 1);
        }

        return $total_price;
    }

}

?>
