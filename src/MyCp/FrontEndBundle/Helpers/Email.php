<?php

namespace MyCp\FrontEndBundle\Helpers;

use Swift_Message;
use MyCp\mycpBundle\Entity\generalReservation;

class Email
{

    private $em; //remove after domain optimization.
    private $container;
    private $defaultLanguageCode;

    public function __construct($entity_manager, $container, $defaultLanguageCode)
    {
        $this->em = $entity_manager; //remove after domain optimization.
        $this->container = $container;
        $this->defaultLanguageCode = $defaultLanguageCode;
    }

    // <editor-fold defaultstate="collapsed" desc="Recommend Mails">
    public function recommend2Friend($email_from, $name_from, $email_to)
    {
        $body = $this->container->get('templating')->render("FrontEndBundle:mails:recommend2FriendMailBody.html.twig", array('from' => $name_from));
        $this->sendEmail("MyCasaParticular", $email_from, $name_from, $email_to, $body);
    }

    public function recommendProperty2Friend($email_from, $name_from, $email_to, $property)
    {
        /* remove after domain optimization. */
        $photo = $this->em->getRepository('mycpBundle:ownership')->getOwnershipPhoto($property->getOwnId());
        $body = $this->container->get('templating')->render("FrontEndBundle:mails:recommendProperty2FriendMailBody.html.twig", array('from' => $name_from));
        $this->sendEmail("MyCasaParticular", $email_from, $name_from, $email_to, $this->container->get('templating')->render("FrontEndBundle:mails:recommendProperty2FriendMailTemplate.html.twig", array('from' => $name_from, 'property' => $property, 'photo' => $photo)));
    }

    public function recommendDestiny2Friend($email_from, $name_from, $email_to, $destiny)
    {
        /* remove after domain optimization. */
        $photo = $this->em->getRepository('mycpBundle:destination')->getAllPhotos($destiny->getDesId());
        if(isset($photo[0])) {
            $photo = $photo[0];
        }
        else {
            $photo = "no_photo.png";
        }
        $this->sendEmail("MyCasaParticular", $email_from, $name_from, $email_to, $this->container->get('templating')->render("FrontEndBundle:mails:recommendDestiny2FriendMailTemplate.html.twig", array('from' => $name_from, 'destiny' => $destiny, 'photo' => $photo)));
    }

// </editor-fold>
//-----------------------------------------------------------------------------
    public function sendTemplatedEmail($subject, $email_from, $email_to, $content)
    {
        $templating = $this->container->get('templating');
        $body = $templating->render("FrontEndBundle:mails:standardMailTemplate.html.twig", array('content' => $content));
        $this->sendEmail($subject, $email_from, "MyCasaParticular.com", $email_to, $body);
    }

    /**
     * @param $subject
     * @param $email_from
     * @param $email_to
     * @param $content
     */
    public function sendTemplatedEmailPartner($subject, $email_from, $email_to, $content)
    {
        $templating = $this->container->get('templating');
        $body = $templating->render("PartnerBundle:Mail:standardMailTemplate.html.twig", array('content' => $content));
        $this->sendEmail($subject, $email_from, "MyCasaParticular.com", $email_to, $body);
    }

    public function sendEmail($subject, $email_from, $name_from, $email_to, $sf_render, $attach = null)
    {
        if(is_object($sf_render)) {
            $sf_render = $sf_render->getContent();
        }
        //echo $sf_render; exit();
        $message = Swift_Message::newInstance()
            ->setSubject($subject)
            ->setFrom($email_from, $name_from)
            ->setTo($email_to)
            ->setBody($sf_render, 'text/html');
        if($attach != null) {
            $message->attach(\Swift_Attachment::fromPath($attach));
        }
        return $this->container->get('mailer')->send($message);
    }

