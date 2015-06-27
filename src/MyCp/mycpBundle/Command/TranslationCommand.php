<?php

namespace MyCp\mycpBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\Output;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use MyCp\mycpBundle\Entity\mailList;

class TranslationCommand extends ContainerAwareCommand {

    protected function configure() {
        $this
                ->setName('mycp:translate')
                ->setDefinition(array())
                ->setDescription('Translate all empty German description from English');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $container = $this->getContainer();
        $em = $container->get('doctrine')->getManager();
        $translatorService = $container->get('mycp.translator.service');

        $output->writeln(date(DATE_W3C) . ': Starting translator command...');

        /*$test = $translatorService->multipleTranslations(array("Hello World!", "Error message"), "en", "de");
        var_dump($test);*/

        //Select all ownership with description in English and no description in Deutch


        $output->writeln('Operation completed!!!');
        return 0;
    }

}
