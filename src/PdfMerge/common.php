<?php

namespace PdfMerge;

use \Guzzle\Http\Client;
use \ZendPdf\PdfDocument;

/**
 * @param \Symfony\Component\HttpFoundation\Request $request
 * @return string[]
 */
function getUrlsFromRequest($request) {
    return array_filter($request->get('urls') ? : array());
}

/**
 * @param string $url
 * @param \Guzzle\Http\Client $httpClient
 * @return string
 * @throws \Guzzle\Common\Exception\GuzzleException
 */
function contentFromUrl($url, $httpClient) {
    return $httpClient->get($url)->send()->getBody(true);
}

/**
 * @param string[] $urls
 * @param \Guzzle\Http\Client $httpClient
 * @return string[]
 * @throws \Guzzle\Common\Exception\GuzzleException
 */
function contentListFromUrls($urls, $httpClient) {
    return array_map(
        function ($url) use ($httpClient) {
            return contentFromUrl($url, $httpClient);
        },
        $urls
    );
}

/**
 * @param \Symfony\Component\HttpFoundation\Request $request
 * @param \Guzzle\Http\Client $httpClient
 * @param string $pdfFactory
 * @return \ZendPdf\PdfDocument[]
 */
function pdfsFromRequest($request, $httpClient, $pdfFactory = '\PdfMerge\pdfFromContent') {
    return array_map(
        $pdfFactory,
        contentListFromUrls(getUrlsFromRequest($request), $httpClient)
    );
}

//--------------------------------------------------------------------------------------------------

/**
 * @param string $content
 * @return \ZendPdf\PdfDocument
 */
function pdfFromContent($content) {
    return PdfDocument::parse($content);
}
