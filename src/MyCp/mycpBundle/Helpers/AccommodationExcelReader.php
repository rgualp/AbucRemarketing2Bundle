<?php

/**
 * Description of AccommodationExcelReader
 *
 * @author Yanet Morales
 */

namespace MyCp\mycpBundle\Helpers;

use Doctrine\ORM\EntityManager;
use MyCp\mycpBundle\Entity\batchType;
use MyCp\mycpBundle\Entity\ownership;
use MyCp\mycpBundle\Entity\ownershipDescriptionLang;
use MyCp\mycpBundle\Entity\ownershipKeywordLang;
use MyCp\mycpBundle\Entity\ownershipStatus;
use Doctrine\Common\Collections\ArrayCollection;
use MyCp\mycpBundle\Entity\room;

class AccommodationExcelReader extends ExcelReader {

    private $idDestination;
    private $rooms;
    private $descriptions;
    private $keywords;

    private $batchProcessStatus;
    private $languages;

    public function __construct(EntityManager $em, $container, $excelDirectoryPath) {
        parent::__construct($em, $container, $excelDirectoryPath);

        $this->rooms = new ArrayCollection();
        $this->descriptions = new ArrayCollection();
        $this->keywords = new ArrayCollection();

        $this->batchProcessStatus = $this->em->getRepository("mycpBundle:ownershipStatus")->find(ownershipStatus::STATUS_BATCH_PROCESS);
        $this->languages = array();
        $this->languages["ES"] = $this->em->getRepository("mycpBundle:lang")->findOneBy(array("lang_code" => "ES"));
        $this->languages["EN"] = $this->em->getRepository("mycpBundle:lang")->findOneBy(array("lang_code" => "EN"));
        $this->languages["DE"] = $this->em->getRepository("mycpBundle:lang")->findOneBy(array("lang_code" => "DE"));
    }

    public function import($excelFileName, $idDestination)
    {
        $this->idDestination = $idDestination;
        $this->processExcel($excelFileName);
    }

