<?php

/**
 * Description of Utils
 *
 * @author DarthDaniel
 */

namespace MyCp\mycpBundle\Helpers;

class Images {

    public static function createThumbnail($origin_file_full_path, $thumb_file_full_path, $height) {
        if(!file_exists(realpath($thumb_file_full_path))){
        $imagine = new \Imagine\Gd\Imagine();
        $mode = \Imagine\Image\ImageInterface::THUMBNAIL_OUTBOUND;
        $img = $imagine->open($origin_file_full_path);
        $size = $img->getSize();

        $width = ($size->getWidth() * $height) / $size->getHeight();

        $img->thumbnail(new \Imagine\Image\Box($width, $height), $mode)
                ->save($thumb_file_full_path, array('format' => 'jpeg','quality' => 100));
        }
    }

    public static function resize($origin_file_full_path, $new_height) {
        $imagine = new \Imagine\Gd\Imagine();
        $image = $imagine->open($origin_file_full_path);
        $size = $image->getSize();
        $new_width = ($size->getWidth() * $new_height) / $size->getHeight();

        $image->resize(new \Imagine\Image\Box($new_width, $new_height))
                ->save($origin_file_full_path, array('format' => 'jpeg','quality' => 100));

        return $new_width;
    }

    public static function resizeDiferentDirectories($file_full_path_from, $file_full_path_to, $new_height) {
        $imagine = new \Imagine\Gd\Imagine();
        $image = $imagine->open($file_full_path_from);
        $size = $image->getSize();
        $new_width = ($size->getWidth() * $new_height) / $size->getHeight();

        $image->resize(new \Imagine\Image\Box($new_width, $new_height))
                ->save($file_full_path_to, array('format' => 'jpeg','quality' => 100));

        return $new_width;
    }

    public static function save($image_path_from, $image_path_to) {
        if(!file_exists(realpath($image_path_to))){
        $imagine = new \Imagine\Gd\Imagine();
        $image = $imagine->open($image_path_from);
        $image->save($image_path_to, array('format' => 'jpeg','quality' => 100));
        }
        return true;
    }

    public static function resizeAndWatermark($origin_file_full_path, $fileName, $watermark_full_path, $new_height, $container, $subPath = "") {
        $imagine = new \Imagine\Gd\Imagine();

        $dirOriginalsPhotos= $container->getParameter('ownership.dir.photos.originals');
        FileIO::createDirectoryIfNotExist($dirOriginalsPhotos.$subPath);
        $imagine->open($origin_file_full_path."/".$fileName)
                ->save($dirOriginalsPhotos.$subPath."/".$fileName, array('format' => 'jpeg','quality' => 100));

        $new_width = Images::resize($origin_file_full_path."/".$fileName, $new_height);

        $watermark = $imagine->open($watermark_full_path);
        $wSize = $watermark->getSize();
        //$watermark_height = ($wSize->getHeight() * ($new_width - 10)) / $wSize->getWidth();
        //$watermark_resize = $watermark->resize(new \Imagine\Image\Box($new_width - 10, $watermark_height));
        //$point = new \Imagine\Image\Point(3, (int) $new_height / 2);
        if ($wSize->getWidth() > $new_width || ($new_width - $wSize->getWidth()) < 10 ) {
            $watermark_width = $wSize->getWidth() - 20;//(($wSize->getHeight() - 10) * ($new_width - 10)) / $new_height;
            $watermark = $watermark->resize(new \Imagine\Image\Box($watermark_width, ($wSize->getHeight() - 10)));
            $wSize = $watermark->getSize();
        }


        $point = new \Imagine\Image\Point(($new_width - $wSize->getWidth() - 10), 10);

        $imagine->open($origin_file_full_path."/".$fileName)
                //->paste($watermark_resize, $point)
                ->paste($watermark, $point)
                ->save($origin_file_full_path."/".$fileName, array('format' => 'jpeg','quality' => 100));
    }

