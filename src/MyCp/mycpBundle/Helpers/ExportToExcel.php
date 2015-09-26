<?php

/**
 * Description of ExportToExcel
 *
 * @author Yanet Morales
 */

namespace MyCp\mycpBundle\Helpers;

use Doctrine\ORM\EntityManager;
use MyCp\mycpBundle\Entity\room;
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

        $total_nights = array();
        $service_time = $this->container->get('time');
        foreach ($checkins as $res) {
            $genRes = $this->em->getRepository('mycpBundle:generalReservation')->find($res[0]['gen_res_id']);
            $reservations = $this->em->getRepository('mycpBundle:ownershipReservation')->findBy(array("own_res_gen_res_id" => $res[0]['gen_res_id']), array("own_res_reservation_from_date" => "ASC"));
            $nights = $genRes->getTotalStayedNights($reservations, $service_time);

            $total_nights[$res[0]["gen_res_id"]] = $nights;
        }

        foreach ($checkins as $check) {
            $data = array();

            $data[0] = \MyCp\FrontEndBundle\Helpers\ReservationHelper::getCASId($check[0]["gen_res_id"]);
            $resDate = $check[0]["gen_res_date"];
            $data[1] = $resDate->format("d/m/Y");
            $data[2] = $check[0]["gen_res_own_id"]["own_mcp_code"];
            $data[3] = $check[0]["gen_res_own_id"]["own_homeowner_1"];
            if ($check[0]["gen_res_own_id"]["own_homeowner_2"] != "")
                $data[3] .= " / " . $check[0]["gen_res_own_id"]["own_homeowner_2"];

            $data[4] = "";
            if ($check[0]["gen_res_own_id"]["own_phone_number"] != "")
                $data[4] .= "(+53) " . $check[0]["gen_res_own_id"]["own_address_province"]["prov_phone_code"] . " " . $check[0]["gen_res_own_id"]["own_phone_number"];

            if ($check[0]["gen_res_own_id"]["own_phone_number"] != "" && $check[0]["gen_res_own_id"]["own_mobile_number"] != "")
                $data[4] .= " / ";

            $data[4] .= $check[0]["gen_res_own_id"]["own_mobile_number"];

            //Total de habitaciones
            $data[5] = $check[1];

            //Total de huéspedes
            $data[6] = $check[3] + $check[5];

            //Noches
            $data[7] = $total_nights[$check[0]["gen_res_id"]];

            //Fecha de Pago
            $payDate = new \DateTime($check[7]);
            $data[8] = $payDate->format("d/m/Y");

            //Pago en casa
            $data[9] = $check[0]["gen_res_total_in_site"] - $check[0]["gen_res_total_in_site"] * $check[0]["gen_res_own_id"]["own_commission_percent"] / 100;
            $data[9] .= " CUC";
            //Cliente
            $data[10] = $check[0]["gen_res_user_id"]["user_user_name"] . " " . $check[0]["gen_res_user_id"]["user_last_name"];
            $data[11] = $check[0]["gen_res_user_id"]["user_country"]["co_name"];


            array_push($results, $data);
        }

        return $results;
    }

    public function exportAccommodationsDirectory($fileName = "directorio") {
        $excel = $this->configExcel("Directorio de alojamientos", "Directorio de alojamientos de MyCasaParticular", "alojamientos");

        $provinces = $this->em->getRepository("mycpBundle:province")->findBy(array(), array("prov_code" => "ASC"));

        foreach ($provinces as $prov) {
            //Hacer una hoja por cada provincia
            $data = $this->dataForAccommodationsDirectory($excel,$prov->getProvId());

            if (count($data) > 0)
                $excel = $this->createSheetForAccommodationsDirectory($excel, $prov->getProvCode(), $data);
        }
        $fileName = $this->getFileName($fileName);
        $this->save($excel, $fileName);
        return $this->export($fileName);
    }

    private function dataForAccommodationsDirectory($excel,$idProvince) {
        $results = array();

        $ownerships = $this->em->getRepository("mycpBundle:ownership")->getByProvince($idProvince);

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

}

?>
