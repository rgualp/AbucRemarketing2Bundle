<?php

namespace MyCp\mycpBundle\Service;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManager;
use MyCp\FrontEndBundle\Helpers\ReservationHelper;
use MyCp\FrontEndBundle\Helpers\Time;
use MyCp\mycpBundle\Entity\booking;
use MyCp\mycpBundle\Entity\generalReservation;
use MyCp\mycpBundle\Entity\ownershipReservation;
use MyCp\mycpBundle\Entity\user;
use MyCp\mycpBundle\Helpers\BackendModuleName;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class TranslatorResponse{
    public $code;
    public $errorMessage;
    public $translation;

    public function __construct($code, $errorMessage, $translation){
        $this->code = $code;
        $this->errorMessage = $errorMessage;
        $this->translation = $translation;
    }
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
     * Translate a single text in the request to Google
     * @param $sourceText
     * @param $sourceLanguageCode
     * @param $targetLanguageCode
     * @return TranslatorResponse
     */
    public function translate($sourceText, $sourceLanguageCode, $targetLanguageCode)
    {
        $url = 'https://www.googleapis.com/language/translate/v2?key=' . $this->translatorApiKey . '&q=' . rawurlencode($sourceText) . '&source='.$sourceLanguageCode.'&target='.$targetLanguageCode;

        $json = json_decode($this->curl_get_contents($url));

        $code = (isset($json->error)) ? $json->error->code : 200;

        return new TranslatorResponse(
            $code,
            ($code != 200) ? $json->error->errors[0]->message : "",
            ($code != 200) ? "" : $json->data->translations[0]->translatedText
            );
    }


    public function multipleTranslations($sourceTextsArray, $sourceLanguageCode, $targetLanguageCode)
    {
        $qQueryPart = "";

        foreach($sourceTextsArray as $sourceText)
            $qQueryPart .= "&q=".rawurlencode($sourceText);

        $url = 'https://www.googleapis.com/language/translate/v2?key=' . $this->translatorApiKey . $qQueryPart . '&source='.$sourceLanguageCode.'&target='.$targetLanguageCode;

        $json = json_decode($this->curl_get_contents($url));
        $responseArray = array();
        $code = (isset($json->error)) ? $json->error->code : 200;

        for($i = 0; $i < count($sourceTextsArray);$i++)
        {
            $response = new TranslatorResponse(
                $code,
                ($code != 200) ? ((count($json->error->errors) > 1) ? $json->error->errors[$i]->message : $json->error->errors[0]->message) : "",
                ($code != 200) ? "" : $json->data->translations[$i]->translatedText
            );
            array_push($responseArray, $response);
        }

        return $responseArray;
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


