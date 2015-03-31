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
            $this->get('session')->getFlashBag()->add('message_error_local', "No hay ficheros para formar el archivo ZIP");
            return $this->redirect($request->server->get('HTTP_REFERER'));
        }

        return $response;
    }

    function downloadAction()
    {
        return $this->render('mycpBundle:export:home.html.twig');
    }

    function airbnbDownloadAction(Request $request)
    {
        if ($request->getMethod() == 'POST') {
            $ownToExport = $request->get('txtCodes');

            $exporter = $this->get("mycp.service.export_to_excel");
            return $exporter->exportToAirBnb($ownToExport);
        }

        return $this->redirect($this->generateUrl('mycp_download_info'));
    }

    public function getAccommodationsAction() {
        $request = $this->getRequest();

        $method = $request->getMethod();

        if ($method == 'POST') {
            $repo = $this->getDoctrine()->getEntityManager()->getRepository('mycpBundle:ownership');

            $ownerships = $repo->findBy(array("own_status" => \MyCp\mycpBundle\Entity\ownershipStatus::STATUS_ACTIVE), array("own_mcp_code" => "ASC"));

            $result = $this->renderView('mycpBundle:export:accommodationsContent.html.twig', array('ownerships' => $ownerships));

            return new Response($result, 200);
        }
        return $this->redirect($this->generateUrl('index'));
    }

}
