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
    const SEARCHER = 9;
    const AWARD_ACCOMMODATION = 10;
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

    const DESTINATION_NAME_ASC = 71;
    const DESTINATION_NAME_DESC = 72;
    const DESTINATION_PROVINCE = 73;
    const DESTINATION_MUNICIPALITY = 74;
    const DESTINATION_CREATION_DATE = 75;

    const FAQ_ORDER = 81;
    const FAQ_QUESTION_ASC = 82;
    const FAQ_QUESTION_DESC = 83;
    const FAQ_CREATION_DATE = 84;

    const SEARCHER_PRICE_LOW_HIGH = 91;
    const SEARCHER_PRICE_HIGH_LOW = 92;
    const SEARCHER_BEST_VALUED = 93;
    const SEARCHER_WORST_VALUED = 94;
    const SEARCHER_A_Z = 95;
    const SEARCHER_Z_A = 96;
    const SEARCHER_RESERVATIONS_HIGH_LOW = 97;
    const SEARCHER_RESERVATIONS_LOW_HIGH = 98;

    const AWARD_ACCOMMODATION_CODE = 99;
    const AWARD_ACCOMMODATION_RANKING = 100;

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
           case ElementToOrder::SEARCHER:
                return array(array(self::SEARCHER_PRICE_LOW_HIGH, "PRICE_LOW_HIGH_ORDER_BY"),
                             array(self::SEARCHER_PRICE_HIGH_LOW, "PRICE_HIGH_LOW_ORDER_BY"),
                             array(self::SEARCHER_BEST_VALUED, "BEST_VALUED_ORDER_BY"),
                             array(self::SEARCHER_WORST_VALUED, "WORST_VALUED_ORDER_BY"),
                             array(self::SEARCHER_A_Z, "A_Z_ORDER_BY"),
                             array(self::SEARCHER_Z_A, "Z_A_ORDER_BY"),
                             array(self::SEARCHER_RESERVATIONS_HIGH_LOW, "RESERVATIONS_HIGH_LOW_ORDERBY"),
                             array(self::SEARCHER_RESERVATIONS_LOW_HIGH, "RESERVATIONS_LOW_HIGH_ORDERBY"));
            case ElementToOrder::AWARD_ACCOMMODATION:
                return array(array(self::AWARD_ACCOMMODATION_CODE, "Código del alojamiento"),
                    array(self::AWARD_ACCOMMODATION_RANKING, "Mayor Ranking"));
        }
    }

}

?>
