<?php
namespace MyCp\PartnerBundle\Controller;

use MyCp\FrontEndBundle\Form\changePasswordUserType;
use MyCp\mycpBundle\Entity\user;
use MyCp\mycpBundle\Form\restorePasswordUserType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\SecurityContext;

class SecurityController extends Controller
{
    /**
     * @param $token
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function activateAccountAction($token, Request $request) {
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('mycpBundle:user')->find($token);
        $user->setUserEnabled(1);
        $em->persist($user);
        $em->flush();
        return $this->render('PartnerBundle:Layout:activateAccount.html.twig', array(
            'user' => $user
        ));
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

    public function restorePasswordAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $errors = array();
        $form = $this->createForm(new \MyCp\FrontEndBundle\Form\restorePasswordUserType($this->get('translator')));
        if ($request->getMethod() == 'POST') {
            $post = $request->get('mycp_frontendbundle_restore_password_usertype');
            $form->handleRequest($request);
            if ($form->isValid()) {
                $user_db = $em->getRepository('mycpBundle:user')->findOneBy(array('user_email' => $post['user_email']));

                if ($user_db) {
                    $service_security = $this->get('Secure');
                    $encode_string = $service_security->getEncodedUserString($user_db);
                    $newPassword = $this->generateStrongPassword();
                    $encode_token = $service_security->encodeString($newPassword);

                    $change_passwordRoute = 'frontend_partner_change_password_user';
                    $changeUrl = $this->get('router')
                        ->generate($change_passwordRoute, array('string' => $encode_string, 'token' => $encode_token), true);
                    //mailing
                    $body = $this->render('@Partner/Mail/restorePassword.html.twig', array('changeUrl' => $changeUrl, 'newPassword' => $newPassword));

                    $service_email = $this->get('Email');
                    $service_email->sendTemplatedEmail(
                        $this->get('translator')->trans('EMAIL_RESTORE_ACCOUNT'), 'noreply@mycasaparticular.com', $user_db->getUserEmail(), $body->getContent());
                    $message = $this->get('translator')->trans("USER_PASSWORD_RECOVERY");
                    $this->get('session')->getFlashBag()->add('message_global_success', $message);
                    return $this->redirect($this->generateUrl('frontend_partner_home'));
                } else {
                    $errors['no_email'] = $this->get('translator')->trans("USER_PASSWORD_RECOVERY_ERROR");
                }
                /* $service_security= $this->get('Secure');
                  $encode_string=$service_security->getEncodedUserString($user);
                  echo '<url>/user_enable/'.$encode_string; */
            }
        }

        return $this->render('LayoutBundle:Security:forgotPassword.html.twig', array(
            'form' => $form->createView(),
            'errors' => $errors,
            'btn_close' => false
        ));
    }

