<?php

/**
 * Description of VoucherHelper
 *
 * @author Yanet Morales
 */

namespace MyCp\mycpBundle\Helpers;

class VoucherHelper {

    public static function sendVoucher($entity_manager, $bookingService, $emailService, $controller, $idReservation, $emailToSend, $replaceExistingVoucher = false) {

        try {
            $bookings_ids = $entity_manager->getRepository('mycpBundle:generalReservation')->getBookings($idReservation);
            $genRes = $entity_manager->getRepository('mycpBundle:generalReservation')->find($idReservation);
            $userTourist = $entity_manager->getRepository('mycpBundle:userTourist')->findOneBy(array('user_tourist_user' => $genRes->getGenResUserId()));

            foreach ($bookings_ids as $bookId) {
                $bookId = $bookId['booking_id'];

                // one could also use $bookingService->getVoucherFilePathByBookingId($bookingId) here, but then the PDF is not created

                $pdfFilePath = $bookingService->createBookingVoucherIfNotExisting($bookId, $replaceExistingVoucher);

                $body = $controller->render('FrontEndBundle:mails:rt_voucher.html.twig', array(
                    'user' => $userTourist->getUserTouristUser(),
                    'user_tourist' => $userTourist,
                    'booking_id' => $bookId,
                    'generalReservation' => $genRes
                ));

                $emailService->sendEmail(
                        $emailToSend, 'Voucher del booking ID_' . $bookId . ' (' . $genRes->getCASId() . ')', $body, 'no-reply@mycasaparticular.com', $pdfFilePath
                );
            }

            $message = 'Se ha enviado satisfactoriamente el voucher asociado a la reservación ' . $genRes->getCASId();
            $controller->get('session')->getFlashBag()->add('message_ok', $message);
        } catch (\Exception $e) {
            $CASId = \MyCp\FrontEndBundle\Helpers\ReservationHelper::getCASId($idReservation);
            $message = 'Error al enviar el voucher asociado a la reservación ' . $CASId . ". " . $e->getMessage();
            $controller->get('session')->getFlashBag()->add('message_error_main', $message);
        }
    }

    public static function sendVoucherToClient($entity_manager, $bookingService, $emailService, $controller, $genRes, $subjectTranslatedKey, $message = null, $createVoucher = true) {

        $idReservation = $genRes->getGenResId();
        try {
            $bookings_ids = $entity_manager->getRepository('mycpBundle:generalReservation')->getBookings($idReservation);
            $user = $genRes->getGenResUserId();
            $userTourist = $entity_manager->getRepository('mycpBundle:userTourist')->findOneBy(array('user_tourist_user' => $user));

            foreach ($bookings_ids as $bookId) {
                $bookId = $bookId['booking_id'];

                $pdfFilePath = "";
                if($createVoucher)
                    $pdfFilePath = $bookingService->createBookingVoucher($bookId);
                else
                    $pdfFilePath = $bookingService->createBookingVoucherIfNotExisting($bookId);

                /*$ownershipReservations = $entity_manager->getRepository('mycpBundle:ownershipReservation')
                        ->findBy(array('own_res_reservation_booking' => $bookId));
                $serviceTime = $controller->get('time');
                $nights = array();

                foreach ($ownershipReservations as $res) {
                    $resNights = $serviceTime->nights($res->getOwnResReservationFromDate()->getTimestamp(), $res->getOwnResReservationToDate()->getTimestamp());
                    array_push($nights, $resNights);
                }*/

                $userLocale = strtolower($userTourist->getUserTouristLanguage()->getLangCode());
                $body = $controller->render('FrontEndBundle:mails:boletin.html.twig', array(
                    'user_locale' => $userLocale,
                    'user' => $user,
                    //'reservations' => $ownershipReservations,
                    'message' => $message,
                    //'nights' => $nights
                ));

                $locale = $controller->get('translator');
                $subject = $locale->trans($subjectTranslatedKey, array(), "messages", $userLocale);
                $emailService->sendEmail(
                        $user->getUserEmail(), $subject, $body, 'no-reply@mycasaparticular.com', $pdfFilePath
                );
            }

            $message = 'Se ha enviado satisfactoriamente al cliente el voucher asociado a la reservación ' . $genRes->getCASId();
            $controller->get('session')->getFlashBag()->add('message_ok', $message);
        } catch (\Exception $e) {
            $CASId = \MyCp\FrontEndBundle\Helpers\ReservationHelper::getCASId($idReservation);
            $message = 'Error al enviar el voucher asociado a la reservación ' . $CASId . ". " . $e->getMessage();
            $controller->get('session')->getFlashBag()->add('message_error_main', $message);
        }
    }

    public static function sendVoucherToClientTest($entity_manager, $bookingService, $emailService, $controller, $genRes, $subjectTranslatedKey, $email, $message = null, $createVoucher = true) {

        $idReservation = $genRes->getGenResId();
        try {
            $bookings_ids = $entity_manager->getRepository('mycpBundle:generalReservation')->getBookings($idReservation);
            $user = $genRes->getGenResUserId();
            $userTourist = $entity_manager->getRepository('mycpBundle:userTourist')->findOneBy(array('user_tourist_user' => $user));

            foreach ($bookings_ids as $bookId) {
                $bookId = $bookId['booking_id'];

                $pdfFilePath = "";
                if($createVoucher)
                    $pdfFilePath = $bookingService->createBookingVoucher($bookId);
                else
                    $pdfFilePath = $bookingService->createBookingVoucherIfNotExisting($bookId);

                /*$ownershipReservations = $entity_manager->getRepository('mycpBundle:ownershipReservation')
                        ->findBy(array('own_res_reservation_booking' => $bookId));
                $serviceTime = $controller->get('time');
                $nights = array();

                foreach ($ownershipReservations as $res) {
                    $resNights = $serviceTime->nights($res->getOwnResReservationFromDate()->getTimestamp(), $res->getOwnResReservationToDate()->getTimestamp());
                    array_push($nights, $resNights);
                }*/

                $userLocale = strtolower($userTourist->getUserTouristLanguage()->getLangCode());
                $body = $controller->render('FrontEndBundle:mails:boletin.html.twig', array(
                    'user_locale' => $userLocale,
                    'user' => $user,
                    //'reservations' => $ownershipReservations,
                    'message' => $message,
                    //'nights' => $nights
                ));

                $locale = $controller->get('translator');
                $subject = $locale->trans($subjectTranslatedKey, array(), "messages", $userLocale);
                $emailService->sendEmail(
                    $email, $subject, $body, 'no-reply@mycasaparticular.com', $pdfFilePath
                );
            }

            $message = 'Se ha enviado satisfactoriamente al cliente el voucher asociado a la reservación ' . $genRes->getCASId();
            $controller->get('session')->getFlashBag()->add('message_ok', $message);
        } catch (\Exception $e) {
            $CASId = \MyCp\FrontEndBundle\Helpers\ReservationHelper::getCASId($idReservation);
            $message = 'Error al enviar el voucher asociado a la reservación ' . $CASId . ". " . $e->getMessage();
            $controller->get('session')->getFlashBag()->add('message_error_main', $message);
        }
    }

}

?>
