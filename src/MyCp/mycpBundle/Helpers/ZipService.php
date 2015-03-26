<?php
namespace MyCp\mycpBundle\Helpers;
use MyCp\mycpBundle\Entity\log;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\HttpFoundation\Request;
use \ZipArchive;


class ZipService
{
    private $em;
    private $container;

    public function __construct($entity_manager, $container)
    {
        $this->em = $entity_manager;
        $this->container = $container;

    }

    public function createDownLoadPhotoZipFile($idOwnership, $ownMyCPCode)
    {
        $photos = $this->em->getRepository("mycpBundle:ownershipPhoto")->findBy(array("own_pho_own", $idOwnership));
        $pathToZip = $this->container->getParameter('ownership.dir.photos.zips');
        $pathToPhotos = $this->container->getParameter('ownership.dir.photos');
        $pathToOriginalsPhotos = $this->container->getParameter('ownership.dir.photos.originals');

        Images::createDirectory($pathToZip);
        $zip = new ZipArchive();
        $zipName = $ownMyCPCode.".zip";
        if($zip->open($pathToZip.$ownMyCPCode.$zipName, \ZipArchive::CREATE))
        {
            $photoFile = "";
            foreach($photos as $ownPhoto)
            {
                if(file_exists(realpath($pathToOriginalsPhotos . $ownPhoto->getOwnPhoPhoto()->getPhoName())))
                        $photoFile = $pathToOriginalsPhotos . $ownPhoto->getOwnPhoPhoto()->getPhoName();
                else
                    $photoFile = $pathToPhotos . $ownPhoto->getOwnPhoPhoto()->getPhoName();


                $zip->addFile($photoFile);
            }
        }

        $response = new Response(file_get_contents($zip->filename));

        $headers = $response->headers->makeDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $zipName);
        $response->headers->set('Content-Disposition', $headers);

        $zip->close();

        return $response;
    }
}