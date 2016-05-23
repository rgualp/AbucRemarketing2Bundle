<?php
/**
 * Created by PhpStorm.
 * User: neti
 * Date: 13/05/2016
 * Time: 11:23
 */

namespace MyCp\CasaModuleBundle\Controller;



use MyCp\CasaModuleBundle\Form\ownershipStep1Type;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\JsonResponse;

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

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response|NotFoundHttpException
     * @Route(name="save_step4", path="/save/step4")
     */
    public function saveStep4Action(Request $request){
        print_r($request->get('form-number-1'));die;
    }

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response|NotFoundHttpException
     * @Route(name="content_tab_step4", path="/content/step4")
     */
    public function getContentTabStep4Action(Request $request){
        return new JsonResponse([
            'num'=>$request->get('num'),
            'success' => true,
            'html' => $this->renderView('MyCpCasaModuleBundle:form:form4.html.twig', array('num'=>$request->get('num'))),
            'msg' => 'Nueva habitacion']);
    }

}