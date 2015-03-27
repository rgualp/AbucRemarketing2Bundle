<?php

namespace MyCp\mycpBundle\Helpers;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use \ZipArchive;

class ZipService {

    private $em;
    private $container;
    /**
     * @var string
     */
    private $zipDirectoryPath;
    private $photoDirectoryPath;
    private $originalDirectoryPath;

    public function __construct($entity_manager, $container, $zipDirectoryPath, $photoDirectoryPath, $originalDirectoryPath) {
        $this->em = $entity_manager;
        $this->container = $container;
        $this->zipDirectoryPath = $zipDirectoryPath;
        $this->photoDirectoryPath = $photoDirectoryPath;
        $this->originalDirectoryPath = $originalDirectoryPath;

    }

    public function createDownLoadPhotoZipFile($idOwnership, $ownMyCPCode, $deleteZip = true) {
        $zipFileName = $this->createZipFile($idOwnership, $ownMyCPCode);

        if(!$zipFileName)
            return null;

        $content = file_get_contents($zipFileName);
        $response = new Response(
                $content, 200, array(
            'Content-Type' => 'application/zip',
            'Content-Disposition' => 'attachment; filename="' . basename($zipFileName) . '"'
                )
        );

        if ($deleteZip) {
            //Deleting the Zip File
            if (file_exists($zipFileName)) {
                unlink($zipFileName);
            }
        }

        return $response;
    }

    public function createZipFile($idOwnership, $ownMyCPCode)
    {
        $photos = $this->em->getRepository("mycpBundle:ownershipPhoto")->getPhotosByIdOwnership($idOwnership);

        Images::createDirectory($this->zipDirectoryPath);
        $zip = new ZipArchive();
        $zipName = $ownMyCPCode . ".zip";
        $filesCount = 0;
        if ($zip->open($this->zipDirectoryPath . $zipName, \ZipArchive::CREATE)) {
            $photoFile = "";
            foreach ($photos as $ownPhoto) {
                if (file_exists(realpath($this->originalDirectoryPath . $ownPhoto->getOwnPhoPhoto()->getPhoName())))
                    $photoFile = $this->originalDirectoryPath . $ownPhoto->getOwnPhoPhoto()->getPhoName();
                else if (file_exists(realpath($this->photoDirectoryPath . $ownPhoto->getOwnPhoPhoto()->getPhoName())))
                    $photoFile = $this->photoDirectoryPath . $ownPhoto->getOwnPhoPhoto()->getPhoName();

                if ($photoFile != "")
                {
                    $zip->addFromString($ownPhoto->getOwnPhoPhoto()->getPhoName(), file_get_contents($photoFile));
                    $filesCount++;
                }
            }
        }
        $zip->close();

        if ($filesCount===0) {
            //Deleting the Zip File
            if (file_exists($this->zipDirectoryPath . $zipName)) {
                unlink($this->zipDirectoryPath . $zipName);
            }
        }
        else return null;

        return $this->zipDirectoryPath . $zipName;
    }

}