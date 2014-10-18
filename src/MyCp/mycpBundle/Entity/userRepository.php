<?php

namespace MyCp\mycpBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * userRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class userRepository extends EntityRepository {

    /*function new_user_casa($id_role, $data, $request, $dir, $factory) {
        $em = $this->getEntityManager();
        $user = new user();
        $address = $data['mycp_mycpbundle_client_casatype']['address'];
        $ownership = new ownership();
        $ownership = $em->getRepository('mycpBundle:ownership')->find($data['mycp_mycpbundle_client_casatype']['ownership']);
        $address = $data['mycp_mycpbundle_client_casatype']['address'];
        $email = $data['mycp_mycpbundle_client_casatype']['email'];
        $country = $em->getRepository('mycpBundle:country')->findBy(array('co_name' => 'Cuba'));
        $user->setUserAddress($address);
        $user->setUserCity($ownership->getOwnAddressMunicipality()->getMunName());
        $user->setUserCountry($country[0]);
        $user->setUserEmail($email);
        $user->setUserCreatedByMigration(false);
        $user->setUserPhone($ownership->getOwnPhoneCode() . ' ' . $ownership->getOwnPhoneNumber());
        $user->setUserName($data['mycp_mycpbundle_client_casatype']['user_name']);
        $user->setUserLastName($data['mycp_mycpbundle_client_casatype']['last_name']);
        //$user->setUserCreationDate(new \DateTime());
        $file = $request->files->get('mycp_mycpbundle_client_casatype');
        if (isset($file['photo'])) {
            $photo = new photo();
            $fileName = uniqid('user-') . '-photo.jpg';
            $file['photo']->move($dir, $fileName);
            //Redimensionando la foto del usuario
            \MyCp\mycpBundle\Helpers\Images::resize($dir . $fileName, 65);

            $photo->setPhoName($fileName);
            $user->setUserPhoto($photo);
            $em->persist($photo);
        }
        $role = $em->getRepository('mycpBundle:role')->find($id_role);
        $user->setUserRole('ROLE_CLIENT_CASA');
        $user->setUserSubrole($role);
        $user->setUserUserName($data['mycp_mycpbundle_client_casatype']['name']);
        $encoder = $factory->getEncoder($user);
        $password = $encoder->encodePassword($data['mycp_mycpbundle_client_casatype']['user_password']['Clave:'], $user->getSalt());
        $user->setUserPassword($password);
        $user_casa = new userCasa();
        $user_casa->setUserCasaOwnership($ownership);
        $user_casa->setUserCasaUser($user);
        $em->persist($user);
        $em->persist($user_casa);
        $em->flush();
    }
     */

    function short_edit_user($id_user, $request, $dir, $factory) {
        $post = $request->request->getIterator()->getArrayCopy();
        $em = $this->getEntityManager();
        $user = $em->getRepository('mycpBundle:user')->findOneBy(array('user_id' => $id_user));

        if ($post['user_password'] != '') {
            $encoder = $factory->getEncoder($user);
            $password = $encoder->encodePassword($post['user_password'], $user->getSalt());
            $user->setUserPassword($password);
        }
        $file = $request->files->get('user_photo');
        if (isset($file)) {
            $photo_user = $user->getUserPhoto();
            if ($photo_user != null) {
                $photo_old = $em->getRepository('mycpBundle:photo')->find($photo_user->getPhoId());
                if ($photo_old)
                    $em->remove($photo_old);
                @unlink($dir . $user->getUserPhoto()->getPhoName());
            }

            $photo = new photo();
            $fileName = uniqid('user-') . '-photo.jpg';
            $file->move($dir, $fileName);
            //Redimensionando la foto del usuario
            \MyCp\mycpBundle\Helpers\Images::resize($dir . $fileName, 65);

            $photo->setPhoName($fileName);
            $user->setUserPhoto($photo);
            $em->persist($photo);
        }
        $em->persist($user);
        $em->flush();
    }

