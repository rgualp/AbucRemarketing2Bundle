<?php

namespace MyCp\mycpBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use MyCp\mycpBundle\Entity\user;
use MyCp\mycpBundle\Entity\userCasa;
use MyCp\mycpBundle\Entity\role;
use MyCp\mycpBundle\Entity\rolePermission;
use MyCp\mycpBundle\Form\clientStaffType;
use MyCp\mycpBundle\Form\clientCasaType;
use MyCp\mycpBundle\Form\clientTouristType;
use MyCp\mycpBundle\Form\clientPartnerType;
use MyCp\mycpBundle\Entity\userPartner;
use MyCp\mycpBundle\Helpers\BackendModuleName;
use \MyCp\FrontEndBundle\Helpers\Utils;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class BackendUserController extends Controller {

    function new_user_staffAction($id_role, Request $request) {
        $service_security = $this->get('Secure');
        $service_security->verifyAccess();
        $em = $this->getDoctrine()->getEntityManager();
        $countries = $em->getRepository('mycpBundle:country')->findAll();
        $data['countries'] = $countries;
        $data['error'] = "";
        $count_errors = 0;

        $form = $this->createForm(new clientStaffType($data));
        if ($request->getMethod() == 'POST') {
            $post = $request->request->getIterator()->getArrayCopy();
            $user_db = $em->getRepository('mycpBundle:user')->findBy(array('user_name' => $post['mycp_mycpbundle_client_stafftype']['user_name']));
            if ($user_db) {
                $data['error'] = 'Ya existe un usuario con ese nombre.';
                $count_errors++;
            }

            if ($post['mycp_mycpbundle_client_stafftype']['user_email'] != "" && !Utils::validateEmail($post['mycp_mycpbundle_client_stafftype']['user_email'])) {
                $data['error'] = 'Correo no válido';
                $count_errors++;
            }

            $form->handleRequest($request);
            if ($form->isValid() && $count_errors == 0) {
                $dir = $this->container->getParameter('user.dir.photos');
                $factory = $this->get('security.encoder_factory');
                $em->getRepository('mycpBundle:user')->insertUserStaff($id_role, $request, $dir, $factory);
                $message = 'Usuario añadido satisfactoriamente.';
                $this->get('session')->getFlashBag()->add('message_ok', $message);

                $service_log = $this->get('log');
                $service_log->saveLog('Create entity for user ' . $post['mycp_mycpbundle_client_stafftype']['user_name'], BackendModuleName::MODULE_USER);

                return $this->redirect($this->generateUrl('mycp_list_users'));
            } else {
                if ($data['error'] == "")
                    $data['error'] = 'Debe llenar el formulario correctamente.';
            }
        }
        return $this->render('mycpBundle:user:newUserStaff.html.twig', array('form' => $form->createView(), 'data' => $data, 'id_role' => $id_role, 'message_error' => $data["error"]));
    }

    function edit_user_staffAction($id_user, Request $request) {
        $service_security = $this->get('Secure');
        $service_security->verifyAccess();
        $em = $this->getDoctrine()->getEntityManager();
        $countries = $em->getRepository('mycpBundle:country')->findAll();
        $data['countries'] = $countries;
        $data['error'] = "";
        $count_errors = 0;

        $data['edit'] = true;

        $request_form = $request->get('mycp_mycpbundle_client_stafftype');
        $data['password'] = $request_form['user_password']['Clave:'];
        $form = $this->createForm(new clientStaffType($data));

        if ($request->getMethod() == 'POST') {
            $post = $request->request->get('mycp_mycpbundle_client_stafftype');
            if ($post['user_email'] != "" && !Utils::validateEmail($post['user_email'])) {
                $data['error'] = 'Correo no válido';
                $count_errors++;
            }

            $form->handleRequest($request);
            if ($form->isValid() && $count_errors == 0) {
                $factory = $this->get('security.encoder_factory');
                $dir = $this->container->getParameter('user.dir.photos');
                $em->getRepository('mycpBundle:user')->editUserStaff($id_user, $request, $dir, $factory);
                $message = 'Usuario actualizado satisfactoriamente.';
                $this->get('session')->getFlashBag()->add('message_ok', $message);
                $service_log = $this->get('log');
                $service_log->saveLog('Edit entity for user ' . $request_form['user_name'], BackendModuleName::MODULE_USER);
                return $this->redirect($this->generateUrl('mycp_list_users'));
            }
        } else {

            $user = $em->getRepository('mycpBundle:user')->find($id_user);
            $data_user['user_name'] = $user->getUserName();
            $data_user['user_address'] = $user->getUserAddress();
            $data_user['user_email'] = $user->getUserEmail();
            $data_user['user_user_name'] = $user->getUserUserName();
            $data_user['user_last_name'] = $user->getUserLastName();
            $data_user['user_phone'] = $user->getUserPhone();
            $data_user['user_city'] = $user->getUserCity();
            $data_user['user_country'] = $user->getUserCountry()->getCoId();

            $form->setData($data_user);
        }
        return $this->render('mycpBundle:user:newUserStaff.html.twig', array('form' => $form->createView(), 'data' => $data, 'id_role' => '', 'edit_user' => $id_user, 'message_error' => $data["error"]));
    }

    function new_user_casaAction($id_role, Request $request) {
        $service_security = $this->get('Secure');
        $service_security->verifyAccess();
        $em = $this->getDoctrine()->getEntityManager();
        $ownerships = $em->getRepository('mycpBundle:userCasa')->getAccommodationsWithoutUser();
        $data['ownerships'] = $ownerships;

        $form = $this->createForm(new clientCasaType($data));
        if ($request->getMethod() == 'POST') {
            $post = $request->request->getIterator()->getArrayCopy();
            $user_db = $em->getRepository('mycpBundle:user')->findBy(array('user_name' => $post['mycp_mycpbundle_client_casatype']['user_name']));
            if ($user_db) {
                $data['error'] = 'Ya existe un usuario con ese nombre.';
            }
            $form->handleRequest($request);
            if ($form->isValid()) {
                if (!isset($data['error'])) {
                    $dir = $this->container->getParameter('user.dir.photos');
                    $factory = $this->get('security.encoder_factory');

                    $ownership = $em->getRepository('mycpBundle:ownership')->find($post['mycp_mycpbundle_client_casatype']['ownership']);
                    $file = $request->files->get('mycp_mycpbundle_client_casatype');
                    $file = $file['photo'];

                    $send_notification_email = (isset($post['user_send_mail']) && !empty($post['user_send_mail']));

                    $em->getRepository('mycpBundle:userCasa')->createUser($ownership, $file, $dir, $factory, $send_notification_email, $this);
                    $message = 'Usuario añadido satisfactoriamente.';
                    $this->get('session')->getFlashBag()->add('message_ok', $message);

                    $service_log = $this->get('log');
                    $service_log->saveLog('Create entity for user ' . $post['mycp_mycpbundle_client_casatype']['user_name'], BackendModuleName::MODULE_USER);

                    return $this->redirect($this->generateUrl('mycp_list_users'));
                }
            } else {
                $data['error'] = 'Debe llenar el formulario correctamente.';
            }
        }
        return $this->render('mycpBundle:user:newUserCasa.html.twig', array('form' => $form->createView(), 'data' => $data, 'id_role' => $id_role));
    }

    function edit_user_casaAction($id_user, Request $request) {
        $service_security = $this->get('Secure');
        $service_security->verifyAccess();
        $em = $this->getDoctrine()->getEntityManager();
        $ownerships = $em->getRepository('mycpBundle:ownership')->findAll();
        $data['ownerships'] = $ownerships;
        $data['edit'] = true;
        $data_user = array();
        $data_user['user_name'] = "";
        $data_user['address'] = "";
        $data_user['email'] = "";
        $data_user['name'] = "";
        $data_user['last_name'] = "";
        $data_user['ownership'] = "";
        $data_user['phone'] = "";
        $data_user['user_id'] = "0";
        $data_user['user_enabled'] = false;

        $count_errors = 0;

        $request_form = $request->get('mycp_mycpbundle_client_casatype');
        $form = $this->createForm(new clientCasaType($data));
        if ($request->getMethod() == 'POST') {

            $form->handleRequest($request);

            if (!Utils::validateEmail($request_form['email'])) {
                $message = 'La dirección de correo no es válida';
                $this->get('session')->getFlashBag()->add('message_error_main', $message);
                $count_errors++;
            }

            if ($form->isValid() && $count_errors == 0) {
                $factory = $this->get('security.encoder_factory');
                $dir = $this->container->getParameter('user.dir.photos');
                $em->getRepository('mycpBundle:userCasa')->edit($id_user, $request, $dir, $factory);
                $message = 'Usuario actualizado satisfactoriamente.';
                $this->get('session')->getFlashBag()->add('message_ok', $message);
                $service_log = $this->get('log');
                $service_log->saveLog('Edit entity for user ' . $request_form['user_name'], BackendModuleName::MODULE_USER);
                $user = $this->get('security.context')->getToken()->getUser();
                if ($user->getUserRole() == 'ROLE_CLIENT_STAFF')
                    return $this->redirect($this->generateUrl('mycp_list_users'));
                else
                    return $this->redirect($this->generateUrl('mycp_backend_front'));
            }
        }
        else {
            $user_casa = new userCasa();
            $user_casa = $em->getRepository('mycpBundle:userCasa')->findBy(array('user_casa_user' => $id_user));
            //var_dump($id_user); exit();
            $user_casa = $user_casa[0];
            $data_user['user_name'] = $user_casa->getUserCasaUser()->getUserName();
            $data_user['address'] = $user_casa->getUserCasaUser()->getUserAddress();
            $data_user['email'] = $user_casa->getUserCasaUser()->getUserEmail();
            $data_user['name'] = $user_casa->getUserCasaUser()->getUserUserName();
            $data_user['phone'] = $user_casa->getUserCasaUser()->getUserPhone();
            $data_user['last_name'] = $user_casa->getUserCasaUser()->getUserLastName();
            $data_user['ownership'] = $user_casa->getUserCasaOwnership()->getOwnId();
            $data_user['phone'] = "";
            $data_user['user_id'] = $user_casa->getUserCasaUser()->getUserId();
            $data_user['user_enabled'] = $user_casa->getUserCasaUser()->getUserEnabled();

            $form->setData($data_user);
        }
        return $this->render('mycpBundle:user:newUserCasa.html.twig', array('form' => $form->createView(),
                    'data' => $data, 'id_role' => '', 'edit_user' => $id_user,
                    'user' => $data_user));
    }

    function new_user_touristAction($id_role, Request $request) {
        $service_security = $this->get('Secure');
        $service_security->verifyAccess();
        $em = $this->getDoctrine()->getEntityManager();
        $countries = $em->getRepository('mycpBundle:country')->findAll();
        $currencies = $em->getRepository('mycpBundle:currency')->findAll();
        $langs = $em->getRepository('mycpBundle:lang')->findAll();
        $data['countries'] = $countries;
        $data['currencies'] = $currencies;
        $data['languages'] = $langs;
        $count_errors = 0;

        $form = $this->createForm(new clientTouristType($data));
        if ($request->getMethod() == 'POST') {
            $post = $request->request->get('mycp_mycpbundle_client_touristtype');
            $user_db = $em->getRepository('mycpBundle:user')->findBy(array('user_name' => $post['user_name']));
            if ($user_db) {
                $data['error'] = 'Ya existe un usuario con ese nombre.';
                $count_errors++;
            }
            if ($post['email'] != "" && !Utils::validateEmail($post['email'])) {
                $data['error'] = 'Correo no válido';
                $count_errors++;
            }
            $form->handleRequest($request);
            if ($form->isValid() && $count_errors == 0) {
                $dir = $this->container->getParameter('user.dir.photos');
                $factory = $this->get('security.encoder_factory');
                $em->getRepository('mycpBundle:userTourist')->insert($id_role, $request, $dir, $factory);
                $message = 'Usuario añadido satisfactoriamente.';
                $this->get('session')->getFlashBag()->add('message_ok', $message);

                $service_log = $this->get('log');
                $service_log->saveLog('Create entity for user ' . $post['user_name'], BackendModuleName::MODULE_USER);

                return $this->redirect($this->generateUrl('mycp_list_users'));
            } else {
                if (!isset($data['error']))
                    $data['error'] = 'Debe llenar el formulario correctamente.';
            }
        }
        return $this->render('mycpBundle:user:newUserTourist.html.twig', array('form' => $form->createView(), 'data' => $data, 'id_role' => $id_role));
    }

    function edit_user_touristAction($id_user, Request $request) {
        $service_security = $this->get('Secure');
        $service_security->verifyAccess();
        $em = $this->getDoctrine()->getEntityManager();
        $countries = $em->getRepository('mycpBundle:country')->findAll();
        $currencies = $em->getRepository('mycpBundle:currency')->findAll();
        $langs = $em->getRepository('mycpBundle:lang')->findAll();
        $data['countries'] = $countries;
        $data['currencies'] = $currencies;
        $data['languages'] = $langs;
        $data['edit'] = true;
        $count_errors = 0;

        $request_form = $request->get('mycp_mycpbundle_client_touristtype');
        $data['password'] = $request_form['user_password']['Clave:'];
        $form = $this->createForm(new clientTouristType($data));

        if ($request->getMethod() == 'POST') {
            $post = $request->request->get('mycp_mycpbundle_client_touristtype');
            if ($post['email'] != "" && !Utils::validateEmail($post['email'])) {
                $data['error'] = 'Correo no válido';
                $count_errors++;
            }
            $form->handleRequest($request);
            if ($form->isValid() && $count_errors == 0) {
                $factory = $this->get('security.encoder_factory');
                $dir = $this->container->getParameter('user.dir.photos');
                $em->getRepository('mycpBundle:userTourist')->edit($id_user, $request, $dir, $factory);
                $message = 'Usuario actualizado satisfactoriamente.';
                $this->get('session')->getFlashBag()->add('message_ok', $message);
                $service_log = $this->get('log');
                $service_log->saveLog('Edit entity for user ' . $request_form['user_name'], BackendModuleName::MODULE_USER);
                return $this->redirect($this->generateUrl('mycp_list_users'));
            }
        } else {
            $user_tourist = null;
            $user_tourist = $em->getRepository('mycpBundle:userTourist')->findOneBy(array('user_tourist_user' => $id_user));

            $user = null;
            if ($user_tourist == null) {
                $user = $em->getRepository('mycpBundle:user')->findOneBy(array('user_id' => $id_user));
                $defaultLangCode = $this->container->getParameter('configuration.default.language.code');
                $defaultCurrencyCode = $this->container->getParameter('configuration.default.currency.code');

                $user_tourist = $em->getRepository('mycpBundle:userTourist')->createDefaultTourist($defaultLangCode, $defaultCurrencyCode, $user);
            }
            /* var_dump($user_tourist); exit;
              $user_tourist=$user_tourist[0]; */
            $data_user['user_name'] = ($user_tourist != null) ? $user_tourist->getUserTouristUser()->getUserName() : $user->getUserName();
            $data_user['address'] = ($user_tourist != null) ? $user_tourist->getUserTouristUser()->getUserAddress() : $user->getUserAddress();
            $data_user['email'] = ($user_tourist != null) ? $user_tourist->getUserTouristUser()->getUserEmail() : $user->getUserEmail();
            $data_user['name'] = ($user_tourist != null) ? $user_tourist->getUserTouristUser()->getUserUserName() : $user->getUserUserName();
            $data_user['last_name'] = ($user_tourist != null) ? $user_tourist->getUserTouristUser()->getUserLastName() : $user->getUserLastName();
            $data_user['phone'] = ($user_tourist != null) ? $user_tourist->getUserTouristUser()->getUserPhone() : $user->getUserPhone();
            $data_user['city'] = ($user_tourist != null) ? $user_tourist->getUserTouristUser()->getUserCity() : $user->getUserCity();
            $data_user['country'] = ($user_tourist != null) ? (($user_tourist->getUserTouristUser()->getUserCountry() != null) ? $user_tourist->getUserTouristUser()->getUserCountry()->getCoId() : null) : (($user->getUserCountry() != null) ? $user->getUserCountry()->getCoId() : null);
            $data_user['currency'] = ($user_tourist != null) ? $user_tourist->getUserTouristCurrency()->getCurrId() : null;
            $data_user['language'] = ($user_tourist != null) ? $user_tourist->getUserTouristLanguage()->getLangId() : null;
            $form->setData($data_user);
        }
        return $this->render('mycpBundle:user:newUserTourist.html.twig', array('form' => $form->createView(), 'data' => $data, 'id_role' => '', 'edit_user' => $id_user, 'tourist'=> $user_tourist));
    }

    function new_user_partnerAction($id_role, Request $request) {
        $service_security = $this->get('Secure');
        $service_security->verifyAccess();
        $em = $this->getDoctrine()->getEntityManager();
        $countries = $em->getRepository('mycpBundle:country')->findAll();
        $currencies = $em->getRepository('mycpBundle:currency')->findAll();
        $langs = $em->getRepository('mycpBundle:lang')->findAll();
        $data['countries'] = $countries;
        $data['currencies'] = $currencies;
        $data['languages'] = $langs;
        $count_errors = 0;

        $form = $this->createForm(new clientPartnerType($data));
        if ($request->getMethod() == 'POST') {
            $post = $request->request->get('mycp_mycpbundle_client_partnertype');
            $user_db = $em->getRepository('mycpBundle:user')->findBy(array('user_name' => $post['user_name']));
            if ($user_db) {
                $data['error'] = 'Ya existe un usuario con ese nombre.';
                $count_errors++;
            }
            if ($post['email'] != "" && !Utils::validateEmail($post['email'])) {
                $data['error'] = 'Correo no válido';
                $count_errors++;
            }
            $form->handleRequest($request);
            if ($form->isValid() && $count_errors == 0) {
                $dir = $this->container->getParameter('user.dir.photos');
                $factory = $this->get('security.encoder_factory');
                $em->getRepository('mycpBundle:userPartner')->insert($id_role, $request, $dir, $factory);
                $message = 'Usuario añadido satisfactoriamente.';
                $this->get('session')->getFlashBag()->add('message_ok', $message);
                $service_log = $this->get('log');
                $service_log->saveLog('Create entity for user ' . $post['user_name'], BackendModuleName::MODULE_USER);
                return $this->redirect($this->generateUrl('mycp_list_users'));
            } else {
                if ($data['error'] == "")
                    $data['error'] = 'Debe llenar el formulario correctamente.';
            }
        }
        return $this->render('mycpBundle:user:newUserPartner.html.twig', array('form' => $form->createView(), 'data' => $data, 'id_role' => $id_role));
    }

    function edit_user_partnerAction($id_user, Request $request) {
        $service_security = $this->get('Secure');
        $service_security->verifyAccess();
        $em = $this->getDoctrine()->getEntityManager();
        $countries = $em->getRepository('mycpBundle:country')->findAll();
        $currencies = $em->getRepository('mycpBundle:currency')->findAll();
        $langs = $em->getRepository('mycpBundle:lang')->findAll();
        $data['countries'] = $countries;
        $data['currencies'] = $currencies;
        $data['languages'] = $langs;
        $data['edit'] = true;
        $count_errors = 0;

        $request_form = $request->get('mycp_mycpbundle_client_partnertype');
        $data['password'] = $request_form['user_password']['Clave:'];
        $form = $this->createForm(new clientPartnerType($data));

        if ($request->getMethod() == 'POST') {
            $post = $request->request->get('mycp_mycpbundle_client_partnertype');
            if ($post['email'] != "" && !Utils::validateEmail($post['email'])) {
                $data['error'] = 'Correo no válido';
                $count_errors++;
            }

            $form->handleRequest($request);
            if ($form->isValid() && $count_errors == 0) {
                $factory = $this->get('security.encoder_factory');
                $dir = $this->container->getParameter('user.dir.photos');
                $em->getRepository('mycpBundle:userPartner')->edit($id_user, $request, $dir, $factory);
                $message = 'Usuario actualizado satisfactoriamente.';
                $this->get('session')->getFlashBag()->add('message_ok', $message);
                $service_log = $this->get('log');
                $service_log->saveLog('Edit entity for user ' . $request_form['user_name'], BackendModuleName::MODULE_USER);
                return $this->redirect($this->generateUrl('mycp_list_users'));
            }
        } else {
            $user_partner = new userPartner();
            $user_partner = $em->getRepository('mycpBundle:userPartner')->findBy(array('user_partner_user' => $id_user));
            $user_partner = $user_partner[0];
            $data_user['user_name'] = $user_partner->getUserPartnerUser()->getUserName();
            $data_user['address'] = $user_partner->getUserPartnerUser()->getUserAddress();
            $data_user['email'] = $user_partner->getUserPartnerUser()->getUserEmail();
            $data_user['company_name'] = $user_partner->getUserPartnerCompanyName();
            $data_user['company_code'] = $user_partner->getUserPartnerCompanyCode();
            $data_user['contact_person'] = $user_partner->getUserPartnerContactPerson();
            $data_user['phone'] = $user_partner->getUserPartnerUser()->getUserPhone();
            $data_user['city'] = $user_partner->getUserPartnerUser()->getUserCity();
            $data_user['country'] = $user_partner->getUserPartnerUser()->getUserCountry()->getCoId();
            $data_user['currency'] = $user_partner->getUserPartnerCurrency()->getCurrId();
            $data_user['language'] = $user_partner->getUserPartnerLanguage()->getLangId();
            $form->setData($data_user);
        }
        return $this->render('mycpBundle:user:newUserPartner.html.twig', array('form' => $form->createView(), 'data' => $data, 'id_role' => '', 'edit_user' => $id_user));
    }

    function list_userAction($items_per_page, Request $request) {
        $service_security = $this->get('Secure');
        $service_security->verifyAccess();
        $filter_user_name = $request->get('filter_user_name');
        $filter_role = $request->get('filter_role');
        $filter_city = $request->get('filter_city');
        $filter_country = $request->get('filter_country');
        $filter_name = $request->get('filter_name');
        $filter_last_name = $request->get('filter_last_name');
        $filter_email = $request->get('filter_email');
        $sort_by = $request->get('sort_by');

        if ($request->getMethod() == 'POST' && $filter_user_name == 'null' && $filter_role == 'null' && $filter_city == 'null' && $filter_country == 'null' && $filter_name == 'null' && $filter_last_name == 'null' && $filter_email == 'null') {
            $message = 'Debe llenar al menos un campo para filtrar.';
            $this->get('session')->getFlashBag()->add('message_error_local', $message);
            return $this->redirect($this->generateUrl('mycp_list_users'));
        }

        if ($filter_user_name == 'null')
            $filter_user_name = '';
        if ($filter_city == 'null')
            $filter_city = '';
        if ($filter_name == 'null')
            $filter_name = '';
        if ($filter_last_name == 'null')
            $filter_last_name = '';
        if ($filter_email == 'null')
            $filter_email = '';

        $page = 1;
        if (isset($_GET['page']))
            $page = $_GET['page'];
        $em = $this->getDoctrine()->getEntityManager();
        $paginator = $this->get('ideup.simple_paginator');
        $paginator->setItemsPerPage($items_per_page);
        $users = $paginator->paginate($em->getRepository('mycpBundle:user')->getAll($filter_user_name, $filter_role, $filter_city, $filter_country, $filter_name, $filter_last_name, $filter_email))->getResult();
        $service_log = $this->get('log');
        $service_log->saveLog('Visit', BackendModuleName::MODULE_USER);



        return $this->render('mycpBundle:user:list.html.twig', array(
                    'users' => $users,
                    'items_per_page' => $items_per_page,
                    'current_page' => $page,
                    'total_items' => $paginator->getTotalItems(),
                    'filter_user_name' => $filter_user_name,
                    'filter_role' => $filter_role,
                    'filter_city' => $filter_city,
                    'filter_country' => $filter_country,
                    'filter_name' => $filter_name,
                    'filter_last_name' => $filter_last_name,
                    'filter_email' => $filter_email,
                    'sort_by' => $sort_by,
        ));
    }

    function delete_userAction($id_user) {
        $service_security = $this->get('Secure');
        $service_security->verifyAccess();
        $id_loged_user = $this->get('security.context')->getToken()->getUser()->getUserId();
        $em = $this->getDoctrine()->getManager();
        $dir = $this->container->getParameter('user.dir.photos');

        if ($id_loged_user != $id_user) {
            $user = new user();

            $user = $em->getRepository('mycpBundle:user')->find($id_user);
            $user_casa = $em->getRepository('mycpBundle:userCasa')->findBy(array('user_casa_user' => $id_user));
            $user_logs = $em->getRepository('mycpBundle:log')->findBy(array('log_user' => $id_user));
            $general_reservations = $em->getRepository('mycpBundle:generalReservation')->findBy(array('gen_res_user_id' => $id_user));
            $user_favorite = $em->getRepository('mycpBundle:favorite')->findBy(array('favorite_user' => $id_user));
            $user_history = $em->getRepository('mycpBundle:userHistory')->findBy(array('user_history_user' => $id_user));
            $user_comment = $em->getRepository('mycpBundle:comment')->findBy(array('com_user' => $id_user));

            foreach ($user_logs as $log) {
                $em->remove($log);
            }

            foreach ($user_comment as $com) {
                $em->remove($com);
            }

            foreach ($user_history as $hist) {
                $em->remove($hist);
            }

            foreach ($user_favorite as $fav) {
                $em->remove($fav);
            }


            foreach ($general_reservations as $gres) {
                $reservations = $em->getRepository('mycpBundle:ownershipReservation')->findBy(array('own_res_gen_res_id' => $gres));
                foreach ($reservations as $res) {
                    $em->remove($res);
                }
                $em->remove($gres);
            }

            if ($user_casa)
                $em->remove($user_casa[0]);
            $user_tourist = $em->getRepository('mycpBundle:userTourist')->findBy(array('user_tourist_user' => $id_user));
            if ($user_tourist)
                $em->remove($user_tourist[0]);
            $user_partner = $em->getRepository('mycpBundle:userPartner')->findBy(array('user_partner_user' => $id_user));
            if ($user_partner)
                $em->remove($user_partner[0]);

            $user_name = $user->getUserName();
            $photo = $user->getUserPhoto();
            if ($photo) {
                @unlink($dir . $photo->getPhoName());
                $em->remove($photo);
            }

            $em->remove($user);
            $em->flush();
            $users = $em->getRepository('mycpBundle:user')->findAll();

            $message = 'Usuario eliminado satisfactoriamente.';
            $this->get('session')->getFlashBag()->add('message_ok', $message);

            $service_log = $this->get('log');
            $service_log->saveLog('Delete entity for user ' . $user_name, BackendModuleName::MODULE_USER);
            return $this->redirect($this->generateUrl('mycp_list_users'));
        } else {
            $message = 'No se puede eliminar este usuario, está autenticado actualmente.';
            $this->get('session')->getFlashBag()->add('message_error_main', $message);
            return $this->redirect($this->generateUrl('mycp_list_users'));
        }
    }

    function new_roleAction($id_role, Request $request) {
        $service_security = $this->get('Secure');
        $service_security->verifyAccess();
        $em = $this->getDoctrine()->getEntityManager();
        $permissions = $em->getRepository('mycpBundle:rolePermission')->getByRole($id_role);
        $role = $em->getRepository('mycpBundle:role')->find($id_role);
        $role_parent = $em->getRepository('mycpBundle:role')->find($role->getRoleParent());

        if ($role_parent->getRoleName() != 'ROLE_CLIENT_STAFF') {
            return $this->redirect($this->generateUrl('mycp_list_users'));
        }

        $data['permissions'] = $permissions;
        $post = array();

        if ($request->getMethod() == 'POST') {
            $post = $request->request->getIterator()->getArrayCopy();
            $errors = 0;
            if (count($post) == 1) {
                $data['error_checkbox'] = 'Debe seleccionar al menos un permiso.';
                $errors++;
            }
            if (empty($post['role_name'])) {
                $data['error_name'] = 'Este valor no debería estar vacío.';
                $errors++;
            }

            if ($errors == 0) {

                $role_new = new role();
                $role_name = $role->getRoleName() . '_' . strtoupper($post['role_name']);
                $role_db = $em->getRepository('mycpBundle:role')->findBy(array('role_name' => $role_name));
                if (!$role_db) {
                    $role_new->setRoleName($role_name);
                    $role_new->setRoleParent($role->getRoleParent());
                    $role_new->setRoleFixed(0);
                    $em->persist($role_new);

                    $array_keys = array_keys($post);
                    foreach ($array_keys as $item) {

                        if (strpos($item, 'permission_') !== false) {
                            $temp = explode('permission_', $item);
                            $temp[1];
                            $role_permission = new rolePermission();
                            $role_permission->setRpRole($role_new);
                            $permission = $em->getRepository('mycpBundle:permission')->find($temp[1]);
                            $role_permission->setRpPermission($permission);
                            $em->persist($role_permission);
                        }
                    }
                    $em->flush();
                    $message = 'Rol añadido satisfactoriamente.';
                    $this->get('session')->getFlashBag()->add('message_ok', $message);
                    return $this->redirect($this->generateUrl('mycp_list_users'));
                } else {
                    $data['error'] = 'Ya existe un rol llamado ' . $role_name . '.';
                }
            } else {
                $data['error'] = 'Debe llenar el formulario correctamente.';
            }
        }
        $array_count_permissions = array();
        $cont = 0;
        if ($permissions) {
            $section_name = $permissions[0]->getRpPermission()->getPermCategory();
            //var_dump($permissions);exit();
            foreach ($permissions as $per) {
                //var_dump($section_name);
                if ($section_name == $per->getRpPermission()->getPermCategory()) {
                    $cont++;
                } else {
                    array_push($array_count_permissions, $cont);
                    $section_name = $per->getRpPermission()->getPermCategory();
                    $cont = 1;
                }
            }
            array_push($array_count_permissions, $cont);
        }
        //var_dump($array_count_permissions); exit();
        $data['array_count'] = $array_count_permissions;
        return $this->render('mycpBundle:user:newRole.html.twig', array('permissions' => $permissions, 'role' => $role, 'data' => $data, 'post' => $post));
    }

    function delete_roleAction($id_role, Request $request) {
        $em = $this->getDoctrine()->getEntityManager();
        $role = $em->getRepository('mycpBundle:role')->find($id_role);
        $users = $em->getRepository('mycpBundle:user')->findBy(array('user_subrole' => $id_role));
        //var_dump($users); exit();
        if ($role->getRoleFixed() == 1) {
            $message = 'No se puede eliminar el rol ' . $role->getRoleName() . ', es un elemento estático.';
            $this->get('session')->getFlashBag()->add('message_error_local2', $message);
            return $this->redirect($this->generateUrl('mycp_list_users'));
        } elseif ($users) {
            $message = 'No se puede eliminar el rol ' . $role->getRoleName() . ', hay usuarios asociados a este.';
            $this->get('session')->getFlashBag()->add('message_error_local2', $message);
            return $this->redirect($this->generateUrl('mycp_list_users'));
        }


        $permissions = $em->getRepository('mycpBundle:rolePermission')->findBy(array('rp_role' => $id_role));
        if ($permissions) {
            foreach ($permissions as $permission)
                $em->remove($permission);
        }
        $em->remove($role);
        $em->flush();

        /* $service_log= $this->get('log');
          $service_log->saveLog('Edit entity for user '.$request_form['user_name'], BackendModuleName::MODULE_USER); */

        $message = 'Rol eliminado satisfactoriamente.';
        $this->get('session')->getFlashBag()->add('message_ok', $message);
        return $this->redirect($this->generateUrl('mycp_list_users'));
    }

    function get_all_rolesAction($selected) {
        $selected = strtoupper($selected);
        $em = $this->getDoctrine()->getEntityManager();
        $roles = $em->getRepository('mycpBundle:role')->findAll();
        return $this->render('mycpBundle:utils:roles.html.twig', array('roles' => $roles, 'selected' => $selected));
    }

    function getUsersCasaAction($exclude_own_id = null) {
        $em = $this->getDoctrine()->getEntityManager();
        $users_casa = $em->getRepository('mycpBundle:userCasa')->getUsers($exclude_own_id);

        return $this->render('mycpBundle:utils:users_casa.html.twig', array('users' => $users_casa));
    }

    function get_all_usersAction($post) {
        $em = $this->getDoctrine()->getEntityManager();
        $users = $em->getRepository('mycpBundle:user')->findAll();
        $selected = '';
        if (isset($post['selected'])) {
            $selected = $post['selected'];
        }
        return $this->render('mycpBundle:utils:users.html.twig', array('selected' => $selected, 'users' => $users));
    }

    function get_roles_staffAction() {
        $selected = '';
        $em = $this->getDoctrine()->getEntityManager();
        $roles = $em->getRepository('mycpBundle:role')->getStaffRoles();
        return $this->render('mycpBundle:utils:roles.html.twig', array('roles' => $roles, 'selected' => $selected));
    }

    function changeStatusAction($userId) {
        try {
            $em = $this->getDoctrine()->getEntityManager();
            $em->getRepository('mycpBundle:user')->changeStatus($userId);

            $message = 'Se ha modificado satisfactoriamente el estado del usuario casa asociado.';
            $this->get('session')->getFlashBag()->add('message_ok', $message);



        } catch (\Exception $e) {
            $message = 'Ha ocurrido un error durante el cambio del estado del usuario casa asociado.';
            $this->get('session')->getFlashBag()->add('message_error_main', $message);
        }

        return $this->redirect($this->generateUrl('mycp_list_ownerships'));
    }

}
