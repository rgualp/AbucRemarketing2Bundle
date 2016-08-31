<?php

namespace MyCp\PartnerBundle\Controller;

use MyCp\mycpBundle\Form\restorePasswordUserType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use MyCp\PartnerBundle\Entity\paTravelAgency;
use MyCp\PartnerBundle\Form\paTravelAgencyType;
use Symfony\Component\HttpFoundation\JsonResponse;

class FrontendController extends Controller
{

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(){
        $em = $this->getDoctrine()->getManager();
        $data = array();
        $data['countries'] = $em->getRepository('mycpBundle:country')->findAllByAlphabetical();
        $form = $this->createForm(new paTravelAgencyType($this->get('translator'), $data));
        return $this->render('PartnerBundle:Frontend:index.html.twig',array(
            'remoteLogin'=>true,
            'form'=>$form->createView()
        ));
    }

    /**
     * @param $route
     * @param null $routeParams
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function navBarAction($route, $routeParams = null){
        $routeParams = empty($routeParams) ? array() : $routeParams;
        $em = $this->getDoctrine()->getManager();
        $user_ids = $em->getRepository('mycpBundle:user')->getIds($this);
        $countItems = $em->getRepository('mycpBundle:favorite')->getTotal($user_ids['user_id'],$user_ids['session_id']);
        $response = $this->render('PartnerBundle:Layout:language-currency.html.twig', array(
            'route' => $route,
            'routeParams' => $routeParams,
            'count_fav'=>$countItems
        ));
        return $response;
    }

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function contentLoginAction(Request $request){
        return $this->render('LayoutBundle:Security:login-content.html.twig', array());
    }

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function contentRegisterAction(Request $request){
        return $this->render('LayoutBundle:Security:register-content.html.twig', array());
    }

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function contentForgotAction(Request $request){
        $errors = array();
        $form = $this->createForm(new \MyCp\FrontEndBundle\Form\restorePasswordUserType($this->get('translator')));
        return $this->render('LayoutBundle:Security:forgot-content.html.twig', array(
            'form' => $form->createView(),
            'errors' => $errors
        ));
    }

    /**
     * @param Request $request
     */
    public function registerAgencyAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $obj = new paTravelAgency();
        $data = array();
        $errors = array();
        $data['countries'] = $em->getRepository('mycpBundle:country')->findAllByAlphabetical();
        $form = $this->createForm(new paTravelAgencyType($this->get('translator'), $data),$obj);

        if(!$request->get('formEmpty')){
            $form->handleRequest($request);
            $post = $request->get('partner_agency');

            $user_db = $em->getRepository('mycpBundle:user')->findBy(array(
                'user_email' => $post['email'],
                'user_created_by_migration' => false));
            if ($user_db) {
                $errors['used_email'] = $this->get('translator')->trans("USER_EMAIL_IN_USE");
            }
            $validate_email = \MyCp\FrontEndBundle\Helpers\Utils::validateEmail($post['email']);
            if(!$validate_email)
                $errors['user_email'] = $this->get('translator')->trans("EMAIL_INVALID_MESSAGE");
            if(count($errors)){
                return new JsonResponse(['success' => false, 'msg' => (array_key_exists('used_email',$errors))?$errors['used_email']:$errors['user_email']]);
            }
            else{
                $obj->setCountry($em->getRepository('mycpBundle:country')->find($post['country']));
                $em->persist($obj);
                $em->flush();
                //Create user
                $factory = $this->get('security.encoder_factory');
                $user=$em->getRepository('PartnerBundle:paTravelAgency')->createUser($obj, null, $factory, true, $this, $this->get('service_container'),$request->get('password'));
                //Create tour operator
                return $this->redirect($this->generateUrl('frontend_partner_registeracountpage'));
            }

        }
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function registerAccountPageAction(){
        return $this->render('PartnerBundle:Layout:registerAgency.html.twig', array());
    }
}
