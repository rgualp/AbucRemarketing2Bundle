<?php

namespace MyCp\PartnerBundle\Controller;

use MyCp\FrontEndBundle\Form\registerUserType;
use MyCp\mycpBundle\Entity\user;
use MyCp\mycpBundle\Form\restorePasswordUserType;
use MyCp\mycpBundle\Helpers\UserMails;
use MyCp\PartnerBundle\Entity\paAccount;
use MyCp\PartnerBundle\Entity\paContact;
use MyCp\PartnerBundle\Form\paContactType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use MyCp\PartnerBundle\Entity\paTravelAgency;
use MyCp\PartnerBundle\Entity\paTourOperator;
use MyCp\PartnerBundle\Entity\paAgencyPackage;

use MyCp\PartnerBundle\Form\paTravelAgencyType;
use Symfony\Component\HttpFoundation\JsonResponse;

class FrontendController extends Controller
{

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
        $packages = $em->getRepository('PartnerBundle:paPackage')->findAll();
        $packagesArray = array();

        foreach($packages as $pack)
        {
            $packagesArray[$pack->getName()] = $pack->getId();
        }

        $form = $this->createForm(new paTravelAgencyType($this->get('translator')));
        if($this->getUser() == null) {
            return $this->render('PartnerBundle:Frontend:index.html.twig', array(
                'remoteLogin' => true,
                'form' => $form->createView(),
                'packages' => $packages,
                'packagesArray' => $packagesArray
            ));
        }
        else {
            return $this->redirect($this->generateUrl('backend_partner_dashboard'));
        }
    }

    /**
     * @param $route
     * @param null $routeParams
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function navBarAction($route, $routeParams = null)
    {
        $session = $this->getRequest()->getSession();
        $routeParams = empty($routeParams) ? array() : $routeParams;
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        if($user!= null){
        if($user->getUserCurrency()!=null) {
            $session->set("curr_rate", $user->getUserCurrency()->getCurrCucChange());
            $session->set("curr_symbol", $user->getUserCurrency()->getCurrSymbol());
            $session->set("curr_acronym", $user->getUserCurrency()->getCurrCode());
        }
        }
        $user_ids = $em->getRepository('mycpBundle:user')->getIds($this);
        $countItems = $em->getRepository('mycpBundle:favorite')->getTotal($user_ids['user_id'], $user_ids['session_id']);
        $response = $this->render('PartnerBundle:Layout:language-currency.html.twig', array(
            'route' => $route,
            'routeParams' => $routeParams,
            'count_fav' => $countItems,

        ));
        return $response;
    }

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function contentLoginAction(Request $request)
    {
        return $this->render('LayoutBundle:Security:login-content.html.twig', array());
    }

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function contentRegisterAction(Request $request)
    {
        return $this->render('LayoutBundle:Security:register-content.html.twig', array());
    }

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function contentForgotAction(Request $request)
    {
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
    public function registerAgencyAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $session = $request->getSession();
        $language = $em->getRepository('mycpBundle:lang')->findOneBy(array('lang_code' => strtoupper($this->get('request')->getLocale())));
        $currency = $em->getRepository('mycpBundle:currency')->findOneBy(array('curr_code' => $session->get('curr_acronym')));


        $obj = new paTravelAgency();
        $errors = array();
        $form = $this->createForm(new paTravelAgencyType($this->get('translator')), $obj);

        if(!$request->get('formEmpty')) {
            $form->handleRequest($request);
            $post = $request->get('partner_agency');

            $user_db = $em->getRepository('mycpBundle:user')->findBy(array(
                'user_email' => $post['email'],
                'user_created_by_migration' => false));
            if($user_db) {
                $errors['used_email'] = $this->get('translator')->trans("USER_EMAIL_IN_USE");
            }
            $validate_email = \MyCp\FrontEndBundle\Helpers\Utils::validateEmail($post['email']);
            if(!$validate_email)
                $errors['user_email'] = $this->get('translator')->trans("EMAIL_INVALID_MESSAGE");

            if(count($errors)) {
                $message = (array_key_exists('used_email', $errors)) ? $errors['used_email'] : $errors['user_email'];
                $this->get('session')->getFlashBag()->add('message_error_emailagency', $message);
                return $this->redirect($this->generateUrl('frontend_partner_home'));
            }
            else {
                $obj->setCountry($em->getRepository('mycpBundle:country')->find($post['country']));
                $obj->setCommission(10);
                $em->persist($obj);
                $em->flush();

                //Save relations package agency
                $package = $em->getRepository('PartnerBundle:paPackage')->find($request->get('packageSelect')); //Esperando a que venga de la vista
                if($package != null) {
                    $package_agency = new paAgencyPackage();
                    $package_agency->setPackage($package);
                    $package_agency->setTravelAgency($obj);
                    $em->persist($package_agency);
                    $em->flush();
                }
                //Create user
                $factory = $this->get('security.encoder_factory');

//                $agencys = $em->getRepository('PartnerBundle:paTravelAgency')->getAll()->getResult();
//                if(count($agencys) >= 10){ //para la V beta.
//                    $user = $em->getRepository('PartnerBundle:paTravelAgency')->createUser($obj, null, $factory, true, $this, $this->get('service_container'), $request->get('password'), $language, $currency, true);
//                    $user->setUserEnabled(0);
//                }
//                else{
                    $user = $em->getRepository('PartnerBundle:paTravelAgency')->createUser($obj, null, $factory, true, $this, $this->get('service_container'), $request->get('password'), $language, $currency);
//                }

                //Create tour operator
                $tourOperator = new paTourOperator();
                $tourOperator->setTourOperator($user);
                $tourOperator->setTravelAgency($obj);
                $em->persist($tourOperator);
                $em->flush();
                //Create Account
                if($package->getName()=='Especial'){
                    $account=new paAccount();
                    $account->setBalance(0);
                    $em->persist($account);
                    $em->flush();
                    $obj->setAccount($account);
                    $em->persist($obj);
                    $em->flush();
                }
                return $this->redirect($this->generateUrl('frontend_partner_home'));
                //return $this->redirect($this->generateUrl('frontend_partner_registeracountpage'));
            }

        }
    }
    /**
     * @param Request $request
     */
    public function addContactAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $session = $request->getSession();
        $user = $this->getUser();

        $tourOperator = $em->getRepository("PartnerBundle:paTourOperator")->findOneBy(array("tourOperator" => $user->getUserId()));
        $agency = $tourOperator->getTravelAgency();

        $obj = new paContact();
        $errors = array();
        $form = $this->createForm(new paContactType($this->get('translator')), $obj);

        if(!$request->get('formEmpty')) {
            $form->handleRequest($request);
            $post = $request->get('partner_agency');

            $validate_email = \MyCp\FrontEndBundle\Helpers\Utils::validateEmail($post['contacts']['__name__']['email']);
            if(!$validate_email)
                $errors['user_email'] = $this->get('translator')->trans("EMAIL_INVALID_MESSAGE");

            if(count($errors)) {

                $message = (array_key_exists('used_email', $errors)) ? $errors['used_email'] : $errors['user_email'];

                $this->get('session')->getFlashBag()->add('message_error_emailagency', $message);
                return $this->redirect($this->generateUrl('frontend_partner_home'));
            }
            else {
                $obj->setName($post['contacts']['__name__']['name']);
                 $obj->setEmail($post['contacts']['__name__']['email']);
                $obj->setPhone($post['contacts']['__name__']['phone']);
                $obj->setMobile($post['contacts']['__name__']['mobile']);
                $obj->setTravelAgency($agency);

                $em->persist($obj);
                $em->flush();


                return new JsonResponse(array('success'=>true,'message'=>'Para comensar operaciones añada un primer deposito'));
                //return $this->redirect($this->generateUrl('frontend_partner_registeracountpage'));
            }

        }
    }

    /**
     * @param Request $request
     */
    public function addTourOperatorAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $session = $request->getSession();
        $user = $this->getUser();
        $userrepo= $em->getRepository('mycpBundle:user');
        $tourOperator = $em->getRepository("PartnerBundle:paTourOperator")->findOneBy(array("tourOperator" => $user->getUserId()));
        $agency = $tourOperator->getTravelAgency();

        $obj = new user();
        $errors = array();
        $data = array();
        $data['countries'] = $em->getRepository('mycpBundle:country')->findAllByAlphabetical();
        $form = $this->createForm(new registerUserType($this->get('translator'),$data));

        if(!$request->get('formEmpty')) {
            $form->handleRequest($request);
            $post = $request->get('mycp_frontendbundle_register_usertype');

            $user_db = $em->getRepository('mycpBundle:user')->findBy(array(
                'user_email' => $post['user_email'],
                'user_created_by_migration' => false));
            if($user_db) {
                $errors['used_email'] = $this->get('translator')->trans("USER_EMAIL_IN_USE");
            }
            $validate_email = \MyCp\FrontEndBundle\Helpers\Utils::validateEmail($post['user_email']);
            if(!$validate_email)
                $errors['user_email'] = $this->get('translator')->trans("EMAIL_INVALID_MESSAGE");

            if(count($errors)) {
                $message = (array_key_exists('used_email', $errors)) ? $errors['used_email'] : $errors['user_email'];
                $this->get('session')->getFlashBag()->add('message_error_emailagency', $message);
                return $this->redirect($this->generateUrl('frontend_partner_home'));
            }
            else {

                $factory = $this->get('security.encoder_factory');
                $encoder = $factory->getEncoder($user);

                $password = $encoder->encodePassword($request->get('password'), $user->getSalt());

                $role=$post['user_role'];

                if($role==1){
                    $obj->setUserRole("ROLE_CLIENT_PARTNER") ;
                    $obj->setUserSubrole($em->getRepository('mycpBundle:role')->findby(array('role_name'=>"ROLE_CLIENT_PARTNER"))[0]);
                    $tourOperators=$agency->getTourOperators();
                    foreach ($tourOperators as $touruser){
                        $obj->addChildren($userrepo->find($touruser->getTourOperator()->getUserId()));
                    }
                }
                elseif ($role==0){
                    $obj->setUserRole("ROLE_CLIENT_PARTNER_TOUROPERATOR") ;
                    $obj->setUserSubrole($em->getRepository('mycpBundle:role')->findby(array('role_name'=>"ROLE_CLIENT_PARTNER_TOUROPERATOR"))[0]);

                }
                else{
                    $obj->setUserRole("ROLE_ECONOMY_PARTNER") ;
                    $obj->setUserSubrole($em->getRepository('mycpBundle:role')->findby(array('role_name'=>"ROLE_ECONOMY_PARTNER"))[0]);
                    $tourOperators=$agency->getTourOperators();

                    foreach ($tourOperators as $touruser){

                        $obj->addChildren($userrepo->find($touruser->getTourOperator()->getUserId()));

                    }

                }
                $obj->setUserPassword($password);
                $obj->setUserName($post['user_email']);
                $obj->setUserEnabled(true);
                $obj->setUserCreatedByMigration(false);
                $obj->setUserUserName($post['user_user_name']);
                $obj->setUserLastName($post['user_last_name']);
                $obj->setUserEmail($post['user_email']);
                $obj->setUserCountry($em->getRepository('mycpBundle:country')->find($post['user_country']));
                $obj->setUserCurrency($user->getUserCurrency());

                $em->persist($obj);
                $em->flush();

                $user->addChildren($obj);
                $em->persist($user);
                $em->flush();


                //Create tour operator
                $tourOperator = new paTourOperator();
                $tourOperator->setTourOperator($obj);
                $tourOperator->setTravelAgency($agency);
                $em->persist($tourOperator);
                $em->flush();

                return new JsonResponse(array('success'=>true,'message'=>'Para comensar operaciones añada un primer deposito'));
                //return $this->redirect($this->generateUrl('frontend_partner_registeracountpage'));
            }

        }
    }
    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function registerAccountPageAction()
    {
        return $this->render('PartnerBundle:Layout:registerAgency.html.twig', array());
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function whoAreAction()
    {

        $em = $this->getDoctrine()->getManager();
        $form = $this->createForm(new paTravelAgencyType($this->get('translator')));
        $packages = $em->getRepository('PartnerBundle:paPackage')->findAll();
        return $this->render('PartnerBundle:Pages:who_we_are.html.twig', array(
            'form' => $form->createView(),
            'packages' => $packages));
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function howWorkAction()
    {
        $em = $this->getDoctrine()->getManager();
        $form = $this->createForm(new paTravelAgencyType($this->get('translator')));
        $packages = $em->getRepository('PartnerBundle:paPackage')->findAll();

        return $this->render('PartnerBundle:Pages:how_work.html.twig', array(
            'form' => $form->createView(),
            'packages' => $packages
        ));
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function faqsAction()
    {
        $em = $this->getDoctrine()->getManager();
        $form = $this->createForm(new paTravelAgencyType($this->get('translator')));
        $packages = $em->getRepository('PartnerBundle:paPackage')->findAll();

        return $this->render('PartnerBundle:Pages:faqs.html.twig', array(
                'form' => $form->createView(),
                'packages' => $packages)
        );
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function legalAction()
    {
        $em = $this->getDoctrine()->getManager();
        $form = $this->createForm(new paTravelAgencyType($this->get('translator')));
        $packages = $em->getRepository('PartnerBundle:paPackage')->findAll();


        return $this->render('PartnerBundle:Pages:legal.html.twig', array(
                'form' => $form->createView(),
                'packages' => $packages)
        );
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function sitemapAction()
    {
        $em = $this->getDoctrine()->getManager();
        $form = $this->createForm(new paTravelAgencyType($this->get('translator')));
        $packages = $em->getRepository('PartnerBundle:paPackage')->findAll();

        return $this->render('PartnerBundle:Pages:sitemap.html.twig', array(
                'form' => $form->createView(),
                'packages' => $packages)
        );
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function contactusAction()
    {
        $em = $this->getDoctrine()->getManager();
        $form = $this->createForm(new paTravelAgencyType($this->get('translator')));
        $packages = $em->getRepository('PartnerBundle:paPackage')->findAll();

        return $this->render('PartnerBundle:Pages:contactus.html.twig', array(
                'form' => $form->createView(),
                'packages' => $packages)
        );
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function testEmailAction()
    {
        $em = $this->getDoctrine()->getManager();
        $reservation = $em->getRepository("mycpBundle:generalReservation")->find(101687);
        $user = $reservation->getGenResUserId();
        $roomReservations = $reservation->getOwn_reservations();
        $timer = $this->get("Time");

        $nights = 0;
        $adults = 0;
        $children = 0;

        foreach($roomReservations as $roomReservation)
        {
            $nights += $timer->nights($roomReservation->getOwnResReservationFromDate()->getTimestamp(), $roomReservation->getOwnResReservationToDate()->getTimestamp());
            $adults += $roomReservation->getOwnResCountAdults();
            $children += $roomReservation->getOwnResCountChildrens();
        }

        $response = $this->render('PartnerBundle:Mail:test.html.twig', array(
            "reservation" => $reservation,
            "user_locale" => "en",
            "agencyName" => "Agencia MALA",
            "nights" => ($nights / count($roomReservations)),
            "adults" => $adults,
            "children" => $children,
            "currency" => $user->getUserCurrency()
        ));
        return $response;
    }
}