    function registerUser($post, $request, $encoder, $translator, $languageCode, $currency) {
        $em = $this->getEntityManager();
        $language = $em->getRepository('mycpBundle:lang')->findOneBy(array('lang_code' => $languageCode));
        $country = $em->getRepository('mycpBundle:country')->find($post['user_country']);
        $role = $em->getRepository('mycpBundle:role')->findBy(array('role_name' => 'ROLE_CLIENT_TOURIST'));

        $user = $em->getRepository('mycpBundle:user')->findOneBy(array(
            'user_email' => $post['user_email'],
            'user_created_by_migration' => false));

        if($user == null)
            $user = new user();

        $user_tourist = new userTourist();

        $user->setUserAddress('');
        $user->setUserCity('');
        $user->setUserCountry($country);
        $user->setUserEmail($post['user_email']);
        $user->setUserLastName($post['user_last_name']);
        $user->setUserUserName($post['user_user_name']);
        $user->setUserPhone('');
        $user->setUserName($post['user_user_name']);
        $user->setUserCreatedByMigration(false);
        $user->setUserRole('ROLE_CLIENT_TOURIST');
        $user->setUserSubrole($role[0]);
        if ($request->get('user_newsletters'))
            $user->setUserNewsletters(1);
        $user->setUserEnabled(0);
        //$user->setUserCreationDate(new \DateTime());
        $password = $encoder->encodePassword($post['user_password'][$translator->trans("FORMS_PASSWORD")], $user->getSalt());

        $user->setUserPassword($password);
        $user_tourist->setUserTouristCurrency($currency);
        $user_tourist->setUserTouristLanguage($language);
        $user_tourist->setUserTouristUser($user);
        $em->persist($user);
        $em->persist($user_tourist);
        $em->flush();

        return $user;
    }

    function new_user_tourist($id_role, $request, $dir, $factory) {
        $em = $this->getEntityManager();
        $form_post = $request->get('mycp_mycpbundle_client_touristtype');

        $lang = $em->getRepository('mycpBundle:lang')->find($form_post['language']);
        $currency = $em->getRepository('mycpBundle:currency')->find($form_post['currency']);
        $country = $em->getRepository('mycpBundle:country')->find($form_post['country']);
        //var_dump($lang); exit();
        $user = new user();
        $user_tourist = new userTourist();

        $user->setUserAddress($form_post['address']);
        $user->setUserCity($form_post['city']);
        $user->setUserCountry($country);
        $user->setUserEmail($form_post['email']);
        $user->setUserPhone($form_post['phone']);
        $user->setUserName($form_post['user_name']);
        $user->setUserLastName($form_post['last_name']);
        $user->setUserCreatedByMigration(false);
        $role = $em->getRepository('mycpBundle:role')->find($id_role);
        $user->setUserRole('ROLE_CLIENT_TOURIST');
        $user->setUserSubrole($role);
        $user->setUserUserName($form_post['name']);
        $encoder = $factory->getEncoder($user);
        //$user->setUserCreationDate(new \DateTime());
        $password = $encoder->encodePassword($form_post['user_password']['Clave:'], $user->getSalt());
        $user->setUserPassword($password);
        $user_tourist->setUserTouristCurrency($currency);
        $user_tourist->setUserTouristLanguage($lang);
        $user_tourist->setUserTouristUser($user);

        $file = $request->files->get('mycp_mycpbundle_client_touristtype');
        if (isset($file['photo'])) {
            $photo = new photo();
            $fileName = uniqid('user-') . '-photo.jpg';
            $file['photo']->move($dir, $fileName);

            //Redimensionando la foto del usuario
            \MyCp\mycpBundle\Helpers\Images::resize($dir . $fileName, 65);

            $photo->setPhoName($fileName);
            $user->setUserPhoto($photo);
            $em->persist($photo);
        }
        $em->persist($user);
        $em->persist($user_tourist);
        $em->flush();
    }

