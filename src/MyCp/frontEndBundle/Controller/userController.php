<?php

namespace MyCp\frontEndBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use MyCp\frontEndBundle\Form\registerUserType;
use MyCp\frontEndBundle\Form\restorePasswordUserType;
use MyCp\frontEndBundle\Form\changePasswordUserType;
use MyCp\mycpBundle\Entity\userTourist;
use MyCp\mycpBundle\Entity\user;

class userController extends Controller {

    public function registerAction(Request $request)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $errors=array();
        $all_post=array();

        $form=$this->createForm(new registerUserType());
        if($request->getMethod()=='POST')
        {
            $post=$request->get('mycp_frontendbundle_register_usertype');
            $all_post = $request->request->getIterator()->getArrayCopy();
            $form->bindRequest($request);
            $user_db=$em->getRepository('mycpBundle:user')->findBy(array('user_email'=>$post['user_email']));
            if($user_db)
            {
                $errors['used_email']='El email está siendo utilizado por otro usuario.';
            }
            if($form->isValid() && !$user_db)
            {
                $factory = $this->get('security.encoder_factory');
                $user2 = new user();
                $encoder = $factory->getEncoder($user2);
                $user_db=$em->getRepository('mycpBundle:user')->frontend_register_user($post,$request,$factory,$encoder);
                $service_security= $this->get('Secure');
                $encode_string=$service_security->encode_string($user_db->getUserEmail().'///'.$user_db->getUserId());

                //mailing
                $enableRoute = 'frontend_enable_user';
                $enableUrl = $this->get('router')
                    ->generate($enableRoute, array('string'=>$encode_string), true);

                $this->send_email(
                    'Activación de su cuenta en MyCasaParticular',
                    'noreply@mycasaparticular.com',
                    $user_db->getUserEmail(),
                    'Gracias por registrarse en MyCasaParticular.com',
                    'Visite el siguiente link para activar su cuenta. '.$enableUrl,'');

                //var_dump($encode_string); exit();
                $message='Grácias por registrarse. Se ha enviado un email para que active su cuenta.';
                $this->get('session')->setFlash('message_global_success',$message);
                return $this->redirect($this->generateUrl('frontend_login'));
            }
        }

