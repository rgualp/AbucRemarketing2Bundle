<?php

/**
 * Description of OrderByHelper
 *
 * @author Yanet
 */

namespace MyCp\mycpBundle\Helpers;

class ElementToOrder
{
    const CHECKIN = 1;
    const RESERVATION = 2;
}

class OrderByHelper {

    const DEFAULT_ORDER_BY = 0;
    const CHECKIN_ORDER_BY_ACCOMMODATION_CODE = 1;
    const CHECKIN_ORDER_BY_ACCOMMODATION_PROVINCE = 2;
    const CHECKIN_ORDER_BY_RESERVATION_CASCODE = 3;
    const CHECKIN_ORDER_BY_RESERVATION_RESERVED_DATE = 4;

    public static function getOrdersFor($elementToOrder)
    {
        switch($elementToOrder)
        {
            case ElementToOrder::CHECKIN:
                                return array(array(self::CHECKIN_ORDER_BY_ACCOMMODATION_CODE, "Propiedad"),
                                             array(self::CHECKIN_ORDER_BY_ACCOMMODATION_PROVINCE, "Provincia"),
                                             array(self::CHECKIN_ORDER_BY_RESERVATION_CASCODE, "Código Reservación"),
                                             array(self::CHECKIN_ORDER_BY_RESERVATION_RESERVED_DATE, "Fecha Reservación"));
        }
    }

}

?>
