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
use MyCp\mycpBundle\Entity\ownershipReservation;
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
        $sheet->setCellValue('a1', 'Notificado');
        $sheet->setCellValue('b1', 'Reserva');
        $sheet->setCellValue('c1', 'Fecha Reserva');
        $sheet->setCellValue('d1', 'Propiedad');
        $sheet->setCellValue('e1', 'Propietario(s)');
        $sheet->setCellValue('f1', 'Teléfono (s)');
        $sheet->setCellValue('g1', 'Hab.');
        $sheet->setCellValue('h1', 'Huésp.');
        $sheet->setCellValue('i1', 'Noches');
        $sheet->setCellValue('j1', 'Fecha Pago');
        $sheet->setCellValue('k1', 'A Pagar');
        $sheet->setCellValue('l1', 'Cliente');
        $sheet->setCellValue('m1', 'País');


        $sheet = $this->styleHeader("a1:m1", $sheet);

        $sheet->fromArray($data, ' ', 'A2');

        for($i = 0; $i < count($data); $i++)
        {
            if($data[$i][0] != "")
            {
                $style = array(
                    'font' => array(
                        'color' => array('rgb' => '000000'),
                    ),
                    'fill' => array(
                        'type' => \PHPExcel_Style_Fill::FILL_SOLID,
                        'color' => array('rgb' => 'd6e9c6')
                ));
                $sheet->getStyle("A".($i + 2).":M".($i + 2))->applyFromArray($style);
            }
        }

        $sheet->setAutoFilter($sheet->calculateWorksheetDimension());
        $this->setColumnAutoSize("a", "m", $sheet);
        return $excel;
    }

    private function dataForCheckin($date, $sort_by) {
        $results = array();

        $checkins = $this->em->getRepository("mycpBundle:generalReservation")->getCheckins($date, $sort_by);

        foreach ($checkins as $check) {
            $data = array();

            if($check["notification"] != null && $check["notification"] != "")
                $data[0] = "SMS Enviado";
            else
                $data[0] = "";

                $data[1] = \MyCp\FrontEndBundle\Helpers\ReservationHelper::getCASId($check["gen_res_id"]);
            $resDate = $check["gen_res_date"];
            $data[2] = $resDate->format("d/m/Y");
            $data[3] = $check["own_mcp_code"];
            $data[4] = $check["own_homeowner_1"];
            if ($check["own_homeowner_2"] != "")
                $data[4] .= " / " . $check["own_homeowner_2"];

            $data[5] = "";
            if ($check["own_phone_number"] != "")
                $data[5] .= "(+53) " . $check["prov_phone_code"] . " " . $check["own_phone_number"];

            if ($check["own_phone_number"] != "" && $check["own_mobile_number"] != "")
                $data[5] .= " / ";

            $data[5] .= $check["own_mobile_number"];

            //Total de habitaciones
            $data[6] = $check["rooms"];

            //Total de huéspedes
            $data[7] = $check["adults"] + $check["children"];

            //Noches
            $data[8] = $check["nights"] / $check["rooms"];

            //Fecha de Pago
            $payDate = new \DateTime($check["payed"]);
            $data[9] = $payDate->format("d/m/Y");

            //Pago en casa
            $payAtService = $check["to_pay_at_service"] - $check["to_pay_at_service"] * $check["own_commission_percent"] / 100;
            $data[10] = number_format((float)$payAtService, 2, '.', '');
            $data[10] .= " CUC";
            //Cliente
            $data[11] = $check["user_user_name"] . " " . $check["user_last_name"];
            $data[12] = $check["co_name"];

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
            $data[11] = ($own["rr"] == 1 ? "Reserva Rápida" : ($own["ri"] == 1 ? "Reserva Inmediata": "Solicitud de Disponibilidad"));

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
        $sheet->setCellValue('l1', 'Modalidad');


        $sheet = $this->styleHeader("a1:l1", $sheet);

        $sheet->fromArray($data, ' ', 'A2');

        $this->setColumnAutoSize("a", "l", $sheet);

        for($i = 0; $i < count($data); $i++)
        {
            if($data[$i][9] != "Activo")
            {
                $style = array(
                    'font' => array(
                        'color' => array('rgb' => 'FF0000'),
                    ),
                );
                $sheet->getStyle("A".($i + 2).":L".($i + 2))->applyFromArray($style);
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

    public function exportReservationsAg($reservations, $startingDate, $fileName = "reservaciones") {
        if(count($reservations) > 0) {
            $excel = $this->configExcel("Listado de reservaciones", "Listado de reservaciones agencia de MyCasaParticular", "reservaciones");

            $data = $this->dataForReservationsAg($excel, $reservations);

            if (count($data) > 0)
                $excel = $this->createSheetForReservationsAg($excel, "Reservaciones", $data, $startingDate);

            $fileName = $this->getFileName($fileName);
            $this->save($excel, $fileName);

            return $this->export($fileName);
        }
    }
    private function dataForReservationsAg($excel,$reservations) {
        $results = array();
        $currentReservation = "";

        foreach ($reservations as $reservation) {
            $data = array();

            $generalReservation = $reservation->getOwnResGenResId();

            if($currentReservation != $generalReservation->getGenResId()){
                //Fecha Reserva
                $data[0] = ($currentReservation != $generalReservation->getGenResId()) ? $generalReservation->getGenResDate()->format("d/m/Y"): "";
                //Código Reserva
                $data[1] = ($currentReservation != $generalReservation->getGenResId()) ? $generalReservation->getCASId(): "";
                //Estado Reserva
                $data[2] = ($currentReservation != $generalReservation->getGenResId()) ? generalReservation::getStatusName($generalReservation->getGenResStatus()) : "";
                //Precio Total
                $data[3] = ($currentReservation != $generalReservation->getGenResId()) ? $generalReservation->getGenResTotalInSite(). " CUC": "";
                //Cliente
                $data[4] = ($currentReservation != $generalReservation->getGenResId()) ? $generalReservation->getGenResUserId()->getUserLastName(): "";

                $data[5] = ($currentReservation != $generalReservation->getGenResId()) ? $generalReservation->getTravelAgencyDetailReservations()->first()->getReservation()->getClient()->getFullName(): "";

                $accommodation = $generalReservation->getGenResOwnId();

                //Código alojamiento
                $data[6] = ($currentReservation != $generalReservation->getGenResId()) ? $accommodation->getOwnMcpCode(): "";
                //Nombre alojamiento
                $data[7] = ($currentReservation != $generalReservation->getGenResId()) ? $accommodation->getOwnName(): "";
                //Propietarios
                $homeOwners = $accommodation->getOwnHomeowner1().(($accommodation->getOwnHomeowner2() != "")? " / ". $accommodation->getOwnHomeowner2() : "");
                $data[8] = ($currentReservation != $generalReservation->getGenResId()) ? $homeOwners: "";
                //Telefono
                $phone = ($accommodation->getOwnPhoneNumber() != "")?"(+53) ".$accommodation->getOwnAddressProvince()->getProvPhoneCode(). " ".$accommodation->getOwnPhoneNumber() : "";
                $data[9] = ($currentReservation != $generalReservation->getGenResId()) ? $phone: "";
                //Movil
                $data[10] = ($currentReservation != $generalReservation->getGenResId()) ? $accommodation->getOwnMobileNumber(): "";
                //Comisión MyCP
                $data[11] = ($currentReservation != $generalReservation->getGenResId()) ? $accommodation->getOwnCommissionPercent()."%": "";

                //Tipo de habitación
                $data[12] = $reservation->getOwnResRoomType();
                //Adultos
                $data[13] = $reservation->getOwnResCountAdults();
                //Niños
                $data[14] = $reservation->getOwnResCountChildrens();
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
                $data[15] = $roomPrice." CUC";

                //Fecha de llegada
                $data[16] = $reservation->getOwnResReservationFromDate()->format("d/m/Y");
                //Noches
                $data[17] = Time::nights($reservation->getOwnResReservationFromDate()->format("Y-m-d"), $reservation->getOwnResReservationToDate()->format("Y-m-d"), "Y-m-d");

                array_push($results, $data);

                if($currentReservation != $reservation->getOwnResGenResId()->getGenResId())
                    $currentReservation = $reservation->getOwnResGenResId()->getGenResId();
            }
            else{

            }
        }

        return $results;
    }
    private function createSheetForReservationsAg($excel, $sheetName, $data, $startingDate) {
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
        $sheet->setCellValue('e5', 'Agencia');
        $sheet->setCellValue('f5', 'Cliente');
        $sheet->setCellValue('g5', 'Código Alojamiento');
        $sheet->setCellValue('h5', 'Nombre Alojamiento');
        $sheet->setCellValue('i5', 'Propietario(s)');
        $sheet->setCellValue('j5', 'Teléfono)');
        $sheet->setCellValue('k5', 'Móvil');
        $sheet->setCellValue('l5', 'Comisión MyCP');
        $sheet->setCellValue('m5', 'Tipo Habitación');
        $sheet->setCellValue('n5', 'Adultos');
        $sheet->setCellValue('o5', 'Niños');
        $sheet->setCellValue('p5', 'Precio');
        $sheet->setCellValue('q5', 'Fecha Llegada');
        $sheet->setCellValue('r5', 'Noches');

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

    public function exportReservationsCheckinAg($reservations, $startingDate, $curr, $fileName = "reservaciones") {
        $translator = $this->get('translator');
        $title = $translator->trans("dashboard.booking.checkin", array(), "messages");
        $description = "Listado de reservaciones checkin agencia de MyCasaParticular";
        if(count($reservations) > 0) {
            $excel = $this->configExcel($title, $description, "reservaciones");

            $data = $this->dataForReservationsCheckinAg($reservations, $curr);

            if (count($data) > 0){
                $excel = $this->createSheetForReservationsCheckinAg($excel, "Reservaciones", $data, $startingDate);
            }

            $fileName = $this->getFileName($fileName);
            $this->save($excel, $fileName);

            return $this->export($fileName);
        }
    }
    private function dataForReservationsCheckinAg($reservations, $curr) {
        $timeService = $this->get('time');
        $results = array();

        foreach ($reservations as $reservation) {
            $data = array();

            /****************************************/
            $totalPrice = 0;
            $totalPriceInHome = 0;
            $totalPriceInHomeX = 0;
            $adults = 0;
            $childrens = 0;

            $ownReservations = $reservation->getOwn_reservations();
            if (!$ownReservations->isEmpty()) {
                $ownReservation = $ownReservations->first();
                do {
                    $totalPrice += $ownReservation->getPriceTotal($timeService) * $curr['change'];
                    $totalPriceInHome += $ownReservation->getPricePerInHome($timeService) * $curr['change'];
                    $totalPriceInHomeX += $ownReservation->getPricePerInHome($timeService);
                    $adults += $ownReservation->getOwnResCountAdults();
                    $childrens += $ownReservation->getOwnResCountChildrens();
                    $ownReservation = $ownReservations->next();
                } while ($ownReservation);
            }

            $totalPrice .= $curr['code'];
            $totalPriceInHome .= $curr['code'];
            $totalPriceInHomeX .= "CUC";
            /****************************************/

            //Código Casa
            $data[0] = $reservation->getGenResOwnId()->getOwnMcpCode();
            //Nombre Casa
            $data[1] = $reservation->getGenResOwnId()->getOwnName();
            //Destino
            $data[2] = $reservation->getGenResOwnId()->getOwnDestination()->getDesName();
            //Fecha llegada
            $data[3] = $reservation->getGenResFromDate()->format('d-m-Y')." ".$reservation->getGenResArrivalHour();
            //Fecha salida
            $data[4] = $reservation->getGenResToDate()->format('d-m-Y');
            //Cliente
            $data[5] = $reservation->getTravelAgencyDetailReservations()->first()->getReservation()->getClient()->getFullName();
            //Habitaciones
            $data[6] = $ownReservations->count();
            //Adultos
            $data[7] = $adults;
            //Niños
            $data[8] = $childrens;
            //Pago en Casa
            $data[9] = $totalPriceInHomeX."($totalPriceInHome)";

            array_push($results, $data);
        }

        return $results;
    }
    private function createSheetForReservationsCheckinAg($excel, $sheetName, $data, $startingDate) {
        $translator = $this->get('translator');

        $sheet = $this->createSheet($excel, $sheetName);

        $sheet->setCellValue('a1', $translator->trans("dashboard.booking.checkin", array(), "messages"));
        $sheet->mergeCells("A1:J1");
        $now = new \DateTime();
        //$sheet->setCellValue('a2', 'Reporte generado con las reservas creadas a partir del: '.$startingDate->format('d/m/Y'));
        //$sheet->mergeCells("A2:L2");
        $sheet->setCellValue('a3', 'Fecha de creación: '.$now->format('d/m/Y H:i'));
        $sheet->mergeCells("A3:J3");

        $sheet->setCellValue('a5', 'Código Casa');
        $sheet->setCellValue('b5', 'Nombre Casa');
        $sheet->setCellValue('c5', 'Destino');
        $sheet->setCellValue('d5', 'Fecha llegada');
        $sheet->setCellValue('e5', 'Fecha salida');
        $sheet->setCellValue('f5', 'Cliente');
        $sheet->setCellValue('g5', 'Habitaciones');
        $sheet->setCellValue('h5', 'Adultos');
        $sheet->setCellValue('i5', 'Niños)');
        $sheet->setCellValue('j5', 'Pago en Casa');

        $centerStyle = array(
            'alignment' => array(
                'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            )
        );
        $sheet->getStyle("A1:J1")->applyFromArray($centerStyle);

        $sheet = $this->styleHeader("A5:J5", $sheet);

        $style = array(
            'font' => array(
                'bold' => true,
                'size' => 14
            ),
        );
        $sheet->getStyle("a1")->applyFromArray($style);

        $sheet->fromArray($data, ' ', 'A6');

        $this->setColumnAutoSize("a", "j", $sheet);

        //$sheet->setAutoFilter($sheet->calculateWorksheetDimension());
        $sheet->setAutoFilter("A5:J".(count($data)+5));

        return $excel;
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

    /**
     * @param $items
     * @param $startingDate
     * @param string $fileName
     * @return Response
     */
    public function exportPendingOwn($items, $startingDate, $fileName = "reservaciones") {
        if(count($items) > 0) {
            $excel = $this->configExcel("Listado de pagos pendientes a propietarios", "Listado de pagos pendientes a propietarios", "pagos");

            $data = $this->dataForPendingOwn($excel, $items);

            if (count($data) > 0)
                $excel = $this->createSheetForPendingOwn($excel, "Pagos", $data, $startingDate);

            $fileName = $this->getFileName($fileName);
            $this->save($excel, $fileName);

            return $this->export($fileName);
        }
    }

    /**
     * @param $items
     * @param $startingDate
     * @param string $fileName
     * @return Response
     */
    public function exportPendingAccommodationAgencyPayment($items, $startingDate, $fileName = "pagos") {
        if(count($items) > 0) {
            $excel = $this->configExcel("Listado de pagos pendientes a propietarios por reservas de agencias", "Listado de pagos pendientes a propietarios por reservas de agencias", "pagos");

            $data = $this->dataForPendingAccommodationAgencyPayment($excel, $items);

            if (count($data) > 0)
                $excel = $this->createSheetForPendingAccommodationAgencyPayment($excel, "Pagos", $data, $startingDate);

            $fileName = $this->getFileName($fileName);
            $this->save($excel, $fileName);

            return $this->export($fileName);
        }
    }

    /**
     * @param $excel
     * @param $items
     * @return array
     */
    private function dataForPendingAccommodationAgencyPayment($excel,$items) {
        $results = array();
        $timer = $this->get("Time");

        foreach ($items as $item) {
            $data = array();

            //Alojamiento
            $accommodation = $item->getAccommodation();

            //Código de la Casa
            $data[] = $accommodation->getOwnMcpCode();
            //Nombre de la casa
            $data[] = $accommodation->getOwnName();
            //Destino
            $data[] = $accommodation->getOwnDestination()->getDesName();

            //Dirección
            $data[] = "Calle ". $accommodation->getOwnAddressStreet()." No.".$accommodation->getOwnAddressNumber().(($accommodation->getOwnAddressBetweenStreet1() != "" && $accommodation->getOwnAddressBetweenStreet2() != "") ? " entre ".$accommodation->getOwnAddressBetweenStreet1()." y ".$accommodation->getOwnAddressBetweenStreet2() : "");

            //Propietario
            $data[] = $accommodation->getOwnHomeowner1().(($accommodation->getOwnHomeowner2() != "")? " / ". $accommodation->getOwnHomeowner2() : "");

            //Teléfonos
            $data[] = (($accommodation->getOwnPhoneNumber() != "")?"(+53) ".$accommodation->getOwnPhoneCode(). " ".$accommodation->getOwnPhoneNumber() : "").(($accommodation->getOwnMobileNumber() != "" && $accommodation->getOwnPhoneNumber() != "") ? " / ": "").(($accommodation->getOwnMobileNumber() != "") ? $accommodation->getOwnMobileNumber(): "");

            //Correos
            $data[] = $accommodation->getOwnEmail1().(($accommodation->getOwnEmail2() != "")? " / ". $accommodation->getOwnEmail2() : "");

            //Reserva
            $reservation = $item->getReservation();

            //CAS
            $data[] = $reservation->getGenResId();

            //Llegada
            $data[] = $reservation->getGenResFromDate()->format("d/m/Y");

            $nights = 0;
            $roomNumbers = "";
            $roomTypes = "";
            $roomPriceFirstNight = "";

            $reservedRooms = $this->em->getRepository("mycpBundle:ownershipReservation")->findBy(array(
                "own_res_gen_res_id" => $reservation->getGenResId(),
                "own_res_status" => ownershipReservation::STATUS_RESERVED
            ));

            foreach($reservedRooms as $roomReservation)
            {
                $currentNight = $timer->nights($roomReservation->getOwnResReservationFromDate()->getTimestamp(), $roomReservation->getOwnResReservationToDate()->getTimestamp());
                $nights += $currentNight;

                $room = $this->em->getRepository("mycpBundle:room")->find($roomReservation->getOwnResSelectedRoomId());

                $roomNumbers = $roomNumbers . "Habitación No.". $room->getRoomNum() . "\n";
                $roomTypes = $roomTypes . $roomReservation->getOwnResRoomType() . "\n";
                $roomPriceFirstNight = $roomPriceFirstNight . $roomReservation->getOwnResTotalInSite() / $currentNight . " CUC \n";
            }

            //Noches
            $data[] = $nights;

            //Número Habitaciones
            $data[] = trim($roomNumbers, "\n");

            //Tipos Habitaciones
            $data[] = trim($roomTypes, "\n");

            //Precio primera noche
            $data[] = trim($roomPriceFirstNight, "\n");

            //Booking
            $booking = $item->getBooking();

            //Id booking
            $data[] = $booking->getBookingId();

            //Fecha del booking
            $data[] = $item->getCreatedDate()->format("d/m/Y");

            //Nombre agencia
            $data[] = $item->getAgency()->getName();

            $agencyReservation = $this->em->getRepository("PartnerBundle:paReservationDetail")->findOneBy(array("reservationDetail" => $reservation->getGenResId()));

            //Nombre del cliente
            $data[] = $agencyReservation->getReservation()->getClient()->getFullName();

            //Pago
            //Fecha del pago
            $data[] = $item->getPayDate()->format("d/m/Y");

            //Monto a la casa
            $data[] = $item->getAmount()." CUC";

            //Tipo
            $data[] = $item->getType()->getTranslations()[0]->getNomLangDescription();

            //Estado: (Pendiente/En proceso/Pagado
            $data[] = $item->getStatus()->getTranslations()[0]->getNomLangDescription();

            array_push($results, $data);
        }

        return $results;
    }

    /**
     * @param $excel
     * @param $sheetName
     * @param $data
     * @param $startingDate
     * @return mixed
     */
    private function createSheetForPendingAccommodationAgencyPayment($excel, $sheetName, $data, $startingDate) {
        $sheet = $this->createSheet($excel, $sheetName);

        $sheet->setCellValue('a1', "Listado de pagos pendientes a propietarios  por reservas de agencias");
        $sheet->mergeCells("A1:Q1");
        $now = new \DateTime();
        $sheet->mergeCells("A2:Q2");
        $sheet->setCellValue('a3', 'Fecha de creación: '.$now->format('d/m/Y H:s'));
        $sheet->mergeCells("A3:Q3");

        $sheet->setCellValue('a5', 'Código');
        $sheet->setCellValue('b5', 'Nombre alojamiento');
        $sheet->setCellValue('c5', 'Destino');
        $sheet->setCellValue('d5', 'Dirección');
        $sheet->setCellValue('e5', 'Propietarios');
        $sheet->setCellValue('f5', 'Teléfonos');
        $sheet->setCellValue('g5', 'Correos');
        $sheet->setCellValue('h5', 'CAS');
        $sheet->setCellValue('i5', 'Fecha llegada');
        $sheet->setCellValue('j5', 'Noches');
        $sheet->setCellValue('k5', 'Número habitación');
        $sheet->setCellValue('l5', 'Tipo habitación');
        $sheet->setCellValue('m5', 'Precio/noche');
        $sheet->setCellValue('n5', 'Id booking');
        $sheet->setCellValue('o5', 'Fecha booking');
        $sheet->setCellValue('p5', 'Agencia');
        $sheet->setCellValue('q5', 'Cliente');
        $sheet->setCellValue('r5', 'A pagar');
        $sheet->setCellValue('s5', 'Monto a pagar');
        $sheet->setCellValue('t5', 'Tipo');
        $sheet->setCellValue('u5', 'Estado');

        $centerStyle = array(
            'alignment' => array(
                'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            )
        );
        $sheet->getStyle("A1:U1")->applyFromArray($centerStyle);

        $sheet = $this->styleHeader("A5:U5", $sheet);

        $style = array(
            'font' => array(
                'bold' => true,
                'size' => 14
            ),
        );
        $sheet->getStyle("a1")->applyFromArray($style);

        $sheet->fromArray($data, ' ', 'A6');

        $this->setColumnAutoSize("a", "u", $sheet);

        $sheet->setAutoFilter("A5:U".(count($data)+5));
        $sheet->getStyle("A5:U".(count($data)+5))
              ->getAlignment()
              ->setWrapText(true);

        return $excel;
    }

    /**
     * @param $items
     * @param $startingDate
     * @param string $fileName
     * @return Response
     */
    public function exportPendingTourist($items, $startingDate, $fileName = "reservaciones") {
        if(count($items) > 0) {
            $excel = $this->configExcel("Listado de pagos pendientes a turstas", "Listado de pagos pendientes a turistas", "pagos");

            $data = $this->dataForPendingTourist($excel, $items);

            if (count($data) > 0)
                $excel = $this->createSheetForPendingTourist($excel, "Pagos", $data, $startingDate);

            $fileName = $this->getFileName($fileName);
            $this->save($excel, $fileName);

            return $this->export($fileName);
        }
    }

    /**
     * @param $items
     * @param $startingDate
     * @param string $fileName
     * @return Response
     */
    public function exportCancelPayment($items, $startingDate, $fromPartner = false, $fileName = "cancelaciones") {
        if($fromPartner)
            $fileName = "cancelacionesAgencia";

        if(count($items) > 0) {
            $excel = $this->configExcel("Listado de cancelaciones", "Listado de cancelaciones", "pagos");

            if($fromPartner)
                $data = $this->dataForCancelPaymentPartner($excel, $items);
            else
                $data = $this->dataForCancelPayment($excel, $items);

            if (count($data) > 0)
            {
                $excel = $this->createSheetForCancelPayment($excel, "Pagos", $data, $startingDate);
            }


            $fileName = $this->getFileName($fileName);
            $this->save($excel, $fileName);

            return $this->export($fileName);
        }
    }

    /**
     * @param $excel
     * @param $items
     * @return array
     */
    private function dataForCancelPayment($excel,$items) {
        $results = array();
        foreach ($items as $item) {
            $data = array();
            $pay_own = $this->em->getRepository("mycpBundle:pendingPayown")->findBy(array("cancel_id" => $item->getCancelId()));
            $pay_tourist = $this->em->getRepository("mycpBundle:pendingPaytourist")->findBy(array("cancel_id" => $item->getCancelId()));
            $dest='';
            $pay=0;

            if(count($pay_own)){
                $dest='Código de Casa:'.$pay_own[0]->getUserCasa()->getOwnMcpCode().'- Nombre de la casa:'.$pay_own[0]->getUserCasa()->getOwnName();
                $pay=$pay_own[0]->getPayAmount().' CUC';

            }
            else if(count($pay_tourist)){
                $dest='Nombre Cliente (Turista) :'.' '.$pay_tourist[0]->getUserTourist()->getUserTouristUser()->getUserCompleteName().' -Correo: '.$pay_tourist[0]->getUserTourist()->getUserTouristUser()->getUserEmail();
                $pay=$pay_tourist[0]->getPayAmount().' '.$pay_tourist[0]->getCancelId()->getBooking()->getBookingCurrency()->getCurrSymbol();

            }

            //Número
            $data[] = $item->getCancelId();
            //Fecha
            $data[] = ($item->getCancelDate()!='')?$item->getCancelDate()->format("d/m/Y"):'';
            //Tipo de Cancelación (De Propietario /De Turista)
            $data[] = $item->getType()->getCancelName();
            //Id booking
            $data[] = $item->getBooking()->getBookingId();
            //Habitaciones canceladas
            $data[] = count($item->getOwnreservations());
            //Devolver a
            $data[] = $dest;
            //Monto a pagar
            $data[] = $pay;


            array_push($results, $data);
        }
        return $results;
    }

    /**
     * @param $excel
     * @param $items
     * @return array
     */
    private function dataForCancelPaymentPartner($excel,$items) {
        $results = array();
        foreach ($items as $item) {
            $data = array();

            $paAccommodation = $this->em->getRepository("PartnerBundle:paPendingPaymentAccommodation")->findBy(array("cancelPayment" => $item->getId()));
            $payAgency = $this->em->getRepository("PartnerBundle:paPendingPaymentAgency")->findBy(array("cancelPayment" => $item->getId()));

            $dest='';
            $pay=0;

            if(count($paAccommodation)){
                $dest="Alojamiento: ".$paAccommodation[0]->getAccommodation()->getOwnMcpCode()."\n"."Nombre:".$paAccommodation[0]->getAccommodation()->getOwnName();
                $pay=$paAccommodation[0]->getAmount().' CUC';

            }
            else if(count($payAgency)){
                $dest="Agencia : ".$payAgency[0]->getAgency()->getName()."\n"."Correo: ".$payAgency[0]->getAgency()->getUEmail();
                $pay=$payAgency[0]->getAmount().' '.$payAgency[0]->getCancelPayment()->getBooking()->getBookingCurrency()->getCurrSymbol();

            }

            //Número
            $data[] = $item->getId();
            //Fecha
            $data[] = ($item->getCancelDate()!='')?$item->getCancelDate()->format("d/m/Y"):'';
            //Tipo de Cancelación
            $data[] = $item->getType()->getTranslations()[0]->getNomLangDescription();
            //Id booking
            $data[] = $item->getBooking()->getBookingId();
            //Habitaciones canceladas
            $data[] = count($item->getOwnreservations());
            //Devolver a
            $data[] = $dest;
            //Monto a pagar
            $data[] = $pay;


            array_push($results, $data);
        }
        return $results;
    }

    /**
     * @param $excel
     * @param $sheetName
     * @param $data
     * @param $startingDate
     * @return mixed
     */
    private function createSheetForCancelPayment($excel, $sheetName, $data, $startingDate) {
        $sheet = $this->createSheet($excel, $sheetName);

        $sheet->setCellValue('a1', "Listado de cancelaciones");
        $sheet->mergeCells("A1:G1");
        $now = new \DateTime();
        $sheet->mergeCells("A2:G2");
        $sheet->setCellValue('a3', 'Fecha de creación: '.$now->format('d/m/Y H:s'));
        $sheet->mergeCells("A3:G3");

        $sheet->setCellValue('a5', 'Número');
        $sheet->setCellValue('b5', 'Fecha de Registro');
        $sheet->setCellValue('c5', 'Tipo de Cancelación');
        $sheet->setCellValue('d5', 'Id Booking');
        $sheet->setCellValue('e5', 'Habitaciones canceladas');
        $sheet->setCellValue('f5', 'Devolver A');
        $sheet->setCellValue('g5', 'Monto a devolver');
        $centerStyle = array(
            'alignment' => array(
                'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            )
        );
        $sheet->getStyle("A1:G1")->applyFromArray($centerStyle);

        $sheet = $this->styleHeader("A5:G5", $sheet);

        $style = array(
            'font' => array(
                'bold' => true,
                'size' => 14
            ),
        );
        $sheet->getStyle("a1")->applyFromArray($style);

        $sheet->fromArray($data, ' ', 'A6');

        $this->setColumnAutoSize("a", "g", $sheet);

        $sheet->setAutoFilter("A5:G".(count($data)+5));
        $sheet->getStyle("A5:G".(count($data)+5))
            ->getAlignment()
            ->setWrapText(true);

        return $excel;
    }



    /**
     * @param $excel
     * @param $items
     * @return array
     */
    private function dataForPendingTourist($excel,$items) {
        $results = array();
        foreach ($items as $item) {
            $data = array();
            //Fecha
            $data[] = ($item->getPaymentDate()!='')?$item->getPaymentDate()->format("d/m/Y"):'';
            //Tipo de Cancelación (De Propietario /De Turista)
            $data[] = $item->getCancelId()->getType()->getCancelName();
            //Nombre del cliente
            $data[] = $item->getUserTourist()->getUserTouristUser()->getUserCompleteName();
            //Correo del cliente
            $data[] = $item->getUserTourist()->getUserTouristUser()->getUserEmail();
            //Monto a pagar
            $data[] = $item->getPayAmount().' '.$item->getCancelId()->getBooking()->getBookingCurrency()->getCurrSymbol();
            //Id Booking
            $data[] = $item->getCancelId()->getBooking()->getBookingId();
            //Fecha de registro
            $data[] = $item->getRegisterDate()->format("d/m/Y");
            //Estado: (Pendiente/En proceso/Pagado
            $data[] = $item->getType()->getTranslations()[0]->getNomLangDescription();

            array_push($results, $data);
        }
        return $results;
    }

    /**
     * @param $excel
     * @param $sheetName
     * @param $data
     * @param $startingDate
     * @return mixed
     */
    private function createSheetForPendingTourist($excel, $sheetName, $data, $startingDate) {
        $sheet = $this->createSheet($excel, $sheetName);

        $sheet->setCellValue('a1', "Listado de pagos pendientes a turistas");
        $sheet->mergeCells("A1:Q1");
        $now = new \DateTime();
        $sheet->mergeCells("A2:Q2");
        $sheet->setCellValue('a3', 'Fecha de creación: '.$now->format('d/m/Y H:s'));
        $sheet->mergeCells("A3:Q3");

        $sheet->setCellValue('a5', 'Fecha de pago');
        $sheet->setCellValue('b5', 'Tipo de Cancelación (De Propietario /De Turista)');
        $sheet->setCellValue('c5', 'Nombre del cliente');

        $sheet->setCellValue('d5', 'Correo del cliente');
        $sheet->setCellValue('e5', 'Monto a pagar');
        $sheet->setCellValue('f5', 'Id Booking');
        $sheet->setCellValue('g5', 'Fecha de Registro');
        $sheet->setCellValue('h5', 'Estado: (Pendiente/En proceso/Pagado)');
        $centerStyle = array(
            'alignment' => array(
                'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            )
        );
        $sheet->getStyle("A1:H1")->applyFromArray($centerStyle);

        $sheet = $this->styleHeader("A5:H5", $sheet);

        $style = array(
            'font' => array(
                'bold' => true,
                'size' => 14
            ),
        );
        $sheet->getStyle("a1")->applyFromArray($style);

        $sheet->fromArray($data, ' ', 'A6');

        $this->setColumnAutoSize("a", "h", $sheet);

        $sheet->setAutoFilter("A5:H".(count($data)+5));

        return $excel;
    }


    /**
     * @param $excel
     * @param $items
     * @return array
     */
    private function dataForPendingOwn($excel,$items) {
        $results = array();
        foreach ($items as $item) {
            $data = array();
            //Fecha de pago
            $data[] = $item->getPaymentDate()->format("d/m/Y");
            //Monto a la casa
            $data[] = $item->getPayAmount()." CUC";
            //Código de la Casa
            $data[] = $item->getUserCasa()->getOwnMcpCode();
            //Nombre de la casa
            $data[] = $item->getUserCasa()->getOwnName();
            //Destino
            $data[] = $item->getUserCasa()->getOwnDestination()->getDesName();
            //Id Booking
            $data[] = $item->getCancelId()->getBooking()->getBookingId();
            //Fecha de Registro
            $data[] = $item->getRegisterDate()->format("d/m/Y");
            //Estado: (Pendiente/En proceso/Pagado
            $data[] = $item->getType()->getTranslations()[0]->getNomLangDescription();

            array_push($results, $data);
        }
        return $results;
    }

    /**
     * @param $excel
     * @param $sheetName
     * @param $data
     * @param $startingDate
     * @return mixed
     */
    private function createSheetForPendingOwn($excel, $sheetName, $data, $startingDate) {
        $sheet = $this->createSheet($excel, $sheetName);

        $sheet->setCellValue('a1', "Listado de pagos pendientes a propietarios");
        $sheet->mergeCells("A1:Q1");
        $now = new \DateTime();
        $sheet->mergeCells("A2:Q2");
        $sheet->setCellValue('a3', 'Fecha de creación: '.$now->format('d/m/Y H:s'));
        $sheet->mergeCells("A3:Q3");

        $sheet->setCellValue('a5', 'Fecha de pago');
        $sheet->setCellValue('b5', 'Monto a pagar a la casa');
        $sheet->setCellValue('c5', 'Código de la Casa');
        $sheet->setCellValue('d5', 'Nombre de la casa');
        $sheet->setCellValue('e5', 'Destino');
        $sheet->setCellValue('f5', 'Id Booking)');
        $sheet->setCellValue('g5', 'Fecha de Registro');
        $sheet->setCellValue('h5', 'Estado: (Pendiente/En proceso/Pagado)');

        $centerStyle = array(
            'alignment' => array(
                'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            )
        );
        $sheet->getStyle("A1:H1")->applyFromArray($centerStyle);

        $sheet = $this->styleHeader("A5:H5", $sheet);

        $style = array(
            'font' => array(
                'bold' => true,
                'size' => 14
            ),
        );
        $sheet->getStyle("a1")->applyFromArray($style);

        $sheet->fromArray($data, ' ', 'A6');

        $this->setColumnAutoSize("a", "h", $sheet);

        $sheet->setAutoFilter("A5:H".(count($data)+5));

        return $excel;
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
    public function exportReservationsStatement(Request $request) {
        $excel = $this->configExcel("Parte de últimas reservaciones recibidas", "Parte de últimas reservaciones recibidas MyCasaParticular", "reportes");

        $sheetName = "General";
        $now=new \DateTime();
        $fileName='Reservaciones_'.$now->format('dmY_Hm');
        $data = $this->dataForReservationsStatement();

        if (count($data) > 0)
            $excel = $this->createSheetForReservationsStatement($excel, $sheetName, $data);

        $fileName = $this->getFileName($fileName);
        $this->save($excel, $fileName);
        return $this->export($fileName);
    }

    public function createExcelReservationsStatement() {
        $excel = $this->configExcel("Parte de últimas reservaciones recibidas", "Parte de últimas reservaciones recibidas MyCasaParticular", "reportes");

        $sheetName = "General";
        $now=new \DateTime();
        $fileName='Reservaciones_'.$now->format('dmY_Hm');
        $data = $this->dataForReservationsStatement();

        if (count($data) > 0)
            $excel = $this->createSheetForReservationsStatement($excel, $sheetName, $data);

        $fileName = $this->getFileName($fileName);
        $this->save($excel, $fileName);
        return $this->excelDirectoryPath.$fileName;
    }

    private function createSheetForReservationsStatement($excel, $sheetName, $data){
        $sheet = $this->createSheet($excel, $sheetName);
        $sheet->setCellValue('a1', "Reporte de últimas reservas recibidas");
        $sheet->setCellValue('a2', 'Rango: ');
        $now = new \DateTime();
        $sheet->setCellValue('b2', 'Generado: '.$now->format('d/m/Y H:s'));

        $sheet->setCellValue('a4', 'Fecha');
        $sheet->setCellValue('b4', 'Id reserva');
        $sheet->setCellValue('c4', 'Estado');
        $sheet->setCellValue('d4', 'Precio');
        $sheet->setCellValue('e4', 'Cod Casa');
        $sheet->setCellValue('f4', 'Cliente');
        $sheet->setCellValue('g4', 'Email');
        $sheet->setCellValue('h4', 'Nombre Casa');
        $sheet->setCellValue('i4', 'Dueños');
        $sheet->setCellValue('j4', 'Teléfono');
        $sheet->setCellValue('k4', 'Celular');
        $sheet->setCellValue('l4', 'Comisión MyCP(%)');
        $sheet->setCellValue('m4', 'Tipo de habitación');
        $sheet->setCellValue('n4', 'Adultos');
        $sheet->setCellValue('o4', 'Niños');
        $sheet->setCellValue('p4', 'Precio baja');
        $sheet->setCellValue('q4', 'Precio alta');
        $sheet->setCellValue('r4', 'Precio especial');
        $sheet->setCellValue('s4', 'Fecha llegada');
        $sheet->setCellValue('t4', 'Noches');
        $sheet->setCellValue('u4', 'Destino');

        $sheet = $this->styleHeader("a4:u4", $sheet);
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

        $this->setColumnAutoSize("a", "u", $sheet);

        return $excel;
    }

    private function dataForReservationsStatement(){
        $results = array();
        $lastId=$this->em->getRepository('mycpBundle:generalReservation')->findOneBy(array('gen_res_last_in_report'=>1));
        if(!$lastId){
            $yesterday=new \DateTime('yesterday');
            $yesterday->sub(new \DateInterval('P30D'));
//            die(dump($yesterday));
        $lastId=$this->em->getRepository('mycpBundle:generalReservation')->findOneBy(array('gen_res_date'=>$yesterday));
        }
        $id=$lastId->getGenResId();
        $query="SELECT generalreservation.gen_res_date, generalreservation.gen_res_id, generalreservation.gen_res_status,generalreservation.gen_res_total_in_site,
      ownership.own_mcp_code, user.user_user_name, user.user_last_name, user.user_email, ownership.own_name, ownership.own_homeowner_1, ownership.own_homeowner_2,
      ownership.own_phone_number, ownership.own_mobile_number, ownership.own_commission_percent, ownershipreservation.own_res_room_type, ownershipreservation.own_res_count_adults, ownershipreservation.own_res_count_childrens, ownershipreservation.own_res_room_price_down, ownershipreservation.own_res_room_price_up, ownershipreservation.own_res_room_price_special,
      generalreservation.gen_res_from_date,DATEDIFF(generalreservation.gen_res_to_date, generalreservation.gen_res_from_date) as total_nigths,
            destination.des_name
FROM ownershipreservation INNER JOIN generalreservation ON ownershipreservation.own_res_gen_res_id = generalreservation.gen_res_id INNER JOIN ownership ON generalreservation.gen_res_own_id = ownership.own_id INNER JOIN destination ON ownership.own_destination = destination.des_id
INNER JOIN user ON generalreservation.gen_res_user_id = user.user_id
WHERE gen_res_id>$id
ORDER BY gen_res_date ASC, user_user_name ASC, user_last_name ASC
;";
      $stmt = $this->em->getConnection()->prepare($query);
      $stmt->execute();
      $data=$stmt->fetchAll();
      foreach($data as $item){
        if($item['gen_res_id']>$id)
        $id=$item['gen_res_id'];
        $temp=array();
          $temp[0]=$item['gen_res_date'];
          $temp[1]='CAS.'.$item['gen_res_id'];
          $estado='';
          switch($item["gen_res_status"]){
              case generalReservation::STATUS_AVAILABLE: $estado='Disponible';
                  break;
              case generalReservation::STATUS_CANCELLED: $estado='Cancelada';
                  break;
              case generalReservation::STATUS_PARTIAL_CANCELLED: $estado='Parcialmente Cancelada';
                  break;
              case generalReservation::STATUS_NOT_AVAILABLE: $estado='No disponible';
                  break;
              case generalReservation::STATUS_PENDING: $estado='Pendiente';
                  break;
              case generalReservation::STATUS_RESERVED: $estado='Reservada';
                  break;
              case generalReservation::STATUS_PARTIAL_RESERVED: $estado='Parcialmente Reservada';
                  break;
              case generalReservation::STATUS_PARTIAL_RESERVED: $estado='Parcialmente Reservada';
                  break;
              case generalReservation::STATUS_OUTDATED: $estado='Vencida';
                  break;
          }
          $temp[2]=$estado;
          $temp[3]=$item['gen_res_total_in_site'];
          $temp[4]=$item['own_mcp_code'];
          $temp[5]=$item['user_user_name']. ' '. $item['user_last_name'];
          $temp[6]=$item['user_email'];
          $temp[7]=$item['own_name'];
          $temp[8]=$item['own_homeowner_1'].' '.$item['own_homeowner_2'];
          $temp[9]=$item['own_phone_number'];
          $temp[10]=$item['own_mobile_number'];
          $temp[11]=$item['own_commission_percent'];
          $temp[12]=$item['own_res_room_type'];
          $temp[13]=$item['own_res_count_adults'];
          $temp[14]=$item['own_res_count_childrens'];
          $temp[15]=$item['own_res_room_price_down'];
          $temp[16]=$item['own_res_room_price_up'];
          $temp[17]=$item['own_res_room_price_special'];
          $temp[18]=$item['gen_res_from_date'];
          $temp[19]=$item['total_nigths'];
          $temp[20]=$item['des_name'];
          $results[]=$temp;
      }
      if($lastId->getGenResLastInReport())
      {
          $lastId->setGenResLastInReport(null);
          $this->em->persist($lastId);
      }

        $newLast=$this->em->getRepository('mycpBundle:generalReservation')->find($id);
        $newLast->setGenResLastInReport(1);
        $this->em->persist($newLast);
        $this->em->flush();
        return $results;


    }

    public function exportUsersReservations($idCliente, $fileName = "reservasCliente") {
        $excel = $this->configExcel("Reporte de reservas de un cliente", "Reporte de clientes en un dia de MyCasaParticular", "reportes");

        $data = $this->dataUsersReservations($idCliente);

        if (count($data) > 0)
            $excel = $this->createSheetUsersReservations($excel,"Cliente-Reservas", $idCliente, $data);

        $fileName = $this->getFileName($fileName."_".$idCliente);
        $this->save($excel, $fileName);
        return $this->export($fileName);
    }

    private function dataUsersReservations($idCliente) {
        $results = array();

        $reportContent = $this->em->getRepository('mycpBundle:generalReservation')->getReservationsRoomsByUser($idCliente);

        $index = 1;
        $currentReservation = 0;
        foreach ($reportContent as $content) {
            $data = array();

            if ($currentReservation != $content["gen_res_id"]) {
                $data[0] = $index++;
                $date = $content["gen_res_date"];
                $data[1] = date('d/m/Y', $date->getTimestamp());
                $data[2] = $content["gen_res_id"];
                $data[3] = ownershipReservation::getStatusShortName($content["own_res_status"]);
                $data[4] = $content["own_mcp_code"].(($content["own_inmediate_booking"]) ? " (RR)": "");
                $data[5] = $content["own_name"];
                $data[6] = $content["own_homeowner_1"].(($content["own_homeowner_2"] != "") ? " / ".$content["own_homeowner_2"]: "");
                $phone = ($content["own_phone_number"] != "") ? $content["prov_phone_code"]." ".$content["own_phone_number"] : "";
                $data[7] = $phone.($content["own_mobile_number"] != "" ? " / ".$content["own_mobile_number"] : "");
                $data[8] = $content["own_commission_percent"]." %";

            } else {
                $data[0] = "";
                $data[1] = "";
                $data[2] = "";
                $data[3] = "";
                $data[4] = "";
                $data[5] = "";
                $data[6] = "";
                $data[7] = "";
                $data[8] = "";
            }

            $data[9] = room::getShortRoomType($content["own_res_room_type"]);
            $data[10] = $content["own_res_count_adults"];
            $data[11] = $content["own_res_count_childrens"];
            $data[12] = $content["own_res_total_in_site"] / $content["nights"];
            $date = $content["own_res_reservation_from_date"];
            $data[13] = date('d/m/Y', $date->getTimestamp());
            $data[14] = $content["nights"];
            //

            if ($currentReservation != $content["gen_res_id"]) {
                $currentReservation = $content["gen_res_id"];
                $data[15] = $content["gen_res_total_in_site"];
            }
            else {
                $data[15] = "";
            }
           //
            array_push($results, $data);
        }

        return $results;
    }

    private function createSheetUsersReservations($excel, $sheetName, $idCliente, $data) {

        $client = $this->em->getRepository('mycpBundle:user')->find($idCliente);
        $userTourist = $this->em->getRepository('mycpBundle:userTourist')->findOneBy(array('user_tourist_user' => $idCliente));
        $sheet = $this->createSheet($excel, $sheetName);
        $sheet->setCellValue('a1', "Cliente: ".$client->getUserUserName()." ".$client->getUserLastName());
        $sheet->setCellValue('a2', 'País: '.$client->getUserCountry()->getCoName());
        $sheet->setCellValue('c2', 'Idioma: '.$userTourist->getUserTouristLanguage()->getLangName());
        $sheet->setCellValue('e2', 'Moneda: '.$userTourist->getUserTouristCurrency()->getCurrCode());
        $now = new \DateTime();
        $sheet->setCellValue('a4', 'Generado: '.$now->format('d/m/Y H:s'));

        $sheet->setCellValue('a6', 'No.');
        $sheet->setCellValue('b6', 'Fecha');
        $sheet->setCellValue('c6', 'Reserva');
        $sheet->setCellValue('d6', 'Estado');
        $sheet->setCellValue('e6', 'Alojamiento');
        $sheet->setCellValue('f6', 'Nombre Alojamiento');
        $sheet->setCellValue('g6', 'Propietario (s)');
        $sheet->setCellValue('h6', 'Teléfono(s)');
        $sheet->setCellValue('i6', 'Comisión');
        $sheet->setCellValue('j6', 'Habitación');
        $sheet->setCellValue('k6', 'Adultos');
        $sheet->setCellValue('l6', 'Niños');
        $sheet->setCellValue('m6', 'Precio-Habitación');
        $sheet->setCellValue('n6', 'Llegada');
        $sheet->setCellValue('o6', 'Noches');
        $sheet->setCellValue('p6', 'Precio Total');

        $sheet = $this->styleHeader("a6:p6", $sheet);
        $style = array(
            'font' => array(
                'bold' => true,
                'size' => 14
            ),
        );
        $sheet->getStyle("a1")->applyFromArray($style);
        $sheet->mergeCells("A1:p1");
        $sheet->mergeCells("A4:p4");

        $centerStyle = array(
            'alignment' => array(
                'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            )
        );
        $sheet->getStyle("A1:k1")->applyFromArray($centerStyle);

        $sheet->fromArray($data, ' ', 'A7');
        $this->setColumnAutoSize("a", "p", $sheet);
        /*$sheet->getStyle('j7:j'.(count($data) + 6))->getNumberFormat()->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_DATE_DDMMYYYY);
        $sheet->setAutoFilter("A7:j".(count($data) + 6));*/

        return $excel;
    }

    public function generateClients($clients, $fileName = "reservasClientes") {
        $excel = $this->configExcel("Reporte de reservas de los clientes", "Reporte de reservas de los clientes de MyCasaParticular", "reportes");

        $data = $this->dataClientsReservations($clients);

        if (count($data) > 0)
            $excel = $this->createSheetClientsReservations($excel,"Clientes-Reservas", $data);

        $fileName = $this->getFileName($fileName);
        $this->save($excel, $fileName);
        //return $this->export($fileName);
        return $fileName;
    }

    public function generateClientsAg($clients, $fileName = "reservasClientes") {
        $excel = $this->configExcel("Reporte de reservas de los clientes", "Reporte de reservas de los clientes de MyCasaParticular", "reportes");

        $data = $this->dataClientsReservationsAg($clients);

        if (count($data) > 0)
            $excel = $this->createSheetClientsReservations($excel,"Clientes-Reservas", $data);

        $fileName = $this->getFileName($fileName);
        $this->save($excel, $fileName);
        //return $this->export($fileName);
        return $fileName;
    }

    public function exportClients($fileName = "reservasClientes") {
        $fileName = $this->getFileName($fileName);
        return $this->export($fileName);
    }

    private function dataClientsReservations($clients) {
        $results = array();

        foreach($clients as $idClient){
            $data = array();

            $client = $this->em->getRepository('mycpBundle:user')->find($idClient);
            $userTourist = $this->em->getRepository('mycpBundle:userTourist')->findOneBy(array('user_tourist_user' => $idClient));

            $data[0] = "Cliente: ".$client->getUserUserName()." ".$client->getUserLastName();
            $data[1] = "";
            $data[2] = 'País: '.$client->getUserCountry()->getCoName();
            $data[3] = "";
            $data[4] = 'Idioma: '.$userTourist->getUserTouristLanguage()->getLangName();
            $data[5] = "";
            $data[6] = 'Moneda: '.$userTourist->getUserTouristCurrency()->getCurrCode();
            $data[7] = "";
            $data[8] = "";
            $data[9] = "";
            $data[10] = "";
            array_push($results, $data);

            $reportContent = $this->em->getRepository('mycpBundle:generalReservation')->getReservationsRoomsByUser($idClient);
            $index = 1;
            $currentReservation = 0;
            foreach ($reportContent as $content) {

                if ($currentReservation != $content["gen_res_id"]) {
                    $data[0] = $index++;
                    $date = $content["gen_res_date"];
                    $data[1] = date('d/m/Y', $date->getTimestamp());
                    $data[2] = $content["gen_res_id"];
                    $data[3] = ownershipReservation::getStatusShortName($content["own_res_status"]);
                    $data[4] = $content["own_mcp_code"].(($content["own_inmediate_booking"]) ? " (RR)": "");
                    $data[5] = $content["own_name"];
                    $data[6] = $content["own_homeowner_1"].(($content["own_homeowner_2"] != "") ? " / ".$content["own_homeowner_2"]: "");
                    $phone = ($content["own_phone_number"] != "") ? $content["prov_phone_code"]." ".$content["own_phone_number"] : "";
                    $data[7] = $phone.($content["own_mobile_number"] != "" ? " / ".$content["own_mobile_number"] : "");
                    $data[8] = $content["own_commission_percent"]." %";

                } else {
                    $data[0] = "";
                    $data[1] = "";
                    $data[2] = "";
                    $data[3] = "";
                    $data[4] = "";
                    $data[5] = "";
                    $data[6] = "";
                    $data[7] = "";
                    $data[8] = "";
                }

                $data[9] = room::getShortRoomType($content["own_res_room_type"]);
                $data[10] = $content["own_res_count_adults"];
                $data[11] = $content["own_res_count_childrens"];
                $data[12] = $content["own_res_total_in_site"] / $content["nights"];
                $date = $content["own_res_reservation_from_date"];
                $data[13] = date('d/m/Y', $date->getTimestamp());
                $data[14] = $content["nights"];
                //

                if ($currentReservation != $content["gen_res_id"]) {
                    $currentReservation = $content["gen_res_id"];
                    $data[15] = $content["gen_res_total_in_site"];
                }
                else {
                    $data[15] = "";
                }

                array_push($results, $data);
            }
        }

        return $results;
    }

    private function dataClientsReservationsAg($clients) {
        $results = array();

        foreach($clients as $idClient){
            $data = array();

            $client_agency = $this->em->getRepository('PartnerBundle:paClient')->find($idClient);
            //$reservations = $this->em->getRepository('mycpBundle:generalReservation')->getByUserAg($idClient);

            $client = $client_agency->getTravelAgency()->getTourOperators()->first()->getTourOperator();//$this->em->getRepository('mycpBundle:user')->find($idClient);
            //$userTourist = $this->em->getRepository('mycpBundle:userTourist')->findOneBy(array('user_tourist_user' => $idClient));

            $data[0] = "Agencia: ".$client->getUserUserName()." ".$client->getUserLastName();
            $data[1] = "Cliente: ".$client_agency->getFullName();
            $data[2] = 'País: '.$client->getUserCountry()->getCoName();
            $data[3] = "";
            $data[4] = 'Idioma: '.$client->getUserLanguage()->getLangName();
            $data[5] = "";
            $data[6] = 'Moneda: '.$client->getUserCurrency()->getCurrCode();
            $data[7] = "";
            $data[8] = "";
            $data[9] = "";
            $data[10] = "";
            array_push($results, $data);

            $reportContent = $this->em->getRepository('mycpBundle:generalReservation')->getReservationsRoomsByClientAg($idClient);
            $index = 1;
            $currentReservation = 0;
            foreach ($reportContent as $content) {
                if ($currentReservation != $content["gen_res_id"]) {
                    $data[0] = $index++;
                    $date = $content["gen_res_date"];
                    $data[1] = date('d/m/Y', $date->getTimestamp());
                    $data[2] = $content["gen_res_id"];
                    $data[3] = ownershipReservation::getStatusShortName($content["own_res_status"]);
                    $data[4] = $content["own_mcp_code"].(($content["own_inmediate_booking"]) ? " (RR)": "");
                    $data[5] = $content["own_name"];
                    $data[6] = $content["own_homeowner_1"].(($content["own_homeowner_2"] != "") ? " / ".$content["own_homeowner_2"]: "");
                    $phone = ($content["own_phone_number"] != "") ? $content["prov_phone_code"]." ".$content["own_phone_number"] : "";
                    $data[7] = $phone.($content["own_mobile_number"] != "" ? " / ".$content["own_mobile_number"] : "");
                    $data[8] = $content["own_commission_percent"]." %";

                } else {
                    $data[0] = "";
                    $data[1] = "";
                    $data[2] = "";
                    $data[3] = "";
                    $data[4] = "";
                    $data[5] = "";
                    $data[6] = "";
                    $data[7] = "";
                    $data[8] = "";
                }

                $data[9] = room::getShortRoomType($content["own_res_room_type"]);
                $data[10] = $content["own_res_count_adults"];
                $data[11] = $content["own_res_count_childrens"];
                $data[12] = $content["own_res_total_in_site"] / $content["nights"];
                $date = $content["own_res_reservation_from_date"];
                $data[13] = date('d/m/Y', $date->getTimestamp());
                $data[14] = $content["nights"];
                //

                if ($currentReservation != $content["gen_res_id"]) {
                    $currentReservation = $content["gen_res_id"];
                    $data[15] = $content["gen_res_total_in_site"];
                }
                else {
                    $data[15] = "";
                }

                array_push($results, $data);
            }
        }

        return $results;
    }

    private function createSheetClientsReservations($excel, $sheetName, $data) {

        $sheet = $this->createSheet($excel, $sheetName);
        $now = new \DateTime();
        $sheet->setCellValue('a1', 'Generado: '.$now->format('d/m/Y H:s'));

        $sheet->setCellValue('a3', 'No.');
        $sheet->setCellValue('b3', 'Fecha');
        $sheet->setCellValue('c3', 'Reserva');
        $sheet->setCellValue('d3', 'Estado');
        $sheet->setCellValue('e3', 'Alojamiento');
        $sheet->setCellValue('f3', 'Nombre Alojamiento');
        $sheet->setCellValue('g3', 'Propietario (s)');
        $sheet->setCellValue('h3', 'Teléfono(s)');
        $sheet->setCellValue('i3', 'Comisión');
        $sheet->setCellValue('j3', 'Habitación');
        $sheet->setCellValue('k3', 'Adultos');
        $sheet->setCellValue('l3', 'Niños');
        $sheet->setCellValue('m3', 'Precio-Habitación');
        $sheet->setCellValue('n3', 'Llegada');
        $sheet->setCellValue('o3', 'Noches');
        $sheet->setCellValue('p3', 'Precio Total');

        $sheet = $this->styleHeader("a3:p3", $sheet);
        $style = array(
            'font' => array(
                'bold' => true,
                'size' => 14
            ),
        );
        $sheet->getStyle("a1")->applyFromArray($style);
        /*$sheet->mergeCells("A1:k1");
        $sheet->mergeCells("A4:k4");*/

        $centerStyle = array(
            'alignment' => array(
                'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            )
        );
        $sheet->getStyle("A1:k1")->applyFromArray($centerStyle);

        $sheet->fromArray($data, ' ', 'A4');
        $this->setColumnAutoSize("a", "p", $sheet);
        /*$sheet->getStyle('j7:j'.(count($data) + 6))->getNumberFormat()->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_DATE_DDMMYYYY);
        $sheet->setAutoFilter("A7:j".(count($data) + 6));*/

        return $excel;
    }

    /**
     * @param $items
     * @param $startingDate
     * @param string $fileName
     * @return Response
     */
    public function exportPendingAgencyPayment($items, $startingDate, $fileName = "pagosAgencias") {
        if(count($items) > 0) {
            $excel = $this->configExcel("Listado de pagos pendientes a agencias", "Listado de pagos pendientes a agencias", "pagos");

            $data = $this->dataForPendingAgencyPayment($excel, $items);

            if (count($data) > 0)
                $excel = $this->createSheetForPendingAgencyPayment($excel, "Pagos", $data, $startingDate);

            $fileName = $this->getFileName($fileName);
            $this->save($excel, $fileName);

            return $this->export($fileName);
        }
    }

    /**
     * @param $excel
     * @param $items
     * @return array
     */
    private function dataForPendingAgencyPayment($excel,$items) {
        $results = array();
        $timer = $this->get("Time");

        foreach ($items as $item) {
            $data = array();

            //Alojamiento
            $agency = $item->getAgency();

            //Nombre de la agencia
            $data[] = $agency->getName();
            //País de la agencia
            $data[] = $agency->getCountry()->getCoName();
            //Ubicacion de la agencia
            $data[] = $agency->getAddress();

            //Correo de la agencia
            $data[] = $agency->getEmail();

            //Telefono
            $data[] = $agency->getPhone();

            //Representante
            $contact = (count($agency->getContacts()) > 0) ? $agency->getContacts()[0] : null;
            $data[] = ($contact != null) ? $contact->getName() : "";

            //Telefono representante
            $data[] = ($contact != null) ? $contact->getPhone().(($contact->getPhone() != "" && $contact->getMobile() != "") ? " / ".$contact->getMobile() : "") : "";;

            //Reserva
            $reservation = $item->getReservation();

            //Reserva - CAS
            $data[] = $reservation->getGenResId();

            //Reserva - Código casa
            $data[] = $reservation->getGenResOwnId()->getOwnMcpCode();

            //Reserva - Nombre casa
            $data[] = $reservation->getGenResOwnId()->getOwnName();

            //Reserva - destino casa
            $data[] = $reservation->getGenResOwnId()->getOwnDestination()->getDesName();

            //Reserva - Booking
            $data[] = $item->getBooking()->getBookingId();

            //Pago
            //Número
            $data[] = $item->getId();

            //Fecha del pago
            $data[] = $item->getPayDate()->format("d/m/Y");

            //Monto a pagar
            $data[] = $item->getAmount()." ".$item->getBooking()->getPayments()[0]->getCurrency()->getCurrCode();

            //Tipo
            $data[] = $item->getType()->getTranslations()[0]->getNomLangDescription();

            //Estado: (Pendiente/En proceso/Pagado
            $data[] = $item->getStatus()->getTranslations()[0]->getNomLangDescription();

            //Fecha de registro
            $data[] = ($item->getRegisterDate() != null) ? $item->getRegisterDate()->format("d/m/Y"): "";

            array_push($results, $data);
        }

        return $results;
    }

    /**
     * @param $excel
     * @param $sheetName
     * @param $data
     * @param $startingDate
     * @return mixed
     */
    private function createSheetForPendingAgencyPayment($excel, $sheetName, $data, $startingDate) {
        $sheet = $this->createSheet($excel, $sheetName);

        $sheet->setCellValue('a1', "Listado de pagos pendientes a agencias");
        $sheet->mergeCells("A1:R1");
        $now = new \DateTime();
        $sheet->mergeCells("A2:R2");
        $sheet->setCellValue('a3', 'Fecha de creación: '.$now->format('d/m/Y H:s'));
        $sheet->mergeCells("A3:R3");

        $sheet->setCellValue('a5', 'Agencia');
        $sheet->setCellValue('b5', 'País');
        $sheet->setCellValue('c5', 'Ubicación');
        $sheet->setCellValue('d5', 'Correo');
        $sheet->setCellValue('e5', 'Teléfono');
        $sheet->setCellValue('f5', 'Representante');
        $sheet->setCellValue('g5', 'Teléfonos representante');
        $sheet->setCellValue('h5', 'CAS');
        $sheet->setCellValue('i5', 'Código alojamiento');
        $sheet->setCellValue('j5', 'Alojamiento');
        $sheet->setCellValue('k5', 'Destino');
        $sheet->setCellValue('l5', 'Booking');
        $sheet->setCellValue('m5', 'Número pago');
        $sheet->setCellValue('n5', 'Fecha pago');
        $sheet->setCellValue('o5', 'Monto a pagar');
        $sheet->setCellValue('p5', 'Tipo pago');
        $sheet->setCellValue('q5', 'Estado');
        $sheet->setCellValue('r5', 'Fecha de registro');

        $centerStyle = array(
            'alignment' => array(
                'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            )
        );
        $sheet->getStyle("A1:R1")->applyFromArray($centerStyle);

        $sheet = $this->styleHeader("A5:R5", $sheet);

        $style = array(
            'font' => array(
                'bold' => true,
                'size' => 14
            ),
        );
        $sheet->getStyle("a1")->applyFromArray($style);

        $sheet->fromArray($data, ' ', 'A6');

        $this->setColumnAutoSize("a", "r", $sheet);

        $sheet->setAutoFilter("A5:R".(count($data)+5));
        $sheet->getStyle("A5:R".(count($data)+5))
            ->getAlignment()
            ->setWrapText(true);

        return $excel;
    }
}

?>
