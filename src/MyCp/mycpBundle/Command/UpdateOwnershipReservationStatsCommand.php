<?php

namespace MyCp\mycpBundle\Command;

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

        if ($input->getOption('all')) {
            $date = null;
        }
        else{
            $argDate = $input->getArgument("date");
            if ($argDate != null || $argDate != "")
                $date = $timer->add("-4 day", $argDate, "Y-m-d");
            else
                $date = $timer->add("-4 day", new \DateTime(), "Y-m-d");


        }

        $ownerships=$em->getRepository("mycpBundle:ownership")->getWithReservations($date);

        if($date == null)
            $output->writeln("Updating all reservations statistics");
        else
            $output->writeln("Updating received reservations stats from ". $date);

        foreach($ownerships as $ownership) {
            $stats = $repository->calculateStats($ownership, $date, $timer);
            $output->writeln($ownership->getOwnMcpCode()." inserting about ".count($stats). " reservation statistics.");

            foreach ($stats as $stat) {
                $output->writeln($stat->getStatNomenclator()->getNomName());
                $repository->insertOrUpdateObj($stat);
            }

            $em->flush();
        }
        $output->writeln("End of process");
    }

}