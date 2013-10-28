<?php

namespace MyCp\mycpBundle\Controller;

use \Symfony\Bundle\FrameworkBundle\Controller\Controller;

class PdfController extends Controller {

    public function pdfAction() {
        $em = $this->getDoctrine()->getEntityManager();

        $response = $this->renderView('mycpBundle:pdf:document.html.twig');

        return $this->download_pdf($response, 'documento');
    }

    function download_pdf($html, $name) {
        require_once("lib/dompdf/dompdf_config.inc.php");

        $dompdf = new \DOMPDF();

        $dompdf->load_html($html);

        $dompdf->set_paper("a4", "landscape");
        $dompdf->render();

        $dompdf->stream($name . ".pdf", array("Attachment" => true));
    }

}

?>
