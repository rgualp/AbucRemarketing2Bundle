<?php

namespace MyCp\FrontEndBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

class NotificationController extends Controller {

    /**
     * @return Response
     */
    public function countNotificationtItemsAction() {
        return $this->render('FrontEndBundle:notification:notificationCountItems.html.twig', array(
                    'count' => 0
        ));
    }
    public function getNotificationBodyAction(){
        return $this->render('FrontEndBundle:notification:notificationCountItems.html.twig', array(
                'count' => 0
            ));
    }
}
