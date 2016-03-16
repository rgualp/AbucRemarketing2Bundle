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
        $body = $controller->render(
            'FrontEndBundle:mails:rt_newOffer.html.twig',
            array(
                'user' => $tourist->getUserTouristUser(),
                'user_tourist' => array($tourist),
                'reservations' => $newReservations,
                'nights' => $arrayNights,
                'oldReservation' => $oldReservation
            )
        );

        $emailService->sendEmail(
            'reservation@mycasaparticular.com', "Nueva oferta", $body, 'no-reply@mycasaparticular.com'
        );
    }

}

?>
