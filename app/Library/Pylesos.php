<?php
namespace App\Library;

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;

class Pylesos
{
    public function download(string $url, HandlerStack $handlerStack = null): string
    {
//        HTTP 451 Unavailable For Legal Reasons, 429  Too Many Requests, 408 Request Timeout
//        $client->request('GET', '/', ['proxy' => 'tcp://localhost:8125']);
//        ProxyRotator::getProxy
        return file_get_contents(__DIR__ . '/../../tests/mock/responses/page1.html');
    }
}
