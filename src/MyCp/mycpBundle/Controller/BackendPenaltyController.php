<?php

namespace MyCp\mycpBundle\Controller;

use MyCp\mycpBundle\Entity\batchType;
use MyCp\mycpBundle\Entity\ownershipReservation;
use MyCp\mycpBundle\Entity\room;
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

class BackendPenaltyController extends Controller {


    public function listAction($accommodationId)
    {
//        $service_security = $this->get('Secure');
//        $service_security->verifyAccess();
        $em = $this->getDoctrine()->getManager();

        $accommodation = $em->getRepository("mycpBundle:ownership")->find($accommodationId);

        $paginator = $this->get('ideup.simple_paginator');
        $paginator->setItemsPerPage(100);
        $penalties = $paginator->paginate($em->getRepository('mycpBundle:penalty')->findByAccommodation($accommodationId))->getResult();
        $page = 1;
        if (isset($_GET['page']))
            $page = $_GET['page'];

        return $this->render('mycpBundle:penalty:list.html.twig', array(
            'list' => $penalties,
            'total_items' => $paginator->getTotalItems(),
            'current_page' => $page,
            'accommodation' => $accommodation
        ));
    }

    public function createAction($accommodationId)
    {
        throw new NotImplementedException("Por implementar");
    }

    public function deleteAction($idPenalty)
    {
        throw new NotImplementedException("Por implementar");
    }
}
