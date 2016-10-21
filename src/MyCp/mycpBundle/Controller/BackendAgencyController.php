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

class BackendAgencyController extends Controller {

    public function active_releaseAction($items_per_page, Request $request) {
        $em = $this->getDoctrine()->getManager();
        $agencys = $em->getRepository('PartnerBundle:paTravelAgency')->getAllDeactive();

        foreach ($agencys as $agency) {
            $user = $agency->getTourOperators()->first()->getTourOperator();
            $user->setUserEnabled(1);

            $em->persist($user);
            $em->flush();

            $language = $user->getUserLanguage();
            UserMails::sendCreateUserPartner($this, $user->getUserEmail(), $user->getName(), $agency->getName(), $user->getUserId(), strtolower($language->getLangCode()), $agency, false);
        }

        return $this->redirect($this->generateUrl('mycp_list_agency'));
    }

    public function list_AgencyAction($items_per_page, Request $request) {
        $service_security = $this->get('Secure');
        $service_security->verifyAccess();
        $page = 1;
        $filter_active = $request->get('filter_active');
        $filter_country = $request->get('filter_country');
        $filter_name = $request->get('filter_name');
        $filter_owner = $request->get('filter_owner');
        $filter_email = $request->get('filter_email');
        $filter_package = $request->get('filter_package');
        $filter_date_created = $request->get('filter_date_created');
        if ($request->getMethod() == 'POST' && $filter_name == 'null' && $filter_active == 'null' && $filter_country == 'null' && $filter_package == 'null' &&
                $filter_email == 'null' && $filter_date_created == 'null' && $filter_owner == 'null'
        ) {
            $message = 'Debe llenar al menos un campo para filtrar.';
            $this->get('session')->getFlashBag()->add('message_error_local', $message);
            return $this->redirect($this->generateUrl('mycp_list_agency'));
        }

        if ($filter_active == 'null')
            $filter_active = '';
        if ($filter_name == 'null')
            $filter_name = '';
        if ($filter_country == 'null')
            $filter_country = '';
        if ($filter_owner == 'null')
            $filter_owner = '';
        if ($filter_email == 'null')
            $filter_email = '';
        if ($filter_date_created == 'null')
            $filter_date_created = '';
        if (isset($_GET['page']))
            $page = $_GET['page'];

//        dump($filter_name);die;
        $em = $this->getDoctrine()->getManager();
        $paginator = $this->get('ideup.simple_paginator');
        $paginator->setItemsPerPage($items_per_page);
        $agencys = $paginator->paginate($em->getRepository('PartnerBundle:paTravelAgency')->getAll(
            $filter_name,
            $filter_country,
            $filter_owner,
            $filter_email,
            $filter_date_created,
            $filter_package,
            $filter_active
        ))->getResult();

        return $this->render('mycpBundle:agency:list.html.twig', array(
                    'agencys' => $agencys,
                    'items_per_page' => $items_per_page,
                    'current_page' => $page,
                    'total_items' => $paginator->getTotalItems(),
                    'filter_name' => $filter_name,
                    'filter_active' => $filter_active,
                    'filter_country' => $filter_country,
                    'filter_owner' => $filter_owner,
                    'filter_email' => $filter_email,
                    'filter_package' => $filter_package,
                    'filter_date_created' => $filter_date_created,
        ));
    }

    public function details_AgencyAction($id, Request $request) {
        $service_security = $this->get('Secure');
        $service_security->verifyAccess();
        $em = $this->getDoctrine()->getManager();
        $agency = $em->getRepository('PartnerBundle:paTravelAgency')->getById($id)[0];

        return $this->render('mycpBundle:agency:details.html.twig', array('agency' => $agency));
    }

    public function edit_AgencyAction($id, Request $request) {
        $service_security = $this->get('Secure');
        $service_security->verifyAccess();

        $em = $this->getDoctrine()->getManager();
        $obj = $em->getRepository('PartnerBundle:paTravelAgency')->find($id);
        $agency = $em->getRepository('PartnerBundle:paTravelAgency')->getById($id)[0];
        $errors = array();

        $form = $this->createForm(new paTravelAgencyType($this->get('translator')),$obj);

        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);
            if ($form->isValid()){
                $agency_data = $form->getData();
                $em->persist($agency_data);
                $em->flush();
                $message = 'La agencia se ha actualizado satisfactoriamente.';
                $this->get('session')->getFlashBag()->add('message_ok', $message);
            }
        }else{

        }

        return $this->render('mycpBundle:agency:edit.html.twig', array(
            'form' => $form->createView(),
            'id_agency' => $id,
            'agency' => $agency
        ));
    }

    public function enable_AgencyAction($id,$activar){

        $em = $this->getDoctrine()->getManager();
        $agency = $em->getRepository('PartnerBundle:paTravelAgency')->getById($id)[0];

        $user_id = $agency['touroperador_id'];

        if ( $em->getRepository('mycpBundle:user')->changeStatus($user_id) ){
            if (!$activar){
                $body = $this->render('@mycp/mail/disabledAgency.html.twig', array('agency' => $agency));
                $service_email = $this->get('Email');
                $service_email->sendTemplatedEmail(
                    $this->get('translator')->trans('EMAIL_AGENCY_DISABLE_ACCOUNT'), 'noreply@mycasaparticular.com', $agency['contact_mail'], $body->getContent());
            }
            $result = true;
        }else{
            $result = false;
        }

        return new JsonResponse(array('result'=>$result,'id'=>$id));
    }

    public function get_agency_namesAction() {
        $em = $this->getDoctrine()->getManager();
        $agencys = $em->getRepository('PartnerBundle:paTravelAgency')->findAll();
        return $this->render('mycpBundle:utils:agencys_names.html.twig', array('agencys' => $agencys));
    }

    public function get_owner_namesAction(){
        $em = $this->getDoctrine()->getManager();
        $owners = $em->getRepository('PartnerBundle:paTourOperator')->getOwners();
        return $this->render('mycpBundle:utils:agency_owner_names.html.twig', array('owners' => $owners));
    }

    public function get_packagesAction($post){
        $em = $this->getDoctrine()->getManager();
        $packages = $em->getRepository('PartnerBundle:paPackage')->findAll();

        $selected = '';
        if (!is_array($post))
            $selected = $post;

        if (isset($post['filter_package']))
            $selected = $post['filter_package'];
        /* else
          $selected = ownershipStatus::STATUS_IN_PROCESS; */

        return $this->render('mycpBundle:utils:agency_package.html.twig', array('packages' => $packages, 'selected' => $selected, 'post' => $post));

    }

}