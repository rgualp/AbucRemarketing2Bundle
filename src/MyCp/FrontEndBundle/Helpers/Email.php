<?php

namespace MyCp\FrontEndBundle\Helpers;

use Swift_Message;
use MyCp\mycpBundle\Entity\generalReservation;

class Email {

    private $em; //remove after domain optimization.
    private $container;

    public function __construct($entity_manager, $container) {
        $this->em = $entity_manager; //remove after domain optimization.
        $this->container = $container;
    }

    // <editor-fold defaultstate="collapsed" desc="Recommend Mails">
    public function recommend2Friend($email_from, $name_from, $email_to) {
        $body = $this->container->get('templating')->render("FrontEndBundle:mails:recommend2FriendMailBody.html.twig", array('from' => $name_from));
        $this->sendEmail($body, $email_from, $name_from, $email_to, $this->container->get('templating')->render("FrontEndBundle:mails:recommend2FriendMailTemplate.html.twig", array('from' => $name_from)));
    }

    public function recommendProperty2Friend($email_from, $name_from, $email_to, $property) {
        /* remove after domain optimization. */
        $photo = $this->em->getRepository('mycpBundle:ownership')->get_ownership_photo($property->getOwnId());
        $body = $this->container->get('templating')->render("FrontEndBundle:mails:recommendProperty2FriendMailBody.html.twig", array('from' => $name_from));
        $this->sendEmail($body, $email_from, $name_from, $email_to, $this->container->get('templating')->render("FrontEndBundle:mails:recommendProperty2FriendMailTemplate.html.twig", array('from' => $name_from, 'property' => $property, 'photo' => $photo)));
    }

    public function recommendDestiny2Friend($email_from, $name_from, $email_to, $destiny) {
        /* remove after domain optimization. */
        $photo = $this->em->getRepository('mycpBundle:destination')->getAllPhotos($destiny->getDesId());
        if (isset($photo[0])) {
            $photo = $photo[0];
        } else {
            $photo = "no_photo.png";
        }
        $this->sendEmail("", $email_from, $name_from, $email_to, $this->container->get('templating')->render("FrontEndBundle:mails:recommendDestiny2FriendMailTemplate.html.twig", array('from' => $name_from, 'destiny' => $destiny, 'photo' => $photo)));
    }

// </editor-fold>
//-----------------------------------------------------------------------------

    public function sendTemplatedEmail($subject, $email_from, $email_to, $content) {
        $templating = $this->container->get('templating');
        $body = $templating->render("FrontEndBundle:mails:standardMailTemplate.html.twig", array('content' => $content));
        $this->sendEmail($subject, $email_from, "MyCasaParticular.com", $email_to, $body);
    }

    public function sendEmail($subject, $email_from, $name_from, $email_to, $sf_render, $attach = null) {
        if (is_object($sf_render)) {
            $sf_render = $sf_render->getContent();
        }
        //echo $sf_render; exit();
        $message = Swift_Message::newInstance()
                ->setSubject($subject)
                ->setFrom($email_from, $name_from)
                ->setTo($email_to)
                ->setBody($sf_render, 'text/html');
        if ($attach != null) {
            $message->attach(\Swift_Attachment::fromPath($attach));
        }
        return $this->container->get('mailer')->send($message);
    }

    public function sendReservation($id_reservation, $custom_message = null, $change_genres_status = true) {
        $templating = $this->container->get('templating');
        $reservation = $this->em->getRepository('mycpBundle:generalReservation')->find($id_reservation);

        if ($change_genres_status) {
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
            $array_dates = $service_time->datesBetween($res->getOwnResReservationFromDate()->getTimestamp(), $res->getOwnResReservationToDate()->getTimestamp());
            array_push($array_nigths, count($array_dates) - 1);
        }
        $user_locale = strtolower($user_tourist->getUserTouristLanguage()->getLangCode());

        $locale = $this->container->get('translator')->setLocale($user_locale);

        // Enviando mail al cliente

        $body = $templating->render('FrontEndBundle:mails:email_offer_available.html.twig', array(
            'user' => $user,
            'reservations' => $reservations,
            'photos' => $array_photos,
            'nights' => $array_nigths,
            'message' => $custom_message,
            'user_locale' => $user_locale,
            'user_currency' => ($user_tourist != null) ? $user_tourist->getUserTouristCurrency() : null,
            'reservationStatus' => $reservation->getGenResStatus()
        ));
        $locale = $this->container->get('translator');
        $subject = $locale->trans('REQUEST_STATUS_CHANGED', array(), "messages", $user_locale);

        $this->sendEmail(
                $subject, 'reservation@mycasaparticular.com', 'MyCasaParticular.com', $user->getUserEmail(), $body
        );
    }


    public function sendOwnersMail($email_to, $owners_name, $own_name, $own_mycp_code) {
        $templating = $this->container->get('templating');

        if (!isset($email_to) || $email_to == "")
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

    public function sendCreateUserCasaMail($email_to, $userName, $userFullName, $secret_token, $own_mycp_code, $own_name) {
        $templating = $this->container->get('templating');

        if (!isset($email_to) || $email_to == "")
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

}
