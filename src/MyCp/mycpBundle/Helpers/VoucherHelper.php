<?php

/**
 * Description of VoucherHelper
 *
 * @author Yanet Morales
 */

namespace MyCp\mycpBundle\Helpers;

class VoucherHelper {

    public static function sendVoucher($entity_manager, $bookingService, $emailService, $controller, $idReservation, $emailToSend) {

        try {
            $bookings_ids = $entity_manager->getRepository('mycpBundle:generalReservation')->getBookings($idReservation);
            $genRes = $entity_manager->getRepository('mycpBundle:generalReservation')->find($idReservation);
            $userTourist = $entity_manager->getRepository('mycpBundle:userTourist')->findOneBy(array('user_tourist_user' => $genRes->getGenResUserId()));

            foreach ($bookings_ids as $bookId) {
                $bookId = $bookId['booking_id'];

                // one could also use $bookingService->getVoucherFilePathByBookingId($bookingId) here, but then the PDF is not created
                $pdfFilePath = $bookingService->createBookingVoucherIfNotExisting($bookId);

                $body = $controller->render('FrontEndBundle:mails:rt_voucher.html.twig', array(
                    'user' => $userTourist->getUserTouristUser(),
                    'user_tourist' => $userTourist,
                    'booking_id' => $bookId,
                    'generalReservation' => $genRes
                ));

                $emailService->sendEmail(
                        $emailToSend,
                        'Voucher del booking ID_' . $bookId . ' (CAS.' . $genRes->getGenResId() . ')',
                        $body,
                        'no-reply@mycasaparticular.com', $pdfFilePath
                );
            }

            $message = 'Se ha enviado satisfactoriamente el voucher asociado a la reservación CAS.' . $genRes->getGenResId();
            $controller->get('session')->getFlashBag()->add('message_ok', $message);
        } catch (\Exception $e) {
            $message = 'Error al enviar el voucher asociado a la reservación CAS.' . $idReservation . ". " . $e->getMessage();
            $controller->get('session')->getFlashBag()->add('message_error_main', $message);
        }
    }

}

?>
