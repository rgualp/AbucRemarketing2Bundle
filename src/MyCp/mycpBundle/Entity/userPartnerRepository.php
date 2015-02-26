<?php

namespace MyCp\mycpBundle\Entity;

use Doctrine\ORM\EntityRepository;
use MyCp\mycpBundle\Entity\userPartner;
use MyCp\mycpBundle\Helpers\Images;

/**
 * userPartnerRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class userPartnerRepository extends EntityRepository
{
    function insert($id_role, $request, $dir, $factory) {
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
            Images::resize($dir . $fileName, 65);

            $photo->setPhoName($fileName);
            $user->setUserPhoto($photo);
            $em->persist($photo);
        }
        $em->persist($user);
        $em->persist($user_partner);
        $em->flush();
    }

    function edit($id_user, $request, $dir, $factory) {
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
            Images::resize($dir . $fileName, 65);

            $photo->setPhoName($fileName);
            $user_partner->getUserPartnerUser()->setUserPhoto($photo);
            $em->persist($photo);
        }
        $em->persist($user_partner);
        $em->flush();
    }
}
