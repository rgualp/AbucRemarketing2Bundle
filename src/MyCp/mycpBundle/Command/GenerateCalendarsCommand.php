<?php

namespace MyCp\mycpBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\Output;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

/*
 * This command must run every night
 */

class GenerateCalendarsCommand extends ContainerAwareCommand {

    protected function configure() {
        $this
                ->setName('mycp:generate_calendars')
                ->setDefinition(array())
                ->setDescription('Generate an .ics file for each room');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $container = $this->getContainer();
        $calendarService = $container->get('mycp.service.calendar');

        $output->writeln(date(DATE_W3C) . ': Starting to generate calendars...');

        try {
            
            $calendarService->createICalForAllAccommodations();

        } catch (\Exception $e) {
            $output->writeln($e->getMessage());
        }
        $output->writeln('Operation completed!!!');
        return 0;
    }

}
