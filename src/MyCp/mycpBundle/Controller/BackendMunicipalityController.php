<?php

namespace MyCp\mycpBundle\Controller;

use MyCp\mycpBundle\Entity\log;
use MyCp\mycpBundle\Helpers\DataBaseTables;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use MyCp\mycpBundle\Entity\municipality;
use MyCp\mycpBundle\Form\municipalityType;
use MyCp\mycpBundle\Helpers\BackendModuleName;

class BackendMunicipalityController extends Controller {

    public function listAction($items_per_page) {
        $service_security= $this->get('Secure');
        $service_security->verifyAccess(); 
        $page = 1;
        if (isset($_GET['page']))
            $page = $_GET['page'];
        $em = $this->getDoctrine()->getManager();
        $paginator = $this->get('ideup.simple_paginator');
        $paginator->setItemsPerPage($items_per_page);
        $municipalities = $paginator->paginate($em->getRepository('mycpBundle:municipality')->getAll())->getResult();

//        $service_log = $this->get('log');
//        $service_log->saveLog('Visita', BackendModuleName::MODULE_MUNICIPALITY, log::OPERATION_VISIT, DataBaseTables::MUNICIPALITY);

        return $this->render('mycpBundle:municipality:list.html.twig', array(
                    'municipalities' => $municipalities,
                    'items_per_page' => $items_per_page,
                    'current_page' => $page,
                    'total_items' => $paginator->getTotalItems()
        ));
    }

    public function newAction(Request $request) {
        $service_security= $this->get('Secure');
        $service_security->verifyAccess();
        $em = $this->getDoctrine()->getManager();
        $municipality = new municipality();
        $provinces = $em->getRepository('mycpBundle:province')->findAll();
        $data['provinces'] = $provinces;
        $form = $this->createForm(new municipalityType($data), $municipality);
        if ($request->getMethod() == 'POST') {
            $post_form = $request->get('mycp_mycpbundle_municipalitytype');
            $form->handleRequest($request);
            if ($form->isValid()) {
                $province = $em->getRepository('mycpBundle:province')->find($post_form['mun_prov_id']);
                $municipality->setMunProvId($province);
                $em->persist($municipality);
                $em->flush();
                $message = 'Municipio añadido satisfactoriamente.';
                $this->get('session')->getFlashBag()->add('message_ok', $message);

                $service_log = $this->get('log');
                $service_log->saveLog($municipality->getLogDescription(), BackendModuleName::MODULE_MUNICIPALITY, log::OPERATION_INSERT, DataBaseTables::MUNICIPALITY);

                return $this->redirect($this->generateUrl('mycp_list_municipality'));
            }
        }
        return $this->render('mycpBundle:municipality:new.html.twig', array('form' => $form->createView(), 'data' => $data));
    }

