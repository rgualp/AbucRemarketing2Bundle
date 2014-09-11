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
        /* $service_security= $this->get('Secure');
          $service_security->verifyAccess(); */
        $page = 1;
        if (isset($_GET['page']))
            $page = $_GET['page'];
        $em = $this->getDoctrine()->getEntityManager();
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
        /* $service_security= $this->get('Secure');
          $service_security->verifyAccess(); */
        $em = $this->getDoctrine()->getEntityManager();
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
            
            
            if ($form->isValid() && $count_errors == 0) {                
                if (isset($post_form['season_destination'])) {
                    $dest = $em->getRepository('mycpBundle:destination')->find($post_form['season_destination']);
                    $season->setSeasonDestination($dest);
                }
                $em->persist($season);
                $em->flush();
                $message = 'Temporada añadida satisfactoriamente.';
                $this->get('session')->getFlashBag()->add('message_ok', $message);

                $service_log = $this->get('log');
                $service_log->saveLog('Create entity from' . $post_form['season_startdate'] . " to " . $post_form['season_enddate'], BackendModuleName::MODULE_SEASON);

                return $this->redirect($this->generateUrl('mycp_list_season'));
            }
        }
        return $this->render('mycpBundle:season:new.html.twig', array('form' => $form->createView(), 'data' => $data));
    }

}