    public static function resizeDiferentDirectoriesAndWatermark($file_full_path_from, $file_full_path_to, $watermark_full_path, $new_height) {
        $imagine = new \Imagine\Gd\Imagine();

        $new_width = Images::resizeDiferentDirectories($file_full_path_from, $file_full_path_to, $new_height);

        $watermark = $imagine->open($watermark_full_path);
        $wSize = $watermark->getSize();
        //$watermark_height = ($wSize->getHeight() * ($new_width - 10)) / $wSize->getWidth();
        //$watermark_resize = $watermark->resize(new \Imagine\Image\Box($new_width - 10, $watermark_height));
        //$point = new \Imagine\Image\Point(3, (int) $new_height / 2);
        if ($wSize->getWidth() > $new_width) {
            $watermark_width = (($wSize->getHeight() - 10) * ($new_width - 10)) / $new_height;
            $watermark = $watermark->resize(new \Imagine\Image\Box($watermark_width, ($wSize->getHeight() - 10)));
            $wSize = $watermark->getSize();
        }

        $point = new \Imagine\Image\Point(($new_width - $wSize->getWidth() - 10), 10);

        $imagine->open($file_full_path_to)
                //->paste($watermark_resize, $point)
                ->paste($watermark, $point)
                ->save($file_full_path_to, array('format' => 'jpeg','quality' => 100));
    }

    public static function processImagesWithDatabaseInfo($entity_manager, $container) {
        $dir_user = $container->getParameter('user.dir.photos');
        $dir_destination = $container->getParameter('destination.dir.photos');
        $dir_destination_thumbs = $container->getParameter('destination.dir.thumbnails');
        $dir_album = $container->getParameter('album.dir.photos');
        $dir_albums_thumbs = $container->getParameter('album.dir.thumbnails');
        $dir_ownership = $container->getParameter('ownership.dir.photos');
        $dir_ownership_thumbs = $container->getParameter('ownership.dir.thumbnails');
        $dir_watermark = $container->getParameter('dir.watermark');

        $ownership_photo_size = $container->getParameter('ownership.dir.photos.size');
        $album_photo_size = $container->getParameter('album.dir.photos.size');
        $destination_photo_size = $container->getParameter('destination.dir.photos.size');
        $thumbs_size = $container->getParameter('thumbnail.size');
        $user_photo_size = $container->getParameter('user.photo.size');

        $albums_photos = $entity_manager->getRepository('mycpBundle:albumPhoto')->findAll();
        foreach ($albums_photos as $albumPhoto) {
            $photo = $albumPhoto->getAlbPhoPhoto();
            if ($photo != null && file_exists(realpath($dir_album . $photo->getPhoName()))) {
                Images::resize($dir_album . $photo->getPhoName(), $album_photo_size);
                Images::createThumbnail($dir_album . $photo->getPhoName(), $dir_albums_thumbs . $photo->getPhoName(), $thumbs_size);
            }
        }

        $users = $entity_manager->getRepository('mycpBundle:user')->findAll();
        foreach ($users as $user) {
            $photo = $user->getUserPhoto();
            if ($photo != null && file_exists(realpath($dir_user . $photo->getPhoName()))) {
                Images::resize($dir_user . $photo->getPhoName(), $user_photo_size);
            }
        }

        $destination_photos = $entity_manager->getRepository('mycpBundle:destinationPhoto')->findAll();
        foreach ($destination_photos as $destinationPhoto) {
            $photo = $destinationPhoto->getDesPhoPhoto();
            if ($photo != null && file_exists(realpath($dir_destination . $photo->getPhoName()))) {
                Images::resize($dir_destination . $photo->getPhoName(), $destination_photo_size);
                Images::createThumbnail($dir_destination . $photo->getPhoName(), $dir_destination_thumbs . $photo->getPhoName(), $thumbs_size);
            }
        }

        $ownership_photos = $entity_manager->getRepository('mycpBundle:ownershipPhoto')->findAll();
        foreach ($ownership_photos as $ownPhoto) {
            $photo = $ownPhoto->getOwnPhoPhoto();
            if ($photo != null && file_exists(realpath($dir_ownership . $photo->getPhoName()))) {
                Images::resizeAndWatermark($dir_ownership, $photo->getPhoName(), $dir_watermark, $ownership_photo_size, null);
                Images::createThumbnail($dir_ownership . $photo->getPhoName(), $dir_ownership_thumbs . $photo->getPhoName(), $thumbs_size);
            }
        }
    }

