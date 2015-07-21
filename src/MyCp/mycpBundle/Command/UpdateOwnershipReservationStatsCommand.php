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
                ->setHelp(<<<EOT
                Command <info>mycp_task:stats:update-ownership</info> Update ownership-reservation stats table.
EOT
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $container = $this->getContainer();
        $em = $container->get('doctrine')->getManager();
        $repository = $em->getRepository("mycpBundle:ownershipReservationStat");
        $ownerships=$em->getRepository("mycpBundle:ownership")->findAll();
        $output->writeln("Updating received reservations stats...");
        $stats=$repository->getTotalReservations($ownerships);
        foreach($stats as $stat){
            $repository->insertOrUpdateObj($stat);
        }

        $em->flush();
        $output->writeln("End of process");
    }

}