<?php

namespace MyCp\mycpBundle\Command;

use MyCp\mycpBundle\Helpers\FileIO;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\Output;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use MyCp\mycpBundle\Entity\mailList;

/*
 * This command must run every day
 */

class ArrangePhotosCommand extends ContainerAwareCommand {

    protected function configure() {
        $this
                ->setName('mycp:arrange_photos')
                ->setDefinition(array())
                ->setDescription('Arrange photos in accommodations');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $container = $this->getContainer();
        $em = $container->get('doctrine')->getManager();
        $dir_ownership = $container->getParameter('ownership.dir.photos');
        $dir_ownership_thumbs = $container->getParameter('ownership.dir.thumbnails');

        $output->writeln(date(DATE_W3C) . ': Starting arrange photos command...');

        $provinces = $em->getRepository("mycpBundle:province")->findAll();
        $newPathToPhoto = "";

        foreach($provinces as $prov)
        {
            FileIO::createDirectoryIfNotExist($dir_ownership.$prov->getProvCode());
            FileIO::createDirectoryIfNotExist($dir_ownership_thumbs."/".$prov->getProvCode());
            FileIO::createDirectoryIfNotExist($dir_ownership."/originals/".$prov->getProvCode());
            $ownPhotos = $this->getAccommodations($prov->getProvId());
            $currentOwnId = 0;

            foreach($ownPhotos as $ownPhoto)
            {
                $photo = $ownPhoto->getOwnPhoPhoto();
                if($currentOwnId != $ownPhoto->getOwnPhoOwn()->getOwnId())
                {
                    $currentOwnId = $ownPhoto->getOwnPhoOwn()->getOwnId();
                    $newPathToPhoto = $prov->getProvCode()."/".$ownPhoto->getOwnPhoOwn()->getOwnMcpCode()."/";
                    FileIO::createDirectoryIfNotExist($dir_ownership.$newPathToPhoto);
                }

                FileIO::move($dir_ownership.$ownPhoto->getOwnPhoPhoto()->getPhoName(), $dir_ownership.$newPathToPhoto.$photo->getPhoName());
                FileIO::move($dir_ownership.$ownPhoto->getOwnPhoPhoto()->getPhoName(), $dir_ownership.$newPathToPhoto.$photo->getPhoName());
                FileIO::move($dir_ownership."originals/".$ownPhoto->getOwnPhoPhoto()->getPhoName(), $dir_ownership."originals/".$newPathToPhoto.$photo->getPhoName());

                $output->writeln($newPathToPhoto.$photo->getPhoName());


                $photo->setPhoName($newPathToPhoto.$photo->getPhoName());

                $em->persist($photo);

            }

            $em->flush();



        }



        $output->writeln('Operation completed!!!');
        return 0;
    }

    private function getAccommodations($provId)
    {
        $container = $this->getContainer();
        $em = $container->get('doctrine')->getManager();

        return $em->createQueryBuilder()
            ->from("mycpBundle:ownershipPhoto", "op")
            ->select("op")
            ->join("op.own_pho_own", "o")
            ->where("o.own_address_province = :provId")
            ->orderBy("o.own_id")
            ->setParameter("provId", $provId)->getQuery()->getResult();
    }

}
