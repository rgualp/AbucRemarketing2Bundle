<?php

namespace MyCp\mycpBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use MyCp\mycpBundle\Entity\award;
use MyCp\mycpBundle\Form\awardType;
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
        //$service_security = $this->get('Secure');
        //$service_security->verifyAccess();
        $em = $this->getDoctrine()->getManager();
        $award = new award();
        $form = $this->createForm(new awardType(), $award);

        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $em->persist($award);
                $em->flush();
                $message = 'Premio añadido satisfactoriamente.';
                $this->get('session')->getFlashBag()->add('message_ok', $message);

                $service_log = $this->get('log');
                $service_log->saveLog('Create award, ' . $award->getName(), BackendModuleName::MODULE_ALBUM);

                return $this->redirect($this->generateUrl('mycp_list_awards'));
            }
        }
        return $this->render('mycpBundle:award:new.html.twig', array(
                    'form' => $form->createView(),
        ));
    }

    function editAction($id, Request $request) {
        //$service_security = $this->get('Secure');
        //$service_security->verifyAccess();
        $em = $this->getDoctrine()->getManager();
        $award = $em->getRepository('mycpBundle:award')->find(array('id' => $id));
        $form = $this->createForm(new awardType(), $award);
        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $em->persist($award);
                $em->flush();
                $message = 'Premio actualizado satisfactoriamente.';
                $this->get('session')->getFlashBag()->add('message_ok', $message);

                $service_log = $this->get('log');
                $service_log->saveLog('Edit award, ' . $award->getName(), BackendModuleName::MODULE_ALBUM);

                return $this->redirect($this->generateUrl('mycp_list_awards'));
            }
        }

        return $this->render('mycpBundle:award:new.html.twig', array(
                    'form' => $form->createView(), 'edit_award' => $id, 'name_award' => $award->getName()
        ));
    }

    function deleteAction($id) {
        //$service_security = $this->get('Secure');
        //$service_security->verifyAccess();
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

    function removeAccommodationAwardAction($award_id, $accommodation_id) {
        //$service_security = $this->get('Secure');
        //$service_security->verifyAccess();
        $em = $this->getDoctrine()->getManager();
        $accommodationAward = $em->getRepository('mycpBundle:accommodationAward')->findOneBy(array("award" => $award_id, "accommodation" => $accommodation_id));

        if ($accommodationAward)
            $em->remove($accommodationAward);
        $em->flush();
        $message = 'Premio removido satisfactoriamente.';
        $this->get('session')->getFlashBag()->add('message_ok', $message);

        $service_log = $this->get('log');
        $service_log->saveLog('Remove award, ' . $award_id. '-'.$accommodation_id, BackendModuleName::MODULE_ALBUM);

        return $this->redirect($this->generateUrl('mycp_accommodation_award_list', array("id" => $award_id)));
    }

    function accommodationsAction($id, $items_per_page) {
        //$service_security = $this->get('Secure');
        //$service_security->verifyAccess();
        $em = $this->getDoctrine()->getManager();
        $accommodationsAward = $em->getRepository('mycpBundle:accommodationAward')->findBy(array("award" => $id), array("year" => "DESC"));
        $award = $em->getRepository('mycpBundle:award')->find($id);

        $paginator = $this->get('ideup.simple_paginator');
        $paginator->setItemsPerPage($items_per_page);
        $accommodationsAward = $paginator->paginate($accommodationsAward)->getResult();
        $page = 1;
        if (isset($_GET['page']))
            $page = $_GET['page'];


        return $this->render('mycpBundle:award:accommodations.html.twig', array(
            'award' => $award, 'list' => $accommodationsAward,
            'items_per_page' => $items_per_page,
            'total_items' => $paginator->getTotalItems(),
            'current_page' => $page,
        ));
    }

    function setAwardAction($id, $items_per_page, Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $filter_active=$request->get('filter_active');
        $filter_name=$request->get('filter_name');
        $sort_by=$request->get('sort_by');
        $filter_province=$request->get('filter_province');
        $filter_municipality=$request->get('filter_municipality');
        $filter_code=$request->get('filter_code');
        $filter_destination=$request->get('filter_destination');

        if($sort_by=='null')  $sort_by=  OrderByHelper::AWARD_ACCOMMODATION_RANKING;

        $award = $em->getRepository('mycpBundle:award')->find($id);

        $paginator = $this->get('ideup.simple_paginator');
        $paginator->setItemsPerPage($items_per_page);
        $accommodationsAward = $em->getRepository("mycpBundle:ownership")->getAccommodationsForSetAward($id, $sort_by, $filter_code, $filter_name, $filter_active, $filter_province, $filter_municipality, $filter_destination);
        $accommodationsAward = $paginator->paginate($accommodationsAward)->getResult();
        $page = 1;
        if (isset($_GET['page']))
            $page = $_GET['page'];

        if($filter_code=='null') $filter_code='';
        if($filter_name=='null') $filter_name='';

        return $this->render('mycpBundle:award:accommodationsForAward.html.twig', array(
            'award' => $award, 'list' => $accommodationsAward,
            'items_per_page' => $items_per_page,
            'total_items' => $paginator->getTotalItems(),
            'current_page' => $page,
            'sort_by' => $sort_by,
            'filter_active' => $filter_active,
            'filter_name' => $filter_name,
            'filter_code' => $filter_code,
            'filter_province' => $filter_province,
            'filter_destination' => $filter_destination,
            'filter_municipality' => $filter_municipality
        ));
    }

}
