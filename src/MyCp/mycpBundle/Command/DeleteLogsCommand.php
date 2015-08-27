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
 * This command must run weekly
 */

class DeleteLogsCommand extends ContainerAwareCommand {

    protected function configure() {
        $this
                ->setName('mycp_task:delete_logs')
                ->setDefinition(array())
                ->setDescription('Delete logs every 3 months');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $container = $this->getContainer();
        $em = $container->get('doctrine')->getManager();
        $timer = $container->get('Time');
        $logger = $container->get('log');

        $output->writeln(date(DATE_W3C) . ': Start exporting and deleting logs command...');
        $date = new \DateTime();
        $date = $timer->add("-3 month", $date->format("Y-m-d") , "Y-m-d");

        $logs = $em->getRepository('mycpBundle:log')->getOldLogs($date);

        if (empty($logs)) {
            $output->writeln('No logs found for export and delete.');
            return 0;
        }

        $output->writeln('Exporting and deleting: ' . count($logs));

        try{
            $logger->exportAndDeleteLogs($logs);
        } catch (\Exception $e) {
            $message = "An error has ocurred " . PHP_EOL . $e->getMessage();
            $output->writeln($message);
        }

        $output->writeln('Operation completed!!!');
        return 0;
    }

}
