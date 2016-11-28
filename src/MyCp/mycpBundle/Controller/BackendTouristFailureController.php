<?php

namespace MyCp\mycpBundle\Controller;

use MyCp\mycpBundle\Entity\batchType;
use MyCp\mycpBundle\Entity\ownershipReservation;
use MyCp\mycpBundle\Entity\penalty;
use MyCp\mycpBundle\Entity\room;
use MyCp\mycpBundle\Entity\touristFailure;
use MyCp\mycpBundle\Form\penaltyType;
use MyCp\mycpBundle\Form\touristFailureType;
use MyCp\mycpBundle\Helpers\DataBaseTables;
use MyCp\mycpBundle\Helpers\FileIO;
use MyCp\PartnerBundle\Form\paTravelAgencyType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Intl\Exception\MethodArgumentNotImplementedException;
use Symfony\Component\Intl\Exception\MethodNotImplementedException;
use Symfony\Component\Intl\Exception\NotImplementedException;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Email;
use MyCp\mycpBundle\Entity\ownership;
use MyCp\mycpBundle\Entity\log;
use MyCp\mycpBundle\Entity\photo;
use MyCp\mycpBundle\Entity\user;
use MyCp\mycpBundle\Entity\photoLang;
use MyCp\mycpBundle\Entity\ownershipPhoto;
use MyCp\mycpBundle\Helpers\OwnershipStatuses;
use MyCp\mycpBundle\Entity\ownershipStatus;
use MyCp\mycpBundle\Helpers\BackendModuleName;
use MyCp\mycpBundle\Helpers\UserMails;
use MyCp\mycpBundle\Helpers\Operations;
use Symfony\Component\Validator\Constraints\RegexValidator;

class BackendTouristFailureController extends Controller {


    public function listAction($accommodationId)
    {
        $service_security = $this->get('Secure');
        $service_security->verifyAccess();
        $em = $this->getDoctrine()->getManager();

        $accommodation = $em->getRepository("mycpBundle:ownership")->find($accommodationId);

        $paginator = $this->get('ideup.simple_paginator');
        $paginator->setItemsPerPage(100);
        $failures = $paginator->paginate($em->getRepository('mycpBundle:touristFailure')->findByAccommodation($accommodationId))->getResult();
        $page = 1;
        if (isset($_GET['page']))
            $page = $_GET['page'];

        return $this->render('mycpBundle:touristFailure:list.html.twig', array(
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
        $failure = new touristFailure();
        $form = $this->createForm(new touristFailureType(), $failure);
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
            'mycpBundle:touristFailure:new.html.twig',
            array('form' => $form->createView(), 'accommodation' => $accommodation)
        );
    }

    public function deleteAction($idFailure)
    {
        $service_security= $this->get('Secure');
        $service_security->verifyAccess();
        $em = $this->getDoctrine()->getManager();

        $failure=$em->getRepository('mycpBundle:touristFailure')->find($idFailure);
        $accommodationId = $failure->getAccommodation()->getOwnId();
        $em->remove($failure);
        $em->flush();
        $message='El fallo se ha eliminado satisfactoriamente.';
        $this->get('session')->getFlashBag()->add('message_ok',$message);

        return $this->redirect($this->generateUrl('mycp_list_touristfailures', array("accommodationId" => $accommodationId)));
    }
}
