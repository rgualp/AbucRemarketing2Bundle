<?php

namespace MyCp\FrontEndBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\SecurityContext;

class MunicipalityController extends Controller {

    public function findAction() {
        $request = $this->getRequest();
        $em = $this->getDoctrine()->getManager();

        $prov_id = $request->request->get('province');

        $mun_total_owns = array();

        $municipalities = $em->getRepository('mycpBundle:municipality')->findBy(array('mun_prov_id' => $prov_id));
        $mun_total = 0;
        $total = 0;

        foreach ($municipalities as $mun) {
            $total = count($em->getRepository('mycpBundle:ownership')->findBy(array('own_address_municipality' => $mun->getMunId(),
                        'own_status' => \MyCp\mycpBundle\Entity\ownershipStatus::STATUS_ACTIVE)));
            $mun_total_owns[$mun->getMunId()] = $total;

            if ($total > 0)
                $mun_total = $mun_total + 1;
        }

        $response = $this->renderView('FrontEndBundle:municipality:municipios_cascade.html.twig', array(
            'mun_total' => $mun_total,
            'municipalities' => $municipalities,
            'mun_total_owns' => $mun_total_owns
                ));

        return new Response($response, 200);
    }

    public function findForDestinationsAction() {
        $request = $this->getRequest();
        $em = $this->getDoctrine()->getManager();

        $prov_id = $request->request->get('province');

        $total_destination_municipality = array();

        $municipalities = $em->getRepository('mycpBundle:municipality')->findBy(array('mun_prov_id' => $prov_id));
        $mun_total = 0;
        $total = 0;

        foreach ($municipalities as $mun) {
            $total = count($em->getRepository('mycpBundle:destinationLocation')->findBy(array('des_loc_municipality' => $mun->getMunId())));
            $total_destination_municipality[$mun->getMunId()] = $total;

            if ($total > 0)
                $mun_total = $mun_total + 1;
        }

        $response = $this->renderView('FrontEndBundle:municipality:municipios_cascade_destination.html.twig', array(
            'mun_total' => $mun_total,
            'municipalities' => $municipalities,
            'total_destination_mun' => $total_destination_municipality
                ));
        return new Response($response, 200);
    }

    public function getWithReservationsAction() {
        $em = $this->getDoctrine()->getManager();

        $paginator = $this->get('ideup.simple_paginator');
        $items_per_page = 15;
        $paginator->setItemsPerPage($items_per_page);
        $municipalities = $paginator->paginate($em->getRepository('mycpBundle:municipality')->getWithReservations())->getResult();
        $page = 1;
        if (isset($_GET['page']))
            $page = $_GET['page'];

        return $this->render('FrontEndBundle:municipality:municipalityWithReservations.html.twig', array(
                    'list' => $municipalities,
                    'items_per_page' => $items_per_page,
                    'total_items' => $paginator->getTotalItems(),
                    'current_page' => $page,
                    'lastPage' => $paginator->getLastPage()
                ));
    }

}
