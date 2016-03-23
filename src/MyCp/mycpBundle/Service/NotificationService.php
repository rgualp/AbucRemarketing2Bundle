<?php

namespace MyCp\mycpBundle\Service;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManager;
use MyCp\FrontEndBundle\Helpers\ReservationHelper;
use MyCp\FrontEndBundle\Helpers\Time;
use MyCp\mycpBundle\Entity\booking;
use MyCp\mycpBundle\Entity\generalReservation;
use MyCp\mycpBundle\Entity\lang;
use MyCp\mycpBundle\Entity\ownership;
use MyCp\mycpBundle\Entity\ownershipDescriptionLang;
use MyCp\mycpBundle\Entity\ownershipReservation;
use MyCp\mycpBundle\Entity\user;
use MyCp\mycpBundle\Helpers\BackendModuleName;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class NotificationService extends Controller
{
    /**
     * @var ObjectManager
     */
    private $em;
    private $serviceNotificationUrl;

    public function __construct(ObjectManager $em, $serviceNotificationUrl)
    {
        $this->em = $em;
        $this->serviceNotificationUrl = $serviceNotificationUrl;
    }

    public function sendNotification(generalReservation $reservation)
    {
        $accommodation = $reservation->getGenResOwnId();
        // http://sms.lvps92-51-151-239.dedicated.hosteurope.de
        //$url= 'http://notification.dev/app_dev.php/api/reservation/add';

        if($accommodation->getOwnMobileNumber() != null  && $accommodation->getOwnMobileNumber() != "") {
            $email = $accommodation->getOwnEmail1();
            $email = ($email == null || $email == "") ? $accommodation->getOwnEmail2(): "";

            $owner = $accommodation->getOwnHomeowner1();
            $owner = ($owner == null || $owner == "") ? $accommodation->getOwnHomeowner2(): "";

            $data['reservation_formtype'] = array(
                'code' => $reservation->getCASId(),//Obligatorio
                'cell_number' => $accommodation->getOwnMobileNumber(),//8 digitos, comenzando con 5
                'email' => $email,//No obligatorio
                'owner' => $owner,//Obligatorio
                'client' => $reservation->getGenResUserId()->getUserCompleteName(),//Obligatorio
                'reservation_created' => $reservation->getGenResDate(),//Obligatorio, Fecha que se realizo la reserva
            );

            $curl = curl_init($this->serviceNotificationUrl);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
            $response = curl_exec($curl);
            $info = curl_getinfo($curl);
            curl_close($curl);
            $code = $info['http_code'];
        }
    }
}


