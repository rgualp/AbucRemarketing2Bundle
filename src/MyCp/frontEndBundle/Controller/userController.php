<?php

namespace MyCp\frontEndBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use MyCp\frontEndBundle\Form\registerUserType;
use MyCp\frontEndBundle\Form\restorePasswordUserType;
use MyCp\frontEndBundle\Form\changePasswordUserType;
use MyCp\frontEndBundle\Form\touristContact;
use MyCp\frontEndBundle\Form\ownerContact;
use MyCp\mycpBundle\Entity\userTourist;
use MyCp\mycpBundle\Entity\user;

class userController extends Controller {

    public function registerAction(Request $request) {
        $em = $this->getDoctrine()->getEntityManager();
        $errors = array();
        $all_post = array();

        $form = $this->createForm(new registerUserType($this->get('translator')));
        if ($request->getMethod() == 'POST') {
            $post = $request->get('mycp_frontendbundle_register_usertype');
            $all_post = $request->request->getIterator()->getArrayCopy();
            $form->bindRequest($request);
            $user_db = $em->getRepository('mycpBundle:user')->findBy(array('user_email' => $post['user_email']));
            if ($user_db) {
                $errors['used_email'] = 'El email está siendo utilizado por otro usuario.';
            }
            if ($form->isValid() && !$user_db) {
                $factory = $this->get('security.encoder_factory');
                $user2 = new user();
                $encoder = $factory->getEncoder($user2);
                $user_db = $em->getRepository('mycpBundle:user')->frontend_register_user($post, $request, $factory, $encoder, $this->get('translator'));
                $service_security = $this->get('Secure');
                $encode_string = $service_security->encode_string($user_db->getUserEmail() . '///' . $user_db->getUserId());

                //mailing
                $enableRoute = 'frontend_enable_user';
                $enableUrl = $this->get('router')
                        ->generate($enableRoute, array('string' => $encode_string), true);

                $service_email = $this->get('Email');
                $service_email->send_email(
                        'Activación de su cuenta en MyCasaParticular', 'noreply@mycasaparticular.com', $user_db->getUserEmail(), 'Gracias por registrarse en MyCasaParticular.com', "Visite el siguiente link para activar su cuenta: $enableUrl");

                //var_dump($encode_string); exit();
                $message = 'Gracias por registrarse. Se ha enviado un email para que active su cuenta.';
                $this->get('session')->setFlash('message_global_success', $message);
                return $this->redirect($this->generateUrl('frontend_login'));
            }
        }

        return $this->render('frontEndBundle:user:registerUser.html.twig', array(
                    'form' => $form->createView(),
                    'errors' => $errors,
                    'post' => $all_post
                ));
    }

    public function enableAction($string) {
        $service_security = $this->get('Secure');
        $decode_string = $service_security->decode_string($string);
        $user_atrib = explode('///', $decode_string);

        $em = $this->getDoctrine()->getEntityManager();
        if (isset($user_atrib[1])) {
            $user = $em->getRepository('mycpBundle:user')->findBy(array('user_id' => $user_atrib[1], 'user_email' => $user_atrib[0]));
            if ($user) {
                $user = $user[0];
                $user->setUserEnabled(1);
                $em->persist($user);
                $em->flush();
                $message = 'Grácias. Su usuario ha sido activado satisfactoriamente.';
                $this->get('session')->setFlash('message_global_success', $message);
                return $this->redirect($this->generateUrl('frontend_login'));
            }
        } else {
            throw $this->createNotFoundException(
                    'Imposible activar usuario, los parámetros no son correctos.'
            );
        }
    }

