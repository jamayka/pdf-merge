<?php

namespace PdfMerge\Concatenate;

use \Mockery as m;

class unitTest extends \PHPUnit_Framework_TestCase {

    public function testImplodeTwoPdfsReturnsPdfWithPagesFromSourcePdfs() {
        $resourceExtractor = m::mock();
        $resourceExtractor->shouldReceive('clonePage')->once()->with('page2.1')->andReturn('page2.1');
        $resourceExtractor->shouldReceive('clonePage')->once()->with('page2.2')->andReturn('page2.2');

        $resultPdf = implodeTwoPdfs(self::pdfStub(1, 2), self::pdfStub(2, 2), $resourceExtractor);

        $this->assertSame(array('page1.1', 'page1.2', 'page2.1', 'page2.2'), $resultPdf->pages);
    }

    public function testImplodePdfsReturnsPdfWithPagesFromAllPdfs() {
        $resourceExtractor = m::mock();
        $resourceExtractor->shouldReceive('clonePage')->withAnyArgs()->andReturnUsing(function ($a) { return $a; });

        $resultPdf = implodePdfs(
            array(self::pdfStub(1, 1), self::pdfStub(2, 2), self::pdfStub(3, 1)),
            $resourceExtractor
        );

        $this->assertInstanceOf('\ZendPdf\PdfDocument', $resultPdf);
        $this->assertSame(array('page1.1', 'page2.1', 'page2.2', 'page3.1'), $resultPdf->pages);
    }

//--------------------------------------------------------------------------------------------------

    private static function pdfStub($n = 0, $pagesCount = 0) {
        $pdfStub = new \StdClass();

        $pdfStub->pages = array();
        for ($i = 1; $i <= $pagesCount; $i++) {
            $pdfStub->pages[] = "page$n.$i";
        }

        return $pdfStub;
    }

}
