<?php

namespace MyCp\mycpBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
/*use Symfony\Component\Validator\Constraints\NotBlank;
use MyCp\mycpBundle\Entity\faqLang;
use MyCp\mycpBundle\Entity\faqCategory;
use MyCp\mycpBundle\Entity\log;
use MyCp\mycpBundle\Entity\faqCategoryLang;
use MyCp\mycpBundle\Form\categoryType;
use MyCp\mycpBundle\Helpers\BackendModuleName;*/

class BackendExportController extends Controller
{
    function exportAccommodationsToExcelAction()
    {
        $service_security = $this->get('Secure');
        $service_security->verifyAccess();
        $exporter = $this->get("mycp.service.export_to_excel");

        return $exporter->exportAccommodationsDirectory();
    }

    function checkInToExcelAction($date)
    {
        /*$service_security = $this->get('Secure');
        $service_security->verifyAccess();*/
        $exporter = $this->get("mycp.service.export_to_excel");

        $date = str_replace("_", "/", $date);

        return $exporter->createCheckinExcel($date, true);
    }
}
