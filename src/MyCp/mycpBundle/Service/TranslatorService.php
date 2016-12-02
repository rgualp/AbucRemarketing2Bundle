<?php

namespace MyCp\mycpBundle\Service;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManager;
use MyCp\FrontEndBundle\Helpers\ReservationHelper;
use MyCp\FrontEndBundle\Helpers\Time;
use MyCp\mycpBundle\Entity\booking;
use MyCp\mycpBundle\Entity\generalReservation;
use MyCp\mycpBundle\Entity\lang;
use MyCp\mycpBundle\Entity\ownership;
use MyCp\mycpBundle\Entity\ownershipDescriptionLang;
use MyCp\mycpBundle\Entity\ownershipReservation;
use MyCp\mycpBundle\Entity\user;
use MyCp\mycpBundle\Helpers\BackendModuleName;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class TranslatorResponse{
    private $code;
    private $errorMessage;
    private $translation;

    public function __construct($code, $errorMessage, $translation){
        $this->code = $code;
        $this->errorMessage = $errorMessage;
        $this->translation = $translation;
    }

    public function getCode()
    {
        return $this->code;
    }

    public function setCode($code)
    {
        $this->code = $code;
        return $this;
    }

    public function getErrorMessage()
    {
        return $this->errorMessage;
    }

    public function setErrorMessage($value)
    {
        $this->errorMessage = $value;
        return $this;
    }

    public function getTranslation()
    {
        return $this->translation;
    }

    public function setTranslation($value)
    {
        $this->translation = $value;
        return $this;
    }
}

class TranslatorResponseStatusCode {

    const STATUS_200 = 200;
    const STATUS_403 = 403;
    const STATUS_500 = 500;

}

class TranslatorService extends Controller
{
    /**
     * @var ObjectManager
     */
    private $em;

    private $logger;

    private $translatorApiKey;

    public function __construct(ObjectManager $em, $logger, $translatorApiKey)
    {
        $this->em = $em;
        $this->logger = $logger;
        $this->translatorApiKey = $translatorApiKey;
    }

    /**
     * Translate a single text in the request to Google. Do strip_tags
     * @param $sourceText
     * @param $sourceLanguageCode
     * @param $targetLanguageCode
     * @return TranslatorResponse
     */
    public function translate($sourceText, $sourceLanguageCode, $targetLanguageCode)
    {
        $url = 'https://www.googleapis.com/language/translate/v2?key=' . $this->translatorApiKey . '&q=' . rawurlencode(strip_tags($sourceText)) . '&source='.strtolower($sourceLanguageCode).'&target='.strtolower($targetLanguageCode);

        $json = json_decode($this->curl_get_contents($url));

        $code = (!isset($json)) ? TranslatorResponseStatusCode::STATUS_500 : ((isset($json->error)) ? $json->error->code : TranslatorResponseStatusCode::STATUS_200);

        return new TranslatorResponse(
            $code,
            ($code != TranslatorResponseStatusCode::STATUS_200) ? ((!isset($json) || !isset($json->error)) ? "Ha ocurrido un error": $json->error->errors[0]->message) : "",
            ($code != TranslatorResponseStatusCode::STATUS_200 || !isset($json) || !isset($json->data)) ? "" : $json->data->translations[0]->translatedText
            );
    }

    public function translateTest($sourceText, $sourceLanguageCode, $targetLanguageCode)
    {
        $code = TranslatorResponseStatusCode::STATUS_200;

        return new TranslatorResponse($code,"","Texto traducido");
    }


    /**
     * Translate multiples text in the request to Google. Do strip_tags
     * @param $sourceTextsArray
     * @param $sourceLanguageCode
     * @param $targetLanguageCode
     * @return array
     */
    public function multipleTranslations($sourceTextsArray, $sourceLanguageCode, $targetLanguageCode)
    {
        $qQueryPart = "";

        foreach($sourceTextsArray as $sourceText)
            $qQueryPart .= "&q=".rawurlencode(strip_tags($sourceText));

        $url = 'https://www.googleapis.com/language/translate/v2?key=' . $this->translatorApiKey . $qQueryPart . '&source='.strtolower($sourceLanguageCode).'&target='.strtolower($targetLanguageCode);

        $json = json_decode($this->curl_get_contents($url));
        $responseArray = array();

        if($json != null) {
            $code = (isset($json->error)) ? $json->error->code : TranslatorResponseStatusCode::STATUS_200;

            for ($i = 0; $i < count($sourceTextsArray); $i++) {
                if ($code != TranslatorResponseStatusCode::STATUS_200) {
                    $response = new TranslatorResponse($code, $json->error->errors[0]->message, "");
                    array_push($responseArray, $response);
                    break;
                }

                $response = new TranslatorResponse($code, "", $json->data->translations[$i]->translatedText);
                array_push($responseArray, $response);
            }
        }

        return $responseArray;
    }

