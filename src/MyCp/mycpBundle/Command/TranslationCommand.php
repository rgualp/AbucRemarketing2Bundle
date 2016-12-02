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

class TranslationCommand extends ContainerAwareCommand {

    protected function configure() {
        $this
                ->setName('mycp:translate')
                ->setDefinition(array(
                    new InputOption('from-lang-code', 'from-code', InputOption::VALUE_REQUIRED),
                    new InputOption('to-lang-code', 'to-code', InputOption::VALUE_REQUIRED)
                ))
                ->setDescription('Translate all empty description of a language from English');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $container = $this->getContainer();
        $em = $container->get('doctrine')->getManager();
        $translatorService = $container->get('mycp.translator.service');
        $toLangcode= $input->getOption('to-lang-code');
        $fromLangcode= $input->getOption('from-lang-code');

        $output->writeln(date(DATE_W3C) . ': Starting translator command...');

        //Select all ownership with description in English and no description in given language
        $targetLanguage = $em->getRepository('mycpBundle:lang')->findOneBy(array("lang_code" => strtoupper($toLangcode)));
        $sourceLanguage = $em->getRepository('mycpBundle:lang')->findOneBy(array("lang_code" => strtoupper($fromLangcode)));

        if($targetLanguage == null)
        {
            $output->writeln("Error: Language with code ".$toLangcode." do not exists in database");
            $output->writeln('Bye bye!');
            return;
        }

        if($sourceLanguage == null)
        {
            $output->writeln("Error: Language with code ".$fromLangcode." do not exists in database");
            $output->writeln('Bye bye!');
            return;
        }

        $untranslatedAccommodations = $em->getRepository("mycpBundle:ownershipDescriptionLang")->getAccommodationsToTranslate(strtolower($fromLangcode), strtolower($toLangcode));

        $output->writeln("Let's translate ".count($untranslatedAccommodations).' accommodations to '. $targetLanguage->getLangName());

        foreach($untranslatedAccommodations as $untranslatedOwnership) {
            $output->writeln('Analizing ' . $untranslatedOwnership->getOwnMcpCode());
            $sourceDescription = $em->getRepository("mycpBundle:ownershipDescriptionLang")->getDescriptionsByAccommodation($untranslatedOwnership, strtolower($fromLangcode));

            $translatedDescription = $em->getRepository("mycpBundle:ownershipDescriptionLang")->getDescriptionsByAccommodation($untranslatedOwnership, strtolower($toLangcode));

            if ($translatedDescription == null)
                $translatedDescription = new ownershipDescriptionLang();

            $briefDescription = $translatedDescription->getOdlBriefDescription();

            $description = $translatedDescription->getOdlDescription();
            $translated = $translatedDescription->getOdlAutomaticTranslation();

            if ($sourceDescription != null) {
                if ($briefDescription == "" && $description == "" && $sourceDescription->getOdlDescription() != "" && $sourceDescription->getOdlBriefDescription() != "") {
                    $output->writeln('Full translating ' . $untranslatedOwnership->getOwnMcpCode());
                    $response = $translatorService->multipleTranslations(array($sourceDescription->getOdlDescription(), $sourceDescription->getOdlBriefDescription()), strtolower($fromLangcode), strtolower($toLangcode));

                    if ($response[0]->getCode() == TranslatorResponseStatusCode::STATUS_200) {
                        $description = $response[0]->getTranslation();
                        $translated = true;
                    }

                    if ($response[1]->getCode() == TranslatorResponseStatusCode::STATUS_200) {
                        $briefDescription = $response[1]->getTranslation();
                        //$translated = true;
                    }
                } else if ($briefDescription == "" && $sourceDescription->getOdlBriefDescription() != "") {
                    $output->writeln('Translating brief description of ' . $untranslatedOwnership->getOwnMcpCode());
                    $response = $translatorService->translate($sourceDescription->getOdlBriefDescription(), strtolower($fromLangcode), strtolower($toLangcode));

                    if ($response->getCode() == TranslatorResponseStatusCode::STATUS_200) {
                        $briefDescription = $response->getTranslation();
                        //$translated = true;
                    }
                } else if ($description == "" && $sourceDescription->getOdlDescription() != "") {
                    $output->writeln('Translating description of ' . $untranslatedOwnership->getOwnMcpCode());
                    $response = $translatorService->translate($sourceDescription->getOdlDescription(), strtolower($fromLangcode), strtolower($toLangcode));

                    if ($response->getCode() == TranslatorResponseStatusCode::STATUS_200) {
                        $description = $response->getTranslation();
                        $translated = true;
                    }
                }

                $translatedDescription->setOdlIdLang($targetLanguage)
                    ->setOdlDescription($description)
                    ->setOdlBriefDescription($briefDescription)
                    ->setOdlOwnership($untranslatedOwnership)
                    ->setOdlAutomaticTranslation($translated);

                $em->persist($translatedDescription);
                $em->flush();
            }
        }

        $output->writeln('You are amazing with translations!');
        return 0;
    }

}
