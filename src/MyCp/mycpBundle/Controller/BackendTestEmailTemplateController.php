<?php

namespace MyCp\mycpBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use MyCp\mycpBundle\Entity\generalReservation;

class BackendTestEmailTemplateController extends Controller {

    public function homeAction() {
        return $this->render('mycpBundle:test:home.html.twig');
    }

    // <editor-fold defaultstate="collapsed" desc="Last chance to book">
    public function lastChanceToBookAction($langCode) {
        return $this->getLastChanceToBookBody($langCode);
    }

    public function lastChanceToBookSendAction($langCode, $newMethod, $mail, Request $request) {
        if ($request->getMethod() == 'POST') {
            $body = $this->getLastChanceToBookBody($langCode);
            return $this->sendEmail($newMethod, $mail, $body, "Testings: Última oportunidad de reservar");
        }
    }

    private function getLastChanceToBookBody($langCode) {
        $em = $this->getDoctrine()->getEntityManager();
        $service_time = $this->get('time');

        $generalReservation = $em
                ->getRepository('mycpBundle:generalReservation')
                ->findOneBy(array('gen_res_status' => generalReservation::STATUS_AVAILABLE));

        $user = $generalReservation->getGenResUserId();
        $ownershipReservations = $em
                ->getRepository('mycpBundle:generalReservation')
                ->getOwnershipReservations($generalReservation);

        $arrayPhotos = array();
        $arrayNights = array();

        $initialPayment = 0;

        foreach ($ownershipReservations as $ownershipReservation) {
            $photos = $em
                    ->getRepository('mycpBundle:ownership')
                    ->getPhotos(
                    // TODO: This line is very strange. Why take the ownId of the genRes of the ownRes?!
                    $ownershipReservation->getOwnResGenResId()->getGenResOwnId()->getOwnId()
            );

            if (!empty($photos)) {
                array_push($arrayPhotos, $photos);
            }

            $array_dates = $service_time
                    ->datesBetween(
                    $ownershipReservation->getOwnResReservationFromDate()->getTimestamp(), $ownershipReservation->getOwnResReservationToDate()->getTimestamp()
            );

            array_push($arrayNights, count($array_dates) - 1);

            $comission = $ownershipReservation->getOwnResGenResId()->getGenResOwnId()->getOwnCommissionPercent() / 100;
            //Initial down payment
            if ($ownershipReservation->getOwnResNightPrice() > 0)
                $initialPayment += $ownershipReservation->getOwnResNightPrice() * (count($array_dates) - 1) * $comission;
            else
                $initialPayment += $ownershipReservation->getOwnResTotalInSite() * $comission;
        }

        return $this->render('FrontEndBundle:mails:last_reminder_available.html.twig', array(
                    'user' => $user,
                    'reservations' => $ownershipReservations,
                    'photos' => $arrayPhotos,
                    'nights' => $arrayNights,
                    'user_locale' => $langCode,
                    'initial_payment' => $initialPayment,
                    'generalReservationId' => $generalReservation->getGenResId()
        ));
    }
    // </editor-fold>

    // <editor-fold defaultstate="collapsed" desc="Accommodation still available">
    public function accommodationStillAvailableAction($langCode) {
        return $this->getAccommodationStillAvailableBody($langCode);
    }

    public function accommodationStillAvailableSendAction($langCode, $newMethod, $mail, Request $request) {
        if ($request->getMethod() == 'POST') {
            $body = $this->getAccommodationStillAvailableBody($langCode);
            return $this->sendEmail($newMethod, $mail, $body, "Testings: Alojamientos disponibles aún");
        }
    }

