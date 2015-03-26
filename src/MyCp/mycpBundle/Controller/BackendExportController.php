<?php

namespace MyCp\mycpBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

/* use Symfony\Component\Validator\Constraints\NotBlank;
  use MyCp\mycpBundle\Entity\faqLang;
  use MyCp\mycpBundle\Entity\faqCategory;
  use MyCp\mycpBundle\Entity\log;
  use MyCp\mycpBundle\Entity\faqCategoryLang;
  use MyCp\mycpBundle\Form\categoryType;
  use MyCp\mycpBundle\Helpers\BackendModuleName; */

class BackendExportController extends Controller {

    function exportAccommodationsToExcelAction() {
        $service_security = $this->get('Secure');
        $service_security->verifyAccess();
        $exporter = $this->get("mycp.service.export_to_excel");

        return $exporter->exportAccommodationsDirectory();
    }

    function checkInToExcelAction($date, $sort_by) {
        /* $service_security = $this->get('Secure');
          $service_security->verifyAccess(); */
        $exporter = $this->get("mycp.service.export_to_excel");

        $date = str_replace("_", "/", $date);

        return $exporter->createCheckinExcel($date, $sort_by, true);
    }

    function zipDownloadOwnPhotoAction($idOwnership, $ownMycpCode) {
        $zip = $this->get("mycp.service.zip");

        $response = $zip->createDownLoadPhotoZipFile($idOwnership, $ownMycpCode);

        if (!$response) {
            $request = $this->getRequest();
            $this->get('session')->getFlashBag()->add('message_error_local', "No hay ficheros para formar el fichero ZIP");
            return $this->redirect($request->server->get('HTTP_REFERER'));
        }

        return $response;
    }

}
