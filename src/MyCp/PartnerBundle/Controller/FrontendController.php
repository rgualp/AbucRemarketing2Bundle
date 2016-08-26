<?php

namespace MyCp\PartnerBundle\Controller;

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
     */
    public function registerAgencyAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $obj = new paTravelAgency();
        $data = array();
        $data['countries'] = $em->getRepository('mycpBundle:country')->findAllByAlphabetical();
        $form = $this->createForm(new paTravelAgencyType($this->get('translator'), $data),$obj);

        if(!$request->get('formEmpty')){
            $form->handleRequest($request);
            //if($form->isValid()){
                $post = $request->get('partner_agency');
                $obj->setCountry($em->getRepository('mycpBundle:country')->find($post['country']));
                $em->persist($obj);
                $em->flush();
                //Create user
//                $factory = $this->get('security.encoder_factory');
//                $user=$em->getRepository('PartnerBundle:paTravelAgency')->createUser($obj, null, $factory, true, $this, $this->get('service_container'));
                return new JsonResponse(['success' => true, 'msg' => 'Se ha adicionado satisfactoriamente']);
           // }
        }
    }
}