    public static function processImagesWithDirectoryInfo($container) {
        $dir_user = $container->getParameter('user.dir.photos');
        $dir_destination = $container->getParameter('destination.dir.photos');
        $dir_destination_thumbs = $container->getParameter('destination.dir.thumbnails');
        $dir_album = $container->getParameter('album.dir.photos');
        $dir_albums_thumbs = $container->getParameter('album.dir.thumbnails');
        $dir_ownership = $container->getParameter('ownership.dir.photos');
        $dir_ownership_thumbs = $container->getParameter('ownership.dir.thumbnails');
        $dir_watermark = $container->getParameter('dir.watermark');

        $ownership_photo_size = $container->getParameter('ownership.dir.photos.size');
        $album_photo_size = $container->getParameter('album.dir.photos.size');
        $destination_photo_size = $container->getParameter('destination.dir.photos.size');
        $thumbs_size = $container->getParameter('thumbnail.size');
        $user_photo_size = $container->getParameter('user.photo.size');

        $total_images_to_process = 50;

        $results = "";
        $destinations_photos = array();
        $albums_photos = array();
        $own_photos = array();
        $process_destination = false;
        $process_albums = false;
        $process_owns = false;


        /* $results = "Redimensionando las fotos de los usuarios... <br/>";
          $users_photos = Images::getDirectoryImagesContent($dir_user);
          Images::create_originals_directory($dir_user);
          foreach ($users_photos as $u_photo) {
          Images::save($dir_user . $u_photo,$dir_user.'/originals/'.$u_photo);
          Images::resizeDiferentDirectories($dir_user.'/originals/'.$u_photo,$dir_user . $u_photo, 65);
          } */

        $results .= "Redimensionando las fotos de los destinos y generando thumbnails...<br/>";
        $destinations_photos = Images::getDirectoryImagesContentWithSize($dir_destination, $total_images_to_process);
        FileIO::createDirectoryIfNotExist($dir_destination . '/originals');
        FileIO::createDirectoryIfNotExist($dir_destination . '/processed');
        FileIO::createDirectoryIfNotExist($dir_destination_thumbs);
        $process_destination = true;
        foreach ($destinations_photos as $d_photo) {
            Images::save($dir_destination . $d_photo, $dir_destination . '/originals/' . $d_photo);

            Images::createThumbnail($dir_destination . '/originals/' . $d_photo, $dir_destination_thumbs . $d_photo, $thumbs_size);
            Images::resizeDiferentDirectories($dir_destination . '/originals/' . $d_photo, $dir_destination . '/processed/' . $d_photo, $destination_photo_size);
            FileIO::deleteFile($dir_destination . $d_photo);
        }

            if (count($destinations_photos) == 0 && $process_destination) {
                $results .= "Redimensionando las fotos de los albums y generando thumbnails...<br/>";
                $albums_photos = Images::getDirectoryImagesContentWithSize($dir_album, $total_images_to_process);
                FileIO::createDirectoryIfNotExist($dir_album . '/originals');
                FileIO::createDirectoryIfNotExist($dir_album . '/processed');
                FileIO::createDirectoryIfNotExist($dir_albums_thumbs);
                $process_albums = true;

                foreach ($albums_photos as $a_photo) {
                    Images::save($dir_album . $a_photo, $dir_album . '/originals/' . $a_photo);

                    Images::createThumbnail($dir_album . '/originals/' . $a_photo, $dir_albums_thumbs . $a_photo, $thumbs_size);
                    Images::resizeDiferentDirectories($dir_album . '/originals/' . $a_photo, $dir_album . '/processed/' . $a_photo, $album_photo_size);
                    FileIO::deleteFile($dir_album . $a_photo);
                }

                    if (count($albums_photos) == 0 && $process_albums) {
                        $results .= "Redimensionando las fotos de los alojamientos, colocando marca de agua y generando thumbnails...<br/>";
                        $own_photos = Images::getDirectoryImagesContentWithSize($dir_ownership, $total_images_to_process);
                        FileIO::createDirectoryIfNotExist($dir_ownership . '/originals');
                        FileIO::createDirectoryIfNotExist($dir_ownership . '/processed');
                        FileIO::createDirectoryIfNotExist($dir_ownership_thumbs);
                        $process_owns = true;

                        foreach ($own_photos as $o_photo) {
                            Images::save($dir_ownership . $o_photo, $dir_ownership . '/originals/' . $o_photo);

                            Images::createThumbnail($dir_ownership . '/originals/' . $o_photo, $dir_ownership_thumbs . $o_photo, $thumbs_size);
                            Images::resizeDiferentDirectoriesAndWatermark($dir_ownership . '/originals/' . $o_photo, $dir_ownership . '/processed/' . $o_photo, $dir_watermark, $ownership_photo_size);

                            FileIO::deleteFile($dir_ownership . $o_photo);
                        }
                    }
                }

        if (count($own_photos) == 0 && $process_owns) {
            $results .= "Iniciando proceso de copiado...<br/>";
            return array('msg_text' => $results, 'finished' => 'true');
        }

        return array('msg_text' => $results, 'finished' => 'false');
    }

