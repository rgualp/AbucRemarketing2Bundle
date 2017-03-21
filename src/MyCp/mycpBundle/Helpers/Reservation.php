<?php

/**
 * Description of Reservation
 *
 * @author Yanet Morales
 */

namespace MyCp\mycpBundle\Helpers;

use Doctrine\ORM\EntityManager;
use Doctrine\Common\Collections\ArrayCollection;
use MyCp\mycpBundle\Entity\generalReservation;
use MyCp\mycpBundle\Entity\ownershipReservation;

class Reservation  {

    public static function sendNewOfferToTeam($controller, $emailService, $tourist, $newReservations, $arrayNights, $oldReservation)
    {
        $bookings = array();
        $totalAmount = 0;

        foreach($newReservations as $reservation){
            $booking = $reservation->getOwnResReservationBooking();
            if($booking != null && !in_array($booking->getBookingId(), $bookings))
            {
                $totalAmount += $booking->getPayedAmount();
                $bookings[] = $booking->getBookingId();
            }
        }

        $body = $controller->render(
            'FrontEndBundle:mails:rt_newOffer.html.twig',
            array(
                'user' => $tourist->getUserTouristUser(),
                'user_tourist' => array($tourist),
                'reservations' => $newReservations,
                'nights' => $arrayNights,
                'oldReservation' => $oldReservation,
                'payedAmount' => $totalAmount
            )
        );

        $emailService->sendEmail(
            'reservation@mycasaparticular.com', "Nueva oferta", $body, 'no-reply@mycasaparticular.com'
        );
    }

}

?>
