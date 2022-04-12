<?php
namespace Pylesos;

use Monolog\Logger;

class FileGetContent implements MotorInterface
{
    private Request $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function download(string $url, Rotator $rotator, array $postParams, array $headers, Logger $logger): Response
    {
        $header = $this->getHeader($headers);
        $context = [
            'http' => [
                'method' => 'GET',
                'request_fulluri' => true,
                'header' => $header
            ]
        ];
        $proxy = $rotator->popProxy();
        if ($proxy) {
            $auth = base64_encode($proxy->getAuth());
            $context['http']['proxy'] = "tcp://" . $proxy->getIp() . ':' . $proxy->getPort();
            $context['http']['header'] .= "Proxy-Authorization: Basic $auth\r\n";
        }

        $logger->debug('Using proxy: ' . ($proxy ? $proxy->getAddress() : 'none'));
        if ($postParams) {
            $context['http']['method'] = 'POST';
            $context['http']['content'] = http_build_query($postParams);
        }

        $responseContent = file_get_contents($url, false, $context ?? null);
        $response = new Response(
            $responseContent,
            explode(' ', $http_response_header[0])[1],
            '',
            '',
            $proxy,
            $this->request
        );
        if ($this->request->isDebug()) {
            $response->setDebug([
                'options' => $this->request->getParams(),
                'response_header' => $http_response_header
            ]);
        }

        return $response;
    }

    private function getHeader(array $headers): string
    {
        $header = $this->request->getParam(Request::CURLOPT_HTTPHEADER) . "\r\n";
        foreach ($headers as $name => $value) {
            $header .= $name . ': ' . $value . "\r\n";
        }

        return $header;
    }
}