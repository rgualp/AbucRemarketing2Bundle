<?php
namespace MyCp\frontEndBundle\Helpers;
use Symfony\Component\HttpFoundation\Request;


class Email
{
    private $container;

    public function __construct($container)
    {
        $this->container = $container;
    }

    public function send_email($subject,$from,$to,$title,$body)
    {
        $templating=$this->container->get('templating');
        $message = \Swift_Message::newInstance()
            ->setSubject($subject)
            ->setFrom($from)
            ->setTo($to)
            ->setBody($templating->render('frontEndBundle:utils:mailTemplate.html.twig',array('title' =>$title,'content' =>$body)), 'text/html'
        )
        ;
        $this->container->get('mailer')->send($message);
    }

}