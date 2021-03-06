<?php

namespace MyCp\mycpBundle\Entity;

use Doctrine\ORM\EntityRepository;
use MyCp\mycpBundle\Helpers\Images;
use Symfony\Component\PropertyAccess\Exception\InvalidArgumentException;

/**
 * userTouristRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class userTouristRepository extends EntityRepository
{
    function insert($id_role, $request, $container, $factory) {
        $em = $this->getEntityManager();
        $dir = $container->getParameter('user.dir.photos');
        $photoSize = $container->getParameter('user.photo.size');

        $form_post = $request->get('mycp_mycpbundle_client_touristtype');

        $lang = $em->getRepository('mycpBundle:lang')->find($form_post['language']);
        $currency = $em->getRepository('mycpBundle:currency')->find($form_post['currency']);
        $country = $em->getRepository('mycpBundle:country')->find($form_post['country']);

        $user = new user();
        $user_tourist = new userTourist();

        $user->setUserAddress($form_post['address']);
        $user->setUserCity($form_post['city']);
        $user->setUserCountry($country);
        $user->setUserEmail($form_post['email']);
        $user->setUserPhone($form_post['phone']);
        $user->setUserName($form_post['user_name']);
        $user->setUserLastName($form_post['last_name']);
        $user->setUserCreatedByMigration(true);
        $role = $em->getRepository('mycpBundle:role')->find($id_role);
        $user->setUserRole('ROLE_CLIENT_TOURIST');
        $user->setUserSubrole($role);
        $user->setUserUserName($form_post['name']);
        $encoder = $factory->getEncoder($user);
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
            Images::resize($dir . $fileName, $photoSize);

            $photo->setPhoName($fileName);
            $user->setUserPhoto($photo);
            $em->persist($photo);
        }
        $em->persist($user);
        $em->persist($user_tourist);
        $em->flush();

        return $user_tourist;
    }

    function edit($id_user, $request, $container, $factory) {
        $post = $request->request->get('mycp_mycpbundle_client_touristtype');
        $em = $this->getEntityManager();
        $dir = $container->getParameter('user.dir.photos');
        $photoSize = $container->getParameter('user.photo.size');

        $user_tourist = $em->getRepository('mycpBundle:userTourist')->findOneBy(array('user_tourist_user' => $id_user));
        if ($user_tourist == null) {
            $user_tourist = new userTourist();
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
        $user_tourist->getUserTouristUser()->setLocked((array_key_exists("locked", $post) && $post['locked']) ? $post['locked']: false);

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
            Images::resize($dir . $fileName, $photoSize);

            $photo->setPhoName($fileName);
            $user_tourist->getUserTouristUser()->setUserPhoto($photo);
            $em->persist($photo);
        }
        $em->persist($user_tourist);
        $em->flush();
        return $user_tourist;
    }

    /**
     * If not exist a userTourist entity associated to a user with role ROLE_USER_TOURIST, this function create a default userTourist entity.
     * @param $defaultLanguageCode
     * @param $defaultCurrencyCode
     * @param user $user
     * @return userTourist
     */
    function createDefaultTourist($defaultLanguageCode, $defaultCurrencyCode, user $user)
    {
        $em = $this->getEntityManager();
        $defaultCurrency = $em->getRepository("mycpBundle:currency")->findOneBy(array("curr_code" =>strtoupper($defaultCurrencyCode)));
        $defaulLanguage = $em->getRepository("mycpBundle:lang")->findOneBy(array("lang_code" =>strtoupper($defaultLanguageCode)));

        if($defaultCurrency === null)
            throw new InvalidArgumentException("The default currency code is not valid. Change its value in config.yml, parameter's name: configuration.default.currency.code");

        if($defaulLanguage === null)
            throw new InvalidArgumentException("The default language code is not valid. Change its value in config.yml, parameter's name: configuration.default.language.code");


        $userTourist = new userTourist();
        $userTourist->setUserTouristCurrency($defaultCurrency);
        $userTourist->setUserTouristLanguage($defaulLanguage);
        $userTourist->setUserTouristUser($user);

        $em->persist($userTourist);
        $em->flush();

        return $userTourist;
    }
}
