<?php

namespace MyCp\mycpBundle\Command;

use MyCp\mycpBundle\Entity\ownershipDescriptionLang;
use MyCp\mycpBundle\Service\TranslatorResponseStatusCode;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\Output;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use MyCp\mycpBundle\Entity\mailList;

class TestTranslationCommand extends ContainerAwareCommand {

    protected function configure() {
        $this
                ->setName('mycp:test-translate')
                ->setDefinition(array())
                ->setDescription('Test translations');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $container = $this->getContainer();
        $em = $container->get('doctrine')->getManager();
        $translatorService = $container->get('mycp.translator.service');

        $output->writeln(date(DATE_W3C) . ': Starting translator command...');

        $response = $translatorService->translate("Esto es un texto de prueba a ver si lo traduces, mijito", "es", "en");

        $output->writeln("Traduciendo al ingles...");
        $output->writeln("Codigo: ".$response->getCode());
        $output->writeln("Error message: ".$response->getErrorMessage());
        $output->writeln("Traducción: ".$response->getTranslation());

        $response = $translatorService->translate("Esto es un texto de prueba a ver si lo traduces, mijito", "es", "de");

        $output->writeln("Traduciendo al alemán...");
        $output->writeln("Codigo: ".$response->getCode());
        $output->writeln("Error message: ".$response->getErrorMessage());
        $output->writeln("Traducción: ".$response->getTranslation());


        $output->writeln('Operation completed!!!');
        return 0;
    }

}
