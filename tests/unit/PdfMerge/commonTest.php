<?php

namespace PdfMerge;

use \Symfony\Component\HttpFoundation\Request;
use \Symfony\Component\HttpFoundation\Response;
use \Mockery as m;
use \Guzzle\Http\Client;

class unitTest extends \PHPUnit_Framework_TestCase {

    public function testGetUrlsFromRequestReturnsListOfUrls() {
        $urls = array(
            'http://binary.ostjob.ch/hfnRd3.pdf',
            'http://binary.ostjob.ch/gwcDew.pdf',
            'http://binary.ostjob.ch/opDwuh.pdf'
        );

        $this->assertSame(
            $urls,
            getUrlsFromRequest(self::request($urls))
        );
    }

    public function testGetUrlsFromRequestSkipsEmptyUrls() {
        $this->assertSame(
            array('http://binary.ostjob.ch/hfnRd3.pdf'),
            getUrlsFromRequest(self::request(array('http://binary.ostjob.ch/hfnRd3.pdf', '', '')))
        );
    }

    public function testGetUrlsFromRequestReturnEmptyArrayForWrongRequest() {
        $this->assertSame(array(), getUrlsFromRequest(Request::create('/', 'POST')));
    }

    public function testGetContentsCallsHttpClientAndReturnsBody() {
        $httpClient = m::mock();

        $response = m::mock();
        $response->shouldReceive('getBody')->with(true)->andReturn('file 1');

        $httpClient->shouldReceive('get')->with('http://binary.ostjob.ch/hfnRd3.pdf')->andReturn($httpClient);
        $httpClient->shouldReceive('send')->andReturn($response);

        $this->assertSame('file 1', contentFromUrl('http://binary.ostjob.ch/hfnRd3.pdf', $httpClient));
    }

    public function testContentListFromUrlsCallsHttpClientAndReturnsListOfReturnedContents() {
        $urls = array(
            'http://binary.ostjob.ch/hfnRd3.pdf',
            'http://binary.ostjob.ch/gwcDew.pdf',
            'http://binary.ostjob.ch/opDwuh.pdf'
        );
        $this->assertSame(
            array(
                'http://binary.ostjob.ch/hfnRd3.pdf content',
                'http://binary.ostjob.ch/gwcDew.pdf content',
                'http://binary.ostjob.ch/opDwuh.pdf content'
            ),
            contentListFromUrls($urls, self::httpClient())
        );
    }

    public function testPdfFromRequestUsesUrlsPostParameter() {
        $pdfs = pdfsFromRequest(
            self::request(array('url1', 'url2', 'url3')),
            self::httpClient(),
            self::pdfFactory()
        );

        $this->assertSame(3, count($pdfs));
        $this->assertSame(
            array('page1 url2 content', 'page2 url2 content'),
            $pdfs[1]->pages
        );
    }

//--------------------------------------------------------------------------------------------------

    private static function request($urls = array()) {
        return Request::create('/', 'POST', array('urls' => $urls));
    }

    private static function httpClient() {
        $httpClient = m::mock();
        $httpClient->shouldReceive('get')->withAnyArgs()->andReturnUsing(
            function ($url) use ($httpClient) {
                $httpClient->shouldReceive('send')->once()->andReturnUsing(
                    function () use ($url) {
                        $response = m::mock();
                        $response->shouldReceive('getBody')->with(true)->andReturn($url . ' content');
                        return $response;
                    }
                );
                return $httpClient;
            }
        );
        return $httpClient;
    }

    private static function pdfFactory() {
        $pdfFactory = m::mock();
        $pdfFactory->shouldReceive('fn')->andReturnUsing(
            function ($content) {
                return (object)array('pages' => array('page1 ' . $content, 'page2 ' . $content));
            }
        );
        return array($pdfFactory, 'fn');
    }

}
