<?php

namespace MyCp\mycpBundle\Entity;

use Doctrine\ORM\EntityRepository;
use MyCp\mycpBundle\Helpers\DoctrineHelp;
use MyCp\mycpBundle\Helpers\Images;
use MyCp\mycpBundle\Helpers\RegistrationMode;
use MyCp\mycpBundle\Helpers\UserStatus;

/**
 * userRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class userRepository extends EntityRepository
{

    function shortEdit($id_user, $request, $container, $factory)
    {
        $post = $request->request->getIterator()->getArrayCopy();
        $em = $this->getEntityManager();
        $dir = $container->getParameter('user.dir.photos');
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

    function registerUser($post, $request, $encoder, $translator, $languageCode, $currency)
    {
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
        $user->setUserEnabled(true);

        $password = $encoder->encodePassword($post['user_password']['first'], $user->getSalt());

        $user->setUserPassword($password);
        $user_tourist->setUserTouristCurrency($currency);
        $user_tourist->setUserTouristLanguage($language);
        $user_tourist->setUserTouristUser($user);
        $em->persist($user);
        $em->persist($user_tourist);
        $em->flush();

        return $user;
    }

    function insertUserStaff($id_role, $request, $container, $factory)
    {
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
        if (array_key_exists("locked", $form_post) && $form_post['locked'])
            $user->setLocked($form_post['locked']);
        $user->setUserEnabled(true);
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

        return $user;
    }

    function editUserStaff($id_user, $request, $container, $factory)
    {
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
        if (array_key_exists("locked", $post) && $post['locked']) {
            $user->setLocked(true);
        } else {
            $user->setLocked(false);
        }

        if (array_key_exists("user_role", $post) && $post['user_role']) {
            $userRole = $em->getRepository('mycpBundle:role')->findOneBy(array('role_id' => $post['user_role']));
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

        return $user;
    }


    public function getAll($filter_user_name, $filter_role, $filter_city, $filter_country, $filter_name, $filter_last_name, $filter_email, $filter_method, $filter_status, $filter_creation_date_from, $filter_creation_date_to)
    {
        $em = $this->getEntityManager();

        $qb = $em->createQueryBuilder()
            ->select("u")
            ->from("mycpBundle:user", "u")
            ->join("u.user_subrole", "sr")
            ->orderBy("u.user_creation_date", "DESC");

        if ($filter_role != 'null' && $filter_role != '') {
            $qb->andWhere("sr.role_name = :filter_role")
                ->setParameter("filter_role", $filter_role);
        }

        if ($filter_city != 'null' && $filter_city != '') {
            $qb->andWhere("u.user_city LIKE :filter_city")
                ->setParameter("filter_city", "%" . $filter_city . "%");
        }

        if ($filter_country != 'null' && $filter_country != '') {
            $qb->andWhere("u.user_country = :filter_country")
                ->setParameter("filter_country", $filter_country);
        }

        if ($filter_name != 'null' && $filter_name != '') {
            $qb->andWhere("u.user_user_name LIKE :filter_name")
                ->setParameter("filter_name", "%" . $filter_name . "%");
        }

        if ($filter_last_name != 'null' && $filter_last_name != '') {
            $qb->andWhere("u.user_last_name LIKE :filter_last_name")
                ->setParameter("filter_last_name", "%" . $filter_last_name . "%");
        }

        if ($filter_email != 'null' && $filter_email != '') {
            $qb->andWhere("u.user_email LIKE :filter_email")
                ->setParameter("filter_email", "%" . $filter_email . "%");
        }

        if ($filter_user_name != 'null' && $filter_user_name != '') {
            $qb->andWhere("u.user_name LIKE :filter_user_name")
                ->setParameter("filter_user_name", "%" . $filter_user_name . "%");
        }

        if ($filter_method != 'null' && $filter_method != '') {

            switch ($filter_method) {
                case RegistrationMode::FACEBOOK:
                    {
                        $qb->andWhere("(u.facebook = 1 or u.user_password = '')")
                            ->andWhere("u.user_role = 'ROLE_CLIENT_TOURIST'");
                        break;
                    }
                case RegistrationMode::REGISTRATION:
                    {
                        $qb->andWhere("u.user_created_by_migration = 0");
                        break;
                    }
            }
        }

        if ($filter_status != 'null' && $filter_status != '') {

            switch ($filter_status) {
                case UserStatus::Inactive:
                    {
                        $qb->andWhere("((u.locked is null or u.locked = 0) AND u.user_enabled = 0)");
                        break;
                    }
                case UserStatus::Locked:
                    {
                        $qb->andWhere("u.locked = 1");
                        break;
                    }
                default:
                    {
                        $qb->andWhere("(u.locked is null or u.locked = 0)");
                        break;
                    }
            }
        }

        if ($filter_creation_date_from != 'null' && $filter_creation_date_from != '') {
            $qb->andWhere("u.user_creation_date >= :filter_creation_date_from")
                ->setParameter("filter_creation_date_from", $filter_creation_date_from);
        }

        if ($filter_creation_date_to != 'null' && $filter_creation_date_to != '') {
            $qb->andWhere("u.user_creation_date <= :filter_creation_date_to")
                ->setParameter("filter_creation_date_to", $filter_creation_date_to);
        }

        return $qb->getQuery()->getResult();
    }

    public function getAllPaginate($filter_user_name, $filter_role, $filter_city, $filter_country, $filter_name, $filter_last_name, $filter_email, $filter_method, $filter_status, $filter_creation_date_from, $filter_creation_date_to, $offset, $limit)
    {
        $em = $this->getEntityManager();

        $qb = $em->createQueryBuilder()
            ->select("u")
            ->from("mycpBundle:user", "u")
            ->join("u.user_subrole", "sr")
            ->orderBy("u.user_creation_date", "DESC");

        if ($filter_role != 'null' && $filter_role != '') {
            $qb->andWhere("sr.role_name = :filter_role")
                ->setParameter("filter_role", $filter_role);
        }

        if ($filter_city != 'null' && $filter_city != '') {
            $qb->andWhere("u.user_city LIKE :filter_city")
                ->setParameter("filter_city", "%" . $filter_city . "%");
        }

        if ($filter_country != 'null' && $filter_country != '') {
            $qb->andWhere("u.user_country = :filter_country")
                ->setParameter("filter_country", $filter_country);
        }

        if ($filter_name != 'null' && $filter_name != '') {
            $qb->andWhere("u.user_user_name LIKE :filter_name")
                ->setParameter("filter_name", "%" . $filter_name . "%");
        }

        if ($filter_last_name != 'null' && $filter_last_name != '') {
            $qb->andWhere("u.user_last_name LIKE :filter_last_name")
                ->setParameter("filter_last_name", "%" . $filter_last_name . "%");
        }

        if ($filter_email != 'null' && $filter_email != '') {
            $qb->andWhere("u.user_email LIKE :filter_email")
                ->setParameter("filter_email", "%" . $filter_email . "%");
        }

        if ($filter_user_name != 'null' && $filter_user_name != '') {
            $qb->andWhere("u.user_name LIKE :filter_user_name")
                ->setParameter("filter_user_name", "%" . $filter_user_name . "%");
        }

        if ($filter_method != 'null' && $filter_method != '') {

            switch ($filter_method) {
                case RegistrationMode::FACEBOOK:
                    {
                        $qb->andWhere("(u.facebook = 1 or u.user_password = '')")
                            ->andWhere("u.user_role = 'ROLE_CLIENT_TOURIST'");
                        break;
                    }
                case RegistrationMode::REGISTRATION:
                    {
                        $qb->andWhere("u.user_created_by_migration = 0");
                        break;
                    }
            }
        }

        if ($filter_status != 'null' && $filter_status != '') {

            switch ($filter_status) {
                case UserStatus::Inactive:
                    {
                        $qb->andWhere("((u.locked is null or u.locked = 0) AND u.user_enabled = 0)");
                        break;
                    }
                case UserStatus::Locked:
                    {
                        $qb->andWhere("u.locked = 1");
                        break;
                    }
                default:
                    {
                        $qb->andWhere("(u.locked is null or u.locked = 0)");
                        break;
                    }
            }
        }

        if ($filter_creation_date_from != 'null' && $filter_creation_date_from != '') {
            $qb->andWhere("u.user_creation_date >= :filter_creation_date_from")
                ->setParameter("filter_creation_date_from", $filter_creation_date_from);
        }

        if ($filter_creation_date_to != 'null' && $filter_creation_date_to != '') {
            $qb->andWhere("u.user_creation_date <= :filter_creation_date_to")
                ->setParameter("filter_creation_date_to", $filter_creation_date_to);
        }

        $paginator = DoctrineHelp::paginate($qb->getQuery(), $offset, $limit);
        $query_clone = clone $qb;
        $query_clone->select('count(u.user_id) as total_elements ');
        $result = $query_clone->getQuery()->getScalarResult();
        $paginator->addInfo('Total items', $result[0]['total_elements']);
        $paginator->addInfo('query', $query_clone);
        return $paginator;
    }

    public function getIds($controller)
    {
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

    public function getSessionId($controller)
    {
        $session_id = null;
        $request = $controller->getRequest();

        if ($request->cookies->has("mycp_user_session"))
            $session_id = $request->cookies->get("mycp_user_session");
        return $session_id;
    }

    public function getUserId($controller)
    {
        $user = $controller->get('security.context')->getToken()->getUser();

        if ($user != null && $user != "anon.")
            return $user->getUserId();

        return -1;
    }

    public function getSessionIdWithRequest($request)
    {
        $session_id = null;
        if ($request->cookies->has("mycp_user_session"))
            $session_id = $request->cookies->get("mycp_user_session");
        return $session_id;
    }

    public function changeStatus($userId, $returnUser = false)
    {
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

            if ($returnUser) {
                return $user;
            }

            return true;
        }

        return false;
    }

    public function getUsersStaff()
    {
        $em = $this->getEntityManager();
        $query = $em->createQuery("SELECT u FROM mycpBundle:user u
        WHERE u.user_role LIKE 'ROLE_CLIENT_STAFF%' AND (u.locked is NULL or u.locked = 0) order by u.user_user_name ASC");
        return $query->getResult();
    }

    public function getUsersInfoStaff()
    {
        $em = $this->getEntityManager();
        $query = $em->createQuery("SELECT u FROM mycpBundle:user u JOIN u.user_subrole sr WHERE sr.role_name = 'ROLE_CLIENT_IP_STAFF' ORDER BY u.user_user_name");
        return $query->getResult();
    }

    public function getUserBackendByEmailAndUserName($email, $userName)
    {
        $em = $this->getEntityManager();
        return $em->createQueryBuilder()
            ->select("u")
            ->from("mycpBundle:user", "u")
            ->where("u.user_role != :userTouristRole")
            ->andWhere("u.user_email = :email")
            ->andWhere("u.user_name = :userName")
            ->setParameter("userName", $userName)
            ->setParameter("email", $email)
            ->setParameter("userTouristRole", "ROLE_CLIENT_TOURIST")
            ->setMaxResults(1)
            ->getQuery()->getOneOrNullResult();
    }

    /**
     */
    public function getUsers()
    {
        $em = $this->getEntityManager();
        $query = $em->createQuery("SELECT u FROM mycpBundle:user u");
        return $query->getResult();
    }

    public function getUserByFilters($filter_user_name, $filter_role, $filter_city, $filter_country, $filter_name, $filter_last_name, $filter_email)
    {
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
            $string_name = "AND u.user_user_name = :filter_name";
        }

        $string_last_name = '';
        if ($filter_last_name != 'null' && $filter_last_name != '') {
            $string_last_name = "AND u.user_last_name LIKE :filter_last_name";
        }

        $string_email = '';
        if ($filter_email != 'null' && $filter_email != '') {
            $string_email = "AND u.user_email = :filter_email";
        }
        $string_user_name = '';
        if ($filter_user_name != 'null' && $filter_user_name != '') {
            $string_user_name = "AND  u.user_name = :filter_user_name";
        }

        $em = $this->getEntityManager();
        $query = $em->createQuery("SELECT u FROM mycpBundle:user u JOIN u.user_subrole sr
        WHERE (u.locked is null or u.locked = 0) $string_user_name $string_role $string_city $string_country $string_name $string_last_name $string_email");

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
            $query->setParameter('filter_email', $filter_email);

        if ($filter_user_name != 'null' && $filter_user_name != '')
            $query->setParameter('filter_user_name', $filter_user_name);
        return $query->getResult();
    }

    public function getTouristUserByEmail($email)
    {
        $em = $this->getEntityManager();
        return $em->createQueryBuilder()
            ->select("u")
            ->from("mycpBundle:user", "u")
            ->where("u.user_role = :userTouristRole")
            ->andWhere("u.user_email = :email")
            ->andWhere("(u.locked is null or u.locked = 0)")
            ->andWhere("u.user_enabled = 1")
            ->setParameter("email", $email)
            ->setParameter("userTouristRole", "ROLE_CLIENT_TOURIST")
            ->setMaxResults(1)
            ->getQuery()->getOneOrNullResult();
    }



    public function getAllTourOperators(array $touroperators,user $user){
     if(!in_array($user,$touroperators)) {
         array_push($touroperators, $user);
     }
     $tem_tours= $user->getChildrens();
     foreach ($tem_tours as $child){
      if(!in_array($child,$touroperators)){
      array_push($touroperators,$child);
      if(count($child->getChildrens())>0){
         $result= $this->getAllTourOperators($touroperators,$child);
         $touroperators=$result;
      }
     }}
     return $touroperators;


    }
    public function getUserNotReservations()
    {
        $date = date('Y-m-d');
        $new_date = strtotime('-2 day', strtotime($date));
        $new_date = date('Y-m-d', $new_date);

        $em = $this->getEntityManager();
        $query = $em->createQuery("SELECT u FROM mycpBundle:user u JOIN u.user_subrole sr
        WHERE (u.locked is null or u.locked = 0) AND u.user_creation_date like '%$new_date%' AND u.user_role = 'ROLE_CLIENT_TOURIST'
        AND u.user_id NOT IN (SELECT DISTINCT us.user_id from mycpBundle:generalReservation gre JOIN gre.gen_res_user_id us
        where gre.gen_res_date>=$new_date
        )
        
         ");


        return $query->getResult();
    }

    public function getNotTourOperators()
    {
        $em = $this->getEntityManager();
        return $em->createQueryBuilder()
            ->select("u")
            ->from("mycpBundle:user", "u")
            ->andWhere("u.user_subrole = 3")
            ->andWhere("u.user_enabled = 1")
            ->getQuery()->getResult();

    }




}
