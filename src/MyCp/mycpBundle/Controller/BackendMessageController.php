<?php

namespace MyCp\mycpBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use MyCp\mycpBundle\Entity\message;
use Symfony\Component\Validator\Constraints\NotBlank;
use MyCp\mycpBundle\Helpers\BackendModuleName;

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

}
