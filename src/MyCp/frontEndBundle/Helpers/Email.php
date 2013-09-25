<?php

namespace MyCp\frontEndBundle\Helpers;

use Swift_Message;

class Email {

    private $container;

    public function __construct($container) {
        $this->container = $container;
    }

    public function recommend2Friend($email_from, $name_from, $email_to) {
        $this->send_email("$name_from te sugiere visitar mycasaparticular.com", $email_from, $name_from, $email_to, $this->container->get('templating')->render("frontEndBundle:mails:recommend2FriendMailTemplate.html.twig", array('from' => $name_from)));
    }

    public function recommendProperty2Friend($email_from, $name_from, $email_to, $property) {
        $this->send_email("$name_from desea sugerirte un alojamiento en mycasaparticular.com", $email_from, $name_from, $email_to, $this->container->get('templating')->render("frontEndBundle:mails:recommendProperty2FriendMailTemplate.html.twig", array('from' => $name_from, 'property' => $property)));
    }

    public function recommendDestiny2Friend($email_from, $name_from, $email_to, $destiny) {
        $this->send_email("$name_from desea sugerirte un destino en mycasaparticular.com", $email_from, $name_from, $email_to, $this->container->get('templating')->render("frontEndBundle:mails:recommendDestiny2FriendMailTemplate.html.twig", array('from' => $name_from, 'destiny' => $destiny)));
    }

    public function send_templated_email($subject, $email_from, $email_to, $content) {
        $templating = $this->container->get('templating');
        $this->send_email($subject, $email_from, "MyCasaParticular.com", $email_to, $templating->render("frontEndBundle:mails:standardMailTemplate.html.twig", array('content' => $content)));
    }

    public function send_email($subject, $email_from, $name_from, $email_to, $sf_render) {
        $message = Swift_Message::newInstance()
                ->setSubject($subject)
                ->setFrom($email_from, $name_from)
                ->setTo($email_to)
                ->setBody($sf_render->getContent(), 'text/html');
        return $this->container->get('mailer')->send($message);
    }

}