        return $this->render('frontEndBundle:user:registerUser.html.twig',array(
            'form'=>$form->createView(),
            'errors'=>$errors,
            'post'=>$all_post
        ));
    }

    public function enableAction($string)
    {
        $service_security= $this->get('Secure');
        $decode_string=$service_security->decode_string($string);
        $user_atrib=explode('///',$decode_string);

        $em = $this->getDoctrine()->getEntityManager();
        if(isset($user_atrib[1]))
        {
            $user=$em->getRepository('mycpBundle:user')->findBy(array('user_id'=>$user_atrib[1],'user_email'=>$user_atrib[0]));
            if($user)
            {
                $user=$user[0];
                $user->setUserEnabled(1);
                $em->persist($user);
                $em->flush();
                $message='Grácias. Su usuario ha sido activado satisfactoriamente.';
                $this->get('session')->setFlash('message_global_success',$message);
                return $this->redirect($this->generateUrl('frontend_login'));
            }
        }
        else
        {
            throw $this->createNotFoundException(
                'Imposible activar usuario, los parámetros no son correctos.'
            );
        }
    }

    public function restore_passwordAction(Request $request)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $currency_list = $em->getRepository('mycpBundle:currency')->findAll();
        $languages_list = $em->getRepository('mycpBundle:lang')->get_active_languages();
        $errors=array();
        $form=$this->createForm(new restorePasswordUserType());
        if($request->getMethod()=='POST')
        {
            $post=$request->get('mycp_frontendbundle_restore_password_usertype');
            $form->bindRequest($request);
            if($form->isValid())
            {
                $user_db=$em->getRepository('mycpBundle:user')->findBy(array('user_email'=>$post['user_email']));
                if($user_db)
                {
                    $user_db=$user_db[0];
                    $service_security= $this->get('Secure');
                    $encode_string=$service_security->encode_string($user_db->getUserEmail().'///'.$user_db->getUserId());

                    $change_passwordRoute = 'frontend_change_password_user';
                    $changeUrl = $this->get('router')
                        ->generate($change_passwordRoute, array('string'=>$encode_string), true);
                    //mailing
                    $this->send_email(
                        'Restauración de su cuenta en MyCasaParticular',
                        'noreply@mycasaparticular.com',
                        $user_db->getUserEmail(),
                        'Recupere su contraseña en MyCasaParticular.com',
                        'Visite el siguiente link para cambiar su contraseña. '.$changeUrl,'');

                    //var_dump($encode_string); exit();
                    $message='Se ha enviado un email para que recupere su contraseña.';
                    $this->get('session')->setFlash('message_global_success',$message);
                    return $this->redirect($this->generateUrl('frontend_login'));

                }
                else
                {
                    $errors['no_email']='No existe ningún usuario con ese email.';
                }
                /*$service_security= $this->get('Secure');
                $encode_string=$service_security->encode_string($user->getUserEmail().'///'.$user->getUserId());
                echo '<url>/user_enable/'.$encode_string;*/
            }

        }

        return $this->render('frontEndBundle:user:restorePasswordUser.html.twig',array(
            'form'=>$form->createView(),
            'errors'=>$errors
        ));

    }

    public function change_passwordAction($string,Request $request)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $currency_list = $em->getRepository('mycpBundle:currency')->findAll();
        $languages_list = $em->getRepository('mycpBundle:lang')->get_active_languages();
        $errors=array();
        $form=$this->createForm(new changePasswordUserType());
        if($request->getMethod()=='POST')
        {
            $post=$request->get('mycp_frontendbundle_change_password_usertype');
            $form->bindRequest($request);
            if($form->isValid())
            {
                $service_security= $this->get('Secure');
                $decode_string=$service_security->decode_string($string);
                $user_atrib=explode('///',$decode_string);
                if(isset($user_atrib[1]))
                {
                    $user=$em->getRepository('mycpBundle:user')->findBy(array('user_id'=>$user_atrib[1],'user_email'=>$user_atrib[0]));
                    if($user)
                    {
                        $user=$user[0];
                        $factory = $this->get('security.encoder_factory');
                        $user2 = new user();
                        $encoder = $factory->getEncoder($user2);
                        $password = $encoder->encodePassword($post['user_password']['Clave:'], $user->getSalt());

                        $user->setUserPassword($password);
                        $em->persist($user);
                        $em->flush();
                        $message='Su contraseña ha sido cambiada satisfactoriamente.';
                        $this->get('session')->setFlash('message_global_success',$message);
                        return $this->redirect($this->generateUrl('frontend_login'));
                    }
                }
                else
                {
                    throw $this->createNotFoundException(
                        'Imposible cambiar contraseña, los parámetros no son correctos.'
                    );
                }

            }

        }

        return $this->render('frontEndBundle:user:changePasswordUser.html.twig',array(
            'string'=>$string,
            'form'=>$form->createView(),
            'errors'=>$errors
        ));
    }

    public function register_user_confirmationAction(Request $request)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $errors=array();

        $form=$this->createForm(new restorePasswordUserType());
        if($request->getMethod()=='POST')
        {
            $post=$request->get('mycp_frontendbundle_restore_password_usertype');
            $form->bindRequest($request);
            if($form->isValid())
            {

                $user_db=$em->getRepository('mycpBundle:user')->findBy(array('user_email'=>$post['user_email']));
                if($user_db)
                {
                    $user_db=$user_db[0];
                    $service_security= $this->get('Secure');
                    $encode_string=$service_security->encode_string($user_db->getUserEmail().'///'.$user_db->getUserId());
                    $enableRoute = 'frontend_enable_user';
                    $enableUrl = $this->get('router')
                        ->generate($enableRoute, array('string'=>$encode_string), true);

                    $this->send_email(
                        'Activación de su cuenta en MyCasaParticular',
                        'noreply@mycasaparticular.com',
                        $user_db->getUserEmail(),
                        'Gracias por registrarse en MyCasaParticular.com',
                        'Visite el siguiente link para activar su cuenta. '.$enableUrl,'');

                    $message='Grácias por registrarse. Se ha enviado un email para que active su cuenta.';
                    $this->get('session')->setFlash('message_global_success',$message);
                    return $this->redirect($this->generateUrl('frontend_login'));
                }
                else
                {
                    $errors['no_email']='No existe ningún usuario con ese email.';
                }

            }

        }

        return $this->render('frontEndBundle:user:registerConfirmationUser.html.twig',array(
            'form'=>$form->createView(),
            'errors'=>$errors
        ));
    }

    public function send_email($subject,$from,$to,$title,$body)
    {
        $message = \Swift_Message::newInstance()
            ->setSubject($subject)
            ->setFrom($from)
            ->setTo($to)
            ->setBody($this->renderView('frontEndBundle:utils:mailTemplate.html.twig',array('title' =>$title,'content' =>$body)), 'text/html'
        )
        ;
        $this->get('mailer')->send($message);
    }

}
