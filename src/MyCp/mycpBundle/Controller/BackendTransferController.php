<?php

namespace MyCp\mycpBundle\Controller;

use MyCp\mycpBundle\Helpers\DataBaseTables;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

use MyCp\mycpBundle\Entity\transfer;
use MyCp\mycpBundle\Entity\photo;
use MyCp\mycpBundle\Entity\log;
use MyCp\mycpBundle\Entity\langFlag;
use MyCp\mycpBundle\Form\TransferForm;
use MyCp\mycpBundle\Helpers\BackendModuleName;



class BackendTransferController extends Controller
{
    public function list_transferAction($items_per_page)
    {
        $service_security= $this->get('Secure');
        $service_security->verifyAccess();
        $page=1;
        if(isset($_GET['page']))$page=$_GET['page'];
        $em = $this->getDoctrine()->getManager();
        $paginator = $this->get('ideup.simple_paginator');
        $paginator->setItemsPerPage($items_per_page);
        $transfers=$paginator->paginate($em->getRepository('mycpBundle:transfer')->findAll())->getResult();

//        $service_log= $this->get('log');
//        $service_log->saveLog('Visit',BackendModuleName::MODULE_LANGUAGE);

        return $this->render('mycpBundle:transfer:list.html.twig',array(
            'transfers'=>$transfers,
            'items_per_page'=>$items_per_page,
            'current_page'=>$page,
            'total_items'=>$paginator->getTotalItems()
        ));
    }

    public function edit_transferAction($id_transfer,Request $request)
    {
        $service_security= $this->get('Secure');
        $service_security->verifyAccess();
        $em = $this->getDoctrine()->getManager();
        $trans=$em->getRepository('mycpBundle:transfer')->find($id_transfer);

       // var_dump($lang_flag[0]->getLangFlagPhoto());
        $data=array();
        $data['trans_flag']=$trans;
        $form=$this->createForm( new TransferForm(),$trans);
        if($request->getMethod()=='POST')
        {

            $form->handleRequest($request);
            $post_form=$request->get('mycp_mycpbundle_transfer');
            if($form->isValid())
            {




                    $em->persist($trans);
                    $em->flush();
                    $message='Transfer actualizado satisfactoriamente.';
                    $this->get('session')->getFlashBag()->add('message_ok',$message);

                    $service_log= $this->get('log');
                    $service_log->saveLog("Log Tansfer", BackendModuleName::MODULE_LANGUAGE, log::OPERATION_UPDATE, DataBaseTables::LANGUAGE);

                    return $this->redirect($this->generateUrl('mycp_list_transfer'));

            }

        }
        return $this->render('mycpBundle:transfer:new.html.twig',array('form'=>$form->createView(),'data'=>$trans,'id_transfer'=>$id_transfer,'edit_transfer'=>true));
    }

    public function new_transferAction(Request $request)
    {
        $service_security= $this->get('Secure');
        $service_security->verifyAccess();
        $transfer=new transfer();
        $data=array();

        $form=$this->createForm( new TransferForm(),$transfer);
        if($request->getMethod()=='POST')
        {
            $post_form=$request->get('mycp_mycpbundle_transfer');
            $file = $request->files->get('photo');
            $form->handleRequest($request);
            if($form->isValid())
            {



                    $em = $this->getDoctrine()->getManager();




                    $em->persist($transfer);

                    $em->flush();
                    $message='Transfer añadido satisfactoriamente.';
                    $this->get('session')->getFlashBag()->add('message_ok',$message);

                    $service_log= $this->get('log');
                    $service_log->saveLog("Log Tansfer", BackendModuleName::MODULE_LANGUAGE, log::OPERATION_INSERT, DataBaseTables::LANGUAGE);

                    return $this->redirect($this->generateUrl('mycp_list_transfer'));

            }
            if($file==null)
                $data['error']='Debe seleccionar una imágen.';
        }
        return $this->render('mycpBundle:transfer:new.html.twig',array('form'=>$form->createView(),'data'=>$data));
    }

    public function delete_transferAction($id_transfer)
    {
        $service_security= $this->get('Secure');
        $service_security->verifyAccess();
        $em = $this->getDoctrine()->getManager();
        $transfer=$em->getRepository('mycpBundle:transfer')->find($id_transfer);


        $em->remove($transfer);
        $em->flush();
        $message='El idioma se ha eliminado satisfactoriamente.';
        $this->get('session')->getFlashBag()->add('message_ok',$message);

        $service_log= $this->get('log');
        $service_log->saveLog("Transfer delete", BackendModuleName::MODULE_LANGUAGE, log::OPERATION_DELETE, DataBaseTables::LANGUAGE);

        return $this->redirect($this->generateUrl('mycp_list_transfer'));
    }

}
