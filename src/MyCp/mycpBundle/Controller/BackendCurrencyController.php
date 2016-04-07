<?php

namespace MyCp\mycpBundle\Controller;

use MyCp\mycpBundle\Helpers\DataBaseTables;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use MyCp\mycpBundle\Entity\currency;
use MyCp\mycpBundle\Entity\log;
use MyCp\mycpBundle\Form\currencyType;
use MyCp\mycpBundle\Helpers\BackendModuleName;

class BackendCurrencyController extends Controller {

    public function list_currenciesAction($items_per_page) {
        $service_security = $this->get('Secure');
        $service_security->verifyAccess();
        $page = 1;
        if (isset($_GET['page']))
            $page = $_GET['page'];
        $em = $this->getDoctrine()->getManager();
        $paginator = $this->get('ideup.simple_paginator');
        $paginator->setItemsPerPage($items_per_page);
        $currencies = $paginator->paginate($em->getRepository('mycpBundle:currency')->findAll())->getResult();

//        $service_log = $this->get('log');
//        $service_log->saveLog('Visit', BackendModuleName::MODULE_CURRENCY);

        return $this->render('mycpBundle:currency:list.html.twig', array(
                    'currencies' => $currencies,
                    'items_per_page' => $items_per_page,
                    'current_page' => $page,
                    'total_items' => $paginator->getTotalItems()
        ));
    }

    public function edit_currencyAction($id_currency, Request $request) {
        $service_security = $this->get('Secure');
        $service_security->verifyAccess();
        $message = '';
        $errors = array();
        $em = $this->getDoctrine()->getManager();
        $currency = $em->getRepository('mycpBundle:currency')->find($id_currency);
        $form = $this->createForm(new currencyType, $currency);
        if ($request->getMethod() == 'POST') {
            $post_form = $request->get('mycp_mycpbundle_currencytype');
            $form->handleRequest($request);
            if ($form->isValid()) {
                if ($currency->getCurrDefault() || $currency->getCurrSitePriceIn()) {
                    $db_curr = $em->getRepository('mycpBundle:currency')->findAll();
                    foreach ($db_curr as $curr) {
                        if ($curr->getCurrId() != $currency->getCurrId()) {
                            if ($currency->getCurrDefault())
                                $curr->setCurrDefault(false);

                            if ($currency->getCurrSitePriceIn())
                                $curr->setCurrSitePriceIn(false);
                        }

                        $em->persist($curr);
                    }
                }

                $em->persist($currency);
                $em->flush();

                if (!$currency->getCurrSitePriceIn()) {
                    $price_in_count = count($em->getRepository("mycpBundle:currency")->findBy(array('curr_site_price_in' => true)));

                    if ($price_in_count == 0) {
                        $message = 'Primero tiene que marcar la moneda en la que están almacenados los precios en la base de datos y la asociación quedará eliminada automáticamente';
                        $errors['curr_site_price_in'] = $message;
                        $this->get('session')->getFlashBag()->add('message_error_local', $message);
                    }
                }
                //else {

                    $message = 'Moneda actualizada satisfactoriamente.';
                    $this->get('session')->getFlashBag()->add('message_ok', $message);

                    $service_log = $this->get('log');
                    $service_log->saveLog($currency->getLogDescription(), BackendModuleName::MODULE_CURRENCY, log::OPERATION_UPDATE, DataBaseTables::CURRENCY);
                    return $this->redirect($this->generateUrl('mycp_list_currencies'));
                //}
            }
        }

        return $this->render('mycpBundle:currency:new.html.twig', array('form' => $form->createView(), 'edit_currency' => $currency->getCurrId(), 'errors' => $errors));
    }

    public function new_currencyAction(Request $request) {
        $service_security = $this->get('Secure');
        $service_security->verifyAccess();
        $currency = new currency();
        $form = $this->createForm(new currencyType, $currency);
        if ($request->getMethod() == 'POST') {
            $post_form = $request->get('mycp_mycpbundle_currencytype');
            $form->handleRequest($request);
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                if ($currency->getCurrDefault() || $currency->getCurrSitePriceIn()) {
                    $db_curr = $em->getRepository('mycpBundle:currency')->findAll();
                    foreach ($db_curr as $curr) {
                        if ($currency->getCurrDefault())
                            $curr->setCurrDefault(false);

                        if ($currency->getCurrSitePriceIn())
                            $curr->getCurrSitePriceIn(false);
                    }
                    $em->persist($curr);
                }

                $em->persist($currency);
                $em->flush();
                $message = 'Moneda añadida satisfactoriamente.';
                $this->get('session')->getFlashBag()->add('message_ok', $message);

                $service_log = $this->get('log');
                $service_log->saveLog($currency->getLogDescription(), BackendModuleName::MODULE_CURRENCY, log::OPERATION_INSERT, DataBaseTables::CURRENCY);

                return $this->redirect($this->generateUrl('mycp_list_currencies'));
            }
        }
        return $this->render('mycpBundle:currency:new.html.twig', array('form' => $form->createView()));
    }

    public function delete_currencyAction($id_currency) {
        $service_security = $this->get('Secure');
        $service_security->verifyAccess();
        $em = $this->getDoctrine()->getManager();
        $currency = $em->getRepository('mycpBundle:currency')->find($id_currency);
        $logDescription = $currency->getLogDescription();
        $user = $em->getRepository('mycpBundle:userTourist')->findBy(array('user_tourist_currency' => $currency));
        if ($user) {
            $message = 'No se puede eliminar la moneda, está siendo utilizada por un usuario.';
            $this->get('session')->getFlashBag()->add('message_error_local', $message);
            return $this->redirect($this->generateUrl('mycp_list_currencies'));
        } else {
            $name_curr = $currency->getCurrName();
            $curr_deleted = null;
            if ($currency->getCurrDefault() == true) {
                $curr_deleted = $currency;
            }

            $em->remove($currency);
            $em->flush();

            if ($curr_deleted != null) {
                $db_curr = $em->getRepository('mycpBundle:currency')->findAll();
                $db_curr[0]->setCurrDefault(1);

                $em->persist($db_curr[0]);
                $em->flush();
            }

            $message = 'La moneda se ha eliminado satisfactoriamente.';
            $this->get('session')->getFlashBag()->add('message_ok', $message);

            $service_log = $this->get('log');
            $service_log->saveLog($logDescription, BackendModuleName::MODULE_CURRENCY, log::OPERATION_DELETE, DataBaseTables::CURRENCY);

            return $this->redirect($this->generateUrl('mycp_list_currencies'));
        }
    }

    public function get_currency_changeAction() {
        $em = $this->getDoctrine()->getManager();
        $currencies = $em->getRepository('mycpBundle:currency')->findAll();
        //$url='http://127.0.0.1/cambio.xml';
        $url = 'http://themoneyconverter.com/rss-feed/USD/rss.xml';
        $rss = simplexml_load_file($url);
        echo '<h6>Cambio a día: ' . $rss->channel->lastBuildDate . '</h6>';
        foreach ($currencies as $currency) {
            foreach ($rss->channel->item as $item) {
                if (strpos(strtolower($item->title), strtolower($currency->getCurrCode() . '/')) === 0) {
                    //echo '<h2>'.$item->title ."</h2>";
                    $array = explode('= ', $item->description);
                    echo "<p>1 cuc = " . $array[1] . "</p>";
                }
            }
        }
        return new Response();
    }

}