    public function multipleTranslationsTest($sourceTextsArray, $sourceLanguageCode, $targetLanguageCode)
    {
        $responseArray = array();
        $code = TranslatorResponseStatusCode::STATUS_200;

        for($i = 0; $i < count($sourceTextsArray);$i++)
        {
            $response = new TranslatorResponse($code,"","Texto traducido");
            array_push($responseArray, $response);
        }

        return $responseArray;
    }

    public function translateAccommodation(ownershipDescriptionLang $description, $sourceLanguageCode, $targetLanguageCode)
    {
        $translations = $this->multipleTranslations(array($description->getOdlBriefDescription(), $description->getOdlDescription()), $sourceLanguageCode, $targetLanguageCode);

        if(count($translations) > 0 && $translations[0]->getCode() == TranslatorResponseStatusCode::STATUS_200){
            $ownership = $description->getOdlOwnership();
            $targetLanguage = $this->em->getRepository("mycpBundle:lang")->findOneBy(array("lang_code" => strtoupper($targetLanguageCode)));

            $translatedDescription = $this->em->getRepository("mycpBundle:ownershipDescriptionLang")->getDescriptionsByAccommodation($ownership, strtoupper($targetLanguageCode));

            if($translatedDescription == null)
                $translatedDescription = new ownershipDescriptionLang();

            $translatedDescription->setOdlAutomaticTranslation(true)
                ->setOdlBriefDescription($translations[0]->getTranslation())
                ->setOdlDescription($translations[1]->getTranslation())
                ->setOdlOwnership($ownership)
                ->setOdlIdLang($targetLanguage);

            $this->em->persist($translatedDescription);
            $this->em->flush();

            $this->logger->saveLog('Translate accommodation '.$ownership->getOwnMcpCode()." from ".strtoupper($sourceLanguageCode)." to ".strtoupper($targetLanguage),  BackendModuleName::MODULE_OWNERSHIP);
        }
    }

    public function translateAccommodationObj(ownershipDescriptionLang $description, ownershipDescriptionLang $translatedDescription, lang $sourceLanguage, lang $targetLanguage)
    {
        $translations = $this->multipleTranslations(array($description->getOdlBriefDescription(), $description->getOdlDescription()), strtolower($sourceLanguage->getLangCode()), strtolower($targetLanguage->getLangCode()));

        if(count($translations) > 0 && $translations[0]->getCode() == TranslatorResponseStatusCode::STATUS_200){
            $ownership = $description->getOdlOwnership();

           /* $translatedDescription = $this->em->getRepository("mycpBundle:ownershipDescriptionLang")->getDescriptionsByAccommodation($ownership, strtoupper($targetLanguage->getLangCode()));

            if($translatedDescription == null)
                $translatedDescription = new ownershipDescriptionLang();*/

            $translatedDescription->setOdlAutomaticTranslation(true)
                ->setOdlBriefDescription($translations[0]->getTranslation())
                ->setOdlDescription($translations[1]->getTranslation())
                ->setOdlOwnership($ownership)
                ->setOdlIdLang($targetLanguage);

            return $translatedDescription;
            /*$this->em->persist($translatedDescription);

            if($doFlush) {
                $this->em->flush();
                $this->logger->saveLog('Translate accommodation ' . $ownership->getOwnMcpCode() . " from " . strtoupper($sourceLanguage->getLangCode()) . " to " . strtoupper($targetLanguage->getLangCode()), BackendModuleName::MODULE_OWNERSHIP);
            }*/
        }
        return null;
    }

    private function curl_get_contents($url)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }
}


