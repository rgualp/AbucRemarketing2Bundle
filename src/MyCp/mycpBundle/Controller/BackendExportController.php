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

    function exportAccommodationsHotToExcelAction(Request $request) {
        /*$service_security = $this->get('Secure');
        $service_security->verifyAccess();*/

        $filter_active = $request->get('filter_active');
        $filter_province = $request->get('filter_province');
        $filter_destination = $request->get('filter_destination');
        $filter_name = $request->get('filter_name');
        $filter_municipality = $request->get('filter_municipality');
        $filter_type = $request->get('filter_type');
        $filter_category = $request->get('filter_category');
        $filter_code = $request->get('filter_code');
        $filter_saler = $request->get('filter_saler');
        $filter_visit_date = $request->get('filter_visit_date');
        $filter_other = $request->get('filter_other');
        $filter_commission = $request->get('filter_commission');


        $filter_start_creation_date = ($request->get('filter_start_creation_date') != null && $request->get('filter_start_creation_date') != '') ? ($request->get('filter_start_creation_date')) : (null);
        $filter_end_creation_date = ($request->get('filter_end_creation_date') != null && $request->get('filter_end_creation_date') != '') ? ($request->get('filter_end_creation_date')) : (null);

        if(isset($filter_start_creation_date) && !isset($filter_end_creation_date)){
            $filter_end_creation_date = $filter_start_creation_date;
        }
        if(!isset($filter_start_creation_date) && isset($filter_end_creation_date)){
            $filter_start_creation_date = $filter_end_creation_date;
        }
        if(isset($filter_start_creation_date) && isset($filter_end_creation_date)){
            $xfilter_start_creation_date = \DateTime::createFromFormat('d-m-Y', $filter_start_creation_date);
            $xfilter_end_creation_date = \DateTime::createFromFormat('d-m-Y', $filter_end_creation_date);
            if($filter_start_creation_date == $filter_end_creation_date){
                $xfilter_end_creation_date->modify('+1 day');
            }

            $filter_start_creation_date = $xfilter_start_creation_date->format('Y-m-d');
            $filter_end_creation_date = $xfilter_end_creation_date->format('Y-m-d');
        }

        if ($request->getMethod() == 'POST' && $filter_name == 'null' && $filter_active == 'null' && $filter_province == 'null' && $filter_municipality == 'null' &&
            $filter_type == 'null' && $filter_category == 'null' && $filter_code == 'null' && $filter_saler == 'null' && $filter_visit_date == 'null' &&
            $filter_destination == 'null' && $filter_other == 'null' && $filter_commission == 'null' && !isset($filter_start_creation_date) && !isset($filter_end_creation_date)
        ) {
            $message = 'Debe llenar al menos un campo para filtrar.';
            $this->get('session')->getFlashBag()->add('message_error_local', $message);
            return $this->redirect($this->generateUrl('mycp_list_ownerships_hot'));
        }

        if ($filter_code == 'null')
            $filter_code = '';
        if ($filter_name == 'null')
            $filter_name = '';
        if ($filter_saler == 'null')
            $filter_saler = '';
        if ($filter_visit_date == 'null')
            $filter_visit_date = '';
        if ($filter_destination == 'null')
            $filter_destination = '';
        if ($filter_other == 'null')
            $filter_other = '';
        if ($filter_commission == 'null')
            $filter_commission = '';
        if (isset($_GET['page']))
            $page = $_GET['page'];

        $em = $this->getDoctrine()->getManager();
        $ownerships = $em->getRepository('mycpBundle:ownership')->getAll(
            $filter_code, $filter_active, $filter_category, $filter_province, $filter_municipality, $filter_destination, $filter_type, $filter_name, $filter_saler, $filter_visit_date, $filter_other, $filter_commission, true, $filter_start_creation_date, $filter_end_creation_date
        )->getResult();


        $exporter = $this->get("mycp.service.export_to_excel");
        return $exporter->exportAccommodationsHotsDirectory('casascalientes', $ownerships);
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

    function airbnbGenerateAction(Request $request)
    {
            $ownToExport = $request->get('codes');
            $exporter = $this->get("mycp.service.export_to_excel");
            $exporter->generateToAirBnb($ownToExport);


            return new Response($this->generateUrl("mycp_download_info_airbnb"), 200);

    }

    function airbnbDownloadAction()
    {
            $exporter = $this->get("mycp.service.export_to_excel");
            return $exporter->exportToAirBnb();
    }

    public function getAccommodationsAction() {
        $request = $this->getRequest();

        $method = $request->getMethod();

        if ($method == 'POST') {
            $repo = $this->getDoctrine()->getManager()->getRepository('mycpBundle:ownership');

            $ownerships = $repo->findBy(array(), array("own_mcp_code" => "ASC"));

            $result = $this->renderView('mycpBundle:export:accommodationsContent.html.twig', array('ownerships' => $ownerships));

            return new Response($result, 200);
        }
        return $this->redirect($this->generateUrl('index'));
    }

    public function zipDownloadSinglePhotoAction($idPhoto, $ownMcpCode)
    {
        $zip = $this->get("mycp.service.zip");

        $response = $zip->createDownloadSinglePhotoZipFile($idPhoto, $ownMcpCode);

        if (!$response) {
            $request = $this->getRequest();
            $this->get('session')->getFlashBag()->add('message_error_local', "No existe el fichero para formar el archivo ZIP");
            return $this->redirect($request->server->get('HTTP_REFERER'));
        }

        return $response;
    }

    function dirGenerateAction(Request $request)
    {
        $ownToExport = $request->get('codes');
        $exporter = $this->get("mycp.service.export_to_excel");
        $exporter->generateDirectoryFragment($ownToExport);


        return new Response($this->generateUrl("mycp_download_info_dir"), 200);

    }

    function dirDownloadAction()
    {
        $exporter = $this->get("mycp.service.export_to_excel");
        return $exporter->exportDirectoryFragment();
    }

}