    protected function processRowData($rowData, $sheet, $rowNumber)
    {
        $this->clearCollections();
        $code = trim($rowData[2]);

        if(!$this->existAccommodationCode($code)) {
            $ownership = new ownership();

            try {
                $ownership->setOwnName(trim($rowData[0]));
                $ownership->setOwnLicenceNumber(trim($rowData[1]));
                $ownership->setOwnMcpCode(trim($rowData[2]));
                $ownership->setOwnAddressStreet(trim($rowData[3]));
                $ownership->setOwnAddressNumber(trim($rowData[4]));
                $ownership->setOwnAddressBetweenStreet1(trim($rowData[5]));
                $ownership->setOwnAddressBetweenStreet2(trim($rowData[6]));
                $ownership->setOwnHomeowner1(trim($rowData[10]));
                $ownership->setOwnHomeowner2(trim($rowData[11]));
                $ownership->setOwnMobileNumber($this->processPhoneNumber($rowData[12]));
                $ownership->setOwnPhoneNumber($this->processPhoneNumber($rowData[13]));
                $ownership->setOwnEmail1(trim($rowData[14]));
                $ownership->setOwnEmail2(trim($rowData[15]));
                $ownership->setOwnCategory(trim($rowData[16]));
                $ownership->setOwnType(trim($rowData[17]));

                $english = trim($rowData[18]);
                $deutsch = trim($rowData[19]);
                $italian = trim($rowData[20]);
                $french = trim($rowData[21]);
                $ownership->setOwnLangs($this->processSpokenLanguages($english, $deutsch, $italian, $french));

                $commission = trim($rowData[22]);
                $commission = str_replace("%", "", $commission);
                $cell = $sheet->getCell('W' . $rowNumber);

                if($cell->getDataType() == \PHPExcel_Cell_DataType::TYPE_NUMERIC) {

                    if($cell->getStyle()->getNumberFormat()->getFormatCode() == \PHPExcel_Style_NumberFormat::FORMAT_PERCENTAGE || $cell->getStyle()->getNumberFormat()->getFormatCode() == \PHPExcel_Style_NumberFormat::FORMAT_PERCENTAGE_00)
                        $ownership->setOwnCommissionPercent($commission * 100);
                }


                //Add rooms to collection
                for ($roomNumber = 1; $roomNumber <= 6; $roomNumber++) {
                    $offset = 23 + ($roomNumber - 1) * 16;
                    $roomData = array_slice($rowData, $offset, 16);
                    $this->addRoom($roomNumber, $ownership, $roomData);
                }

                $breakFast = $this->processBooleanValue($rowData[119]);
                $breakFastPrice = $this->processPrice($rowData[120]);

                $ownership->setOwnFacilitiesBreakfast(($breakFast || $breakFastPrice != ""));
                $ownership->setOwnFacilitiesBreakfastPrice($breakFastPrice);

                $dinner = $this->processBooleanValue($rowData[121]);
                $dinnerPriceFrom = $this->processPrice($rowData[122]);
                $dinnerPriceTo = $this->processPrice($rowData[123]);

                $ownership->setOwnFacilitiesDinner(($dinner || $dinnerPriceFrom != ""));
                $ownership->setOwnFacilitiesDinnerPriceFrom($dinnerPriceFrom);
                $ownership->setOwnFacilitiesDinnerPriceTo($dinnerPriceTo);

                $parking = $this->processBooleanValue($rowData[124]);
                $parkingPrice = $this->processPrice($rowData[125]);

                $ownership->setOwnFacilitiesParking(($parking || $parkingPrice != ""));
                $ownership->setOwnFacilitiesParkingPrice($parkingPrice);

                $ownership->setOwnFacilitiesNotes(trim($rowData[126]));
                $ownership->setOwnWaterJacuzee($this->processBooleanValue($rowData[127]));
                $ownership->setOwnWaterSauna($this->processBooleanValue($rowData[128]));
                $ownership->setOwnWaterPiscina($this->processBooleanValue($rowData[129]));
                $ownership->setOwnDescriptionBicycleParking($this->processBooleanValue($rowData[130]));
                $ownership->setOwnDescriptionPets($this->processBooleanValue($rowData[131]));
                $ownership->setOwnDescriptionLaundry($this->processBooleanValue($rowData[132]));
                $ownership->setOwnDescriptionInternet($this->processBooleanValue($rowData[133]));

                //add descriptions
                $this->addDescription("ES", trim($rowData[134]), trim($rowData[135]), $ownership);
                $this->addDescription("EN", trim($rowData[136]), trim($rowData[137]), $ownership);
                $this->addDescription("DE", trim($rowData[138]), trim($rowData[139]), $ownership);

                //add keywords
                $this->addKeyword("ES", trim($rowData[140]), $ownership);
                $this->addKeyword("EN", trim($rowData[141]), $ownership);
                $this->addKeyword("DE", trim($rowData[142]), $ownership);

                $ownership->setOwnGeolocateX(trim($rowData[143]));
                $ownership->setOwnGeolocateY(trim($rowData[144]));
                $ownership->setOwnTop20($this->processBooleanValue($rowData[145]));
                $ownership->setOwnSelection($this->processBooleanValue($rowData[146]));
                $ownership->setOwnInmediateBooking($this->processBooleanValue($rowData[147]));
                $ownership->setOwnNotRecommendable($this->processBooleanValue($rowData[148]));
                $ownership->setOwnComment(trim($rowData[150]));
                $ownership->setOwnSaler(trim($rowData[151]));


                $cell = $sheet->getCell('EW' . $rowNumber);
                if($rowData[152] != "" && \PHPExcel_Shared_Date::isDateTime($cell)) {
                    $visitDate = \DateTime::createFromFormat("d/m/Y", date("d/m/Y", \PHPExcel_Shared_Date::ExcelToPHP($cell->getValue())));
                    $ownership->setOwnVisitDate($visitDate);
                }

                //TODO: Reset this counters
                $ownership->setOwnCommentsTotal(0);
                $ownership->setOwnMaximumNumberGuests(0);
                $ownership->setOwnRating(0);
                $ownership->setOwnMaximumPrice(0);
                $ownership->setOwnMinimumPrice(0);
                $ownership->setOwnRoomsTotal(0);

                //Add status, destination, province and municipality entities and general data
                $this->setLocalization($ownership);
                $ownership->setOwnStatus($this->batchProcessStatus);
                $ownership->setOwnSyncSt(SyncStatuses::ADDED);
                $ownership->setOwnCreationDate(new \DateTime());
                $ownership->setOwnSendedToTeam(false);

                //Save ownership, rooms and descriptions
                $this->em->persist($ownership);
                $this->saveCollections();
                $this->em->flush();

                //Add CountElement in
                $this->addSavedElement();
                $this->clearCollections();
            } catch (\Exception $e) {
                $this->reopenEntityManager();
                $this->addError("<b>Fila $rowNumber: </b>".$e->getMessage()." <br/> ". $e->getTraceAsString());
            }
        }
        else
        {
            $this->reopenEntityManager();
            $this->addMessage("<b>Fila $rowNumber: </b>Ya existe un alojamiento con el código ".$code);
        }

    }

