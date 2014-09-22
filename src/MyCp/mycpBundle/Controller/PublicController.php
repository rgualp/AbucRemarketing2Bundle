<?php

namespace MyCp\mycpBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\SecurityContext;

use MyCp\mycpBundle\Entity\user;


class PublicController extends Controller
{

    public function loginAction($urlLogin)
    {

        $request = $this->getRequest();
        $session = $request->getSession();

        if ($request->attributes->has(SecurityContext::AUTHENTICATION_ERROR)) {
            $error = $request->attributes->get(SecurityContext::AUTHENTICATION_ERROR);
        } else {
            $error = $session->get(SecurityContext::AUTHENTICATION_ERROR);
            $session->remove(SecurityContext::AUTHENTICATION_ERROR);
        }
        return $this->render('mycpBundle:public:login.html.twig', array(
            'last_username' => $session->get(SecurityContext::LAST_USERNAME),
            'error' => $error,
            'urlLogin' => $urlLogin,
        ));
    }

    public function access_deniedAction()
    {
        return $this->render('mycpBundle:utils:access_denied.html.twig');
    }

    public function get_mun_by_provAction($post,Request $request)
    {
        //$post = $request->request->getIterator()->getArrayCopy();
        $em = $this->getDoctrine()->getManager();
        $selected = '';
        if($post && isset($post['ownership_address_municipality']))
        {
            $selected = $post['ownership_address_municipality'];
        }
        $municipalities = array();
        
        if($post && isset($post['ownership_address_province']))
            $municipalities = $em->getRepository('mycpBundle:municipality')->findBy(array('mun_prov_id'=>$post['ownership_address_province']));
        /*else
            $municipalities = $em->getRepository ('mycpBundle:municipality')->findAll();*/
        return $this->render('mycpBundle:utils:list_municipality.html.twig', array('municipalities' => $municipalities,'selected'=>$selected));
    }
    
    public function get_mun_by_prov_callbackAction($country_code,Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $municipalities = $em->getRepository('mycpBundle:municipality')->findBy(array('mun_prov_id'=>$country_code));
        return $this->render('mycpBundle:utils:list_municipality.html.twig', array('municipalities' => $municipalities));
    }

    public function get_munAction($post,Request $request)
    {
        if(isset($post['filter_municipality']))
        {
            $post['ownership_address_municipality']=$post['filter_municipality'];
        }
        $em = $this->getDoctrine()->getManager();
        
        if(isset($post['ownership_address_province']))
            $municipalities = $em->getRepository('mycpBundle:municipality')->findBy(array('mun_prov_id' => $post['ownership_address_province']));
        else
            $municipalities = $em->getRepository('mycpBundle:municipality')->findAll();
        return $this->render('mycpBundle:utils:list_municipality.html.twig', array('municipalities' => $municipalities,'data'=>$post));
    }

    public function get_cities_by_countryAction($country_code,Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $country = $em->getRepository('mycpBundle:country')->find($country_code);
        $cities = $em->getRepository('mycpBundle:city')->findBy(array('cit_co_code'=>$country->getCoCode()));
        return $this->render('mycpBundle:utils:list_municipality.html.twig', array('municipalities' => $cities));
    }

    public function get_provincesAction($post)
    {
        $em = $this->getDoctrine()->getManager();
        $provinces=$em->getRepository('mycpBundle:province')->findAll();
        $selected='';
        if($post && isset($post['ownership_address_province']))
            $selected=$post['ownership_address_province'];
        return $this->render('mycpBundle:utils:province.html.twig',array('selected'=>$selected,'provinces'=>$provinces));
    }

    public function get_countriesAction($selected)
    {

        $em = $this->getDoctrine()->getManager();
        $countries=$em->getRepository('mycpBundle:country')->findAll();
        return $this->render('mycpBundle:utils:country.html.twig',array('selected'=>$selected,'countries'=>$countries));
    }





}
