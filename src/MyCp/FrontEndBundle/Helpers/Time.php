<?php
namespace MyCp\FrontEndBundle\Helpers;
use \MyCp\mycpBundle\Entity\season;

class Time
{
    public static function datesBetween($startdate, $enddate, $format=null){

        (is_int($startdate)) ? 1 : $startdate = strtotime($startdate);
        (is_int($enddate)) ? 1 : $enddate = strtotime($enddate);

        if($startdate > $enddate){
            return false; //The end date is before start date
        }

        while($startdate <= $enddate){
            $arr[] = ($format) ? date($format, $startdate) : $startdate;
            $startdate = strtotime("+1 day", $startdate);
        }
        return $arr;
    }

    public static function seasonTypeByDate($seasons,$date_timestamp)
    {
        foreach($seasons as $season)
        {
            if($season->getSeasonStartDate()->getTimestamp() <= $date_timestamp && $season->getSeasonEndDate()->getTimestamp() >= $date_timestamp)
                return $season->getSeasonType();
        }
        return season::SEASON_TYPE_LOW;
    }

    public static function seasonByDate($seasons,$date_timestamp)
    {
        foreach($seasons as $season)
        {
            if($season->getSeasonStartDate()->getTimestamp() <= $date_timestamp && $season->getSeasonEndDate()->getTimestamp() >= $date_timestamp)
                switch($season->getSeasonType()){
                  case season::SEASON_TYPE_HIGH: return "top";
                  case season::SEASON_TYPE_SPECIAL: return "special";
                  default: return "down";
                }
        }
        return 'down';
    }

}
