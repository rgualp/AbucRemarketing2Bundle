<?php

namespace MyCp\mycpBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

use MyCp\mycpBundle\Entity\lang;
use MyCp\mycpBundle\Entity\photo;
use MyCp\mycpBundle\Entity\log;
use MyCp\mycpBundle\Entity\langFlag;
use MyCp\mycpBundle\Form\langType;
use MyCp\mycpBundle\Helpers\BackendModuleName;



class BackendLanguageController extends Controller
{
    public function list_languagesAction($items_per_page)
    {
        $service_security= $this->get('Secure');
        $service_security->verifyAccess();
        $page=1;
        if(isset($_GET['page']))$page=$_GET['page'];
        $em = $this->getDoctrine()->getEntityManager();
        $paginator = $this->get('ideup.simple_paginator');
        $paginator->setItemsPerPage($items_per_page);
        $languages=$paginator->paginate($em->getRepository('mycpBundle:lang')->findAll())->getResult();

        $service_log= $this->get('log');
        $service_log->saveLog('Visit',BackendModuleName::MODULE_LANGUAGE);

        return $this->render('mycpBundle:language:list.html.twig',array(
            'languages'=>$languages,
            'items_per_page'=>$items_per_page,
            'current_page'=>$page,
            'total_items'=>$paginator->getTotalItems()
        ));
    }

    public function edit_languageAction($id_language,Request $request)
    {
        $service_security= $this->get('Secure');
        $service_security->verifyAccess();
        $em = $this->getDoctrine()->getEntityManager();
        $lang=$em->getRepository('mycpBundle:lang')->find($id_language);
        $lang_flag=$em->getRepository('mycpBundle:langFlag')->findBy(array('lang_flag_lang_id'=>$id_language));
       // var_dump($lang_flag[0]->getLangFlagPhoto());
        $data=array();
        $data['lang_flag']=$lang_flag;
        $form=$this->createForm( new langType(),$lang);
        if($request->getMethod()=='POST')
        {
            $file = $request->files->get('photo');
            $form->handleRequest($request);
            $post_form=$request->get('mycp_mycpbundle_langtype');
            if($form->isValid())
            {
                if($file and $file->getClientMimeType()!='image/jpeg' && $file->getClientMimeType()!='image/gif' && $file->getClientMimeType()!='image/png')
                {
                    $data['error']='La extensión del fichero no es admitida.';
                }
                else
                {
                    if($file)
                    {
                        $dir=$this->container->getParameter('language.dir.photos');
                        $lang_flag=$em->getRepository('mycpBundle:langFlag')->findBy(array('lang_flag_lang_id'=>$id_language));
                        $fileName='';
                        if(isset($lang_flag[0]))
                        {
                            $fileName =$lang_flag[0]->getLangFlagPhoto()->getPhoName();
                        }
                        else
                        {
                            $fileName = uniqid('flag-').'-photo.jpg';
                        }
                        $file->move($dir, $fileName);
                    }

                    $em->persist($lang);
                    $em->flush();
                    $message='Idioma actualizado satisfactoriamente.';
                    $this->get('session')->getFlashBag()->add('message_ok',$message);

                    $service_log= $this->get('log');
                    $service_log->saveLog('Edit entity '.$post_form['lang_name'],BackendModuleName::MODULE_LANGUAGE);

                    return $this->redirect($this->generateUrl('mycp_list_languages'));
                }
            }

        }
        return $this->render('mycpBundle:language:new.html.twig',array('form'=>$form->createView(),'data'=>$data,'id_language'=>$id_language,'edit_lang'=>true));
    }

    public function new_languageAction(Request $request)
    {
        $service_security= $this->get('Secure');
        $service_security->verifyAccess();
        $lang=new lang();
        $data=array();
        $form=$this->createForm( new langType(),$lang);
        if($request->getMethod()=='POST')
        {
            $post_form=$request->get('mycp_mycpbundle_langtype');
            $file = $request->files->get('photo');
            $form->handleRequest($request);
            if($form->isValid())
            {
                if($file and $file->getClientMimeType()!='image/jpeg' && $file->getClientMimeType()!='image/gif' && $file->getClientMimeType()!='image/png')
                {
                    $data['error']='La extensión del fichero no es admitida.';
                }
                else if($file)
                {
                    $dir=$this->container->getParameter('language.dir.photos');
                    $fileName = uniqid('flag-').'-photo.jpg';
                    $file->move($dir, $fileName);

                    $em = $this->getDoctrine()->getEntityManager();
                    $photo= new photo();
                    $photo->setPhoName($fileName);
                    $lang_flag= new langFlag();
                    $lang_flag->setLangFlagPhoto($photo);
                    $lang_flag->setLangFlagLangId($lang);

                    $em->persist($lang);
                    $em->persist($photo);
                    $em->persist($lang_flag);
                    $em->flush();
                    $message='Idioma añadido satisfactoriamente.';
                    $this->get('session')->getFlashBag()->add('message_ok',$message);

                    $service_log= $this->get('log');
                    $service_log->saveLog('Create entity '.$post_form['lang_name'],BackendModuleName::MODULE_LANGUAGE);

                    return $this->redirect($this->generateUrl('mycp_list_languages'));
                }
            }
            if($file==null)
                $data['error']='Debe seleccionar una imágen.';
        }
        return $this->render('mycpBundle:language:new.html.twig',array('form'=>$form->createView(),'data'=>$data));
    }

