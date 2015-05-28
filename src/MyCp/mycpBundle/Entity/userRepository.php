<?php

namespace MyCp\mycpBundle\Entity;

use Doctrine\ORM\EntityRepository;
use MyCp\mycpBundle\Helpers\Images;

/**
 * userRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class userRepository extends EntityRepository {

    function shortEdit($id_user, $request, $container, $factory) {
        $post = $request->request->getIterator()->getArrayCopy();
        $em = $this->getEntityManager();
        $dir=$container->getParameter('user.dir.photos');
        $photoSize = $container->getParameter('user.photo.size');

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
            Images::resize($dir . $fileName, $photoSize);

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

        if ($user == null)
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

    function insertUserStaff($id_role, $request, $container, $factory) {
        $em = $this->getEntityManager();
        $dir = $container->getParameter('user.dir.photos');
        $photoSize = $container->getParameter('user.photo.size');

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
            Images::resize($dir . $fileName, $photoSize);

            $photo->setPhoName($fileName);
            $user->setUserPhoto($photo);
            $em->persist($photo);
        }
        $em->persist($user);
        $em->flush();
    }

    function editUserStaff($id_user, $request, $container, $factory) {
        $post = $request->request->get('mycp_mycpbundle_client_stafftype');
        $em = $this->getEntityManager();
        $dir = $container->getParameter('user.dir.photos');
        $photoSize = $container->getParameter('user.photo.size');

        $user = $em->getRepository('mycpBundle:user')->find($id_user);
        $user->setUserName($post['user_name']);
        $user->setUserAddress($post['user_address']);
        $user->setUserEmail($post['user_email']);
        $user->setUserCity($post['user_city']);
        $user->setUserUserName($post['user_user_name']);
        $user->setUserLastName($post['user_last_name']);
        $user->setUserPhone($post['user_phone']);
        if($post['user_role']){
            $userRole = $em->getRepository('mycpBundle:role')->findOneBy(array('role_id'=>$post['user_role']));
            $user->setUserRole($userRole->getRoleName());
            $user->setUserSubrole($userRole);
        }

        $country = $em->getRepository('mycpBundle:country')->find($post['user_country']);

        $user->setUserCountry($country);

        if ($post['user_password']['Clave:'] != '') {
            $user_enc = new user();
            $encoder = $factory->getEncoder($user_enc);
            $password = $encoder->encodePassword($post['user_password']['Clave:'], $user_enc->getSalt());
            $user->setUserPassword($password);
        }
        $file = $request->files->get('mycp_mycpbundle_client_stafftype');
        if (isset($file['user_photo'])) {
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
            Images::resize($dir . $fileName, $photoSize);

            $photo->setPhoName($fileName);
            $user->setUserPhoto($photo);
            $em->persist($photo);
        }
        $em->persist($user);
        $em->flush();
    }

    public function getAll($filter_user_name, $filter_role, $filter_city, $filter_country, $filter_name, $filter_last_name, $filter_email) {
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
            $query->setParameter('filter_role', $filter_role);

        if ($filter_city != 'null' && $filter_city != '')
            $query->setParameter('filter_city', "%" . $filter_city . "%");

        if ($filter_country != 'null' && $filter_country != '')
            $query->setParameter('filter_country', $filter_country);

        if ($filter_name != 'null' && $filter_name != '')
            $query->setParameter('filter_name', "%" . $filter_name . "%");

        if ($filter_last_name != 'null' && $filter_last_name != '')
            $query->setParameter('filter_last_name', "%" . $filter_last_name . "%");

        if ($filter_email != 'null' && $filter_email != '')
            $query->setParameter('filter_email', "%" . $filter_email . "%");

        $query->setParameter('filter_user_name', "%" . $filter_user_name . "%");

        return $query->getResult();
    }

    public function getIds($controller) {
        $user_id = null;
        $session_id = null;
        $request = $controller->getRequest();
        $user = $controller->getUser();

        if ($user != null && $user != "anon.")
            $user_id = $user->getUserId();

        if ($user_id == null) {
            if ($request->cookies->has("mycp_user_session")) {
                $session_id = $request->cookies->get("mycp_user_session");
            } else {
                $session = $controller->getRequest()->getSession();
                $session_id = $session->getId();
                setcookie("mycp_user_session", $session_id, time() + 60 * 60 * 24 * 365, '/');
            }
        }

        return array('user_id' => $user_id, 'session_id' => $session_id);
    }

    public function getSessionId($controller) {
        $session_id = null;
        $request = $controller->getRequest();

        if ($request->cookies->has("mycp_user_session"))
            $session_id = $request->cookies->get("mycp_user_session");
        return $session_id;
    }

    public function getUserId($controller) {
        $user = $controller->get('security.context')->getToken()->getUser();

        if ($user != null && $user != "anon.")
            return $user->getUserId();

        return -1;
    }

    public function getSessionIdWithRequest($request) {
        $session_id = null;
        if ($request->cookies->has("mycp_user_session"))
            $session_id = $request->cookies->get("mycp_user_session");
        return $session_id;
    }

    public function changeStatus($userId) {
        $em = $this->getEntityManager();
        $user = $em->getRepository('mycpBundle:user')->find($userId);

        if (isset($user)) {
            $currentStatus = $user->getUserEnabled();
            $userActivationDate = $user->getUserActivationDate();

            $user->setUserEnabled(!$currentStatus);

            if (!$currentStatus && !isset($userActivationDate))
                $user->setUserActivationDate(new \DateTime());
            $em->persist($user);
            $em->flush();
        }
    }

    public function getUsersStaff()
    {
        $em = $this->getEntityManager();
        $query = $em->createQuery("SELECT u FROM mycpBundle:user u
        WHERE u.user_role LIKE 'ROLE_CLIENT_STAFF%' order by u.user_user_name ASC");
        return $query->getResult();
    }

}
