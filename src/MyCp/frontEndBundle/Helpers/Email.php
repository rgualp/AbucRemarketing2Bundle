<?php

namespace MyCp\frontEndBundle\Helpers;

use Swift_Message;

class Email {

    private $em; //remove after domain optimization.
    private $container;

    public function __construct($entity_manager, $container) {
        $this->em = $entity_manager; //remove after domain optimization.
        $this->container = $container;
    }

    // <editor-fold defaultstate="collapsed" desc="Recommend Mails">
    public function recommend2Friend($email_from, $name_from, $email_to) {
        $body = $this->container->get('templating')->render("frontEndBundle:mails:recommend2FriendMailBody.html.twig", array('from' => $name_from));
        $this->send_email($body, $email_from, $name_from, $email_to, $this->container->get('templating')->render("frontEndBundle:mails:recommend2FriendMailTemplate.html.twig", array('from' => $name_from)));
    }

    public function recommendProperty2Friend($email_from, $name_from, $email_to, $property) {
        /* remove after domain optimization. */
        $photo = $this->em->getRepository('mycpBundle:ownership')->get_ownership_photo($property->getOwnId());
        $body = $this->container->get('templating')->render("frontEndBundle:mails:recommendProperty2FriendMailBody.html.twig", array('from' => $name_from));
        $this->send_email($body, $email_from, $name_from, $email_to, $this->container->get('templating')->render("frontEndBundle:mails:recommendProperty2FriendMailTemplate.html.twig", array('from' => $name_from, 'property' => $property, 'photo' => $photo)));
    }

    public function recommendDestiny2Friend($email_from, $name_from, $email_to, $destiny) {
        /* remove after domain optimization. */
        $photo = $this->em->getRepository('mycpBundle:destination')->get_destination_photos($destiny->getDesId());
        if (isset($photo[0])) {
            $photo = $photo[0];
        } else {
            $photo = "no_photo.png";
        }
        $this->send_email("", $email_from, $name_from, $email_to, $this->container->get('templating')->render("frontEndBundle:mails:recommendDestiny2FriendMailTemplate.html.twig", array('from' => $name_from, 'destiny' => $destiny, 'photo' => $photo)));
    }

// </editor-fold>
//-----------------------------------------------------------------------------

    public function send_templated_email($subject, $email_from, $email_to, $content) {
        $templating = $this->container->get('templating');
        $body = $templating->render("frontEndBundle:mails:standardMailTemplate.html.twig", array('content' => $content));
        $this->send_email($subject, $email_from, "MyCasaParticular.com", $email_to, $body);
    }

    public function send_email($subject, $email_from, $name_from, $email_to, $sf_render,$attach=null) {
        if (is_object($sf_render)) {
            $sf_render = $sf_render->getContent();
        }
        //echo $sf_render; exit();
        $message = Swift_Message::newInstance()
                ->setSubject($subject)
                ->setFrom($email_from, $name_from)
                ->setTo($email_to)
                ->setBody($sf_render, 'text/html');
        if($attach!=null)
        {
            $message->attach(\Swift_Attachment::fromPath($attach));
        }
        return $this->container->get('mailer')->send($message);
    }

    public function send_reservation($id_reservation)
    {
        $templating = $this->container->get('templating');
        $reservation=$this->em->getRepository('mycpBundle:generalReservation')->find($id_reservation);
        $reservation->setGenResStatus(1);
        $reservation->setGenResStatusDate(new \DateTime(date('Y-m-d')));
        $reservation->setGenResHour(date('G'));
        $this->em->persist($reservation);
        $this->em->flush();
        $reservations=$this->em->getRepository('mycpBundle:ownershipReservation')->findBy(array('own_res_gen_res_id'=>$id_reservation));
        $user=$reservation->getGenResUserId();
        $user_tourist=$this->em->getRepository('mycpBundle:userTourist')->findBy(array('user_tourist_user'=>$user->getUserId()));
        $array_photos=array();
        $array_nigths=array();
        $service_time=$this->container->get('time');

        foreach($reservations as $res)
        {
            $photos=$this->em->getRepository('mycpBundle:ownership')->getPhotos($res->getOwnResGenResId()->getGenResOwnId()->getOwnId());
            array_push($array_photos,$photos);
            $array_dates= $service_time->dates_between($res->getOwnResReservationFromDate()->getTimestamp(),$res->getOwnResReservationToDate()->getTimestamp());
            array_push($array_nigths,count($array_dates));
        }
        $this->container->get('translator')->setLocale($user_tourist[0]->getUserTouristLanguage()->getLangCode());
        $message = $this->container->get('request')->get('message_to_client');
        if(isset($message[0]))
            $message[0]=strtoupper($message[0]);

        // Enviando mail al cliente
        $body=$templating->render('frontEndBundle:mails:email_offer_available.html.twig',array(
            'user'=>$user,
            'reservations'=>$reservations,
            'photos'=>$array_photos,
            'nights'=>$array_nigths,
            'message'=>$message
        ));

        $locale = $this->container->get('translator');
        $subject=$locale->trans('REQUEST_STATUS_CHANGED');

        $this->send_email(
            $subject,
            'reservation@mycasaparticular.com',
            'MyCasaParticular.com',
            $user->getUserEmail(),
            $body
        );
    }

}