    protected function configBatchProcess()
    {
        $this->getBatchProcessObject()->setBatchType(batchType::BATCH_TYPE_ACCOMMODATION);
    }

    private function existAccommodationCode($code)
    {
        $own = $this->em->getRepository("mycpBundle:ownership")->findBy(array("own_mcp_code" => $code));
        return count($own) > 0;
    }

    private function setLocalization(ownership $ownership)
    {
        $destination = $this->em->getRepository("mycpBundle:destination")->find($this->idDestination);
        $destinationLocation = $this->em->getRepository("mycpBundle:destinationLocation")->findOneBy(array('des_loc_destination' => $destination->getDesId()));
        $municipality = $destinationLocation->getDesLocMunicipality();
        $province = $destinationLocation->getDesLocProvince();

        $ownership->setOwnDestination($destination);
        $ownership->setOwnAddressMunicipality($municipality);
        $ownership->setOwnAddressProvince($province);
    }

    private function processPhoneNumber($phoneValue)
    {
        $value = trim($phoneValue);
        $value = str_replace("-","",$value);
        return str_replace(" ","",$value);
    }

    private function processSpokenLanguages($english, $deutsch, $italian, $french)
    {
        $languages = "";
        $languages .= ($english == "Off")? "0":"1";
        $languages .= ($deutsch == "Off")? "0":"1";
        $languages .= ($italian == "Off")? "0":"1";
        $languages .= ($french == "Off")? "0":"1";

        return $languages;
    }

    private function addRoom($roomNumber, $ownership, $roomData)
    {

        if($roomData[0] != "") {

                $room = new room();
                $room->setRoomNum($roomNumber);
                $room->setRoomOwnership($ownership);

                $room->setRoomType(trim($roomData[0]));
                $room->setRoomBeds(trim($roomData[1]));
                $room->setRoomPriceDownTo($this->processPrice($roomData[2]));
                $room->setRoomPriceUpTo($this->processPrice($roomData[3]));
                $room->setRoomPriceSpecial($this->processPrice($roomData[4]));
                $room->setRoomClimate(trim($roomData[5]));
                $room->setRoomAudiovisual(trim($roomData[6]));
                $room->setRoomSmoker($this->processBooleanValue($roomData[7]));
                $room->setRoomSafe($this->processBooleanValue($roomData[8]));
                $room->setRoomBaby($this->processBooleanValue($roomData[9]));
                $room->setRoomBathroom(trim($roomData[10]));
                $room->setRoomStereo($this->processBooleanValue($roomData[11]));
                $room->setRoomWindows(trim($roomData[12]));
                $room->setRoomBalcony(trim($roomData[13]));
                $room->setRoomTerrace($this->processBooleanValue($roomData[14]));
                $room->setRoomYard($this->processBooleanValue($roomData[15]));

                $this->rooms->add($room);
        }
    }

    private function addDescription ($langCode, $shortDescriptionText, $descriptionText, $ownership)
    {
        if($shortDescriptionText != "" && $descriptionText) {
            $description = new ownershipDescriptionLang();
            $description->setOdlOwnership($ownership);
            $description->setOdlBriefDescription($shortDescriptionText);
            $description->setOdlDescription($descriptionText);
            $description->setOdlIdLang($this->languages[$langCode]);

            $this->descriptions->add($description);
        }

    }

    private function addKeyword($langCode, $keywordsText, $ownership)
    {
        if($keywordsText != "") {
            $keyword = new ownershipKeywordLang();
            $keyword->setOklOwnership($ownership);
            $keyword->setOklIdLang($this->languages[$langCode]);
            $keyword->setOklKeywords($keywordsText);

            $this->keywords->add($keyword);
        }
    }

    private function processPrice($dataValue)
    {
        if($dataValue != "") {
            $price = trim($dataValue);
            $price = str_replace("$", "", $price);

            $parts = explode(".", $price);

            if(count($parts)) {
                if ($parts[1] == "00")
                    return $parts[0];
                else return $price;
            }
        }
        return "";
    }

    private function processBooleanValue($boolValue)
    {
        return (trim($boolValue) == "No" || trim($boolValue) == "Off") ? 0: 1;
    }

    private function clearCollections()
    {
        $this->rooms->clear();
        $this->descriptions->clear();
        $this->keywords->clear();
    }

    private function saveCollections()
    {
        $this->reopenEntityManager();
        foreach($this->rooms as $room)
        {
            $this->em->persist($room);
        }

        foreach($this->descriptions as $description)
        {
            $this->em->persist($description);
        }

        foreach($this->keywords as $keyword)
        {
            $this->em->persist($keyword);
        }
    }
}

?>
