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
use MyCp\mycpBundle\Helpers\Dates;
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
    private $notificationSendSms;

    public function __construct(ObjectManager $em, $serviceNotificationUrl, $notificationServiceApiKey, $time, $notificationSendSms)
    {
        $this->em = $em;
        $this->serviceNotificationUrl = $serviceNotificationUrl;
        $this->notificationServiceApiKey = $notificationServiceApiKey;
        $this->time =$time;
        $this->notificationSendSms = $notificationSendSms;
    }

    public function sendConfirmPaymentSMSNotification($reservation, $isTesting = false)
    {
        $accommodation = $reservation->getGenResOwnId();
        if($accommodation->getOwnMobileNumber() != null  && $accommodation->getOwnMobileNumber() != "" && $accommodation->getOwnSmsNotifications()) {
            $mobileNumber = ($isTesting) ? "52540669" : $accommodation->getOwnMobileNumber();
            $touristName = $reservation->getGenResUserId()->getUserCompleteName();
            $message = "Mycasaparticular confirma reserva del cliente ".$touristName.", revise su correo o contáctenos al 78673574";
            $subType = "RESERVATION_PAID";
            $reservationObj = array(
                "casId" => $reservation->getCASId(),
                "genResId" => $reservation->getGenResId()
            );

            $response = $this->sendSMSNotification($mobileNumber, $message, $subType);

            if($response != null)
                $this->createNotification($reservationObj,$subType, $response);
        }
    }

    public function sendCheckinSMSNotification($reservationObj, $isTesting = false)
    {
        if($reservationObj["mobile"] != null  && $reservationObj["mobile"] != "" && $reservationObj["smsNotification"]) {
            $mobileNumber = ($isTesting) ? "52540669" : $reservationObj["mobile"];
            $message = "MyCasaParticular le recuerda, cliente ".$reservationObj["touristCompleteName"]." arriba el ".$reservationObj["arrivalDate"]." por ".$reservationObj["nights"]." noches pagará ".$reservationObj["payAtService"]." CUC.";
            $subType = "CHECKIN";

            $response = $this->sendSMSNotification($mobileNumber, $message, $subType);

            if($response != null)
                $this->createNotification($reservationObj,$subType, $response);
        }
    }

    public function sendInmediateBookingSMSNotification($reservation, $isTesting = false)
    {
        $accommodation = $reservation->getGenResOwnId();
        if($accommodation->getOwnMobileNumber() != null  && $accommodation->getOwnMobileNumber() != "" && $accommodation->getOwnSmsNotifications()) {
            $mobileNumber = ($isTesting) ? "52540669" : $accommodation->getOwnMobileNumber();
            $touristName = $reservation->getGenResUserId()->getUserCompleteName();
            $reservationData = $this->em->getRepository("mycpBundle:generalReservation")->getDataFromGeneralReservation($reservation->getGenResId());
            $fromDate =  \DateTime::createFromFormat("Y-m-d",$reservationData[0]["fromDate"]);
            $fromDate = $fromDate->format("d/m/y");
            $rooms = $reservationData[0]["rooms"];
            $nights = $reservationData[0]["nights"] / $rooms;
            $guests = $reservationData[0]["guests"];

            $message = "MyCasaParticular: Tiene una solicitud para el $fromDate por $nights noches. Son $rooms habitaciones/$guests personas. Si está disponible, llame en menos de 1hora al 78673574.";

            $subType = "INMEDIATE_BOOKING";
            $reservationObj = array(
                "casId" => $reservation->getCASId(),
                "genResId" => $reservation->getGenResId()
            );

            $response = $this->sendSMSNotification($mobileNumber, $message, $subType);

            if($response != null)
                $this->createNotification($reservationObj,$subType, $response);
        }
    }

    private function sendSMSNotification($mobileNumber, $message, $subtype)
    {
        if($this->notificationSendSms == 1) {
            $data['sms'] = array(
                'project' => $this->notificationServiceApiKey,//Obligatorio
                'to' => "53" . $mobileNumber,//8 digitos, comenzando con 5
                'msg' => $message,//No obligatorio
                'sms_type' => $subtype,//Obligatorio
            );

            $url = $this->serviceNotificationUrl . '/api/sms/add';
            $curl = curl_init($url);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
            $response = curl_exec($curl);
            $info = curl_getinfo($curl);
            curl_close($curl);
            $code = $info['http_code'];

            return array(
                "code" => $code,
                "response" => $response,
                "message" => $message,
                "mobile" => $mobileNumber
            );
        }
        return null;
    }

    private function createNotification($reservationObj, $subType, $response)
    {
        try {
            $notificationType = $this->em->getRepository("mycpBundle:nomenclator")->findOneBy(array("nom_category" => "notificationType", "nom_name" => "sms_nt"));
            $reservation = $this->em->getRepository("mycpBundle:generalReservation")->find($reservationObj["genResId"]);

            if($response["code"] != 201)
                $notificationStatus = $this->em->getRepository("mycpBundle:nomenclator")->findOneBy(array("nom_category" => "notificationStatus", "nom_name" => "failed_ns"));
            else
                $notificationStatus = $this->em->getRepository("mycpBundle:nomenclator")->findOneBy(array("nom_category" => "notificationStatus", "nom_name" => "success_ns"));


            $notification = new notification();
            $notification->setCode($response["code"])
                ->setReservation($reservation)
                ->setResponse($response["response"])
                ->setCreated(new \DateTime())
                ->setMessage($response["message"])
                ->setSendTo($response["mobile"])
                ->setSubType($subType)
                ->setDescription($reservationObj["casId"])
                ->setNotificationType($notificationType)
                ->setStatus($notificationStatus)
            ;

            $this->em->persist($notification);
            $this->em->flush();
        }
        catch(\Exception $e)
        {
            echo $e->getMessage();
        }
    }

    private function createReservationNotification($reservationObj, $subType, $notificationStatus)
    {
        try {
            $notificationType = $this->em->getRepository("mycpBundle:nomenclator")->findOneBy(array("nom_category" => "notificationType", "nom_name" => "sms_nt"));
            $reservation = $this->em->getRepository("mycpBundle:generalReservation")->find($reservationObj["genResId"]);

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


