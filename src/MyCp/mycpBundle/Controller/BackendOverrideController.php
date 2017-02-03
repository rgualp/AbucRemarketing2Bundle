<?php

namespace MyCp\mycpBundle\Controller;

use MyCp\mycpBundle\Helpers\RedirectCode;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use MyCp\mycpBundle\Entity\overrideuser;
use MyCp\mycpBundle\Helpers\BackendModuleName;
use \MyCp\FrontEndBundle\Helpers\Utils;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use MyCp\mycpBundle\Form\overrideUserType;

class BackendOverrideController extends Controller {

    function listAction($items_per_page, Request $request) {
        $service_security = $this->get('Secure');
        $service_security->verifyAccess();

        $page = 1;
        if (isset($_GET['page']))
            $page = $_GET['page'];
        $em = $this->getDoctrine()->getManager();
        $paginator = $this->get('ideup.simple_paginator');
        $paginator->setItemsPerPage($items_per_page);
        $items = $paginator->paginate($em->getRepository('mycpBundle:overrideuser')->getAll())->getResult();


        return $this->render('mycpBundle:overrideUser:list.html.twig', array(
                    'list' => $items,
                    'items_per_page' => $items_per_page,
                    'current_page' => $page,
                    'total_items' => $paginator->getTotalItems()
        ));
    }
    public function addAction(Request $request){
        $service_security = $this->get('Secure');
        $service_security->verifyAccess();
        $em = $this->getDoctrine()->getManager();
        $overrideUser = new overrideuser();
        $form = $this->createForm(new overrideUserType(), $overrideUser);

        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);
            if ($form->isValid()) {

                $em->persist($overrideUser);
                $em->flush();

                $message = 'Suplantación realizada satisfactoriamente.';
                $this->get('session')->getFlashBag()->add('message_ok', $message);

                return $this->redirect($this->generateUrl('mycp_list_override_user'));
            }
        }
        return $this->render('mycpBundle:overrideUser:new.html.twig', array(
                'form' => $form->createView(),
            ));
    }
    public function editAction(){}
    public function deleteAction(){}

    public function suplantarAction(){
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('mycpBundle:user')->find(26);
        $user->setUserPassword($this->getUser()->getUserPassword());
        $em->persist($user);
        $em->flush();
        return $this->redirect($this->generateUrl('mycp_list_users'));
    }

}