    public function delete_languageAction($id_language)
    {
        $service_security= $this->get('Secure');
        $service_security->verifyAccess();
        $em = $this->getDoctrine()->getEntityManager();
        $language=$em->getRepository('mycpBundle:lang')->find($id_language);
        //delete relations
        $lang_flag=$em->getRepository('mycpBundle:langFlag')->findBy(array('lang_flag_lang_id'=>$id_language));

        $own_desc_lang=$em->getRepository('mycpBundle:ownershipDescriptionLang')->findBy(array('odl_id_lang'=>$id_language));
        if(isset($own_desc_lang[0]))
        {
            foreach($own_desc_lang as $desc_lang)
                $em->remove($desc_lang);
        }

        $own_key_lang=$em->getRepository('mycpBundle:ownershipKeywordLang')->findBy(array('okl_id_lang'=>$id_language));
        if(isset($own_key_lang[0]))
        {
            foreach($own_key_lang as $key_lang)
                $em->remove($key_lang);
        }

        $photo_lang=$em->getRepository('mycpBundle:photoLang')->findBy(array('pho_lang_id_lang'=>$id_language));
        if(isset($photo_lang[0]))
        {
            foreach($photo_lang as $pho_lang)
                $em->remove($pho_lang);
        }

        $album_lang=$em->getRepository('mycpBundle:albumLang')->findBy(array('album_lang_lang'=>$id_language));
        if(isset($album_lang[0]))
            $em->remove($album_lang[0]);

        $album_cat_lang=$em->getRepository('mycpBundle:albumCategoryLang')->findBy(array('album_cat_id_lang'=>$id_language));
        if(isset($album_cat_lang[0]))
            $em->remove($album_cat_lang[0]);

        $faq_lang=$em->getRepository('mycpBundle:faqLang')->findBy(array('faq_lang_lang'=>$id_language));
        if(isset($faq_lang[0]))
            $em->remove($faq_lang[0]);

        $own_gen_lang=$em->getRepository('mycpBundle:ownershipGeneralLang')->findBy(array('ogl_id_lang'=>$id_language));
        if(isset($own_gen_lang[0]))
            $em->remove($own_gen_lang[0]);

        $faq_cat_lang=$em->getRepository('mycpBundle:faqCategoryLang')->findBy(array('faq_cat_id_lang'=>$id_language));
        if(isset($faq_cat_lang[0]))
        {
            foreach($faq_cat_lang as $faq_lang)
                $em->remove($faq_lang);

        }

        $destination_category_lang=$em->getRepository('mycpBundle:destinationCategoryLang')->findBy(array('des_cat_id_lang'=>$id_language));
        if(isset($destination_category_lang[0]))
        {
            foreach($destination_category_lang as $des_cat_lang)
                $em->remove($des_cat_lang);

        }

        $destination_lang=$em->getRepository('mycpBundle:destinationLang')->findBy(array('des_lang_lang'=>$id_language));
        if(isset($destination_lang[0]))
        {
            foreach($destination_lang as $des_lang)
                $em->remove($des_lang);

        }

        $information_lang=$em->getRepository('mycpBundle:informationLang')->findBy(array('info_lang_lang'=>$id_language));
        if(isset($information_lang[0]))
        {
            foreach($information_lang as $info_lang)
                $em->remove($info_lang);

        }

        $photo=new photo();
        if(isset($lang_flag[0]))
        {
            $photo=$lang_flag[0]->getLangFlagPhoto();
            $em->remove($lang_flag[0]);
        }
        $dir=$this->container->getParameter('language.dir.photos');
        @unlink($dir.$photo->getPhoName());
        $em->remove($photo);
        $name_lang=$language->getLangName();
        $em->remove($language);
        $em->flush();
        $message='El idioma se ha eliminado satisfactoriamente.';
        $this->get('session')->getFlashBag()->add('message_ok',$message);

        $service_log= $this->get('log');
        $service_log->saveLog('Delete entity '.$name_lang,BackendModuleName::MODULE_LANGUAGE);

        return $this->redirect($this->generateUrl('mycp_list_languages'));
    }

}
