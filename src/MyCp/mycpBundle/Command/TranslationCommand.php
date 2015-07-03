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
                ->setDefinition(array())
                ->setDescription('Translate all empty German description from English');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $container = $this->getContainer();
        $em = $container->get('doctrine')->getManager();
        $translatorService = $container->get('mycp.translator.service');

        $output->writeln(date(DATE_W3C) . ': Starting translator command...');

        //Select all ownership with description in English and no description in Deutch
        $untranslatedAccommodations = $em->getRepository("mycpBundle:ownershipDescriptionLang")->getAccommodationsToTranslate("en", "de");

        $output->writeln('Translating '.count($untranslatedAccommodations).' accommodations');
        $targetLanguage = $em->getRepository('mycpBundle:lang')->findOneBy(array("lang_code" => "DE"));
        foreach($untranslatedAccommodations as $untranslatedOwnership) {
            $output->writeln('Translating ' . $untranslatedOwnership->getOwnMcpCode());
            $sourceDescription = $em->getRepository("mycpBundle:ownershipDescriptionLang")->getDescriptionsByAccommodation($untranslatedOwnership, "en");

            $translatedDescription = $em->getRepository("mycpBundle:ownershipDescriptionLang")->getDescriptionsByAccommodation($untranslatedOwnership, "de");

            if ($translatedDescription == null)
                $translatedDescription = new ownershipDescriptionLang();

            $briefDescription = $translatedDescription->getOdlBriefDescription();

            $description = $translatedDescription->getOdlDescription();
            $translated = $translatedDescription->getOdlAutomaticTranslation();

            if ($sourceDescription != null) {
                if ($briefDescription == "" && $description == "" && $sourceDescription->getOdlDescription() != "" && $sourceDescription->getOdlBriefDescription() != "") {
                    $response = $translatorService->multipleTranslations(array($sourceDescription->getOdlDescription(), $sourceDescription->getOdlBriefDescription()), "en", "de");

                    if ($response[0]->getCode() == TranslatorResponseStatusCode::STATUS_200) {
                        $description = $response[0]->getTranslation();
                        $translated = true;
                    }

                    if ($response[1]->getCode() == TranslatorResponseStatusCode::STATUS_200) {
                        $briefDescription = $response[1]->getTranslation();
                        //$translated = true;
                    }
                } else if ($briefDescription == "" && $sourceDescription->getOdlBriefDescription() != "") {
                    $response = $translatorService->translate($sourceDescription->getOdlBriefDescription(), "en", "de");

                    if ($response->getCode() == TranslatorResponseStatusCode::STATUS_200) {
                        $briefDescription = $response->getTranslation();
                        //$translated = true;
                    }
                } else if ($description == "" && $sourceDescription->getOdlDescription() != "") {
                    $response = $translatorService->translate($sourceDescription->getOdlDescription(), "en", "de");

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
            }
        }

        $em->flush();
        $output->writeln('Operation completed!!!');
        return 0;
    }

}
