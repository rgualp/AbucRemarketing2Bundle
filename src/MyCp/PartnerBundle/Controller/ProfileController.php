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

class ProfileController extends Controller
{
    /**
     * @return JsonResponse
     */
    public function profileAgencyAction(){
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $tourOperator = $em->getRepository("PartnerBundle:paTourOperator")->findOneBy(array("tourOperator" => $user->getUserId()));
        $form = $this->createForm(new paTravelAgencyType($this->get('translator')),$tourOperator->getTravelAgency());
        return new JsonResponse([
            'success' => true,
            'id' => 'id_dashboard_profile_agency',
            'html' => $this->renderView('PartnerBundle:Profile:profile_agency.html.twig', array( 'form'=>$form->createView())),
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
}
