<?php

namespace PdfMerge\Concatenate;

class integrationTest extends \PHPUnit_Framework_TestCase {

    public function testImplodePdfsReturnNotEmptyPdf() {
        /** @var $pdf \ZendPdf\PdfDocument */
        $pdf = implodePdfs(
            array(
                \ZendPdf\PdfDocument::load(__DIR__ . '/data/pdf1.pdf'),
                \ZendPdf\PdfDocument::load(__DIR__ . '/data/pdf2.pdf')
            )
        );
        $this->assertGreaterThan(20000, strlen($pdf->render()));
        $this->assertSame(3, count($pdf->pages));
    }

}
