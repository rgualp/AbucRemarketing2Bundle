<?php

namespace MyCp\mycpBundle\Command;

use MyCp\mycpBundle\Helpers\FileIO;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\Output;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use MyCp\mycpBundle\Helpers\Images;

class ImageCommand extends ContainerAwareCommand {

    protected function configure() {
        $this->setName('mycp_task:image_process')
                ->setDefinition(array())
                ->setDescription('Process images of destinations, albums and accomodations')
                ->setHelp(<<<EOT
                Command <info>mycp_task:image_process</info> generate thumbnails for every image in destination, album and ownership folders and resize those images.
                Also, for images in ownership folder, insert a watermark.
EOT
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $container = $this->getContainer();
        $dir_user = $container->getParameter('user.dir.photos');
        $dir_destination = $container->getParameter('destination.dir.photos');
        $dir_destination_photo_size = $container->getParameter('destination.dir.photos.size');
        $dir_destination_thumbs = $container->getParameter('destination.dir.thumbnails');
        $dir_album = $container->getParameter('album.dir.photos');
        $dir_album_photo_size = $container->getParameter('album.dir.photos.size');
        $dir_albums_thumbs = $container->getParameter('album.dir.thumbnails');
        $dir_ownership = $container->getParameter('ownership.dir.photos');
        $dir_ownership_photo_size = $container->getParameter('ownership.dir.photos.size');
        $dir_ownership_thumbs = $container->getParameter('ownership.dir.thumbnails');
        $dir_watermark = $container->getParameter('dir.watermark');
        $thumbnail_size = $container->getParameter('thumbnail.size');

        $output->writeln("Step 1 of 3. Resizing destinations' photos and generating thumbnails...");
        $destinations_photos = Images::getDirectoryImagesContent($dir_destination);
        FileIO::createDirectoryIfNotExist($dir_destination . '/originals');
        //FileIO::createDirectory($dir_destination . '/processed');
        FileIO::createDirectoryIfNotExist($dir_destination_thumbs);
        foreach ($destinations_photos as $d_photo) {
            Images::save($dir_destination . $d_photo, $dir_destination . '/originals/' . $d_photo);
            Images::createThumbnail($dir_destination . '/originals/' . $d_photo, $dir_destination_thumbs . $d_photo, $thumbnail_size);
            Images::resizeDiferentDirectories($dir_destination . '/originals/' . $d_photo, $dir_destination . $d_photo, $dir_destination_photo_size);
        }

        $output->writeln("Step 2 of 3. Resizing albums' photos and generating thumbnails...");
        $albums_photos = Images::getDirectoryImagesContent($dir_album);
        FileIO::createDirectoryIfNotExist($dir_album . '/originals');
        //FileIO::createDirectoryIfNotExist($dir_album . '/processed');
        FileIO::createDirectoryIfNotExist($dir_albums_thumbs);

        foreach ($albums_photos as $a_photo) {
            Images::save($dir_album . $a_photo, $dir_album . '/originals/' . $a_photo);

            Images::createThumbnail($dir_album . '/originals/' . $a_photo, $dir_albums_thumbs . $a_photo, $thumbnail_size);
            Images::resizeDiferentDirectories($dir_album . '/originals/' . $a_photo, $dir_album . $a_photo, $dir_album_photo_size);
        }

        $output->writeln("Step 3 of 3. Resizing ownerships' photos, pasting watermark image and generating thumbnails...");
        $own_photos = Images::getDirectoryImagesContent($dir_ownership);
        FileIO::createDirectoryIfNotExist($dir_ownership . '/originals');
        //FileIO::createDirectoryIfNotExist($dir_ownership . '/processed');
        FileIO::createDirectoryIfNotExist($dir_ownership_thumbs);

        foreach ($own_photos as $o_photo) {
            Images::save($dir_ownership . $o_photo, $dir_ownership . '/originals/' . $o_photo);
            Images::createThumbnail($dir_ownership . '/originals/' . $o_photo, $dir_ownership_thumbs . $o_photo, $thumbnail_size);
            Images::resizeDiferentDirectoriesAndWatermark($dir_ownership . '/originals/' . $o_photo, $dir_ownership . $o_photo, $dir_watermark, $dir_ownership_photo_size);
        }

        $output->writeln("End of process");
    }

}