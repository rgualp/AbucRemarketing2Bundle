<?php

namespace MyCp\mycpBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\Output;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use MyCp\mycpBundle\Entity\mailList;

/*
 * This command must run every day
 */

class NightsCounterCommand extends ContainerAwareCommand {

    protected function configure() {
        $this
                ->setName('mycp:nights_counter')
                ->setDefinition(array())
                ->setDescription('Counts nights of a reservation');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {

        $output->writeln(date(DATE_W3C) . ': Starting nights counter command...');
        $this->updateOwnershipReservations($output);
        $this->updateGeneralReservation($output);
        $output->writeln('Operation completed!!!');
        return 0;
    }

    private function updateOwnershipReservations(OutputInterface $output)
    {
        $container = $this->getContainer();
        $em = $container->get('doctrine')->getManager();
        $timer = $container->get("Time");

        $startIndex = 0;
        $pageSize = 10;
        $totalReservations = $em->getRepository("mycpBundle:ownershipReservation")->getOwnReservationsForNightsCounterTotal();

        $output->writeln('Reservations total: '.$totalReservations);
        while($startIndex < $totalReservations) {
            $output->writeln('Index: '.$startIndex);
            $reservations = $em->getRepository("mycpBundle:ownershipReservation")->getOwnReservationsByPagesForNightsCounter($startIndex, $pageSize);
            foreach ($reservations as $reservation) {
                $output->writeln('Updating ownershipReservation: ' . $reservation->getOwnResId());
                $nights = $timer->nights($reservation->getOwnResReservationFromDate()->getTimestamp(), $reservation->getOwnResReservationToDate()->getTimestamp());
                $reservation->setOwnResNights($nights);
                $em->persist($reservation);
            }
            $em->flush();
            $startIndex += $pageSize;
        }
    }

    private function updateGeneralReservation(OutputInterface $output)
    {
        $container = $this->getContainer();
        $em = $container->get('doctrine')->getManager();

        $startIndex = 0;
        $pageSize = 10;
        $totalReservations = $em->getRepository("mycpBundle:generalReservation")->getReservationsForNightCounterTotal();
        $output->writeln('Reservations total: '.$totalReservations);

        while($startIndex < $totalReservations) {
            $output->writeln('Index: '.$startIndex);
            $generalReservations = $em->getRepository("mycpBundle:generalReservation")->getReservationsByPagesForNightsCounter($startIndex, $pageSize);

        foreach($generalReservations as $gres)
        {
            $output->writeln('Updating: '.$gres->getCASId() );
            $reservations = $em->getRepository("mycpBundle:ownershipReservation")->findBy(array("own_res_gen_res_id" => $gres->getGenResId()));
            $nights = 0;
            $price = 0;

            foreach($reservations as $reservation)
            {
                $nights += $reservation->getOwnResNights();

                if($reservation->getOwnResNightPrice() != 0)
                    $price += $reservation->getOwnResNightPrice() * $reservation->getOwnResNights();
                else
                    $price += $reservation->getOwnResTotalInSite();
            }

            $gres->setGenResNights($nights);
            if($price != $gres->getGenResTotalInSite())
                $gres->setGenResTotalInSite($price);

            $em->persist($gres);
        }
            $em->flush();
            $startIndex += $pageSize;
        }
    }

}
