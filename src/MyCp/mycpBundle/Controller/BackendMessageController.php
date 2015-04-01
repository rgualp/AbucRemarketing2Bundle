<?php

namespace MyCp\mycpBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use MyCp\mycpBundle\Entity\message;

class BackendMessageController extends Controller {

    public function messageControlAction($userTourist)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $messages = $em->getRepository("mycpBundle:message")->findBy(array("message_send_to" => $userTourist->getUserTouristUser()->getUserId()), array("message_date" => "DESC"));

        return $this->render('mycpBundle:message:messages.html.twig', array(
            'userTourist' => $userTourist,
            'userLogged' => $user,
            'messages' => $messages
        ));
    }

    public function sendAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $userTourist = $em->getRepository("mycpBundle:userTourist")->find($request->get("touristId"));
        $sender = $this->getUser();
        $userId = $request->get("userId");
        $sendTo = $em->getRepository("mycpBundle:user")->find($userId);
        $subject = $request->get("subject");
        $messageBody = $request->get("messageBody");

        //Create message object
        $message = new message();
        $message->setMessageBody($messageBody);
        $message->setMessageDate(new \DateTime());
        $message->setMessageSendTo($sendTo);
        $message->setMessageSender($sender);
        $message->setMessageSubject($subject);


        //Send message to user email
        $serviceEmail = $this->get('Email');
        $userLocale = $userTourist->getUserTouristLanguage()->getLangCode();
        $templateBody = $this->render('FrontEndBundle:mails:standardTemplatedMailTemplate.html.twig', array(
                'content' => $message->getMessageBody(),
                'user_locale' => $userLocale
            ));

            $serviceEmail->sendEmail(
                    $message->getMessageSubject(),
                    'reservation1@mycasaparticular.com',
                    $message->getMessageSubject() . ' - MyCasaParticular.com', $userTourist->getUserTouristUser()->getUserEmail(), $templateBody
            );

        //Save message to database
        $em->persist($message);
        $em->flush();

        $messages = $em->getRepository("mycpBundle:message")->findBy(array("message_send_to" => $userId), array("message_date" => "DESC"));

        $data = $this->render('mycpBundle:message:messages.html.twig', array(
            'userTourist' => $userTourist,
            'userLogged' => $sender,
            'messages' => $messages
        ));

        return new Response($data,200);
    }

}
