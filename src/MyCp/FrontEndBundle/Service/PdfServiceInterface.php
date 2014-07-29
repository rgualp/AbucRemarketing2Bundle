<?php

namespace MyCp\FrontEndBundle\Service;

use Symfony\Component\HttpFoundation\Response;

interface PdfServiceInterface
{
    /**
     * Streams HTML as PDF to be viewed in a browser.
     *
     * @param string|Response $html The HTML to be rendered.
     * @param string $fileName The resulting file name without the ".pdf" ending.
     */
    public function streamHtmlAsPdf($html, $fileName);

    /**
     * Stores HTML as PDF on the file system.
     *
     * @param string|Response $html The HTML to be rendered.
     * @param string $filePath The file path where the PDF shall be stored.
     * @return bool
     */
    public function storeHtmlAsPdf($html, $filePath);
}