    private function getAccommodationStillAvailableBody($langCode) {
        $em = $this->getDoctrine()->getEntityManager();
        $service_time = $this->get('time');

        $generalReservation = $em
                ->getRepository('mycpBundle:generalReservation')
                ->findOneBy(array('gen_res_status' => generalReservation::STATUS_AVAILABLE));

        $user = $generalReservation->getGenResUserId();
        $ownershipReservations = $em
                ->getRepository('mycpBundle:generalReservation')
                ->getOwnershipReservations($generalReservation);

        $arrayPhotos = array();
        $arrayNights = array();

        $initialPayment = 0;

        foreach ($ownershipReservations as $ownershipReservation) {
            $photos = $em
                    ->getRepository('mycpBundle:ownership')
                    ->getPhotos(
                    // TODO: This line is very strange. Why take the ownId of the genRes of the ownRes?!
                    $ownershipReservation->getOwnResGenResId()->getGenResOwnId()->getOwnId()
            );

            if (!empty($photos)) {
                array_push($arrayPhotos, $photos);
            }

            $array_dates = $service_time
                    ->datesBetween(
                    $ownershipReservation->getOwnResReservationFromDate()->getTimestamp(), $ownershipReservation->getOwnResReservationToDate()->getTimestamp()
            );

            array_push($arrayNights, count($array_dates) - 1);

            $comission = $ownershipReservation->getOwnResGenResId()->getGenResOwnId()->getOwnCommissionPercent() / 100;
            //Initial down payment
            if ($ownershipReservation->getOwnResNightPrice() > 0)
                $initialPayment += $ownershipReservation->getOwnResNightPrice() * (count($array_dates) - 1) * $comission;
            else
                $initialPayment += $ownershipReservation->getOwnResTotalInSite() * $comission;
        }

        return $this->render('FrontEndBundle:mails:reminder_available.html.twig', array(
                    'user' => $user,
                    'reservations' => $ownershipReservations,
                    'photos' => $arrayPhotos,
                    'nights' => $arrayNights,
                    'user_locale' => $langCode,
                    'initial_payment' => $initialPayment
        ));
    }
    // </editor-fold>

    // <editor-fold defaultstate="collapsed" desc="No available">
    public function notAvailableAction($langCode) {
        return $this->getNotAvailableBody($langCode);
    }

    public function notAvailableSendAction($langCode, $newMethod, $mail, Request $request) {
        if ($request->getMethod() == 'POST') {
            $body = $this->getNotAvailableBody($langCode);
            return $this->sendEmail($newMethod, $mail, $body, "Testings: Alojamientos no disponibles");
        }
    }

    private function getNotAvailableBody($langCode) {
        $em = $this->getDoctrine()->getEntityManager();
        $service_time = $this->get('time');

        $generalReservation = $em
                ->getRepository('mycpBundle:generalReservation')
                ->findOneBy(array('gen_res_status' => generalReservation::STATUS_AVAILABLE));

        $user = $generalReservation->getGenResUserId();
        $ownershipReservations = $em
                ->getRepository('mycpBundle:generalReservation')
                ->getOwnershipReservations($generalReservation);

        $arrayPhotos = array();
        $arrayNights = array();

        $initialPayment = 0;

        foreach ($ownershipReservations as $ownershipReservation) {
            $photos = $em
                    ->getRepository('mycpBundle:ownership')
                    ->getPhotos(
                    // TODO: This line is very strange. Why take the ownId of the genRes of the ownRes?!
                    $ownershipReservation->getOwnResGenResId()->getGenResOwnId()->getOwnId()
            );

            if (!empty($photos)) {
                array_push($arrayPhotos, $photos);
            }

            $array_dates = $service_time
                    ->datesBetween(
                    $ownershipReservation->getOwnResReservationFromDate()->getTimestamp(), $ownershipReservation->getOwnResReservationToDate()->getTimestamp()
            );

            array_push($arrayNights, count($array_dates) - 1);

            $comission = $ownershipReservation->getOwnResGenResId()->getGenResOwnId()->getOwnCommissionPercent() / 100;
            //Initial down payment
            if ($ownershipReservation->getOwnResNightPrice() > 0)
                $initialPayment += $ownershipReservation->getOwnResNightPrice() * (count($array_dates) - 1) * $comission;
            else
                $initialPayment += $ownershipReservation->getOwnResTotalInSite() * $comission;
        }

        return $this->render('FrontEndBundle:mails:expired_offer_reminder.html.twig', array(
                    'user' => $user,
                    'reservations' => $ownershipReservations,
                    'photos' => $arrayPhotos,
                    'nights' => $arrayNights,
                    'user_locale' => $langCode,
                    'initial_payment' => $initialPayment,
                    'generalReservationId' => $generalReservation->getGenResId()
        ));
    }
    // </editor-fold>

