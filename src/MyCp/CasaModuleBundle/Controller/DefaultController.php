<?php

namespace MyCp\CasaModuleBundle\Controller;

use MyCp\CasaModuleBundle\Form\ownershipStepPhotosType;
use MyCp\mycpBundle\Entity\ownershipPhoto;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use MyCp\CasaModuleBundle\Form\ownershipStep1Type;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use MyCp\mycpBundle\Entity\ownershipStatus;


class DefaultController extends Controller
{
    public function indexAction()
    {
        $user=$this->getUser();
        if(empty($user->getUserUserCasa()))
            return new NotFoundHttpException('El usuario no es usuario casa');
        $ownership=  $user->getUserUserCasa()[0]->getUserCasaOwnership();
        if($ownership->getOwnLangs()){
            if(substr($ownership->getOwnLangs(),0,1))
                $langs[]='1000';
            if(substr($ownership->getOwnLangs(),1,1))
                $langs[]='0100';
            if(substr($ownership->getOwnLangs(),2,1))
                $langs[]='0010';
            if(substr($ownership->getOwnLangs(),3,1))
                $langs[]='0001';
        }
        if($ownership->getOwnStatus()->getStatusId()==ownershipStatus::STATUS_ACTIVE){
            return $this->render('MyCpCasaModuleBundle:Default:dashboard.html.twig', array(
                'ownership'=>$ownership,
                'dashboard'=>true,
                'langs'=>$langs
            ));
        }
        else{
            $form=$this->createForm(new ownershipStep1Type(),$ownership);
            $photosForm=$this->createForm(new ownershipStepPhotosType(),$ownership,array( 'action' => $this->generateUrl('save_step6'), 'attr' =>['id'=>'mycp_mycpbundle_ownership_step_photos']));
            return $this->render('MyCpCasaModuleBundle:Default:register.html.twig', array(
                'ownership'=>$ownership,
                'form'=>$form->createView(),
                'photoForm'=>$photosForm->createView(),
                'dashboard'=>false,
                'langs'=>$langs
            ));
        }

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
            'ownership'=>$ownership,
            'dashboard' => true
        ));
    }
}
