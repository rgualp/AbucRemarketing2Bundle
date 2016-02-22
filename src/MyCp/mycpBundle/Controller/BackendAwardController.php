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

class BackendAwardController extends Controller {

    function listAction($items_per_page, Request $request) {
        //$service_security = $this->get('Secure');
        //$service_security->verifyAccess();
        $em = $this->getDoctrine()->getManager();

        $paginator = $this->get('ideup.simple_paginator');
        $paginator->setItemsPerPage($items_per_page);
        $awards = $paginator->paginate($em->getRepository('mycpBundle:award')->findAll())->getResult();
        $page = 1;
        if (isset($_GET['page']))
            $page = $_GET['page'];
        return $this->render('mycpBundle:award:list.html.twig', array(
                    'list' => $awards,
                    'items_per_page' => $items_per_page,
                    'total_items' => $paginator->getTotalItems(),
                    'current_page' => $page,
        ));
    }

    function newAction(Request $request) {
        $service_security = $this->get('Secure');
        $service_security->verifyAccess();
        $em = $this->getDoctrine()->getManager();
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
                $message = 'Premio añadido satisfactoriamente.';
                $this->get('session')->getFlashBag()->add('message_ok', $message);

                $service_log = $this->get('log');
                $service_log->saveLog('Create award, ' . $post['lang' . $languages[0]->getLangId()], BackendModuleName::MODULE_ALBUM);

                return $this->redirect($this->generateUrl('mycp_list_awards'));
            }
        }
        return $this->render('mycpBundle:award:new.html.twig', array(
                    'form' => $form->createView(),
        ));
    }

    function editAction($id, Request $request) {
        $service_security = $this->get('Secure');
        $service_security->verifyAccess();
        $em = $this->getDoctrine()->getManager();

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
                $message = 'Premio actualizado satisfactoriamente.';
                $this->get('session')->getFlashBag()->add('message_ok', $message);

                $service_log = $this->get('log');
                $service_log->saveLog('Edit award, ' . $post['lang' . $languages[0]->getLangId()], BackendModuleName::MODULE_ALBUM);

                return $this->redirect($this->generateUrl('mycp_list_awards'));
            }
        }

        return $this->render('mycpBundle:award:new.html.twig', array(
                    'form' => $form->createView(), 'edit_award' => $id, 'name_award' => $album_cat_lang[0]->getAlbumCatDescription()
        ));
    }

    function deleteAction($id) {
        $service_security = $this->get('Secure');
        $service_security->verifyAccess();
        $em = $this->getDoctrine()->getManager();
        $award = $em->getRepository('mycpBundle:award')->find($id);

        if ($award)
            $em->remove($award);
        $em->flush();
        $message = 'Premio eliminado satisfactoriamente.';
        $this->get('session')->getFlashBag()->add('message_ok', $message);

        $service_log = $this->get('log');
        $service_log->saveLog('Delete award, ' . $id, BackendModuleName::MODULE_ALBUM);

        return $this->redirect($this->generateUrl('mycp_list_awards'));
    }

}
