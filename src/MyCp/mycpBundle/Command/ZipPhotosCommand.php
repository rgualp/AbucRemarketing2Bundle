<?php

namespace MyCp\mycpBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\Output;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

/*
 * This command must run weekly
 */

class ZipPhotosCommand extends ContainerAwareCommand {

    protected function configure() {
        $this
                ->setName('mycp:zip_photos')
                ->setDefinition(array())
                ->setDescription('Create a .zip file with all the photos of an accommodation');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $container = $this->getContainer();
        $em = $container->get('doctrine')->getManager();

        $output->writeln(date(DATE_W3C) . ': Starting zip photo command...');

        $ownerships = $em->getRepository('mycpBundle:ownership')->findAll();
        $zipService = $container->get('mycp.service.zip');

        if (empty($ownerships)) {
            $output->writeln('No accommodations found.');
            return 0;
        }

        foreach($ownerships as $own)
        {
            $zipService->createZipFile($own->getOwnId(), $own->getOwnMcpCode());
        }


        $output->writeln('Operation completed!!!');
        return 0;
    }

}
