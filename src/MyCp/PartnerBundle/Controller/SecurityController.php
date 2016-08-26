<?php
namespace MyCp\PartnerBundle\Controller;

use MyCp\FrontEndBundle\Form\changePasswordUserType;
use MyCp\mycpBundle\Entity\user;
use MyCp\mycpBundle\Form\restorePasswordUserType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\SecurityContext;

class SecurityController extends Controller
{
    /**
     * @param $token
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function activateAccountAction($token, Request $request) {/*
        $em = $this->getDoctrine()->getManager();
        $userCasa = null;
        $all_post = array();
        $count_error = 0;

        $complete_user = array(
            'user_user_name' => "",
            'user_last_name' => "",
            'user_email' => "",
            'user_phone' => "",
            'user_address' => "",
            'user_password' => "");

        if (isset($token) && $token != null && $token != "" && $token != "0") {
            $userCasa = $em->getRepository('mycpBundle:userCasa')->getOneByToken($token);

            if ($userCasa) {
                $complete_user = array(
                    'user_user_name' => $userCasa->getUserCasaUser()->getUserUserName(),
                    'user_last_name' => $userCasa->getUserCasaUser()->getUserLastName(),
                    'user_email' => $userCasa->getUserCasaUser()->getUserEmail(),
                    'user_phone' => $userCasa->getUserCasaUser()->getUserPhone(),
                    'user_address' => $userCasa->getUserCasaUser()->getUserAddress(),
                    'user_password' => "");
            }
            else
                throw new \Exception("No existe un usuario asociado");

            $form = $this->createForm(new enableUserCasaType($this->get('translator')), $complete_user);

            if ($request->getMethod() == 'POST') {
                $post = $request->get('mycp_frontendbundle_profile_usertype');
                $post = $request->request->getIterator()->getArrayCopy();
                $post = $post['mycp_frontendbundle_enabled_user_casatype'];
                $form->handleRequest($request);

                if (isset($post['user_email']) && !\MyCp\FrontEndBundle\Helpers\Utils::validateEmail($post['user_email'])) {
                    $message = $this->get('translator')->trans("EMAIL_INVALID_MESSAGE");
                    $this->get('session')->getFlashBag()->add('message_global_success', $message);
                    $count_error++;
                }

                /*if (isset($post['user_email'])) {

                    $user_db = $em->getRepository('mycpBundle:user')->findBy(array(
                        'user_email' => $post['user_email'],
                        'user_created_by_migration' => false));
                    if ($user_db) {
                        $message = $this->get('translator')->trans("USER_EMAIL_IN_USE");
                        $this->get('session')->getFlashBag()->add('message_global_success', $message);
                        $count_error++;
                    }
                }

                if ($userCasa->getUserCasaSecretToken() != $post['user_secret_token']) {
                    $message = $this->get('translator')->trans("INVALID_SECRET_TOKEN");
                    $this->get('session')->getFlashBag()->add('message_global_success', $message);
                    $count_error++;
                }
                if ($form->isValid() && $count_error == 0) {
                    $user = $userCasa->getUserCasaUser();
                    $user->setUserUserName($post['user_user_name']);
                    $user->setUserLastName($post['user_last_name']);
                    $user->setUserEmail($post['user_email']);
                    $user->setUserPhone($post['user_phone']);
                    $user->setUserAddress($post['user_address']);
                    $user->setUserEnabled(1);
                    $user->setUserActivationDate(new \DateTime());
                    $factory = $this->get('security.encoder_factory');
                    $user2 = new user();
                    $encoder = $factory->getEncoder($user2);

                    $keyPassword = $this->get('translator')->trans("FORMS_PASSWORD");
                    $password = $encoder->encodePassword($post['user_password'][$keyPassword], $user->getSalt());
                    $user->setUserPassword($password);
                    $em->persist($user);
                    $em->flush();

                    $message = $this->get('translator')->trans("USER_ACTIVATE_ACCOUNT_SUCCESS");
                    $this->get('session')->getFlashBag()->add('message_ok', $message);
                    return $this->redirect($this->generateUrl('my_cp_casa_login'));
                }
            }

            return $this->render('MyCpCasaModuleBundle:Registration:activateAccount.html.twig', array(
                'user' => $userCasa,
                'form' => $form->createView(),
                'secret_token' => $token
            ));
        }
        else
            throw new \Exception("Wrong url arguments");*/
    }
    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function loginAction(Request $request)
    {
        $session = $request->getSession();

        // get the login error if there is one
        if ($request->attributes->has(SecurityContext::AUTHENTICATION_ERROR)) {
            $error = $request->attributes->get(
                SecurityContext::AUTHENTICATION_ERROR
            );
        } else {
            $error = $session->get(SecurityContext::AUTHENTICATION_ERROR);
            $session->remove(SecurityContext::AUTHENTICATION_ERROR);
        }

        return $this->render(
            'LayoutBundle:Security:login.html.twig',
            array(
                // last username entered by the user
                'last_username' => $session->get(SecurityContext::LAST_USERNAME),
                'error'         => $error,
                'btn_close'=>false
            )
        );
    }

   /* public function forgotPasswordAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $errors = array();
        $form = $this->createForm(new restorePasswordUserType());
        if ($request->getMethod() == 'POST') {
            $post = $request->get('mycp_mycpbundle_restore_password_usertype');
            $form->handleRequest($request);
            if ($form->isValid()) {

                $user_db = $em->getRepository('mycpBundle:user')->getUserBackendByEmailAndUserName($post['user_email'], $post['user_name']);
                if ($user_db) {
                    $service_security = $this->get('Secure');
                    $encode_string = $service_security->getEncodedUserString($user_db);

                    $change_passwordRoute = 'my_cp_change_password_user';
                    $changeUrl = $this->get('router')
                        ->generate($change_passwordRoute, array('string' => $encode_string), true);
                    //mailing
                    $body = $this->render('mycpBundle:mail:restorePassword.html.twig', array('changeUrl' => $changeUrl));

                    $service_email = $this->get('Email');
                    $service_email->sendEmail("Restauración de su cuenta en MyCasaParticular", 'no_reply@mycasaparticular.com', 'MyCasaParticular.com', $user_db->getUserEmail(), $body->getContent());
                    $message = "Se ha enviado un correo para que recupere su contraseña.";
                    $this->get('session')->getFlashBag()->add('message_ok', $message);
                    return $this->redirect($this->generateUrl('my_cp_casa_login'));
                } else {
                    $message = "No existe ningún usuario con ese correo con acceso a esta sección.";
                    $this->get('session')->getFlashBag()->add('message_error', $message);
                }
            }
        }
        return $this->render('MyCpCasaModuleBundle:Security:forgot_password.html.twig', array(
            'form' => $form->createView(),
            'errors' => $errors
        ));
    }

    public function changePasswordAction($string, Request $request) {
        $em = $this->getDoctrine()->getManager();
        $errors = array();
        $form = $this->createForm(new \MyCp\mycpBundle\Form\changePasswordUserType());
        if ($request->getMethod() == 'POST') {
            $post = $request->get('mycp_mycpbundle_change_password_usertype');
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

                        $message = "Su contraseña ha sido cambiada satisfactoriamente.";
                        //mailing
                        $service_email = $this->get('Email');
                        $service_email->sendEmail($message, 'no_reply@mycasaparticular.com', 'MyCasaParticular.com', $user->getUserEmail(), $message);

                        $this->get('session')->getFlashBag()->add('message_ok', $message);
                        return $this->redirect($this->generateUrl('my_cp_casa_login'));
                    }
                } else {
                    $message = "Imposible cambiar contraseña, los parámetros no son correctos.";
                    $this->get('session')->getFlashBag()->add('message_error', $message);
                }
            }
        }

        return $this->render('MyCpCasaModuleBundle:Security:reset_password.html.twig', array(
            'string' => $string,
            'form' => $form->createView(),
            'errors' => $errors
        ));
    }*/
}