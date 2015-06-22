<?php

namespace MyCp\mycpBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\Output;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;


class UpdateOwnershipStatsCommand extends ContainerAwareCommand {

    protected function configure() {
        $this->setName('mycp_task:stats:update-ownership')
                ->setDefinition(array())
                ->setDescription('Update ownership stats table')
                ->setHelp(<<<EOT
                Command <info>mycp_task:stats:update-ownership</info> Update ownership stats table.
EOT
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $container = $this->getContainer();
        $em = $container->get('doctrine')->getManager();
        $ownStatRepository = $em->getRepository("mycpBundle:ownershipStat");
        $municipalities=$ownStatRepository->getMunicipalities();
        $output->writeln("Updating Status stats...");
        $stats=$ownStatRepository->getOwnershipTotalsByStatus($municipalities);
        foreach($stats as $stat){
            $ownStatRepository->insertOrUpdateObj($stat);
        }
        $output->writeln("Updating Type stats...");
        $stats=$ownStatRepository->getOwnershipTotalsByType($municipalities);
        foreach($stats as $stat){
            $ownStatRepository->insertOrUpdateObj($stat);
        }
        $output->writeln("Updating Category stats...");
        $stats=$ownStatRepository->getOwnershipTotalsByCategory($municipalities);
        foreach($stats as $stat){
            $ownStatRepository->insertOrUpdateObj($stat);
        }
        $output->writeln("Updating Summary stats...");
        $stats=$ownStatRepository->getOwnershipTotalsBySummary($municipalities);
        foreach($stats as $stat){
            $ownStatRepository->insertOrUpdateObj($stat);
        }
        $output->writeln("Updating Language stats...");
        $stats=$ownStatRepository->getOwnershipTotalsByLanguage($municipalities);
        foreach($stats as $stat){
            $ownStatRepository->insertOrUpdateObj($stat);
        }
        $output->writeln("Updating Rooms Number stats...");
        $stats=$ownStatRepository->getOwnershipTotalsByRoomsNumber($municipalities);
        foreach($stats as $stat){
            $ownStatRepository->insertOrUpdateObj($stat);
        }
        $em->flush();
        $output->writeln("End of process");
    }

}