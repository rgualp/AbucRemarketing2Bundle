<?php

/**
 * Description of ExcelReader
 *
 * @author Yanet Morales
 */

namespace MyCp\mycpBundle\Helpers;

use PHPExcel_IOFactory;
use Doctrine\ORM\EntityManager;

abstract class ExcelReader extends BatchProcessManager{

    /**
     * 'doctrine.orm.entity_manager' service
     *
     * @var \Doctrine\ORM\EntityManager
     */
    protected $container;

    /**
     * @var string
     */
    protected $excelDirectoryPath;
    protected $log;

    public function __construct(EntityManager $em, $container, $excelDirectoryPath, $log) {
        parent::__construct($em);
        $this->container = $container;
        $this->excelDirectoryPath = $excelDirectoryPath;
        $this->log = $log;

        FileIO::createDirectoryIfNotExist($this->excelDirectoryPath);
    }

    protected function processExcel($excelFileName)
    {
        $excelFileNameFullPath = $this->excelDirectoryPath.$excelFileName;

        try {
            $inputFileType = PHPExcel_IOFactory::identify($excelFileNameFullPath);
            $objReader = PHPExcel_IOFactory::createReader($inputFileType);
            $objPHPExcel = $objReader->load($excelFileNameFullPath);
        } catch(\Exception $e) {
            die('Error loading file "'.pathinfo($excelFileNameFullPath,PATHINFO_BASENAME).'": '.$e->getMessage());
        }

        $sheet = $objPHPExcel->getSheet(0);
        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();

        $this->startProcess();

        for ($row = 2; $row <= $highestRow; $row++){
            $this->addElement();

            try {
                $rowData = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row,
                    NULL,
                    TRUE,
                    FALSE);
                $this->processRowData($rowData[0], $sheet, $row);
            }
            catch(\Exception $e)
            {
                $this->addError("<b>Fila $row: </b>".$e->getMessage()." <br/> ".$e->getTraceAsString());
                continue;
            }
        }

        $this->endProcess();
        $this->setStatus();
        $this->saveProcess();
        FileIO::deleteFile($excelFileNameFullPath);

    }

    protected abstract function processRowData($rowData, $sheet, $rowNumber);
}

?>
