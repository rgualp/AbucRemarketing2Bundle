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
    const RESERVATION_LODGING_MODULE = 4;
    const COMMENT = 5;
    const COMMENT_LODGING_MODULE = 6;
    const DESTINATION = 7;
    const FAQ = 8;
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

    const COMMENT_ACCOMMODATION_CODE_ASC = 51;
    const COMMENT_ACCOMMODATION_CODE_DESC = 52;
    const COMMENT_DATE = 53;
    const COMMENT_USER_NAME_ASC = 54;
    const COMMENT_USER_NAME_DESC = 55;
    const COMMENT_RATING = 56;

    const DESTINATION_NAME_ASC = 61;
    const DESTINATION_NAME_DESC = 62;
    const DESTINATION_PROVINCE = 63;
    const DESTINATION_MUNICIPALITY = 64;
    const DESTINATION_CREATION_DATE = 65;

    const FAQ_ORDER = 71;
    const FAQ_QUESTION_ASC = 72;
    const FAQ_QUESTION_DESC = 73;
    const FAQ_CREATION_DATE = 74;

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
            case ElementToOrder::RESERVATION:
                return array(array(self::RESERVATION_NUMBER, "Número de reserva"),
                             array(self::RESERVATION_DATE, "Fecha reserva"),
                             array(self::RESERVATION_DATE_ARRIVE, "Fecha llegada"),
                             array(self::RESERVATION_PRICE_TOTAL, "Precio total"),
                             array(self::RESERVATION_STATUS, "Estado de reserva"),
                             array(self::RESERVATION_ACCOMMODATION_CODE, "Código de la propiedad"));
            case ElementToOrder::RESERVATION_LODGING_MODULE:
                return array(array(self::RESERVATION_NUMBER, "Número de reserva"),
                             array(self::RESERVATION_DATE, "Fecha reserva"),
                             array(self::RESERVATION_DATE_ARRIVE, "Fecha llegada"),
                             array(self::RESERVATION_PRICE_TOTAL, "Precio total"),
                             array(self::RESERVATION_STATUS, "Estado de reserva"));
           case ElementToOrder::COMMENT:
                return array(array(self::COMMENT_ACCOMMODATION_CODE_ASC, "Código Propiedad (A-Z)"),
                             array(self::COMMENT_ACCOMMODATION_CODE_DESC, "Código Propiedad (Z-A)"),
                             array(self::COMMENT_DATE, "Fecha comentario"),
                             array(self::COMMENT_USER_NAME_ASC, "Nombre cliente (A-Z)"),
                             array(self::COMMENT_USER_NAME_DESC, "Nombre cliente (Z-A)"),
                             array(self::COMMENT_RATING, "Puntuación otorgada"));
           case ElementToOrder::COMMENT_LODGING_MODULE:
                return array(array(self::COMMENT_DATE, "Fecha comentario"),
                             array(self::COMMENT_USER_NAME_ASC, "Nombre cliente (A-Z)"),
                             array(self::COMMENT_USER_NAME_DESC, "Nombre cliente (Z-A)"),
                             array(self::COMMENT_RATING, "Puntuación otorgada"));
           case ElementToOrder::DESTINATION:
                return array(array(self::DESTINATION_NAME_ASC, "Nombre (A-Z)"),
                             array(self::DESTINATION_NAME_DESC, "Nombre (Z-A)"),
                             array(self::DESTINATION_PROVINCE, "Provincia"),
                             array(self::DESTINATION_MUNICIPALITY, "Municipio"),
                             array(self::DESTINATION_CREATION_DATE, "Fecha creación"));
           case ElementToOrder::FAQ:
                return array(array(self::FAQ_ORDER, "Orden de aparición"),
                             array(self::FAQ_QUESTION_ASC, "Pregunta (A-Z)"),
                             array(self::FAQ_QUESTION_DESC, "Pregunta (Z-A)"),
                             array(self::FAQ_CREATION_DATE, "Fecha creación"));
        }
    }

}

?>
