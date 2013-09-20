<?php

namespace MyCp\frontEndBundle\Helpers;

use Swift_Message;

class Email {

    private $container;

    public function __construct($container) {
        $this->container = $container;
    }

    public function send_email($subject, $from, $to, $body) {
        $templating = $this->container->get('templating');
        $message = Swift_Message::newInstance()
                ->setSubject($subject)
                ->setFrom($from)
                ->setTo($to)
                ->setBody($templating->render("frontEndBundle:mails:standardMailTemplate.html.twig", array('content' => $body)), 'text/html');
        return $this->container->get('mailer')->send($message);
    }

    public function recommend2Friend($name_from, $email_to) {
        $templating = $this->container->get('templating');
        $message = Swift_Message::newInstance()
                ->setSubject("$name_from te sugiere visitar mycasaparticular.com")
                ->setFrom('noreply@mycasaparticular.com', "mycasaparticular.com")
                ->setTo($email_to)
                ->setBody($templating->render("frontEndBundle:mails:recommend2FriendMailTemplate.html.twig", array('from' => $name_from)), 'text/html');
        return $this->container->get('mailer')->send($message);
    }

}