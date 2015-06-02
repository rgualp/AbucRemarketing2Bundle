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
    private $userDirectoryPath;

    public function __construct($entity_manager, $container, $zipDirectoryPath, $photoDirectoryPath, $originalDirectoryPath, $userDirectoryPath) {
        $this->em = $entity_manager;
        $this->container = $container;
        $this->zipDirectoryPath = $zipDirectoryPath;
        $this->photoDirectoryPath = $photoDirectoryPath;
        $this->originalDirectoryPath = $originalDirectoryPath;
        $this->userDirectoryPath = $userDirectoryPath;
    }

    public function createDownloadSinglePhotoZipFile($idPhoto, $ownMcpCode, $deleteZip = true)
    {
        $ownPhoto = $this->em->getRepository("mycpBundle:ownershipPhoto")->findOneBy(array("own_pho_photo" => $idPhoto));
        $zipName = $ownMcpCode. "-".$idPhoto.".zip";
        $zipFileName = $this->createZipPhotoFile($zipName, array($ownPhoto));
         return $this->download($zipFileName, $deleteZip);
    }

    public function createDownLoadPhotoZipFile($idOwnership, $ownMyCPCode, $deleteZip = true) {
        $photos = $this->em->getRepository("mycpBundle:ownershipPhoto")->getPhotosByIdOwnership($idOwnership);
        $ownerPhotos = $this->em->getRepository("mycpBundle:ownershipPhoto")->getOwnerPhoto($idOwnership);
        $zipName = $ownMyCPCode . ".zip";
        $zipFileName = $this->createZipPhotoFile($zipName, $photos, $ownerPhotos);

        return $this->download($zipFileName, $deleteZip);
    }

    public function createZipPhotoFile($zipName, $zipPhotoContent, $additionalPhotoContent) {

        FileIO::createDirectoryIfNotExist($this->zipDirectoryPath);
        $zip = new ZipArchive();

        $filesCount = 0;
        if ($zip->open($this->zipDirectoryPath . $zipName, \ZipArchive::CREATE)) {
            $photoFile = "";

            foreach($additionalPhotoContent as $ownerPhoto)
            {
                $photoFile = "";
                if (file_exists(realpath($this->userDirectoryPath . $ownerPhoto->getOwnOwnerPhoto()->getPhoName()))) {
                    $photoFile = realpath($this->userDirectoryPath . $ownerPhoto->getOwnOwnerPhoto()->getPhoName());
                }

                if ($photoFile != "") {
                    $zip->addFromString("Propietario.jpg", file_get_contents($photoFile));
                    $filesCount++;
                }
            }

            foreach ($zipPhotoContent as $ownPhoto) {

                if (file_exists(realpath($this->originalDirectoryPath . $ownPhoto->getOwnPhoPhoto()->getPhoName())))
                    $photoFile = realpath($this->originalDirectoryPath . $ownPhoto->getOwnPhoPhoto()->getPhoName());
                else if (file_exists(realpath($this->photoDirectoryPath . $ownPhoto->getOwnPhoPhoto()->getPhoName())))
                    $photoFile = realpath($this->photoDirectoryPath . $ownPhoto->getOwnPhoPhoto()->getPhoName());

                if ($photoFile != "") {
                    $zip->addFromString($ownPhoto->getOwnPhoPhoto()->getPhoName(), file_get_contents($photoFile));
                    $filesCount++;
                }
            }
        }
        $zip->close();

        if ($filesCount === 0) {
            //Deleting the Zip File
            FileIO::deleteFile($this->zipDirectoryPath . $zipName);
            return null;
        }

        return $this->zipDirectoryPath . $zipName;
    }

    public function download($zipFileName,$deleteZip)
    {
        if (!$zipFileName)
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
            FileIO::deleteFile($zipFileName);
        }

        return $response;
    }

}