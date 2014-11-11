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
            if($month_position == 0)
            return new \DateTime($date_array[1] . '/' . $date_array[0] . '/' . $date_array[2]);
            else
                return new \DateTime($date_array[0] . '/' . $date_array[1] . '/' . $date_array[2]);
        } catch (\Exception $e) {
            return new \DateTime();
        }
    }
    
    public static function createForQuery($date_string, $format_string)
    {
        $date = \DateTime::createFromFormat($format_string, $date_string);
       
        return ($date != null) ? $date->format("Y-m-d") : null;
    }

}

?>
