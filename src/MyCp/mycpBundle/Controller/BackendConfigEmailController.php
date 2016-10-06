<?php

namespace MyCp\mycpBundle\Controller;

use MyCp\mycpBundle\Helpers\FormMode;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use MyCp\mycpBundle\Entity\configEmail;
use MyCp\mycpBundle\Form\configEmailType;
use MyCp\mycpBundle\Entity\emailDestination;
use MyCp\mycpBundle\Form\emailDestinationType;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
class BackendConfigEmailController extends Controller {

    /**
     * @return Response
     */
    public function listAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $config= $em->getRepository('mycpBundle:configEmail')->findAll();
        if(count($config))
            $form = $this->createForm(new configEmailType(),$config[0]);
            else
                $form = $this->createForm(new configEmailType());

        $page= $request->get('page')?$request->get('page'):1;
        $items_per_page= $request->get('items_per_page')?$request->get('items_per_page'):20;
        $paginator = $this->get('ideup.simple_paginator');
        $paginator->setItemsPerPage($items_per_page);
        $items = $paginator->paginate($em->getRepository('mycpBundle:emailDestination')->findAll())->getResult();

        $form_destination = $this->createForm(new emailDestinationType());

        return $this->render('mycpBundle:configEmail:list.html.twig', array(
            'form'=>$form->createView(),
            'form_destination'=>$form_destination->createView(),
            'items' => $items,
            'items_per_page' => $items_per_page,
            'current_page' => $page,
            'total_items' => $paginator->getTotalItems()
        ));
    }

    /**
     * @param Request $request
     */
    public function saveAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $config= $em->getRepository('mycpBundle:configEmail')->findAll();
        if(count($config))
            $obj=$config[0];
            else
                $obj= new configEmail();
        $form = $this->createForm(new configEmailType(),$obj);
        if(!$request->get('formEmpty')){
            $form->handleRequest($request);
            $em->persist($obj);
            $em->flush();
            return new JsonResponse(['success' => true, 'message' =>'Se ha modificado satisfactoriamente']);
        }
    }

    /**
     * @param Request $request
     */
    public function saveDestinationAction(Request $request){

        $em = $this->getDoctrine()->getManager();
        $id = $request->get('id');
        $obj = ($id!='') ? $em->getRepository('mycpBundle:emailDestination')->find($id) : new emailDestination();

        $newForm= new emailDestinationType();
        $form = $this->createForm($newForm, $obj);

        if(!$request->get('formEmpty')){
            $form->handleRequest($request);
            if($form->isValid()){
                $em->persist($obj);
                $em->flush();
                return new JsonResponse(['success' => true, 'message' =>'Se ha adicionado satisfactoriamente']);
            }
        }
        $data['form_destination']= $form->createView();
        return $this->render('mycpBundle:configEmail:modal_config.html.twig', $data);
    }

    /**
     * @param $id
     * @return JsonResponse
     */
    public function deleteDestinationAction($id){
        $em = $this->getDoctrine()->getManager();
        $obj= $em->getRepository('mycpBundle:emailDestination')->find($id);
        $em->remove($obj);
        $em->flush();
        return new JsonResponse(['success' => true, 'message' =>'Se ha eliminado satisfactoriamente']);
    }
}