    public function sendReservation($id_reservation, $custom_message = null, $change_genres_status = false, $isANewOffer = false)
    {
        $templating = $this->container->get('templating');
        $reservation = $this->em->getRepository('mycpBundle:generalReservation')->find($id_reservation);

        if($change_genres_status) {
            $reservation->setGenResStatus(generalReservation::STATUS_AVAILABLE);
            $reservation->setGenResStatusDate(new \DateTime(date('Y-m-d')));
            $reservation->setGenResHour(date('G'));
            $this->em->persist($reservation);
            $this->em->flush();
        }
        $reservations = $this->em->getRepository('mycpBundle:ownershipReservation')->findBy(array('own_res_gen_res_id' => $id_reservation));
        $user = $reservation->getGenResUserId();
        $user_tourist = $this->em->getRepository('mycpBundle:userTourist')->findOneBy(array('user_tourist_user' => $user->getUserId()));
        $array_photos = array();
        $array_nigths = array();
        $service_time = $this->container->get('time');

        foreach ($reservations as $res) {
            $photos = $this->em->getRepository('mycpBundle:ownership')->getPhotos($res->getOwnResGenResId()->getGenResOwnId()->getOwnId());
            array_push($array_photos, $photos);
            $totalNights = $service_time->nights($res->getOwnResReservationFromDate()->getTimestamp(), $res->getOwnResReservationToDate()->getTimestamp());
            array_push($array_nigths, $totalNights);
        }
        $touristLanguage = ($user_tourist != null) ? $user_tourist->getUserTouristLanguage() : $user->getUserLanguage();
        $user_locale = (isset($touristLanguage)) ? strtolower($touristLanguage->getLangCode()) : strtolower($this->defaultLanguageCode);

        $locale = $this->container->get('translator');
        $subject = $locale->trans('REQUEST_STATUS_CHANGED', array(), "messages", $user_locale);
        // Enviando mail al cliente
        if($user->getUserRole()=="ROLE_CLIENT_PARTNER"){
            $body = $templating->render('PartnerBundle:Mail:email_offer_available.html.twig', array(
                    'user' => $user,
                    'reservations' => $reservations,
                    'photos' => $array_photos,
                    'nights' => $array_nigths,
                    'message' => $custom_message,
                    'user_locale' => $user_locale,
                    'user_currency' => ($user_tourist != null) ? $user_tourist->getUserTouristCurrency() : $user->getUserLanguage(),
                    'reservationStatus' => $reservation->getGenResStatus()
                ));
        }
        else{

            if($isANewOffer)
            {
                $subject = $locale->trans('NEW_OFFER_TOURIST_SUBJECT', array(), "messages", $user_locale);

                $body = $templating->render('FrontEndBundle:mails:email_new_offer_available.html.twig', array(
                    'user' => $user,
                    'reservations' => $reservations,
                    'photos' => $array_photos,
                    'nights' => $array_nigths,
                    'message' => $custom_message,
                    'user_locale' => $user_locale,
                    'user_currency' => ($user_tourist != null) ? $user_tourist->getUserTouristCurrency() : $user->getUserLanguage(),
                    'reservationStatus' => $reservation->getGenResStatus()
                ));
            }
            else {
                $body = $templating->render('FrontEndBundle:mails:email_offer_available.html.twig', array(
                    'user' => $user,
                    'reservations' => $reservations,
                    'photos' => $array_photos,
                    'nights' => $array_nigths,
                    'message' => $custom_message,
                    'user_locale' => $user_locale,
                    'user_currency' => ($user_tourist != null) ? $user_tourist->getUserTouristCurrency() : $user->getUserLanguage(),
                    'reservationStatus' => $reservation->getGenResStatus()
                ));
            }

        }

        $this->sendEmail(
            $subject, 'reservation@mycasaparticular.com', 'MyCasaParticular.com', $user->getUserEmail(), $body
        );
    }

    public function sendOwnersMail($email_to, $owners_name, $own_name, $own_mycp_code)
    {
        $templating = $this->container->get('templating');

        if(!isset($email_to) || $email_to == "")
            throw new \InvalidArgumentException("The email to can not be empty");

        $content = $templating->render('FrontEndBundle:mails:ownersMailBody.html.twig', array(
            'owners_name' => $owners_name,
            'own_name' => $own_name,
            'own_mycp_code' => $own_mycp_code
        ));

        $this->sendEmail(
            "Bienvenido a MyCasaParticular", 'casa@mycasaparticular.com', 'MyCasaParticular.com', $email_to, $content
        );
    }