    public static function copyFromProcessedToMainDirectory($container, $results) {
        $dir_user = $container->getParameter('user.dir.photos');
        $dir_destination = $container->getParameter('destination.dir.photos');
        $dir_destination_processed = $dir_destination . '/processed/';
        $dir_album = $container->getParameter('album.dir.photos');
        $dir_albums_processed = $dir_album . '/processed/';
        $dir_ownership = $container->getParameter('ownership.dir.photos');
        $dir_ownership_processed = $dir_ownership . '/processed/';

        $total_images_to_process = 50;

        //$results = "";
        $destinations_photos = array();
        $albums_photos = array();
        $own_photos = array();
        $process_destination = false;
        $process_albums = false;
        $process_owns = false;

        $results .= "Copiando las fotos de los destinos hacia el directorio principal...<br/>";
        $destinations_photos = Images::getDirectoryImagesContentWithSize($dir_destination_processed, $total_images_to_process);
        $process_destination = true;

        foreach ($destinations_photos as $d_photo) {
            Images::save($dir_destination_processed . $d_photo, $dir_destination . $d_photo);
            FileIO::deleteFile($dir_destination_processed . $d_photo);
        }

        if (count($destinations_photos) == 0 && $process_destination) {
            $results .= "Copiando las fotos de los albums hacia el directorio principal...<br/>";
            $albums_photos = Images::getDirectoryImagesContentWithSize($dir_albums_processed, $total_images_to_process);
            $process_albums = true;

            foreach ($albums_photos as $a_photo) {
                Images::save($dir_albums_processed . $a_photo, $dir_album . $a_photo);
                FileIO::deleteFile($dir_albums_processed . $a_photo);
            }

            if (count($albums_photos) == 0 && $process_albums) {
                $results .= "Copiando las fotos de los alojamientos en el directorio principal...<br/>";
                $own_photos = Images::getDirectoryImagesContentWithSize($dir_ownership_processed, $total_images_to_process);
                $process_owns = true;

                foreach ($own_photos as $o_photo) {
                    Images::save($dir_ownership_processed . $o_photo, $dir_ownership . $o_photo);
                    FileIO::deleteFile($dir_ownership_processed . $o_photo);
                }
            }
        }
        if (count($own_photos) == 0 && $process_owns) {
            FileIO::deleteDirectory($dir_albums_processed);
            FileIO::deleteDirectory($dir_destination_processed);
            FileIO::deleteDirectory($dir_ownership_processed);

            $results .= "Fin del procesamiento";
            return array('msg_text' => $results, 'finished' => 'true');
        }

        return array('msg_text' => $results, 'finished' => 'false');
    }

    public static function getDirectoryImagesContent($directory) {
        $results = array();
        $handler = opendir($directory);

        while ($file = readdir($handler)) {
            if ($file != "." && $file != ".." && $file != "no_photo.png" && $file != "no_photo_square.gif" && (strpos($file, ".jpg") !== FALSE || strpos($file, ".png") !== FALSE || strpos($file, ".gif") !== FALSE || strpos($file, ".bmp") !== FALSE)) {
                $results[] = $file;
            }
        }

        closedir($handler);
        return $results;
    }

    public static function getDirectoryImagesContentWithSize($directory, $total_files) {
        $results = array();

        if (is_dir($directory)) {
            $handler = opendir($directory);

            $count = 0;

            while ($file = readdir($handler)) {
                if ($count < $total_files) {
                    if ($file != "." && $file != ".." && $file != "no_photo.png" && $file != "no_photo_square.gif" && (strpos($file, ".jpg") !== FALSE || strpos($file, ".png") !== FALSE || strpos($file, ".gif") !== FALSE || strpos($file, ".bmp") !== FALSE)) {
                        $results[] = $file;
                    }
                    $count++;
                }
                else
                    break;
            }

            closedir($handler);
        }
        return $results;
    }
}

?>
