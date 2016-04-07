<?php

/**
 * Description of OwnershipStatuses
 *
 * @author Yanet
 */

namespace MyCp\mycpBundle\Helpers;

class BackendModuleName {

    const NO_MODULE = 0;
    const MODULE_DESTINATION = 1;
    const MODULE_FAQS = 2;
    const MODULE_ALBUM = 3;
    const MODULE_OWNERSHIP = 4;
    const MODULE_CURRENCY = 5;
    const MODULE_LANGUAGE = 6;
    const MODULE_RESERVATION = 7;
    const MODULE_USER = 8;
    const MODULE_GENERAL_INFORMATION = 9;
    const MODULE_COMMENT = 10;
    const MODULE_UNAVAILABILITY_DETAILS = 11;
    const MODULE_METATAGS = 12;
    const MODULE_MUNICIPALITY = 13;
    const MODULE_SEASON = 14;
    const MODULE_LODGING_RESERVATION = 15;
    const MODULE_LODGING_COMMENT = 16;
    const MODULE_LODGING_OWNERSHIP = 17;
    const MODULE_LODGING_USER = 18;
    const MODULE_MAIL_LIST = 19;
    const MODULE_BATCH_PROCESS = 20;
    const MODULE_CLIENT_MESSAGES = 21;
    const MODULE_CLIENT_COMMENTS = 22;
    const MODULE_AWARD = 23;

    public static function getModuleName($module_number)
    {
        switch ($module_number) {
            case BackendModuleName::MODULE_DESTINATION: return "Destination";
            case BackendModuleName::MODULE_FAQS: return "FAQ";
            case BackendModuleName::MODULE_ALBUM: return "Album";
            case BackendModuleName::MODULE_OWNERSHIP: return "Accommodation";
            case BackendModuleName::MODULE_CURRENCY: return "Currency";
            case BackendModuleName::MODULE_LANGUAGE: return "Language";
            case BackendModuleName::MODULE_RESERVATION: return "Reservation";
            case BackendModuleName::MODULE_USER: return "User";
            case BackendModuleName::MODULE_GENERAL_INFORMATION: return "General Information";
            case BackendModuleName::MODULE_COMMENT: return "Comment";
            case BackendModuleName::MODULE_UNAVAILABILITY_DETAILS: return "Unavailability Details";
            case BackendModuleName::MODULE_METATAGS: return "Meta Tags";
            case BackendModuleName::MODULE_MUNICIPALITY: return "Municipality";
            case BackendModuleName::MODULE_SEASON: return "Season";
            case BackendModuleName::MODULE_LODGING_RESERVATION: return "Lodging - Reservations";
            case BackendModuleName::MODULE_LODGING_COMMENT: return "Lodging - Comments";
            case BackendModuleName::MODULE_LODGING_OWNERSHIP: return "Lodging - MyCasa";
            case BackendModuleName::MODULE_LODGING_USER: return "Lodging - User Profile";
            case BackendModuleName::MODULE_MAIL_LIST: return "Mail List";
            case BackendModuleName::MODULE_BATCH_PROCESS: return "Batch Process";
            case BackendModuleName::MODULE_CLIENT_MESSAGES: return "Messages to Clients";
            case BackendModuleName::MODULE_CLIENT_COMMENTS: return "Comments of Clients";
            case BackendModuleName::MODULE_AWARD: return "Awards";

            default: return "MyCP";
        }
    }

}

class DataBaseTables {

    const USER = "user";
    const METATAGS = "metatag";
    const MUNICIPALITY = "municipality";
    const DESTINATION = "destination";
    const DESTINATION_CATEGORY = "destinationCategory";
    const DESTINATION_PHOTO = "destinationPhoto";
    const OWNERSHIP = "ownership";
    const OWNERSHIP_PHOTO = "ownershipPhoto";
    const ROOM = "room";
    const BATCH_PROCESS = "batchProcess";
    const UNAVAILABILITY_DETAILS = "unavailabilityDetails";
    const ALBUM_CATEGORY = "albumCategory";
    const ALBUM = "album";
    const ALBUM_PHOTO = "albumPhoto";
    const FAQ_CATEGORY = "faqCategory";
    const FAQ_LANG = "faqLang";
    const FAQ = "faq";
    const INFORMATION = "information";
    const INFORMATION_LANG = "informationLang";
    const COMMENT = "comment";
    const AWARD = "award";
    const CLIENT_COMMENT = "clientComment";
    const MESSAGE = "message";
    const GENERAL_RESERVATION = "generalReservation";
    const ROLE = "role";
    const CURRENCY = "currency";
    const LANGUAGE = "lang";


}
?>
