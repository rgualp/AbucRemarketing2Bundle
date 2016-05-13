<?php
/**
 * Created by PhpStorm.
 * User: neti
 * Date: 13/05/2016
 * Time: 11:23
 */

namespace MyCp\CasaModuleBundle\Controller;


use FOS\RestBundle\Controller\Annotations\Route;
use MyCp\CasaModuleBundle\Form\ownershipStep1Type;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
* @Route("/ownership/edit")
 */
class StepsController extends Controller
{
    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response|NotFoundHttpException
     * @Route(name="casa_module_edit_step1", path="/step1")
     */
 public function step1Action(Request $request){
     $user=$this->getUser();
     if(empty($user->getUserUserCasa()))
         return new NotFoundHttpException('El usuario no es usuario casa');
     $ownership=  $user->getUserUserCasa()[0]->getUserCasaOwnership();
     $form=$this->createForm(new ownershipStep1Type(),$ownership);
     $form->handleRequest($request);
     if($form->isValid()){

     }
     return $this->render('MyCpCasaModuleBundle:Ownership:step1.html.twig',array(
         'ownership'=>$ownership,
         'form'=>$form->createView()
     ));

 }
}