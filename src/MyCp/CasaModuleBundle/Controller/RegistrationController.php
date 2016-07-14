<?php
/**
 * Created by PhpStorm.
 * User: neti
 * Date: 04/05/2016
 * Time: 12:01
 */

namespace MyCp\CasaModuleBundle\Controller;


use MyCp\FrontEndBundle\Form\enableUserCasaType;
use MyCp\mycpBundle\Entity\log;
use MyCp\mycpBundle\Entity\owner;
use MyCp\mycpBundle\Entity\ownerAccommodation;
use MyCp\mycpBundle\Entity\ownership;
use MyCp\mycpBundle\Entity\ownershipStatus;
use MyCp\mycpBundle\Entity\user;
use MyCp\mycpBundle\Helpers\BackendModuleName;
use MyCp\mycpBundle\Helpers\DataBaseTables;
use MyCp\mycpBundle\Helpers\UserMails;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;

class RegistrationController extends Controller
{

    public function registerAction(Request $request){
        $errors = array();
        $data = array();
        $data['country_code'] = '';
        $data['municipality_code'] = '';
        $data['count_errors'] = 0;
        $data['status_id'] = 0;
        $data['ownership_mcp_code'] = '(Automático)';
        $data['ownership_owner'] = "no_photo.gif";
        $data['id_ownership'] = "-1";
    if($request->isMethod('post')){
        $not_blank_validator = new NotBlank();
        $not_blank_validator->message = "Este campo no puede estar vacío.";
        $emailConstraint = new Email();
        $emailConstraint->message = 'El email no es válido.';
        $em=$this->getDoctrine()->getManager();
        $similar_names = $em->getRepository('mycpBundle:ownership')->findBy(array('own_name' => $request->get('own_name')));
        if (count($similar_names) > 0) {
            $errors['own_name'] = 'Ya existe una propiedad con este nombre.';
            $data["count_errors"] += 1;
        }
        if($this->get('validator')->validateValue($request->get('own_email_1'), $emailConstraint)){

        }
        else{
            $errors['own_email_1'] = $emailConstraint->message;
            $data['count_errors']+=count($this->get('validator')->validateValue($request->get('own_email_1'), $emailConstraint));
        }
       //        $errors['own_email_1'] = $this->get('validator')->validateValue($request->get('own_email_1'), $emailConstraint);


        if ($request->get('own_email_1') != "" && !\MyCp\FrontEndBundle\Helpers\Utils::validateEmail($request->get('own_email_1'))) {
            $errors['own_email_1'] = $emailConstraint->message;
            $data['count_errors']++;
        }
        $data['country_code'] = $request->get('own_province');
        $data['municipality_code'] = $request->get('own_municipality');
//        $ownership = $em->getRepository('mycpBundle:ownership')->insert($post, $request, $dir, $factory, (isset($post['user_create']) && !empty($post['user_create'])), (isset($post['user_send_mail']) && !empty($post['user_send_mail'])), $this,$translator, $this->container);
//        $current_ownership_id = $ownership->getOwnId();
        $province=$em->getRepository('mycpBundle:province')->find($request->get('own_province'));
        $municipality=$em->getRepository('mycpBundle:municipality')->find($request->get('own_municipality'));
       if($data["count_errors"]==0){
        $ownership=new ownership();
        $ownership->setOwnName(trim($request->get('own_name')))
            ->setOwnLicenceNumber(trim($request->get('own_license_number')))
            ->setOwnAddressProvince($province)
            ->setOwnPhoneNumber(trim($request->get('own_phone')))
            ->setOwnPhoneCode($province->getProvPhoneCode())
            ->setOwnAddressMunicipality($municipality)
            ->setOwnHomeowner1($request->get('own_homeowner_1'))
            ->setOwnEmail1($request->get('own_email_1'));
        $status = $em->getRepository('mycpBundle:ownershipStatus')->find(ownershipStatus::STATUS_INSERTED_BY_OWNER);
        $ownership->setOwnStatus($status);
        $ownership->setOwnCommentsTotal(0)
            ->setOwnMaximumNumberGuests(0)
            ->setOwnRating(0)
            ->setOwnMaximumPrice(0)
            ->setOwnMinimumPrice(0)
            ->setOwnRoomsTotal(0)
           ->setOwnFacilitiesBreakfast(0)
           ->setOwnFacilitiesDinner(0)
           ->setOwnFacilitiesParking(0)
           ->setOwnFacilitiesNotes(0)
            ->setOwnDescriptionBicycleParking(0)
            ->setOwnDescriptionPets(0)
            ->setOwnDescriptionLaundry(0)
            ->setOwnDescriptionInternet(0)
            ->setOwnTop20(0)
            ->setOwnSelection(0)
            ->setOwnInmediateBooking(0)
            ->setOwnNotRecommendable(0)
            ->setOwnCubaCoupon(0)
            ->setOwnSmsNotifications(0);
        ;

        $em->persist($ownership);
        $dir = $this->container->getParameter('user.dir.photos');
        $factory = $this->get('security.encoder_factory');
        $userCasa=$em->getRepository('mycpBundle:userCasa')->createUserNew($ownership, null, $factory, true, $this, $this->get('service_container'));

//        UserMails::sendOwnersMail($this, $request->get('own_email_1'), null, $request->get('own_homeowner_1'), null, $request->get('own_name'), $ownership->getOwnMcpCode());

        $message = 'La propiedad '.$ownership->getOwnMcpCode().' ha sido añadida satisfactoriamente.';

           //Create new owner
           $owner = new owner();
           $owner->setEmail($request->get('own_email_1'))
               ->setFullName($request->get('own_homeowner_1'))
               ->setPhone(trim($request->get('own_phone')))
               ->setProvince($province)
               ->setMunicipality($municipality)
               ->setMain(true)
           ;

           $em->persist($owner);

           $ownerAccommodation = new ownerAccommodation();
           $ownerAccommodation->setAccommodation($ownership)
               ->setOwner($owner);

           $em->persist($ownerAccommodation);

        $em->flush();
           $data['id_ownership']=$ownership->getOwnId();
           $data['ownership_mcp_code']=$ownership->getOwnMcpCode();
           $data['status_id']=$ownership->getOwnStatus()->getStatusId();
           return $this->render('MyCpCasaModuleBundle:Registration:success.html.twig',
               array());
       }
        else
            return $this->render('MyCpCasaModuleBundle:Registration:register.html.twig',
                array(
                    'data'=>$data,
                    'errors'=>$errors,
    ));
    }
    return $this->render('MyCpCasaModuleBundle:Registration:register.html.twig',
        array());
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
            throw new \Exception("Wrong url arguments");
    }
}