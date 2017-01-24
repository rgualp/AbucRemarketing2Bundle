<?php

namespace MyCp\CasaModuleBundle\Service;

use Abuc\RemarketingBundle\Event\JobEvent;
use Doctrine\Common\Persistence\ObjectManager;
use MyCp\mycpBundle\Entity\generalReservation;
use MyCp\mycpBundle\Entity\notification;
use MyCp\mycpBundle\Entity\ownershipReservation;
use MyCp\mycpBundle\JobData\GeneralReservationJobData;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class NotificationService extends Controller
{
    /**
     * @var ObjectManager
     */
    private $em;

    protected $container;

    public function __construct(ObjectManager $em, $container)
    {
        $this->em = $em;
        $this->container = $container;
    }

    public function notificationresp(notification $notification, $availability, $sendEmail = true){
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
        }
        else {
            $generalReservation->setGenResStatus(generalReservation::STATUS_NOT_AVAILABLE);
        }

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
