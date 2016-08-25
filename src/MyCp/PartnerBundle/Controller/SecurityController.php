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