    public function restore_passwordAction(Request $request) {
        $em = $this->getDoctrine()->getEntityManager();
        $currency_list = $em->getRepository('mycpBundle:currency')->findAll();
        $languages_list = $em->getRepository('mycpBundle:lang')->get_active_languages();
        $errors = array();
        $form = $this->createForm(new restorePasswordUserType($this->get('translator')));
        if ($request->getMethod() == 'POST') {
            $post = $request->get('mycp_frontendbundle_restore_password_usertype');
            $form->bindRequest($request);
            if ($form->isValid()) {
                $user_db = $em->getRepository('mycpBundle:user')->findBy(array('user_email' => $post['user_email']));
                if ($user_db) {
                    $user_db = $user_db[0];
                    $service_security = $this->get('Secure');
                    $encode_string = $service_security->encode_string($user_db->getUserEmail() . '///' . $user_db->getUserId());

                    $change_passwordRoute = 'frontend_change_password_user';
                    $changeUrl = $this->get('router')
                            ->generate($change_passwordRoute, array('string' => $encode_string), true);
                    //mailing
                    $service_email = $this->get('Email');

                    $service_email->send_email(
                            'Restauración de su cuenta en MyCasaParticular', 'noreply@mycasaparticular.com', $user_db->getUserEmail(), 'Recupere su contraseña en MyCasaParticular.com', 'Visite el siguiente link para cambiar su contraseña. ' . $changeUrl);
                    //var_dump($encode_string); exit();
                    $message = 'Se ha enviado un email para que recupere su contraseña.';
                    $this->get('session')->setFlash('message_global_success', $message);
                    return $this->redirect($this->generateUrl('frontend_login'));
                } else {
                    $errors['no_email'] = 'No existe ningún usuario con ese email.';
                }
                /* $service_security= $this->get('Secure');
                  $encode_string=$service_security->encode_string($user->getUserEmail().'///'.$user->getUserId());
                  echo '<url>/user_enable/'.$encode_string; */
            }
        }

        return $this->render('frontEndBundle:user:restorePasswordUser.html.twig', array(
                    'form' => $form->createView(),
                    'errors' => $errors
                ));
    }

    public function change_passwordAction($string, Request $request) {
        $em = $this->getDoctrine()->getEntityManager();
        $currency_list = $em->getRepository('mycpBundle:currency')->findAll();
        $languages_list = $em->getRepository('mycpBundle:lang')->get_active_languages();
        $errors = array();
        $form = $this->createForm(new changePasswordUserType($this->get('translator')));
        if ($request->getMethod() == 'POST') {
            $post = $request->get('mycp_frontendbundle_change_password_usertype');
            $form->bindRequest($request);
            if ($form->isValid()) {
                $service_security = $this->get('Secure');
                $decode_string = $service_security->decode_string($string);
                $user_atrib = explode('///', $decode_string);
                if (isset($user_atrib[1])) {
                    $user = $em->getRepository('mycpBundle:user')->findBy(array('user_id' => $user_atrib[1], 'user_email' => $user_atrib[0]));
                    if ($user) {
                        $user = $user[0];
                        $factory = $this->get('security.encoder_factory');
                        $user2 = new user();
                        $encoder = $factory->getEncoder($user2);
                        $password = $encoder->encodePassword($post['user_password']['Clave:'], $user->getSalt());

                        $user->setUserPassword($password);
                        $em->persist($user);
                        $em->flush();
                        $message = 'Su contraseña ha sido cambiada satisfactoriamente.';
                        $this->get('session')->setFlash('message_global_success', $message);
                        return $this->redirect($this->generateUrl('frontend_login'));
                    }
                } else {
                    throw $this->createNotFoundException(
                            'Imposible cambiar contraseña, los parámetros no son correctos.'
                    );
                }
            }
        }

        return $this->render('frontEndBundle:user:changePasswordUser.html.twig', array(
                    'string' => $string,
                    'form' => $form->createView(),
                    'errors' => $errors
                ));
    }

