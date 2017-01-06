<?php

namespace MyCp\mycpBundle\Command;

use Abuc\RemarketingBundle\Event\JobEvent;
use MyCp\mycpBundle\Entity\generalReservation;
use MyCp\mycpBundle\Entity\ownershipReservation;
use MyCp\mycpBundle\JobData\GeneralReservationJobData;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use JMS\DiExtraBundle\Annotation as DI;

class AvailabilityOwnerCommand extends ContainerAwareCommand {

    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @var \Doctrine\Common\Persistence\ObjectManager
     */
    private $em;

    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    private $container;

    /**
     * @var \MyCp\mycpBundle\Entity\availabilityOwnerRepository
     */
    private $availabilityOwnerRepository;

    protected function configure() {
        $this->setName('availability:owner:update')->setDescription('Actualizar disponibilidad dada por propietarios desde MyCasa Renta');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $now = new \DateTime();
        $now_format = $now->format('Y-m-d H:i:s');
        $output->writeln('<info>**** ---------------------Inicio:' . $now_format .'--------------------- ****</info>');
        $output->writeln('<info>**** Recopilando disponibilidades dadas por propietarios ****</info>');

        $this->container = $this->getContainer();
        $this->em = $this->container->get('doctrine')->getManager();
        $this->availabilityOwnerRepository = $this->em->getRepository("mycpBundle:availabilityOwner");

        $r = $this->availabilityOwnerRepository->availabilityOwner();
        $output->writeln('<info>**** Cantidad de disponibilidades no leidas ' . count($r) . ' ****</info>');

        foreach ($r as $availabilityOwner){
            /*Proceso de actualización*/
            $generalReservation = $availabilityOwner->getReservation();
            $availability = $availabilityOwner->getResStatus();

            $ownership_reservations = $generalReservation->getOwnReservations();
            foreach ($ownership_reservations as $ownership_reservation) {
                $output->writeln('<info>**** Ownership_reservation:' . $ownership_reservation->getOwnResId() . ' ****</info>');

                if($availability == 1){
                    $ownership_reservation->setOwnResStatus(ownershipReservation::STATUS_AVAILABLE);
                }
                else{
                    $ownership_reservation->setOwnResStatus(ownershipReservation::STATUS_NOT_AVAILABLE);
                }
                $this->em->persist($ownership_reservation);
                $this->em->flush();
                $output->writeln('<info>**** Ownership_reservation:' . $ownership_reservation->getOwnResId() . ' actualizada a:'.$ownership_reservation->getOwnResStatus().'****</info>');
            }

            if($availability == 1) {
                $generalReservation->setGenResStatus(generalReservation::STATUS_AVAILABLE);
            }
            else {
                $generalReservation->setGenResStatus(generalReservation::STATUS_NOT_AVAILABLE);
            }

            $output->writeln('<info>**** AvailabilityOwner:' . $availabilityOwner->getId() . ' ****</info>');
            $availabilityOwner->setActive(0);
            $this->em->persist($availabilityOwner);
            $this->em->flush();
            $output->writeln('<info>**** AvailabilityOwner:' . $availabilityOwner->getId() . ' actualizada a:'.$availabilityOwner->getActive().'****</info>');

            /*Envio de Email*/
            $output->writeln('<info>**** Enviando Correo ****</info>');
            $container = $this->getContainer();
            $id_reservation = $generalReservation->getGenResId();
            $service_email = $container->get('Email');
            $service_email->sendReservation($id_reservation, "", false);
            $output->writeln('<info>**** Enviando Correo ****</info>');

            if ($generalReservation->getGenResStatus() == generalReservation::STATUS_AVAILABLE){
                $output->writeln('<info>**** Incertando job ****</info>');
                // inform listeners that a reservation was sent out
                $dispatcher = $container->get('event_dispatcher');
                $eventData = new GeneralReservationJobData($id_reservation);
                $dispatcher->dispatch('mycp.event.reservation.sent_out', new JobEvent($eventData));
                $output->writeln('<info>**** Job incertado ****</info>');
            }
        }

        $now = new \DateTime();
        $now_format = $now->format('Y-m-d H:i:s');
        $output->writeln('<info>**** -------------------------Fin:' . $now_format .'--------------------------- ****</info>');
    }

}