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
    const CLIENT = 3;
}

class OrderByHelper {

    const DEFAULT_ORDER_BY = 0;
    const CHECKIN_ORDER_BY_ACCOMMODATION_CODE = 11;
    const CHECKIN_ORDER_BY_ACCOMMODATION_PROVINCE = 12;
    const CHECKIN_ORDER_BY_RESERVATION_CASCODE = 13;
    const CHECKIN_ORDER_BY_RESERVATION_RESERVED_DATE = 14;

    const RESERVATION_NUMBER = 21;
    const RESERVATION_DATE = 22;
    const RESERVATION_DATE_ARRIVE = 23;
    const RESERVATION_PRICE_TOTAL = 24;
    const RESERVATION_ACCOMMODATION_CODE = 25;
    const RESERVATION_STATUS = 26;

    const CLIENT_NAME = 31;
    const CLIENT_CITY = 32;
    const CLIENT_EMAIL = 33;
    const CLIENT_COUNTRY = 34;
    const CLIENT_RESERVATIONS_TOTAL = 35;

    public static function getOrdersFor($elementToOrder)
    {
        switch($elementToOrder)
        {
            case ElementToOrder::CHECKIN:
                                return array(array(self::CHECKIN_ORDER_BY_ACCOMMODATION_CODE, "Propiedad"),
                                             array(self::CHECKIN_ORDER_BY_ACCOMMODATION_PROVINCE, "Provincia"),
                                             array(self::CHECKIN_ORDER_BY_RESERVATION_CASCODE, "Código Reservación"),
                                             array(self::CHECKIN_ORDER_BY_RESERVATION_RESERVED_DATE, "Fecha Reservación"));
            case ElementToOrder::CLIENT:
                                return array(array(self::CLIENT_RESERVATIONS_TOTAL, "Total de Reservas"),
                                             array(self::CLIENT_NAME, "Nombre del cliente"),
                                             array(self::CLIENT_CITY, "Ciudad del cliente"),
                                             array(self::CLIENT_EMAIL, "Correo del cliente"),
                                             array(self::CLIENT_COUNTRY, "País del cliente"));
            case ElementToOrder::CHECKIN:
                                return array(array(self::CHECKIN_ORDER_BY_ACCOMMODATION_CODE, "Propiedad"),
                                             array(self::CHECKIN_ORDER_BY_ACCOMMODATION_PROVINCE, "Provincia"),
                                             array(self::CHECKIN_ORDER_BY_RESERVATION_CASCODE, "Código Reservación"),
                                             array(self::CHECKIN_ORDER_BY_RESERVATION_RESERVED_DATE, "Fecha Reservación"));
        }
    }

}

?>
