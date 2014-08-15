<?php

/**
 * Description of Utils
 *
 * @author DarthDaniel
 */

namespace MyCp\mycpBundle\Helpers;

class Dates {

    public static function createFromString($date_string, $date_separator = '/') {
        try {
            $date_array = explode($date_separator, $date_string);
            return new \DateTime($date_array[1] . '/' . $date_array[0] . '/' . $date_array[2]);
        } catch (\Exception $e) {
            return new \DateTime();
        }
    }

}

?>
