<?php
namespace Pylesos;

use Monolog\Logger;

class Curl implements MotorInterface
{
    private Request $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function download(string $url, Rotator $rotator, $postParams, array $headers, Logger $logger): Response
    {
        $ch = curl_init();
        $curlOptions = $this->getCurlOptions($url);
        $proxy = $rotator->popProxy();
        if ($proxy) {
            $curlOptions['CURLOPT_PROXY'] = $proxy->getAddress();
            if ($proxy->getAuth()) {
                $curlOptions['CURLOPT_PROXYUSERPWD'] = $proxy->getAuth();
            }
        }

        $logger->debug('Using proxy: ' . ($proxy ? $proxy->getAddress() : 'none'));
        foreach ($curlOptions as $optionName => $optionValue) {
            if ($this->request->canConvertToArray($optionValue)) {
                curl_setopt($ch, constant($optionName), $this->request->parseArrayParam($optionValue));
            } else {
                curl_setopt($ch, constant($optionName), $optionValue);
            }
        }

        if ($headers) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }

        if ($postParams) {
            curl_setopt(
                $ch,
                CURLOPT_POSTFIELDS,
                is_array($postParams) ? http_build_query($postParams) : $postParams
            );
            curl_setopt($ch, CURLOPT_POST, 1);
        }

        $curlResponse = curl_exec($ch);
        if (curl_errno($ch)) {
            $error = curl_error($ch);
        }

        $info = curl_getinfo($ch);
        $lastUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
        curl_close($ch);
        $response = new Response(
            $curlResponse,
            $info['http_code'],
            $lastUrl,
            $error ?? '',
            $proxy,
            $this->request
        );
        if ($this->request->isDebug()) {
            $response->setDebug([
                'options' => $this->request->getParams(),
                'curl_options' => $curlOptions,
                'info' => $info,
                'curl_response' => $curlResponse
            ]);
        }

        return $response;
    }

    private function getCurlOptions(string $url)
    {
        $defaults = [
            'CURLOPT_URL' => $url,
            'CURLOPT_USERAGENT' => $this->request->getUserAgent($url)
        ];

        return array_replace($defaults, $this->getCurlOptParams());
    }

    private function getCurlOptParams(): array
    {
        $params = [];
        foreach ($this->request->getParams() as $name => $value) {
            if (substr($name, 0, 8) == 'CURLOPT_') {
                $params[$name] = $value;
            }
        }

        return $params;
    }
}