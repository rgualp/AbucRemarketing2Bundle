<?php

namespace MyCp\mycpBundle\Service;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManager;
use MyCp\FrontEndBundle\Helpers\ReservationHelper;
use MyCp\FrontEndBundle\Helpers\Time;
use MyCp\mycpBundle\Entity\booking;
use MyCp\mycpBundle\Entity\generalReservation;
use MyCp\mycpBundle\Entity\lang;
use MyCp\mycpBundle\Entity\notification;
use MyCp\mycpBundle\Entity\ownership;
use MyCp\mycpBundle\Entity\ownershipDescriptionLang;
use MyCp\mycpBundle\Entity\ownershipReservation;
use MyCp\mycpBundle\Entity\reservationNotification;
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
    private $notificationServiceApiKey;
    private $time;

    public function __construct(ObjectManager $em, $serviceNotificationUrl, $notificationServiceApiKey, $time)
    {
        $this->em = $em;
        $this->serviceNotificationUrl = $serviceNotificationUrl;
        $this->notificationServiceApiKey = $notificationServiceApiKey;
        $this->time =$time;
    }

    public function sendConfirmPaymentSMSNotification($reservation, $isTesting = false)
    {
        $accommodation = $reservation->getGenResOwnId();
        if($accommodation->getOwnMobileNumber() != null  && $accommodation->getOwnMobileNumber() != "") {
            $mobileNumber = ($isTesting) ? "52540669" : $accommodation->getOwnMobileNumber();
            $touristName = $reservation->getGenResUserId()->getUserCompleteName();
            $message = "Mycasaparticular confirma reserva del cliente ".$touristName.", revise su correo o contáctenos al 78673574";
            $subType = "RESERVATION_PAID";
            $description = $reservation->getCASId();

            $status = $this->sendSMSNotification($mobileNumber, $message, $subType, $description);
            $this->createReservationNotification($reservation,$subType, $status);
        }
    }

    public function sendCheckinSMSNotification($reservation, $isTesting = false)
    {
        $accommodation = $reservation->getGenResOwnId();
        if($accommodation->getOwnMobileNumber() != null  && $accommodation->getOwnMobileNumber() != "") {
            $mobileNumber = ($isTesting) ? "52540669" : $accommodation->getOwnMobileNumber();
            $touristName = $reservation->getGenResUserId()->getUserCompleteName();

            $reservations = $this->em->getRepository('mycpBundle:ownershipReservation')->findBy(array("own_res_gen_res_id" => $reservation->getGenResId()), array("own_res_reservation_from_date" => "ASC"));
            $nights = $reservation->getTotalStayedNights($reservations, $this->time);
            $payAtService =  $reservation->getGenResTotalInSite() - $reservation->getGenResTotalInSite() * $accommodation->getOwnCommissionPercent()/100;
            $payAtService = number_format((float)$payAtService, 2, '.', '');

            $message = "MyCasaParticular le recuerda, cliente ".$touristName." arriba el ".$reservations[0]->getOwnResReservationFromDate()->format("d-m-Y")." por ".$nights." noches pagará ".$payAtService." CUC.";
            $subType = "CHECKIN";
            $description = $reservation->getCASId();

            $status = $this->sendSMSNotification($mobileNumber, $message, $subType, $description);
            $this->createReservationNotification($reservation,$subType, $status);
        }
    }

    private function sendSMSNotification($mobileNumber, $message, $subtype, $description)
    {
        $data['notificacionsmsrest_formtype'] = array(
            'project' => $this->notificationServiceApiKey,//Obligatorio
            'to' => $mobileNumber,//8 digitos, comenzando con 5
            'msg' => $message,//No obligatorio
            'notification_type' => $subtype,//Obligatorio
        );

        $url= $this->serviceNotificationUrl.'/api/notificacion/sms/add';
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
        $response = curl_exec($curl);
        $info = curl_getinfo($curl);
        curl_close($curl);
        $code = $info['http_code'];


        if($code!= 201){
            $notificationType = $this->em->getRepository("mycpBundle:nomenclator")->findOneBy(array("nom_category" => "notificationType", "nom_name" => "sms_nt"));
            $notificationStatus = $this->em->getRepository("mycpBundle:nomenclator")->findOneBy(array("nom_category" => "notificationStatus", "nom_name" => "failed_ns"));

            $notification = new notification();
            $notification->setCode($code)
                ->setResponse($response)
                ->setCreated(new \DateTime)
                ->setMessage($message)
                ->setSendTo($mobileNumber)
                ->setSubType($subtype)
                ->setDescription($description)
                ->setNotificationType($notificationType)
                ->setStatus($notificationStatus);

            $this->em->persist($notification);
            $this->em->flush();

            return $notificationStatus;
        }

        return $this->em->getRepository("mycpBundle:nomenclator")->findOneBy(array("nom_category" => "notificationStatus", "nom_name" => "pending_ns"));
    }

    private function createReservationNotification(generalReservation $reservation, $subType, $notificationStatus)
    {
        try {
            $notificationType = $this->em->getRepository("mycpBundle:nomenclator")->findOneBy(array("nom_category" => "notificationType", "nom_name" => "sms_nt"));

            $reservationNotification = new reservationNotification();
            $reservationNotification->setReservation($reservation)
                ->setCreated(new \DateTime)
                ->setSubType($subType)
                ->setNotificationType($notificationType)
                ->setStatus($notificationStatus);

            $this->em->persist($reservationNotification);
            $this->em->flush();
        }
        catch(\Exception $e)
        {
            echo $e->getMessage();
        }
    }
}


