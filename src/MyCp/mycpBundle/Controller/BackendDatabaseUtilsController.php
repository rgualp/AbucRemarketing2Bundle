<?php

namespace MyCp\mycpBundle\Controller;

use MyCp\mycpBundle\Entity\permission;
use MyCp\mycpBundle\Entity\rolePermission;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class BackendDatabaseUtilsController extends Controller {

    function execute_queryAction(Request $request)
    {
        $em= $this->getDoctrine()->getManager();
        $role= $em->getRepository('mycpBundle:role')->find(4);

        $permission= new permission();
        $permission->setPermDescription('Visualizar Categorías');
        $permission->setPermCategory('Destinos');
        $permission->setPermRoute('mycp_list_category_destination');
        $em->persist($permission);
        $em->flush();

        $role_perm= new rolePermission();
        $role_perm->setRpPermission($permission);
        $role_perm->setRpRole($role);
        $em->persist($role_perm);
        $em->flush();


        $permission= new permission();
        $permission->setPermDescription('Adicionar Categoría');
        $permission->setPermCategory('Destinos');
        $permission->setPermRoute('mycp_new_category_destination');
        $em->persist($permission);
        $em->flush();

        $role_perm= new rolePermission();
        $role_perm->setRpPermission($permission);
        $role_perm->setRpRole($role);
        $em->persist($role_perm);
        $em->flush();

        $permission= new permission();
        $permission->setPermDescription('Editar Categoría');
        $permission->setPermCategory('Destinos');
        $permission->setPermRoute('mycp_edit_category_destination');
        $em->persist($permission);
        $em->flush();

        $role_perm= new rolePermission();
        $role_perm->setRpPermission($permission);
        $role_perm->setRpRole($role);
        $em->persist($role_perm);
        $em->flush();


        $permission= new permission();
        $permission->setPermDescription('Eliminar Categoría');
        $permission->setPermCategory('Destinos');
        $permission->setPermRoute('mycp_delete_category_destination');
        $em->persist($permission);
        $em->flush();

        $role_perm= new rolePermission();
        $role_perm->setRpPermission($permission);
        $role_perm->setRpRole($role);
        $em->persist($role_perm);
        $em->flush();



        return $this->render('mycpBundle:database:execute.html.twig');
    }

}
