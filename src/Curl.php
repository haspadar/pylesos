<?php
namespace Pylesos;

class Curl implements MotorInterface
{
    private Request $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function download(string $url, Rotator $rotator): Response
    {
        $ch = curl_init();
        $curlOptions = $this->getCurlOptions($url);
        foreach ($curlOptions as $optionName => $optionValue) {
            if ($this->request->canConvertToArray($optionValue)) {
                curl_setopt($ch, constant($optionName), $this->request->parseArrayParam($optionValue));
            } else {
                curl_setopt($ch, constant($optionName), $optionValue);
            }
        }

        $proxy = $rotator->popProxy();
        if ($proxy) {
            curl_setopt($ch, CURLOPT_PROXY, $proxy->getAddress());
            curl_setopt($ch, CURLOPT_PROXYUSERPWD, $proxy->getAuth());

        }

        $curlResponse = curl_exec($ch);
        if (curl_errno($ch)) {
            $error = curl_error($ch);
        }

        $info = curl_getinfo($ch);
        curl_close($ch);
        $response = new Response(
            $curlResponse,
            $info['http_code'],
            $error ?? '',
            $proxy,
            $this->request
        );
        if ($this->request->isDebug()) {
            $response->setDebug([
                'curl_options' => $curlOptions,
                'info' => $info
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