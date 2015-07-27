<?php

namespace MyCp\FrontEndBundle\Helpers;

use \MyCp\mycpBundle\Entity\season;

class Time {

    public static function nights($startdate, $enddate, $format = null)
    {
        $dates = Time::datesBetween($startdate, $enddate, $format);
        return count($dates) - 1;
    }

    public static function datesBetween($startdate, $enddate, $format = null) {

        (is_int($startdate)) ? 1 : $startdate = strtotime($startdate);
        (is_int($enddate)) ? 1 : $enddate = strtotime($enddate);

        if ($startdate > $enddate) {
            return false; //The end date is before start date
        }

        while ($startdate <= $enddate) {
            $arr[] = ($format) ? date($format, $startdate) : $startdate;
            $startdate = strtotime("+1 day", $startdate);
        }
        return $arr;
    }

    public static function seasonTypeByDate($seasons, $date_timestamp) {
        $season_type = season::SEASON_TYPE_LOW;
        foreach ($seasons as $season) {
            if ($season->getSeasonStartDate()->getTimestamp() <= $date_timestamp && $season->getSeasonEndDate()->getTimestamp() >= $date_timestamp) {
                if ($season_type == season::SEASON_TYPE_LOW ||
                        ($season_type == season::SEASON_TYPE_HIGH && $season->getSeasonType() == season::SEASON_TYPE_SPECIAL))
                    $season_type = $season->getSeasonType();
            }
        }
        return $season_type;
    }

    public static function seasonByDate($seasons, $date_timestamp) {
        $season_type = Time::seasonTypeByDate($seasons, $date_timestamp);
        switch ($season_type) {
            case season::SEASON_TYPE_HIGH: return "top";
            case season::SEASON_TYPE_SPECIAL: return "special";
            default: return "down";
        }
    }

    /**
     * Add a time date string to a time string with certain format
     * @param $timeStringToAdd
     * @param $dateString
     * @param $dateStringFormat
     * @return \DateTime
     */
    public static function add($timeStringToAdd, $dateString, $dateStringFormat)
    {
        $dateAdded = \DateTime::createFromFormat($dateStringFormat, $dateString);
        $dateAddedTimeStamp = $dateAdded->getTimestamp();
        $dateAddedTimeStamp = strtotime($timeStringToAdd, $dateAddedTimeStamp);
        $dateAdded->setTimestamp($dateAddedTimeStamp);

        return $dateAdded->format($dateStringFormat);
    }

    public static function addObj($timeStringToAdd, $date)
    {
        $dateAddedTimeStamp = $date->getTimestamp();
        $dateAddedTimeStamp = strtotime($timeStringToAdd, $dateAddedTimeStamp);
        $date->setTimestamp($dateAddedTimeStamp);

        return $date;
    }
}

