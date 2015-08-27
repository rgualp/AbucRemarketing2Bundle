<?php

/**
 * Description of AccommodationExcelReader
 *
 * @author Yanet Morales
 */

namespace MyCp\mycpBundle\Helpers;

use Doctrine\ORM\EntityManager;
use MyCp\mycpBundle\Entity\batchType;
use MyCp\mycpBundle\Entity\ownership;
use MyCp\mycpBundle\Entity\ownershipDescriptionLang;
use MyCp\mycpBundle\Entity\ownershipKeywordLang;
use MyCp\mycpBundle\Entity\ownershipStatus;
use Doctrine\Common\Collections\ArrayCollection;
use MyCp\mycpBundle\Entity\room;

class FileIO  {

    public static function createDirectoryIfNotExist($dirName)
    {
        if (!is_dir($dirName)) {
            mkdir($dirName, 0755, true);
        }
    }

    public static function deleteFile($completeFileName)
    {
        if (file_exists($completeFileName)) {
            unlink($completeFileName);
        }
    }

    public static function deleteDirectory($dirName)
    {
        if (is_dir($dirName))
            rmdir($dirName);
    }

    public static function getDatedFileName($preffixName, $fileExtension)
    {
        $date =  new \DateTime();
        $date = $date->format("Ymd");
        return $preffixName."_".$date.$fileExtension;

    }

    public static function getFilesInDirectory($dir)
    {
        $files = array();

        if (is_dir($dir)) {
            if ($dh = opendir($dir)) {
                while (($file = readdir($dh)) !== false) {
                    if($file != "." && $file != "..")
                        array_push($files, $file);
                }
                closedir($dh);
            }
        }
        return $files;
    }
}

?>
