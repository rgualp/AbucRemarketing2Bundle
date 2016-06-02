<?php

namespace MyCp\CasaModuleBundle\Controller;

use MyCp\CasaModuleBundle\Form\ownershipStepPhotosType;
use MyCp\mycpBundle\Entity\ownershipPhoto;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use MyCp\CasaModuleBundle\Form\ownershipStep1Type;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class DefaultController extends Controller
{
    public function indexAction()
    {
        $user=$this->getUser();
        if(empty($user->getUserUserCasa()))
            return new NotFoundHttpException('El usuario no es usuario casa');
        $ownership=  $user->getUserUserCasa()[0]->getUserCasaOwnership();
        $form=$this->createForm(new ownershipStep1Type(),$ownership);
//        $owp= new ownershipPhoto();
//        $owp->setOwnPhoOwn($ownership);
//        $ownership->addPhoto($owp);
        $photosForm=$this->createForm(new ownershipStepPhotosType(),$ownership,array( 'action' => $this->generateUrl('save_step6'), 'attr' =>['id'=>'mycp_mycpbundle_ownership_step_photos']));
        return $this->render('MyCpCasaModuleBundle:Default:index.html.twig', array(
            'ownership'=>$ownership,
            'form'=>$form->createView(),
            'photoForm'=>$photosForm->createView()
        ));
    }
    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response|NotFoundHttpException
     *
     */

    public function ownershipCalendarAction(Request $request){
        $user=$this->getUser();
        if(empty($user->getUserUserCasa()))
            return new NotFoundHttpException('El usuario no es usuario casa');
        $ownership=  $user->getUserUserCasa()[0]->getUserCasaOwnership();
        return $this->render('MyCpCasaModuleBundle:Default:calendar.html.twig', array(
            'ownership'=>$ownership
        ));
    }
}
