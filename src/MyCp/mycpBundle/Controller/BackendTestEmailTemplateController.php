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
        $userTourist = $em->getRepository("mycpBundle:userTourist")->findOneBy(array("user_tourist_user" => $user->getUserId()));

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
                    'generalReservationId' => $generalReservation->getGenResId(),
                    'user_currency' => ($userTourist != null) ? $userTourist->getUserTouristCurrency() : null
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
        $userTourist = $em->getRepository("mycpBundle:userTourist")->findOneBy(array("user_tourist_user" => $user->getUserId()));
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
                    'initial_payment' => $initialPayment,
                    'user_currency' => ($userTourist != null) ? $userTourist->getUserTouristCurrency() : null
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
        $userTourist = $em->getRepository("mycpBundle:userTourist")->findOneBy(array("user_tourist_user" => $user->getUserId()));

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
                    'generalReservationId' => $generalReservation->getGenResId(),
                    'user_currency' => ($userTourist != null) ? $userTourist->getUserTouristCurrency() : null
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

    private function sendEmail($newMethod, $mail, $body, $subject) {
        if ($newMethod) {
            $service_email = $this->get('mycp.service.email_manager');
            $service_email->sendEmail($mail, $subject . " (Nuevo)", $body);

            $message = 'Mensaje enviado utilizando el método actual.';
            $this->get('session')->getFlashBag()->add('message_ok', $message);
        } else {
            $service_email = $this->get('Email');
            $service_email->sendEmail($subject . " (Antiguo)", 'no-reply@mycasaparticular.com', 'MyCasaParticular.com', $mail, $body);

            $message = 'Mensaje enviado utilizando el método antiguo.';
            $this->get('session')->getFlashBag()->add('message_ok', $message);
        }
        return $this->redirect($this->generateUrl('mycp_test_home'));
    }

    public function cartFullReminderAction($langCode) {
        $em = $this->getDoctrine()->getEntityManager();
        $userTourist = $em->getRepository('mycpBundle:userTourist')->findOneBy(array());
        $userName = $userTourist->getUserTouristUser()->getUserCompleteName();
        $cartItems = $em->getRepository('mycpBundle:cart')->testValues($userTourist->getUserTouristUser());
        $service_time = $this->get('Time');

        $accommodations = array();
        $cartAccommodations = array();
        $cartPrices = array();

        $current_own_id = 0;

        foreach ($cartItems as $item) {
            if ($item->getCartRoom()->getRoomOwnership()->getOwnId() != $current_own_id) {
                $current_own_id = $item->getCartRoom()->getRoomOwnership()->getOwnId();
                array_push($accommodations, $item->getCartRoom()->getRoomOwnership());

                $cartAccommodations[$current_own_id] = array();
                $cartPrices[$current_own_id] = array();
            }

            array_push($cartAccommodations[$current_own_id], $item);
            array_push($cartPrices[$current_own_id], $item->calculatePrice($em, $service_time, $this->container->getParameter('configuration.triple.room.charge'), $this->container->getParameter('configuration.service.fee')));
        }

        $photos = $em->getRepository("mycpBundle:ownership")->get_photos_array($accommodations);

        return $this->render('FrontEndBundle:mails:cartFull.html.twig', array(
                    'user_name' => $userName,
                    'user_locale' => $langCode,
                    'owns' => $accommodations,
                    'cartItems' => $cartAccommodations,
                    'photos' => $photos,
                    'prices' => $cartPrices,
                    'user_currency' => $userTourist->getUserTouristCurrency()
        ));
    }

    public function feedbackReminderAction($langCode) {
        $em = $this->getDoctrine()->getEntityManager();
        $service_time = $this->get('time');


        $generalReservation = $em
                ->getRepository('mycpBundle:generalReservation')
                ->findOneBy(array('gen_res_status' => generalReservation::STATUS_RESERVED));

        $user = $generalReservation->getGenResUserId();
        //$userTourist = $em->getRepository("mycpBundle:userTourist")->findOneBy(array("user_tourist_user" => $user->getUserId()));
        $userName = $user->getUserCompleteName();

        $ownershipReservations = $em
                ->getRepository('mycpBundle:generalReservation')
                ->getOwnershipReservations($generalReservation);

        $arrayPhotos = array();
        $arrayNights = array();

        $photos = $em
                ->getRepository('mycpBundle:ownership')
                ->getPhotos($generalReservation->getGenResOwnId()->getOwnId());

        if (!empty($photos)) {
            array_push($arrayPhotos, $photos);
        }

        foreach ($ownershipReservations as $ownershipReservation) {
            $array_dates = $service_time
                    ->datesBetween(
                    $ownershipReservation->getOwnResReservationFromDate()->getTimestamp(), $ownershipReservation->getOwnResReservationToDate()->getTimestamp()
            );

            array_push($arrayNights, count($array_dates) - 1);
        }

        return $this->render('FrontEndBundle:mails:feedback.html.twig', array(
                    'reservations' => $ownershipReservations,
                    'photos' => $arrayPhotos,
                    'nights' => $arrayNights,
                    'generalReservationId' => $generalReservation->getGenResId(),
                    'user_name' => $userName,
                    'user_locale' => $langCode,
        ));
    }

    public function accommodationNotificationAction() {
        $em = $this->getDoctrine()->getEntityManager();
        $ownerships = $em->getRepository('mycpBundle:ownership')->getNotSendedToReservationTeam();

        return $this->render('FrontEndBundle:mails:rt_accommodation_notification.html.twig', array(
                    'ownerships' => $ownerships
        ));
    }

    public function checkAvailableAction($casId, Request $request) {
        $em = $this->getDoctrine()->getEntityManager();
        $service_time = $this->get('time');

        if($casId == null)
        $generalReservation = $em
                ->getRepository('mycpBundle:generalReservation')
                ->findOneBy(array('gen_res_status' => generalReservation::STATUS_AVAILABLE));
        else
            $generalReservation = $em
                ->getRepository('mycpBundle:generalReservation')->find($casId);

        $user = $generalReservation->getGenResUserId();
        $userTourist = $em->getRepository("mycpBundle:userTourist")->findOneBy(array("user_tourist_user" => $user->getUserId()));

        $ownershipReservations = $em
                ->getRepository('mycpBundle:generalReservation')
                ->getOwnershipReservations($generalReservation);

        $arrayNights = array();

        foreach ($ownershipReservations as $ownershipReservation) {
            $array_dates = $service_time
                    ->datesBetween(
                    $ownershipReservation->getOwnResReservationFromDate()->getTimestamp(), $ownershipReservation->getOwnResReservationToDate()->getTimestamp()
            );

            array_push($arrayNights, count($array_dates) - 1);
        }

        return $this->render('FrontEndBundle:mails:rt_email_check_available.html.twig', array(
                    'user' => $user,
                    'user_tourist' => $userTourist,
                    'reservations' => $ownershipReservations,
                    'nigths' => $arrayNights,
                    'comment' => $request->getSession()->get('message_cart')
        ));
    }

    public function sendVoucherAction($mail, Request $request) {
        if ($request->getMethod() == 'POST') {
            $em = $this->getDoctrine()->getEntityManager();
            $generalReservation = $em
                    ->getRepository('mycpBundle:generalReservation')
                    ->findOneBy(array('gen_res_status' => generalReservation::STATUS_RESERVED));
            $bookingService = $this->get('front_end.services.booking');
            $service_email = $this->get('mycp.service.email_manager');
            \MyCp\mycpBundle\Helpers\VoucherHelper::sendVoucher($em, $bookingService, $service_email, $this, $generalReservation->getGenResId(), $mail);
        }
        return $this->redirect($this->generateUrl('mycp_test_home'));
    }

}

