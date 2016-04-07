<?php

namespace MyCp\mycpBundle\Controller;

use MyCp\mycpBundle\Helpers\DataBaseTables;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints\NotBlank;
use MyCp\mycpBundle\Entity\faqLang;
use MyCp\mycpBundle\Entity\faqCategory;
use MyCp\mycpBundle\Entity\log;
use MyCp\mycpBundle\Entity\faqCategoryLang;
use MyCp\mycpBundle\Form\categoryType;
use MyCp\mycpBundle\Helpers\BackendModuleName;

class BackendFAQSController extends Controller
{
    function list_categoryAction($items_per_page,Request $request)
    {
        $service_security= $this->get('Secure');
        $service_security->verifyAccess();
        $em = $this->getDoctrine()->getManager();
        $languages=$em->getRepository('mycpBundle:lang')->findAll();
        $paginator = $this->get('ideup.simple_paginator');
        $paginator->setItemsPerPage($items_per_page);
        $categories=$paginator->paginate($em->getRepository('mycpBundle:faqCategoryLang')->getCategories())->getResult();
        $page=1;
        if(isset($_GET['page']))$page=$_GET['page'];
        return $this->render('mycpBundle:faq:categoryList.html.twig',array(

            'categories'=>$categories,
            'items_per_page'=>$items_per_page,
            'total_items'=>$paginator->getTotalItems(),
            'current_page'=>$page,
        ));
    }