    public function sendCreateUserCasaMail($email_to, $userName, $userFullName, $secret_token, $own_mycp_code, $own_name)
    {
        $templating = $this->container->get('templating');

        if(!isset($email_to) || $email_to == "")
            throw new \InvalidArgumentException("The email to can not be empty");

        $content = $templating->render('FrontEndBundle:mails:createUserCasaMailBody.html.twig', array(
            'user_name' => $userName,
            'user_full_name' => $userFullName,
            'own_name' => $own_name,
            'own_mycp_code' => $own_mycp_code,
            'secret_token' => $secret_token
        ));

        $this->sendEmail(
            "Creación de cuenta de usuario", 'casa@mycasaparticular.com', 'MyCasaParticular.com', $email_to, $content
        );
    }

    public function sendCreateUserCasaMailNew($email_to, $userName, $userFullName, $secret_token, $own_mycp_code, $own_name)
    {
        $templating = $this->container->get('templating');
        if(!isset($email_to) || $email_to == "")
            throw new \InvalidArgumentException("The email to can not be empty");

        $content = $templating->render('FrontEndBundle:mails:createUserCasaNewMailBody.html.twig', array(
            'user_name' => $userName,
            'user_full_name' => $userFullName,
            'own_name' => $own_name,
            'own_mycp_code' => $own_mycp_code,
            'secret_token' => $secret_token
        ));

        $this->sendEmail(
            "Active su cuenta en MyCasaParticular.com", 'casa@mycasaparticular.com', 'MyCasaParticular.com', $email_to, $content
        );
    }

    /**
     * @param $email_to
     * @param $userName
     * @param $userFullName
     * @param $secret_token
     * @param string $user_locale
     * @param $agency
     * @param bool $beta
     * @internal param $own_mycp_code
     * @internal param $own_name
     */
    public function sendCreateUserPartner($email_to, $userName, $userFullName, $secret_token, $user_locale = 'es', $agency, $beta = null)
    {
        $locale = $this->container->get('translator');
        $subject = $locale->trans('label.email.registeraccount.text.seven', array(), "messages", $user_locale);

        $templating = $this->container->get('templating');
        if(!isset($email_to) || $email_to == "")
            throw new \InvalidArgumentException("The email to can not be empty");

        $twig = 'PartnerBundle:Mail:createUserPartner.html.twig';
        if($beta === true){
            $twig = 'PartnerBundle:Mail:createUserPartnerBeta.html.twig';
        }
        else if($beta === false){
            $twig = 'PartnerBundle:Mail:createUserPartnerRelease.html.twig';
        }

        $content = $templating->render($twig, array(
            'user_name' => $userName,
            'user_full_name' => $userFullName,
            'secret_token' => $secret_token,
            'user_locale' => $user_locale,
            'agency' => $agency
        ));

        $this->sendEmail("$subject", 'partner@mycasaparticular.com', 'MyCasaParticular.com', $email_to, $content);
    }

    public function sendCreateUserCasaMailCommand($user_casa, $ownership)
    {
        $user = $user_casa->getUserCasaUser();
        $user_fullname = trim($user->getUserUserName() . ' ' . $user->getUserLastName());
        $email_to = $user->getUserEmail();

        $templating = $this->container->get('templating');

        if(!isset($email_to) || $email_to == "")
            throw new \InvalidArgumentException("The email to can not be empty");

        $data = array();
        $data['user_name'] = $user->getUserName();
        $data['user_full_name'] = $user_fullname;
        $data['own_name'] = $ownership->getOwnName();
        $data['own_mycp_code'] = $ownership->getOwnMcpCode();
        $data['secret_token'] = $user_casa->getUserCasaSecretToken();
        $data['user_locale'] = 'es';

        $content = $templating->render('FrontEndBundle:mails:createUserCasaMailBodyCommand.html.twig', $data);
//        $email_to= 'arieskienmendoza@gmail.com';
        $subject = 'MyCasaParticular.com brinda nuevas oportunidades para sus clientes';
        $this->sendEmail($subject, 'no_responder@mycasaparticular.com', 'MyCasaParticular.com', $email_to, $content);
    }

