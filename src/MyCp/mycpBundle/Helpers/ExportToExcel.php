<?php

/**
 * Description of ExportToExcel
 *
 * @author Yanet Morales
 */

namespace MyCp\mycpBundle\Helpers;

use Doctrine\ORM\EntityManager;
use MyCp\FrontEndBundle\Helpers\Time;
use MyCp\mycpBundle\Entity\generalReservation;
use MyCp\mycpBundle\Entity\room;
use MyCp\mycpBundle\Entity\season;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class ExportToExcel extends Controller {

    /**
     * 'doctrine.orm.entity_manager' service
     *
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;
    protected $container;

    /**
     * @var string
     */
    private $excelDirectoryPath;

    public function __construct(EntityManager $em, $container, $excelDirectoryPath) {
        $this->em = $em;
        $this->container = $container;
        $this->excelDirectoryPath = $excelDirectoryPath;
    }

    public function exportRpDailyInPlaceClients($date, $dateRangeFrom, $dateRangeTo, $reportId, $timer, $fileName = "clientes_en_dia") {
        $excel = $this->configExcel("Reporte de clientes en un dia", "Reporte de clientes en un dia de MyCasaParticular", "reportes");

        $data = $this->dataForRpDailyInPlaceClients($date, $dateRangeFrom, $dateRangeTo, $timer);

        if (count($data) > 0)
            $excel = $this->createSheetForRpDailyInPlaceClients($excel, $date, $reportId, $date, $data);

        $fileName = $this->getFileName($fileName);
        $this->save($excel, $fileName);
        return $this->export($fileName);
    }

    private function dataForRpDailyInPlaceClients($date, $dateRangeFrom, $dateRangeTo, $timer) {
        $results = array();

        $reportContent = $this->em->getRepository("mycpBundle:report")->rpDailyInPlaceClients($date, $dateRangeFrom, $dateRangeTo, $timer);

        foreach ($reportContent as $content) {
            $data = array();

            $data[0] = $content["clientName"].' '.$content["clientLastName"];
            $data[1] = $content["clientCountry"];
            $data[2] = $content["clientEmail"];
            $data[3] = $content["ownCode"];
            $data[4] = $content["owner1"].(($content["owner1"] != "" && $content["owner2"] != "")? " / ". $content["owner2"] : "");
            $data[5] = (($content["ownPhoneNumber"] != "")?"(+53) ".$content["phoneCode"]. " ".$content["ownPhoneNumber"] : "").((trim($content["ownMobile"]) != "" && trim($content["ownPhoneNumber"]) != "") ? " / ": "").(($content["ownMobile"] != "") ? $content["ownMobile"]: "");
            $data[6] = $content["bookedNights"];
            $data[7] = $content["bookedDestinations"];

            //Itinerario
            $itinerary = "";
            for($i = 0; $i < count($content['itinerary']); $i++)
            {
                $itineraryItem = $content['itinerary'][$i];
                $itinerary.= $itineraryItem['prov_name'].' ('.$itineraryItem['own_mcp_code'].')';

                if($i != count($content['itinerary']) - 1)
                    $itinerary .= ", ";
            }

            $data[8] = $itinerary;

            $arrival = \DateTime::createFromFormat('Y-m-d', $content["arrivalDate"]);
            $data[9] = date('d/m/Y',$arrival->getTimestamp()); //$arrival->format('d/m/Y');

            $leaving = \DateTime::createFromFormat('Y-m-d', $content["leavingDate"]);
            $data[10] = date('d/m/Y',$leaving->getTimestamp());//$leaving->format('d/m/Y');

            array_push($results, $data);
        }

        return $results;
    }

    private function createSheetForRpDailyInPlaceClients($excel, $sheetName, $reportId, $date, $data) {

        $report = $this->em->getRepository("mycpBundle:report")->find($reportId);
        $sheet = $this->createSheet($excel, $sheetName);
        $sheet->setCellValue('a1', "Reporte: ".$report->getReportName());
        $dateObject = \DateTime::createFromFormat('Y-m-d', $date);
        $date = $dateObject->format('d/m/Y');
        $sheet->setCellValue('a2', 'Fecha: '.$date);
        $now = new \DateTime();
        $sheet->setCellValue('c2', 'Generado: '.$now->format('d/m/Y H:s'));
        // $sheet->setCellValue('a3', "(*) Los datos fueron calculados teniendo en cuenta el rango comprendido entre 30 días antes y 30 días después de la fecha seleccionada.");

        $sheet->setCellValue('a5', 'Cliente');
        $sheet->setCellValue('b5', 'País');
        $sheet->setCellValue('c5', 'Correo');
        $sheet->setCellValue('d5', 'Alojamiento');
        $sheet->setCellValue('e5', 'Propietario(s)');
        $sheet->setCellValue('f5', 'Teléfono(s)');
        $sheet->setCellValue('g5', 'Noches Reservadas(*)');
        $sheet->setCellValue('h5', 'Destinos Reservados(*)');
        $sheet->setCellValue('i5', 'Itinerario(*)');
        $sheet->setCellValue('j5', 'Llegada(*)');
        $sheet->setCellValue('k5', 'Salida(*)');

        $sheet = $this->styleHeader("a5:k5", $sheet);
        $style = array(
            'font' => array(
                'bold' => true,
                'size' => 14
            ),
        );
        $sheet->getStyle("a1")->applyFromArray($style);

        $sheet->fromArray($data, ' ', 'A6');
        $this->setColumnAutoSize("a", "k", $sheet);
        $sheet->getStyle('j6:k'.(count($data) + 5))->getNumberFormat()->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_DATE_DDMMYYYY);
        $sheet->setAutoFilter("A5:K".(count($data) + 5));

        return $excel;
    }


    public function exportOwnershipGeneralStats(Request $request, $reportId, $provinceId = "", $municipalityId = "",  $fileName = "resumenAlojamientos") {
        $excel = $this->configExcel("Reporte resumen de propiedades", "Reporte resumen de propiedades de MyCasaParticular", "reportes");

        $location = "Cuba";
        $sheetName = "Cuba";
        $province = null;
        $municipality = null;

        if($provinceId != -1 && $provinceId != "")
        {
            $province =  $this->em->getRepository("mycpBundle:province")->find($provinceId);
            $location = $province->getProvName();
            $sheetName = $province->getProvName();
        }

        if($municipalityId != -1 && $municipalityId != "")
        {
            $municipality =  $this->em->getRepository("mycpBundle:municipality")->find($municipalityId);
            $location = $municipality->getMunName();
            $sheetName = $municipality->getMunName();
        }

        $data = $this->dataForRpAccommodationSummaryStat($province, $municipality);

        if (count($data["data"]) > 0)
            $excel = $this->createSheetForRpAccommodationSummaryStat($excel, $sheetName, $reportId, $location, $data);

        $fileName = $this->getFileName($fileName);
        $this->save($excel, $fileName);
        return $this->export($fileName);
    }

    private function dataForRpAccommodationSummaryStat($province, $municipality) {
        $results = array();

        $reportContent = $this->em->getRepository('mycpBundle:ownershipStat')->getBulb(null, (($municipality != null)? null : $province), $municipality);

        $nomParent = "";
        $nomTitle = array();
        $index = 1;

        foreach ($reportContent as $content) {
            $data = array();

            if($content[0]->getStatNomenclator()->getNomParent() != $nomParent )
            {
                $nomParent = $content[0]->getStatNomenclator()->getNomParent();

                $data[0] = "Por ". $nomParent->getNomName();
                $data[1] = "";
                array_push($results, $data);
                array_push($nomTitle, $index);
                $index ++;
            }

            $data[0] = $content[0]->getStatNomenclator()->getNomName();
            $data[1] = $content["stat_value"];

            $index ++;
            array_push($results, $data);
        }

        return array("data" => $results, "nomTitles" => $nomTitle);
    }

    private function createSheetForRpAccommodationSummaryStat($excel, $sheetName, $reportId, $location, $data) {

        $report = $this->em->getRepository("mycpBundle:report")->find($reportId);
        $sheet = $this->createSheet($excel, $sheetName);
        $sheet->setCellValue('a1', "Reporte: ".$report->getReportName());
        $sheet->setCellValue('a2', 'Alcance: '.$location);
        $now = new \DateTime();
        $sheet->setCellValue('b2', 'Generado: '.$now->format('d/m/Y H:s'));

        $sheet->setCellValue('a4', 'Categoría');
        $sheet->setCellValue('b4', 'Valor');

        //dump($data["nomTitles"]);die;

        $sheet = $this->styleHeader("a4:b4", $sheet);
        $style = array(
            'font' => array(
                'bold' => true,
                'size' => 14
            ),
        );
        $sheet->getStyle("a1")->applyFromArray($style);

        $styleSubTitles = array(
            'font' => array(
                'bold' => true,
                'size' => 12
            ),
        );
        foreach($data["nomTitles"] as $titleIndex)
        {
            $sheet->getStyle("a".($titleIndex + 4))->applyFromArray($styleSubTitles);
        }

        $sheet
            ->getStyle("a5:a".(count($data["data"])+4))
            ->getNumberFormat()
            ->setFormatCode( \PHPExcel_Style_NumberFormat::FORMAT_TEXT );

        $sheet->fromArray($data["data"], ' ', 'A5');
        //$sheet->setAutoFilter("A4:B".(count($data) + 5));
        $this->setColumnAutoSize("a", "b", $sheet);

        return $excel;
    }

    public function exportOwnershipGeneralList($nomenclatorId, $provinceId, $municipalityId, $fileName="listaAlojamientos"){

        $excel = $this->configExcel("Reporte resumen de propiedades (lista)", "Reporte resumen de propiedades (lista) de MyCasaParticular", "reportes");

        $location = "Cuba";
        $sheetName = "Cuba";
        $province = null;
        $municipality = null;
        $nomenclator = null;

        if($provinceId != -1 && $provinceId != "")
        {
            $province =  $this->em->getRepository("mycpBundle:province")->find($provinceId);
            $location = $province->getProvName();
            $sheetName = $province->getProvName();
        }

        if($municipalityId != -1 && $municipalityId != "")
        {
            $municipality =  $this->em->getRepository("mycpBundle:municipality")->find($municipalityId);
            $location = $municipality->getMunName();
            $sheetName = $municipality->getMunName();
        }
        if($nomenclatorId != -1 && $nomenclatorId != "")
        {
            $nomenclator = $this->em->getRepository("mycpBundle:nomenclatorStat")->find($nomenclatorId);
        }

        $data = $this->dataForRpAccommodationList($nomenclator,$province, $municipality);

        if (count($data) > 0)
            $excel = $this->createSheetForRpAccommodationList($excel, $sheetName, $nomenclator, $location, $data);

        $fileName = $this->getFileName($fileName);
        $this->save($excel, $fileName);
        return $this->export($fileName);
    }

    private function dataForRpAccommodationList($nomenclator,$province, $municipality) {
        $results = array();

        $reportContent = $this->em->getRepository('mycpBundle:ownershipStat')->getOwnershipReportListContent($nomenclator,$province, $municipality);

        foreach ($reportContent as $content) {
            $data = array();

            $data[0] = $content->getOwnMcpCode();
            $data[1] = $content->getOwnName();
            $data[2] = ($content->getOwnStatus() != null) ? $content->getOwnStatus()->getStatusName() : "Sin estado";
            array_push($results, $data);
        }

        return $results;
    }

    private function createSheetForRpAccommodationList($excel, $sheetName, $nomenclator, $location, $data) {

        $sheet = $this->createSheet($excel, $sheetName);
        $sheet->setCellValue('a1', "Listado de Alojamientos");
        $sheet->setCellValue('a2', 'Alcance: '.$location);
        $filter = $nomenclator->getNomFullName();
        $sheet->setCellValue('b2', 'Filtro: '.$filter);
        $now = new \DateTime();
        $sheet->setCellValue('c2', 'Generado: '.$now->format('d/m/Y H:s'));
        $sheet->setCellValue('b3', 'Total: '.count($data)." alojamientos");

        $sheet->setCellValue('a5', 'Código');
        $sheet->setCellValue('b5', 'Alojamiento');
        $sheet->setCellValue('c5', 'Estado');

        $sheet = $this->styleHeader("a5:c5", $sheet);
        $style = array(
            'font' => array(
                'bold' => true,
                'size' => 14
            ),
        );
        $sheet->getStyle("a1")->applyFromArray($style);


        $sheet->fromArray($data, ' ', 'A6');

        $this->setColumnAutoSize("a", "c", $sheet);
        $sheet->setAutoFilter("A5:C".(count($data) + 5));

        return $excel;
    }


    /*
     * Crea un fichero excel con los check-in que ocurrirán en la fecha introducida
     * El formato del parametro $date será 'd/m/Y'
     */

    public function createCheckinExcel($date, $sort_by, $export = false) {
        $excel = $this->configExcel("Check-in $date", "Check-in del $date - MyCasaParticular", "check-in");
        $array_date_from = explode('/', $date);
        $checkinDate = "";
        if (count($array_date_from) > 1)
            $checkinDate = $array_date_from[2] . $array_date_from[1] . $array_date_from[0];
        $fileName = "CheckIn_$checkinDate.xlsx";

        $data = $this->dataForCheckin($date, $sort_by);

        if (count($data) > 0)
            $excel = $this->createSheetForCheckin($excel, str_replace("/", "-", $date), $data);

        if ($export) {
            $this->save($excel, $fileName);
            return $this->export($fileName);
        } else {
            $this->save($excel, $fileName);
            return $this->excelDirectoryPath . $fileName;
        }
    }

    private function createSheetForCheckin($excel, $sheetName, $data) {
        $sheet = $this->createSheet($excel, $sheetName);
        $sheet->setCellValue('a1', 'Reserva');
        $sheet->setCellValue('b1', 'Fecha Reserva');
        $sheet->setCellValue('c1', 'Propiedad');
        $sheet->setCellValue('d1', 'Propietario(s)');
        $sheet->setCellValue('e1', 'Teléfono (s)');
        $sheet->setCellValue('f1', 'Hab.');
        $sheet->setCellValue('g1', 'Huésp.');
        $sheet->setCellValue('h1', 'Noches');
        $sheet->setCellValue('i1', 'Fecha Pago');
        $sheet->setCellValue('j1', 'A Pagar');
        $sheet->setCellValue('k1', 'Cliente');
        $sheet->setCellValue('l1', 'País');
        $sheet->setCellValue('m1', 'Contactado');

        $sheet = $this->styleHeader("a1:m1", $sheet);

        $sheet->fromArray($data, ' ', 'A2');
        $sheet->setAutoFilter($sheet->calculateWorksheetDimension());
        $this->setColumnAutoSize("a", "m", $sheet);
        return $excel;
    }

    private function dataForCheckin($date, $sort_by) {
        $results = array();

        $checkins = $this->em->getRepository("mycpBundle:generalReservation")->getCheckins($date, $sort_by);

        foreach ($checkins as $check) {
            $data = array();

            $data[0] = \MyCp\FrontEndBundle\Helpers\ReservationHelper::getCASId($check["gen_res_id"]);
            $resDate = $check["gen_res_date"];
            $data[1] = $resDate->format("d/m/Y");
            $data[2] = $check["own_mcp_code"];
            $data[3] = $check["own_homeowner_1"];
            if ($check["own_homeowner_2"] != "")
                $data[3] .= " / " . $check["own_homeowner_2"];

            $data[4] = "";
            if ($check["own_phone_number"] != "")
                $data[4] .= "(+53) " . $check["prov_phone_code"] . " " . $check["own_phone_number"];

            if ($check["own_phone_number"] != "" && $check["own_mobile_number"] != "")
                $data[4] .= " / ";

            $data[4] .= $check["own_mobile_number"];

            //Total de habitaciones
            $data[5] = $check["rooms"];

            //Total de huéspedes
            $data[6] = $check["adults"] + $check["children"];

            //Noches
            $data[7] = $check["nights"];

            //Fecha de Pago
            $payDate = new \DateTime($check["payed"]);
            $data[8] = $payDate->format("d/m/Y");

            //Pago en casa
            $payAtService = $check["to_pay_at_service"] - $check["to_pay_at_service"] * $check["own_commission_percent"] / 100;
            $data[9] = number_format((float)$payAtService, 2, '.', '');
            $data[9] .= " CUC";
            //Cliente
            $data[10] = $check["user_user_name"] . " " . $check["user_last_name"];
            $data[11] = $check["co_name"];


            array_push($results, $data);
        }

        return $results;
    }

    public function exportAccommodationsDirectory($fileName = "directorio") {
        $excel = $this->configExcel("Directorio de alojamientos", "Directorio de alojamientos de MyCasaParticular", "alojamientos");

        $provinces = $this->em->getRepository("mycpBundle:province")->findBy(array(), array("prov_code" => "ASC"));

        $index = 0;
        foreach ($provinces as $prov) {
            //Hacer una hoja por cada provincia
            $data = $this->dataForAccommodationsDirectory($excel,$prov->getProvId());

            if (count($data) > 0) {
                $index++;
                $sheetName = ($prov!= null && $prov->getProvCode() != null  && $prov->getProvCode() != "") ? $prov->getProvCode() : (($prov!= null && $prov->getProvName() != null  && $prov->getProvName() != "") ? $prov->getProvName() : "Hoja".$index);
                $excel = $this->createSheetForAccommodationsDirectory($excel, $sheetName, $data);
            }
        }
        $fileName = $this->getFileName($fileName);
        $this->save($excel, $fileName);
        return $this->export($fileName);
    }

    public function getAccommodationsDirectoryByStatus($status, $fileName = "directorioActivas", $exportFile = false) {
        $excel = $this->configExcel("Directorio de alojamientos activos", "Directorio de alojamientos activos de MyCasaParticular", "alojamientos activos");

        $provinces = $this->em->getRepository("mycpBundle:province")->findBy(array(), array("prov_code" => "ASC"));

        $index = 0;
        foreach ($provinces as $prov) {
            //Hacer una hoja por cada provincia
            $data = $this->dataForAccommodationsDirectory($excel,$prov->getProvId(), $status);

            if (count($data) > 0) {
                $index++;
                $sheetName = ($prov!= null && $prov->getProvCode() != null && $prov->getProvCode() != "") ? $prov->getProvCode() : (($prov!= null && $prov->getProvName() != null  && $prov->getProvName() != "") ? $prov->getProvName() : "Hoja".$index);
                $excel = $this->createSheetForAccommodationsDirectory($excel, $sheetName, $data);
            }
        }
        $fileName = $this->getFileName($fileName);
        $this->save($excel, $fileName);

        if($exportFile)
            return $this->export($fileName);

        return $this->excelDirectoryPath.$fileName;
    }

    private function dataForAccommodationsDirectory($excel,$idProvince, $status = null) {
        $results = array();

        $ownerships = $this->em->getRepository("mycpBundle:ownership")->getByProvince($idProvince, $status);

        foreach ($ownerships as $own) {
            $data = array();

            $data[0] = $own["mycpCode"];
            $data[1] = $own["name"];
            $data[2] = $own["totalRooms"];
            $data[3] = $own["owner1"].(($own["owner2"] != "")? " / ". $own["owner2"] : "");
            $data[4] = "Calle ". $own["street"]." No.".$own["number"].(($own["between1"] != "" && $own["between2"] != "") ? " entre ".$own["between1"]." y ".$own["between2"] : "");
            $data[5] = (($own["phone"] != "")?"(+53) ".$own["provCode"]. " ".$own["phone"] : "").(($own["mobile"] != "" && $own["phone"] != "") ? " / ": "").(($own["mobile"] != "") ? $own["mobile"]: "");
            $data[6] = $own["email1"].(($own["email2"] != "")? " / ". $own["email2"] : "");

            if($own["lowDown"] != $own["highDown"])
                $data[7] = $own["lowDown"]." - ".$own["highDown"]." CUC";
            else
                $data[7] = $own["highDown"]." CUC";


            if($own["lowUp"] != $own["highUp"])
                $data[8] = $own["lowUp"]." - ".$own["highUp"]." CUC";
            else
                $data[8] = $own["highUp"]." CUC";

            $data[9] = $own["status"];
            $data[10] = $own["municipality"];

            array_push($results, $data);
        }

        return $results;
    }

    private function createSheetForAccommodationsDirectory($excel, $sheetName, $data) {
        $sheet = $this->createSheet($excel, $sheetName);
        $sheet->setCellValue('a1', 'Código');
        $sheet->setCellValue('b1', 'Casa');
        $sheet->setCellValue('c1', 'Habitaciones');
        $sheet->setCellValue('d1', 'Propietario(s)');
        $sheet->setCellValue('e1', 'Dirección');
        $sheet->setCellValue('f1', 'Teléfono(s)');
        $sheet->setCellValue('g1', 'Correo(s)');
        $sheet->setCellValue('h1', 'Temporada Baja');
        $sheet->setCellValue('i1', 'Temporada Alta');
        $sheet->setCellValue('j1', 'Estado');
        $sheet->setCellValue('k1', 'Municipio');


        $sheet = $this->styleHeader("a1:k1", $sheet);

        $sheet->fromArray($data, ' ', 'A2');

        $this->setColumnAutoSize("a", "k", $sheet);

        for($i = 0; $i < count($data); $i++)
        {
            if($data[$i][9] != "Activo")
            {
                $style = array(
                    'font' => array(
                        'color' => array('rgb' => 'FF0000'),
                    ),
                );
                $sheet->getStyle("A".($i + 2).":K".($i + 2))->applyFromArray($style);
            }
        }

        $sheet->setAutoFilter($sheet->calculateWorksheetDimension());

        return $excel;
    }

    public function generateToAirBnb($ownsIdArray, $fileName = "airBnbDirectorio") {
        if (count($ownsIdArray)) {
            $excel = $this->configExcel("Directorio de alojamientos", "Directorio de alojamientos de MyCasaParticular", "alojamientos");
            $data = $this->dataAirBnb($ownsIdArray);

            if (count($data) > 0)
                $excel = $this->createSheetAirBnb($excel, "Listado", $data);

            $fileName = $this->getFileName($fileName);
            $this->save($excel, $fileName);
            return $fileName;
        }
    }

    public function exportToAirBnb($fileName = "airBnbDirectorio") {
        $fileName = $this->getFileName($fileName);
        return $this->export($fileName);
    }

    private function dataAirBnb($ownsCodesArray) {
        $results = array();

        $ownerships = $this->em->getRepository("mycpBundle:ownership")->getByIdsArray($ownsCodesArray);

        foreach ($ownerships as $own) {
            $data = array();

            $data[0] = $own["mycpCode"] . "-" . $own["roomNumber"];
            $data[1] = $own["name"];
            $data[2] = $own["owner1"];
            if ($own["owner2"] != "")
                $data[2] .= " / " . $own["owner2"];
            $data[3] = "Calle " . $own["street"] . " No. " . $own["number"] . " entre " . $own["between1"] . " y " . $own["between2"] . ". " . $own["municipality"] . ". " . $own["province"];
            $data[4] = $own["totalRooms"];
            $data[5] = ($own["bathroom"] != "") ? 1 : 0;
            $data[6] = $own["bedsTotal"];
            $data[7] = room::getTotalGuests($own["type"]);
            $data[8] = $own["priceUp"] . " CUC";
            $data[9] = ($own["internet"] > 0) ? "Yes" : "No";
            $data[10] = ($own["audiovisual"] != "No") ? "Yes" : "No";
            $data[11] = ((strpos($own["climate"], 'Aire acondicionado') !== false)) ? "Yes" : "No";
            $data[12] = ($own["washer"] > 0) ? "Extra" : "No";
            $data[13] = ($own["breakfast"] > 0) ? (($own["breakfastPrice"] > 0) ? "Extra" : "Yes") : "No";
            $data[14] = ($own["pets"] > 0) ? "Yes" : "No";
            $data[15] = ($own["smoker"] > 0) ? "Yes" : "No";
            $data[16] = ($own["parking"] > 0) ? (($own["parkingPrice"] > 0) ? "Extra" : "Yes") : "No";
            $data[17] = ($own["pool"] > 0) ? "Yes" : "No";
            $data[18] = ($own["hotTub"] > 0) ? "Yes" : "No";
            $data[19] = $own["geoX"];
            $data[20] = $own["geoY"];
            $data[21] = room::getCalendarUrl($data[0], $this->getRequest());


            array_push($results, $data);
        }

        return $results;
    }

    private function createSheetAirBnb($excel, $sheetName, $data) {
        $sheet = $this->createSheet($excel, $sheetName);
        $sheet->setCellValue('a1', 'Code');
        $sheet->setCellValue('b1', 'Name');
        $sheet->setCellValue('c1', 'Owner (s)');
        $sheet->setCellValue('d1', 'Address');
        $sheet->setCellValue('e1', 'Bedrooms');
        $sheet->setCellValue('f1', 'Bathrooms');
        $sheet->setCellValue('g1', 'Beds');
        $sheet->setCellValue('h1', 'Accommodates');
        $sheet->setCellValue('i1', 'Prices');
        $sheet->setCellValue('j1', 'Internet');
        $sheet->setCellValue('k1', 'TV');
        $sheet->setCellValue('l1', 'AirConditioning');
        $sheet->setCellValue('m1', 'Washer');
        $sheet->setCellValue('n1', 'Breakfast');
        $sheet->setCellValue('o1', 'Pets');
        $sheet->setCellValue('p1', 'Smokers');
        $sheet->setCellValue('q1', 'Parking');
        $sheet->setCellValue('r1', 'Pool');
        $sheet->setCellValue('s1', 'HotTube');
        $sheet->setCellValue('t1', 'GeoX');
        $sheet->setCellValue('u1', 'GeoY');
        $sheet->setCellValue('v1', 'Calendar (.ics)');

        $sheet = $this->styleHeader("a1:v1", $sheet);

        $sheet->fromArray($data, ' ', 'A2');

        $this->setColumnAutoSize("a", "l", $sheet);
        $sheet->setAutoFilter($sheet->calculateWorksheetDimension());
        return $excel;
    }

    public function exportDirectoryFragment($fileName = "listado") {
        $fileName = $this->getFileName($fileName);
        return $this->export($fileName);
    }

    public function generateDirectoryFragment($ownsIdArray, $fileName = "listado") {
        if (count($ownsIdArray)) {
            $excel = $this->configExcel("Directorio de alojamientos", "Directorio de alojamientos de MyCasaParticular", "alojamientos");
            $data = $this->dataDirectoryFragment($ownsIdArray);

            if (count($data) > 0)
                $excel = $this->createSheetDirectoryFragment($excel, "Listado", $data);

            $fileName = $this->getFileName($fileName);
            $this->save($excel, $fileName);
            return $fileName;
        }
    }

    private function dataDirectoryFragment($ownsCodesArray) {
        $results = array();

        $ownerships = $this->em->getRepository("mycpBundle:ownership")->getByIdsForExport($ownsCodesArray);

        foreach ($ownerships as $own) {
            $data = array();

            $data[0] = $own["mycpCode"];
            $data[1] = $own["name"];
            $data[2] = $own["owner1"];
            if ($own["owner2"] != "")
                $data[2] .= " / " . $own["owner2"];
            $data[3] = "Calle " . $own["street"] . " No. " . $own["number"] . " entre " . $own["between1"] . " y " . $own["between2"];
            $data[4] = $own["municipality"];
            $data[5] = $own["province"];
            $data[6] = "(+53) ".$own["phoneCode"]." ".$own["phone"];
            $data[7] = $own["mobile"];
            $data[8] = $own["status"];

            array_push($results, $data);
        }

        return $results;
    }

    private function createSheetDirectoryFragment($excel, $sheetName, $data) {
        $sheet = $this->createSheet($excel, $sheetName);
        $sheet->setCellValue('a1', 'Código');
        $sheet->setCellValue('b1', 'Nombre');
        $sheet->setCellValue('c1', 'Propietario (s)');
        $sheet->setCellValue('d1', 'Dirección');
        $sheet->setCellValue('e1', 'Municipio');
        $sheet->setCellValue('f1', 'Provincia');
        $sheet->setCellValue('g1', 'Teléfono');
        $sheet->setCellValue('h1', 'Celular');
        $sheet->setCellValue('i1', 'Estado');

        $sheet = $this->styleHeader("a1:i1", $sheet);

        $sheet->fromArray($data, ' ', 'A2');

        $this->setColumnAutoSize("a", "i", $sheet);
        $sheet->setAutoFilter($sheet->calculateWorksheetDimension());
        return $excel;
    }

    private function configExcel($title, $description, $category) {
        $excel = new \PHPExcel();

        $properties = new \PHPExcel_DocumentProperties();

        $properties->setCreator("MyCasaParticular.com")
                ->setTitle($title)
                ->setLastModifiedBy("MyCasaParticular.com")
                ->setDescription($description)
                ->setSubject($title)
                ->setKeywords("excel MyCasaParticular $category")
                ->setCategory($category);
        $excel->setProperties($properties);
        return $excel;
    }

    private function createSheet($excel, $sheetName) {
        $sheet = new \PHPExcel_Worksheet($excel, $sheetName);
        $excel->addSheet($sheet, -1);
        //$sheet = $excel->createSheet(-1);
        $sheet->setTitle($sheetName);
        return $sheet;
    }

    private function styleError($row, $sheet) {
        $style = array(
            'font' => array(
                'bold' => true,
                'color' => array('rgb' => 'FF0000'),
            ),
        );
        $sheet->getStyle($row)->applyFromArray($style);
        return $sheet;
    }

    private function styleHeader($header, $sheet) {
        $sheet->getStyle($header)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB('dddddd');
        $style = array(
            'font' => array('bold' => true,),
            'alignment' => array('horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,),
        );
        $sheet->getStyle($header)->applyFromArray($style);
        return $sheet;
    }

    private function setColumnAutoSize($startColumn, $endColumn, $sheet) {
        for ($col = ord($startColumn); $col <= ord($endColumn); $col++) {
            $sheet->getColumnDimension(chr($col))->setAutoSize(true);
        }

        return $sheet;
    }

    private function export($fileName) {
        return $this->exportToDirectory($fileName, $this->excelDirectoryPath);
    }

    private function exportToDirectory($fileName, $directory) {
        $content = file_get_contents($directory . $fileName);
        return new Response(
            $content, 200, array(
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet; charset=utf-8',
                'Content-Disposition' => 'attachment; filename="' . $fileName . '"'
            )
        );
    }

    private function save($excel, $fileName) {
        $this->saveToDirectory($excel, $fileName, $this->excelDirectoryPath);
    }

    private function saveToDirectory($excel, $fileName, $directoryPath) {
        FileIO::createDirectoryIfNotExist($directoryPath);
        FileIO::deleteFile($directoryPath . $fileName);

        $excel->setActiveSheetIndex(0);

        $writer = new \PHPExcel_Writer_Excel2007($excel);
        $writer->save($directoryPath . $fileName);
    }

    public function exportOwnershipVsReservationsStats(Request $request, $reportId, $dateFrom, $dateTo, $provinceId = "", $municipalityId = "", $destinationId = "", $fileName= "resumenAlojamientosReservas") {
        $excel = $this->configExcel("Reporte resumen de propiedades vs reservaciones", "Reporte resumen de propiedades vs reservaciones de MyCasaParticular", "reportes");

        $location = "Cuba";
        $sheetName = "Cuba";
        $province = null;
        $municipality = null;
        $destination = null;

        if($provinceId != -1 && $provinceId != "")
        {
            $province =  $this->em->getRepository("mycpBundle:province")->find($provinceId);
            $location = $province->getProvName();
            $sheetName = $province->getProvName();
        }

        if($municipalityId != -1 && $municipalityId != "")
        {
            $municipality =  $this->em->getRepository("mycpBundle:municipality")->find($municipalityId);
            $location = $municipality->getMunName();
            $sheetName = $municipality->getMunName();
        }

        if($destinationId != -1 && $destinationId != "")
        {
            $destination =  $this->em->getRepository("mycpBundle:destination")->find($destinationId);
            $location = $destination->getDesName();
            $sheetName = $destination->getDesName();
        }

        $data = $this->dataForAccommodationReservationSummaryStat($province, $municipality, $destination, $dateFrom, $dateTo );

        if (count($data["data"]) > 0)
            $excel = $this->createSheetForAccommodationReservationSummaryStat($excel, $sheetName, $reportId, $location, $data);

        $fileName = $this->getFileName($fileName);
        $this->save($excel, $fileName);
        return $this->export($fileName);
    }
    public function exportReservationsByClientReport(Request $request, $reportId, $dateFrom, $dateTo, $fileName= "resumenReservasxClientes") {
        $excel = $this->configExcel("Reporte resumen de reservaciones por clientes", "Reporte resumen de reservaciones por clientes de MyCasaParticular", "reportes");
        $dateFrom=\DateTime::createFromFormat('Y-m-d',$dateFrom);
        $dateTo=\DateTime::createFromFormat('Y-m-d', $dateTo);
        $data = $this->dataForReservationsByClientReport($dateFrom->format('Y-m-d'), $dateTo->format('Y-m-d') );
        if (count($data) > 0)
            $excel = $this->createSheetForReservationsByClientReport($excel, 'Resumen', $reportId, $data, $dateFrom, $dateTo);

        $fileName = $this->getFileName($fileName);
        $this->save($excel, $fileName);
        return $this->export($fileName);
    }
    public function exportBookingDetailsReport($data, $dateFrom, $dateTo, $fileName= "resumenBookings") {
        $excel = $this->configExcel("Reporte resumen de bookings", "Reporte resumen de Bookings de MyCasaParticular", "reportes");
        if (count($data) > 0)
            $excel = $this->createSheetForBookingDetailsReport($excel, 'Bookings', $data, $dateFrom, $dateTo);

        $fileName = $this->getFileName($fileName);
        $this->save($excel, $fileName);
        return $this->export($fileName);
    }
    public function exportdetails_client_reservation_to_excelReport($data, $dateFrom, $dateTo,$client, $fileName= "filtroReservasCliente") {
        $excel = $this->configExcel("Reporte reservas de cliente", "Reporte de Reservas de cliente de MyCasaParticular", "reportes");
        if (count($data) > 0)
            $excel = $this->createSheetForClientReservationsReport($excel, 'Reservas', $data,$client, $dateFrom, $dateTo);

        $fileName = $this->getFileName($fileName);
        $this->save($excel, $fileName);
        return $this->export($fileName);
    }
    private function dataForReservationsByClientReport($dateFrom, $dateTo){
        $results = array();
        $reportContent = $this->em->getRepository("mycpBundle:report")->reservationsByClientsSummary($dateFrom, $dateTo);

        $dataArr = array();
        $sol=0;
        $disp=0;
        $no_disp=0;
        $pend=0;
        $res=0;
        $venc=0;
        $cancel=0;
        foreach ($reportContent as $content) {
            $data = array();
            if($content["solicitudes"]>0)
            $sol=$sol+$content["solicitudes"];
            if($content["disponibles"]>0)
            $disp=$disp+$content["disponibles"];
            if($content["no_disponibles"]>0)
            $no_disp=$no_disp+$content["no_disponibles"];
            if($content["pendientes"]>0)
            $pend=$pend+$content["pendientes"];
            if($content["reservas"]>0)
            $res=$res+$content["reservas"];
            if($content["vencidas"]>0)
            $venc=$venc+$content["vencidas"];
            if($content["canceladas"]>0)
            $cancel=$cancel+$content["canceladas"];

            $data[0] = $content["user_name"].' '.$content["user_last_name"];
            $data[1] = $content["solicitudes"];
            $data[2] = $content["disponibles"];
            $data[3] = $content["no_disponibles"];
            $data[4] = $content["pendientes"];
            $data[5] = $content["reservas"];
            $data[6] = $content["vencidas"];
            $data[7] = $content["canceladas"];
            array_push($dataArr, $data);
        }
        if(count($reportContent)>0){
            $data = array();
            $data[0] = "Total";
            $data[1] = ''.$sol;
            $data[2] = ''.$disp;
            $data[3] = ''.$no_disp;
            $data[4] = ''.$pend;
            $data[5] = ''.$res;
            $data[6] = ''.$venc;
            $data[7] = ''.$cancel;
            array_push($dataArr, $data);
        }
        return $dataArr;


    }
    private function createSheetForReservationsByClientReport($excel,$sheetName,$reportId, $data, $dateFrom, $dateTo){
        $report = $this->em->getRepository("mycpBundle:report")->find($reportId);
        $sheet = $this->createSheet($excel, $sheetName);
        $sheet->setCellValue('a1', "Reporte: ".$report->getReportName());
        $now = new \DateTime();
        $sheet->setCellValue('a2', 'Rango: '.$dateFrom->format('d/m/Y').'-'.$dateTo->format('d/m/Y'));

        $sheet->setCellValue('b2', 'Generado: '.$now->format('d/m/Y H:s'));

        $sheet->setCellValue('a5', 'Cliente');
        $sheet->setCellValue('b5', 'Solicitudes');
        $sheet->setCellValue('c5', 'Disponibles');
        $sheet->setCellValue('d5', 'No Disponibles');
        $sheet->setCellValue('e5', 'Pendientes');
        $sheet->setCellValue('f5', 'Reservaciones');
        $sheet->setCellValue('g5', 'Vencidas');
        $sheet->setCellValue('h5', 'Canceladas');

        $sheet = $this->styleHeader("a5:h5", $sheet);
        $style = array(
            'font' => array(
                'bold' => true,
                'size' => 14
            ),
        );
        $sheet->getStyle("a1")->applyFromArray($style);

        $sheet->fromArray($data, ' ', 'A6');
        $this->setColumnAutoSize("a", "h", $sheet);
        $sheet->setAutoFilter("A5:H".(count($data) + 5));

        return $excel;
    }
    private function createSheetForBookingDetailsReport($excel,$sheetName, $data, $dateFrom, $dateTo){
        $sheet = $this->createSheet($excel, $sheetName);
        $sheet->setCellValue('a1', "Reporte: Resumen de Bookings");
        $now = new \DateTime();
        $sheet->setCellValue('a2', 'Rango: '.$dateFrom.'-'.$dateTo);

        $sheet->setCellValue('b2', 'Generado: '.$now->format('d/m/Y H:s'));

        $sheet->setCellValue('a5', 'ID Booking');
        $sheet->setCellValue('b5', 'FechaBooking');
        $sheet->setCellValue('c5', 'Prepago');
        $sheet->setCellValue('d5', 'Moneda');
        $sheet->setCellValue('e5', 'Usuario');
        $sheet->setCellValue('f5', 'País');
        $sheet->setCellValue('g5', 'Cód. Reserva');
        $sheet->setCellValue('h5', 'Cód. Casa');

        $sheet = $this->styleHeader("a5:h5", $sheet);
        $style = array(
            'font' => array(
                'bold' => true,
                'size' => 14
            ),
        );
        $sheet->getStyle("a1")->applyFromArray($style);

        $sheet->fromArray($data, ' ', 'A6');
        $this->setColumnAutoSize("a", "h", $sheet);
        $sheet->setAutoFilter("A5:H".(count($data) + 5));

        return $excel;
    }
    private function createSheetForClientReservationsReport($excel,$sheetName, $data,$client, $dateFrom, $dateTo){
        $sheet = $this->createSheet($excel, $sheetName);
        $sheet->setCellValue('a1', "Reporte: Listado de Reservas");
        $sheet->setCellValue('b1', "Cliente: ".$client->getName().' '.$client->getUserLastName());
        $now = new \DateTime();
        $sheet->setCellValue('a2', 'Rango: '.$dateFrom->format('d/m/Y').'-'.$dateTo->format('d/m/Y'));

        $sheet->setCellValue('b2', 'Generado: '.$now->format('d/m/Y H:s'));

        $sheet->setCellValue('a5', 'Fecha Reserva');
        $sheet->setCellValue('b5', 'Cód. Reserva');
        $sheet->setCellValue('c5', 'Propiedad');
        $sheet->setCellValue('d5', 'Habitaciones');
        $sheet->setCellValue('e5', 'Adultos');
        $sheet->setCellValue('f5', 'Niños');
        $sheet->setCellValue('g5', 'Noches');
        $sheet->setCellValue('h5', 'Fecha de entrada');
        $sheet->setCellValue('i5', 'Precio');
        $sheet->setCellValue('j5', 'Destino');
        $sheet->setCellValue('k5', 'Estado Reserva');

        $sheet = $this->styleHeader("a5:k5", $sheet);
        $style = array(
            'font' => array(
                'bold' => true,
                'size' => 14
            ),
        );
        $sheet->getStyle("a1")->applyFromArray($style);
        $sheet->getStyle("b1")->applyFromArray($style);

        $sheet->fromArray($data, ' ', 'A6');
        $this->setColumnAutoSize("a", "k", $sheet);
        $sheet->setAutoFilter("A5:K".(count($data) + 5));

        return $excel;
    }
    private function dataForAccommodationReservationSummaryStat($province, $municipality, $destination, $dateFrom, $dateTo) {
        $results = array();

        $accommodations = $this->em->getRepository('mycpBundle:ownershipReservationStat')->getAccommodations(null, $province,$municipality, $destination, $dateFrom, $dateTo);
        $generalData = $this->em->getRepository('mycpBundle:ownershipReservationStat')->getBulb(null, $province,$municipality, $destination, $dateFrom, $dateTo);

        $data = array();
        $nomTitle = array();
        $currencyCell = array();
        $index = 1;

        $data[0] = "Generales";
        $data[1] = "";
        array_push($results, $data);
        array_push($nomTitle, $index);
        $index++;
        $nomParent = "";

        foreach ($generalData as $content) {
            if ($content[0]->getStatNomenclator()->getNomParent() != $nomParent) {
                $nomParent = $content[0]->getStatNomenclator()->getNomParent();
                $data[0] = "Por " . $nomParent->getNomName();
                $data[1] = "";
                array_push($results, $data);
                array_push($nomTitle, $index);
                $index++;
            }

            $data[0] = $content[0]->getStatNomenclator()->getNomName();
            $data[1] = $content["stat_value"];
            if($nomParent == "Ingresos")
                array_push($currencyCell, $index);

            $index++;
            array_push($results, $data);
        }

        foreach($accommodations as $accommodation) {
            $nomParent = "";
            $ownership = $accommodation->getStatAccommodation();

            $data[0] = "";
            $data[1] = "";
            array_push($results, $data);
            $index++;

            $data[0] = $ownership->getOwnMcpCode(). " - ". $ownership->getOwnName();
            $data[1] = "";
            array_push($results, $data);
            array_push($nomTitle, $index);
            $index++;

            $reportContent = $this->em->getRepository('mycpBundle:ownershipReservationStat')->getBulb(null, $province,$municipality, $destination, $dateFrom, $dateTo, $ownership->getOwnId());
            foreach ($reportContent as $content) {
                if ($content[0]->getStatNomenclator()->getNomParent() != $nomParent) {
                    $nomParent = $content[0]->getStatNomenclator()->getNomParent();

                    $data[0] = "Por " . $nomParent->getNomName();
                    $data[1] = "";
                    array_push($results, $data);
                    array_push($nomTitle, $index);
                    $index++;
                }

                $data[0] = $content[0]->getStatNomenclator()->getNomName();
                $data[1] = $content["stat_value"];

                if($nomParent == "Ingresos")
                    array_push($currencyCell, $index);

                $index++;
                array_push($results, $data);
            }
        }

        return array("data" => $results, "nomTitles" => $nomTitle, "currencyCells" => $currencyCell);
    }

    private function createSheetForAccommodationReservationSummaryStat($excel, $sheetName, $reportId, $location, $data) {

        $report = $this->em->getRepository("mycpBundle:report")->find($reportId);
        $sheet = $this->createSheet($excel, $sheetName);
        $sheet->setCellValue('a1', "Reporte: ".$report->getReportName());
        $sheet->setCellValue('a2', 'Alcance: '.$location);
        $now = new \DateTime();
        $sheet->setCellValue('b2', 'Generado: '.$now->format('d/m/Y H:s'));

        $sheet->setCellValue('a4', 'Categoría');
        $sheet->setCellValue('b4', 'Valor');

        //dump($data["nomTitles"]);die;

        $sheet = $this->styleHeader("a4:b4", $sheet);
        $style = array(
            'font' => array(
                'bold' => true,
                'size' => 14
            ),
        );
        $sheet->getStyle("a1")->applyFromArray($style);

        $styleSubTitles = array(
            'font' => array(
                'bold' => true,
                'size' => 12
            ),
        );
        foreach($data["nomTitles"] as $titleIndex)
        {
            $sheet->getStyle("a".($titleIndex + 4))->applyFromArray($styleSubTitles);
        }

        $sheet
            ->getStyle("a5:a".(count($data["data"])+4))
            ->getNumberFormat()
            ->setFormatCode( \PHPExcel_Style_NumberFormat::FORMAT_TEXT );

        foreach($data["currencyCells"] as $titleIndex)
        {
            $sheet->getStyle("b".($titleIndex + 4))->getNumberFormat()
                ->setFormatCode( \PHPExcel_Style_NumberFormat::FORMAT_CURRENCY_USD_SIMPLE );
        }

        $sheet->fromArray($data["data"], ' ', 'A5');

        $this->setColumnAutoSize("a", "b", $sheet);

        return $excel;
    }


    public function exportAndDeleteUDetails($uDetailsCollection, $uDetailsDirectory)
    {
        if(count($uDetailsCollection)) {
            $excel = $this->configExcel("Detalles de no disponibilidad", "Detalles de no disponibilidad - MyCasaParticular", "reportes");
            FileIO::createDirectoryIfNotExist($uDetailsDirectory);
            $data = $this->dataForUDetails($uDetailsCollection);

            if (count($data) > 0)
                $excel = $this->createSheetForUDetails($excel, "Disponibilidad", $data);

            $fileName = FileIO::getDatedFileName("disponibilidad", ".xlsx");
            $this->saveToDirectory($excel, $fileName, $uDetailsDirectory);
            return $this->exportToDirectory($fileName, $uDetailsDirectory);

        }
    }

    private function dataForUDetails($uDetailsCollection) {
        $results = array();
        $data = array();

        foreach ($uDetailsCollection as $uDetails) {
            $data[0] = $uDetails->getRoom()->getRoomOwnership()->getOwnMcpCode();
            $data[1] = $uDetails->getRoom()->getRoomNum();
            $data[2] = $uDetails->getUdFromDate()->format("d/m/Y");
            $data[3] = $uDetails->getUdToDate()->format("d/m/Y");
            $data[4] = $uDetails->getUdReason();
            array_push($results, $data);

            $this->em->remove($uDetails);
        }

        $this->em->flush();
        return $results;
    }

    private function createSheetForUDetails($excel, $sheetName, $data) {

        $sheet = $this->createSheet($excel, $sheetName);
        $sheet->setCellValue('a1', "Detalles de No Disponibilidad");
        $now = new \DateTime();
        $sheet->setCellValue('a2', 'Generado: '.$now->format('d/m/Y H:s'));

        $sheet->setCellValue('a4', 'Propiedad');
        $sheet->setCellValue('b4', 'No. Habitación');
        $sheet->setCellValue('c4', 'Desde');
        $sheet->setCellValue('d4', 'Hasta');
        $sheet->setCellValue('e4', 'Motivo');

        $sheet = $this->styleHeader("a4:e4", $sheet);
        $style = array(
            'font' => array(
                'bold' => true,
                'size' => 14
            ),
        );
        $sheet->getStyle("a1")->applyFromArray($style);

        /*$sheet
            ->getStyle("a5:a".(count($data["data"])+4))
            ->getNumberFormat()
            ->setFormatCode( \PHPExcel_Style_NumberFormat::FORMAT_TEXT );

        foreach($data["currencyCells"] as $titleIndex)
        {
            $sheet->getStyle("b".($titleIndex + 4))->getNumberFormat()
                ->setFormatCode( \PHPExcel_Style_NumberFormat::FORMAT_CURRENCY_USD_SIMPLE );
        }*/

        $sheet->fromArray($data, ' ', 'A5');

        $this->setColumnAutoSize("a", "e", $sheet);
        //$sheet->setAutoFilter("A4:e6");

        return $excel;
    }

    private function getFileName($preffixName)
    {
        $date =  new \DateTime();
        $date = $date->format("Ymd");
        return $preffixName."_".$date.".xlsx";

    }

    public function createExcelForSalesReports($data){
        $excel = $this->configExcel("Reporte de ventas", "Reporte resumen de propiedades y sus reservaciones de MyCasaParticular", "reportes");
        $sheet = $this->createSheet($excel, 'Reporte');
        $sheet->setCellValue('a1', "Resumen de propiedades con reservas");
        $now = new \DateTime();
        $sheet->setCellValue('a2', 'Generado: '.$now->format('d/m/Y H:s'));

        $sheet->setCellValue('a4', 'Código');
        $sheet->setCellValue('b4', 'Nombre');
        $sheet->setCellValue('c4', 'Propietario1');
        $sheet->setCellValue('d4', 'Propietario2');
        $sheet->setCellValue('e4', 'Correo1');
        $sheet->setCellValue('f4', 'Correo2');
        $sheet->setCellValue('g4', 'Telefono');
        $sheet->setCellValue('h4', 'Código teleseleccion');
        $sheet->setCellValue('i4', 'Celular');
        $sheet->setCellValue('j4', 'Calle');
        $sheet->setCellValue('k4', 'No');
        $sheet->setCellValue('l4', 'Entre');
        $sheet->setCellValue('m4', 'Y');
        $sheet->setCellValue('n4', 'Municipio');
        $sheet->setCellValue('o4', 'Provincia');
        $sheet->setCellValue('p4', 'Gestor');
        $sheet->setCellValue('q4', 'Creada');
        $sheet->setCellValue('r4', 'Solicitudes');
        $sheet->setCellValue('s4', 'Reservas');
        $sheet->setCellValue('t4', 'Ingresos');
        $sheet->setCellValue('u4', 'Tipo');
        $sheet->setCellValue('v4', 'Selección');
        $sheet->setCellValue('w4', 'Habitaciones');
        $sheet->setCellValue('x4', 'Precio Baja');
        $sheet->setCellValue('y4', 'Precio Alta');
        $sheet->setCellValue('z4', 'Porciento acordado');
        $sheet->setCellValue('aa4', 'Reviews');
        $sheet->setCellValue('ab4', 'Categoría');
        $sheet->setCellValue('ac4', 'fotos');
        $sheet->setCellValue('ad4', 'Módulo casa');
        $sheet->setCellValue('ae4', 'Estado');

        $sheet = $this->styleHeader("a4:ae4", $sheet);
        $style = array(
            'font' => array(
                'bold' => true,
                'size' => 14
            ),
        );
        $sheet->getStyle("a1")->applyFromArray($style);

        $sheet->fromArray($data, ' ', 'A5');

        $this->setColumnAutoSize("a", "ae", $sheet);
        $fileName = $this->getFileName('Reporte Sales');
        $this->save($excel, $fileName);
        return $this->export($fileName);
    }
    public function createExcelForSalesReportsCommand(){
        $conn=$this->get('doctrine.dbal.default_connection');
        $query="SELECT
  own.own_mcp_code as codigo,
  own.own_name as nombre,
  own.own_homeowner_1 as propietario1,
  own.own_homeowner_2 as propietario2,
  own.own_email_1 as correo1,
  own.own_email_2 as correo2,
  own.own_phone_number AS telefono,
  own.own_phone_code as codigo_telefono,
  own.own_mobile_number as celular,
  own.own_address_street as calle,
  own.own_address_number as numero,
  own.own_address_between_street_1 as entre,
  own.own_address_between_street_2 as y,
  municipality.mun_name as municipio,
  province.prov_name as provincia,
  own.own_saler as gestor,
  own.own_creation_date as creada,
  (SELECT COUNT(ownershipreservation.own_res_id) FROM ownershipreservation INNER JOIN generalreservation ON generalreservation.gen_res_id = ownershipreservation.own_res_gen_res_id WHERE generalreservation.gen_res_own_id=own.own_id) AS solicitudes,
  (SELECT COUNT(ownershipreservation.own_res_id) FROM ownershipreservation INNER JOIN generalreservation ON generalreservation.gen_res_id = ownershipreservation.own_res_gen_res_id WHERE generalreservation.gen_res_own_id=own.own_id AND ownershipreservation.own_res_status=5) AS reservas,
  (SELECT SUM(ownershipreservation.own_res_total_in_site) FROM ownershipreservation INNER JOIN generalreservation ON generalreservation.gen_res_id = ownershipreservation.own_res_gen_res_id WHERE generalreservation.gen_res_own_id=own.own_id AND ownershipreservation.own_res_status=5) AS ingresos,
  own.own_type as tipo,
  own.own_selection as seleccion,
  own.own_rooms_total as habitaciones,
  own.own_minimum_price as precio_baja,
  own.own_maximum_price as precio_alta,
  own.own_commission_percent as porciento_acordado,
  own.own_comments_total as reviews,
  own.own_category as categoria,
  (SELECT COUNT(ownershipphoto.own_pho_id) FROM ownershipphoto INNER JOIN ownership ON ownership.own_id = ownershipphoto.own_pho_own_id WHERE ownershipphoto.own_pho_own_id=own.own_id) AS fotos,
   (SELECT COUNT(usercasa.user_casa_id) FROM usercasa INNER JOIN ownership ON ownership.own_id = usercasa.user_casa_ownership WHERE usercasa.user_casa_ownership=own.own_id) AS modulo_casa,
  ownershipstatus.status_name as estado
FROM ownership own
  INNER JOIN municipality ON municipality.mun_id = own.own_address_municipality
  INNER JOIN province ON province.prov_id = municipality.mun_prov_id
  INNER JOIN ownershipstatus ON ownershipstatus.status_id = own.own_status
ORDER BY own.own_mcp_code ASC
;
";

        $stmt=$conn->prepare($query);
        $stmt->execute();
        $result=$stmt->fetchAll();
        $excel = $this->configExcel("Reporte de ventas", "Reporte resumen de propiedades y sus reservaciones de MyCasaParticular", "reportes");
        $sheet = $this->createSheet($excel, 'Reporte');
        $sheet->setCellValue('a1', "Resumen de propiedades con reservas");
        $now = new \DateTime();
        $sheet->setCellValue('a2', 'Generado: '.$now->format('d/m/Y H:s'));

        $sheet->setCellValue('a4', 'Código');
        $sheet->setCellValue('b4', 'Nombre');
        $sheet->setCellValue('c4', 'Propietario1');
        $sheet->setCellValue('d4', 'Propietario2');
        $sheet->setCellValue('e4', 'Correo1');
        $sheet->setCellValue('f4', 'Correo2');
        $sheet->setCellValue('g4', 'Telefono');
        $sheet->setCellValue('h4', 'Código teleseleccion');
        $sheet->setCellValue('i4', 'Celular');
        $sheet->setCellValue('j4', 'Calle');
        $sheet->setCellValue('k4', 'No');
        $sheet->setCellValue('l4', 'Entre');
        $sheet->setCellValue('m4', 'Y');
        $sheet->setCellValue('n4', 'Municipio');
        $sheet->setCellValue('o4', 'Provincia');
        $sheet->setCellValue('p4', 'Gestor');
        $sheet->setCellValue('q4', 'Creada');
        $sheet->setCellValue('r4', 'Solicitudes');
        $sheet->setCellValue('s4', 'Reservas');
        $sheet->setCellValue('t4', 'Ingresos');
        $sheet->setCellValue('u4', 'Tipo');
        $sheet->setCellValue('v4', 'Selección');
        $sheet->setCellValue('w4', 'Habitaciones');
        $sheet->setCellValue('x4', 'Precio Baja');
        $sheet->setCellValue('y4', 'Precio Alta');
        $sheet->setCellValue('z4', 'Porciento acordado');
        $sheet->setCellValue('aa4', 'Reviews');
        $sheet->setCellValue('ab4', 'Categoría');
        $sheet->setCellValue('ac4', 'fotos');
        $sheet->setCellValue('ad4', 'Módulo casa');
        $sheet->setCellValue('ae4', 'Estado');

        $sheet = $this->styleHeader("a4:ae4", $sheet);
        $style = array(
            'font' => array(
                'bold' => true,
                'size' => 14
            ),
        );
        $sheet->getStyle("a1")->applyFromArray($style);

        $sheet->fromArray($result, ' ', 'A5');

        $this->setColumnAutoSize("a", "ae", $sheet);
        $fileName = $this->getFileName('Reporte Sales');
        $this->save($excel, $fileName);
        return $this->excelDirectoryPath.$fileName;
    }


    public function exportReservations($reservations, $startingDate, $fileName = "reservaciones") {
        if(count($reservations) > 0) {
            $excel = $this->configExcel("Listado de reservaciones", "Listado de reservaciones de MyCasaParticular", "reservaciones");

            $data = $this->dataForReservations($excel, $reservations);

            if (count($data) > 0)
                $excel = $this->createSheetForReservations($excel, "Reservaciones", $data, $startingDate);

            $fileName = $this->getFileName($fileName);
            $this->save($excel, $fileName);

            return $this->export($fileName);
        }
    }

    private function dataForReservations($excel,$reservations) {
        $results = array();
        $currentReservation = "";

        foreach ($reservations as $reservation) {
            $data = array();

            $generalReservation = $reservation->getOwnResGenResId();

            //Fecha Reserva
            $data[0] = ($currentReservation != $generalReservation->getGenResId()) ? $generalReservation->getGenResDate()->format("d/m/Y"): "";
            //Código Reserva
            $data[1] = ($currentReservation != $generalReservation->getGenResId()) ? $generalReservation->getCASId(): "";
            //Estado Reserva
            $data[2] = ($currentReservation != $generalReservation->getGenResId()) ? generalReservation::getStatusName($generalReservation->getGenResStatus()) : "";
            //Precio Total
            $data[3] = ($currentReservation != $generalReservation->getGenResId()) ? $generalReservation->getGenResTotalInSite(). " CUC": "";
            //Cliente
            $data[4] = ($currentReservation != $generalReservation->getGenResId()) ? $generalReservation->getGenResUserId()->getUserCompleteName(): "";

            $accommodation = $generalReservation->getGenResOwnId();

            //Código alojamiento
            $data[5] = ($currentReservation != $generalReservation->getGenResId()) ? $accommodation->getOwnMcpCode(): "";
            //Nombre alojamiento
            $data[6] = ($currentReservation != $generalReservation->getGenResId()) ? $accommodation->getOwnName(): "";
            //Propietarios
            $homeOwners = $accommodation->getOwnHomeowner1().(($accommodation->getOwnHomeowner2() != "")? " / ". $accommodation->getOwnHomeowner2() : "");
            $data[7] = ($currentReservation != $generalReservation->getGenResId()) ? $homeOwners: "";
            //Telefono
            $phone = ($accommodation->getOwnPhoneNumber() != "")?"(+53) ".$accommodation->getOwnAddressProvince()->getProvPhoneCode(). " ".$accommodation->getOwnPhoneNumber() : "";
            $data[8] = ($currentReservation != $generalReservation->getGenResId()) ? $phone: "";
            //Movil
            $data[9] = ($currentReservation != $generalReservation->getGenResId()) ? $accommodation->getOwnMobileNumber(): "";
            //Comisión MyCP
            $data[10] = ($currentReservation != $generalReservation->getGenResId()) ? $accommodation->getOwnCommissionPercent()."%": "";

            //Tipo de habitación
            $data[11] = $reservation->getOwnResRoomType();
            //Adultos
            $data[12] = $reservation->getOwnResCountAdults();
            //Niños
            $data[13] = $reservation->getOwnResCountChildrens();
            //Precio Habitación
            $roomPrice = 0;

            if($reservation->getOwnResNightPrice() != null)
                $roomPrice = $reservation->getOwnResNightPrice();
            else {
                $idDestination = ($accommodation->getOwnDestination() != null) ? $accommodation->getOwnDestination()->getDesId() : null;
                $minSeason = Time::season($this->em, $reservation->getOwnResReservationFromDate(), $reservation->getOwnResReservationFromDate(), $reservation->getOwnResReservationToDate(), $idDestination);
                $maxSeason = Time::season($this->em, $reservation->getOwnResReservationToDate(), $reservation->getOwnResReservationFromDate(), $reservation->getOwnResReservationToDate(), $idDestination);

                if ($minSeason == season::SEASON_TYPE_HIGH || $maxSeason == season::SEASON_TYPE_HIGH)
                    $roomPrice = $reservation->getPriceBySeason(season::SEASON_TYPE_HIGH);
                elseif($minSeason == season::SEASON_TYPE_SPECIAL || $maxSeason == season::SEASON_TYPE_SPECIAL)
                    $roomPrice = $reservation->getPriceBySeason(season::SEASON_TYPE_SPECIAL);
                else
                    $roomPrice = $reservation->getPriceBySeason(season::SEASON_TYPE_LOW);
            }
            $data[14] = $roomPrice." CUC";

            //Fecha de llegada
            $data[15] = $reservation->getOwnResReservationFromDate()->format("d/m/Y");
            //Noches
            $data[16] = Time::nights($reservation->getOwnResReservationFromDate()->format("Y-m-d"), $reservation->getOwnResReservationToDate()->format("Y-m-d"), "Y-m-d");

            array_push($results, $data);

            if($currentReservation != $reservation->getOwnResGenResId()->getGenResId())
                $currentReservation = $reservation->getOwnResGenResId()->getGenResId();
        }

        return $results;
    }

    private function createSheetForReservations($excel, $sheetName, $data, $startingDate) {
        $sheet = $this->createSheet($excel, $sheetName);

        $sheet->setCellValue('a1', "Listado de reservas");
        $sheet->mergeCells("A1:Q1");
        $now = new \DateTime();
        $sheet->setCellValue('a2', 'Reporte generado con las reservas creadas a partir del: '.$startingDate->format('d/m/Y'));
        $sheet->mergeCells("A2:Q2");
        $sheet->setCellValue('a3', 'Fecha de creación: '.$now->format('d/m/Y H:s'));
        $sheet->mergeCells("A3:Q3");

        $sheet->setCellValue('a5', 'Fecha Reserva');
        $sheet->setCellValue('b5', 'Código Reserva');
        $sheet->setCellValue('c5', 'Estado Reserva');
        $sheet->setCellValue('d5', 'Precio Total');
        $sheet->setCellValue('e5', 'Cliente');
        $sheet->setCellValue('f5', 'Código Alojamiento');
        $sheet->setCellValue('g5', 'Nombre Alojamiento');
        $sheet->setCellValue('h5', 'Propietario(s)');
        $sheet->setCellValue('i5', 'Teléfono)');
        $sheet->setCellValue('j5', 'Móvil');
        $sheet->setCellValue('k5', 'Comisión MyCP');
        $sheet->setCellValue('l5', 'Tipo Habitación');
        $sheet->setCellValue('m5', 'Adultos');
        $sheet->setCellValue('n5', 'Niños');
        $sheet->setCellValue('o5', 'Precio');
        $sheet->setCellValue('p5', 'Fecha Llegada');
        $sheet->setCellValue('q5', 'Noches');

        $centerStyle = array(
            'alignment' => array(
                'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            )
        );
        $sheet->getStyle("A1:Q1")->applyFromArray($centerStyle);

        $sheet = $this->styleHeader("A5:Q5", $sheet);

        $style = array(
            'font' => array(
                'bold' => true,
                'size' => 14
            ),
        );
        $sheet->getStyle("a1")->applyFromArray($style);

        $sheet->fromArray($data, ' ', 'A6');

        $this->setColumnAutoSize("a", "q", $sheet);

        //$sheet->setAutoFilter($sheet->calculateWorksheetDimension());
        $sheet->setAutoFilter("A5:Q".(count($data)+5));

        return $excel;
    }

    public function exportReservationRange(Request $request, $reportId, $dateFrom, $dateTo, $fileName= "resumenReservaciones") {
        $excel = $this->configExcel("Reporte resumen de reservaciones", "Reporte resumen de reservaciones de MyCasaParticular", "reportes");

        $range = "Desde ". $dateFrom." al ".$dateTo;
        $sheetName = "General";

        $data = $this->dataForReservationRange($dateFrom, $dateTo);

        if (count($data) > 0)
            $excel = $this->createSheetForReservationRange($excel, $sheetName, $reportId, $range, $data);

        $fileName = $this->getFileName($fileName);
        $this->save($excel, $fileName);
        return $this->export($fileName);
    }

    private function dataForReservationRange($dateFrom, $dateTo) {
        $results = array();
        $list = $this->em->getRepository('mycpBundle:generalReservation')->getReservationRangeReportContent($dateFrom, $dateTo);

        foreach ($list as $item) {
            $data = array();

            $data[0] = generalReservation::getStatusName($item["status"]);
            $data[1] = $item["total"];
            $data[2] = $item["nights"];
            array_push($results, $data);
        }


        return $results;
    }

    private function createSheetForReservationRange($excel, $sheetName, $reportId, $range, $data) {

        $report = $this->em->getRepository("mycpBundle:report")->find($reportId);
        $sheet = $this->createSheet($excel, $sheetName);
        $sheet->setCellValue('a1', "Reporte: ".$report->getReportName());
        $sheet->setCellValue('a2', 'Rango: '.$range);
        $now = new \DateTime();
        $sheet->setCellValue('b2', 'Generado: '.$now->format('d/m/Y H:s'));

        $sheet->setCellValue('a4', 'Estado');
        $sheet->setCellValue('b4', 'Total');
        $sheet->setCellValue('c4', 'Noches');

        $sheet = $this->styleHeader("a4:c4", $sheet);
        $style = array(
            'font' => array(
                'bold' => true,
                'size' => 14
            ),
        );
        $sheet->getStyle("a1")->applyFromArray($style);

        $sheet
            ->getStyle("a5:a".(count($data)+4))
            ->getNumberFormat()
            ->setFormatCode( \PHPExcel_Style_NumberFormat::FORMAT_TEXT );

        $sheet->fromArray($data, ' ', 'A5');

        $this->setColumnAutoSize("a", "c", $sheet);

        return $excel;
    }

    public function exportReservationRangeDetails($list, $filter_string, $fileName= "reporteDetallesReservacionesPeriodo") {
        $excel = $this->configExcel("Resumen de reservaciones por período", "Resumen de reservaciones por período de MyCasaParticular", "reportes");

        $sheetName = "General";

        $data = $this->dataForReservationRangeDetails($list);

        if (count($data) > 0)
            $excel = $this->createSheetForReservationRangeDetails($excel, $sheetName, $data, $filter_string);

        $fileName = $this->getFileName($fileName);
        $this->save($excel, $fileName);
        return $this->export($fileName);
    }

    private function dataForReservationRangeDetails($list) {
        $results = array();

        foreach ($list as $item) {
            $data = array();

            $data[0] = $item[0]->getCASId();
            $data[1] = $item[0]->getGenResDate()->format("d/m/Y");
            $data[2] = $item[0]->getGenResOwnId()->getOwnMcpCode();
            $data[3] = $item[0]->getGenResUserId()->getUserUserName()." ".$item[0]->getGenResUserId()->getUserLastName();
            $data[4] = ($item[0]->getModifiedBy() != null && $item[0]->getModifiedBy() != "") ? $item["userName"]." ".$item["userLastName"] : " - ";
            $data[5] = $item["nights"];
            array_push($results, $data);
        }


        return $results;
    }

    private function createSheetForReservationRangeDetails($excel, $sheetName, $data, $filter_string) {

        $sheet = $this->createSheet($excel, $sheetName);
        $sheet->setCellValue('a1', "Reporte: Resumen de reservaciones (Detalles)");
        $sheet->mergeCells("A1:F1");
        $now = new \DateTime();
        $sheet->setCellValue('a2', 'Generado: '.$now->format('d/m/Y H:s'));
        $sheet->setCellValue('b2', 'Filtro: '.$filter_string);
        $sheet->mergeCells("B2:F2");

        $sheet->setCellValue('a4', 'Código Reserva');
        $sheet->setCellValue('b4', 'Fecha');
        $sheet->setCellValue('c4', 'Alojamiento');
        $sheet->setCellValue('d4', 'Turista');
        $sheet->setCellValue('e4', 'Usuario que atendió la reserva');
        $sheet->setCellValue('f4', 'Noches');

        $sheet = $this->styleHeader("a4:f4", $sheet);
        $style = array(
            'font' => array(
                'bold' => true,
                'size' => 14
            ),
        );
        $sheet->getStyle("a1")->applyFromArray($style);

        $sheet
            ->getStyle("a5:a".(count($data)+4))
            ->getNumberFormat()
            ->setFormatCode( \PHPExcel_Style_NumberFormat::FORMAT_TEXT );

        $sheet->fromArray($data, ' ', 'A5');

        $this->setColumnAutoSize("a", "f", $sheet);

        return $excel;
    }

    public function exportReservationUser(Request $request, $reportId, $dateFrom, $dateTo, $fileName= "resumenReservacionesUsuario") {
        $excel = $this->configExcel("Reporte resumen de reservaciones por usuario", "Reporte resumen de reservaciones por usuario de MyCasaParticular", "reportes");

        $range = "Desde ". $dateFrom." al ".$dateTo;
        $sheetName = "General";

        $data = $this->dataForReservationUser($dateFrom, $dateTo);

        if (count($data) > 0)
            $excel = $this->createSheetForReservationUser($excel, $sheetName, $reportId, $range, $data);

        $fileName = $this->getFileName($fileName);
        $this->save($excel, $fileName);
        return $this->export($fileName);
    }

    private function dataForReservationUser($dateFrom, $dateTo) {
        $results = array();
        $list = $this->em->getRepository('mycpBundle:generalReservation')->getReservationUserReportContent($dateFrom, $dateTo);

        $total_available = 0;
        $total_notAvailable = 0;
        $total_logs = 0;

        foreach ($list as $item) {
            $data = array();

            $total_available += $item["available"];
            $total_notAvailable += $item["non_available"];
            $total_logs += $item["logs"];

            $data[0] = $item["name"] . " " . $item["lastName"];
            $data[1] = $item["available"];
            $data[2] = $item["non_available"];
            $data[3] = $item["logs"];

            array_push($results, $data);
        }

        $data = array();
        $data[0] = "TOTAL";
        $data[1] = $total_available;
        $data[2] = $total_notAvailable;
        $data[3] = $total_logs;
        array_push($results, $data);



        return $results;
    }

    private function createSheetForReservationUser($excel, $sheetName, $reportId, $range, $data) {

        $report = $this->em->getRepository("mycpBundle:report")->find($reportId);
        $sheet = $this->createSheet($excel, $sheetName);
        $sheet->setCellValue('a1', "Reporte: ".$report->getReportName());
        $sheet->setCellValue('a2', 'Rango: '.$range);
        $now = new \DateTime();
        $sheet->setCellValue('b2', 'Generado: '.$now->format('d/m/Y H:s'));

        $sheet->setCellValue('a4', 'Usuario');
        $sheet->setCellValue('b4', 'Reservas Disponibles');
        $sheet->setCellValue('c4', 'Reservas No Disponibles');
        $sheet->setCellValue('d4', 'Operaciones');

        $sheet = $this->styleHeader("a4:d4", $sheet);
        $style = array(
            'font' => array(
                'bold' => true,
                'size' => 14
            ),
        );
        $sheet->getStyle("a1")->applyFromArray($style);

        $sheet
            ->getStyle("a5:a".(count($data)+4))
            ->getNumberFormat()
            ->setFormatCode( \PHPExcel_Style_NumberFormat::FORMAT_TEXT );

        $sheet->fromArray($data, ' ', 'A5');

        $this->setColumnAutoSize("a", "d", $sheet);

        return $excel;
    }
}

?>
