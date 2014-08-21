<?php

namespace MyCp\mycpBundle\Controller;

use MyCp\mycpBundle\Entity\destinationCategory;
use MyCp\mycpBundle\Entity\destinationCategoryLang;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints\NotBlank;

use MyCp\mycpBundle\Entity\destination;
use MyCp\mycpBundle\Entity\photo;
use MyCp\mycpBundle\Entity\photoLang;
use MyCp\mycpBundle\Entity\destinationPhoto;
use MyCp\mycpBundle\Form\categoryType;
use MyCp\mycpBundle\Entity\log;
use MyCp\mycpBundle\Helpers\BackendModuleName;

class BackendDestinationController extends Controller
{
    function new_categoryAction(Request $request) {
        $service_security = $this->get('Secure');
        $service_security->verifyAccess();
        $em = $this->getDoctrine()->getEntityManager();
        $languages = $em->getRepository('mycpBundle:lang')->findAll();
        $form = $this->createForm(new categoryType(array('languages' => $languages,'des_photo'=>true)));

        if ($request->getMethod() == 'POST') {
            $form->bind($request);
            if ($form->isValid()) {
                $category = new destinationCategory();
                $em->persist($category);

                $post = $form->getData();
                foreach ($languages as $language) {
                    $category_lang = new destinationCategoryLang();
                    $category_lang->setDesCatIdCat($category);
                    $category_lang->setDesCatIdLang($language);
                    $category_lang->setDesCatName($post['lang' . $language->getLangId()]);
                    $em->persist($category_lang);
                }

                $dir=$this->container->getParameter('destination.cat.dir.icons');
                $file = $request->files->get('mycp_mycpbundle_categorytype');
                if (isset($file['photo'])) {
                    $photo = new photo();
                    $fileName = uniqid('dest-') . '-icon.jpg';
                    $file['photo']->move($dir, $fileName);
                    $category->setDesIcon($fileName);
                }
                if (isset($file['photo_atraction'])) {
                    $photo = new photo();
                    $fileName = uniqid('dest-') . '-prov-icon.jpg';
                    $file['photo_atraction']->move($dir, $fileName);
                    $category->setDesIconProvMap($fileName);
                }

                $em->flush();

                $message = 'Categoría añadida satisfactoriamente.';
                $this->get('session')->getFlashBag()->add('message_ok', $message);

                $service_log = $this->get('log');
                $service_log->saveLog('Create category, ' . $post['lang' . $languages[0]->getLangId()], BackendModuleName::MODULE_DESTINATION);

                return $this->redirect($this->generateUrl('mycp_list_category_destination'));
            }
        }
        return $this->render('mycpBundle:destination:categoryNew.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    function edit_categoryAction($id_category, Request $request) {
        $service_security = $this->get('Secure');
        $service_security->verifyAccess();
        $em = $this->getDoctrine()->getEntityManager();
        $languages = $em->getRepository('mycpBundle:lang')->findAll();
        $dest_cat_lang = $em->getRepository('mycpBundle:destinationCategoryLang')->findBy(array('des_cat_id_cat'=>$id_category));
        if ($request->getMethod() == 'POST') {
            $form = $this->createForm(new categoryType(array('languages' => $languages,'des_photo'=>true)));
        } else {
            $dest_cat = $em->getRepository('mycpBundle:destinationCategoryLang')->findBy(array('des_cat_id_cat' => $id_category));
            $form = $this->createForm(new categoryType(array('languages' => $languages, 'des_cat_lang' => $dest_cat,'des_photo'=>true)));
        }


        if ($request->getMethod() == 'POST') {
            $form->bind($request);
            if ($form->isValid()) {

                $post = $form->getData();

                foreach ($languages as $language) {
                    $dest_cat_lang = new destinationCategoryLang();
                    $dest_cat_lang = $em->getRepository('mycpBundle:destinationCategoryLang')->findBy(array('des_cat_id_lang' => $language,'des_cat_id_cat'=>$id_category));
                    $dest_cat_lang[0]->setDesCatName($post['lang' . $language->getLangId()]);
                    $em->persist($dest_cat_lang[0]);
                }

                $category=$em->getRepository('mycpBundle:destinationCategory')->find($id_category);

                $dir=$this->container->getParameter('destination.cat.dir.icons');
                $file = $request->files->get('mycp_mycpbundle_categorytype');
                if (isset($file['photo'])) {
                    $photo = new photo();
                    $fileName = uniqid('dest-') . '-icon.jpg';
                    $file['photo']->move($dir, $fileName);
                    @unlink($dir . $category->getDesIcon());
                    $category->setDesIcon($fileName);
                }
                if (isset($file['photo_atraction'])) {
                    $photo = new photo();
                    $fileName = uniqid('dest-') . '-prov-icon.jpg';
                    $file['photo_atraction']->move($dir, $fileName);
                    @unlink($dir . $category->getDesIconProvMap());
                    $category->setDesIconProvMap($fileName);
                }

                $em->flush();
                $message = 'Categoría actualizada satisfactoriamente.';
                $this->get('session')->getFlashBag()->add('message_ok', $message);

                $service_log = $this->get('log');
                $service_log->saveLog('Edit category, ' . $post['lang' . $languages[0]->getLangId()], BackendModuleName::MODULE_DESTINATION);

                return $this->redirect($this->generateUrl('mycp_list_category_destination'));
            }
        }


        return $this->render('mycpBundle:destination:categoryNew.html.twig', array(
            'form' => $form->createView(), 'edit_category' => $id_category, 'name_category' => $dest_cat_lang[0]->getDesCatName()
        ));
    }

    function delete_categoryAction($id_category) {
        $service_security = $this->get('Secure');
        $service_security->verifyAccess();
        $em = $this->getDoctrine()->getEntityManager();
        $category = $em->getRepository('mycpBundle:destinationCategory')->find($id_category);
        $category_langs = $em->getRepository('mycpBundle:destinationCategoryLang')->findby(array('des_cat_id_cat' => $category));
        $destinations= $em->getRepository('mycpBundle:destination')->findAll();
        foreach($destinations as $destination)
        {
            if($destination->getDesCategories()->contains($category))
            {
                $destination->getDesCategories()->removeElement($category);
            }
            $em->persist($destination);

        }
        foreach($category_langs as $cat_lang)
        {
            $em->remove($cat_lang);
        }
        $old_cat_lang = $category_langs[0]->getDesCatName();

        if ($category)
        {
            $dir=$this->container->getParameter('destination.cat.dir.icons');
            @unlink($dir.$category->getDesIcon());
            $em->remove($category);
        }
        $em->flush();


        $message = 'Categoría eliminada satisfactoriamente.';
        $this->get('session')->getFlashBag()->add('message_ok', $message);

        $service_log = $this->get('log');
        $service_log->saveLog('Delete category, ' . $old_cat_lang, BackendModuleName::MODULE_DESTINATION);

        return $this->redirect($this->generateUrl('mycp_list_category_destination'));
    }

    function list_categoryAction($items_per_page, Request $request) {
        $service_security = $this->get('Secure');
        $service_security->verifyAccess();
        $em = $this->getDoctrine()->getEntityManager();
        $languages = $em->getRepository('mycpBundle:lang')->findAll();

        $paginator = $this->get('ideup.simple_paginator');
        $paginator->setItemsPerPage($items_per_page);
        $categories = $paginator->paginate($em->getRepository('mycpBundle:destinationCategoryLang')->getCategories())->getResult();
        $page = 1;
        if (isset($_GET['page']))
            $page = $_GET['page'];
        return $this->render('mycpBundle:destination:categoryList.html.twig', array(
            'categories' => $categories,
            'items_per_page' => $items_per_page,
            'total_items' => $paginator->getTotalItems(),
            'current_page' => $page,
        ));
    }

    public function new_destinationAction(Request $request)
    {
        $service_security= $this->get('Secure');
        $service_security->verifyAccess();

        $errors = array();
        $post = $request->request->getIterator()->getArrayCopy();
        $em = $this->getDoctrine()->getEntityManager();

        if ($request->getMethod() == 'POST') {
            $not_blank_validator = new NotBlank();
            $not_blank_validator->message="Este campo no puede estar vacío.";
            $array_keys=array_keys($post);
            $count=$errors_validation=$count_errors= 0;

            if($request->get('public')=='on')
            {
                foreach ($post as $item) {

                    if($array_keys[$count]!='ownership_address_municipality' and strpos($array_keys[$count], 'brief_')!==0 and strpos($array_keys[$count], 'desc_')!==0)
                    {
                        $errors[$array_keys[$count]] = $errors_validation=$this->get('validator')->validateValue($item, $not_blank_validator);
                        $count_errors+=count($errors_validation);
                    }
                    $count++;
                }
            }
            else
            {
                $errors['name'] = $errors_validation=$this->get('validator')->validateValue($request->get('name'), $not_blank_validator);
                $count_errors+=count($errors_validation);

                $errors['ownership_address_province'] = $errors_validation=$this->get('validator')->validateValue($request->get('ownership_address_province'), $not_blank_validator);
                $count_errors+=count($errors_validation);
            }

            if ($count_errors == 0) {
                //save into database

                if($request->request->get('edit_destination'))
                {
                    $em->getRepository('mycpBundle:destination')->edit_destination($post);
                    $message='Destino actualizado satisfactoriamente.';

                    $service_log= $this->get('log');
                    $service_log->saveLog('Edit entity '.$post['name'],BackendModuleName::MODULE_DESTINATION);
                }
                else
                {
                    $em->getRepository('mycpBundle:destination')->insert_destination($post);
                    $message='Destino añadido satisfactoriamente.';

                    $service_log= $this->get('log');
                    $service_log->saveLog('Create entity '.$post['name'],BackendModuleName::MODULE_DESTINATION);
                }

                $this->get('session')->getFlashBag()->add('message_ok',$message);
                return $this->redirect($this->generateUrl('mycp_list_destination'));
            }


            if($request->request->get('edit_destination'))
            {
                $id_destination=$request->request->get('edit_destination');
                $destination=$em->getRepository('mycpBundle:destination')->find($id_destination);
                $post['name_destination']=$destination->getDesName();
                $post['name']=$request->request->get('name');
                $post['id_destination']=$id_destination;
            }
        }
        $categories= $em->getRepository('mycpBundle:destinationCategoryLang')->getCategories('object');
        $languages = $em->getRepository('mycpBundle:lang')->get_all_languages();
        return $this->render('mycpBundle:destination:new.html.twig', array('languages' => $languages, 'errors' => $errors, 'data' => $post,'categories'=>$categories));

    }

    public function list_destinationAction($items_per_page, Request $request)
    {
        $service_security= $this->get('Secure');
        $service_security->verifyAccess();

        $page=1;
        $data='';
        $filter_active=$request->get('filter_active');
        $filter_name=$request->get('filter_name');
        $sort_by=$request->get('sort_by');
        $filter_province=$request->get('filter_province');
        $filter_municipality=$request->get('filter_municipality');
        if($request->getMethod()=='POST' && $filter_active=='null' && $filter_name=='null' && $filter_province=='null' && $filter_municipality=='null')
        {
            $message='Debe llenar al menos un campo para filtrar.';
            $this->get('session')->getFlashBag()->add('message_error_local',$message);
            return $this->redirect($this->generateUrl('mycp_list_destination'));
        }
        if($filter_name=='null') $filter_name='';
        if(isset($_GET['page']))$page=$_GET['page'];
        $paginator = $this->get('ideup.simple_paginator');
        $paginator->setItemsPerPage($items_per_page);
        $em = $this->getDoctrine()->getEntityManager();
        $destinations= $paginator->paginate($em->getRepository('mycpBundle:destination')->get_all_destinations($filter_name,$filter_active,$filter_province, $filter_municipality,$sort_by))->getResult();
        //var_dump($destinations[0]->getDesLocMunicipality()->getMunName()); exit();

        foreach($destinations as $destination)
        {
            $photos=$em->getRepository('mycpBundle:destinationPhoto')->findBy(array('des_pho_destination'=>$destination->getDesLocDestination()->getDesId()));
            $data[$destination->getDesLocDestination()->getDesId().'_photo_count']=count($photos);
        }

        $service_log= $this->get('log');
        $service_log->saveLog('Visit module',BackendModuleName::MODULE_DESTINATION);

        return $this->render('mycpBundle:destination:list.html.twig', array(
            'destinations' => $destinations,
            'photo_count'=>$data,
            'items_per_page'=>$items_per_page,
            'current_page'=>$page,
            'total_items'=>$paginator->getTotalItems(),
            'filter_name'=>$filter_name,
            'filter_active'=>$filter_active,
            'filter_province'=>$filter_province,
            'filter_municipality'=>$filter_municipality,
            'sort_by'=>$sort_by
        ));
    }

    public function delete_destinationAction($id_destination)
    {
        $service_security= $this->get('Secure');
        $service_security->verifyAccess();

        $em = $this->getDoctrine()->getEntityManager();
        $dir=$this->container->getParameter('destination.dir.photos');
        $dir_thumbs=$this->container->getParameter('destination.dir.thumbnails');
        $destinationLangs=$em->getRepository('mycpBundle:destinationLang')->findBy(array('des_lang_destination'=>$id_destination));
        $destinationPhotos=$em->getRepository('mycpBundle:destinationPhoto')->findBy(array('des_pho_destination'=>$id_destination));
        $destinationFavorites=$em->getRepository('mycpBundle:favorite')->findBy(array('favorite_destination'=>$id_destination));
        $userHistories=$em->getRepository('mycpBundle:userHistory')->findBy(array('user_history_destination'=>$id_destination));
        $destination_location=$em->getRepository('mycpBundle:destinationLocation')->findBy(array('des_loc_destination'=>$id_destination));

        foreach($destinationLangs as $destinationLang)
        {
            $em->remove($destinationLang);
        }

        foreach($destination_location as $desloc)
        {
            $em->remove($desloc);
        }


        foreach($userHistories as $userHist)
        {
            $em->remove($userHist);
        }

        foreach($destinationFavorites as $favs)
        {
            $em->remove($favs);
        }
        foreach($destinationPhotos as $destinationPhoto)
        {
            $photo=$em->getRepository('mycpBundle:photo')->find($destinationPhoto->getDesPhoPhoto()->getPhoId());
            @unlink($dir.$photo->getPhoName());
            @unlink($dir_thumbs.$photo->getPhoName());
            $destinationPhotoLangs=$em->getRepository('mycpBundle:photoLang')->findBy(array('pho_lang_id_photo'=>$photo->getPhoId()));
            foreach($destinationPhotoLangs as $destinationPhotoLang)
            {
                $em->remove($destinationPhotoLang);
            }

            $em->remove($destinationPhoto);
            $em->remove($photo);
        }


        $destination=$em->getRepository('mycpBundle:destination')->find($id_destination);
        $name_destination=$destination->getDesName();
        if($destination)
            $em->remove($destination);
        $em->flush();
        $message='Destino eliminado satisfactoriamente.';
        $this->get('session')->getFlashBag()->add('message_ok',$message);

        $service_log= $this->get('log');
        $service_log->saveLog('Delete entity '.$name_destination,BackendModuleName::MODULE_DESTINATION);

        return $this->redirect($this->generateUrl('mycp_list_destination'));
    }

    public function edit_destinationAction($id_destination)
    {
        $service_security= $this->get('Secure');
        $service_security->verifyAccess();

        $errors = array();
        $em = $this->getDoctrine()->getEntityManager();
        $destination=$em->getRepository('mycpBundle:destination')->find($id_destination);
        $languages = $em->getRepository('mycpBundle:lang')->get_all_languages();
        $destinationsLang=$em->getRepository('mycpBundle:destinationLang')->findBy(array('des_lang_destination'=>$id_destination));
        $destinationsKeywordLang=$em->getRepository('mycpBundle:destinationKeywordLang')->findBy(array('dkl_destination'=>$id_destination));
        $destinationsLocation=$em->getRepository('mycpBundle:destinationLocation')->findBy(array('des_loc_destination'=>$id_destination));

        //var_dump($destinationsLocation[0]->getDesLocMunicipality()->getMunId()); exit();

        $data['name_destination']=$destination->getDesName();
        $data['name']=$destination->getDesName();
        $data['poblation']=$destination->getDesPoblation();
        $data['ref_place']=$destination->getDesRefPlace();
        $data['geolocate_x']=$destination->getDesGeolocateX();
        $data['geolocate_y']=$destination->getDesGeolocateY();
        $data['cat_location_x']=$destination->getDesCatLocationX();
        $data['cat_location_y']=$destination->getDesCatLocationY();
        $data['cat_location_prov_x']=$destination->getDesCatLocationProvX();
        $data['cat_location_prov_y']=$destination->getDesCatLocationProvY();
        $data['id_destination']=$id_destination;
        $data['ownership_address_province']=$destinationsLocation[0]->getDesLocProvince()->getProvId();
        if($data['ownership_address_municipality']=$destinationsLocation[0]->getDesLocMunicipality())
        $data['ownership_address_municipality']=$destinationsLocation[0]->getDesLocMunicipality()->getMunId();

        if($destination->getDesActive()==1)
            $data['public']=TRUE;
        $a=0;
        foreach($languages as $language)
        {
            if(isset($destinationsLang[$a]))
            {
                $data['brief_'.$language['lang_id']]=$destinationsLang[$a]->getDesLangBrief();
                $data['desc_'.$language['lang_id']]=$destinationsLang[$a]->getDesLangDesc();

            }

            if(isset($destinationsKeywordLang[$a]))
            {
                $data['seo_keyword_'.$language['lang_id']]=$destinationsKeywordLang[$a]->getDklKeywords();
                $data['seo_description_'.$language['lang_id']]=$destinationsKeywordLang[$a]->getDklDescription();
            }
            $a++;

        }

        $des_categories = $destination->getDesCategories();
        foreach($des_categories as $cat)
        {
            $data['category_'.$cat->getDesCatId()]=true;
        }

        $data['edit_destination']=TRUE;
        $categories= $em->getRepository('mycpBundle:destinationCategoryLang')->getCategories('object');

        return $this->render('mycpBundle:destination:new.html.twig', array('languages' => $languages, 'errors' => $errors, 'data' => $data,'categories'=>$categories));
    }

    public function list_photosAction($id_destination,$items_per_page,Request $request)
    {
        $service_security= $this->get('Secure');
        $service_security->verifyAccess();
        $data=array();
        $page=1;
        if(isset($_GET['page']))$page=$_GET['page'];
        $em = $this->getDoctrine()->getEntityManager();
        $data['languages']= $em->getRepository('mycpBundle:lang')->get_all_languages();
        $paginator = $this->get('ideup.simple_paginator');
        $paginator->setItemsPerPage($items_per_page);
        $photos=$paginator->paginate($em->getRepository('mycpBundle:destinationPhoto')->getByDestination($id_destination))->getResult();
        foreach($photos as $photo)
        {
            $data['description_photo_'.$photo->getDesPhoPhoto()->getPhoId()]=$em->getRepository('mycpBundle:photoLang')->findBy(array('pho_lang_id_photo'=>$photo->getDesPhoPhoto()->getPhoId()));
        }

        $dir=$this->container->getParameter('destination.dir.photos');
        $destination=$em->getRepository('mycpBundle:destination')->find($id_destination);
        return $this->render('mycpBundle:destination:photosList.html.twig',array(
            'data'=>$data,
            'photos'=>$photos,
            'dir'=>$dir,
            'id_destination'=>$id_destination,
            'destination'=>$destination,
            'items_per_page'=>$items_per_page,
            'current_page'=>$page,
            'total_items'=>$paginator->getTotalItems(),
        ));
    }

    public function new_photosAction($id_destination,Request $request)
    {
        $service_security= $this->get('Secure');
        $service_security->verifyAccess();
        $data=array();
        $errors=array();
        $post='';
        $em = $this->getDoctrine()->getEntityManager();
        $data['languages']= $em->getRepository('mycpBundle:lang')->get_all_languages();
        $dir=$this->container->getParameter('destination.dir.photos');
        $dir_thumbs=$this->container->getParameter('destination.dir.thumbnails');
        $dir_watermark=$this->container->getParameter('dir.watermark');
        $photo_size = $this->container->getParameter('destination.dir.photos.size');
        $thumbs_size = $this->container->getParameter('thumbnail.size');

        if ($request->getMethod() == 'POST') {
            $post = $request->request->getIterator()->getArrayCopy();
            $files = $request->files->get('images');
            //var_dump($files);
            if($files['files'][0]===null)
            {
                $data['error']='Debe seleccionar una imágen.';
            }
            else
            {
                $count_errors= 0;
                foreach($files['files'] as $file)
                {
                    if( $file->getClientMimeType()!='image/jpeg' && $file->getClientMimeType()!='image/gif' && $file->getClientMimeType()!='image/png')
                    {
                            //$file->getClientSize()< 102400
                            $data['error']='Extensión de fichero no admitida.';
                            $count_errors++;
                            break;
                    }
                }

                $not_blank_validator = new NotBlank();
                $not_blank_validator->message="Este campo no puede estar vacío.";
                $array_keys=array_keys($post);
                $count=$errors_validation= 0;
                foreach ($post as $item) {
                    $errors[$array_keys[$count]] = $errors_validation=$this->get('validator')->validateValue($item, $not_blank_validator);
                    $count_errors+=count($errors_validation);
                    $count++;
                }

                if ($count_errors == 0) {
                    $destination=$em->getRepository('mycpBundle:destination')->find($id_destination);
                    $langs=$em->getRepository('mycpBundle:lang')->findAll();
                    foreach($files['files'] as $file)
                    {
                        $destinationPhoto= new destinationPhoto();
                        $photo= new photo();
                        $fileName = uniqid('destination-').'-photo.jpg';
                        $file->move($dir, $fileName);
                        //Creando thumbnail, redimensionando y colocando marca de agua
                        \MyCp\mycpBundle\Helpers\Images::createThumbnail($dir.$fileName, $dir_thumbs.$fileName, $thumbs_size);
                        //\MyCp\mycpBundle\Helpers\Images::resizeAndWatermark($dir.$fileName, $dir_watermark, 480);
                        \MyCp\mycpBundle\Helpers\Images::resize($dir.$fileName, $photo_size);

                        $photo->setPhoName($fileName);
                        $destinationPhoto->setDesPhoDestination($destination);
                        $destinationPhoto->setDesPhoPhoto($photo);
                        $em->persist($destinationPhoto);
                        $em->persist($photo);

                        foreach($langs as $lang)
                        {
                            $photoLang= new photoLang();
                            $photoLang->setPhoLangDescription($post['description_'.$lang->getLangId()]);
                            $photoLang->setPhoLangIdLang($lang);
                            $photoLang->setPhoLangIdPhoto($photo);
                            $em->persist($photoLang);
                        }
                    }
                    $em->flush();

                    $service_log= $this->get('log');
                    $service_log->saveLog('Create photo, entity '.$destination->getDesName(),BackendModuleName::MODULE_DESTINATION);

                    $message='Ficheros subidos satisfactoriamente.';
                    $this->get('session')->getFlashBag()->add('message_ok',$message);
                    return $this->redirect($this->generateUrl('mycp_list_photos_destination',array('id_destination'=>$id_destination)));




             /*   if( $file->getClientMimeType()!='image/jpeg' && $file->getClientMimeType()!='image/gif' && $file->getClientMimeType()!='image/png')
                {
                    //$file->getClientSize()< 102400
                    $data['error']='La extensión del fichero no es admitida.';
                }
                else
                {

                    $not_blank_validator = new NotBlank();
                    $not_blank_validator->message="Este campo no puede estar vacío.";
                    $array_keys=array_keys($post);
                    $count=$errors_validation=$count_errors= 0;
                    foreach ($post as $item) {
                        $errors[$array_keys[$count]] = $errors_validation=$this->get('validator')->validateValue($item, $not_blank_validator);
                        $count_errors+=count($errors_validation);
                        $count++;
                    }

                    if ($count_errors == 0) {
                        $file->move($dir, $fileName);
                        $destination= new destination();
                        $destination=$em->getRepository('mycpBundle:destination')->find($id_destination);
                        $photo= new photo();
                        $destinationPhoto= new destinationPhoto();
                        $photo->setPhoName($fileName);
                        //$photo->setPhoDestination($destination);
                        $destinationPhoto->setDesPhoDestination($destination);
                        $destinationPhoto->setDesPhoPhoto($photo);
                        $em->persist($destinationPhoto);
                        $em->persist($photo);
                        $em->flush();
                        $langs=$em->getRepository('mycpBundle:lang')->findAll();
                        foreach($langs as $lang)
                        {
                            $photoLang= new photoLang();
                            $photoLang->setPhoLangDescription($post['description_'.$lang->getLangId()]);
                            $photoLang->setPhoLangIdLang($lang);
                            $photoLang->setPhoLangIdPhoto($photo);
                            $em->persist($photoLang);
                        }
                        $em->flush();

                        $service_log= $this->get('log');
                        $service_log->saveLog('Create photo, entity '.$destination->getDesName(),BackendModuleName::MODULE_DESTINATION);

                        $message='El fichero se ha subido satisfactoriamente.';
                        $this->get('session')->getFlashBag()->add('message_ok',$message);
                        return $this->redirect($this->generateUrl('mycp_list_photos_destination',array('id_destination'=>$id_destination)));

                    }*/
                }
            }
        }
        $destination=$em->getRepository('mycpBundle:destination')->find($id_destination);
        return $this->render('mycpBundle:destination:photosNew.html.twig',array(
            'data'=>$data,
            'dir'=>$dir,
            'id_destination'=>$id_destination,
            'destination'=>$destination,
            'errors'=>$errors,'post'=>$post
        ));
    }

     public function delete_photoAction($id_destination,$id_photo)
     {
         $service_security= $this->get('Secure');
         $service_security->verifyAccess();
         $dir=$this->container->getParameter('destination.dir.photos');
         $dir_thumbnails=$this->container->getParameter('destination.dir.thumbnails');
         $em = $this->getDoctrine()->getEntityManager();
         $data['languages']= $em->getRepository('mycpBundle:lang')->get_all_languages();
         $destination= $em->getRepository('mycpBundle:destination')->find($id_destination);
         $photo=$em->getRepository('mycpBundle:photo')->find($id_photo);
         $photoLangs=$em->getRepository('mycpBundle:photoLang')->findBy(array('pho_lang_id_photo'=>$id_photo));
         foreach($photoLangs as $photoLang)
             $em->remove($photoLang);
         $photoDel=$photo;
         $destination_photo=$em->getRepository('mycpBundle:destinationPhoto')->findBy(array('des_pho_photo'=>$id_photo));
         $em->remove($destination_photo[0]);
         $em->remove($photo);
         $em->flush();
         @unlink($dir.$photoDel->getPhoName());
         @unlink($dir_thumbnails.$photoDel->getPhoName());
         $message='El fichero se ha eliminado satisfactoriamente.';
         $this->get('session')->getFlashBag()->add('message_ok',$message);

         $service_log= $this->get('log');
         $service_log->saveLog('Delete photo, entity '.$destination->getDesName(),BackendModuleName::MODULE_DESTINATION);

         return $this->redirect($this->generateUrl('mycp_list_photos_destination',array('id_destination'=>$id_destination)));
     }

    public function get_all_municipalityAction($data)
    {
        //var_dump($data);
        $em = $this->getDoctrine()->getEntityManager();
        $municipalities = $em->getRepository('mycpBundle:municipality')->findAll();
        return $this->render('mycpBundle:utils:municipality.html.twig',array('municipalities'=>$municipalities,'data'=>$data));
    }

    public function edit_photoAction($id_photo,$id_destination,Request $request)
    {
        $service_security= $this->get('Secure');
        $service_security->verifyAccess();
        $post='';
        $em = $this->getDoctrine()->getEntityManager();
        $errors=array();
        $photo_langs= $em->getRepository('mycpBundle:photoLang')->findBy(array('pho_lang_id_photo'=>$id_photo));
        $data=array();
        foreach($photo_langs as $photo_lang)
        {
            $data['description_'.$photo_lang->getPhoLangIdLang()->getLangId()]=$photo_lang->getPhoLangDescription();
        }
        if ($request->getMethod() == 'POST') {
            $post = $request->request->getIterator()->getArrayCopy();
            $not_blank_validator = new NotBlank();
            $not_blank_validator->message="Este campo no puede estar vacío.";
            $array_keys=array_keys($post);
            $count=$errors_validation=$count_errors= 0;
            foreach ($post as $item) {
                $errors[$array_keys[$count]] = $errors_validation=$this->get('validator')->validateValue($item, $not_blank_validator);
                $count_errors+=count($errors_validation);
                $count++;
            }
            if ($count_errors == 0) {
                foreach($array_keys as $item)
                {
                    $id_lang=substr($item, 12, strlen($item));
                    $photo_lang=new  \MyCp\mycpBundle\Entity\photoLang();
                    $photo_lang=$em->getRepository('mycpBundle:photoLang')->findBy(array('pho_lang_id_lang'=>$id_lang,'pho_lang_id_photo'=>$id_photo));
                    if(isset($photo_lang[0]))
                    {
                        $photo_lang[0]->setPhoLangDescription($post['description_'.$id_lang]);
                        $em->persist($photo_lang[0]);
                    }

                }
                $em->flush();
                $message='Imágen actualizada satisfactoriamente.';
                $this->get('session')->getFlashBag()->add('message_ok',$message);
                $destination= $em->getRepository('mycpBundle:destination')->find($id_destination);

                $service_log= $this->get('log');
                $service_log->saveLog('Edit photo, entity '.$destination->getDesName(),BackendModuleName::MODULE_DESTINATION);

                return $this->redirect($this->generateUrl('mycp_list_photos_destination',array('id_destination'=>$id_destination)));

            }
        }
        $photo= $em->getRepository('mycpBundle:photo')->find($id_photo);
        $data['languages']= $em->getRepository('mycpBundle:lang')->get_all_languages();
        return $this->render('mycpBundle:destination:photoEdit.html.twig',array(
            'errors'=>$errors,
            'data'=>$data,
            'id_photo'=>$id_photo,
            'photo'=>$photo,
            'id_destination'=>$id_destination,
            'post'=>$post));
    }

    public function set_orderAction($ids,Request $request)
    {
        $ids=str_replace(' ','',$ids);
        $ids_array= explode(",", $ids);
        $em = $this->getDoctrine()->getEntityManager();
        $order=1;
        foreach($ids_array as $id)
        {
            $destination=new destination();
            $destination=$em->getRepository('mycpBundle:destination')->find($id);
            $destination->setDesOrder($order);
            $em->persist($destination);
            $em->flush();
            $order++;
        }
        return new Response($ids);
    }




}