    function edit_user_tourist($id_user, $request, $dir, $factory) {
        $post = $request->request->get('mycp_mycpbundle_client_touristtype');
        $em = $this->getEntityManager();
        $user_tourist = new userTourist();
        $user_tourist = $em->getRepository('mycpBundle:userTourist')->findOneBy(array('user_tourist_user' => $id_user));
        if($user_tourist == null)
        {
            $user_tourist =new userTourist();
            $user = $em->getRepository('mycpBundle:user')->findOneBy(array('user_id' => $id_user));
            $user_tourist->setUserTouristUser($user);
        }
        $user_tourist->getUserTouristUser()->setUserName($post['user_name']);
        $user_tourist->getUserTouristUser()->setUserAddress($post['address']);
        $user_tourist->getUserTouristUser()->setUserEmail($post['email']);
        $user_tourist->getUserTouristUser()->setUserUserName($post['name']);
        $user_tourist->getUserTouristUser()->setUserLastName($post['last_name']);
        $user_tourist->getUserTouristUser()->setUserPhone($post['phone']);
        $user_tourist->getUserTouristUser()->setUserCity($post['city']);
        $country = $em->getRepository('mycpBundle:country')->find($post['country']);
        $currency = $em->getRepository('mycpBundle:currency')->find($post['currency']);
        $language = $em->getRepository('mycpBundle:lang')->find($post['language']);
        $user_tourist->getUserTouristUser()->setUserCountry($country);
        $user_tourist->setUserTouristCurrency($currency);
        $user_tourist->setUserTouristLanguage($language);

        if ($post['user_password']['Clave:'] != '') {
            $encoder = $factory->getEncoder($user_tourist->getUserTouristUser());
            $password = $encoder->encodePassword($post['user_password']['Clave:'], $user_tourist->getUserTouristUser()->getSalt());
            $user_tourist->getUserTouristUser()->setUserPassword($password);
        }
        $file = $request->files->get('mycp_mycpbundle_client_touristtype');
        if (isset($file['photo'])) {
            $user_photo = $user_tourist->getUserTouristUser()->getUserPhoto();
            if ($user_photo != null) {
                $photo_old = $em->getRepository('mycpBundle:photo')->find($user_photo->getPhoId());
                if ($photo_old)
                    $em->remove($photo_old);
                @unlink($dir . $user_tourist->getUserTouristUser()->getUserPhoto()->getPhoName());
            }

            $photo = new photo();
            $fileName = uniqid('user-') . '-photo.jpg';
            $file['photo']->move($dir, $fileName);

            //Redimensionando la foto del usuario
            \MyCp\mycpBundle\Helpers\Images::resize($dir . $fileName, 65);

            $photo->setPhoName($fileName);
            $user_tourist->getUserTouristUser()->setUserPhoto($photo);
            $em->persist($photo);
        }
        $em->persist($user_tourist);
        $em->flush();
    }

    function new_user_partner($id_role, $request, $dir, $factory) {
        $em = $this->getEntityManager();
        $form_post = $request->get('mycp_mycpbundle_client_partnertype');

        $lang = $em->getRepository('mycpBundle:lang')->find($form_post['language']);
        $currency = $em->getRepository('mycpBundle:currency')->find($form_post['currency']);
        $country = $em->getRepository('mycpBundle:country')->find($form_post['country']);

        $user = new user();
        $user_partner = new userPartner();

        $user->setUserAddress($form_post['address']);
        $user->setUserCity($form_post['city']);
        $user->setUserCountry($country);
        $user->setUserEmail($form_post['email']);
        $user->setUserPhone($form_post['phone']);
        $user->setUserName($form_post['user_name']);
        $user->setUserLastName($form_post['user_name']);
        $user->setUserCreatedByMigration(false);
        $role = $em->getRepository('mycpBundle:role')->find($id_role);
        $user->setUserRole('ROLE_CLIENT_PARTNER');
        $user->setUserSubrole($role);
        $user->setUserUserName($form_post['user_name']);
        $encoder = $factory->getEncoder($user);
        //$user->setUserCreationDate(new \DateTime());       
        $password = $encoder->encodePassword($form_post['user_password']['Clave:'], $user->getSalt());
        $user->setUserPassword($password);
        $user_partner->setUserPartnerCurrency($currency);
        $user_partner->setUserPartnerLanguage($lang);
        $user_partner->setUserPartnerUser($user);
        $user_partner->setUserPartnerContactPerson($form_post['contact_person']);
        $user_partner->setUserPartnerCompanyCode($form_post['company_code']);
        $user_partner->setUserPartnerCompanyName($form_post['company_name']);

        $file = $request->files->get('mycp_mycpbundle_client_partnertype');
        if (isset($file['photo'])) {
            $photo = new photo();
            $fileName = uniqid('user-') . '-photo.jpg';
            $file['photo']->move($dir, $fileName);

            //Redimensionando la foto del usuario
            \MyCp\mycpBundle\Helpers\Images::resize($dir . $fileName, 65);

            $photo->setPhoName($fileName);
            $user->setUserPhoto($photo);
            $em->persist($photo);
        }
        $em->persist($user);
        $em->persist($user_partner);
        $em->flush();
    }

