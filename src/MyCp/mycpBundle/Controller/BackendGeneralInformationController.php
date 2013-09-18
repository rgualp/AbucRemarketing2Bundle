<?php

namespace MyCp\mycpBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use MyCp\mycpBundle\Entity\albumCategory;
use MyCp\mycpBundle\Entity\information;
use MyCp\mycpBundle\Entity\informationLang;
use Symfony\Component\Validator\Constraints\NotBlank;


class BackendGeneralInformationController extends Controller
{
    function list_informationsAction(Request $request)
    {
        $service_security= $this->get('Secure');
        $service_security->verify_access();
        $em=$this->getDoctrine()->getEntityManager();
        $informations=$em->getRepository('mycpBundle:informationLang')->get_informations();
        $categories = $em->getRepository('mycpBundle:information')->category_names($informations, "ES");
        $service_log= $this->get('log');
        $service_log->save_log('Visit',9);
        return $this->render('mycpBundle:generalInformation:list.html.twig',array('informations'=>$informations, "categories"=>$categories));
    }

    function new_informationAction(Request $request)
    {
        $service_security= $this->get('Secure');
        $service_security->verify_access();
        $errors=array();
        $post = $request->request->getIterator()->getArrayCopy();
        $em=$this->getDoctrine()->getEntityManager();
        $languages=$em->getRepository('mycpBundle:lang')->findAll();
        $information_types = $em->getRepository('mycpBundle:nomenclator')->get_by_category('information');
        
        if($request->getMethod()=='POST')
        {
            $not_blank_validator = new NotBlank();
            $not_blank_validator->message="Este campo no puede estar vacío.";
            $array_keys=array_keys($post);

            $count=$count_errors= 0;
            foreach ($post as $item) {
                /*if($array_keys[$count]=='information_type' && $item == "-1"){
                    //crear un objeto para validar
                }
                else*/ if($array_keys[$count]!='edit_information')
                {
                    $errors[$array_keys[$count]] = $errors_validation=$this->get('validator')->validateValue($item, $not_blank_validator);
                    $count_errors+=count($errors_validation);
                }
                $count++;
            }

            if($count_errors==0)
            {
                if(isset($post['edit_information']))
                {
                    $em->getRepository('mycpBundle:information')->edit_information($post);
                    $message='Información actualizada satisfactoriamente.';
                    $this->get('session')->setFlash('message_ok',$message);
                    $service_log= $this->get('log');
                    $service_log->save_log('Edit entity, '.$post['info_name_'.$languages[0]->getLangId()],9);
                }
                else
                {
                    $em->getRepository('mycpBundle:information')->new_information($post);
                    $message='Información añadida satisfactoriamente.';
                    $this->get('session')->setFlash('message_ok',$message);
                    $service_log= $this->get('log');
                    $service_log->save_log('Create entity, '.$post['info_name_'.$languages[0]->getLangId()],9);;
                }
                return $this->redirect($this->generateUrl('mycp_list_informations'));
            }
            if(isset($post['edit_information']))
            {
                return $this->render('mycpBundle:generalInformation:new.html.twig',array('languages'=>$languages,"info_types" => $information_types,'errors'=>$errors,'post'=>$post,'edit_information'=>$post['edit_information']));
            }

        }
        return $this->render('mycpBundle:generalInformation:new.html.twig',array('languages'=>$languages,"info_types" => $information_types,'errors'=>$errors,'post'=>$post));
    }

    function edit_informationAction($id_information,Request $request)
    {
        $service_security= $this->get('Secure');
        $service_security->verify_access();
        $em=$this->getDoctrine()->getEntityManager();
        $languages=$em->getRepository('mycpBundle:lang')->findAll();
        $post=array();
        $information = $em->getRepository('mycpBundle:information')->find($id_information);
        $informations_langs=$em->getRepository('mycpBundle:informationLang')->findBy(array('info_lang_info'=>$id_information));
        $information_types = $em->getRepository('mycpBundle:nomenclator')->get_by_category('information');
        
        $post['information_type'] = $information->getInfoIdNom()->getNomId();
        foreach($informations_langs as $information_lang)
        {
            $post['info_name_'.$information_lang->getInfoLangLang()->getLangId()]=$information_lang->getInfoLangName();
            $post['info_content_'.$information_lang->getInfoLangLang()->getLangId()]=$information_lang->getInfoLangContent();
        }
        return $this->render('mycpBundle:generalInformation:new.html.twig',array('edit_information'=>$id_information,'post'=>$post,'languages'=>$languages,"info_types" => $information_types));
    }

    function delete_informationAction($id_information,Request $request)
    {
        $service_security= $this->get('Secure');
        $service_security->verify_access();

        $em=$this->getDoctrine()->getEntityManager();
        $information=$em->getRepository('mycpBundle:information')->find($id_information);
        $informations_lang=$em->getRepository('mycpBundle:informationLang')->findBy(array('info_lang_info'=>$id_information));

        if($information)
        {
            if($information->getInfoFixed()==1)
            {
                $message='No se puede eliminar la información, es un elemento estático.';
                $this->get('session')->setFlash('message_error_local',$message);
                return $this->redirect($this->generateUrl('mycp_list_informations'));
            }
            else
            {
                foreach($informations_lang as $information_lang)
                    $em->remove($information_lang);
                $em->remove($information);
                $em->flush();
                $service_log= $this->get('log');
                $service_log->save_log('Delete entity, '.$informations_lang[0]->getInfoLangName(),9);
            }

        }
        $message='Información eliminada satisfactoriamente.';
        $this->get('session')->setFlash('message_ok',$message);

        return $this->redirect($this->generateUrl('mycp_list_informations'));

    }
}
