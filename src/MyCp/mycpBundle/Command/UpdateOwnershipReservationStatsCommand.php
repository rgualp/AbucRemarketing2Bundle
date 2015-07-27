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
                ->setHelp(<<<EOT
                Command <info>mycp_task:stats:update-ownership-reservation</info> Update ownership-reservation stats table.
EOT
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $container = $this->getContainer();
        $em = $container->get('doctrine')->getManager();
        $timer = $container->get('Time');
        $argDate = $input->getArgument("date");
        $date = $timer->add("-4 day", $argDate, "Y-m-d");

        $repository = $em->getRepository("mycpBundle:ownershipReservationStat");
        $ownerships=$em->getRepository("mycpBundle:ownership")->getWithReservations($date);
        $output->writeln("Updating received reservations stats...");

        foreach($ownerships as $ownership) {
            $stats = $repository->calculateStats($ownership, $date, $timer);
            $output->writeln($ownership->getOwnMcpCode()." inserting ".count($stats). " reservation statistics.");

            foreach ($stats as $stat) {
                $repository->insertOrUpdateObj($stat);
            }

            $em->flush();
        }
        $output->writeln("End of process");
    }

}