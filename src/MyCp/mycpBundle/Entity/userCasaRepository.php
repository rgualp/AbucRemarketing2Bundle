<?php

namespace MyCp\mycpBundle\Entity;

use Doctrine\ORM\EntityRepository;
use MyCp\mycpBundle\Helpers\FileIO;

/**
 * userCasaRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class userCasaRepository extends EntityRepository {

    function getAll() {
        $em = $this->getEntityManager();
        $query = $em->createQuery("SELECT uc FROM mycpBundle:userCasa uc
        GROUP BY uc.user_casa_user");
        return $query->getResult();
    }

    function getByUser($user_id)
    {
        $em = $this->getEntityManager();
        $query = $em->createQuery("SELECT uc FROM mycpBundle:userCasa uc join uc.user_casa_user us
        Where us.user_id = :user_id");
        $query->setParameter(':user_id', $user_id)->setMaxResults(1);
        return $query->getSingleResult();
    }

    function getUser($exclude_own_id)
    {
        $em = $this->getEntityManager();
        $query = $em->createQuery("SELECT user.user_id, user.user_name, own.own_mcp_code, own.own_name FROM mycpBundle:userCasa uc
        JOIN uc.user_casa_user user
        JOIN uc.user_casa_ownership own" .
        (($exclude_own_id != null) ? " WHERE own.own_id <> $exclude_own_id " : " ") .
        " ORDER BY user.user_name");
        return $query->getResult();
    }

    function createUser($ownership, $file, $factory, $send_creation_mail, $controller, $container) {
        $em = $this->getEntityManager();

        $user_casa = $em->getRepository('mycpBundle:userCasa')->findOneBy(array('user_casa_ownership' => $ownership->getOwnId()));

        if($user_casa == null) {
            $dir_file = $container->getParameter('user.dir.photos');
            $photoSize = $container->getParameter('user.photo.size');
            $user = new user();
            $country = $em->getRepository('mycpBundle:country')->findBy(array('co_name' => 'Cuba'));
            $subrole = $em->getRepository('mycpBundle:role')->findOneBy(array('role_name' => 'ROLE_CLIENT_CASA'));

            $address = $ownership->getOwnAddressStreet() . " #" . $ownership->getOwnAddressNumber() . ", " . $ownership->getOwnAddressMunicipality()->getMunName() . ", " . $ownership->getOwnAddressProvince()->getProvName();
            $phone = '(+53) ' . $ownership->getOwnAddressProvince()->getProvPhoneCode() . ' ' . $ownership->getOwnPhoneNumber();

            $email = $ownership->getOwnEmail1();
            if (empty($email))
                $email = $ownership->getOwnEmail2();

            $user->setUserAddress($address);
            $user->setUserCity($ownership->getOwnAddressMunicipality()->getMunName());
            $user->setUserCountry($country[0]);
            $user->setUserEmail($email);
            $user->setUserPhone($phone);
            $user->setUserName($ownership->getOwnMcpCode());

            if ($file) {
                $photo = new photo();
                $fileName = uniqid('user-') . '-photo.jpg';
                $file->move($dir_file, $fileName);
                //Redimensionando la foto del usuario
                \MyCp\mycpBundle\Helpers\Images::resize($dir_file . $fileName, $photoSize);
                $photo->setPhoName($fileName);
                $user->setUserPhoto($photo);
                $em->persist($photo);
            }

            $user->setUserRole('ROLE_CLIENT_CASA');
            $user->setUserEnabled(false);
            $user->setUserCreatedByMigration(false);
            $user->setUserSubrole($subrole);
            $user_name = explode(' ', $ownership->getOwnHomeOwner1());
            $user->setUserUserName($user_name[0]);
            $lastName = (count($user_name) > 1) ? $user_name[1] : "";
            $user->setUserLastName($lastName);
            $user->setUserPassword(" ");

            $user_casa = new userCasa();
            $user_casa->setUserCasaOwnership($ownership);
            $user_casa->setUserCasaUser($user);
            $encoder = $factory->getEncoder($user);
            $secret_token = $encoder->encodePassword("casa_" . $ownership->getOwnMcpCode(), $user->getSalt());
            $secret_token = base64_encode($secret_token);
            $secret_token = str_replace('/', '1', $secret_token);
            $secret_token = str_replace(' ', '2', $secret_token);
            $secret_token = str_replace('+', '3', $secret_token);
            $secret_token = str_replace('=', '4', $secret_token);
            $secret_token = str_replace('?', '5', $secret_token);
            $user_casa->setUserCasaSecretToken($secret_token);
            $em->persist($user);
            $em->persist($user_casa);
        }

        if ($send_creation_mail) {
            $user = $user_casa->getUserCasaUser();
            \MyCp\mycpBundle\Helpers\UserMails::sendCreateUserCasaMail($controller, $user->getUserEmail(), $user->getUserName(), $user->getUserUserName() . ' ' . $user->getUserLastName(), $user_casa->getUserCasaSecretToken(), $ownership->getOwnName(), $ownership->getOwnMcpCode());
        }

        return $user_casa;
    }

    function edit($id_user, $request, $container, $factory) {
         $post = $request->request->getIterator()->getArrayCopy();
         $em = $this->getEntityManager();
         $dir = $container->getParameter('user.dir.photos');
         $photoSize = $container->getParameter('user.photo.size');
         $user_casa = $em->getRepository('mycpBundle:userCasa')->findBy(array('user_casa_user' => $id_user));
         $user_casa = $user_casa[0];
         $user_casa->getUserCasaUser()->setUserName($post['mycp_mycpbundle_client_casatype']['user_name']);
         $user_casa->getUserCasaUser()->setUserAddress($post['mycp_mycpbundle_client_casatype']['address']);
         $user_casa->getUserCasaUser()->setUserUserName($post['mycp_mycpbundle_client_casatype']['name']);
         $user_casa->getUserCasaUser()->setUserLastName($post['mycp_mycpbundle_client_casatype']['last_name']);
         $user_casa->getUserCasaUser()->setUserEmail($post['mycp_mycpbundle_client_casatype']['email']);
         $user_casa->getUserCasaUser()->setUserPhone($post['mycp_mycpbundle_client_casatype']['phone']);

         $user_locked = (isset($post['mycp_mycpbundle_client_casatype']['locked']));
         $user_casa->getUserCasaUser()->setLocked($user_locked);

         $user_enabled = (isset($post['mycp_mycpbundle_client_casatype']['user_enabled']));
         $user_casa->getUserCasaUser()->setUserEnabled($user_enabled);
         if ($post['mycp_mycpbundle_client_casatype']['user_password']['Clave:'] != '') {
             $encoder = $factory->getEncoder($user_casa->getUserCasaUser());
             $password = $encoder->encodePassword($post['mycp_mycpbundle_client_casatype']['user_password']['Clave:'], $user_casa->getUserCasaUser()->getSalt());
             $user_casa->getUserCasaUser()->setUserPassword($password);
         }
         $file = $request->files->get('mycp_mycpbundle_client_casatype');
         if (isset($file['photo'])) {
             $photo_user = $user_casa->getUserCasaUser()->getUserPhoto();
             if ($photo_user != null) {
                 $photo_old = $em->getRepository('mycpBundle:photo')->find($photo_user->getPhoId());
                 if ($photo_old)
                     $em->remove($photo_old);
                 FileIO::deleteFile($dir . $user_casa->getUserCasaUser()->getUserPhoto()->getPhoName());
             }

             $photo = new photo();
             $fileName = uniqid('user-') . '-photo.jpg';
             $file['photo']->move($dir, $fileName);
             //Redimensionando la foto del usuario
             \MyCp\mycpBundle\Helpers\Images::resize($dir . $fileName, $photoSize);

             $photo->setPhoName($fileName);
             $user_casa->getUserCasaUser()->setUserPhoto($photo);
             $em->persist($photo);
         }
         $em->persist($user_casa);
         $em->flush();
        return $user_casa;
     }

    function getOwnersPhotos($ownership_id) {
        $em = $this->getEntityManager();
        $query_string = "SELECT pho.pho_name as photo
                  FROM mycpBundle:userCasa uc
                  JOIN uc.user_casa_user u
                  JOIN u.user_photo pho
                  WHERE uc.user_casa_ownership = $ownership_id";

        $query = $em->createQuery($query_string);
        $photo_name = $query->setMaxResults(1)->getResult();

        if ($photo_name == null)
            $photo_name = "no_photo.gif";
        else
        {
            $photo_name = $photo_name[0]["photo"];
            if (!file_exists(realpath("uploads/userImages/" . $photo_name)))
            $photo_name = "no_photo.gif";
        }
        return $photo_name;
    }

    function getAccommodationsWithoutUser()
    {
        $em = $this->getEntityManager();
        $query = $em->createQuery("SELECT o FROM mycpBundle:ownership o
        WHERE (SELECT count(user) FROM mycpBundle:userCasa user WHERE user.user_casa_ownership = o.own_id) = 0
        AND o.own_status =".ownershipStatus::STATUS_ACTIVE."
        ORDER BY o.own_mcp_code");
        return $query->getResult();
    }

    function getOneByOwnCode($own_code)
    {
        $em = $this->getEntityManager();
        $query_string = "SELECT uc
                  FROM mycpBundle:userCasa uc
                  JOIN uc.user_casa_ownership o
                  WHERE o.own_mcp_code = '$own_code'";

        $query = $em->createQuery($query_string)->setMaxResults(1);
        return $query->getOneOrNullResult();
    }

    function getOneByToken($token)
    {
        $em = $this->getEntityManager();
        $query_string = "SELECT uc
                  FROM mycpBundle:userCasa uc
                  WHERE uc.user_casa_secret_token = '$token'";

        $query = $em->createQuery($query_string)->setMaxResults(1);
        return $query->getOneOrNullResult();
    }

    function changeStatus($ownId, $isEnabled)
    {
        $em = $this->getEntityManager();
        $userCasa = $em->getRepository('mycpBundle:userCasa')->findOneBy(array('user_casa_ownership' => $ownId));

        if(isset($userCasa))
        {
            $user = $userCasa->getUserCasaUser();
            $activationDate = $user->getUserActivationDate();

            if($isEnabled || (!$isEnabled && isset($activationDate)))
            {
                $user->setUserEnabled($isEnabled);
                $em->persist($user);
                $em->flush();
            }
        }
    }
}