    public function activateAccountAction($langCode) {
        return $this->getActivateAccountBody($langCode);
    }

    public function activateAccountSendAction($langCode, $newMethod, $mail, Request $request) {
        if ($request->getMethod() == 'POST') {
            $body = $this->getActivateAccountBody($langCode);
            return $this->sendEmail($newMethod, $mail, $body, "Testings: Activar cuenta");
        }
    }

    private function getActivateAccountBody($langCode) {
        $em = $this->getDoctrine()->getEntityManager();
        $user = $em->getRepository('mycpBundle:user')->findOneBy(array('user_enabled' => true));
        $activationUrl = $this->getActivationUrl($user, $langCode);
        $userName = $user->getUserCompleteName();

        return $this->render('FrontEndBundle:mails:enableAccount.html.twig', array(
                    'enableUrl' => $activationUrl,
                    'user_name' => $userName,
                    'user_locale' => $langCode
        ));
    }

    public function activateAccountReminderAction($langCode) {
        return $this->getActivateAccountReminderBody($langCode);
    }

    public function activateAccountReminderSendAction($langCode, $newMethod, $mail, Request $request) {
        if ($request->getMethod() == 'POST') {
            $body = $this->getActivateAccountReminderBody($langCode);
            return $this->sendEmail($newMethod, $mail, $body, "Testings: Activar cuenta - 1 recordatorio");
        }
    }

    private function getActivateAccountReminderBody($langCode) {
        $em = $this->getDoctrine()->getEntityManager();
        $user = $em->getRepository('mycpBundle:user')->findOneBy(array('user_enabled' => true));
        $activationUrl = $this->getActivationUrl($user, $langCode);
        $userName = $user->getUserCompleteName();

        return $this->render('FrontEndBundle:mails:enableAccountReminder.html.twig', array(
                    'enableUrl' => $activationUrl,
                    'user_name' => $userName,
                    'user_locale' => $langCode
        ));
    }

    public function activateAccountLastReminderAction($langCode) {
        return $this->getActivateAccountLastReminderBody($langCode);
    }

    public function activateAccountLastReminderSendAction($langCode, $newMethod, $mail, Request $request) {
        if ($request->getMethod() == 'POST') {
            $body = $this->getActivateAccountLastReminderBody($langCode);
            return $this->sendEmail($newMethod, $mail, $body, "Testings: Activar cuenta - 2 recordatorio");
        }
    }

    public function getActivateAccountLastReminderBody($langCode) {
        $em = $this->getDoctrine()->getEntityManager();
        $user = $em->getRepository('mycpBundle:user')->findOneBy(array('user_enabled' => true));
        $activationUrl = $this->getActivationUrl($user, $langCode);
        $userName = $user->getUserCompleteName();

        return $this->render('FrontEndBundle:mails:enableAccountLateReminder.html.twig', array(
                    'enableUrl' => $activationUrl,
                    'user_name' => $userName,
                    'user_locale' => $langCode
        ));
    }

    private function getActivationUrl($user, $userLocale) {
        $encodedString = $this->get('Secure')->getEncodedUserString($user);
        $enableUrl = $this->get('router')->generate('frontend_enable_user', array(
            'string' => $encodedString,
            'locale' => $userLocale,
            '_locale' => $userLocale), true);
        return $enableUrl;
    }

    private function sendEmail($newMethod, $mail, $body, $subject)
    {
        if ($newMethod) {
                $service_email = $this->get('mycp.service.email_manager');
                $service_email->sendEmail($mail, $subject. " (Nuevo)" , $body);

                $message = 'Mensaje enviado utilizando el método actual.';
                $this->get('session')->getFlashBag()->add('message_ok', $message);

            } else {
                $service_email = $this->get('Email');
                $service_email->sendEmail($subject. " (Antiguo)", 'no-reply@mycasaparticular.com', 'MyCasaParticular.com', $mail, $body);

                $message = 'Mensaje enviado utilizando el método antiguo.';
                $this->get('session')->getFlashBag()->add('message_ok', $message);
            }
        return $this->redirect($this->generateUrl('mycp_test_home'));
    }

}
