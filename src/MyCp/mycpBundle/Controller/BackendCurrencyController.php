<?php

namespace MyCp\mycpBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

use MyCp\mycpBundle\Entity\currency;
use MyCp\mycpBundle\Entity\log;
use MyCp\mycpBundle\Form\currencyType;


class BackendCurrencyController extends Controller
{
    public function list_currenciesAction($items_per_page)
    {
        $service_security= $this->get('Secure');
        $service_security->verify_access();
        $page=1;
        if(isset($_GET['page']))$page=$_GET['page'];
        $em = $this->getDoctrine()->getEntityManager();
        $paginator = $this->get('ideup.simple_paginator');
        $paginator->setItemsPerPage($items_per_page);
        $currencies=$paginator->paginate($em->getRepository('mycpBundle:currency')->findAll())->getResult();

        $service_log= $this->get('log');
        $service_log->save_log('Visit',5);

        return $this->render('mycpBundle:currency:list.html.twig',array(
            'currencies'=>$currencies,
            'items_per_page'=>$items_per_page,
            'current_page'=>$page,
            'total_items'=>$paginator->getTotalItems()
        ));
    }

    public function edit_currencyAction($id_currency,Request $request)
    {
        $service_security= $this->get('Secure');
        $service_security->verify_access();
        $message='';
        $em = $this->getDoctrine()->getEntityManager();
        $currency=$em->getRepository('mycpBundle:currency')->find($id_currency);
        $form=$this->createForm( new currencyType,$currency);
        if($request->getMethod()=='POST')
        {
            $post_form=$request->get('mycp_mycpbundle_currencytype');
            $form->handleRequest($request);
            if($form->isValid())
            {
                $em->persist($currency);
                $em->flush();
                $message='Moneda actualizada satisfactoriamente.';
                $this->get('session')->getFlashBag()->add('message_ok',$message);

                $service_log= $this->get('log');
                $service_log->save_log('Edit entity '.$post_form['curr_name'],5);

                return $this->redirect($this->generateUrl('mycp_list_currencies'));
            }

        }

        return $this->render('mycpBundle:currency:new.html.twig',array('form'=>$form->createView(),'edit_currency'=>$currency->getCurrId()));

    }

    public function new_currencyAction(Request $request)
    {
        $service_security= $this->get('Secure');
        $service_security->verify_access();
        $currency=new currency();
        $form=$this->createForm( new currencyType,$currency);
        if($request->getMethod()=='POST')
        {
            $post_form=$request->get('mycp_mycpbundle_currencytype');
            $form->handleRequest($request);
            if($form->isValid())
            {
                $em = $this->getDoctrine()->getEntityManager();
                $em->persist($currency);
                $em->flush();
                $message='Moneda añadida satisfactoriamente.';
                $this->get('session')->getFlashBag()->add('message_ok',$message);

                $service_log= $this->get('log');
                $service_log->save_log('Create entity '.$post_form['curr_name'],5);

                return $this->redirect($this->generateUrl('mycp_list_currencies'));
            }

        }
        return $this->render('mycpBundle:currency:new.html.twig',array('form'=>$form->createView()));
    }

    public function delete_currencyAction($id_currency)
    {
        $service_security= $this->get('Secure');
        $service_security->verify_access();
        $em = $this->getDoctrine()->getEntityManager();
        $currency=$em->getRepository('mycpBundle:currency')->find($id_currency);
        $user=$em->getRepository('mycpBundle:userTourist')->findBy(array('user_tourist_currency'=>$currency));
        if($user)
        {
            $message='No se puede eliminar la moneda, está siendo utilizada por un usuario.';
            $this->get('session')->getFlashBag()->add('message_error_local',$message);
            return $this->redirect($this->generateUrl('mycp_list_currencies'));
        }
        else
        {
            $name_curr=$currency->getCurrName();
            $em->remove($currency);
            $em->flush();
            $message='La moneda se ha eliminado satisfactoriamente.';
            $this->get('session')->getFlashBag()->add('message_ok',$message);

            $service_log= $this->get('log');
            $service_log->save_log('Delete entity '.$name_curr,5);

            return $this->redirect($this->generateUrl('mycp_list_currencies'));
        }
    }

    public function get_currency_changeAction()
    {
        $em = $this->getDoctrine()->getEntityManager();
        $currencies=$em->getRepository('mycpBundle:currency')->findAll();
        //$url='http://127.0.0.1/cambio.xml';
        $url='http://themoneyconverter.com/rss-feed/USD/rss.xml';
        $rss = simplexml_load_file($url);
        echo '<h6>Cambio a día: '.$rss->channel->lastBuildDate.'</h6>';
        foreach($currencies as $currency)
        {
            foreach ($rss->channel->item as $item)
            {
                if(strpos(strtolower($item->title), strtolower($currency->getCurrCode().'/'))===0)
                {
                    //echo '<h2>'.$item->title ."</h2>";
                    $array=explode('= ',$item->description);
                    echo "<p>1 cuc = " . $array[1] . "</p>";
                }
            }
        }
        return new Response();
    }

}
