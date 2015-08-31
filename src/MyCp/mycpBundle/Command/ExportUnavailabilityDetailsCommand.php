<?php

namespace MyCp\mycpBundle\Command;

use MyCp\mycpBundle\Helpers\FileIO;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\Output;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;


class ExportUnavailabilityDetailsCommand extends ContainerAwareCommand {

    protected function configure() {
        $this->setName('mycp_task:export-udetails')
                ->setDefinition(array())
                ->setDescription('Export and delete unavailability details')->setHelp(<<<EOT
                Command <info>mycp_task:export-udetails</info> Export and delete unavailability details.
EOT
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $container = $this->getContainer();
        $em = $container->get('doctrine')->getManager();
        $timer = $container->get('Time');
        $exporter = $container->get('mycp.service.export_to_excel');
        $uDetailsDirectory = $this->getContainer()->getParameter("configuration.dir.udetails");

        $output->writeln(date(DATE_W3C) . ': Start exporting and deleting unavailability details older than 6 months command...');
        $date = new \DateTime();
        $date = $timer->add("-6 month", $date->format("Y-m-d") , "Y-m-d");

        $uDetails = $em->getRepository('mycpBundle:unavailabilityDetails')->getOldDetails($date);

        if (empty($uDetails)) {
            $output->writeln('No unavailability details older than 6 months found for export and delete.');
            return 0;
        }

        $output->writeln('Exporting and deleting: ' . count($uDetails));

        try{
            $exporter->exportAndDeleteUDetails($uDetails, $uDetailsDirectory);
        } catch (\Exception $e) {
            $message = "An error has ocurred " . PHP_EOL . $e->getMessage();
            $output->writeln($message);
        }

        $output->writeln('Operation completed!!!');
        return 0;
    }

}