<?php

namespace MyCp\CasaModuleBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use MyCp\CasaModuleBundle\Form\ownershipStep1Type;

class DefaultController extends Controller
{
    public function indexAction()
    {
        $user=$this->getUser();
        if(empty($user->getUserUserCasa()))
            return new NotFoundHttpException('El usuario no es usuario casa');
        $ownership=  $user->getUserUserCasa()[0]->getUserCasaOwnership();
        $form=$this->createForm(new ownershipStep1Type(),$ownership);

        return $this->render('MyCpCasaModuleBundle:Default:index.html.twig', array(
            'ownership'=>$ownership,
            'form'=>$form->createView()
        ));
    }
}
