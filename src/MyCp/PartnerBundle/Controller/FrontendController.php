<?php

namespace MyCp\PartnerBundle\Controller;

use MyCp\mycpBundle\Entity\user;
use MyCp\mycpBundle\Form\restorePasswordUserType;
use MyCp\mycpBundle\Helpers\UserMails;
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
        $form = $this->createForm(new paTravelAgencyType($this->get('translator')));
        if($this->getUser() == null) {
            return $this->render('PartnerBundle:Frontend:index.html.twig', array(
                'remoteLogin' => true,
                'form' => $form->createView()
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
        $routeParams = empty($routeParams) ? array() : $routeParams;
        $em = $this->getDoctrine()->getManager();
        $user_ids = $em->getRepository('mycpBundle:user')->getIds($this);
        $countItems = $em->getRepository('mycpBundle:favorite')->getTotal($user_ids['user_id'], $user_ids['session_id']);
        $response = $this->render('PartnerBundle:Layout:language-currency.html.twig', array(
            'route' => $route,
            'routeParams' => $routeParams,
            'count_fav' => $countItems
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
                $em->persist($obj);
                $em->flush();

                //Save relations package agency
                $package = $em->getRepository('PartnerBundle:paPackage')->findAll(); //Esperando a que venga de la vista
                if(count($package)) {
                    $package_agency = new paAgencyPackage();
                    $package_agency->setPackage($package[0]);
                    $package_agency->setTravelAgency($obj);
                    $em->persist($package_agency);
                    $em->flush();
                }
                //Create user
                $factory = $this->get('security.encoder_factory');

                $agencys = $em->getRepository('PartnerBundle:paTravelAgency')->getAll()->getResult();
                if(count($agencys) >= 10){ //para la V beta.
                    $user = $em->getRepository('PartnerBundle:paTravelAgency')->createUser($obj, null, $factory, true, $this, $this->get('service_container'), $request->get('password'), $language, $currency, true);
                    $user->setUserEnabled(0);
                }
                else{
                    $user = $em->getRepository('PartnerBundle:paTravelAgency')->createUser($obj, null, $factory, true, $this, $this->get('service_container'), $request->get('password'), $language, $currency);
                }

                //Create tour operator
                $tourOperator = new paTourOperator();
                $tourOperator->setTourOperator($user);
                $tourOperator->setTravelAgency($obj);
                $em->persist($tourOperator);
                $em->flush();

                return $this->redirect($this->generateUrl('frontend_partner_home'));
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
        return $this->render('PartnerBundle:Pages:who_we_are.html.twig', array());
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function howWorkAction()
    {
        return $this->render('PartnerBundle:Pages:how_work.html.twig', array());
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function faqsAction()
    {
        return $this->render('PartnerBundle:Pages:faqs.html.twig', array());
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function legalAction()
    {
        return $this->render('PartnerBundle:Pages:legal.html.twig', array());
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function sitemapAction()
    {
        return $this->render('PartnerBundle:Pages:sitemap.html.twig', array());
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function contactusAction()
    {
        return $this->render('PartnerBundle:Pages:contactus.html.twig', array());
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function testEmailAction()
    {
        $response = $this->render('PartnerBundle:Mail:test.html.twig', array());
        return $response;
    }
}
