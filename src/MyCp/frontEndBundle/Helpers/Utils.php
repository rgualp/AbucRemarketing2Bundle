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

    public static function url_normalize($text)
    {
        $furl=str_replace(" ", "-", $text);
        $furl=str_replace("á", "a", $furl);
        $furl=str_replace("é", "e", $furl);
        $furl=str_replace("í", "i", $furl);
        $furl=str_replace("ó", "o", $furl);
        $furl=str_replace("ú", "u", $furl);
        $furl=str_replace("ü", "u", $furl);
        $furl=str_replace("ñ", "nn", $furl);
        $furl=str_replace("Á", "A", $furl);
        $furl=str_replace("É", "E", $furl);
        $furl=str_replace("Í", "I", $furl);
        $furl=str_replace("Ó", "O", $furl);
        $furl=str_replace("Ú", "U", $furl);
        //$furl=str_replace("Ñ", "NN", $furl);
        $furl = strtolower ($furl);
        $furl=str_replace("nn", "ñ", $furl);

        return $furl;
    }

}

?>
