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
        $furl=str_replace("à", "a", $furl);
        $furl=str_replace("è", "e", $furl);
        $furl=str_replace("ì", "i", $furl);
        $furl=str_replace("ò", "o", $furl);
        $furl=str_replace("ù", "u", $furl);
        $furl=str_replace("ü", "u", $furl);
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
        $furl=str_replace("À", "A", $furl);
        $furl=str_replace("È", "E", $furl);
        $furl=str_replace("Ì", "I", $furl);
        $furl=str_replace("Ò", "O", $furl);
        $furl=str_replace("Ù", "U", $furl);
        $furl=str_replace("Ñ", "_nn_", $furl);
        $furl=str_replace("ä", "a", $furl);
        $furl=str_replace("ü", "u", $furl);
        $furl=str_replace("Ü", "U", $furl);
        $furl=str_replace("Ä", "A", $furl);
        $furl=str_replace("Ë", "E", $furl);
        $furl=str_replace("ë", "e", $furl);
        $furl=str_replace("ï", "i", $furl);
        $furl=str_replace("Ï", "I", $furl);
        $furl=str_replace("Ö", "O", $furl);
        $furl=str_replace("ö", "o", $furl);
        $furl = strtolower ($furl);
        $furl=str_replace("_nn_", "ñ", $furl);


        return $furl;
    }

    public static function convert_text($text) {

        $t = $text;

        $specChars = array(
            '!' => '%21',    '"' => '%22', 'ñ' => '%C3%B1',
            '#' => '%23',    '$' => '%24',    '%' => '%25',
            '&' => '%26',    '\'' => '%27',   '(' => '%28',
            ')' => '%29',    '*' => '%2A',    '+' => '%2B',
            ',' => '%2C',    '-' => '%2D',    '.' => '%2E',
            '/' => '%2F',    ':' => '%3A',    ';' => '%3B',
            '<' => '%3C',    '=' => '%3D',    '>' => '%3E',
            '?' => '%3F',    '@' => '%40',    '[' => '%5B',
            '\\' => '%5C',   ']' => '%5D',    '^' => '%5E',
            '_' => '%5F',    '`' => '%60',    '{' => '%7B',
            '|' => '%7C',    '}' => '%7D',    '~' => '%7E',
            ',' => '%E2%80%9A',  ' ' => '%20', '’' => '%E2%80%99'
        );

        foreach ($specChars as $k => $v) {
            $t = str_replace($v, $k, $t);
        }

        return $t;
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
