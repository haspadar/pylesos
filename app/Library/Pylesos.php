<?php

namespace App\Library;

use App\Library\Pylesos\Exception;
use GuzzleHttp\Client;

class Pylesos
{
    const TIMEOUT = 5;

    private ProxyRotator $proxyRotator;

    private UserAgentRotator $userAgentRotator;

    private Motor $motor;

    private Client $client;

    private ?Report $report = null;

    public function __construct(
        Motor $motor,
        ProxyRotator $proxyRotator,
        UserAgentRotator $userAgentRotator,
        Report $report = null
    ) {
        $this->motor = $motor;
        $this->proxyRotator = $proxyRotator;
        $this->userAgentRotator = $userAgentRotator;
        $this->report = $report;
    }

    public function download(string $url, bool $isCacheEnabled = true, int $attemptsLimit = 10): string
    {
        if ($isCacheEnabled && $cache = $this->getCache($url)) {
            \Log::debug('Found page in cache');

            return $cache;
        }

        $response = $this->rotateDownload($url, $attemptsLimit);
        if ($isCacheEnabled && isset($response) && $response) {
            \Log::debug('Added page to cache');
            $this->addCache($url, $response);
        }

        return $response ?? '';
    }

    public function getClient(): ?Client
    {
        return $this->client;
    }

    private function addCache(string $url, string $response): void
    {
        \DB::table('cache')->insert([
            'url' => $url,
            'domain' => new Domain($url),
            'response' => $response
        ]);
    }

    private  function getCache(string $url): string
    {
        $found = \DB::select('SELECT * FROM cache WHERE url = :url', [
            'url' => $url
        ]);

        return $found ? $found[0]->response : '';
    }

    private function rotateDownload(string $url, int $attemptsLimit, int $attemptsCounter = 0): string
    {
        if ($attemptsCounter == $attemptsLimit || !$this->proxyRotator->getRowsCount()) {
            return '';
        }

        $this->client = $this->generateClient();
        try {
            $content = $this->motor->download($url, $this->client);
            $this->addReport($content);

            return $content;
        } catch (\Exception $e) {
            $this->addReport(
                $this->motor->getResponse()
                    ? $this->motor->getResponse()
                        ->getBody()
                        ->getContents()
                    : '',
                $e
            );
            $this->proxyRotator->skip();
            $this->userAgentRotator->skip();

            return $this->rotateDownload(
                $url,
                $attemptsLimit,
                $attemptsCounter + 1
            );
        }
    }

    private function generateClient(): \GuzzleHttp\Client
    {
        return new \GuzzleHttp\Client([
            'curl' => $this->getCurlOptions(),
            'timeout' => self::TIMEOUT,
            'headers' => [
                'User-Agent' => $this->getUserAgentOptions()
            ]
        ]);
    }

    private function getCurlOptions(): array
    {
        if ($this->proxyRotator && $proxy = $this->proxyRotator->getRow()) {
            return [
                CURLOPT_PROXY => $proxy->getAddress(),
                CURLOPT_PROXYTYPE => $proxy->getCurlProxyType()
            ];
        }

        return [];
    }

    public function getReport(): ?Report
    {
        return $this->report;
    }

    private function getUserAgentOptions(): string
    {
        if ($this->userAgentRotator && $userAgent = $this->userAgentRotator->getUserAgent()) {
            return $userAgent;
        }

        return '';
    }

    private function addReport(string $responseContent, \Exception $exception = null): void
    {
        if ($this->report) {
            $this->report->add(
                $this->proxyRotator->getProxy(),
                $this->userAgentRotator->getUserAgent(),
                $responseContent,
                $this->motor->getResponse(),
                $exception
            );
        }
    }
}
