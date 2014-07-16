<?php

namespace MyCp\FrontEndBundle\Controller;

use MyCp\FrontEndBundle\Form\changePasswordUserType;
use MyCp\FrontEndBundle\Form\ownerContact;
use MyCp\FrontEndBundle\Form\registerUserType;
use MyCp\FrontEndBundle\Form\restorePasswordUserType;
use MyCp\FrontEndBundle\Form\touristContact;
use MyCp\FrontEndBundle\Form\profileUserType;
use MyCp\mycpBundle\Entity\user;
use MyCp\mycpBundle\Entity\photo;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class userController extends Controller {

    public function registerAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $errors = array();
        $all_post = array();
        $data = array();
        $data['countries'] = $em->getRepository('mycpBundle:country')->findAllByAlphabetical();

        $form = $this->createForm(new registerUserType($this->get('translator'), $data));
        if ($request->getMethod() == 'POST') {
            $post = $request->get('mycp_frontendbundle_register_usertype');
            $all_post = $request->request->getIterator()->getArrayCopy();
            $form->handleRequest($request);
            $user_db = $em->getRepository('mycpBundle:user')->findBy(array(
                'user_email' => $post['user_email'],
                'user_created_by_migration' => false));
            if ($user_db) {
                $errors['used_email'] = $this->get('translator')->trans("USER_EMAIL_IN_USE");
            }
            if ($form->isValid() && !$user_db) {
                $factory = $this->get('security.encoder_factory');
                $user2 = new user();
                $encoder = $factory->getEncoder($user2);

                $session = $request->getSession();

                $languageCode = $request->attributes->get('app_lang_code');
                $languageCode = empty($languageCode) ? $request->attributes->get('_locale') : $session->get('_locale', 'en');
                $languageCode = strtoupper($languageCode);

                $currency = $em->getRepository('mycpBundle:currency')->findOneBy(array('curr_default' => true));

                if($session->get('curr_acronym') != $currency->getCurrCode())
                    $currency = $em->getRepository('mycpBundle:currency')->findOneBy(array('curr_code' => $session->get('curr_acronym')));

                $user_db = $em->getRepository('mycpBundle:user')
                        ->registerUser($post, $request, $encoder, $this->get('translator'), $languageCode, $currency);
                $service_security = $this->get('Secure');
                $encode_string = $service_security->encode_string($user_db->getUserEmail() . '///' . $user_db->getUserId());

                //mailing
                $enableRoute = 'frontend_enable_user';
                $enableUrl = $this->get('router')->generate($enableRoute, array('string' => $encode_string), true);
                $body = $this->render('FrontEndBundle:mails:enableAccount.html.twig', array('enableUrl' => $enableUrl));

                $service_email = $this->get('Email');
                $service_email->send_templated_email($this->get('translator')->trans('EMAIL_ACCOUNT_REGISTERED_SUBJECT'), 'noreply@mycasaparticular.com', $user_db->getUserEmail(), $body->getContent());

                $message = $this->get('translator')->trans("USER_CREATE_ACCOUNT_SUCCESS");
                $this->get('session')->getFlashBag()->add('message_global_success', $message);
                return $this->redirect($this->generateUrl('frontend_login'));
            }
        }

        return $this->render('FrontEndBundle:user:registerUser.html.twig', array(
                    'form' => $form->createView(),
                    'errors' => $errors,
                    'post' => $all_post
        ));
    }

    public function enableAction($string) {
        $service_security = $this->get('Secure');
        $decode_string = $service_security->decode_string($string);
        $user_atrib = explode('///', $decode_string);

        $em = $this->getDoctrine()->getManager();
        if (isset($user_atrib[1])) {
            $user = $em->getRepository('mycpBundle:user')->findBy(array('user_id' => $user_atrib[1], 'user_email' => $user_atrib[0]));

            if ($user) {
                $user = $user[0];
                $user->setUserEnabled(1);
                $user->setUserActivationDate(new \DateTime());
                $em->persist($user);
                $em->flush();
                $message = $this->get('translator')->trans("USER_ACTIVATE_ACCOUNT_SUCCESS");
                $this->get('session')->getFlashBag()->add('message_global_success', $message);
                return $this->redirect($this->generateUrl('frontend_login'));
            }
        } else {
            throw $this->createNotFoundException($this->get('translator')->trans("USER_ACTIVATE_ERROR"));
        }
        throw $this->createNotFoundException($this->get('translator')->trans("USER_ACTIVATE_ERROR"));
    }

    public function restore_passwordAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $errors = array();
        $form = $this->createForm(new restorePasswordUserType($this->get('translator')));
        if ($request->getMethod() == 'POST') {
            $post = $request->get('mycp_frontendbundle_restore_password_usertype');
            $form->handleRequest($request);
            if ($form->isValid()) {
                $user_db = $em->getRepository('mycpBundle:user')->findOneBy(array('user_email' => $post['user_email']));
                if ($user_db) {
                    $service_security = $this->get('Secure');
                    $encode_string = $service_security->encode_string($user_db->getUserEmail() . '///' . $user_db->getUserId());

                    $change_passwordRoute = 'frontend_change_password_user';
                    $changeUrl = $this->get('router')
                            ->generate($change_passwordRoute, array('string' => $encode_string), true);
                    //mailing
                    $body = $this->render('FrontEndBundle:mails:restorePassword.html.twig', array('changeUrl' => $changeUrl));

                    $service_email = $this->get('Email');
                    $service_email->send_templated_email(
                            $this->get('translator')->trans('EMAIL_RESTORE_ACCOUNT'), 'noreply@mycasaparticular.com', $user_db->getUserEmail(), $body->getContent());
                    $message = $this->get('translator')->trans("USER_PASSWORD_RECOVERY");
                    $this->get('session')->getFlashBag()->add('message_global_success', $message);
                    return $this->redirect($this->generateUrl('frontend_login'));
                } else {
                    $errors['no_email'] = $this->get('translator')->trans("USER_PASSWORD_RECOVERY_ERROR");
                }
                /* $service_security= $this->get('Secure');
                  $encode_string=$service_security->encode_string($user->getUserEmail().'///'.$user->getUserId());
                  echo '<url>/user_enable/'.$encode_string; */
            }
        }

        return $this->render('FrontEndBundle:user:restorePasswordUser.html.twig', array(
                    'form' => $form->createView(),
                    'errors' => $errors
        ));
    }

    public function change_password_startAction() {
        $service_security = $this->get('Secure');
        $user = $this->getUser();
        $encode_string = $service_security->encode_string($user->getUserEmail() . '///' . $user->getUserId());
        return $this->redirect($this->generateUrl('frontend_change_password_user', array('string' => $encode_string)));
    }

    public function change_passwordAction($string, Request $request) {
        $em = $this->getDoctrine()->getManager();
        $errors = array();
        $form = $this->createForm(new changePasswordUserType($this->get('translator')));
        if ($request->getMethod() == 'POST') {
            $post = $request->get('mycp_frontendbundle_change_password_usertype');
            $form->handleRequest($request);
            if ($form->isValid()) {
                $service_security = $this->get('Secure');
                $decode_string = $service_security->decode_string($string);
                $user_atrib = explode('///', $decode_string);
                if (isset($user_atrib[1])) {
                    $user = $em->getRepository('mycpBundle:user')->findOneBy(array('user_id' => $user_atrib[1], 'user_email' => $user_atrib[0]));
                    if ($user) {
                        $factory = $this->get('security.encoder_factory');
                        $user2 = new user();
                        $encoder = $factory->getEncoder($user2);
                        if (isset($post['user_password']['Clave']))
                            $password = $encoder->encodePassword($post['user_password']['Clave'], $user->getSalt());
                        else
                            $password = $encoder->encodePassword($post['user_password']['Password'], $user->getSalt());
                        $user->setUserPassword($password);
                        $em->persist($user);
                        $em->flush();

                        $message = $this->get('translator')->trans('EMAIL_PASS_CHANGED');
                        //mailing
                        $service_email = $this->get('Email');
                        $service_email->send_templated_email(
                                $message, 'noreply@mycasaparticular.com', $user->getUserEmail(), $message);

                        $this->get('session')->getFlashBag()->add('message_global_success', $message);
                        return $this->redirect($this->generateUrl('frontend_login'));
                    }
                } else {
                    throw $this->createNotFoundException($this->get('translator')->trans("USER_PASSWORD_CHANGE_ERROR"));
                }
            }
        }
        //var_dump($form->createView()->getChildren());
        //exit();
        return $this->render('FrontEndBundle:user:changePasswordUser.html.twig', array(
                    'string' => $string,
                    'form' => $form->createView(),
                    'errors' => $errors
        ));
    }

    public function register_user_confirmationAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $errors = array();

        $form = $this->createForm(new restorePasswordUserType($this->get('translator')));
        if ($request->getMethod() == 'POST') {

            $post = $request->get('mycp_frontendbundle_restore_password_usertype');
            $form->handleRequest($request);
            if ($form->isValid()) {

                $user_db = $em->getRepository('mycpBundle:user')->findBy(array('user_email' => $post['user_email']));
                if ($user_db) {
                    $user_db = $user_db[0];
                    $service_security = $this->get('Secure');
                    $encode_string = $service_security->encode_string($user_db->getUserEmail() . '///' . $user_db->getUserId());
                    $enableRoute = 'frontend_enable_user';
                    $enableUrl = $this->get('router')
                            ->generate($enableRoute, array('string' => $encode_string), true);
                    $service_email = $this->get('Email');
                    $body = $this->render('FrontEndBundle:mails:enableAccount.html.twig', array('enableUrl' => $enableUrl));
                    $service_email->send_templated_email($this->get('translator')->trans("USER_ACCOUNT_ACTIVATION_EMAIL"), 'noreply@mycasaparticular.com', $user_db->getUserEmail(), $body->getContent());
                    $message = $this->get('translator')->trans("USER_CREATE_ACCOUNT_SUCCESS");
                    $this->get('session')->getFlashBag()->add('message_global_success', $message);
                    return $this->redirect($this->generateUrl('frontend_login'));
                } else {
                    $errors['no_email'] = $this->get('translator')->trans("USER_PASSWORD_RECOVERY_ERROR");
                }
            }
        }

        return $this->render('FrontEndBundle:user:registerConfirmationUser.html.twig', array(
                    'form' => $form->createView(),
                    'errors' => $errors
        ));
    }

    public function contactAction(Request $request) {
        $errors = array();
        $this->get('session')->set("form_type", null);
        $form_tourist = $this->createForm(new touristContact($this->get('translator')));
        $form_owner = $this->createForm(new ownerContact($this->get('translator')));
        if ($request->getMethod() == 'POST') {
            $post_tourist = $request->get('mycp_frontendbundle_contact_tourist');
            if ($post_tourist != null) {
                $form_tourist->handleRequest($request);
                $this->get('session')->set("form_type", "tourist_form");

                if ($form_tourist->isValid()) {
                    $tourist_name = $post_tourist['tourist_name'];
                    $tourist_last_name = $post_tourist['tourist_last_name'];
                    $tourist_email = $post_tourist['tourist_email'];
                    $tourist_phone = $post_tourist['tourist_phone'];
                    $tourist_comment = $post_tourist['tourist_comment'];

                    /* $service_security = $this->get('Secure');
                      $encode_string = $service_security->encode_string($user_db->getUserEmail() . '///' . $user_db->getUserId());
                      $enableRoute = 'frontend_enable_user';
                      $enableUrl = $this->get('router')
                      ->generate($enableRoute, array('string' => $encode_string), true); */

                    $service_email = $this->get('Email');
                    $content = $this->render('FrontEndBundle:mails:contactMailBody.html.twig', array(
                        'tourist_name' => $tourist_name,
                        'tourist_last_name' => $tourist_last_name,
                        'tourist_phone' => $tourist_phone,
                        'tourist_email' => $tourist_email,
                        'tourist_comment' => $tourist_comment
                    ));
                    $service_email->send_templated_email(
                            'Contacto de huesped', $tourist_email, 'info@mycasaparticular.com ', $content->getContent());
                    $message = $this->get('translator')->trans("USER_CONTACT_TOURIST_SUCCESS");
                    $this->get('session')->getFlashBag()->add('message_global_success', $message);

                    return $this->redirect($this->generateUrl('frontend_contact_user'));
                }
            }

            $post_owner = $request->get('mycp_frontendbundle_contact_owner');

            if ($post_owner != null) {
                $form_owner->handleRequest($request);
                $this->get('session')->set("form_type", "owner_form");

                if ($form_owner->isValid()) {
                    $owner_full_name = $post_owner['owner_full_name'];
                    $owner_own_name = $post_owner['owner_own_name'];
                    $owner_email = $post_owner['owner_email'];
                    $owner_province = $post_owner['owner_province'];
                    $owner_mun = $post_owner['owner_mun'];
                    $owner_comment = $post_owner['owner_comment'];

                    /* $service_security = $this->get('Secure');
                      $encode_string = $service_security->encode_string($user_db->getUserEmail() . '///' . $user_db->getUserId());
                      $enableRoute = 'frontend_enable_user';
                      $enableUrl = $this->get('router')
                      ->generate($enableRoute, array('string' => $encode_string), true); */

                    $service_email = $this->get('Email');
                    $content = $this->render('FrontEndBundle:mails:ownerContactMailBody.html.twig', array(
                        'owner_fullname' => $owner_full_name,
                        'own_name' => $owner_own_name,
                        'province' => $owner_province,
                        'municipality' => $owner_mun,
                        'comments' => $owner_comment,
                        'email' => $owner_email
                    ));
                    $service_email->send_templated_email(
                            'Contacto de propietario', $owner_email, 'casa@mycasaparticular.com', $content->getContent());

                    $message = $this->get('translator')->trans("USER_CONTACT_OWNER_SUCCESS");
                    $this->get('session')->getFlashBag()->add('message_global_success', $message);

                    return $this->redirect($this->generateUrl('frontend_contact_user'));
                }
            }
        }

        return $this->render('FrontEndBundle:user:contact.html.twig', array(
                    'form_tourist' => $form_tourist->createView(),
                    'form_owner' => $form_owner->createView(),
                    'errors' => $errors
        ));
    }

    public function info_tab_userAction($destination_id = null, $ownership_id = null) {
        $em = $this->getDoctrine()->getManager();
        $user_ids = $em->getRepository('mycpBundle:user')->user_ids($this);

        $favorite_destinations = $em->getRepository('mycpBundle:favorite')->get_favorite_destinations($user_ids["user_id"], $user_ids["session_id"], 4, $destination_id);

        for ($i = 0; $i < count($favorite_destinations); $i++) {
            if (!file_exists(realpath("uploads/destinationImages/" . $favorite_destinations[$i]['photo'])))
                $favorite_destinations[$i]['photo'] = 'no_photo.png';
        }


        $ownership_favorities = $em->getRepository('mycpBundle:favorite')->get_favorite_ownerships($user_ids["user_id"], $user_ids["session_id"], 4, $ownership_id);

        for ($i = 0; $i < count($ownership_favorities); $i++) {
            if (!file_exists(realpath("uploads/ownershipImages/" . $ownership_favorities[$i]['photo'])))
                $ownership_favorities[$i]['photo'] = 'no_photo.png';
        }

        $history_destinations = $em->getRepository('mycpBundle:userHistory')->get_history_destinations($user_ids["user_id"], $user_ids["session_id"], 4, $destination_id);
        for ($i = 0; $i < count($history_destinations); $i++) {
            if (!file_exists(realpath("uploads/destinationImages/" . $history_destinations[$i]['photo'])))
                $history_destinations[$i]['photo'] = 'no_photo.png';
        }

        $history_owns = $em->getRepository('mycpBundle:userHistory')->get_history_ownerships($user_ids["user_id"], $user_ids["session_id"], 4, $ownership_id);
        for ($i = 0; $i < count($history_owns); $i++) {
            if (!file_exists(realpath("uploads/ownershipImages/" . $history_owns[$i]['photo'])))
                $history_owns[$i]['photo'] = 'no_photo.png';
        }
        return $this->render('FrontEndBundle:user:infoTabUser.html.twig', array(
                    'destination_favorites' => $favorite_destinations,
                    'history_destinations' => $history_destinations,
                    'favorite_owns' => $ownership_favorities,
                    'history_owns' => $history_owns
        ));
    }

    public function profileAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $errors = array();
        $all_post = array();
        $data = array();
        $data['countries'] = $em->getRepository('mycpBundle:country')->findAll();
        $data['currencies'] = $em->getRepository('mycpBundle:currency')->findAll();
        $data['languages'] = $em->getRepository('mycpBundle:lang')->findBy(array('lang_active' => 1), array('lang_name' => 'ASC'));

        $user = $this->getUser();
        // var_dump($user); exit();
        $userTourist = $em->getRepository('mycpBundle:userTourist')->findOneBy(array('user_tourist_user' => $user->getUserId()));

        $complete_user = array(
            'user_user_name' => $user->getUserUserName(),
            'user_last_name' => $user->getUserLastName(),
            'user_email' => $user->getUserEmail(),
            'user_phone' => $user->getUserPhone(),
            'user_cell' => $userTourist->getUserTouristCell(),
            'user_address' => $user->getUserAddress(),
            'user_city' => $user->getUserCity(),
            'user_newsletter' => $user->getUserNewsletters(),
            'user_country' => $user->getUserCountry()->getCoId(),
            'user_zip_code' => $userTourist->getUserTouristPostalCode(),
            'user_gender' => $userTourist->getUserTouristGender(),
            'user_currency' => ($userTourist->getUserTouristCurrency() != null) ? $userTourist->getUserTouristCurrency()->getCurrId() : 0,
            'user_lang' => ($userTourist->getUserTouristLanguage() != null) ? $userTourist->getUserTouristLanguage()->getLangId() : 0
        );

        $form = $this->createForm(new profileUserType($this->get('translator'), $data), $complete_user);
        if ($request->getMethod() == 'POST') {
            $post = $request->get('mycp_frontendbundle_profile_usertype');
            $all_post = $request->request->getIterator()->getArrayCopy();
            $form->handleRequest($request);

            if ($form->isValid()) {

                $user_db = $em->getRepository('mycpBundle:user')->findOneBy(array('user_email' => $post['user_email']));
                if ($user_db->getUserId() == $user->getUserId()) {

                    $user->setUserUserName($post['user_user_name']);
                    $user->setUserLastName($post['user_last_name']);
                    $user->setUserEmail($post['user_email']);
                    $user->setUserPhone($post['user_phone']);
                    $user->setUserCountry($em->getRepository('mycpBundle:country')->find($post['user_country']));
                    $user->setUserAddress($post['user_address']);
                    $user->setUserCity($post['user_city']);

                    if (isset($post['user_newsletters']))
                        $user->setUserNewsletters(1);
                    else
                        $user->setUserNewsletters(0);

                    //subir photo
                    $dir = $this->container->getParameter('user.dir.photos');
                    $file = $request->files->get('user_photo');
                    // var_dump($request->files);
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

                    $userTourist->setUserTouristCell($post['user_cell']);
                    $userTourist->setUserTouristPostalCode($post['user_zip_code']);
                    $userTourist->setUserTouristGender($post['user_gender']);
                    $userTourist->setUserTouristCurrency($em->getRepository('mycpBundle:currency')->find($post['user_currency']));
                    $userTourist->setUserTouristLanguage($em->getRepository('mycpBundle:lang')->find($post['user_lang']));
                    $em->persist($userTourist);
                    $em->flush();

                    $lang = $userTourist->getUserTouristLanguage();
                    $locale = strtolower($lang->getLangCode());

                    $message = $this->get('translator')->trans("USER_PROFILE_SAVED", array(),'messages', $locale);
                    $this->get('session')->getFlashBag()->add('message_global_success', $message);

                    //Change global currency and language if user set a new one
                    $session = $request->getSession();

                    $curr = $userTourist->getUserTouristCurrency();
                    if (isset($curr)) {
                        $session->set("curr_rate", $curr->getCurrCucChange());
                        $session->set("curr_symbol", $curr->getCurrSymbol());
                        $session->set("curr_acronym", $curr->getCurrCode());
                    }


                    if (isset($lang)) {
                        $session->set('browser_lang', $locale);
                        $session->set('app_lang_name', $lang->getLangName());
                        $session->set('app_lang_code', $lang->getLangCode());


                        $locale = array('locale' => $locale, '_locale' => $locale);
                        return $this->redirect($this->generateUrl("frontend_profile_user", $locale));
                    }
                } else {
                    $errors['no_email'] = $this->get('translator')->trans("USER_PROFILE_EMAIL_ERROR");
                }
            } else {
                $errors['complete_form'] = $this->get('translator')->trans("FILL_FORM_CORRECTLY");
            }
        }
        return $this->render('FrontEndBundle:user:profileUser.html.twig', array(
                    'form' => $form->createView(),
                    'errors' => $errors,
                    'post' => $all_post,
                    'user' => $user,
                    'tourist' => $userTourist

        ));
    }

}