    function edit_user_partner($id_user, $request, $dir, $factory) {
        $post = $request->request->get('mycp_mycpbundle_client_partnertype');
        $em = $this->getEntityManager();
        $user_partner = new userPartner();
        $user_partner = $em->getRepository('mycpBundle:userPartner')->findBy(array('user_partner_user' => $id_user));
        $user_partner = $user_partner[0];
        $user_partner->getUserPartnerUser()->setUserName($post['user_name']);
        $user_partner->getUserPartnerUser()->setUserAddress($post['address']);
        $user_partner->getUserPartnerUser()->setUserEmail($post['email']);
        $user_partner->getUserPartnerUser()->setUserUserName($post['user_name']);
        $user_partner->getUserPartnerUser()->setUserLastName($post['user_name']);
        $user_partner->getUserPartnerUser()->setUserPhone($post['phone']);
        $user_partner->getUserPartnerUser()->setUserCity($post['city']);
        $country = $em->getRepository('mycpBundle:country')->find($post['country']);
        $currency = $em->getRepository('mycpBundle:currency')->find($post['currency']);
        $language = $em->getRepository('mycpBundle:lang')->find($post['language']);
        $user_partner->getUserPartnerUser()->setUserCountry($country);
        $user_partner->setUserPartnerCurrency($currency);
        $user_partner->setUserPartnerLanguage($language);
        $user_partner->setUserPartnerCompanyCode($post['company_code']);
        $user_partner->setUserPartnerCompanyName($post['company_name']);
        $user_partner->setUserPartnerContactPerson($post['contact_person']);

        if ($post['user_password']['Clave:'] != '') {
            $encoder = $factory->getEncoder($user_partner->getUserPartnerUser());
            $password = $encoder->encodePassword($post['user_password']['Clave:'], $user_partner->getUserPartnerUser()->getSalt());
            $user_partner->getUserPartnerUser()->setUserPassword($password);
        }
        $file = $request->files->get('mycp_mycpbundle_client_partnertype');
        if (isset($file['photo'])) {
            $photo_user = $user_partner->getUserPartnerUser()->getUserPhoto();

            if ($photo_user != null) {
                $photo_old = $em->getRepository('mycpBundle:photo')->find($photo_user->getPhoId());
                if ($photo_old)
                    $em->remove($photo_old);
                @unlink($dir . $user_partner->getUserPartnerUser()->getUserPhoto()->getPhoName());
            }

            $photo = new photo();
            $fileName = uniqid('user-') . '-photo.jpg';
            $file['photo']->move($dir, $fileName);

            //Redimensionando la foto del usuario
            \MyCp\mycpBundle\Helpers\Images::resize($dir . $fileName, 65);

            $photo->setPhoName($fileName);
            $user_partner->getUserPartnerUser()->setUserPhoto($photo);
            $em->persist($photo);
        }
        $em->persist($user_partner);
        $em->flush();
    }

