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
        
        $width = ($size->getWidth() * $height)/$size->getHeight();

        $img->thumbnail(new \Imagine\Image\Box($width, $height), $mode)
            ->save($thumb_file_full_path);       
    }
    
    public static function resize($origin_file_full_path, $new_height) {
        $imagine = new \Imagine\Gd\Imagine();
        $image = $imagine->open($origin_file_full_path);
        $size = $image->getSize();
        $new_width = ($size->getWidth() * $new_height)/$size->getHeight();

        $image->resize(new \Imagine\Image\Box($new_width, $new_height))
                      ->save($origin_file_full_path);
        
        return $new_width;
    }
    
    public static function resize_and_watermark($origin_file_full_path, $watermark_full_path, $new_height) {
        $imagine = new \Imagine\Gd\Imagine();
        
        $new_width = Images::resize($origin_file_full_path, $new_height);
        
        $watermark = $imagine->open($watermark_full_path);
        $wSize = $watermark->getSize();
        $watermark_height = ($wSize->getHeight() * ($new_width - 10))/$wSize->getWidth();
        $watermark_resize = $watermark->resize(new \Imagine\Image\Box($new_width - 10, $watermark_height));

        $point = new \Imagine\Image\Point(3, (int)$new_height/2);

        $imagine->open($origin_file_full_path)
                ->paste($watermark_resize, $point)
                ->save($origin_file_full_path);
    }
    
    public static function process_images_with_database_info($entity_manager, $container)
    {
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
            if($photo != null && file_exists(realpath($dir_album.$photo->getPhoName())))
            {
              Images::resize_and_watermark($dir_album.$photo->getPhoName(), $dir_watermark, 480);   
              Images::create_thumbnail($dir_album.$photo->getPhoName(), $dir_albums_thumbs, 160);   
            }
        }
        
        $users = $entity_manager->getRepository('mycpBundle:user')->findAll();        
        foreach ($users as $user) {
            $photo = $user->getUserPhoto();
            if($photo != null && file_exists(realpath($dir_user.$photo->getPhoName())))
            {
              Images::resize($dir_user.$photo->getPhoName(), 65);     
            }
        }
        
        $destination_photos = $entity_manager->getRepository('mycpBundle:destinationPhoto')->findAll();        
        foreach ($destination_photos as $destinationPhoto) {
            $photo = $destinationPhoto->getDesPhoPhoto();
            if($photo != null && file_exists(realpath($dir_destination.$photo->getPhoName())))
            {
              Images::resize_and_watermark($dir_destination.$photo->getPhoName(), $dir_watermark, 480);   
              Images::create_thumbnail($dir_destination.$photo->getPhoName(), $dir_destination_thumbs, 160);   
            }
        }
        
        $ownership_photos = $entity_manager->getRepository('mycpBundle:ownershipPhoto')->findAll();        
        foreach ($ownership_photos as $ownPhoto) {
            $photo = $ownPhoto->getOwnPhoPhoto();
            if($photo != null && file_exists(realpath($dir_ownership.$photo->getPhoName())))
            {
              Images::resize_and_watermark($dir_ownership.$photo->getPhoName(), $dir_watermark, 480);   
              Images::create_thumbnail($dir_ownership.$photo->getPhoName(), $dir_ownership_thumbs, 160);   
            }
        }
        
    }

}

?>
