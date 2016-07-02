<?php

namespace MyCp\mycpBundle\Command;

use Doctrine\ORM\Query\Expr;
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
                ->addOption('accommodations-only', null, InputOption::VALUE_NONE, 'Solo fotos de los alojamientos')
                ->addOption('destinations-only', null, InputOption::VALUE_NONE, 'Solo fotos de los detinos')
                ->addOption('all', null, InputOption::VALUE_NONE, 'Todas las fotos')
                ->setDescription('Arrange photos in accommodations');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $accommodationsOnly= $input->getOption('accommodations-only');
        $destinationsOnly= $input->getOption('destinations-only');
        $allPhotos= $input->getOption('all');

        $container = $this->getContainer();
        $em = $container->get('doctrine')->getManager();

        $output->writeln(date(DATE_W3C) . ': Starting arrange photos command...');

        $provinces = $em->getRepository("mycpBundle:province")->findAll();
        $newPathToPhoto = "";

        foreach($provinces as $prov)
        {
            if($accommodationsOnly || $allPhotos){
                $dir_ownership = $container->getParameter('ownership.dir.photos');
                $dir_ownership_thumbs = $container->getParameter('ownership.dir.thumbnails');

                FileIO::createDirectoryIfNotExist($dir_ownership.$prov->getProvCode());
                FileIO::createDirectoryIfNotExist($dir_ownership_thumbs.$prov->getProvCode());
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
                        FileIO::createDirectoryIfNotExist($dir_ownership."/originals/".$newPathToPhoto);
                        FileIO::createDirectoryIfNotExist($dir_ownership_thumbs.$newPathToPhoto);
                    }

                    FileIO::move($dir_ownership.$ownPhoto->getOwnPhoPhoto()->getPhoName(), $dir_ownership.$newPathToPhoto.$photo->getPhoName());
                    FileIO::move($dir_ownership_thumbs.$ownPhoto->getOwnPhoPhoto()->getPhoName(), $dir_ownership_thumbs.$newPathToPhoto.$photo->getPhoName());
                    FileIO::move($dir_ownership."originals/".$ownPhoto->getOwnPhoPhoto()->getPhoName(), $dir_ownership."originals/".$newPathToPhoto.$photo->getPhoName());

                    $output->writeln($newPathToPhoto.$photo->getPhoName());

                    $photo->setPhoNotes($photo->getPhoName());
                    $photo->setPhoName($newPathToPhoto.$photo->getPhoName());

                    $em->persist($photo);
                    $em->flush();
                }
            }

            if($destinationsOnly || $allPhotos){
                $dir_destination = $container->getParameter('destination.dir.photos');
                $dir_destination_thumbs = $container->getParameter('destination.dir.thumbnails');

                FileIO::createDirectoryIfNotExist($dir_destination.$prov->getProvCode());
                FileIO::createDirectoryIfNotExist($dir_destination_thumbs.$prov->getProvCode());

                $desPhotos = $this->getDestinations($prov->getProvId());
                $currentDesId = 0;

                foreach($desPhotos as $desPhoto)
                {
                    $photo = $desPhoto->getDesPhoPhoto();
                    if($currentDesId != $desPhoto->getDesPhoDestination()->getDesId())
                    {
                        $currentDesId = $desPhoto->getDesPhoDestination()->getDesId();
                        $newPathToPhoto = $prov->getProvCode()."/";
                        FileIO::createDirectoryIfNotExist($dir_destination.$newPathToPhoto);
                        FileIO::createDirectoryIfNotExist($dir_destination_thumbs.$newPathToPhoto);
                    }

                    FileIO::move($dir_destination.$desPhoto->getOwnPhoPhoto()->getPhoName(), $dir_destination.$newPathToPhoto.$photo->getPhoName());
                    FileIO::move($dir_destination_thumbs.$desPhoto->getOwnPhoPhoto()->getPhoName(), $dir_destination_thumbs.$newPathToPhoto.$photo->getPhoName());

                    $output->writeln($newPathToPhoto.$photo->getPhoName());

                    $photo->setPhoNotes($photo->getPhoName());
                    $photo->setPhoName($newPathToPhoto.$photo->getPhoName());

                    $em->persist($photo);
                    $em->flush();
                }
            }


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
            ->join("op.own_pho_photo", "p")
            ->where("o.own_address_province = :provId")
            ->andWhere("p.pho_notes is null")
            ->orderBy("o.own_id")
            ->setParameter("provId", $provId)->getQuery()->getResult();
    }

    private function getDestinations($provId)
    {
        $container = $this->getContainer();
        $em = $container->get('doctrine')->getManager();

        return $em->createQueryBuilder()
            ->from("mycpBundle:destinationPhoto", "dp")
            ->select("distinct dp")
            ->join("dp.des_pho_destination", "d")
            ->innerJoin('d.locations', 'l')
            ->where("(SELECT min(prov.prov_id) FROM mycpBundle:destinationLocation loc JOIN loc.des_loc_province prov JOIN loc.des_loc_destination d1 where d1.des_id = d.des_id ) = :provId")
            ->orderBy("d.des_id")
            ->setParameter("provId", $provId)->getQuery()->getResult();
    }

}