    function new_user_staff($id_role, $request, $dir, $factory) {
        $em = $this->getEntityManager();
        $form_post = $request->get('mycp_mycpbundle_client_stafftype');
        $country = $em->getRepository('mycpBundle:country')->find($form_post['user_country']);

        $user = new user();

        $user->setUserAddress($form_post['user_address']);
        $user->setUserCity($form_post['user_city']);
        $user->setUserCountry($country);
        $user->setUserEmail($form_post['user_email']);
        $user->setUserPhone($form_post['user_phone']);
        $user->setUserName($form_post['user_name']);
        $user->setUserLastName($form_post['user_last_name']);
        $user->setUserCreatedByMigration(false);
        $role = $em->getRepository('mycpBundle:role')->find($id_role);
        $user->setUserRole('ROLE_CLIENT_STAFF');
        $user->setUserSubrole($role);
        $user->setUserUserName($form_post['user_user_name']);
        //$user->setUserCreationDate(new \DateTime());
        $encoder = $factory->getEncoder($user);
        $password = $encoder->encodePassword($form_post['user_password']['Clave:'], $user->getSalt());
        $user->setUserPassword($password);

        $file = $request->files->get('mycp_mycpbundle_client_stafftype');
        //var_dump($file); exit();
        if (isset($file['user_photo'])) {
            $photo = new photo();
            $fileName = uniqid('user-') . '-photo.jpg';
            $file['user_photo']->move($dir, $fileName);
            //Redimensionando la foto del usuario
            \MyCp\mycpBundle\Helpers\Images::resize($dir . $fileName, 65);

            $photo->setPhoName($fileName);
            $user->setUserPhoto($photo);
            $em->persist($photo);
        }
        $em->persist($user);
        $em->flush();
    }

    function edit_user_staff($id_user, $request, $dir, $factory) {
        $post = $request->request->get('mycp_mycpbundle_client_stafftype');
        $em = $this->getEntityManager();
        $user = new user();
        $user = $em->getRepository('mycpBundle:user')->find($id_user);
        $user->setUserName($post['user_name']);
        $user->setUserAddress($post['user_address']);
        $user->setUserEmail($post['user_email']);
        $user->setUserCity($post['user_city']);
        $user->setUserUserName($post['user_user_name']);
        $user->setUserLastName($post['user_last_name']);
        $user->setUserPhone($post['user_phone']);
        $country = $em->getRepository('mycpBundle:country')->find($post['user_country']);

        $user->setUserCountry($country);

        //var_dump($post['user_password']['Clave:']); exit();
        if ($post['user_password']['Clave:'] != '') {
            $user_enc = new user();
            $encoder = $factory->getEncoder($user_enc);
            $password = $encoder->encodePassword($post['user_password']['Clave:'], $user_enc->getSalt());
            $user->setUserPassword($password);
        }
        $file = $request->files->get('mycp_mycpbundle_client_stafftype');
        if (isset($file['user_photo'])) {
            //var_dump($user->getUserPhoto());

            if ($user->getUserPhoto() != null) {
                $photo_old = $em->getRepository('mycpBundle:photo')->find($user->getUserPhoto()->getPhoId());
                if ($photo_old)
                    $em->remove($photo_old);
                @unlink($dir . $user->getUserPhoto()->getPhoName());
            }


            $photo = new photo();
            $fileName = uniqid('user-') . '-photo.jpg';
            $file['user_photo']->move($dir, $fileName);
            //Redimensionando la foto del usuario
            \MyCp\mycpBundle\Helpers\Images::resize($dir . $fileName, 65);

            $photo->setPhoName($fileName);
            $user->setUserPhoto($photo);
            $em->persist($photo);
        }
        $em->persist($user);
        $em->flush();
    }

