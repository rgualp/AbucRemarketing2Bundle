<?php

/**
 * Description of Utils
 *
 * @author DarthDaniel
 */

namespace MyCp\mycpBundle\Helpers;

class Images {

    public static function create_thumbnail($origin_file_full_path, $thumb_file_full_path, $height) {
        $imagine = new \Imagine\Gd\Imagine();
        $mode = \Imagine\Image\ImageInterface::THUMBNAIL_OUTBOUND;
        $img = $imagine->open($origin_file_full_path);
        $size = $img->getSize();

        $width = ($size->getWidth() * $height) / $size->getHeight();

        $img->thumbnail(new \Imagine\Image\Box($width, $height), $mode)
                ->save($thumb_file_full_path);
    }

    public static function resize($origin_file_full_path, $new_height) {
        $imagine = new \Imagine\Gd\Imagine();
        $image = $imagine->open($origin_file_full_path);
        $size = $image->getSize();
        $new_width = ($size->getWidth() * $new_height) / $size->getHeight();

        $image->resize(new \Imagine\Image\Box($new_width, $new_height))
                ->save($origin_file_full_path);

        return $new_width;
    }

    public static function resize_and_watermark($origin_file_full_path, $watermark_full_path, $new_height) {
        $imagine = new \Imagine\Gd\Imagine();

        $new_width = Images::resize($origin_file_full_path, $new_height);

        $watermark = $imagine->open($watermark_full_path);
        $wSize = $watermark->getSize();
        //$watermark_height = ($wSize->getHeight() * ($new_width - 10)) / $wSize->getWidth();
        //$watermark_resize = $watermark->resize(new \Imagine\Image\Box($new_width - 10, $watermark_height));

        //$point = new \Imagine\Image\Point(3, (int) $new_height / 2);
        if($wSize->getWidth() > $new_width)
        {
            $watermark_width = (($wSize->getHeight()-10) * ($new_width - 10)) / $new_height;
            $watermark = $watermark->resize(new \Imagine\Image\Box($watermark_width, ($wSize->getHeight()-10)));
            $wSize = $watermark->getSize();
        }
        
        $point = new \Imagine\Image\Point(($new_width - $wSize->getWidth() - 10), 10);

        $imagine->open($origin_file_full_path)
                //->paste($watermark_resize, $point)
                ->paste($watermark, $point)
                ->save($origin_file_full_path);
        
    }

    public static function process_images_with_database_info($entity_manager, $container) {
        $dir_user = $container->getParameter('user.dir.photos');
        $dir_destination = $container->getParameter('destination.dir.photos');
        $dir_destination_thumbs = $container->getParameter('destination.dir.thumbnails');
        $dir_album = $container->getParameter('album.dir.photos');
        $dir_albums_thumbs = $container->getParameter('album.dir.thumbnails');
        $dir_ownership = $container->getParameter('ownership.dir.photos');
        $dir_ownership_thumbs = $container->getParameter('ownership.dir.thumbnails');
        $dir_watermark = $container->getParameter('dir.watermark');

        $albums_photos = $entity_manager->getRepository('mycpBundle:albumPhoto')->findAll();
        foreach ($albums_photos as $albumPhoto) {
            $photo = $albumPhoto->getAlbPhoPhoto();
            if ($photo != null && file_exists(realpath($dir_album . $photo->getPhoName()))) {
                Images::resize($dir_album . $photo->getPhoName(), 480);
                Images::create_thumbnail($dir_album . $photo->getPhoName(), $dir_albums_thumbs. $photo->getPhoName(), 160);
            }
        }

        $users = $entity_manager->getRepository('mycpBundle:user')->findAll();
        foreach ($users as $user) {
            $photo = $user->getUserPhoto();
            if ($photo != null && file_exists(realpath($dir_user . $photo->getPhoName()))) {
                Images::resize($dir_user . $photo->getPhoName(), 65);
            }
        }

        $destination_photos = $entity_manager->getRepository('mycpBundle:destinationPhoto')->findAll();
        foreach ($destination_photos as $destinationPhoto) {
            $photo = $destinationPhoto->getDesPhoPhoto();
            if ($photo != null && file_exists(realpath($dir_destination . $photo->getPhoName()))) {
                Images::resize($dir_destination . $photo->getPhoName(), 480);
                Images::create_thumbnail($dir_destination . $photo->getPhoName(), $dir_destination_thumbs. $photo->getPhoName(), 160);
            }
        }

        $ownership_photos = $entity_manager->getRepository('mycpBundle:ownershipPhoto')->findAll();
        foreach ($ownership_photos as $ownPhoto) {
            $photo = $ownPhoto->getOwnPhoPhoto();
            if ($photo != null && file_exists(realpath($dir_ownership . $photo->getPhoName()))) {
                Images::resize_and_watermark($dir_ownership . $photo->getPhoName(), $dir_watermark, 480);
                Images::create_thumbnail($dir_ownership . $photo->getPhoName(), $dir_ownership_thumbs. $photo->getPhoName(), 160);
            }
        }
    }

    public static function process_images_with_directory_info($container) {
        $dir_user = $container->getParameter('user.dir.photos');
        $dir_destination = $container->getParameter('destination.dir.photos');
        $dir_destination_thumbs = $container->getParameter('destination.dir.thumbnails');
        $dir_album = $container->getParameter('album.dir.photos');
        $dir_albums_thumbs = $container->getParameter('album.dir.thumbnails');
        $dir_ownership = $container->getParameter('ownership.dir.photos');
        $dir_ownership_thumbs = $container->getParameter('ownership.dir.thumbnails');
        $dir_watermark = $container->getParameter('dir.watermark');

        $results = "Redimensionando las fotos de los usuarios... <br/>";
        $users_photos = Images::get_directory_images_content($dir_user);
        foreach ($users_photos as $u_photo) {
            Images::resize($dir_user . $u_photo, 65);
        }

        $results .= "Redimensionando las fotos de los destinos y generando thumbnails...<br/>";
        $destinations_photos = Images::get_directory_images_content($dir_destination);
        foreach ($destinations_photos as $d_photo) {
            Images::create_thumbnail($dir_destination . $d_photo, $dir_destination_thumbs. $d_photo, 160);
            Images::resize($dir_destination . $d_photo, 480);            
        }

        $results .= "Redimensionando las fotos de los albums y generando thumbnails...<br/>";
        $albums_photos = Images::get_directory_images_content($dir_album);
        foreach ($albums_photos as $a_photo) {
            Images::create_thumbnail($dir_album . $a_photo, $dir_albums_thumbs. $a_photo, 160);
            Images::resize($dir_album . $a_photo, 480);            
        }

        $results .= "Redimensionando las fotos de los alojamientos, colocando marca de agua y generando thumbnails...<br/>";
        $own_photos = Images::get_directory_images_content($dir_ownership);
        foreach ($own_photos as $o_photo) {
            Images::create_thumbnail($dir_ownership . $o_photo, $dir_ownership_thumbs. $o_photo, 160);
            Images::resize_and_watermark($dir_ownership . $o_photo, $dir_watermark, 480);            
        }

        $results .= "Fin del procesamiento";
        return $results;
    }

    public static function get_directory_images_content($directory) {
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

}

?>
