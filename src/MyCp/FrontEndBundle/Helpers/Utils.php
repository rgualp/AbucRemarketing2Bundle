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
    
    public static function validateEmail($text)
    {
       return \Swift_Validate::email($text);
    }

    public static function isMobileNumberValid($value){

        return preg_match("/^5[0-9]{7}$/", $value);
    }

    public static function getTextFromNormalized($text)
    {
        $furl=str_replace("-", " ", $text);
        $furl=str_replace("  ", "-", $furl);

        return $furl;
    }

    public static function removeNewlines($text)
    {
        $text = str_replace("\n", "", $text);
        $text = str_replace("\r", "", $text);

        return $text;
    }

   public static function loadFrontendSlides()
   {
       $number = rand(1, 4);
       $folderSliderPath = "bundles/frontend/img/slideshow/".$number."/";
       $slides = glob($folderSliderPath.'*.{jpg,gif,png}', GLOB_BRACE);
       return $slides;
   }
}

?>
