<?php

namespace MyCp\FrontEndBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use MyCp\FrontEndBundle\Form\enableUserCasaType;
use MyCp\mycpBundle\Entity\user;

class UserCasaController extends Controller {

    public function homeAction() {

        return $this->render('FrontEndBundle:userCasa:home.html.twig');
    }

    public function activateAccountAction($token, Request $request) {
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
                }*/
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
                    $this->get('session')->getFlashBag()->add('message_global_success', $message);
                    return $this->redirect($this->generateUrl('lodging_login'));
                }
            }

            return $this->render('FrontEndBundle:userCasa:activateAccount.html.twig', array(
                        'user' => $userCasa,
                        'form' => $form->createView(),
                        'secret_token' => $token
            ));
        }
        else
            throw new \Exception("Wrong url arguments");
    }

}