    public function get_all_users($filter_user_name, $filter_role, $filter_city, $filter_country, $filter_name, $filter_last_name, $filter_email) {
        $string_role = '';
        if ($filter_role != 'null' && $filter_role != '') {
            $string_role = "AND sr.role_name = :filter_role";
        }

        $string_city = '';
        if ($filter_city != 'null' && $filter_city != '') {
            $string_city = "AND u.user_city LIKE :filter_city";
        }

        $string_country = '';
        if ($filter_country != 'null' && $filter_country != '') {
            $string_country = "AND u.user_country = :filter_country";
        }

        $string_name = '';
        if ($filter_name != 'null' && $filter_name != '') {
            $string_name = "AND u.user_user_name LIKE :filter_name";
        }

        $string_last_name = '';
        if ($filter_last_name != 'null' && $filter_last_name != '') {
            $string_last_name = "AND u.user_last_name LIKE :filter_last_name";
        }

        $string_email = '';
        if ($filter_email != 'null' && $filter_email != '') {
            $string_email = "AND u.user_email LIKE :filter_email";
        }

        $em = $this->getEntityManager();
        $query = $em->createQuery("SELECT u FROM mycpBundle:user u JOIN u.user_subrole sr
        WHERE u.user_name LIKE :filter_user_name $string_role $string_city $string_country $string_name $string_last_name $string_email");
        
        if ($filter_role != 'null' && $filter_role != '')
            $query->setParameter ('filter_role', $filter_role);
        
        if ($filter_city != 'null' && $filter_city != '')
            $query->setParameter ('filter_city', "%".$filter_city."%");
        
        if ($filter_country != 'null' && $filter_country != '')
            $query->setParameter ('filter_country', $filter_country);
        
        if ($filter_name != 'null' && $filter_name != '')
             $query->setParameter ('filter_name', "%".$filter_name."%");
        
        if ($filter_last_name != 'null' && $filter_last_name != '')
            $query->setParameter ('filter_last_name', "%".$filter_last_name."%");
        
        if ($filter_email != 'null' && $filter_email != '')
            $query->setParameter ('filter_email', "%".$filter_email."%");
        
        $query->setParameter ('filter_user_name', "%".$filter_user_name."%");
        
        return $query->getResult();
    }

    function get_roles_staff() {
        $em = $this->getEntityManager();
        $query = $em->createQuery("SELECT r FROM mycpBundle:role r
        WHERE r.role_name LIKE 'ROLE_CLIENT_STAFF%'");
        return $query->getResult();
    }

    /**
     * Yanet
     */
    
    public function user_ids($controller) {
        $user_id = null;
        $session_id = null;
        $request = $controller->getRequest();
        $user = $controller->getUser();

        if ($user != null && $user != "anon.")
            $user_id = $user->getUserId();

        if ($user_id == null) {
            if ($request->cookies->has("mycp_user_session")) {
                //var_dump ($request->cookies->get("mycp_user_session"));
                $session_id = $request->cookies->get("mycp_user_session");
            } else {
                $session = $controller->getRequest()->getSession();
                $now = time();
                $session_id = $session->getId(); //."_".$now;
                setcookie("mycp_user_session", $session_id, time() + 60 * 60 * 24 * 365,'/');
            }
        }

        return array('user_id' => $user_id, 'session_id' => $session_id);
    }

    public function get_session_id($controller) {
        $session_id = null;
        $request = $controller->getRequest();

        if ($request->cookies->has("mycp_user_session"))
            $session_id = $request->cookies->get("mycp_user_session");
        return $session_id;
    }

    public function get_user_id($controller) {
        $user = $controller->get('security.context')->getToken()->getUser();

        if ($user != null && $user != "anon.")
            return $user->getUserId();

        return -1;
    }

    public function get_session_id_with_request($request) {
        $session_id = null;
        if ($request->cookies->has("mycp_user_session"))
            $session_id = $request->cookies->get("mycp_user_session");
        return $session_id;
    }
    
    public function changeStatus($userId)
    {
        $em = $this->getEntityManager();
        $user = $em->getRepository('mycpBundle:user')->find($userId);
        $currentStatus = $user->getUserEnabled();
        $userActivationDate = $user->getUserActivationDate();
        
        $user->setUserEnabled(!$currentStatus);
        
        if(!$currentStatus && !isset($userActivationDate))
            $user->setUserActivationDate(new \DateTime());
        $em->persist($user);
        $em->flush();
    }

}
