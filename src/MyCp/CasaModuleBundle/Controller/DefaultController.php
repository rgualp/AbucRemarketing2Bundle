<?php

namespace MyCp\CasaModuleBundle\Controller;

use MyCp\CasaModuleBundle\Form\ownershipStepPhotosType;
use MyCp\mycpBundle\Entity\ownershipPhoto;
use MyCp\mycpBundle\Helpers\BackendModuleName;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use MyCp\CasaModuleBundle\Form\ownershipStep1Type;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use MyCp\mycpBundle\Entity\ownershipStatus;


class DefaultController extends Controller
{
    public function indexAction()
    {
        $user=$this->getUser();
        $em = $this->getDoctrine()->getManager();

        if($user->getUserUserCasa()->isEmpty())
            throw new AccessDeniedHttpException('Sorry, this section is only for owners!!!');
        $ownership=  $user->getUserUserCasa()[0]->getUserCasaOwnership();

        $ranking = $em->getRepository("mycpBundle:ownership")->getRankingFormula($ownership);

        //$canPublish = $em->getRepository("mycpBundle:ownership")->getFacturacionMes($code);

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
//            return $this->render('MyCpCasaModuleBundle:Default:dashboard.html.twig', array(
//                'ownership'=>$ownership,
//                'dashboard'=>true,
//                'langs'=>(isset($langs))?$langs:array()
//            ));
            return new RedirectResponse($this->generateUrl('show_property'));
        }
        else{
            $form=$this->createForm(new ownershipStep1Type($ownership->getOwnAddressProvince()),$ownership);
            $photosForm=$this->createForm(new ownershipStepPhotosType(),$ownership,array( 'action' => $this->generateUrl('save_step6'), 'attr' =>['id'=>'mycp_mycpbundle_ownership_step_photos']));
            return $this->render('MyCpCasaModuleBundle:Default:register.html.twig', array(
                'ownership'=>$ownership,
                'form'=>$form->createView(),
                'photoForm'=>$photosForm->createView(),
                'dashboard'=>false,
                'langs'=>(isset($langs))?$langs:array(),
                //'price'=>$canPublish,
                'ranking'=>$ranking
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


    public function commentsAction(Request $request)
    {
        $sort_by=$request->get('sort_by');
        if($sort_by=='null') $sort_by=  \MyCp\mycpBundle\Helpers\OrderByHelper::DEFAULT_ORDER_BY;
        $em = $this->getDoctrine()->getManager();

        $user = $this->get('security.context')->getToken()->getUser();

        if($user->getUserRole()!='ROLE_CLIENT_CASA')
            $comments_list = $em->getRepository('mycpBundle:comment')->getAll(null,null,null, null,$sort_by);
        else
        {
            $user_casa = $em->getRepository('mycpBundle:userCasa')->getByUser($user->getUserId());
            $comments_list = $em->getRepository('mycpBundle:comment')->getByUserCasa(null,null,null, null,$sort_by, $user_casa->getUserCasaId());
        }


        $service_log= $this->get('log');
        $service_log->saveLog('Visit module',  BackendModuleName::MODULE_LODGING_COMMENT);

        $ownership = $this->getUser()->getUserUserCasa()[0]->getUserCasaOwnership();
        return $this->render('MyCpCasaModuleBundle:Default:comment.html.twig', array(
            'ownership'=>$ownership,
            'dashboard'=>true,
            'comments' => $comments_list,
            'sort_by' => $sort_by
        ));
    }


    public function showAdviceAction(Request $request)
    {
        $ownership = $this->getUser()->getUserUserCasa()[0]->getUserCasaOwnership();
        return $this->render('MyCpCasaModuleBundle:Steps:advice.html.twig', array(
                'ownership'=>$ownership,
                'dashboard'=>true)
        );
    }

    public function manualMyCRDownloadAction(){
        //variable
        $pathToFile = $this->container->getParameter("configuration.dir.additionalsFiles");

        $response = new BinaryFileResponse($pathToFile.'Instructivo_para_propietarios_de_MyCasa_Renta_v2.0.pdf');
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT,'Instructivo_para_propietarios_de_MyCasa_Renta_v2.0.pdf');
        return $response;
    }
}
