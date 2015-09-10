<?php

namespace MyCp\mycpBundle\Command;

use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\Output;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;


class UpdateOwnershipReservationStatsCommand extends ContainerAwareCommand {

    protected function configure() {
        $this->setName('mycp_task:stats:update-ownership-reservation')
                ->setDefinition(array())
                ->setDescription('Update ownership-reservation stats table')
                ->addArgument("date", InputArgument::OPTIONAL,"Starting date for get reservations", (new \DateTime())->format("Y-m-d"))
                ->addOption("all", null,InputOption::VALUE_NONE, "If set, analyse all reservations in database no matter if a date is pass as argument")
                ->setHelp(<<<EOT
                Command <info>mycp_task:stats:update-ownership-reservation</info> Update ownership-reservation stats table.
EOT
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $container = $this->getContainer();
        $em = $container->get('doctrine')->getManager();
        $timer = $container->get('Time');
        $repository = $em->getRepository("mycpBundle:ownershipReservationStat");
        $maxResults = 50;
        $startIndex = 0;

        $date = new \DateTime();
        $date = $timer->add("-4 day", $date->format("Y-m-d"), "Y-m-d");
        $olders = false;

        if ($input->getOption('all')) {
            $output->writeln("Updating all reservations statistics");
            $olders = true;
        }
        else{
            $argDate = $input->getArgument("date");
            if ($argDate != null || $argDate != "")
                $date = $timer->add("-4 day", $argDate, "Y-m-d");
            $output->writeln("Updating received reservations stats from ". $date);
        }

        //$ownerships=$em->getRepository("mycpBundle:ownership")->getWithReservations($date);

        $nomenclatorRepository=$em->getRepository("mycpBundle:nomenclatorStat");
        $nomReservations = $nomenclatorRepository->findOneBy(array('nom_name'=>'Solicitudes'));
        $nomReceived = $nomenclatorRepository->findOneBy(array('nom_name'=>'Recibidas', "nom_parent" => $nomReservations));
        $nomNonAvailable=$nomenclatorRepository->findOneBy(array('nom_name'=>'No disponibles', "nom_parent" => $nomReservations));
        $nomAvailable=$nomenclatorRepository->findOneBy(array('nom_name'=>'Marcadas como disponibles', "nom_parent" => $nomReservations));
        $nomReserved=$nomenclatorRepository->findOneBy(array('nom_name'=>'Reservadas', "nom_parent" => $nomReservations));
        $nomOutdated=$nomenclatorRepository->findOneBy(array('nom_name'=>'Vencidas', "nom_parent" => $nomReservations));

        $nomRent=$nomenclatorRepository->findOneBy(array('nom_name'=>'Hospedaje'));
        $nomGuests=$nomenclatorRepository->findOneBy(array('nom_name'=>'Huéspedes recibidos', "nom_parent" => $nomRent));
        $nomNights=$nomenclatorRepository->findOneBy(array('nom_name'=>'Noches reservadas', "nom_parent" => $nomRent));
        $nomRooms=$nomenclatorRepository->findOneBy(array('nom_name'=>'Habitaciones reservadas', "nom_parent" => $nomRent));

        $nomComment=$nomenclatorRepository->findOneBy(array('nom_name'=>'Comentarios'));
        $nomCommentsTotal=$nomenclatorRepository->findOneBy(array('nom_name'=>'Total', "nom_parent" => $nomComment));

        $nomIncomes=$nomenclatorRepository->findOneBy(array('nom_name'=>'Ingresos'));
        $nomPossibleIncomesTotal=$nomenclatorRepository->findOneBy(array('nom_name'=>'Posibles inglesos totales', "nom_parent" => $nomIncomes));
        $nomAccommodationRealIncomes=$nomenclatorRepository->findOneBy(array('nom_name'=>'Ingresos reales (Casa)', "nom_parent" => $nomIncomes));
        $nomMyCPRealIncomes=$nomenclatorRepository->findOneBy(array('nom_name'=>'Ingresos reales (MyCP)', "nom_parent" => $nomIncomes));

        $nomenclators = array('received' => $nomReceived, 'nonAvailable' => $nomNonAvailable, 'available' => $nomAvailable, 'reserved' => $nomReserved,
        'outdated' => $nomOutdated, 'guests' => $nomGuests, 'nights' => $nomNights, 'rooms' => $nomRooms, 'commentsTotal' => $nomCommentsTotal,
        'possibleIncomes' => $nomPossibleIncomesTotal, 'accommodationIncomes' => $nomAccommodationRealIncomes, 'mycpIncomes' => $nomMyCPRealIncomes);

        $stats = $repository->calculateStats($nomenclators, $date, $timer, $olders, $startIndex, $maxResults);
        $index = 0;

        while(count($stats) > 0) {
            $index++;
            $output->writeln("Iteration " . $index);
            ProgressBar::setFormatDefinition('minimal', 'Progress: %percent%%');
            ProgressBar::setFormatDefinition('minimal_nomax', '%percent%%');

            $progress = new ProgressBar($output, count($stats));
            $progress->setFormat('minimal');

            $i = 0;
            foreach ($stats as $stat) {
                $repository->insertOrUpdateObj($stat);
                if ($i++ < count($stats))
                    $progress->advance();
            }

            $em->flush();
            $progress->finish();

            $startIndex = $startIndex + $maxResults;
            $stats = $repository->calculateStats($nomenclators, $date, $timer, $olders, $startIndex, $maxResults);
        }

        $startIndex = 0;
        $comments = $repository->calculateCommentsStats($nomCommentsTotal, $olders, $date, $startIndex, $maxResults);
        while(count($comments) > 0) {
            $index++;
            $output->writeln("Iteration " . $index);
            ProgressBar::setFormatDefinition('minimal', 'Progress: %percent%%');
            ProgressBar::setFormatDefinition('minimal_nomax', '%percent%%');

            $progress = new ProgressBar($output, count($stats));
            $progress->setFormat('minimal');

            $i = 0;
            foreach ($comments as $stat) {
                $repository->insertOrUpdateObj($stat);
                if ($i++ < count($stats))
                    $progress->advance();
            }

            $em->flush();
            $progress->finish();

            $startIndex = $startIndex + $maxResults;
            $comments = $repository->calculateCommentsStats($nomCommentsTotal, $olders, $date, $startIndex, $maxResults);
        }
        $output->writeln("End of process");
    }

}