    public function editAction($id_municipality, Request $request) {
        $service_security= $this->get('Secure');
        $service_security->verifyAccess();
        $em = $this->getDoctrine()->getManager();
        $municipality = $em->getRepository('mycpBundle:municipality')->find($id_municipality);
        $municipality->setMunProvId($municipality->getMunProvId()->getProvId());
        $data = array();
        $data['provinces'] = $em->getRepository('mycpBundle:province')->findAll();
        $form = $this->createForm(new municipalityType($data), $municipality);
        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);
            $post_form = $request->get('mycp_mycpbundle_municipalitytype');
            if ($form->isValid()) {
                $province = $em->getRepository('mycpBundle:province')->find($post_form['mun_prov_id']);
                $municipality->setMunProvId($province);
                $em->persist($municipality);
                $em->flush();
                $message = 'Municipio actualizado satisfactoriamente.';
                $this->get('session')->getFlashBag()->add('message_ok', $message);

                $service_log = $this->get('log');
                $service_log->saveLog($municipality->getLogDescription(), BackendModuleName::MODULE_MUNICIPALITY, log::OPERATION_UPDATE, DataBaseTables::MUNICIPALITY);

                return $this->redirect($this->generateUrl('mycp_list_municipality'));
            }
        }
        return $this->render('mycpBundle:municipality:new.html.twig', array('form' => $form->createView(), 'data' => $data, 'id_municipality' => $id_municipality, 'edit' => true));
    }

    public function deleteAction($id_municipality)
    {
        $service_security= $this->get('Secure');
        $service_security->verifyAccess();
        $em = $this->getDoctrine()->getManager();
        $municipality=$em->getRepository('mycpBundle:municipality')->find($id_municipality);
        $accommodations_total = count($em->getRepository('mycpBundle:ownership')->findBy(array('own_address_municipality'=> $municipality->getMunId())));
        $destinations_total = count($em->getRepository('mycpBundle:destinationLocation')->findBy(array('des_loc_municipality'=> $municipality->getMunId())));

        if($accommodations_total == 0 && $destinations_total == 0){
            $logDescription = $municipality->getLogDescription();
            $em->remove($municipality);
            $em->flush();
            $message='El municipio se ha eliminado satisfactoriamente.';
            $this->get('session')->getFlashBag()->add('message_ok',$message);

            $service_log = $this->get('log');
            $service_log->saveLog($logDescription, BackendModuleName::MODULE_MUNICIPALITY, log::OPERATION_DELETE, DataBaseTables::MUNICIPALITY);
        }
        else{
            $message='Error: El municipio que desea eliminar posee datos asociados.';
            $this->get('session')->getFlashBag()->add('message_error_main',$message);
        }

        return $this->redirect($this->generateUrl('mycp_list_municipality'));
    }
    
    public function list_accommodationsAction($id_municipality,$items_per_page)
    {
        $service_security= $this->get('Secure');
        $service_security->verifyAccess();
        $page = 1;
        if (isset($_GET['page']))
            $page = $_GET['page'];
        $em = $this->getDoctrine()->getManager();
        $paginator = $this->get('ideup.simple_paginator');
        $paginator->setItemsPerPage($items_per_page);
        $accommodations = $paginator->paginate($em->getRepository('mycpBundle:municipality')->getAccommodations($id_municipality))->getResult();
        $municipality = $em->getRepository('mycpBundle:municipality')->find($id_municipality);

        $service_log = $this->get('log');
        $service_log->saveLog($municipality->getLogDescription()." (Lista de alojamientos)", BackendModuleName::MODULE_MUNICIPALITY, log::OPERATION_VISIT, DataBaseTables::MUNICIPALITY);

        return $this->render('mycpBundle:municipality:accommodations.html.twig', array(
                    'accommodations' => $accommodations,
                    'items_per_page' => $items_per_page,
                    'current_page' => $page,
                    'total_items' => $paginator->getTotalItems(),
                    'municipality' => $municipality,
            
        ));
    }
    
    public function list_destinationsAction($id_municipality,$items_per_page)
    {
        $service_security= $this->get('Secure');
        $service_security->verifyAccess();
        $page = 1;
        if (isset($_GET['page']))
            $page = $_GET['page'];
        $em = $this->getDoctrine()->getManager();
        $paginator = $this->get('ideup.simple_paginator');
        $paginator->setItemsPerPage($items_per_page);
        $destinations = $paginator->paginate($em->getRepository('mycpBundle:municipality')->getDestinations($id_municipality))->getResult();

        $municipality = $em->getRepository('mycpBundle:municipality')->find($id_municipality);
        $service_log = $this->get('log');
        $service_log->saveLog($municipality->getLogDescription()." (Lista de destinos)", BackendModuleName::MODULE_MUNICIPALITY, log::OPERATION_VISIT, DataBaseTables::MUNICIPALITY);

        return $this->render('mycpBundle:municipality:destinations.html.twig', array(
                    'destinations' => $destinations,
                    'items_per_page' => $items_per_page,
                    'current_page' => $page,
                    'total_items' => $paginator->getTotalItems(),
                    'municipality' => $municipality,
            
        ));
    }

}