    public function register_user_confirmationAction(Request $request) {
        $em = $this->getDoctrine()->getEntityManager();
        $errors = array();

        $form = $this->createForm(new restorePasswordUserType($this->get('translator')));
        if ($request->getMethod() == 'POST') {
            $post = $request->get('mycp_frontendbundle_restore_password_usertype');
            $form->bindRequest($request);
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
                    $service_email->send_email(
                            'Activación de su cuenta en MyCasaParticular', 'noreply@mycasaparticular.com', $user_db->getUserEmail(), 'Gracias por registrarse en MyCasaParticular.com', 'Visite el siguiente link para activar su cuenta. ' . $enableUrl);

                    $message = 'Grácias por registrarse. Se ha enviado un email para que active su cuenta.';
                    $this->get('session')->setFlash('message_global_success', $message);
                    return $this->redirect($this->generateUrl('frontend_login'));
                } else {
                    $errors['no_email'] = 'No existe ningún usuario con ese email.';
                }
            }
        }

        return $this->render('frontEndBundle:user:registerConfirmationUser.html.twig', array(
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
                $form_tourist->bindRequest($request);
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
                    $service_email->send_email(
                            'Contacto de un huesped', 'info@mycasaparticular.com ', $tourist_email, "El Sr(a). $tourist_name $tourist_last_name, con numero de telefono $tourist_phone, ha hecho el siguiente comentario: $tourist_comment");

                    $message = 'Gracias por contactar con nosotros. Su comentario ha sido enviado.';
                    $this->get('session')->setFlash('message_global_success', $message);
                    
                    return $this->redirect($this->generateUrl('frontend_contact_user'));
                }
            }

            $post_owner = $request->get('mycp_frontendbundle_contact_owner');

            if ($post_owner != null) {
                $form_owner->bindRequest($request);
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
                    $service_email->send_email(
                            'Contacto de un propietario', 'casa@mycasaparticular.com', $owner_email, "El Sr(a). $owner_full_name, desea incluir una nueva casa con los siguientes datos:  
                            Duenno(a): $owner_full_name;  Nombre de la casa: $owner_own_name; 
                            Provincia: $owner_province; 
                            Municipio: $owner_mun; 
                            Comentarios: $owner_comment");

                    $message = 'Gracias por contactar con nosotros. Sus datos han sido enviados.';
                    $this->get('session')->setFlash('message_global_success', $message);
                    
                    return $this->redirect($this->generateUrl('frontend_contact_user'));
                }
            }
        }

        return $this->render('frontEndBundle:user:contact.html.twig', array(
                    'form_tourist' => $form_tourist->createView(),
                    'form_owner' => $form_owner->createView(),
                    'errors' => $errors
                ));
    }

    public function info_tab_userAction($destination_id = null, $ownership_id = null) {
        $em = $this->getDoctrine()->getEntityManager();
        $user_ids = $em->getRepository('mycpBundle:user')->user_ids($this);

        $favorite_destination_ids = $em->getRepository('mycpBundle:favorite')->get_element_id_list(false, $user_ids["user_id"], $user_ids["session_id"],4, $destination_id);
        $destination_favorities = $em->getRepository('mycpBundle:destination')->get_list_by_ids($favorite_destination_ids);
        $destination_favorities_photos = $em->getRepository('mycpBundle:destination')->get_destination_photos($destination_favorities);
        $destination_favorities_localization = $em->getRepository('mycpBundle:destination')->get_destination_location($destination_favorities);
        $destination_favorities_statistics = $em->getRepository('mycpBundle:destination')->get_destination_owns_statistics($destination_favorities);
        
        $favorite_own_ids = $em->getRepository('mycpBundle:favorite')->get_element_id_list(true, $user_ids["user_id"], $user_ids["session_id"],4, $ownership_id);
        $ownership_favorities = $em->getRepository('mycpBundle:ownership')->getListByIds($favorite_own_ids);
        $ownership_favorities_photos = $em->getRepository('mycpBundle:ownership')->get_photos_array($ownership_favorities);
       
        $history_destinations = $em->getRepository('mycpBundle:userHistory')->get_list_entity($user_ids, false, 4, $destination_id);
        $history_destinations_photos = $em->getRepository('mycpBundle:destination')->get_destination_photos($history_destinations);
        $history_destinations_localization = $em->getRepository('mycpBundle:destination')->get_destination_location($history_destinations);
        $history_destinations_statistics = $em->getRepository('mycpBundle:destination')->get_destination_owns_statistics($history_destinations);
        $history_destinations_favorites = $em->getRepository('mycpBundle:favorite')->is_in_favorite_array($history_destinations, false,$user_ids['user_id'], $user_ids['session_id']);
        
        $history_owns = $em->getRepository('mycpBundle:userHistory')->get_list_entity($user_ids, true, 4, $ownership_id);
        $history_owns_photos = $em->getRepository('mycpBundle:ownership')->get_photos_array($history_owns);
        $history_owns_favorites = $em->getRepository('mycpBundle:favorite')->is_in_favorite_array($history_owns, true,$user_ids['user_id'], $user_ids['session_id']);
        
        return $this->render('frontEndBundle:user:infoTabUser.html.twig', array(
            'destination_favorites' => $destination_favorities,
            'destination_favorites_photos' => $destination_favorities_photos,
            'destination_favorites_localizations' => $destination_favorities_localization,
            'destination_favorities_statistics' => $destination_favorities_statistics,
            'history_destinations' => $history_destinations,
            'history_destinations_photos' => $history_destinations_photos,
            'history_destinations_localizations' => $history_destinations_localization,
            'history_destinations_statistics' => $history_destinations_statistics,
            'history_destinations_favorites' => $history_destinations_favorites,
            'favorite_owns' => $ownership_favorities,
            'favorite_owns_photos' => $ownership_favorities_photos,
            'history_owns' => $history_owns,
            'history_owns_photos' => $history_owns_photos,
            'history_owns_favorites' => $history_owns_favorites
                ));
    }

}
