<?php
/**
 * Description of Utils
 *
 * @author DarthDaniel
 */
namespace MyCp\FrontEndBundle\Helpers;
use Doctrine\Common\Util\Debug;

class Utils {

    public static function debug($var) {
        echo "<pre>";
        Debug::dump($var);
        echo "</pre>";
    }

    public static function urlNormalize($text)
    {
        $furl=str_replace("-", "--", $text);
        $furl=str_replace(" ", "-", $furl);
        $furl=str_replace("á", "a", $furl);
        $furl=str_replace("é", "e", $furl);
        $furl=str_replace("í", "i", $furl);
        $furl=str_replace("ó", "o", $furl);
        $furl=str_replace("ú", "u", $furl);
        $furl=str_replace("ü", "u", $furl);
        $furl=str_replace("ñ", "_nn_", $furl);
        $furl=str_replace("Á", "A", $furl);
        $furl=str_replace("É", "E", $furl);
        $furl=str_replace("Í", "I", $furl);
        $furl=str_replace("Ó", "O", $furl);
        $furl=str_replace("Ú", "U", $furl);
        $furl=str_replace("Ñ", "_nn_", $furl);
        $furl = strtolower ($furl);
        $furl=str_replace("_nn_", "ñ", $furl);

        return $furl;
    }

    public static function getTextFromNormalized($text)
    {
        $furl=str_replace("-", " ", $text);
        $furl=str_replace("  ", "-", $furl);

        return $furl;
    }
}

?>
