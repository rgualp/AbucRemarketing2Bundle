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

    public function recommend2Friend($email_from, $name_from, $email_to) {
        $body=$this->container->get('templating')->render("frontEndBundle:mails:recommend2FriendMailBody.html.twig", array('from' => $name_from));
        $this->send_email($body, $email_from, $name_from, $email_to, $this->container->get('templating')->render("frontEndBundle:mails:recommend2FriendMailTemplate.html.twig", array('from' => $name_from)));
    }

    public function recommendProperty2Friend($email_from, $name_from, $email_to, $property) {
        /* remove after domain optimization. */
        $photo = $this->em->getRepository('mycpBundle:ownership')->get_ownership_photo($property->getOwnId());
        $body=$this->container->get('templating')->render("frontEndBundle:mails:recommendProperty2FriendMailBody.html.twig", array('from' => $name_from));
        $this->send_email($body, $email_from, $name_from, $email_to, $this->container->get('templating')->render("frontEndBundle:mails:recommendProperty2FriendMailTemplate.html.twig", array('from' => $name_from, 'property' => $property, 'photo' => $photo)));
    }

    public function recommendDestiny2Friend($email_from, $name_from, $email_to, $destiny) {
        /* remove after domain optimization. */
        $photo = $this->em->getRepository('mycpBundle:destination')->get_destination_photos($destiny->getDesId());
        if(isset($photo[0]))
        {
            $photo=$photo[0];
        }
        else
        {
            $photo="no_photo.png";
        }
        $this->send_email("", $email_from, $name_from, $email_to, $this->container->get('templating')->render("frontEndBundle:mails:recommendDestiny2FriendMailTemplate.html.twig", array('from' => $name_from, 'destiny' => $destiny, 'photo' => $photo)));
    }

    public function send_templated_email($subject, $email_from, $email_to, $content) {
        $templating = $this->container->get('templating');
        $body=$templating->render("frontEndBundle:mails:standardMailTemplate.html.twig", array('content' => $content));
        $this->send_email($subject, $email_from, "MyCasaParticular.com", $email_to,$body );
    }

    public function send_email($subject, $email_from, $name_from, $email_to, $sf_render) {
        if(is_object($sf_render)){
            $sf_render = $sf_render->getContent();
        }
        //echo $sf_render; exit();
        $message = Swift_Message::newInstance()
                ->setSubject($subject)
                ->setFrom($email_from, $name_from)
                ->setTo($email_to)
                ->setBody($sf_render, 'text/html');
        return $this->container->get('mailer')->send($message);
    }

}
