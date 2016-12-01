<?php

namespace MyCp\mycpBundle\Controller;

use MyCp\mycpBundle\Entity\failure;
use MyCp\mycpBundle\Form\failureType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class BackendFailureController extends Controller {


    public function listAction($accommodationId)
    {
        $service_security = $this->get('Secure');
        $service_security->verifyAccess();
        $em = $this->getDoctrine()->getManager();

        $accommodation = $em->getRepository("mycpBundle:ownership")->find($accommodationId);

        $paginator = $this->get('ideup.simple_paginator');
        $paginator->setItemsPerPage(100);
        $failures = $paginator->paginate($em->getRepository('mycpBundle:failure')->findByAccommodation($accommodationId))->getResult();
        $page = 1;
        if (isset($_GET['page']))
            $page = $_GET['page'];

        return $this->render('mycpBundle:failure:list.html.twig', array(
            'list' => $failures,
            'total_items' => $paginator->getTotalItems(),
            'current_page' => $page,
            'accommodation' => $accommodation
        ));
    }

    public function createAction($accommodationId, Request $request)
    {
        $service_security = $this->get('Secure');
        $service_security->verifyAccess();
        $em = $this->getDoctrine()->getManager();
        $failure = new failure();
        $form = $this->createForm(new failureType(), $failure);
        $accommodation = $em->getRepository("mycpBundle:ownership")->find($accommodationId);

        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);
            if ($form->isValid()) {

                $creationDate = new \DateTime();

                $failure->setAccommodation($accommodation)
                    ->setCreationDate($creationDate)
                    ->setUser($this->getUser());

                $em->persist($failure);
                $em->flush();

                $message='El fallo se ha insertado satisfactoriamente.';
                $this->get('session')->getFlashBag()->add('message_ok',$message);


                return $this->redirect($this->generateUrl('mycp_list_touristfailures', array("accommodationId" => $accommodationId)));
            }
        }

        return $this->render(
            'mycpBundle:failure:new.html.twig',
            array('form' => $form->createView(), 'accommodation' => $accommodation)
        );
    }

    public function deleteAction($idFailure)
    {
        $service_security= $this->get('Secure');
        $service_security->verifyAccess();
        $em = $this->getDoctrine()->getManager();

        $failure=$em->getRepository('mycpBundle:failure')->find($idFailure);
        $accommodationId = $failure->getAccommodation()->getOwnId();
        $em->remove($failure);
        $em->flush();
        $message='El fallo se ha eliminado satisfactoriamente.';
        $this->get('session')->getFlashBag()->add('message_ok',$message);

        return $this->redirect($this->generateUrl('mycp_list_touristfailures', array("accommodationId" => $accommodationId)));
    }
}