    public function sendCreateUserCasaMailApologiesCommand($user_casa, $ownership)
    {
        $user = $user_casa->getUserCasaUser();
        $user_fullname = trim($user->getUserUserName() . ' ' . $user->getUserLastName());
        $email_to = $user->getUserEmail();

        $templating = $this->container->get('templating');

        if(!isset($email_to) || $email_to == "")
            throw new \InvalidArgumentException("The email to can not be empty");

        $data = array();
        $data['user_name'] = $user->getUserName();
        $data['user_full_name'] = $user_fullname;
        $data['own_name'] = $ownership->getOwnName();
        $data['own_mycp_code'] = $ownership->getOwnMcpCode();
        $data['secret_token'] = $user_casa->getUserCasaSecretToken();
        $data['user_locale'] = 'es';

        $content = $templating->render('FrontEndBundle:mails:createUserCasaApologiesMailBodyCommand.html.twig', $data);
//        $email_to= 'arieskienmendoza@gmail.com';
        $subject = 'MyCasaParticular.com brinda nuevas oportunidades para sus clientes';
        $this->sendEmail($subject, 'no_responder@mycasaparticular.com', 'MyCasaParticular.com', $email_to, $content);
    }

    public function sendInfoCasaRentaCommand($user_casa)
    {
        $user = $user_casa->getUserCasaUser();
        $user_fullname = trim($user->getUserUserName() . ' ' . $user->getUserLastName());
        $email_to = $user->getUserEmail();

        if(!isset($email_to) || $email_to == "")
            throw new \InvalidArgumentException("The email to can not be empty");

        $data = array();
        $data['user_name'] = $user->getUserName();
        $data['user_full_name'] = $user_fullname;
        $data['secret_token'] = $user_casa->getUserCasaSecretToken();
        $data['user_locale'] = 'es';

        $templating = $this->container->get('templating');
        $content = $templating->render('FrontEndBundle:mails:infoCasaRentaCommand.html.twig', $data);
        $subject = 'Como usar MyCasa Renta';
        $this->sendEmail($subject, 'no_responder@mycasaparticular.com', 'MyCasaParticular.com', $email_to, $content);
    }

    public function notificationSmsCheckIn($users)
    {
        $data = array();
        $data['user_locale'] = 'es';
        $email_manager = $this->container->get('mycp.service.email_manager');
        $content = $email_manager->getViewContent('FrontEndBundle:mails:notificacionSmsCheckInCommand.html.twig', $data);

        foreach ($users as $email) {
            $message = Swift_Message::newInstance()
                ->setSubject('Nuevo servicio de MyCasaParticular')
                ->setFrom('no_responder@mycasaparticular.com', 'MyCasaParticular.com')
                ->setBcc($email)
                ->setBody($content, 'text/html');
            $this->container->get('mailer')->send($message);
        }
    }

    public function sendCasaModulePublishAccommodation($user)
    {
        $user_fullname = trim($user->getUserUserName() . ' ' . $user->getUserLastName());
        $email_to = $user->getUserEmail();

        if(!isset($email_to) || $email_to == "")
            throw new \InvalidArgumentException("The email to can not be empty");

        $data = array();
        $data['user_locale'] = 'es';

        $templating = $this->container->get('templating');
        $content = $templating->render('MyCpCasaModuleBundle:mail:publish_accommodation.html.twig', $data);
        $subject = 'Bienvenido a MyCasaParticular';
        $this->sendEmail($subject, 'no_responder@mycasaparticular.com', 'MyCasaParticular.com', $email_to, $content);
    }

}
