<?php

namespace MyCp\FrontEndBundle\Helpers;

use MyCp\mycpBundle\Entity\season;

class ReservationHelper {

    public static function getTotalPrice($em, $service_time, $reservation, $triple_room_charge) {
        $total_price = 0;
        $destination_id = ($reservation->getOwnResGenResId()->getGenResOwnId()->getOwnDestination() != null) ? $reservation->getOwnResGenResId()->getGenResOwnId()->getOwnDestination()->getDesId(): null;
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
        
        if($reservation->getTripleRoomCharged())
            $total_price += $triple_room_charge * (count($array_dates) - 1);
        
        return $total_price;
    }
    
    public static function roomPriceBySeason($room, $seasonType)
    {
        switch($seasonType)
        {
            case \MyCp\mycpBundle\Entity\season::SEASON_TYPE_HIGH: return $room->getRoomPriceUpTo();
            case \MyCp\mycpBundle\Entity\season::SEASON_TYPE_SPECIAL: return ($room->getRoomPriceSpecial() != null && $room->getRoomPriceSpecial() > 0) ? $room->getRoomPriceSpecial(): $room->getRoomPriceUpTo();
            default: return $room->getRoomPriceDownTo();
        }
    }
    
    public static function reservationPriceBySeason($reservation, $seasonType)
    {
        switch($seasonType)
        {
            case \MyCp\mycpBundle\Entity\season::SEASON_TYPE_HIGH: return $reservation->getOwnResRoomPriceUp();
            case \MyCp\mycpBundle\Entity\season::SEASON_TYPE_SPECIAL: return ($reservation->getOwnResRoomPriceSpecial() != null && $reservation->getOwnResRoomPriceSpecial() > 0) ? $reservation->getOwnResRoomPriceSpecial(): $reservation->getOwnResRoomPriceUp();
            default: return $reservation->getOwnResRoomPriceDown();
        }
    }

}

?>
