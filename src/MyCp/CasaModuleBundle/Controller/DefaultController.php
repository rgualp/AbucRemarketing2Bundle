<?php

namespace MyCp\CasaModuleBundle\Controller;

use MyCp\CasaModuleBundle\Form\ownershipStepPhotosType;
use MyCp\mycpBundle\Entity\ownershipPhoto;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use MyCp\CasaModuleBundle\Form\ownershipStep1Type;
use MyCp\mycpBundle\Entity\ownershipStatus;

class DefaultController extends Controller
{
    public function indexAction()
    {
        $user=$this->getUser();
        if(empty($user->getUserUserCasa()))
            return new NotFoundHttpException('El usuario no es usuario casa');
        $ownership=  $user->getUserUserCasa()[0]->getUserCasaOwnership();
        if($ownership->getOwnStatus()->getStatusId()==ownershipStatus::STATUS_ACTIVE){
            return $this->render('MyCpCasaModuleBundle:Default:dashboard.html.twig', array());
        }
        else{
            $form=$this->createForm(new ownershipStep1Type(),$ownership);
            $photosForm=$this->createForm(new ownershipStepPhotosType(),$ownership,array( 'action' => $this->generateUrl('save_step6'), 'attr' =>['id'=>'mycp_mycpbundle_ownership_step_photos']));
            return $this->render('MyCpCasaModuleBundle:Default:register.html.twig', array(
                'ownership'=>$ownership,
                'form'=>$form->createView(),
                'photoForm'=>$photosForm->createView()
            ));
        }

    }
}