    function new_categoryAction(Request $request)
    {
        $service_security= $this->get('Secure');
        $service_security->verifyAccess();
        $em = $this->getDoctrine()->getManager();
        $languages=$em->getRepository('mycpBundle:lang')->findAll();
        $form = $this->createForm(new categoryType(array('languages'=>$languages)));

        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $category= new faqCategory();
                $em->persist($category);
                $em->flush();
                $post=$form->getData();
                foreach($languages as $language)
                {
                    $category_lang= new faqCategoryLang();
                    $category_lang->setFaqCatIdCat($category);
                    $category_lang->setFaqCatIdLang($language);
                    $category_lang->setFaqCatDescription($post['lang'.$language->getLangId()]);
                    $em->persist($category_lang);
                }
                $em->flush();
                $message='Categoría añadida satisfactoriamente.';
                $this->get('session')->getFlashBag()->add('message_ok',$message);

                $service_log= $this->get('log');
                $service_log->saveLog($category->getLogDescription(), BackendModuleName::MODULE_FAQS, log::OPERATION_INSERT, DataBaseTables::FAQ_CATEGORY);

                return $this->redirect($this->generateUrl('mycp_list_category_faq'));
            }
        }
        return $this->render('mycpBundle:faq:categoryNew.html.twig',array(
            'form' => $form->createView(),

        ));
    }

    function edit_categoryAction($id_category, Request $request)
    {
        $service_security= $this->get('Secure');
        $service_security->verifyAccess();
        $em = $this->getDoctrine()->getManager();

        $languages=$em->getRepository('mycpBundle:lang')->findAll();
        $category = $em->getRepository("mycpBundle:faqCategory")->find($id_category);
        //$faq_cat_langs=$em->getRepository('mycpBundle:faqCategoryLang')->getCategories();
        $faq_cat_lang = $em->getRepository('mycpBundle:faqCategoryLang')->findBy(array('faq_cat_id_cat'=>$id_category));

        if($request->getMethod() == 'POST')
        {
            $form = $this->createForm(new categoryType(array('languages'=>$languages)));

        }
        else
        {
            $faq_cat_lang=$em->getRepository('mycpBundle:faqCategoryLang')->findBy(array('faq_cat_id_cat'=>$id_category));
            $form = $this->createForm(new categoryType(array('languages'=>$languages,'faq_cat_lang'=>$faq_cat_lang)));
        }

        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);
            if ($form->isValid()) {

                $post=$form->getData();
                foreach($languages as $language)
                {
                    $faq_cat_lang=$em->getRepository('mycpBundle:faqCategoryLang')->findBy(array('faq_cat_id_lang'=>$language,'faq_cat_id_cat'=>$id_category));
                    $faq_cat_lang[0]->setFaqCatDescription($post['lang'.$language->getLangId()]);
                    $em->persist($faq_cat_lang[0]);

                }

                $em->flush();
                $message='Categoría actualizada satisfactoriamente.';
                $this->get('session')->getFlashBag()->add('message_ok',$message);

                $service_log= $this->get('log');
                $service_log->saveLog($category->getLogDescription(), BackendModuleName::MODULE_FAQS, log::OPERATION_UPDATE, DataBaseTables::FAQ_CATEGORY);

                return $this->redirect($this->generateUrl('mycp_list_category_faq'));
            }
        }

        return $this->render('mycpBundle:faq:categoryNew.html.twig',array(
            'form' => $form->createView(), 'edit_category'=>$id_category, 'name_category'=>$faq_cat_lang[0]->getFaqCatDescription()

        ));
    }

    public function new_faqAction(Request $request)
    {
        $service_security= $this->get('Secure');
        $service_security->verifyAccess();
        $errors = array();
        $post = $request->request->getIterator()->getArrayCopy();
        $em = $this->getDoctrine()->getManager();
        $faqs=$em->getRepository('mycpBundle:faq')->findAll();
        $array_faqs=array();
        foreach($faqs as $faq)
        {
            $faqsLang=$em->getRepository('mycpBundle:faqLang')->findBy(array('faq_lang_faq'=>$faq->getFaqId()));
            if(isset($faqsLang[0]))
            {
                $array_faqs['faq_'.$faq->getFaqId()]=$faqsLang[0]->getFaqLangQuestion();
            }
        }

        if ($request->getMethod() == 'POST') {
            $not_blank_validator = new NotBlank();
            $not_blank_validator->message="Este campo no puede estar vacío.";
            $array_keys=array_keys($post);
            $count=$errors_validation=$count_errors= 0;
            foreach ($post as $item) {
                if(strpos($array_keys[$count], 'question_')!==0 and strpos($array_keys[$count], 'answer_')!==0)
                {
                    $errors[$array_keys[$count]] = $errors_validation=$this->get('validator')->validateValue($item, $not_blank_validator);
                    $count_errors+=count($errors_validation);
                }
                $count++;
            }


            if ($count_errors == 0) {
                //save into database

                if($request->request->get('edit_faq'))
                {
                    $faq = $em->getRepository('mycpBundle:faq')->edit($post);
                    $message='Pregunta actualizada satisfactoriamente.';

                    $service_log= $this->get('log');
                    $service_log->saveLog($faq->getLogDescription(), BackendModuleName::MODULE_FAQS, log::OPERATION_UPDATE, DataBaseTables::FAQ);
                }
                else
                {

                    $faq = $em->getRepository('mycpBundle:faq')->insert($post);
                    $message='Pregunta añadida satisfactoriamente.';

                    $service_log= $this->get('log');
                    $service_log->saveLog($faq->getLogDescription(), BackendModuleName::MODULE_FAQS, log::OPERATION_INSERT, DataBaseTables::FAQ);
                }
                $this->get('session')->getFlashBag()->add('message_ok',$message);
                return $this->redirect($this->generateUrl('mycp_list_faqs'));
            }
            if($request->request->get('edit_faq'))
            {
                $id_faq=$request->request->get('edit_faq');
                $faq=$em->getRepository('mycpBundle:destination')->find($id_faq);
                $post['name']=$faqsLang[0]->getFaqLangQuestion();
                $post['id_faq']=$id_faq;
            }

        }
        $languages = $em->getRepository('mycpBundle:lang')->getAll();
        return $this->render('mycpBundle:faq:new.html.twig', array('languages' => $languages, 'errors' => $errors, 'data' => $post,'faqs_text'=>$array_faqs));
    }

    public function list_faqsAction($items_per_page,Request $request)
    {
        $service_security= $this->get('Secure');
        $service_security->verifyAccess();
        $page=1;
        $filter_active=$request->get('filter_active');
        $filter_name=$request->get('filter_name');
        $filter_category=$request->get('category');
        $sort_by=$request->get('sort_by');

        if($filter_category=='')
            $filter_category='null';
        if($sort_by=='')
            $sort_by= \MyCp\mycpBundle\Helpers\OrderByHelper::DEFAULT_ORDER_BY;
        
        if($request->getMethod()=='POST' && $filter_active=='null' && $filter_name=='null' && $filter_category=='null' && $sort_by=='null')
        {
            $message='Debe llenar al menos un campo para filtrar.';
            $this->get('session')->getFlashBag()->add('message_error_local',$message);
            return $this->redirect($this->generateUrl('mycp_list_faqs'));
        }
        if($filter_name=='null') $filter_name='';
        if(isset($_GET['page']))$page=$_GET['page'];
        $em = $this->getDoctrine()->getManager();
        $paginator = $this->get('ideup.simple_paginator');
        $paginator->setItemsPerPage($items_per_page);
        $faqs= $paginator->paginate($em->getRepository('mycpBundle:faq')->getAll($filter_name,$filter_active,$filter_category,$sort_by))->getResult();
        $data=array();

        foreach($faqs as $faq)
        {
            $data[$faq->getFaqLangFaq()->getFaqId().'_category']=$em->getRepository('mycpBundle:faqCategoryLang')->findBy(array('faq_cat_id_cat'=>$faq->getFaqLangFaq()->getFaqCategory()));
        }
//        $service_log= $this->get('log');
//        $service_log->saveLog('Visit',BackendModuleName::MODULE_FAQS);
        return $this->render('mycpBundle:faq:list.html.twig', array(
            'faqs' => $faqs,
            'data'=>$data,
            'items_per_page'=>$items_per_page,
            'current_page'=>$page,
            'total_items'=>$paginator->getTotalItems(),
            'filter_name'=>$filter_name,
            'category'=>$filter_category,
            'filter_active'=>$filter_active,
            'sort_by'=>$sort_by
        ));
    }

    function edit_faqAction($id_faq)
    {
        $service_security= $this->get('Secure');
        $service_security->verifyAccess();
        $errors = array();
        $em = $this->getDoctrine()->getManager();
        $faq=$em->getRepository('mycpBundle:faq')->find($id_faq);
        $languages = $em->getRepository('mycpBundle:lang')->getAll();
        $faqsLang=$em->getRepository('mycpBundle:faqLang')->findBy(array('faq_lang_faq'=>$id_faq));
        $data['name']=$faqsLang[0]->getFaqLangQuestion();
        $data['category']=$faq->getFaqCategory()->getFaqCatId();
        $data['id_faq']=$id_faq;
        if($faq->getFaqActive()==1)
            $data['public']=TRUE;
        $a=0;
        foreach($languages as $language)
        {
            if(isset($faqsLang[$a]))
            {
                $data['question_'.$language['lang_id']]=$faqsLang[$a]->getFaqLangQuestion();
                $data['answer_'.$language['lang_id']]=$faqsLang[$a]->getFaqLangAnswer();
                $a++;
            }
        }

        $data['edit_faq']=TRUE;
        return $this->render('mycpBundle:faq:new.html.twig', array('languages' => $languages, 'errors' => $errors, 'data' => $data));
    }

    function delete_faqAction($id_faq)
    {
        $service_security= $this->get('Secure');
        $service_security->verifyAccess();
        $em = $this->getDoctrine()->getManager();
        $faqsLang=$em->getRepository('mycpBundle:faqLang')->findBy(array('faq_lang_faq'=>$id_faq));
        $faq=$em->getRepository('mycpBundle:faq')->find($id_faq);
        $logDescription = $faq->getLogDescription();

        foreach($faqsLang as $faqLang)
        {
            $em->remove($faqLang);
        }

        if($faq)
            $em->remove($faq);
        $em->flush();
        /*$faqs=$em->getRepository('mycpBundle:faq')->findAll();
        $array_faqs=array();
        foreach($faqs as $faq)
        {
            $faqsLang=$em->getRepository('mycpBundle:faqLang')->findBy(array('faq_lang_faq'=>$faq->getFaqId()));
            if(isset($faqsLang[0]))
                $array_faqs['faq_'.$faq->getFaqId()]=$faqsLang[0]->getFaqLangQuestion();

        }*/
        $message='Pregunta eliminada satisfactoriamente.';
        $this->get('session')->getFlashBag()->add('message_ok',$message);

        $service_log= $this->get('log');
        $service_log->saveLog($logDescription, BackendModuleName::MODULE_FAQS, log::OPERATION_DELETE, DataBaseTables::FAQ);

        return $this->redirect($this->generateUrl('mycp_list_faqs'));
    }

    public function set_faqs_orderAction($ids,Request $request)
    {
        $ids=str_replace(' ','',$ids);
        $ids_array= explode(",", $ids);
        $em = $this->getDoctrine()->getManager();
        $order=1;
        foreach($ids_array as $id)
        {
            $faq=new \MyCp\mycpBundle\Entity\faq();
            $faq=$em->getRepository('mycpBundle:faq')->find($id);
            $faq->setFaqOrder($order);
            $em->persist($faq);
            $em->flush();
            $order++;
        }
        return new Response($ids);
    }

    function delete_categoryAction($id_category)
    {
        $service_security= $this->get('Secure');
        $service_security->verifyAccess();
        $em = $this->getDoctrine()->getManager();
        $category=$em->getRepository('mycpBundle:faqCategory')->find($id_category);
        $logDescription = $category->getLogDescription();
        $categoryLangs=$em->getRepository('mycpBundle:faqCategoryLang')->findby(array('faq_cat_id_cat'=>$category));
        $faqs=$em->getRepository('mycpBundle:faq')->findby(array('faq_category'=>$id_category));
        foreach($categoryLangs as $categoryLang)
        {
            $em->remove($categoryLang);
        }
        foreach($faqs as $faq)
        {

            $faq_langs=$em->getRepository('mycpBundle:faqLang')->findby(array('faq_lang_faq'=>$faq->getFaqId()));
            foreach($faq_langs as $faq_lang)
            {
                $em->remove($faq_lang);
            }
            $em->remove($faq);
        }
        if($category)
            $em->remove($category);
        $em->flush();
        $message='Categoría eliminada satisfactoriamente.';
        $this->get('session')->getFlashBag()->add('message_ok',$message);

        $service_log= $this->get('log');
        $service_log->saveLog($logDescription, BackendModuleName::MODULE_FAQS, log::OPERATION_DELETE, DataBaseTables::FAQ_CATEGORY);

        return $this->redirect($this->generateUrl('mycp_list_category_faq'));
    }

    function get_all_categoriesAction($data)
    {
        $em = $this->getDoctrine()->getManager();
        $categories=$em->getRepository('mycpBundle:faqCategoryLang')->getCategories();
        return $this->render('mycpBundle:utils:category_faq.html.twig',array('categories'=>$categories,'data'=>$data));
    }
}
