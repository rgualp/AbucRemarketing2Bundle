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

        /*$handle = curl_init($url);
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($handle);
        $responseDecoded = json_decode($response, true);
        $responseCode = curl_getinfo($handle, CURLINFO_HTTP_CODE);
        curl_close($handle);*/

        $json = json_decode(file_get_contents($url));

        return new TranslatorResponse(
            $json->responseStatus,
            ($json->responseStatus != 200) ? $json->error->errors[0]->message : "",
            ($json->responseStatus != 200) ? "" : $json->data->translations[0]->translatedText
            );
    }

    /**
     * Translate multiple tests in a single request to Google. This is the best way
     * @param $sourceTextsArray
     * @param $sourceLanguageCode
     * @param $targetLanguageCode
     * @return array
     */
    public function multipleTranslations($sourceTextsArray, $sourceLanguageCode, $targetLanguageCode)
    {
        $qQueryPart = "";

        foreach($sourceTextsArray as $sourceText)
            $qQueryPart .= "&q=".rawurlencode($sourceText);

        $url = 'https://www.googleapis.com/language/translate/v2?key=' . $this->translatorApiKey . $qQueryPart . '&source='.$sourceLanguageCode.'&target='.$targetLanguageCode;

        $json = json_decode(file_get_contents($url));
        $responseArray = array();

        for($i = 0; $i < count($sourceTextsArray);$i++)
        {
            $response = new TranslatorResponse(
                $json->responseStatus,
                ($json->responseStatus != 200) ? $json->error->errors[$i]->message : "",
                ($json->responseStatus != 200) ? "" : $json->data->translations[$i]->translatedText
            );
            array_push($responseArray, $response);
        }

        return $responseArray;
    }

    /*
         * The response is a json like this
         * {
             "data": {
              "translations": [{"translatedText": "Bonjour tout le monde!"}]
             }
            }
         * */
}


