<?php
/**
 * Created by PhpStorm.
 * User: Karel
 * Date: 11/5/14
 * Time: 12:18 PM
 */

namespace MyCp\FrontEndBundle\Controller;

use MyCp\FrontEndBundle\Form\FacebookLoginType;
use MyCp\FrontEndBundle\Form\Model\FacebookLogin;
use MyCp\mycpBundle\Entity\currency;
use MyCp\mycpBundle\Entity\user;
use MyCp\mycpBundle\Entity\userTourist;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class OAuthController extends Controller
{
    public function facebookLoginAction(Request $request)
    {
        if ($request->getMethod() === "GET") {
            $fbLoginForm = $this->createForm(
                new FacebookLoginType(),
                new FacebookLogin(),
                array('action' => $this->generateUrl('facebook_login'))
            );

            return $this->render(
                'FrontEndBundle:OAuth:facebookLogin.html.twig',
                array('fbLoginForm' => $fbLoginForm->createView())
            );
        } else {
            if ($request->getMethod() === "POST") {

                $form = $this->createForm(new FacebookLoginType(), new FacebookLogin());
                $form->handleRequest($request);

                if ($form->isValid()) {
                    $em = $this->getDoctrine()->getManager();
                    $userRepository = $em->getRepository("mycpBundle:user");

                    $fbLoginData = $form->getData();
                    $user = $userRepository->findOneBy(array('user_email' => $fbLoginData->getEmail()));

                    if ($user == null) {

                        $user = new user();
                        $role = $em->getRepository('mycpBundle:role')->findBy(array('role_name' => 'ROLE_CLIENT_TOURIST'));

                        //If first-time-user using facebook, we should add him to db
                        $user->setUserName($fbLoginData->getEmail())
                            ->setUserEmail(strtolower($fbLoginData->getEmail()))
                            ->setUserUserName($fbLoginData->getName())
                            ->setUserLastName($fbLoginData->getLastName())
                            ->setUserRole("ROLE_CLIENT_TOURIST")
                            ->setUserAddress('')
                            ->setUserCity('')
                            ->setUserPhone('')
                            ->setUserPassword("")
                            ->setUserEnabled(true) //enable directly because this is a confirmed user email from facebook.
                            ->setUserCreatedByMigration(false)
                            ->setUserSubrole($role[0]);

                        $userTourist = new userTourist();

                        //default currency for the user
                        $currency = $em->getRepository('mycpBundle:currency')->findOneBy(array('curr_default' => true));

                        //default language for the user
//                        $languageCode = $request->attributes->get('app_lang_code');
//                        $languageCode = empty($languageCode) ? $request->attributes->get('_locale') : $request->getSession()->get('_locale', 'en');
                        $languageCode =$fbLoginData->getLanguage()?$fbLoginData->getLanguage(): $request->attributes->get('app_lang_code');
                        $languageCode = strtoupper($languageCode);
                        $language = $em->getRepository('mycpBundle:lang')->findOneBy(array('lang_code' => $languageCode));

                        //we can get the gender of the user from $fbLoginData->getGender() as male or female
                        $gender = $fbLoginData->getGender() == "male" ? 0 : 1;

                        //default country for the user
                        //$country = $em->getRepository('mycpBundle:country')->find('USA');
                        $user->setUserCountry($fbLoginData->getCountry());
                        $userTourist->setUserTouristCurrency($currency);
                        $userTourist->setUserTouristLanguage($language);
                        $userTourist->setUserTouristUser($user);
                        $userTourist->setUserTouristGender($gender);

                        $em->persist($user);
                        $em->persist($userTourist);
                        $em->flush();
                    }

                    //authenticate the user
                    $providerKey = 'user'; //the name of the firewall
                    $token = new UsernamePasswordToken($user, null, $providerKey, $user->getRoles());
                    $this->get("security.context")->setToken($token);
                    $this->get('session')->set('_security_user', serialize($token));
                }
            }
        }
        return $this->redirect($this->generateUrl("frontend_welcome"));
    }

    public function checkEmailAction(Request $request){
        $email=$request->get('email');
        $em=$this->getDoctrine()->getManager();
        $userRepository = $em->getRepository("mycpBundle:user");
        $user = $userRepository->findOneBy(array('user_email' => $email));
        $response=array();
        $response['exists']=($user!=null)?true:false;
        return new JsonResponse($response);
    }
}
