<?php

namespace MyCp\mycpBundle\Controller;

use MyCp\mycpBundle\Helpers\FileIO;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use MyCp\mycpBundle\Entity\albumCategory;
use MyCp\mycpBundle\Entity\albumCategoryLang;
use MyCp\mycpBundle\Entity\album;
use MyCp\mycpBundle\Entity\albumPhoto;
use MyCp\mycpBundle\Entity\photo;
use MyCp\mycpBundle\Entity\photoLang;
use MyCp\mycpBundle\Form\categoryType;
use Symfony\Component\Validator\Constraints\NotBlank;
use MyCp\mycpBundle\Helpers\BackendModuleName;

class BackendAlbumController extends Controller {

    function list_categoryAction($items_per_page, Request $request) {
        $service_security = $this->get('Secure');
        $service_security->verifyAccess();
        $em = $this->getDoctrine()->getEntityManager();
        $languages = $em->getRepository('mycpBundle:lang')->findAll();

        $paginator = $this->get('ideup.simple_paginator');
        $paginator->setItemsPerPage($items_per_page);
        $categories = $paginator->paginate($em->getRepository('mycpBundle:albumCategoryLang')->getCategories())->getResult();
        $page = 1;
        if (isset($_GET['page']))
            $page = $_GET['page'];
        return $this->render('mycpBundle:album:categoryList.html.twig', array(
                    'categories' => $categories,
                    'items_per_page' => $items_per_page,
                    'total_items' => $paginator->getTotalItems(),
                    'current_page' => $page,
        ));
    }

