<?php

namespace MyCp\mycpBundle\Controller;

use MyCp\mycpBundle\Helpers\FormMode;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use MyCp\mycpBundle\Entity\award;
use MyCp\mycpBundle\Form\awardType;
use MyCp\mycpBundle\Helpers\BackendModuleName;
use Symfony\Component\HttpFoundation\Response;

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
        $form = $this->createForm(new awardType(true), $award);

        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $this->processAwardPost($award, $em, $request);
                $message = 'Premio añadido satisfactoriamente.';
                $this->get('session')->getFlashBag()->add('message_ok', $message);

                $service_log = $this->get('log');
                $service_log->saveLog('Create award, ' . $award->getName(), BackendModuleName::MODULE_AWARD);

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
        $priceFileNames = array("big" => ($award != null) ? $award->getIconOrClassName() : "", "small" => ($award != null) ? $award->getSecondIconOrClassName(): "");
        $form = $this->createForm(new awardType(false), $award);

        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $this->processAwardPost($award, $em, $request, $priceFileNames);
                $message = 'Premio actualizado satisfactoriamente.';
                $this->get('session')->getFlashBag()->add('message_ok', $message);

                $service_log = $this->get('log');
                $service_log->saveLog('Edit award, ' . $award->getName(), BackendModuleName::MODULE_AWARD);

                return $this->redirect($this->generateUrl('mycp_list_awards'));
            }
        }

        return $this->render('mycpBundle:award:new.html.twig', array(
                    'form' => $form->createView(), 'edit_award' => $id, 'award' => $award
        ));
    }

    private function processAwardPost(award $award, $em, Request $request, $priceFileNames = null)
    {
        $dir = $this->container->getParameter('award.dir.photos');
        $file = $request->files->get('mycp_mycpbundle_awardtype');
        if (isset($file['icon_or_class_name'])) {
            $fileName = uniqid('award-') . '-photo.jpg';
            $file['icon_or_class_name']->move($dir, $fileName);
            $award->setIconOrClassName($fileName);
        }
        else if($priceFileNames != null){
            $award->setIconOrClassName($priceFileNames["big"]);
        }

        if (isset($file['second_icon_or_class_name'])) {
            $fileName = uniqid('award-') . '-photo.jpg';
            $file['second_icon_or_class_name']->move($dir, $fileName);
            $award->setSecondIconOrClassName($fileName);
        }
        else if($priceFileNames != null){
            $award->setSecondIconOrClassName($priceFileNames["small"]);
        }


        $em->persist($award);
        $em->flush();
    }

    function deleteAction($id) {
        //$service_security = $this->get('Secure');
        //$service_security->verifyAccess();

        $em = $this->getDoctrine()->getManager();
        $dir = $this->container->getParameter('award.dir.photos');
        $award = $em->getRepository('mycpBundle:award')->find($id);

        if(count($award->getAwardAccommodations()) == 0) {
            if ($award->getIconOrClassName()) {
                @unlink($dir . $award->getIconOrClassName());
            }

            if ($award->getSecondIconOrClassName()) {
                @unlink($dir . $award->getSecondIconOrClassName());
            }


            if ($award)
                $em->remove($award);
            $em->flush();
            $message = 'Premio eliminado satisfactoriamente.';
            $this->get('session')->getFlashBag()->add('message_ok', $message);

            $service_log = $this->get('log');
            $service_log->saveLog('Delete award, ' . $id, BackendModuleName::MODULE_AWARD);
        }
        else{
            $message = 'El premio no puede ser eliminado porque esta otorgado en '.count($award->getAwardAccommodations()). " alojamientos";
            $this->get('session')->getFlashBag()->add('message_error_main', $message);
        }

        return $this->redirect($this->generateUrl('mycp_list_awards'));
    }

    function removeAccommodationAwardAction($award_id, $accommodation_id, Request $request) {
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
        $service_log->saveLog('Remove award, ' . $award_id. '-'.$accommodation_id, BackendModuleName::MODULE_AWARD);

        return $this->redirect($this->generateUrl('mycp_accommodation_award_list', array("id" => $award_id)));
    }

    function accommodationsAction($id, $items_per_page, Request $request) {
        //$service_security = $this->get('Secure');
        //$service_security->verifyAccess();
        $em = $this->getDoctrine()->getManager();
        $filter_year=$request->get('filter_year');
        $filter_code=$request->get('filter_code');
        $filter_name=$request->get('filter_name');

        if ($filter_year == 'null')
            $filter_year = '';
        if ($filter_code == 'null')
            $filter_code = '';
        if ($filter_name == 'null')
            $filter_name = '';

        $accommodationsAward = $em->getRepository('mycpBundle:accommodationAward')->getAccommodationsAward($id, $filter_year, $filter_code, $filter_name);
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
            'filter_year' => $filter_year,
            'filter_code' => $filter_code,
            'filter_name' => $filter_name
        ));
    }

    function setAwardAction($id, $items_per_page, Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $filter_name=$request->get('filter_name');
        $sort_by=$request->get('sort_by');
        $filter_province=$request->get('filter_province');
        $filter_municipality=$request->get('filter_municipality');
        $filter_code=$request->get('filter_code');
        $filter_destination=$request->get('filter_destination');
        $filter_year=$request->get('filter_year');

        if($sort_by=='null')  $sort_by=  OrderByHelper::AWARD_ACCOMMODATION_RANKING;

        $award = $em->getRepository('mycpBundle:award')->find($id);

        $paginator = $this->get('ideup.simple_paginator');
        $paginator->setItemsPerPage($items_per_page);
        $accommodationsAward = $em->getRepository("mycpBundle:accommodationAward")->getAccommodationsForSetAward($id, $sort_by, $filter_code, $filter_name, $filter_province, $filter_municipality, $filter_destination, $filter_year);
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
            'filter_name' => $filter_name,
            'filter_code' => $filter_code,
            'filter_province' => $filter_province,
            'filter_destination' => $filter_destination,
            'filter_municipality' => $filter_municipality,
            'filter_year' => $filter_year
        ));
    }

    public function setAwardCallbackAction() {
        $request = $this->getRequest();
        $em = $this->getDoctrine()->getManager();
        $accommodations_ids = $request->request->get('accommodations_ids');
        $award_id = $request->request->get('award_id');
        $items_per_page = $request->request->get('items_per_page');
        $filter_code = $request->request->get('filter_code');
        $filter_province = $request->request->get('filter_province');
        $filter_municipality = $request->request->get('filter_municipality');
        $filter_destination = $request->request->get('filter_destination');
        $filter_name = $request->request->get('filter_name');
        $sort_by = $request->request->get('sort_by');
        $filter_year = $request->request->get('filter_year');
        $year = $request->request->get('year');


        $response = null;

        $service_log = $this->get('log');
        //Premiar
        $em->getRepository('mycpBundle:accommodationAward')->setAccommodationAward($accommodations_ids, $award_id, $year, $service_log);

        $message = 'Se han premiado ' . count($accommodations_ids) . ' alojamientos exitosamente';
        $this->get('session')->getFlashBag()->add('message_ok', $message);

        $response = $this->generateUrl('mycp_set_award_accommodation', array("id" => $award_id,
        "filter_code" => $filter_code, "filter_destination" => $filter_destination, "filter_municipality" => $filter_municipality,
        "filter_name" => $filter_name, "filter_province" => $filter_province, "items_per_page" => $items_per_page, "sort_by" => $sort_by,
        "filter_year" => $filter_year));

        return new Response($response);
    }

    function removeAccommodationAwardCallbackAction()
    {
        $request = $this->getRequest();
        $em = $this->getDoctrine()->getManager();
        $accommodation_id = $request->request->get('accommodation_id');
        $award_id = $request->request->get('award_id');
        $items_per_page = $request->request->get('items_per_page');
        $filter_code = $request->request->get('filter_code');
        $filter_province = $request->request->get('filter_province');
        $filter_municipality = $request->request->get('filter_municipality');
        $filter_destination = $request->request->get('filter_destination');
        $filter_name = $request->request->get('filter_name');
        $sort_by = $request->request->get('sort_by');
        $filter_year = $request->request->get('filter_year');

        $response = null;

        $accommodationAward = $em->getRepository('mycpBundle:accommodationAward')->findOneBy(array("award" => $award_id, "accommodation" => $accommodation_id));

        if ($accommodationAward)
            $em->remove($accommodationAward);
        $em->flush();
        $message = 'Premio removido satisfactoriamente.';
        $this->get('session')->getFlashBag()->add('message_ok', $message);

        $service_log = $this->get('log');
        $service_log->saveLog('Remove award, ' . $award_id. '-'.$accommodation_id, BackendModuleName::MODULE_AWARD);

        $response = $this->generateUrl('mycp_set_award_accommodation', array("id" => $award_id,
            "filter_code" => $filter_code, "filter_destination" => $filter_destination, "filter_municipality" => $filter_municipality,
            "filter_name" => $filter_name, "filter_province" => $filter_province, "items_per_page" => $items_per_page, "sort_by" => $sort_by,
            "filter_year" => $filter_year));

        return new Response($response);

    }

}
