<?php

namespace PdfMerge\Concatenate;

/**
 * @param \ZendPdf\PdfDocument $pdf1
 * @param \ZendPdf\PdfDocument $pdf2
 * @param \ZendPdf\Resource\Extractor $resourceExtractor
 * @return \ZendPdf\PdfDocument
 */
function implodeTwoPdfs($pdf1, $pdf2, $resourceExtractor) {
    $resultPdf = clone $pdf1;

    foreach ($pdf2->pages as $page) {
        $resultPdf->pages[] = $resourceExtractor->clonePage($page);
    }

    return $resultPdf;
}

/**
 * @param \ZendPdf\PdfDocument[] $pdfs
 * @param \ZendPdf\Resource\Extractor|null $resourceExtractor
 * @return \ZendPdf\PdfDocument
 */
function implodePdfs($pdfs, $resourceExtractor = null) {
    if ($resourceExtractor === null) {
        $resourceExtractor = new \ZendPdf\Resource\Extractor();
    }

    return array_reduce(
        $pdfs,
        function ($a, $b) use ($resourceExtractor) {
            return implodeTwoPdfs($a, $b, $resourceExtractor);
        },
        new \ZendPdf\PdfDocument()
    );
}
