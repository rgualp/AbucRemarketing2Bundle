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

    public function createDownloadSinglePhotoZipFile($idPhoto, $ownMcpCode, $deleteZip = true)
    {
        $ownPhoto = $this->em->getRepository("mycpBundle:ownershipPhoto")->findOneBy(array("own_pho_photo" => $idPhoto));
        $zipName = $ownMcpCode. "-".$idPhoto.".zip";
        $zipFileName = $this->createZipPhotoFile($zipName, array($ownPhoto));
         return $this->download($zipFileName, $deleteZip);
    }

    public function createDownLoadPhotoZipFile($idOwnership, $ownMyCPCode, $deleteZip = true) {
        $photos = $this->em->getRepository("mycpBundle:ownershipPhoto")->getPhotosByIdOwnership($idOwnership);
        $zipName = $ownMyCPCode . ".zip";
        $zipFileName = $this->createZipPhotoFile($zipName, $photos);

        return $this->download($zipFileName, $deleteZip);
    }

    public function createZipPhotoFile($zipName, $zipPhotoContent) {

        Images::createDirectory($this->zipDirectoryPath);
        $zip = new ZipArchive();

        $filesCount = 0;
        if ($zip->open($this->zipDirectoryPath . $zipName, \ZipArchive::CREATE)) {
            $photoFile = "";
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
            if (file_exists($this->zipDirectoryPath . $zipName)) {
                unlink($this->zipDirectoryPath . $zipName);
            }

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
            if (file_exists($zipFileName)) {
                unlink($zipFileName);
            }
        }

        return $response;
    }

}