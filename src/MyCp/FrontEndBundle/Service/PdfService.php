<?php

namespace MyCp\FrontEndBundle\Service;

use Symfony\Component\HttpFoundation\Response;
use Font_Metrics;

class PdfService implements PdfServiceInterface
{
    /**
     * @var string
     */
    private $domPdfLibIncludeFile;

    /**
     * Construct
     * @param string $domPdfLibIncludeFile Path to the DOMPDF library include file.
     * @throws \InvalidArgumentException
     */
    public function __construct($domPdfLibIncludeFile)
    {
        if (!is_file($domPdfLibIncludeFile)) {
            throw new \InvalidArgumentException(
                'This is not a valid file path: ' . $domPdfLibIncludeFile);
        }

        $this->domPdfLibIncludeFile = $domPdfLibIncludeFile;
    }

    /**
     * {@inheritDoc}
     */
    public function storeHtmlAsPdf($html, $filePath)
    {
        if (empty($filePath) || !is_string($filePath)) {
            return false;
        }

        if ($html instanceof Response) {
            $html = $html->getContent();
        }

        $dompdf = $this->getDomPdfObject($html);
        $content_out = $dompdf->output();
        $fpdf = fopen($filePath, 'w');

        if ($fpdf === false) {
            return false;
        }

        fwrite($fpdf, $content_out);
        fclose($fpdf);
        return file_exists($filePath);
    }

    /**
     * {@inheritDoc}
     */
    public function streamHtmlAsPdf($html, $fileName)
    {
        if ($html instanceof Response) {
            $html = $html->getContent();
        }

        $dompdf = $this->getDomPdfObject($html);
        $dompdf->stream($fileName . ".pdf", array("Attachment" => false));
    }

    /**
     * Creates a DOMPDF object from HTML.
     *
     * @param $html
     * @return \DOMPDF
     */
    private function getDomPdfObject($html)
    {
        $domPdfLib = $this->domPdfLibIncludeFile;
        require_once($domPdfLib);
        $dompdf = new \DOMPDF();
        $dompdf->load_html($html);
        //$dompdf->set_paper("a4", "landscape");
        $dompdf->set_paper("a4");
        $dompdf->render();
        $canvas = $dompdf->get_canvas();
        $font = Font_Metrics::get_font("helvetica", "bold");
        $canvas->page_text(530, 20, "Page: {PAGE_NUM} of {PAGE_COUNT}", $font, 6, array(0,0,0));

        return $dompdf;
    }
}
