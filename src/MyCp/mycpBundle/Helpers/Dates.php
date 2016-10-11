<?php

/**
 * Description of Utils
 *
 * @author DarthDaniel
 */

namespace MyCp\mycpBundle\Helpers;

class Dates {

    public static function createFromString($date_string, $date_separator = '/', $month_position = 0) {
        try {
            $date_array = explode($date_separator, $date_string);
            if($month_position == 0) {
                $month = ltrim($date_array[0], "0");
                $day = ltrim($date_array[1], "0");
                return new \DateTime($day . '/' . $month . '/' . $date_array[2]);
            }
            else {
                $month = ltrim($date_array[1], "0");
                $day = ltrim($date_array[0], "0");
                return new \DateTime($day . '/' . $month . '/' . $date_array[2]);
            }
        } catch (\Exception $e) {
            return new \DateTime();
        }
    }

    public static function createDateFromString($dateString,$date_separator = '/', $month_position = 0){
        $date_array = explode($date_separator, $dateString);
        $month = ($month_position == 0) ? ltrim($date_array[0], "0") : ltrim($date_array[1], "0");
        $day = ($month_position == 0) ? ltrim($date_array[1], "0") : ltrim($date_array[0], "0");

        $timeStamp = mktime(0, 0, 0, $month, $day, $date_array[2]);

        $date = new \DateTime();
        $date->setTimestamp($timeStamp);

        return $date;
    }
    
    public static function createForQuery($date_string, $format_string)
    {
        $date = \DateTime::createFromFormat($format_string, $date_string);
       
        return ($date != null) ? $date->format("Y-m-d") : null;
    }

}

?>
