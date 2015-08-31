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
use Symfony\Component\HttpFoundation\Response;

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

    public static function download($filePath, $fileName)
    {
        $file_info = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($file_info, $filePath.$fileName);
        finfo_close($file_info);

        $response = new Response();
        $response->headers->set('Cache-Control', 'private');
        $response->headers->set('Content-type', $mime_type);
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $fileName . '"');
        $response->headers->set('Content-length', filesize($filePath.$fileName));
        $response->sendHeaders();

        $response->setContent(readfile($filePath.$fileName));

        return $response;
    }
}

?>