    function new_categoryAction(Request $request) {
        $service_security = $this->get('Secure');
        $service_security->verifyAccess();
        $em = $this->getDoctrine()->getEntityManager();
        $languages = $em->getRepository('mycpBundle:lang')->findAll();
        $form = $this->createForm(new categoryType(array('languages' => $languages)));

        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $category = new albumCategory();
                $em->persist($category);
                $em->flush();
                $post = $form->getData();
                foreach ($languages as $language) {
                    $category_lang = new albumCategoryLang();
                    $category_lang->setAlbumCatIdCat($category);
                    $category_lang->setAlbumCatIdLang($language);
                    $category_lang->setAlbumCatDescription($post['lang' . $language->getLangId()]);
                    $em->persist($category_lang);
                }
                $em->flush();
                $message = 'Categoría añadida satisfactoriamente.';
                $this->get('session')->getFlashBag()->add('message_ok', $message);

                $service_log = $this->get('log');
                $service_log->saveLog('Create category, ' . $post['lang' . $languages[0]->getLangId()], BackendModuleName::MODULE_ALBUM);

                return $this->redirect($this->generateUrl('mycp_list_category_album'));
            }
        }
        return $this->render('mycpBundle:album:categoryNew.html.twig', array(
                    'form' => $form->createView(),
        ));
    }

    function edit_categoryAction($id_category, Request $request) {
        $service_security = $this->get('Secure');
        $service_security->verifyAccess();
        $em = $this->getDoctrine()->getEntityManager();

        $languages = $em->getRepository('mycpBundle:lang')->findAll();
        $album_cat_lang = $em->getRepository('mycpBundle:albumCategoryLang')->findBy(array('album_cat_id_cat' => $id_category));
        if ($request->getMethod() == 'POST') {
            $form = $this->createForm(new categoryType(array('languages' => $languages)));
        } else {
            $album_cat_lang = $em->getRepository('mycpBundle:albumCategoryLang')->findBy(array('album_cat_id_cat' => $id_category));
            $form = $this->createForm(new categoryType(array('languages' => $languages, 'album_cat_lang' => $album_cat_lang)));
        }


        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);
            if ($form->isValid()) {

                $post = $form->getData();

                foreach ($languages as $language) {
                    $album_cat_lang = $em->getRepository('mycpBundle:albumCategoryLang')->findBy(array('album_cat_id_lang' => $language,'album_cat_id_cat'=>$id_category));
                    $album_cat_lang[0]->setAlbumCatDescription($post['lang' . $language->getLangId()]);
                    $em->persist($album_cat_lang[0]);
                }
                $em->flush();
                $message = 'Categoría actualizada satisfactoriamente.';
                $this->get('session')->getFlashBag()->add('message_ok', $message);

                $service_log = $this->get('log');
                $service_log->saveLog('Edit category, ' . $post['lang' . $languages[0]->getLangId()], BackendModuleName::MODULE_ALBUM);

                return $this->redirect($this->generateUrl('mycp_list_category_album'));
            }
        }

        return $this->render('mycpBundle:album:categoryNew.html.twig', array(
                    'form' => $form->createView(), 'edit_category' => $id_category, 'name_category' => $album_cat_lang[0]->getAlbumCatDescription()
        ));
    }

    function delete_categoryAction($id_category) {
        $service_security = $this->get('Secure');
        $service_security->verifyAccess();
        $em = $this->getDoctrine()->getEntityManager();
        $category = $em->getRepository('mycpBundle:albumCategory')->find($id_category);
        $category_langs = $em->getRepository('mycpBundle:albumCategoryLang')->findby(array('album_cat_id_cat' => $category));
        $albums = $em->getRepository('mycpBundle:album')->findby(array('album_category' => $id_category));
        $old_cat_lang = $category_langs[0]->getAlbumCatDescription();

        $dir = $this->container->getParameter('album.dir.photos');
        $dir_thumbs = $this->container->getParameter('album.dir.thumbnails');

        foreach ($category_langs as $category_lang) {
            $em->remove($category_lang);
        }
        foreach ($albums as $album) {

            $album_langs = $em->getRepository('mycpBundle:albumLang')->findby(array('album_lang_album' => $album->getAlbumId()));
            foreach ($album_langs as $album_lang) {
                $em->remove($album_lang);
            }
            $em->remove($album);

            $albums_photos = $em->getRepository('mycpBundle:albumPhoto')->findby(array('alb_pho_album' => $album->getAlbumId()));
            foreach ($albums_photos as $albums_photo) {
                $photo = $em->getRepository('mycpBundle:photo')->find($albums_photo->getAlbPhoPhoto()->getPhoId());
                FileIO::deleteFile($dir . $photo->getPhoName());
                FileIO::deleteFile($dir_thumbs . $photo->getPhoName());
                $albumsPhotoLangs = $em->getRepository('mycpBundle:photoLang')->findBy(array('pho_lang_id_photo' => $photo->getPhoId()));
                foreach ($albumsPhotoLangs as $albumPhotoLang) {
                    $em->remove($albumPhotoLang);
                }
                $em->remove($albums_photo);
                $em->remove($photo);

            }
            $em->remove($album);
        }
        if ($category)
            $em->remove($category);
        $em->flush();
        $message = 'Categoría eliminada satisfactoriamente.';
        $this->get('session')->getFlashBag()->add('message_ok', $message);

        $service_log = $this->get('log');
        $service_log->saveLog('Delete category, ' . $old_cat_lang, BackendModuleName::MODULE_ALBUM);

        return $this->redirect($this->generateUrl('mycp_list_category_album'));
    }

    function list_albumsAction($items_per_page, Request $request) {
        $service_security = $this->get('Secure');
        $service_security->verifyAccess();

        /* $permissions=$em->getRepository('mycpBundle:permission')->findAll();
          $role=$em->getRepository('mycpBundle:role')->find(4);
          //var_dump($permissions); exit();
          foreach($permissions as $permission)
          {
          $roleper= new \MyCp\mycpBundle\Entity\rolePermission();
          $roleper->setRpPermission($permission);
          $roleper->setRpRole($role);
          $em->persist($roleper);
          }
          $em->flush();
          exit(); */

        $em = $this->getDoctrine()->getEntityManager();

        $page = 1;
        $filter_active = $request->get('filter_active');
        $filter_name = $request->get('filter_name');
        $filter_category = $request->get('filter_category');
        if ($request->getMethod() == 'POST' && $filter_active == 'null' && $filter_name == 'null' && $filter_category == 'null') {
            $message = 'Debe llenar al menos un campo para filtrar.';
            $this->get('session')->getFlashBag()->add('message_error_local', $message);
            return $this->redirect($this->generateUrl('mycp_list_albums'));
        }
        if ($filter_name == 'null')
            $filter_name = '';
        if (isset($_GET['page']))
            $page = $_GET['page'];

        $paginator = $this->get('ideup.simple_paginator');
        $paginator->setItemsPerPage($items_per_page);
        $albums = $paginator->paginate($em->getRepository('mycpBundle:album')->getAll($filter_name, $filter_active, $filter_category))->getResult();
        $data = array();
        foreach ($albums as $album) {

            $photos = $em->getRepository('mycpBundle:albumPhoto')->findBy(array('alb_pho_album' => $album->getAlbumId()));
            $data[$album->getAlbumId() . '_photo_count'] = count($photos);
            $data[$album->getAlbumId() . '_category'] = $em->getRepository('mycpBundle:albumCategoryLang')->findBy(array('album_cat_id_cat' => $album->getAlbumCategory()));
        }

        $service_log = $this->get('log');
        $service_log->saveLog('Visit', BackendModuleName::MODULE_ALBUM);

        return $this->render('mycpBundle:album:list.html.twig', array(
                    'albumes' => $albums,
                    'photo_count' => $data,
                    'data' => $data,
                    'items_per_page' => $items_per_page,
                    'current_page' => $page,
                    'total_items' => $paginator->getTotalItems(),
                    'filter_name' => $filter_name,
                    'filter_active' => $filter_active,
                    'filter_category' => $filter_category,
        ));
    }

    function get_all_categoriesAction($data) {
        $em = $this->getDoctrine()->getEntityManager();
        $categories = $em->getRepository('mycpBundle:albumCategoryLang')->getCategories();
        return $this->render('mycpBundle:utils:category.html.twig', array('categories' => $categories, 'data' => $data));
    }

    public function new_albumAction(Request $request) {
        $service_security = $this->get('Secure');
        $service_security->verifyAccess();
        $errors = array();
        $post = $request->request->getIterator()->getArrayCopy();
        $em = $this->getDoctrine()->getEntityManager();

        if ($request->getMethod() == 'POST') {
            $not_blank_validator = new NotBlank();
            $not_blank_validator->message = "Este campo no puede estar vacío.";
            $array_keys = array_keys($post);
            $count = $errors_validation = $count_errors = 0;

            foreach ($post as $item) {
                if(strpos($array_keys[$count], 'name_')!==0 and strpos($array_keys[$count], 'brief_desc_')!==0)
                {
                    $errors[$array_keys[$count]] = $errors_validation = $this->get('validator')->validateValue($item, $not_blank_validator);
                    $count_errors+=count($errors_validation);
                }
                $count++;
            }

            if ($count_errors == 0) {
                //save into database
                $service_log = $this->get('log');
                if ($request->request->get('edit_album')) {
                    $em->getRepository('mycpBundle:album')->edit($post);
                    $message = 'Álbum actualizado satisfactoriamente.';
                    $album_lang_save = $em->getRepository('mycpBundle:albumLang')->findBy(array('album_lang_album' => $post['edit_album']));

                    $service_log->saveLog('Edit entity, ' . $album_lang_save[0]->getAlbumLangName(), BackendModuleName::MODULE_ALBUM);
                } else {
                    $em->getRepository('mycpBundle:album')->insert($post);
                    $message = 'Álbum añadido satisfactoriamente.';
                    $languages = $em->getRepository('mycpBundle:lang')->findAll();

                    $service_log->saveLog('Create entity, ' . $post['name_' . $languages[0]->getLangId()], BackendModuleName::MODULE_ALBUM);
                }
                $this->get('session')->getFlashBag()->add('message_ok', $message);
                return $this->redirect($this->generateUrl('mycp_list_albums'));
            }
            if ($request->request->get('edit_album')) {
                $id_album = $request->request->get('edit_album');
                $album = $em->getRepository('mycpBundle:album')->find($id_album);
                $post['name_album'] = $album->getAlbumName();
                $post['id_album'] = $id_album;
            }
        }

        $languages = $em->getRepository('mycpBundle:lang')->getAll();
        return $this->render('mycpBundle:album:new.html.twig', array('languages' => $languages, 'errors' => $errors, 'data' => $post));
    }

    public function delete_albumAction($id_album) {
        $service_security = $this->get('Secure');
        $service_security->verifyAccess();
        $em = $this->getDoctrine()->getEntityManager();
        $dir = $this->container->getParameter('album.dir.photos');
        $dir_thumbs = $this->container->getParameter('album.dir.thumbnails');
        $albumPhotos = $em->getRepository('mycpBundle:albumPhoto')->findBy(array('alb_pho_album' => $id_album));
        $albumLangs = $em->getRepository('mycpBundle:albumLang')->findBy(array('album_lang_album' => $id_album));
        if ($albumLangs) {
            foreach ($albumLangs as $albumLang) {
                $em->remove($albumLang);
            }
        }

        foreach ($albumPhotos as $albumPhoto) {
            $photo = $em->getRepository('mycpBundle:photo')->find($albumPhoto->getAlbPhoPhoto()->getPhoId());
            FileIO::deleteFile($dir . $photo->getPhoName());
            FileIO::deleteFile($dir_thumbs . $photo->getPhoName());
            $destinationPhotoLangs = $em->getRepository('mycpBundle:photoLang')->findBy(array('pho_lang_id_photo' => $photo->getPhoId()));
            foreach ($destinationPhotoLangs as $destinationPhotoLang) {
                $em->remove($destinationPhotoLang);
            }
            $em->remove($albumPhoto);
            $em->remove($photo);

        }

        $album = $em->getRepository('mycpBundle:album')->find($id_album);
        $album_lang = $em->getRepository('mycpBundle:albumLang')->findBy(array('album_lang_album' => $id_album));
        $old_entity = $album_lang[0]->getAlbumLangName();
        $em->remove($album);
        $em->flush();
        $message = 'Álbum eliminado satisfactoriamente.';
        $this->get('session')->getFlashBag()->add('message_ok', $message);

        $service_log = $this->get('log');
        $service_log->saveLog('Delete entity, ' . $old_entity, BackendModuleName::MODULE_ALBUM);

        return $this->redirect($this->generateUrl('mycp_list_albums'));
    }

    public function edit_albumAction($id_album) {
        $service_security = $this->get('Secure');
        $service_security->verifyAccess();
        $errors = array();
        $em = $this->getDoctrine()->getEntityManager();
        $album = $em->getRepository('mycpBundle:album')->find($id_album);
        $languages = $em->getRepository('mycpBundle:lang')->getAll();
        $albumsLang = $em->getRepository('mycpBundle:albumLang')->findBy(array('album_lang_album' => $id_album));

        $data['name_album'] = $album->getAlbumName();
        //$data['name']=$destination->getDesName();
        $data['category'] = $album->getAlbumCategory()->getAlbCatId();
        $data['id_album'] = $id_album;
        if ($album->getAlbumActive() == 1)
            $data['public'] = TRUE;
        $a = 0;
        foreach ($languages as $language) {
            if (isset($albumsLang[$a])) {
                $data['name_' . $language['lang_id']] = $albumsLang[$a]->getAlbumLangName();
                $data['brief_desc_' . $language['lang_id']] = $albumsLang[$a]->getAlbumLangBriefDescription();
                $a++;
            }
        }

        $data['edit_album'] = TRUE;
        return $this->render('mycpBundle:album:new.html.twig', array('languages' => $languages, 'errors' => $errors, 'data' => $data));
    }

    public function list_photosAction($id_album, $items_per_page, Request $request) {
        $service_security = $this->get('Secure');
        $service_security->verifyAccess();
        $data = array();
        $page = 1;
        if (isset($_GET['page']))
            $page = $_GET['page'];
        $em = $this->getDoctrine()->getEntityManager();
        $paginator = $this->get('ideup.simple_paginator');
        $paginator->setItemsPerPage($items_per_page);
        $data['languages'] = $em->getRepository('mycpBundle:lang')->getAll();
        $photos = $paginator->paginate($em->getRepository('mycpBundle:albumPhoto')->getPhotosByIdAlbum($id_album))->getResult();
        foreach ($photos as $photo) {
            $data['description_photo_' . $photo->getAlbPhoPhoto()->getPhoId()] = $em->getRepository('mycpBundle:photoLang')->findBy(array('pho_lang_id_photo' => $photo->getAlbPhoPhoto()->getPhoId()));
        }
        $dir = $this->container->getParameter('album.dir.photos');
        $album = $em->getRepository('mycpBundle:album')->find($id_album);
        return $this->render('mycpBundle:album:photosList.html.twig', array(
                    'data' => $data,
                    'photos' => $photos,
                    'dir' => $dir,
                    'id_album' => $id_album,
                    'items_per_page' => $items_per_page,
                    'album' => $album,
                    'current_page' => $page,
                    'total_items' => $paginator->getTotalItems(),
        ));
    }

    public function new_photosAction($id_album, Request $request) {
        $service_security = $this->get('Secure');
        $service_security->verifyAccess();
        $data = array();
        $errors = array();
        $post = '';
        $em = $this->getDoctrine()->getEntityManager();
        $data['languages'] = $em->getRepository('mycpBundle:lang')->getAll();
        $dir = $this->container->getParameter('album.dir.photos');
        $dir_thumbs = $this->container->getParameter('album.dir.thumbnails');
        $photo_size = $this->container->getParameter('album.dir.photos.size');
        $thumbs_size = $this->container->getParameter('thumbnail.size');
        $dir_watermark = $this->container->getParameter('dir.watermark');
        $album = $em->getRepository('mycpBundle:album')->find($id_album);
        if ($request->getMethod() == 'POST') {

            $post = $request->request->getIterator()->getArrayCopy();
            $files = $request->files->get('images');

            if ($files['files'][0] === null) {
                $data['error'] = 'Debe seleccionar una imágen.';
            } else {
                $count_errors = 0;
                foreach ($files['files'] as $file) {
                    if ($file->getClientMimeType() != 'image/jpeg' && $file->getClientMimeType() != 'image/gif' && $file->getClientMimeType() != 'image/png') {
                        //$file->getClientSize()< 102400
                        $data['error'] = 'Extensión de fichero no admitida.';
                        $count_errors++;
                        break;
                    }
                }

                $not_blank_validator = new NotBlank();
                $not_blank_validator->message = "Este campo no puede estar vacío.";
                $array_keys = array_keys($post);
                $count = $errors_validation = 0;
                foreach ($post as $item) {
                    $errors[$array_keys[$count]] = $errors_validation = $this->get('validator')->validateValue($item, $not_blank_validator);
                    $count_errors+=count($errors_validation);
                    $count++;
                }

                if ($count_errors == 0) {
                    $album = $em->getRepository('mycpBundle:album')->find($id_album);
                    $langs = $em->getRepository('mycpBundle:lang')->findAll();
                    foreach ($files['files'] as $file) {
                        $albumPhoto = new albumPhoto();
                        $photo = new photo();
                        $fileName = uniqid('destination-') . '-photo.jpg';
                        $file->move($dir, $fileName);

                        //Creando thumbnail, redimensionando y colocando marca de agua
                        \MyCp\mycpBundle\Helpers\Images::createThumbnail($dir.$fileName, $dir_thumbs.$fileName, $thumbs_size);
                        \MyCp\mycpBundle\Helpers\Images::resize($dir.$fileName, $photo_size);
                        //\MyCp\mycpBundle\Helpers\Images::resizeAndWatermark($dir, $fileName, $dir_watermark, 480, $this->container);

                        $photo->setPhoName($fileName);
                        $albumPhoto->setAlbPhoAlbum($album);
                        $albumPhoto->setAlbPhoPhoto($photo);
                        $em->persist($albumPhoto);
                        $em->persist($photo);

                        foreach ($langs as $lang) {
                            $photoLang = new photoLang();
                            $photoLang->setPhoLangDescription($post['description_' . $lang->getLangId()]);
                            $photoLang->setPhoLangIdLang($lang);
                            $photoLang->setPhoLangIdPhoto($photo);
                            $em->persist($photoLang);
                        }
                    }
                    $em->flush();

                    $message = 'Ficheros subidos satisfactoriamente.';
                    $this->get('session')->getFlashBag()->add('message_ok', $message);

                    $service_log = $this->get('log');
                    $service_log->saveLog('Create photo, entity ' . $album->getAlbumName(), BackendModuleName::MODULE_ALBUM);

                    return $this->redirect($this->generateUrl('mycp_list_photos_album', array('id_album' => $id_album)));
                }
            }
        }
        return $this->render('mycpBundle:album:photosNew.html.twig', array(
                    'data' => $data,
                    'dir' => $dir,
                    'id_album' => $id_album,
                    'album' => $album,
                    'errors' => $errors,
                    'post' => $post));
    }

    public function delete_photoAction($id_album, $id_photo) {
        $service_security = $this->get('Secure');
        $service_security->verifyAccess();
        $dir = $this->container->getParameter('album.dir.photos');
        $dir_thumbs = $this->container->getParameter('album.dir.thumbnails');
        $em = $this->getDoctrine()->getEntityManager();
        $album = $em->getRepository('mycpBundle:album')->find($id_album);
        $data['languages'] = $em->getRepository('mycpBundle:lang')->getAll();
        $photo = $em->getRepository('mycpBundle:photo')->find($id_photo);
        $photoLangs = $em->getRepository('mycpBundle:photoLang')->findBy(array('pho_lang_id_photo' => $id_photo));
        foreach ($photoLangs as $photoLang)
            $em->remove($photoLang);
        $photoDel = $photo;
        $album_photo = $em->getRepository('mycpBundle:albumPhoto')->findBy(array('alb_pho_photo' => $id_photo));
        $em->remove($album_photo[0]);
        $em->remove($photo);
        $em->flush();
        FileIO::deleteFile($dir . $photoDel->getPhoName());
        FileIO::deleteFile($dir_thumbs . $photoDel->getPhoName());
        $message = 'El fichero se ha eliminado satisfactoriamente.';
        $this->get('session')->getFlashBag()->add('message_ok', $message);

        $service_log = $this->get('log');
        $service_log->saveLog('Delete photo, entity ' . $album->getAlbumName(), BackendModuleName::MODULE_ALBUM);

        return $this->redirect($this->generateUrl('mycp_list_photos_album', array('id_album' => $id_album)));
    }

    public function edit_photoAction($id_photo, $id_album, Request $request) {
        $service_security = $this->get('Secure');
        $service_security->verifyAccess();
        $post = '';
        $em = $this->getDoctrine()->getEntityManager();
        $errors = array();
        $album = $em->getRepository('mycpBundle:album')->find($id_album);
        $photo_langs = $em->getRepository('mycpBundle:photoLang')->findBy(array('pho_lang_id_photo' => $id_photo));
        $data = array();
        foreach ($photo_langs as $photo_lang) {
            $data['description_' . $photo_lang->getPhoLangIdLang()->getLangId()] = $photo_lang->getPhoLangDescription();
        }
        if ($request->getMethod() == 'POST') {
            $post = $request->request->getIterator()->getArrayCopy();
            $not_blank_validator = new NotBlank();
            $not_blank_validator->message = "Este campo no puede estar vacío.";
            $array_keys = array_keys($post);
            $count = $errors_validation = $count_errors = 0;
            foreach ($post as $item) {
                $errors[$array_keys[$count]] = $errors_validation = $this->get('validator')->validateValue($item, $not_blank_validator);
                $count_errors+=count($errors_validation);
                $count++;
            }
            if ($count_errors == 0) {
                foreach ($array_keys as $item) {
                    $id_lang = substr($item, 12, strlen($item));
                    $photo_lang = new \MyCp\mycpBundle\Entity\photoLang();
                    $photo_lang = $em->getRepository('mycpBundle:photoLang')->findBy(array('pho_lang_id_lang' => $id_lang, 'pho_lang_id_photo' => $id_photo));
                    if (isset($photo_lang[0])) {
                        $photo_lang[0]->setPhoLangDescription($post['description_' . $id_lang]);
                        $em->persist($photo_lang[0]);
                    }
                }
                $em->flush();
                $message = 'Imágen actualizada satisfactoriamente.';
                $this->get('session')->getFlashBag()->add('message_ok', $message);

                $service_log = $this->get('log');
                $service_log->saveLog('Edit photo, entity ' . $album->getAlbumName(), BackendModuleName::MODULE_ALBUM);

                return $this->redirect($this->generateUrl('mycp_list_photos_album', array('id_album' => $id_album)));
            }
        }
        $photo = $em->getRepository('mycpBundle:photo')->find($id_photo);
        $data['languages'] = $em->getRepository('mycpBundle:lang')->getAll();
        return $this->render('mycpBundle:album:photoEdit.html.twig', array(
                    'errors' => $errors,
                    'data' => $data,
                    'id_photo' => $id_photo,
                    'photo' => $photo,
                    'id_album' => $id_album,
                    'post' => $post));
    }

}
