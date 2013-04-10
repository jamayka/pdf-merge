<?php

use \Symfony\Component\HttpFoundation\Request;
use \Symfony\Component\HttpFoundation\Response;

require_once __DIR__ . '/../vendor/autoload.php';

set_error_handler(
    function ($code, $msg, $file, $line) {
        throw new \ErrorException($msg, $code, 1, $file, $line);
    }
);

//--------------------------------------------------------------------------------------------------

$request = Request::createFromGlobals();
$response = new Response();

if ($request->isMethod('POST')) {
    try {
        $pdf = \PdfMerge\Concatenate\implodePdfs(
            \PdfMerge\pdfsFromRequest($request, new \Guzzle\Http\Client())
        );

        if (count($pdf->pages) > 0) {
            $response = responseWithContent($response, $pdf->render());
            $response->headers->set('Content-Type', 'application/pdf');
        } else {
            $response = responseWithContent($response, 'Empty PDFs', 400);
        }
    } catch (\ZendPdf\Exception\ExceptionInterface $e) {
        $response = responseWithContent($response, 'Wrong PDF', 400);
    } catch (\Guzzle\Http\Exception\HttpException $e) {
        $response = responseWithContent($response, 'Cannot load PDF', 400);
    } catch (\Guzzle\Common\Exception\GuzzleException $e) {
        $response = responseWithContent($response, 'Wrong URL', 400);
    } catch (\Exception $e) {
        $response = responseWithContent($response, 'Server error', 500);
    }
} else {
    $response = responseWithContent($response, 'Method not allowed', 405);
}

$response->send();

//--------------------------------------------------------------------------------------------------

function responseWithContent($response, $content, $statusCode = 200) {
    return $response->setContent($content)->setStatusCode($statusCode);
}