//    public function changePasswordAction($string,$token, Request $request) {
//        $em = $this->getDoctrine()->getManager();
//        $errors = array();
//        $form = $this->createForm(new changePasswordUserType($this->get('translator')));
//        if ($request->getMethod() == 'POST') {
//            $post = $request->get('mycp_frontendbundle_change_password_usertype');
//            $form->handleRequest($request);
//            if ($form->isValid()) {
//                $service_security = $this->get('Secure');
//                $decode_string = $service_security->decodeString($string);
//                $decode_token = $service_security->decodeString($token);
//                dump($decode_token);die;
//                $user_atrib = explode('///', $decode_string);
//                if (isset($user_atrib[1])) {
//                    $user = $em->getRepository('mycpBundle:user')->findOneBy(array('user_id' => $user_atrib[1], 'user_email' => $user_atrib[0]));
//                    if ($user) {
//                        $factory = $this->get('security.encoder_factory');
//                        $user2 = new user();
//                        $encoder = $factory->getEncoder($user2);
//                        if (isset($post['user_password']['Clave']))
//                            $password = $encoder->encodePassword($post['user_password']['Clave'], $user->getSalt());
//                        else
//                            $password = $encoder->encodePassword($post['user_password']['Password'], $user->getSalt());
//                        $user->setUserPassword($password);
//                        $em->persist($user);
//                        $em->flush();
//
//                        $message = $this->get('translator')->trans('EMAIL_PASS_CHANGED');
//                        //mailing
//                        $service_email = $this->get('Email');
//                        $service_email->sendTemplatedEmail(
//                            $message, 'noreply@mycasaparticular.com', $user->getUserEmail(), $message);
//
//                        $this->get('session')->getFlashBag()->add('message_global_success', $message);
////                        return $this->redirect($this->generateUrl('frontend_login'));
//                        //authenticate the user
//                        $providerKey = 'user'; //the name of the firewall
//                        $token = new UsernamePasswordToken($user, $password, $providerKey, $user->getRoles());
//                        $this->get("security.context")->setToken($token);
//                        $this->get('session')->set('_security_user', serialize($token));
//                        return $this->redirect($this->generateUrl("frontend_partner_home"));
//                    }
//                } else {
//                    throw $this->createNotFoundException($this->get('translator')->trans("USER_PASSWORD_CHANGE_ERROR"));
//                }
//            }
//        }
//        //var_dump($form->createView()->getChildren());
//        //exit();
//        return $this->render('LayoutBundle:Security:changePasswordUser.html.twig', array(
//            'string' => $string,
//            'form' => $form->createView(),
//            'errors' => $errors
//        ));
//    }

    public function changePasswordAction($string,$token, Request $request) {
        $em = $this->getDoctrine()->getManager();
        $service_security = $this->get('Secure');
        $decode_string = $service_security->decodeString($string);
        $decode_token = $service_security->decodeString($token);
        $user_atrib = explode('///', $decode_string);

        if (isset($user_atrib[1])) {
            $user = $em->getRepository('mycpBundle:user')->findOneBy(array('user_id' => $user_atrib[1], 'user_email' => $user_atrib[0]));
            if ($user) {
                $factory = $this->get('security.encoder_factory');
                $user2 = new user();
                $encoder = $factory->getEncoder($user2);
                $password = $encoder->encodePassword($decode_token, $user->getSalt());
                $user->setUserPassword($password);
                $em->persist($user);
                $em->flush();

                $message = $this->get('translator')->trans('EMAIL_PASS_CHANGED');
                //mailing
                $service_email = $this->get('Email');
                $service_email->sendTemplatedEmail(
                    $message, 'noreply@mycasaparticular.com', $user->getUserEmail(), $message);

                $this->get('session')->getFlashBag()->add('message_global_success', $message);
//                        return $this->redirect($this->generateUrl('frontend_login'));
                //authenticate the user
                $providerKey = 'user'; //the name of the firewall
                $token = new UsernamePasswordToken($user, $password, $providerKey, $user->getRoles());
                $this->get("security.context")->setToken($token);
                $this->get('session')->set('_security_user', serialize($token));
                return $this->redirect($this->generateUrl("backend_partner_dashboard"));
            }
        } else {
            throw $this->createNotFoundException($this->get('translator')->trans("USER_PASSWORD_CHANGE_ERROR"));
        }
    }

    public function generateStrongPassword($length = 9, $add_dashes = false, $available_sets = 'luds')
    {
        $sets = array();
        if(strpos($available_sets, 'l') !== false)
            $sets[] = 'abcdefghjkmnpqrstuvwxyz';
        if(strpos($available_sets, 'u') !== false)
            $sets[] = 'ABCDEFGHJKMNPQRSTUVWXYZ';
        if(strpos($available_sets, 'd') !== false)
            $sets[] = '23456789';

        $all = '';
        $password = '';
        foreach($sets as $set)
        {
            $password .= $set[array_rand(str_split($set))];
            $all .= $set;
        }
        $all = str_split($all);
        for($i = 0; $i < $length - count($sets); $i++)
            $password .= $all[array_rand($all)];
        $password = str_shuffle($password);
        if(!$add_dashes)
            return $password;
        $dash_len = floor(sqrt($length));
        $dash_str = '';
        while(strlen($password) > $dash_len)
        {
            $dash_str .= substr($password, 0, $dash_len) . '-';
            $password = substr($password, $dash_len);
        }
        $dash_str .= $password;
        return $dash_str;
    }
}