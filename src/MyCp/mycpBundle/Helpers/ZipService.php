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

    public function createDownLoadPhotoZipFile($idOwnership, $ownMyCPCode) {
        $photos = $this->em->getRepository("mycpBundle:ownershipPhoto")->getPhotosByIdOwnership($idOwnership);
        $pathToZip = $this->container->getParameter('ownership.dir.photos.zips');
        $pathToPhotos = $this->container->getParameter('ownership.dir.photos');
        $pathToOriginalsPhotos = $this->container->getParameter('ownership.dir.photos.originals');

        Images::createDirectory($pathToZip);
        $zip = new ZipArchive();
        $zipName = $ownMyCPCode . ".zip";
        if ($zip->open($pathToZip . $zipName, \ZipArchive::CREATE)) {
            $photoFile = "";
            foreach ($photos as $ownPhoto) {
                if (file_exists(realpath($pathToOriginalsPhotos . $ownPhoto->getOwnPhoPhoto()->getPhoName())))
                    $photoFile = $pathToOriginalsPhotos . $ownPhoto->getOwnPhoPhoto()->getPhoName();
                else if (file_exists(realpath($pathToPhotos . $ownPhoto->getOwnPhoPhoto()->getPhoName())))
                    $photoFile = $pathToPhotos . $ownPhoto->getOwnPhoPhoto()->getPhoName();

                if ($photoFile != "")
                    $zip->addFromString($ownPhoto->getOwnPhoPhoto()->getPhoName(), file_get_contents($photoFile));
            }
        }
        $zip->close();

        $content = file_get_contents($pathToZip . $zipName);
        $response = new Response(
                $content, 200, array(
            'Content-Type' => 'application/zip',
            'Content-Disposition' => 'attachment; filename="' . $zipName . '"'
                )
        );

        //Deleting the Zip File
        if (file_exists($pathToZip . $zipName)) {
            unlink($pathToZip . $zipName);
        }

        return $response;
    }

}