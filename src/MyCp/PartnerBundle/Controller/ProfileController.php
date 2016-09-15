<?php

namespace MyCp\PartnerBundle\Controller;

use MyCp\mycpBundle\Form\restorePasswordUserType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use MyCp\PartnerBundle\Entity\paTravelAgency;
use MyCp\PartnerBundle\Entity\paTourOperator;

use MyCp\PartnerBundle\Form\paTravelAgencyType;
use Symfony\Component\HttpFoundation\JsonResponse;
use MyCp\mycpBundle\Entity\user;
use MyCp\mycpBundle\Entity\photo;

class ProfileController extends Controller
{
    /**
     * @return JsonResponse
     */
    public function profileAgencyAction(){
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $tourOperator = $em->getRepository("PartnerBundle:paTourOperator")->findOneBy(array("tourOperator" => $user->getUserId()));
        $agency = $tourOperator->getTravelAgency();
        $form = $this->createForm(new paTravelAgencyType($this->get('translator'),true),$agency);
        return new JsonResponse([
            'success' => true,
            'id' => 'id_dashboard_profile_agency',
            'html' => $this->renderView('PartnerBundle:Profile:profile_agency.html.twig', array( 'form'=>$form->createView(), 'email'=>$agency->getEmail()))
        ]);
    }

    /**
     * @param Request $request
     */
    public function changePasswordAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $password=$request->get('password');
        $old_password=$request->get('old-password');

        $user = $this->getUser();
        $user_password=$user->getPassword();

        $factory = $this->get('security.encoder_factory');

        $user2 = new user();
        $encoder = $factory->getEncoder($user2);
        $oldpass = $encoder->encodePassword($old_password, $user2->getSalt());

        $password = $encoder->encodePassword($password, $user->getSalt());
        if($user_password==$oldpass){
            $user->setUserPassword($password);
            $em->persist($user);
            $em->flush();
            return new JsonResponse(array('success'=>true,'message'=>$this->get('translator')->trans("msg.change.password.satisfactory")));
        }
        else
            return new JsonResponse(array('success'=>false,'message'=>$this->get('translator')->trans("msg.change.password.invalid")));
    }

    /**
     * @param Request $request
     */
    public function deactivateAgencyAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $emailAgency=$request->get('emailAgency');
        $passwordAgency=$request->get('passwordAgency');
        $user = $this->getUser();
        $factory = $this->get('security.encoder_factory');
        $user2 = new user();
        $encoder = $factory->getEncoder($user2);
        $pass = $encoder->encodePassword($passwordAgency, $user2->getSalt());
        if(($emailAgency==$user->getUserName()) && ($pass==$user->getPassword())){
            $user->setUserEnabled(false);
            $em->persist($user);
            $em->flush();
            return new JsonResponse(array('success'=>true));
        }
        else
            return new JsonResponse(array('success'=>false,'message'=>$this->get('translator')->trans("msg.password.email.invalid")));
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function updateAgencyAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $tourOperator = $em->getRepository("PartnerBundle:paTourOperator")->findOneBy(array("tourOperator" => $user->getUserId()));
        $obj=$tourOperator->getTravelAgency();
        $form = $this->createForm(new paTravelAgencyType($this->get('translator')),$tourOperator->getTravelAgency());
        if(!$request->get('formEmpty')){
            $form->handleRequest($request);
                $em->persist($obj);
                $em->flush();
                return new JsonResponse(['success' => true, 'message' => 'Se ha modificado satisfactoriamente']);
        }
    }
    /**
     * @param Request $request
     */
    public function saveAvatarAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        //subir photo
        $dir = $this->container->getParameter('user.dir.photos');
        $file = $request->files->get('file');
        if (isset($file)) {
            $photo = new photo();
            $fileName = uniqid('user-') . '-photo.jpg';
            $file->move($dir, $fileName);
            //Redimensionando la foto del usuario
            \MyCp\mycpBundle\Helpers\Images::resize($dir . $fileName, 150);
            $photo->setPhoName($fileName);
            $user->setUserPhoto($photo);
            $em->persist($photo);
        }
        $em->persist($user);
        $em->flush();
        return new JsonResponse([
            'success' => true,
            'dir'=>$fileName
        ]);
    }
}
