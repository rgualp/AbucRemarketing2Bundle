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
    private $notificationTestEnvironment;
    private $notificationTestPhone;
    private $smsContactPhone;
    private $smsContactMobile;
    private $loggerService;
    private $notificationSendCheckingsSms;
    private $notificationSendConfirmationSms;
    private $notificationSendInmediatebookingSms;

    public function __construct(ObjectManager $em, $serviceNotificationUrl, $notificationServiceApiKey, $time, $notificationSendSms, $notificationTestEnvironment, $notificationTestPhone, $smsContactPhone, $smsContactMobile, $loggerService, $notificationSendCheckingsSms, $notificationSendConfirmationSms, $notificationSendInmediatebookingSms)
    {
        $this->em = $em;
        $this->serviceNotificationUrl = $serviceNotificationUrl;
        $this->notificationServiceApiKey = $notificationServiceApiKey;
        $this->time =$time;
        $this->notificationSendSms = $notificationSendSms;
        $this->notificationTestEnvironment = $notificationTestEnvironment;
        $this->notificationTestPhone = $notificationTestPhone;
        $this->smsContactMobile = $smsContactMobile;
        $this->smsContactPhone = $smsContactPhone;
        $this->loggerService = $loggerService;
        $this->notificationSendCheckingsSms = $notificationSendCheckingsSms;
        $this->notificationSendConfirmationSms = $notificationSendConfirmationSms;
        $this->notificationSendInmediatebookingSms = $notificationSendInmediatebookingSms;
    }

    public function sendConfirmPaymentSMSNotification($reservation)
    {
        if($this->notificationSendConfirmationSms) {
            $accommodation = $reservation->getGenResOwnId();
            if ($accommodation->getOwnMobileNumber() != null && $accommodation->getOwnMobileNumber() != "" && $accommodation->getOwnSmsNotifications()) {
                $mobileNumber = ($this->notificationTestEnvironment) ? $this->notificationTestPhone : $accommodation->getOwnMobileNumber();
                $touristName = $reservation->getGenResUserId()->getUserCompleteName();
                $message = "Mycasaparticular confirma reserva del cliente " . $touristName . ", revise su correo o contáctenos al " . $this->smsContactPhone;
                $subType = notification::SUB_TYPE_RESERVATION_PAID;
                $reservationObj = array(
                    "casId" => $reservation->getCASId(),
                    "genResId" => $reservation->getGenResId()
                );

                $response = $this->sendSMSNotification($mobileNumber, $message, $subType);

                if ($response != null)
                    $this->createNotification($reservationObj, $subType, $response);
            }
        }
    }

    public function sendCheckinSMSNotification($reservationObj)
    {
        if($this->notificationSendCheckingsSms) {
            if ($reservationObj["mobile"] != null && $reservationObj["mobile"] != "" && $reservationObj["smsNotification"]) {
                $mobileNumber = ($this->notificationTestEnvironment) ? $this->notificationTestPhone : $reservationObj["mobile"];
                $message = "MyCasaParticular le recuerda, cliente " . $reservationObj["touristCompleteName"] . " arriba el " . $reservationObj["arrivalDate"] . " por " . $reservationObj["nights"] . " noches pagará " . $reservationObj["payAtService"] . " CUC.";
                $subType = notification::SUB_TYPE_CHECKIN;

                $response = $this->sendSMSNotification($mobileNumber, $message, $subType);

                if ($response != null)
                    $this->createNotification($reservationObj, $subType, $response);
            }
        }
    }

    public function sendInmediateBookingSMSNotification($reservation)
    {
        if($this->notificationSendInmediatebookingSms) {
            $accommodation = $reservation->getGenResOwnId();
            if ($accommodation->getOwnMobileNumber() != null && $accommodation->getOwnMobileNumber() != "" && $accommodation->getOwnSmsNotifications()) {
                $mobileNumber = ($this->notificationTestEnvironment) ? $this->notificationTestPhone : $accommodation->getOwnMobileNumber();
                $touristName = $reservation->getGenResUserId()->getUserCompleteName();
                $reservationDatas = $this->em->getRepository("mycpBundle:generalReservation")->getDataFromGeneralReservation($reservation->getGenResId());

                foreach ($reservationDatas as $reservationData) {
                    $fromDate = $reservationData["fromDate"];
                    $fromDate = $fromDate->format("d-m-y");
                    $rooms = $reservationData["rooms"];
                    $nights = $reservationData["nights"] / $rooms;
                    $guests = $reservationData["guests"];
                    $reservationId = $reservation->getGenResId();

                    //$message = "";
                    $noches = ($nights > 1) ? 's' : '';
                    $personas = ($guests > 1) ? 's' : '';
                    $contactPhone = $this->smsContactPhone;
                    $contactMobile = $this->smsContactMobile;

                    //$message = 'MyCasaParticular:Si está disponible para ' . $fromDate . ',' . $nights . 'noche' . $noches . ',' . $guests . 'persona' . $personas . ',' . $rooms . 'hab, CAS' . $reservationId . ', llame al ' . $contactPhone . '/' . $contactMobile . ' o envíe SMS con su CAS.';
                    $message = 'MyCasaParticular:Solicitud para '.$fromDate.', '.$nights.' noche'.$noches.', '.$guests.' persona'.$personas.', '.$rooms.'hab, CAS'.$reservationId.'. Llame ahora al '.$contactPhone.' o '.$contactMobile.'.';

                    $subType = notification::SUB_TYPE_INMEDIATE_BOOKING;
                    $reservationObj = array(
                        "casId" => $reservation->getCASId(),
                        "genResId" => $reservation->getGenResId()
                    );

                    $response = $this->sendSMSNotification($mobileNumber, $message, $subType);

                    if ($response != null)
                        $this->createNotification($reservationObj, $subType, $response);
                }
            }
        }
    }

    public function sendSMSNotification($mobileNumber, $message, $subtype)
    {
        if($this->notificationSendSms == 1 && $message != null && $message != "" && $mobileNumber != null  && $mobileNumber != "") {
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

        $this->loggerService->logSMS(date('Y-m-d H:i:s'). " Mobile number: ".$mobileNumber.". Text: ".$message. ". Type: ".$subtype);

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
                ->setSendTo(($this->notificationTestEnvironment) ? $this->notificationTestPhone : $response["mobile"])
                ->setSubType($subType)
                ->setDescription($reservationObj["casId"])
                ->setNotificationType($notificationType)
                ->setStatus($notificationStatus)
                ->setOwnership($reservation->getGenResOwnId())
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


