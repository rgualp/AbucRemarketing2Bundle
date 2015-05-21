<?php
/**
 * Created by PhpStorm.
 * User: Neti
 * Date: 16/05/2015
 * Time: 10:26
 */

namespace MyCp\mycpBundle\Controller;
use Doctrine\ORM\EntityRepository;
use MyCp\mycpBundle\Entity\permission;
use MyCp\mycpBundle\Form\roleType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use MyCp\mycpBundle\Entity\user;
use MyCp\mycpBundle\Entity\userCasa;
use MyCp\mycpBundle\Entity\role;
use MyCp\mycpBundle\Entity\rolePermission;
use MyCp\mycpBundle\Helpers\BackendModuleName;
use \MyCp\FrontEndBundle\Helpers\Utils;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;


class BackendRbacController extends Controller {

    function addRoleAction(Request $request){
//        throw new NotFoundHttpException("Under construction");
        //        $service_security= $this->get('Secure');
//        $service_security->verifyAccess();
   /*     $rol=new role();
        $em=$this->getDoctrine()->getManager();
        $privileges = $em->getRepository('mycpBundle:permission')->findAll();
        foreach($privileges as $privilege){
            $rp = new rolePermission();
            $rp->setRpPermission($privilege);
            $rp->setRpRole($rol);
            $rol->addPermission($rp);
        }
        $form = $this->createForm(new roleType(), $rol);*/
        $em=$this->getDoctrine()->getManager();
        $roles=$em->getRepository('mycpBundle:role')->findAll();
        $repository = $em->getRepository('mycpBundle:permission');
        $query = $repository->createQueryBuilder('p')
            ->orderBy('p.perm_category', 'ASC')->getQuery();
        $privileges = $query->getResult();
        $cat='';
        $arr=array();
        foreach($privileges as $privilege){
            $arr["".$privilege->getPermId()]=$privilege->getPermDescription();
        /* if($privilege->getPermCategory()!=$cat){
             $cat=$privilege->getPermCategory();
          $arr[$cat]=array();
         }
            $arr[$cat][$privilege->getPermId()]=$privilege->getPermDescription();
            */

        }
     //   dump($arr); die;
        $role=new role();
        $form = $this->createFormBuilder($role)
            ->add('role_name')
            ->add('role_parent','choice', array(
                'choices'=>$roles,
                'placeholder'=>'Selecciona el rol padre'
            ))
            ->add('role_fixed')
            ->add('permissions','entity', array(
                                'class' => 'mycpBundle:permission',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('p')
                        ->orderBy('p.perm_category', 'ASC');
                    //  ->groupBy('p.perm_category');
                },
                'expanded'=>true,
                'multiple'=>true
            ))
//            ->add('permissions', 'entity', array(
//                'class' => 'mycpBundle:permission',
//                'query_builder' => function (EntityRepository $er) {
//                    return $er->createQueryBuilder('p')
//                        ->orderBy('p.perm_category', 'ASC');
//                    //  ->groupBy('p.perm_category');
//                },
//                'expanded'=>true,
//                'multiple'=>true
//            ))
            ->getForm();
        $form->handleRequest($request);
        if($form->isValid()){

        }
        return $this->render('mycpBundle:rbac:add_role.html.twig', array(
            'form' => $form->createView()
        ));
    }
    function editRoleAction($id, Request $request){
        throw new NotFoundHttpException("Under construction");
    }
    function deleteRoleAction($id, Request $request){
        throw new NotFoundHttpException("Under construction");
    }
    function listRolesAction(Request $request){
        //        throw new NotFoundHttpException("Under construction");
//        $service_security= $this->get('Secure');
//        $service_security->verifyAccess();
        $page= $request->get('page')?$request->get('page'):1;
        $items_per_page= $request->get('items_per_page')?$request->get('items_per_page'):20;
        $em=$this->getDoctrine()->getManager();
        $paginator = $this->get('ideup.simple_paginator');
        $paginator->setItemsPerPage($items_per_page);
        $roles = $paginator->paginate($em->getRepository('mycpBundle:role')->findAll())->getResult();

        //$service_log = $this->get('log');
        //  $service_log->saveLog('Visit', BackendModuleName::MODULE_METATAGS);

        return $this->render('mycpBundle:rbac:list_roles.html.twig', array(
            'roles' => $roles,
            'items_per_page' => $items_per_page,
            'current_page' => $page,
            'total_items' => $paginator->getTotalItems()
        ));
    }
    function addPrivilegeAction(Request $request){
        //        throw new NotFoundHttpException("Under construction");
        //        $service_security= $this->get('Secure');
//        $service_security->verifyAccess();
        $permission=new permission();
        $em=$this->getDoctrine()->getManager();
        //$privileges = $em->getRepository('mycpBundle:permission')->findAll();
        $routes = $this->get('service_container')->get('router')->getRouteCollection()->all();
        $routesArr=array();
        foreach($routes as $key=>$value){
            if ($key[0]!='_')
             $routesArr[$key]=$key;
        }
        //dump($routesArr); die;
        $form = $this->createFormBuilder($permission)
        ->add('perm_description','text',array(
            'label'=> 'Permiso',
            'attr'=>array(
                'class'=> 'span6'
            )
        ))
        ->add('perm_category','text',array(
            'label'=> 'Categoría',
            'attr'=>array(
                'class'=> 'span6'
            )
        ))
         ->add('perm_route','choice', array(
             'choices'=>$routesArr,
             'label'=> 'Ruta Asociada',
             'attr'=>array(
                 'class'=> 'span6 select2'
             )
         ))
            ->getForm()
        ;
        $form->handleRequest($request);
        if($form->isValid()){
            $em->persist($permission);
            $em->flush();
            $request->getSession()->getFlashBag()->add(
                'success',
                'El permiso fue creado'
            );
            return $this->redirectToRoute('mycp_list_privileges');

        }
        return $this->render('mycpBundle:rbac:add_permission.html.twig', array(
            'form' => $form->createView()
        ));
    }
    function editPrivilegeAction($id, Request $request){

        $em=$this->getDoctrine()->getManager();
        $permission=$em->getRepository('mycpBundle:permission')->findOneBy(array('perm_id'=>$id));
        //$privileges = $em->getRepository('mycpBundle:permission')->findAll();
        $routes = $this->get('service_container')->get('router')->getRouteCollection()->all();
        $routesArr=array();
        foreach($routes as $key=>$value){
            if ($key[0]!='_')
                $routesArr[$key]=$key;
        }
        //dump($routesArr); die;
        $form = $this->createFormBuilder($permission)
            ->add('perm_description','text',array(
                'label'=> 'Permiso',
                'attr'=>array(
                    'class'=> 'span6'
                )
            ))
            ->add('perm_category','text',array(
                'label'=> 'Categoría',
                'attr'=>array(
                    'class'=> 'span6'
                )
            ))
            ->add('perm_route','choice', array(
                'choices'=>$routesArr,
                'label'=> 'Ruta Asociada',
                'attr'=>array(
                    'class'=> 'span6 select2'
                )
            ))
            ->getForm()
        ;
        $form->handleRequest($request);
        if($form->isValid()){
            $em->persist($permission);
            $em->flush();
            $request->getSession()->getFlashBag()->add(
                'success',
                'El permiso fue actualizado'
            );
            return $this->redirectToRoute('mycp_list_privileges');

        }
        return $this->render('mycpBundle:rbac:edit_permission.html.twig', array(
            'form' => $form->createView(),
            'permission'=>$permission
        ));
    }
    function deletePrivilegeAction($id, Request $request){
        throw new NotFoundHttpException("Under construction");
    }
    function listPrivilegesAction(Request $request){
//        throw new NotFoundHttpException("Under construction");
//        $service_security= $this->get('Secure');
//        $service_security->verifyAccess();
        $page= $request->get('page')?$request->get('page'):1;
        $items_per_page= $request->get('items_per_page')?$request->get('items_per_page'):20;
        $em=$this->getDoctrine()->getManager();
        $paginator = $this->get('ideup.simple_paginator');
        $paginator->setItemsPerPage($items_per_page);
        $permissions = $paginator->paginate($em->getRepository('mycpBundle:permission')->findAll())->getResult();

        //$service_log = $this->get('log');
      //  $service_log->saveLog('Visit', BackendModuleName::MODULE_METATAGS);

        return $this->render('mycpBundle:rbac:list_permission.html.twig', array(
            'permissions' => $permissions,
            'items_per_page' => $items_per_page,
            'current_page' => $page,
            'total_items' => $paginator->getTotalItems()
        ));
    }
}