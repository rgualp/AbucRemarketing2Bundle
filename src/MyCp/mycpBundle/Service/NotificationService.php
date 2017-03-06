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
use MyCp\PartnerBundle\Entity\paPendingPaymentAccommodation;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Abuc\RemarketingBundle\Event\JobEvent;
use MyCp\mycpBundle\JobData\GeneralReservationJobData;
use Symfony\Component\HttpFoundation\Response;

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
    private $notificationSendCanceledbookingSms;
    private $notificationAgencyCompletePaymentSms;
    private $notificationAgencyCompletePaymentDepositSms;
    protected $container;

    public function __construct(ObjectManager $em, $serviceNotificationUrl, $notificationServiceApiKey, $time, $notificationSendSms, $notificationTestEnvironment, $notificationTestPhone, $smsContactPhone, $smsContactMobile, $loggerService, $notificationSend, $container)
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
        $this->notificationSendCheckingsSms = $notificationSend['checkings_sms'];
        $this->notificationSendConfirmationSms = $notificationSend['confirmation_sms'];
        $this->notificationSendInmediatebookingSms = $notificationSend['inmediatebooking_sms'];
        $this->notificationSendCanceledbookingSms = $notificationSend['canceledbooking_sms'];
        $this->notificationAgencyCompletePaymentSms = $notificationSend['agency_complete_payment_sms'];
        $this->notificationAgencyCompletePaymentDepositSms = $notificationSend['agency_complete_payment_deposit_sms'];
        $this->container = $container;
    }

    public function sendInmediateBookingSMSNotification($reservation)
    {
        $accommodation = $reservation->getGenResOwnId();

        //$touristName = $reservation->getGenResUserId()->getUserCompleteName();
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

            $mobileNumber = "";
            $response = array();
            if ($this->notificationSendInmediatebookingSms && $accommodation->getOwnMobileNumber() != null && $accommodation->getOwnMobileNumber() != "" && $accommodation->getOwnSmsNotifications()) {
                $mobileNumber = ($this->notificationTestEnvironment) ? $this->notificationTestPhone : $accommodation->getOwnMobileNumber();
                $response = $this->sendSMSNotification($mobileNumber, $message, $subType);
            }
            $response["message"] = $message;
            $response["mobile"] = $mobileNumber;
            $this->createNotification($reservationObj, $subType, $response);
        }
    }

    public function sendCheckinSMSNotification($reservationObj)
    {
        $message = "MyCasaParticular le recuerda, cliente " . $reservationObj["touristCompleteName"] . " arriba el " . $reservationObj["arrivalDate"] . " por " . $reservationObj["nights"] . " noches pagará " . $reservationObj["payAtService"] . " CUC.";
        $subType = notification::SUB_TYPE_CHECKIN;

        $mobileNumber = "";
        $response = array();

        if ($this->notificationSendCheckingsSms && $reservationObj["mobile"] != null && $reservationObj["mobile"] != "" && $reservationObj["smsNotification"]) {
            $mobileNumber = ($this->notificationTestEnvironment) ? $this->notificationTestPhone : $reservationObj["mobile"];
            $response = $this->sendSMSNotification($mobileNumber, $message, $subType);
        }

        $response["message"] = $message;
        $response["mobile"] = $mobileNumber;
        $this->createNotification($reservationObj, $subType, $response);
    }

    public function sendConfirmPaymentSMSNotification(generalReservation $reservation)
    {
        $accommodation = $reservation->getGenResOwnId();

        $touristName = $reservation->getGenResUserId()->getUserCompleteName();
        $message = "MyCasaParticular confirma CAS".$reservation->getGenResId()." llegada ".$reservation->getGenResFromDate()->format('d/m/Y').", cliente " . $touristName . "." /*$this->smsContactPhone*/;
        $subType = notification::SUB_TYPE_RESERVATION_PAID;
        $reservationObj = array(
            "casId" => $reservation->getCASId(),
            "genResId" => $reservation->getGenResId()
        );

        $mobileNumber = "";
        $response = array();
        if ($this->notificationSendConfirmationSms && $accommodation->getOwnMobileNumber() != null && $accommodation->getOwnMobileNumber() != "" && $accommodation->getOwnSmsNotifications()) {
            $mobileNumber = ($this->notificationTestEnvironment) ? $this->notificationTestPhone : $accommodation->getOwnMobileNumber();
            $response = $this->sendSMSNotification($mobileNumber, $message, $subType);
        }
        $response["message"] = $message;
        $response["mobile"] = $mobileNumber;
        $this->createNotification($reservationObj, $subType, $response);
    }

    public function sendSMSReservationsCancel(ownershipReservation $ownershipReservation, $refund = null)
    {
        $reservation = $ownershipReservation->getOwnResGenResId();
        $accommodation = $reservation->getGenResOwnId();
        $room = $this->em->getRepository("mycpBundle:room")->find($ownershipReservation->getOwnResSelectedRoomId());

        $message="MyCasaParticular informa:CAS".$reservation->getGenResId()." con fecha de llegada ".$ownershipReservation->getOwnResReservationFromDate()->format('d/m/Y')." ha sido cancelada por turista (Hab.".$room->getRoomNum().").";
        if($refund != null){
            $message .= " Se le rembolsará ".$refund." CUC.";
        }

        $subType = notification::SUB_TYPE_CANCELED_BOOKING;
        $reservationObj = array(
            "casId" => $reservation->getCASId(),
            "genResId" => $reservation->getGenResId()
        );

        $mobileNumber = "";
        $response = array();
        if ($this->notificationSendConfirmationSms && $accommodation->getOwnMobileNumber() != null && $accommodation->getOwnMobileNumber() != "" && $accommodation->getOwnSmsNotifications()) {
            $mobileNumber = ($this->notificationTestEnvironment) ? $this->notificationTestPhone : $accommodation->getOwnMobileNumber();
            $response = $this->sendSMSNotification($mobileNumber, $message, $subType);
        }
        $response["message"] = $message;
        $response["mobile"] = $mobileNumber;
        $this->createNotification($reservationObj, $subType, $response);
    }

    public function sendAgencyCompletePaymentSMSNotification(paPendingPaymentAccommodation $pendingPaymentAccommodation)
    {
        $accommodation = $pendingPaymentAccommodation->getAccommodation();
        $reservation = $pendingPaymentAccommodation->getReservation();

        $message = "MyCasaParticular confirma solicitud de agencia. Usted recibirá ".$pendingPaymentAccommodation->getAmount()."CUC. CAS".$reservation->getGenResId().". El turista llega ".$reservation->getGenResFromDate()->format('d/m/Y').". " /*$this->smsContactPhone*/;
        $subType = notification::SUB_TYPE_COMPLETE_PAYMENT;
        $reservationObj = array(
            "casId" => $reservation->getCASId(),
            "genResId" => $reservation->getGenResId()
        );

        $mobileNumber = "";
        $response = array();
        if ($this->notificationAgencyCompletePaymentSms && $accommodation->getOwnMobileNumber() != null && $accommodation->getOwnMobileNumber() != "" && $accommodation->getOwnSmsNotifications()) {
            $mobileNumber = ($this->notificationTestEnvironment) ? $this->notificationTestPhone : $accommodation->getOwnMobileNumber();
            $response = $this->sendSMSNotification($mobileNumber, $message, $subType);
        }
        $response["message"] = $message;
        $response["mobile"] = $mobileNumber;
        $this->createNotification($reservationObj, $subType, $response);
    }

    public function sendAgencyCompletePaymentDepositSMSNotification(paPendingPaymentAccommodation $pendingPaymentAccommodation)
    {
        $accommodation = $pendingPaymentAccommodation->getAccommodation();
        $reservation = $pendingPaymentAccommodation->getReservation();

        $message = "MyCasaParticular confirma pago realizado por reserva de agencia. Usted recibió ".$pendingPaymentAccommodation->getAmount()."CUC."/*$this->smsContactPhone*/;
        $subType = notification::SUB_TYPE_COMPLETE_PAYMENT_DEPOSIT;
        $reservationObj = array(
            "casId" => $reservation->getCASId(),
            "genResId" => $reservation->getGenResId()
        );

        $mobileNumber = "";
        $response = array();
        if ($this->notificationAgencyCompletePaymentDepositSms && $accommodation->getOwnMobileNumber() != null && $accommodation->getOwnMobileNumber() != "" && $accommodation->getOwnSmsNotifications()) {
            $mobileNumber = ($this->notificationTestEnvironment) ? $this->notificationTestPhone : $accommodation->getOwnMobileNumber();
            $response = $this->sendSMSNotification($mobileNumber, $message, $subType);
        }
        $response["message"] = $message;
        $response["mobile"] = $mobileNumber;
        $this->createNotification($reservationObj, $subType, $response);
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

            $sync = false;
            if($response != null && isset($response["code"]) && $response["code"] == Response::HTTP_CREATED){
               $sync = true;
            }
            /*if($response["code"] != 201)
                $notificationStatus = $this->em->getRepository("mycpBundle:nomenclator")->findOneBy(array("nom_category" => "notificationStatus", "nom_name" => "failed_ns"));
            else
                $notificationStatus = $this->em->getRepository("mycpBundle:nomenclator")->findOneBy(array("nom_category" => "notificationStatus", "nom_name" => "success_ns"));*/

            $code = ($response != null && isset($response["code"])) ? ($response["code"]) : (Response::HTTP_BAD_REQUEST);
            $r = ($response != null && isset($response["response"])) ? ($response["response"]) : "sin respuesta";

            $notification = new notification();
            $notification->setCode($code)
                ->setReservation($reservation)
                ->setResponse($r)
                ->setCreated(new \DateTime())
                ->setMessage($response["message"]);
            $notification->setSendTo(($this->notificationTestEnvironment) ? $this->notificationTestPhone : $response["mobile"])
                ->setSubType($subType)
                ->setDescription($reservationObj["casId"])
                ->setNotificationType($notificationType)
                ->setSync($sync)
                ->setOwnership($reservation->getGenResOwnId());

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

    public function answerInmediateBooking(notification $notification, $availability, $sendEmail = true){
        $generalReservation = $notification->getReservation();
        $ownership_reservations = $generalReservation->getOwnReservations();
        foreach ($ownership_reservations as $ownership_reservation) {
            if($availability == 1){
                $ownership_reservation->setOwnResStatus(ownershipReservation::STATUS_AVAILABLE);
            }
            else{
                $ownership_reservation->setOwnResStatus(ownershipReservation::STATUS_NOT_AVAILABLE);
            }
            $this->em->persist($ownership_reservation);
            $this->em->flush();
        }

        if($availability == 1) {
            $generalReservation->setGenResStatus(generalReservation::STATUS_AVAILABLE);
            $notification->setActionResponse(notification::ACTION_RESPONSE_AVAILABLE);
        }
        else {
            $generalReservation->setGenResStatus(generalReservation::STATUS_NOT_AVAILABLE);
            $notification->setActionResponse(notification::ACTION_RESPONSE_UNAVAILABLE);
        }
        $this->em->persist($generalReservation);
        $this->em->persist($notification);
        $this->em->flush();

        if($sendEmail){
            /*Envio de Email*/
            $id_reservation = $generalReservation->getGenResId();
            $service_email = $this->container->get('Email');
            $service_email->sendReservation($id_reservation, "Disponibilidad dada por propietario desde el Módulo Casa", false);
        }

        if ($generalReservation->getGenResStatus() == generalReservation::STATUS_AVAILABLE){
            // inform listeners that a reservation was sent out
            $dispatcher = $this->container->get('event_dispatcher');
            $eventData = new GeneralReservationJobData($id_reservation);
            $dispatcher->dispatch('mycp.event.reservation.sent_out', new JobEvent($eventData));
        }
    }
}


