<?php

namespace App\Library;

use App\Library\Pylesos\Exception;
use GuzzleHttp\Client;

class Pylesos
{
    const TIMEOUT = 5;

    /**
     * @var ProxyRotator
     */
    private ?ProxyRotator $proxyRotator = null;

    private ?UserAgentRotator $userAgentRotator = null;

    private ?Motor $motor = null;

    private Client $client;

    private bool $isCacheEnabled = true;

    private int $attemptsLimit = 10;

    private int $notFoundRotatesCount = 3;

    private int $banRotatesCount = 10;

    private Report $report;

    public function __construct(
        Motor $motor,
        ProxyRotator $proxyRotator,
        UserAgentRotator $userAgentRotator,
        $isCacheEnabled
    ) {
        $this->setMotor($motor);
        $this->setProxyRotator($proxyRotator);
        $this->setUserAgentRotator($userAgentRotator);
        $this->isCacheEnabled = $isCacheEnabled;
        $this->report = new Report();
    }

    public function setAttemptsLimit(int $limit): void
    {
        $this->attemptsLimit = $limit;
    }

    public function download(string $url): string
    {
        if ($this->isCacheEnabled() && $cache = $this->getCache($url)) {
            \Log::debug('Found page in cache');

            return $cache;
        }

        try {
            $response = $this->rotateDownload($url);

//            if ($this->proxyRotator->getLiveProxiesCount()) {
//                \Log::debug('Download with rotating');
//                $response = $this->rotateDownload($url);
//            } else {
//                \Log::debug('Download without rotating');
//                $this->client = $this->generateClient();
//                $response = $this->motor->download($url, $this->client);
//            }
        } catch (\Exception $e) {
            $this->getReport()->addException($e);
        }

        if ($this->isCacheEnabled() && isset($response) && $response) {
            \Log::debug('Added page to cache');
            $this->addCache($url, $response);
        }

        return $response ?? '';
    }

    public function getClient(): ?Client
    {
        return $this->client;
    }

    public function getUserAgentRotator(): ?UserAgentRotator
    {
        return $this->userAgentRotator;
    }

    public function getProxyRotator(): ?ProxyRotator
    {
        return $this->proxyRotator;
    }

    public function isCacheEnabled(): bool
    {
        return $this->isCacheEnabled;
    }

    public function disableCache(): void
    {
        $this->isCacheEnabled = false;
    }

    private function setMotor(Motor $motorMock): void
    {
        $this->motor = $motorMock;
    }

    private  function setProxyRotator(ProxyRotator $proxyRotator): void
    {
        $this->proxyRotator = $proxyRotator;
    }

    private  function setUserAgentRotator(UserAgentRotator $userAgentRotator): void
    {
        $this->userAgentRotator = $userAgentRotator;
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

    private function rotateDownload(string $url, int $attemptsCounter = 0): string
    {
        if ($attemptsCounter == $this->getAttemptsLimit()) {
            throw new Exception(sprintf('After %d attempts', $attemptsCounter));
        }

        if (!$this->proxyRotator->getRowsCount()) {
            throw new Exception('Proxy Rotator is empty');
        }

        $this->client = $this->generateClient();
        try {
            $response = $this->motor->download($url, $this->client);
            $this->report->addGoodProxy($url, $this->proxyRotator->getProxy());

            return $response;
        } catch (Motor\Exception $e) {
            $this->report->addBadProxy($url, $this->proxyRotator->getProxy(), $e);
            $this->proxyRotator->skip();
            $this->userAgentRotator->skip();

            return $this->rotateDownload($url, $attemptsCounter + 1);
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

    public function getReport(): Report
    {
        return $this->report;
    }

    private function getUserAgentOptions(): string
    {
        if ($this->userAgentRotator && $userAgent = $this->userAgentRotator->getLiveUserAgent()) {
            return $userAgent;
        }

        return '';
    }

    private function getAttemptsLimit(): int
    {
        return $this->attemptsLimit;
    }
}
