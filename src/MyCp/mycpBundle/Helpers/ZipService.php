<?php

namespace MyCp\mycpBundle\Helpers;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use \ZipArchive;

class ZipService {

    private $em;
    private $container;

    public function __construct($entity_manager, $container) {
        $this->em = $entity_manager;
        $this->container = $container;
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
        $pathToZip = $this->container->getParameter('ownership.dir.photos.zips');
        $pathToPhotos = $this->container->getParameter('ownership.dir.photos');
        $pathToOriginalsPhotos = $this->container->getParameter('ownership.dir.photos.originals');

        Images::createDirectory($pathToZip);
        $zip = new ZipArchive();
        $zipName = $ownMyCPCode . ".zip";
        $filesCount = 0;
        if ($zip->open($pathToZip . $zipName, \ZipArchive::CREATE)) {
            $photoFile = "";
            foreach ($photos as $ownPhoto) {
                if (file_exists(realpath($pathToOriginalsPhotos . $ownPhoto->getOwnPhoPhoto()->getPhoName())))
                    $photoFile = $pathToOriginalsPhotos . $ownPhoto->getOwnPhoPhoto()->getPhoName();
                else if (file_exists(realpath($pathToPhotos . $ownPhoto->getOwnPhoPhoto()->getPhoName())))
                    $photoFile = $pathToPhotos . $ownPhoto->getOwnPhoPhoto()->getPhoName();

                if ($photoFile != "")
                {
                    $zip->addFromString($ownPhoto->getOwnPhoPhoto()->getPhoName(), file_get_contents(realpath($photoFile)));
                    $filesCount++;
                }
            }
        }
        $zip->close();

        if ($filesCount) {
            //Deleting the Zip File
            if (file_exists(realpath($pathToZip . $zipName))) {
                unlink(realpath($pathToZip . $zipName));
            }
        }
        else return null;

        return realpath($pathToZip . $zipName);
    }

}