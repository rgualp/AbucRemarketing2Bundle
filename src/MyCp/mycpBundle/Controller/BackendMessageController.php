<?php

namespace MyCp\mycpBundle\Controller;

use MyCp\mycpBundle\Helpers\BackendModuleName;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use MyCp\mycpBundle\Entity\message;

class BackendMessageController extends Controller {

    public function messageControlAction($userTourist, $showSubject = false)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $commentsTotal = $em->getRepository("mycpBundle:clientComment")->getCommentsTotal($userTourist->getUserTouristUser());

        return $this->render('mycpBundle:message:messages.html.twig', array(
            'userTourist' => $userTourist,
            'userLogged' => $user,
            'showSubject' => $showSubject,
            'commentsTotal' => $commentsTotal
        ));
    }

    public function messageUserControlAction($userTo, $showSubject = false)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $commentsTotal = $em->getRepository("mycpBundle:clientComment")->getCommentsTotal($userTo);

        return $this->render('mycpBundle:message:messages_user.html.twig', array(
            'user' => $userTo,
            'userLogged' => $user,
            'showSubject' => $showSubject,
            'commentsTotal' => $commentsTotal
        ));
    }

    public function sendAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $touristId = $request->get("touristId");
        $userTourist = isset($touristId) ? $em->getRepository("mycpBundle:userTourist")->find($touristId) : null;
        $from = $this->getUser();
        $userId = $request->get("userId");
        $to = $em->getRepository("mycpBundle:user")->find($userId);
        $subject = $request->get("subject");
        $messageBody = $request->get("messageBody");

        //Create message object
        $em->getRepository("mycpBundle:message")->insert($from, $to, $subject, $messageBody);
        $service_log= $this->get('log');
        $service_log->saveLog('Insert client message',  BackendModuleName::MODULE_CLIENT_MESSAGES);


        //Send message to user email
        $serviceEmail = $this->get('mycp.service.email_manager');
        $userLocale = isset($touristId) ?  $userTourist->getUserTouristLanguage()->getLangCode() : $to->getUserLanguage()->getLangCode();
        $templateBody = $this->render('FrontEndBundle:mails:standardTemplatedMailTemplate.html.twig', array(
                'content' => $messageBody,
                'user_locale' => $userLocale
            ));

        $serviceEmail->sendEmail($to->getUserEmail(), $subject . ' - MyCasaParticular.com', $templateBody);
        $commentsTotal = $em->getRepository("mycpBundle:clientComment")->getCommentsTotal(isset($touristId) ? $userTourist->getUserTouristUser() : $to);

        $data = isset($touristId) ? $this->render('mycpBundle:message:messages.html.twig', array(
            'userTourist' => $userTourist,
            'userLogged' => $from,
            'commentsTotal' => $commentsTotal
        ))->getContent() : $this->render('mycpBundle:message:messages_user.html.twig', array(
            'user' => $to,
            'userLogged' => $from,
            'commentsTotal' => $commentsTotal
        ));;

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

    public function listCommentsAction(Request $request)
    {
        $userId = $request->get("userId");
        $data = $this->getCommentList($userId);
        return new Response($data, 200);
    }

    public function insertClientCommentAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $staffUser = $this->getUser();
        $userId = $request->get("userId");
        $clientUser = $em->getRepository("mycpBundle:user")->find($userId);
        $commentText = $request->get("commentText");


        //Insert comment
        $totalEqualComments = count($em->getRepository("mycpBundle:clientComment")->findBy(array("comment_client_user" => $userId, "comment_text" => $commentText)));

        if($totalEqualComments == 0) {
            $em->getRepository("mycpBundle:clientComment")->insert($clientUser, $staffUser, $commentText);
            $service_log = $this->get('log');
            $service_log->saveLog('Insert client comment', BackendModuleName::MODULE_CLIENT_COMMENTS);
        }

        $data = $this->getCommentList($userId);
        return new Response($data, 200);
    }

    private function getCommentList($userId)
    {
        $em = $this->getDoctrine()->getManager();

        $comments = $em->getRepository("mycpBundle:clientComment")->findBy(array("comment_client_user" => $userId), array("comment_date" => "DESC"));

        $data = $this->render('mycpBundle:message:clientComments.html.twig', array(
            'comments' => $comments
        ))->getContent();

        return $data;
    }

}
