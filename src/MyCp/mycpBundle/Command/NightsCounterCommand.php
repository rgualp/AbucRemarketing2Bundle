<?php

namespace MyCp\mycpBundle\Command;

use Symfony\Component\Console\Helper\ProgressBar;
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

        ProgressBar::setFormatDefinition('minimal', 'Progress: %percent%%');
        ProgressBar::setFormatDefinition('minimal_nomax', '%percent%%');

        $progressBar = new ProgressBar($output, $totalReservations);
        $progressBar->setFormat('minimal');

        $output->writeln('Reservations total: '.$totalReservations);
        $i = 0;
        while($startIndex < $totalReservations) {
            $output->writeln('Index: '.$startIndex);
            $reservations = $em->getRepository("mycpBundle:ownershipReservation")->getOwnReservationsByPagesForNightsCounter($startIndex, $pageSize);
            foreach ($reservations as $reservation) {
                //if($reservation->getOwnResNights() == 0 || $reservation->setOwnResNights() == null) {
                    $nights = $timer->nights($reservation->getOwnResReservationFromDate()->getTimestamp(), $reservation->getOwnResReservationToDate()->getTimestamp());
                    $reservation->setOwnResNights($nights);

                    if($reservation->getOwnResNightPrice() != null && $reservation->getOwnResNightPrice() > 0)
                    {
                        $reservation->setOwnResTotalInSite($nights * $reservation->getOwnResNightPrice());
                    }

                    $em->persist($reservation);
                //}

                if($i++ < $totalReservations)
                    $progressBar->advance();
            }
            $em->flush();
            $startIndex += $pageSize;
        }

        $progressBar->finish();
    }

    private function updateGeneralReservation(OutputInterface $output)
    {
        $container = $this->getContainer();
        $em = $container->get('doctrine')->getManager();

        $startIndex = 0;
        $pageSize = 10;
        $totalReservations = $em->getRepository("mycpBundle:generalReservation")->getReservationsForNightCounterTotal();
        $output->writeln('Reservations total: '.$totalReservations);

        while($startIndex < $totalReservations || $totalReservations == 0) {
            $output->writeln('Index: '.$startIndex);
            $generalReservations = $em->getRepository("mycpBundle:generalReservation")->getReservationsByPagesForNightsCounter($startIndex, $pageSize);

        foreach($generalReservations as $gres)
        {
            if($gres->getGenResNights() == 0 || $gres->getGenResNights() == null) {
                $output->writeln('Updating: ' . $gres->getCASId());
                $reservations = $em->getRepository("mycpBundle:ownershipReservation")->findBy(array("own_res_gen_res_id" => $gres->getGenResId()));
                $nights = 0;
                $price = 0;

                foreach ($reservations as $reservation) {
                    $nights += $reservation->getOwnResNights();

                    if ($reservation->getOwnResNightPrice() != 0)
                        $price += $reservation->getOwnResNightPrice() * $reservation->getOwnResNights();
                    else
                        $price += $reservation->getOwnResTotalInSite();
                }

                $gres->setGenResNights($nights);
                if ($price != $gres->getGenResTotalInSite())
                    $gres->setGenResTotalInSite($price);

                $em->persist($gres);
            }
        }
            $em->flush();
            $startIndex += $pageSize;
        }
    }

}
