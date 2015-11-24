<?php

namespace MyCp\mycpBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use MyCp\mycpBundle\Entity\season;
use MyCp\mycpBundle\Form\seasonType;
use MyCp\mycpBundle\Helpers\BackendModuleName;

class BackendSeasonController extends Controller {

    public function listAction($items_per_page) {
        $service_security= $this->get('Secure');
        $service_security->verifyAccess();
        $page = 1;
        if (isset($_GET['page']))
            $page = $_GET['page'];
        $em = $this->getDoctrine()->getManager();
        $paginator = $this->get('ideup.simple_paginator');
        $paginator->setItemsPerPage($items_per_page);
        $seasons = $paginator->paginate($em->getRepository('mycpBundle:season')->getAll())->getResult();

        $service_log = $this->get('log');
        $service_log->saveLog('Visit', BackendModuleName::MODULE_SEASON);

        return $this->render('mycpBundle:season:list.html.twig', array(
                    'seasons' => $seasons,
                    'items_per_page' => $items_per_page,
                    'current_page' => $page,
                    'total_items' => $paginator->getTotalItems()
        ));
    }

    public function newAction(Request $request) {
        $service_security= $this->get('Secure');
        $service_security->verifyAccess();
        $em = $this->getDoctrine()->getManager();
        $season = new season();
        $destinations = $em->getRepository('mycpBundle:destination')->findAll();
        $data['destinations'] = $destinations;
        $data['season_types'] = season::getSeasonTypes();
        $form = $this->createForm(new seasonType($data), $season);
        $count_errors = 0;
        if ($request->getMethod() == 'POST') {
            $post_form = $request->get('mycp_mycpbundle_seasontype');
            $form->handleRequest($request);
            if($post_form['season_type'] == season::SEASON_TYPE_SPECIAL && (!isset($post_form['season_destination']) || $post_form['season_destination'] == ""))
            {
                $message = 'Para crear una temporada especial tiene que seleccionar un destino';
                $this->get('session')->getFlashBag()->add('message_error_main', $message);
                $count_errors++;
            }

            if($post_form['season_type'] == season::SEASON_TYPE_SPECIAL && (!isset($post_form['season_reason']) || trim($post_form['season_reason']) == ""))
            {
                $message = 'Para crear una temporada especial tiene que escribir un motivo';
                $this->get('session')->getFlashBag()->add('message_error_main', $message);
                $count_errors++;
            }

            if ($form->isValid() && $count_errors == 0) {
                if (isset($post_form['season_destination'])) {
                    $dest = $em->getRepository('mycpBundle:destination')->find($post_form['season_destination']);
                    $season->setSeasonDestination($dest);
                }
                $season->setSeasonReason(trim($post_form['season_reason']));
                $em->persist($season);
                $em->flush();
                $message = 'Temporada añadida satisfactoriamente.';
                $this->get('session')->getFlashBag()->add('message_ok', $message);

                $service_log = $this->get('log');
                $service_log->saveLog('Create entity from ' . $post_form['season_startdate'] . " to " . $post_form['season_enddate'], BackendModuleName::MODULE_SEASON);

                return $this->redirect($this->generateUrl('mycp_list_season'));
            }
        }
        return $this->render('mycpBundle:season:new.html.twig', array('form' => $form->createView(), 'data' => $data));
    }

    public function editAction($id_season, Request $request) {
        $service_security= $this->get('Secure');
        $service_security->verifyAccess();
        $em = $this->getDoctrine()->getManager();
        $season = $em->getRepository('mycpBundle:season')->find($id_season);

        $current_destination = $season->getSeasonDestination();
        if(isset($current_destination))
            $season->setSeasonDestination($current_destination->getDesId());

        $data = array();
        $destinations = $em->getRepository('mycpBundle:destination')->findAll();
        $data['destinations'] = $destinations;
        $data['season_types'] = season::getSeasonTypes();
        $form = $this->createForm(new seasonType($data), $season);
        $count_errors = 0;

        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);
            $post_form = $request->get('mycp_mycpbundle_seasontype');

            if($post_form['season_type'] == season::SEASON_TYPE_SPECIAL && (!isset($post_form['season_destination']) || $post_form['season_destination'] == ""))
            {
                $message = 'Para crear una temporada especial tiene que seleccionar un destino';
                $this->get('session')->getFlashBag()->add('message_error_main', $message);
                $count_errors++;
            }

            if($post_form['season_type'] == season::SEASON_TYPE_SPECIAL && (!isset($post_form['season_reason']) || trim($post_form['season_reason']) == ""))
            {
                $message = 'Para crear una temporada especial tiene que escribir un motivo';
                $this->get('session')->getFlashBag()->add('message_error_main', $message);
                $count_errors++;
            }

            if ($form->isValid() && $count_errors == 0) {
                if (isset($post_form['season_destination'])) {
                    $dest = $em->getRepository('mycpBundle:destination')->find($post_form['season_destination']);
                    $season->setSeasonDestination($dest);
                }

                if(isset($post_form['season_reason']))
                    $season->setSeasonReason(trim($post_form['season_reason']));
                $em->persist($season);
                $em->flush();
                $message = 'Temporada actualizado satisfactoriamente.';
                $this->get('session')->getFlashBag()->add('message_ok', $message);

                $service_log = $this->get('log');
                $service_log->saveLog('Edit entity from ' . $post_form['season_startdate'] . " to " . $post_form['season_enddate'], BackendModuleName::MODULE_SEASON);

                return $this->redirect($this->generateUrl('mycp_list_season'));
            }
        }
        return $this->render('mycpBundle:season:new.html.twig', array('form' => $form->createView(), 'data' => $data, 'id_season' => $id_season, 'edit' => true));
    }

    public function deleteAction($id_season)
    {
        $service_security= $this->get('Secure');
        $service_security->verifyAccess();
        $em = $this->getDoctrine()->getManager();
        $season=$em->getRepository('mycpBundle:season')->find($id_season);

        $season_start=$season->getSeasonStartDate();
        $season_end=$season->getSeasonEndDate();
        $em->remove($season);
        $em->flush();
        $message='La temporada se ha eliminado satisfactoriamente.';
        $this->get('session')->getFlashBag()->add('message_ok',$message);

        $service_log= $this->get('log');
        $service_log->saveLog('Delete entity from ' . date("d/m/Y",$season_start->getTimestamp()) . " to " . date("d/m/Y",$season_end->getTimestamp()),BackendModuleName::MODULE_SEASON);

        return $this->redirect($this->generateUrl('mycp_list_season'));
    }

}
