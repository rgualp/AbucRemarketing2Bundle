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

    public function exportAccommodationsDirectory($fileName ="directorio.xlsx") {
        $excel = $this->configExcel();

        //TODO: Hacer una hoja por cada provincia
        $data = $this->dataForAccommodationsDirectory();
        $excel = $this->createSheetForAccommodationsDirectory($excel, "Alojamientos", $data);
        $excel = $this->createSheetForAccommodationsDirectory($excel, "Alojamientos1", $data);
        $excel = $this->createSheetForAccommodationsDirectory($excel, "Alojamientos2", $data);

        return $this->export($excel, $fileName);
    }

    private function dataForAccommodationsDirectory()
    {
        $results = array();
        $data = array();

        $data[0] = "CH001";
        $data[1] = "CH001";
        $data[2] = "CH001";
        $data[3] = "CH001";
        $data[4] = "CH001";
        $data[5] = "CH001";
        $data[6] = "CH001";
        $data[7] = "CH001";
        $data[8] = "CH001";

        array_push($results, $data);

        return $results;

    }

    private function createSheetForAccommodationsDirectory($excel, $sheetName, $data) {
        $sheet = $this->createSheet($excel, $sheetName);
        $sheet->setCellValue('a1', 'Propiedad');
        $sheet->setCellValue('b1', 'Habitaciones');
        $sheet->setCellValue('c1', 'Propietario 1');
        $sheet->setCellValue('d1', 'Propietario 2');
        $sheet->setCellValue('e1', 'Teléfono');
        $sheet->setCellValue('f1', 'Móvil');
        $sheet->setCellValue('g1', 'Dirección');
        $sheet->setCellValue('h1', 'Provincia');
        $sheet->setCellValue('h1', 'Municipio');
        $sheet->setCellValue('h1', 'Estado');
        $sheet->setCellValue('h1', 'Temporada Baja');
        $sheet->setCellValue('i1', 'Temporada Alta');

        $sheet = $this->styleHeader("a1:i1", $sheet);

        $sheet->fromArray($data, ' ', 'A2');

        $this->setColumnAutoSize("a", "i", $sheet);
        return $excel;
    }

    private function configExcel() {
        $excel = new \PHPExcel();
        /*$excel->setProperties()
                ->setCreator("MyCasaParticular.com")
                ->setTitle("Directorio de alojamientos")
                ->setLastModifiedBy("MyCasaParticular.com")
                ->setDescription("Directorio de alojamientos de MyCasaParticular")
                ->setSubject("Directorio de alojamientos")
                ->setKeywords("excel MyCasaParticular alojamientos")
                ->setCategory("alojamientos");*/
        return $excel;
    }

    private function createSheet($excel, $sheetName) {
        $sheet = new \PHPExcel_Worksheet($excel, $sheetName);
        $excel->addSheet($sheet, -1);
        $sheet->setTitle($sheetName);
        return $sheet;
    }

    private function styleHeader($header, $sheet) {
        $sheet->getStyle($header)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB('00ffff00');
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
        if (!is_dir($this->excelDirectoryPath)) {
            mkdir($this->excelDirectoryPath, 0755, true);
        }

        $excel->setActiveSheetIndex(0);

        $writer = new \PHPExcel_Writer_Excel2007($excel);
        $writer->save($this->excelDirectoryPath.$fileName);

        $content = file_get_contents($this->excelDirectoryPath.$fileName);
        return new Response(
                $content, 200, array(
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="'.$fileName.'"'
                )
        );
    }
}

?>
