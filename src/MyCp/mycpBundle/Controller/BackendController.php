<?php

namespace MyCp\mycpBundle\Controller;

use MyCp\mycpBundle\Form\restorePasswordUserType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class BackendController extends Controller {

    public function backend_frontAction() {
        if ($this->get('security.context')->isGranted('ROLE_CLIENT_TOURIST')) {
            $this->get('security.context')->setToken(null);
            //$this->get('request')->getSession()->invalidate();
            return $this->redirect($this->generateUrl('frontend_welcome'));
        } else if ($this->get('security.context')->isGranted('ROLE_CLIENT_CASA')) {
            return $this->redirect($this->generateUrl('mycp_lodging_front'));
        } else {
            $user = $this->get('security.context')->getToken()->getUser();
            $em = $this->getDoctrine()->getManager();
            if ($user->getUserPhoto())
                $photo = $em->getRepository('mycpBundle:photo')->find($user->getUserPhoto()->getPhoId());
            else
                $photo = null;
            return $this->render('mycpBundle:backend:welcome.html.twig', array('photo' => $photo));
        }
    }

    public function forgotPasswordAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $errors = array();
        $form = $this->createForm(new restorePasswordUserType($this->get('translator')));
        if ($request->getMethod() == 'POST') {
            $post = $request->get('mycp_mycpbundle_restore_password_usertype');
            $form->handleRequest($request);
            if ($form->isValid()) {

                $user_db = $em->getRepository('mycpBundle:user')->getUserBackendByEmail($post['user_email']);
                if ($user_db) {
                    $service_security = $this->get('Secure');
                    $encode_string = $service_security->getEncodedUserString($user_db);

                    $change_passwordRoute = 'mycp_change_password_user';
                    $changeUrl = $this->get('router')
                        ->generate($change_passwordRoute, array('string' => $encode_string), true);
                    //mailing
                    $body = $this->render('mycpBundle:mail:restorePassword.html.twig', array('changeUrl' => $changeUrl));

                    $service_email = $this->get('Email');
                    $service_email->sendEmail("Restauración de su cuenta en MyCasaParticular", 'no_reply@mycasaparticular.com', 'MyCasaParticular.com', $user_db->getUserEmail(), $body->getContent());
                    $message = "Se ha enviado un correo para que recupere su contraseña.";
                    $this->get('session')->getFlashBag()->add('message_ok', $message);
                    return $this->redirect($this->generateUrl('backend_login'));
                } else {
                    $message = "No existe ningún usuario con ese correo con acceso a esta sección.";
                    $this->get('session')->getFlashBag()->add('message_error', $message);
                }
            }
        }
        return $this->render('mycpBundle:user:restorePasswordUser.html.twig', array(
            'form' => $form->createView(),
            'errors' => $errors
        ));
    }

    public function changePasswordAction($string, Request $request) {
        $em = $this->getDoctrine()->getManager();
        $errors = array();
        $form = $this->createForm(new changePasswordUserType($this->get('translator')));
        if ($request->getMethod() == 'POST') {
            $post = $request->get('mycp_frontendbundle_change_password_usertype');
            $form->handleRequest($request);
            if ($form->isValid()) {
                $service_security = $this->get('Secure');
                $decode_string = $service_security->decodeString($string);
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
                        $service_email->sendTemplatedEmail(
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

}
