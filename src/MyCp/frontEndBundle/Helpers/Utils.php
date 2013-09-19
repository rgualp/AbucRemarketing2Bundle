<?php
/**
 * Description of Utils
 *
 * @author DarthDaniel
 */
namespace MyCp\frontEndBundle\Helpers;
use Doctrine\Common\Util\Debug;

class Utils {

    public static function debug($var) {
        echo "<pre>";
        Debug::dump($var);
        echo "</pre>";
    }

}

?>
