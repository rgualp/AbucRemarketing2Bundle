<?php

namespace MyCp\mycpBundle\Controller;

use MyCp\mycpBundle\Helpers\RedirectCode;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use MyCp\mycpBundle\Entity\overrideuser;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use MyCp\mycpBundle\Form\overrideUserType;

class BackendOverrideController extends Controller {

    /**
     * @param $items_per_page
     * @param Request $request
     * @return Response
     */
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

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
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

    /**
     * @param $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteAction($id){
        $service_security = $this->get('Secure');
        $service_security->verifyAccess();
        $em = $this->getDoctrine()->getManager();

        $item = $em->getRepository('mycpBundle:overrideuser')->find($id);
        $user = $item->getOverrideTo();
        $user->setUserPassword($item->getOverridePassword());
        $item->setOverrideEnable(false);
        $em->persist($item);
        $em->persist($user);
        $em->flush();
        return $this->redirect($this->generateUrl('mycp_list_override_user'));
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function findAction(Request $request){
        $service_security = $this->get('Secure');
        $service_security->verifyAccess();
        $em = $this->getDoctrine()->getManager();

        $name = $request->get('name');
        $service_security = $this->get('Secure');
        $service_security->verifyAccess();
        $item = $em->getRepository('mycpBundle:user')->findBy(array('user_name' => $name));
        if(count($item))
            return new JsonResponse(['success' => true, 'iduser' =>$item[0]->getUserId(),'name'=> $item[0]->getUserUserName() . ' ' . $item[0]->getUserLastName(),'email'=>$item[0]->getUserEmail()]);
        else
            return new JsonResponse(['success' => false, 'iduser' =>'','name'=> '','email'=>'']);


    }

    /**
     * @param Request $request
     * @param $id
     * @return JsonResponse
     */
    public function overrideAction(Request $request,$id){
        $service_security = $this->get('Secure');
        $service_security->verifyAccess();
        $em = $this->getDoctrine()->getManager();

        if($id!=''){
            $overrideUser = $em->getRepository('mycpBundle:overrideuser')->find($id);
            $user_override_by = $overrideUser->getOverrideBy();
            $user_override_to = $overrideUser->getOverrideTo();

        }
        else{
            $user_override_by = $em->getRepository('mycpBundle:user')->find($request->get('idOverrideBy'));
            $user_override_to = $em->getRepository('mycpBundle:user')->find($request->get('idOverrideTo'));

            $overrideUser = new overrideuser();
            $overrideUser->setReason($request->get('reason'));
            $overrideUser->setOverrideTo($user_override_to);
            $overrideUser->setOverrideBy($user_override_by);
        }
        $overrideUser->setOverrideDate(new \DateTime(date('Y-m-d')));
        $overrideUser->setOverridePassword($user_override_to->getUserPassword());
        $overrideUser->setOverrideEnable(true);

        $em->persist($overrideUser);

        $user_override_to->setUserPassword($user_override_by->getUserPassword());
        $em->persist($user_override_to);
        $em->flush();
        if ( $request->isXmlHttpRequest() )
            return new JsonResponse(['success' => true]);
        else
            return $this->redirect($this->generateUrl('mycp_list_override_user'));
    }

}
