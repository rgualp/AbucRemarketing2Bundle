<?php

namespace MyCp\PartnerBundle\Repository;

use Doctrine\ORM\EntityRepository;
use MyCp\mycpBundle\Entity\user;

/**
 * paTravelAgencyRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class paTravelAgencyRepository extends EntityRepository
{

    /**
     * @param $obj
     * @param $file
     * @param $factory
     * @param $send_creation_mail
     * @param $controller
     * @param $container
     */
    public function createUser($obj, $file, $factory, $send_creation_mail, $controller, $container, $pass, $language, $currency, $beta = null)
    {
        $em = $this->getEntityManager();
        $agency = $em->getRepository('PartnerBundle:paTravelAgency')->find($obj->getId());
        $subrole = $em->getRepository('mycpBundle:role')->findOneBy(array('role_name' => 'ROLE_CLIENT_PARTNER'));
        $address = $obj->getAddress();
        $phone = $obj->getPhone();
        $email = $obj->getEmail();

        $user = new user();
        $user2 = new user();
        $encoder = $factory->getEncoder($user2);
        $password = $encoder->encodePassword($pass, $user->getSalt());
        $user->setUserPassword($password);
        $user->setUserAddress($address);
        $user->setUserCountry($obj->getCountry());
        $user->setUserEmail($email);
        $user->setUserPhone($phone);
        $user->setUserName($obj->getEmail());
        $user->setUserRole('ROLE_CLIENT_PARTNER');
        //$user->setUserEnabled(false);
        $user->setUserEnabled(true);
        $user->setUserCreatedByMigration(false);
        $user->setUserSubrole($subrole);
        $user->setUserUserName($obj->getEmail());
        $user->setUserLastName($obj->getName());

        $user->setUserCurrency($currency);
        $user->setUserLanguage($language);
        $em->persist($user);
        $em->flush();

        if($send_creation_mail) {
            \MyCp\mycpBundle\Helpers\UserMails::sendCreateUserPartner($controller, $user->getUserEmail(), $user->getName(), $agency->getName(), $user->getUserId(), strtolower($language->getLangCode()), $agency, $beta);
        }
        //Send email team MCP
        $service_email = $controller->get('Email');
        $translator = $controller->get('translator');
        $subject = $translator->trans('subject.email.partner_mcpteam', array(), "messages", 'es');
        $contacts = $obj->getContacts();
        $name_contact = (count($contacts)) ? $contacts[0]->getName() : '';
        $phone_contact = (count($contacts)) ? $contacts[0]->getPhone() . ', ' . $contacts[0]->getMobile() : ' ';
        $email_contact = (count($contacts)) ? $contacts[0]->getEmail() : ' ';

        $content = '<p>' . $translator->trans('content.one.email.partner_mcpteam', array(), "messages", 'es') . ' '
            . $obj->getName() . ' ' . $translator->trans('content.two.email.partner_mcpteam', array(), "messages", 'es') . ' '
            . $obj->getEmail() . ' ' . $translator->trans('content.three.email.partner_mcpteam', array(), "messages", 'es')
            . ' (' . $obj->getPhone() . ',' . $obj->getPhoneAux() . ').</br> '
            . $translator->trans('content.four.email.partner_mcpteam', array(), "messages", 'es') . ' ' . $obj->getAddress()
            . $translator->trans('content.five.email.partner_mcpteam', array(), "messages", 'es') . ' ' . $obj->getCountry() . '.</br>'
            . $translator->trans('content.six.email.partner_mcpteam', array(), "messages", 'es') . ' '
            . $name_contact . ' '
            . $translator->trans('content.seven.email.partner_mcpteam', array(), "messages", 'es') . ' ' . $phone_contact
            . $translator->trans('content.heiht.email.partner_mcpteam', array(), "messages", 'es') . ' ' . $email_contact . '.</br>'
            . $translator->trans('content.nine.email.partner_mcpteam', array(), "messages", 'es') . '</p>';

        $service_email->sendTemplatedEmailPartner($subject, 'partner@mycasaparticular.com', 'reservation.partner@mycasaparticular.com', $content);

        return $user;
    }

    function getAll($filter_name = '', $filter_country = '', $filter_owner = '', $filter_email = '', $filter_date_created = '', $filter_package = '', $filter_active = '')
    {
        $condition = "";
        $join = "";


        if($filter_active != 'null' && $filter_active != '') {
            $condition .= "AND (us.user_enabled = :filter_active ";
            $condition .= ") ";
        }

        if($filter_country != 'null' && $filter_country != '') {
            $condition .= " AND co.co_id = :filter_country ";
        }
        if($filter_email != 'null' && $filter_email != '') {
            $condition .= " AND ag.email LIKE :filter_email ";
        }

        if($filter_date_created != 'null' && $filter_date_created != '') {
            $condition .= " AND ag.created LIKE :filter_date_created ";
        }

        if($filter_owner != 'null' && $filter_owner != '') {
            $condition .= " AND us.user_user_name LIKE :filter_owner ";
        }

        if($filter_package != 'null' && $filter_package != '') {
            $join .= "
            JOIN PartnerBundle:paAgencyPackage packAg WITH packAg.package = :filter_package AND packAg.travelAgency = ag.id
             ";
        }


        $em = $this->getEntityManager();
        $query = $em->createQuery("SELECT
          ag.id as id,
          ag.name as name,
          ag.email as contact_mail,
          us.user_user_name as touroperador,
          co.co_name as name_country,
          (SELECT MIN(pack.name) FROM PartnerBundle:paAgencyPackage packAgency
          JOIN PartnerBundle:paPackage pack WITH packAgency.package = pack.id
          WHERE packAgency.travelAgency = ag.id
          ORDER BY packAgency.datePayment DESC) as name_package,
          us.user_enabled as status,
          ag.created as date_register
        FROM PartnerBundle:paTravelAgency ag
        JOIN PartnerBundle:paTourOperator pat WITH ag.id = pat.travelAgency
        JOIN mycpBundle:user us WITH pat.tourOperator = us.user_id
        JOIN mycpBundle:country co WITH co.co_id = ag.country

        $join

        WHERE ag.name LIKE :filter_name $condition

        ");

        if(isset($filter_name)) {
            $query->setParameter('filter_name', "%" . $filter_name . "%");
        }


        if($filter_active != 'null' && $filter_active != '')
            $query->setParameter('filter_active', $filter_active);

        if($filter_country != 'null' && $filter_country != '')
            $query->setParameter('filter_country', $filter_country);


        if($filter_email != 'null' && $filter_email != '')
            $query->setParameter('filter_email', "%" . $filter_email . "%");

        if($filter_date_created != 'null' && $filter_date_created != '')
            $query->setParameter('filter_date_created', "%" . $filter_date_created . "%");

        if($filter_owner != 'null' && $filter_owner != '')
            $query->setParameter('filter_owner', "%" . $filter_owner . "%");

        if($filter_package != 'null' && $filter_package != '')
            $query->setParameter('filter_package', $filter_package);

        //dump($query->getDQL());die;

        return $query;
    }

    function getAllDeactive(){
        $qb = $this->createQueryBuilder('a');

        $qb->join('a.tourOperators', 'to');
        $qb->join('to.tourOperator', 'u');
        $qb->andWhere('u.user_enabled = 0');

        return $qb->getQuery()->execute();
    }

    public function getById($id)
    {

        $em = $this->getEntityManager();
        $query = $em->createQuery("SELECT
          ag.id as id,
          ag.name as name,
          ag.phone as ag_phone,
          ag.phoneAux as ag_phone_aux,
          ag.email as contact_mail,
          us.user_id as touroperador_id,
          us.user_user_name as touroperador,
          us.user_last_name as touroperador_last_name,
          us.user_email as user_email,
          us.user_phone as user_phone,
          co.co_name as name_country,
          contact.phone as contact_phone,
          contact.mobile as contact_mobile,
          (SELECT MIN(pack.name) FROM PartnerBundle:paAgencyPackage packAgency 
          JOIN PartnerBundle:paPackage pack WITH packAgency.package = pack.id 
          WHERE packAgency.travelAgency = ag.id 
          ORDER BY packAgency.datePayment DESC) as name_package,
          us.user_enabled as status,
          ag.created as date_register
        FROM PartnerBundle:paTravelAgency ag
        JOIN PartnerBundle:paTourOperator pat WITH ag.id = pat.travelAgency
        JOIN PartnerBundle:paContact contact WITH ag.id = contact.travelAgency
        JOIN mycpBundle:user us WITH pat.tourOperator = us.user_id
        JOIN mycpBundle:country co WITH co.co_id = ag.country
        
        WHERE ag.id = :filter_id
        
        ");

        $query->setParameter('filter_id', $id);

        return $query->getResult();
    }
}
