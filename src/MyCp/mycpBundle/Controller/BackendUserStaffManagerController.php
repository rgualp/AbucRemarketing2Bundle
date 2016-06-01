<?php

namespace MyCp\mycpBundle\Controller;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityRepository;
use MyCp\mycpBundle\Entity\log;
use MyCp\mycpBundle\Entity\permission;
use MyCp\mycpBundle\Entity\userStaffManager;
use MyCp\mycpBundle\Form\roleType;
use MyCp\mycpBundle\Form\userStaffManagerDestinationType;
use MyCp\mycpBundle\Helpers\DataBaseTables;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use MyCp\mycpBundle\Entity\user;
use MyCp\mycpBundle\Entity\userCasa;
use MyCp\mycpBundle\Entity\role;
use MyCp\mycpBundle\Entity\rolePermission;
use MyCp\mycpBundle\Helpers\BackendModuleName;
use \MyCp\FrontEndBundle\Helpers\Utils;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;


class BackendUserStaffManagerController extends Controller {

    function addUserDestinationAction(Request $request){
        $em=$this->getDoctrine()->getManager();

        if($request->getMethod() == 'POST' && isset($request->get('form')['user']) && isset($request->get('form')['destinations'])){
            $idUser = $request->get('form')['user'];
            $idsDestinations = $request->get('form')['destinations'];

            $repoUserStaffManager = $em->getRepository('mycpBundle:userStaffManager');
            $userStaffManager = $repoUserStaffManager->findBy(array('user_staff_manager_user'=>$idUser));
            if(count($userStaffManager) > 0){
                $userStaffManager = $userStaffManager[0];
            }
            else{
                $userStaffManager = new userStaffManager();
                $user = $em->getRepository('mycpBundle:user')->find($idUser);
                $userStaffManager->setUserStaffManagerUser($user);
                $repoUserStaffManager->saveUserStaffManager($userStaffManager);
            }

            foreach($idsDestinations as $desId){
                $repoUserStaffManager->insertUserDestination($userStaffManager->getUserStaffManagerId(), $desId);
            }

            return $this->redirectToRoute('mycp_rbusm_list_user_dest');
        }
        else{
            $userArr = array();
            $users=$em->getRepository('mycpBundle:user')->getUsersInfoStaff();
            foreach($users as $key=>$value){
                $userArr[$value->getUserId()] = $value->getUserUserName().' ('. $value->getName().')';
            }

            $destArr= array();
            $destinations=$em->getRepository('mycpBundle:destination')->getAll();
            foreach($destinations as $key=>$value){
                $destArr[$value['des_id']] = $value['des_name'];
            }

            $form = $this->createFormBuilder()
                ->add('user','choice', array(
                    'choices'=>$userArr,
                    'label'=> 'Usuario',
                    'attr'=>array(
                        'class'=> 'span6 select2'
                    )
                ))
                ->add('destinations','choice', array(
                    'choices'=>$destArr,
                    'label'=> 'Destinos',
                    'attr'=>array(
                        'multiple'=>true,
                        'class'=> 'span6 select2'
                    )
                ))
                ->getForm();;

            return $this->render('mycpBundle:rbusm:add_user_destination.html.twig', array(
                'form' => $form->createView()
            ));
        }
    }

    function listUserDestinationAction(Request $request){
        $items_per_page= $request->get('items_per_page')?$request->get('items_per_page'):20;
        $page= $request->get('page')?$request->get('page'):1;
        $paginator = $this->get('ideup.simple_paginator');

        $em=$this->getDoctrine()->getManager();

        $repoUserStaffManager = $em->getRepository('mycpBundle:userStaffManager');
        $usersStaffManager = $repoUserStaffManager->findAll();

        return $this->render('mycpBundle:rbusm:list_user_destinations.html.twig', array(
            'users_destinations' => $usersStaffManager,
            'items_per_page' => $items_per_page,
            'current_page' => $page,
            'total_items' => $paginator->getTotalItems()
        ));
    }

    function editUserDestinationAction($id, Request $request){
        $em=$this->getDoctrine()->getManager();

        if($request->getMethod() == 'POST' && isset($request->get('form')['user']) && isset($request->get('form')['destinations'])){
            $idUser = $request->get('form')['user'];
            $idsDestinations = $request->get('form')['destinations'];

            $repoUserStaffManager = $em->getRepository('mycpBundle:userStaffManager');
            $userStaffManager = $repoUserStaffManager->findBy(array('user_staff_manager_user'=>$idUser));
            $userStaffManager = $userStaffManager[0];

            $repoUserStaffManager->clearUserDestination($userStaffManager->getUserStaffManagerId());
            foreach($idsDestinations as $desId){
                $repoUserStaffManager->insertUserDestination($userStaffManager->getUserStaffManagerId(), $desId);
            }

            return $this->redirectToRoute('mycp_rbusm_list_user_dest');
        }
        else{
            $userArr = array();
            $users=$em->getRepository('mycpBundle:user')->find($id);
            $userArr[$users->getUserId()] = $users->getUserUserName().' ('. $users->getName().')';
            $repoUserStaffManager = $em->getRepository('mycpBundle:userStaffManager');
            $userStaffManager = $repoUserStaffManager->findBy(array('user_staff_manager_user'=>$id))[0];

            $destinationsSelected = $userStaffManager->getDestinations();
            $destinationsSelectedArr = [];
            $destination = $destinationsSelected->first();
            if($destination){
                do {
                    $destinationsSelectedArr[] = $destination->getDesId();
                    $destination = $destinationsSelected->next();
                }
                while ($destination);
            }
            $destinationsSelectedArr = '['.implode(',', $destinationsSelectedArr).']';

            $destArr= array();
            $destinations=$em->getRepository('mycpBundle:destination')->getAll();
            foreach($destinations as $key=>$value){
                $destArr[$value['des_id']] = $value['des_name'];
            }

            $form = $this->createFormBuilder()
                ->add('user','choice', array(
                    'choices'=>$userArr,
                    'label'=> 'Usuario',
                    'attr'=>array(
                        'class'=> 'span6 select2'
                    )
                ))
                ->add('destinations','choice', array(
                    'choices'=>$destArr,
                    'label'=> 'Destinos',
                    'attr'=>array(
                        'multiple'=>true,
                        'class'=> 'span6 select2'
                    )
                ))
                ->getForm();

            return $this->render('mycpBundle:rbusm:edit_user_destination.html.twig', array(
                'form' => $form->createView(),
                'id' => $id,
                'destinationsSelected' => $destinationsSelectedArr
            ));
        }
    }

    function deleteUserDestinationAction($id, Request $request){
        $em=$this->getDoctrine()->getManager();

        $repoUserStaffManager = $em->getRepository('mycpBundle:userStaffManager');
        $userStaffManager = $repoUserStaffManager->findBy(array('user_staff_manager_user'=>$id))[0];

        $repoUserStaffManager->clearUserDestination($userStaffManager->getUserStaffManagerId());
        $repoUserStaffManager->removeUserStaffManager($userStaffManager);

        return $this->redirectToRoute('mycp_rbusm_list_user_dest');
    }
}