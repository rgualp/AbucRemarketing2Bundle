<?php

namespace MyCp\mycpBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use MyCp\mycpBundle\Entity\message;

class BackendMessageController extends Controller {

    public function messageControlAction($userTourist, $showSubject = false)
    {
        $user = $this->getUser();

        return $this->render('mycpBundle:message:messages.html.twig', array(
            'userTourist' => $userTourist,
            'userLogged' => $user,
            'showSubject' => $showSubject
        ));
    }

    public function sendAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $userTourist = $em->getRepository("mycpBundle:userTourist")->find($request->get("touristId"));
        $from = $this->getUser();
        $userId = $request->get("userId");
        $to = $em->getRepository("mycpBundle:user")->find($userId);
        $subject = $request->get("subject");
        $messageBody = $request->get("messageBody");

        //Create message object
        $em->getRepository("mycpBundle:message")->insert($from, $to, $subject, $messageBody);


        //Send message to user email
        $serviceEmail = $this->get('mycp.service.email_manager');
        $userLocale = $userTourist->getUserTouristLanguage()->getLangCode();
        $templateBody = $this->render('FrontEndBundle:mails:standardTemplatedMailTemplate.html.twig', array(
                'content' => $messageBody,
                'user_locale' => $userLocale
            ));

        $serviceEmail->sendEmail($to->getUserEmail(), $subject . ' - MyCasaParticular.com', $templateBody);

        $data = $this->render('mycpBundle:message:messages.html.twig', array(
            'userTourist' => $userTourist,
            'userLogged' => $from
        ))->getContent();

        return new Response($data,200);
    }

    public function listAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $userId = $request->get("userId");
        $messages = $em->getRepository("mycpBundle:message")->findBy(array("message_send_to" => $userId), array("message_date" => "DESC"));

        $data = $this->render('mycpBundle:message:clientMessagesList.html.twig', array(
            'messages' => $messages
        ))->getContent();

        return new Response($data, 200);
    }

}
