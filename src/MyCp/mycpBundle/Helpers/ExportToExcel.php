<?php

/**
 * Description of ExportToExcel
 *
 * @author Yanet Morales
 */

namespace MyCp\mycpBundle\Helpers;

use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\Response;

class ExportToExcel {

    /**
     * 'doctrine.orm.entity_manager' service
     *
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;
    private $container;

    /**
     * @var string
     */
    private $excelDirectoryPath;

    public function __construct(EntityManager $em, $container, $excelDirectoryPath) {
        $this->em = $em;
        $this->container = $container;
        $this->excelDirectoryPath = $excelDirectoryPath;
    }

    /*
     * Crea un fichero excel con los check-in que ocurrirán en la fecha introducida
     * El formato del parametro $date será 'd/m/Y'
     */
    public function createCheckinExcel($date, $export=false) {
        $excel = $this->configExcel("Check-in $date", "Check-in del $date - MyCasaParticular", "check-in");
        $array_date_from = explode('/', $date);
        $checkinDate="";
        if (count($array_date_from) > 1)
            $checkinDate = $array_date_from[2] . $array_date_from[1] . $array_date_from[0];
        $fileName = "CheckIn_$checkinDate.xlsx";

        $data = $this->dataForCheckin($date);

        if(count($data) > 0)
             $excel = $this->createSheetForCheckin($excel, str_replace("/","-",$date), $data);

        if($export)
            return $this->export($excel, $fileName);
        else
        {
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

        $this->setColumnAutoSize("a", "m", $sheet);
        return $excel;
    }

    private function dataForCheckin($date) {
        $results = array();

        $checkins = $this->em->getRepository("mycpBundle:generalReservation")->getCheckins($date);

        $total_nights = array();
        $service_time = $this->container->get('time');
        foreach ($checkins as $res) {
            $owns_res = $this->em->getRepository('mycpBundle:ownershipReservation')->findBy(array('own_res_gen_res_id' => $res[0]['gen_res_id']));
            $temp_total_nights = 0;
            foreach ($owns_res as $own) {
                $nights = $service_time->nights($own->getOwnResReservationFromDate()->getTimestamp(), $own->getOwnResReservationToDate()->getTimestamp());
                $temp_total_nights+=$nights;
            }
            $total_nights[$res[0]["gen_res_id"]] = $temp_total_nights;
        }

        foreach ($checkins as $check) {
            $data = array();

            $data[0] = "CAS.".$check[0]["gen_res_id"];
            $resDate = $check[0]["gen_res_date"];
            $data[1] = $resDate->format("d/m/Y");
            $data[2] = $check[0]["gen_res_own_id"]["own_mcp_code"];
            $data[3] = $check[0]["gen_res_own_id"]["own_homeowner_1"];
            if($check[0]["gen_res_own_id"]["own_homeowner_2"] != "")
                $data[3] .= " / ". $check[0]["gen_res_own_id"]["own_homeowner_2"];

            $data[4] = "";
            if($check[0]["gen_res_own_id"]["own_phone_number"] != "")
                $data[4] .= "(+53) ".$check[0]["gen_res_own_id"]["own_address_province"]["prov_phone_code"]." ".$check[0]["gen_res_own_id"]["own_phone_number"];

            if($check[0]["gen_res_own_id"]["own_phone_number"] != "" && $check[0]["gen_res_own_id"]["own_mobile_number"] != "")
                $data[4] .= " / ";

            $data[4] .= $check[0]["gen_res_own_id"]["own_mobile_number"];

            //Total de habitaciones
            $data[5] = $check[1];

            //Total de huéspedes
            $data[6] = $check[3]+$check[5];

            //Noches
            $data[7] = $total_nights[$check[0]["gen_res_id"]];

            //Fecha de Pago
            $payDate = new \DateTime($check[7]);
            $data[8] = $payDate->format("d/m/Y");

            //Pago en casa
            $data[9] = $check[0]["gen_res_total_in_site"]-$check[0]["gen_res_total_in_site"]*$check[0]["gen_res_own_id"]["own_commission_percent"]/100;
            $data[9] .= " CUC";
            //Cliente
            $data[10] = $check[0]["gen_res_user_id"]["user_user_name"]." ".$check[0]["gen_res_user_id"]["user_last_name"];
            $data[11] = $check[0]["gen_res_user_id"]["user_country"]["co_name"];


            array_push($results, $data);
        }

        return $results;
    }

    public function exportAccommodationsDirectory($fileName = "directorio.xlsx") {
        $excel = $this->configExcel("Directorio de alojamientos", "Directorio de alojamientos de MyCasaParticular", "alojamientos");

        $provinces = $this->em->getRepository("mycpBundle:province")->findBy(array(), array("prov_code" => "ASC"));

        foreach ($provinces as $prov) {
            //TODO: Hacer una hoja por cada provincia
            $data = $this->dataForAccommodationsDirectory($prov->getProvId());

            if(count($data) > 0)
                $excel = $this->createSheetForAccommodationsDirectory($excel, $prov->getProvCode(), $data);
        }

        return $this->export($excel, $fileName);
    }

    private function dataForAccommodationsDirectory($idProvince) {
        $results = array();

        $ownerships = $this->em->getRepository("mycpBundle:ownership")->getByProvince($idProvince);

        foreach ($ownerships as $own) {
            $data = array();

            $data[0] = $own["mycpCode"];
            $data[1] = $own["totalRooms"];
            $data[2] = $own["owner1"].(($own["owner2"] != "")? " / ". $own["owner2"] : "");
            $data[3] = (($own["phone"] != "")?"{+53) ".$own["provCode"]. " ".$own["phone"] : "").(($own["mobile"] != "" && $own["phone"] != "") ? " / ": "").(($own["mobile"] != "") ? $own["mobile"]: "");
            $data[4] = "Calle ". $own["street"]." No.".$own["number"].(($own["between1"] != "" && $own["between2"] != "") ? " entre ".$own["between1"]." y ".$own["between2"] : "");
            $data[5] = $own["municipality"];
            $data[6] = $own["status"];

            if($own["lowDown"] != $own["highDown"])
                $data[7] = $own["lowDown"]." - ".$own["highDown"]." CUC";
            else
                $data[7] = $own["highDown"]." CUC";


            if($own["lowUp"] != $own["highUp"])
                $data[8] = $own["lowUp"]." - ".$own["highUp"]." CUC";
            else
                $data[8] = $own["highUp"]." CUC";

            array_push($results, $data);
        }

        return $results;
    }

    private function createSheetForAccommodationsDirectory($excel, $sheetName, $data) {
        $sheet = $this->createSheet($excel, $sheetName);
        $sheet->setCellValue('a1', 'Propiedad');
        $sheet->setCellValue('b1', 'Habitaciones');
        $sheet->setCellValue('c1', 'Propietario(s)');
        $sheet->setCellValue('d1', 'Teléfono(s)');
        $sheet->setCellValue('e1', 'Dirección');
        $sheet->setCellValue('f1', 'Municipio');
        $sheet->setCellValue('g1', 'Estado');
        $sheet->setCellValue('h1', 'Temporada Baja');
        $sheet->setCellValue('i1', 'Temporada Alta');

        $sheet = $this->styleHeader("a1:i1", $sheet);

        $sheet->fromArray($data, ' ', 'A2');

        $this->setColumnAutoSize("a", "i", $sheet);
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

    private function export($excel, $fileName) {
        $this->save($excel, $fileName);

        $content = file_get_contents($this->excelDirectoryPath . $fileName);
        return new Response(
                $content, 200, array(
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"'
                )
        );
    }

    private function save($excel, $fileName) {
        if (!is_dir($this->excelDirectoryPath)) {
            mkdir($this->excelDirectoryPath, 0755, true);
        }

        $excel->setActiveSheetIndex(0);

        $writer = new \PHPExcel_Writer_Excel2007($excel);
        $writer->save($this->excelDirectoryPath . $fileName);
    }

}

?>
