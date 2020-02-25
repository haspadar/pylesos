<?php

namespace App\Library;

use App\Library\Motor\BanException;
use App\Library\Motor\NotFoundException;
use GuzzleHttp\Client;

class Pylesos
{
    const TIMEOUT = 5;

    /**
     * @var ProxyRotator
     */
    private ProxyRotator $proxyRotator;

    /**
     * @var UserAgentRotator
     */
    private UserAgentRotator $userAgentRotator;

    /**
     * @var Motor
     */
    private Motor $motor;

    /**
     * @var Client
     */
    private $client;

    private $isCacheEnabled = true;

    private $notFoundRotatesCount = 20;

    private $banRotatesCount = 20;

    public function __construct(Site $site, Motor $motor) {
        $this->setProxyRotator(new ProxyRotator($site));
        $this->setUserAgentRotator(new UserAgentRotator($site));
        $this->setMotor($motor);
    }

    public function download(string $url): string
    {
        if ($this->isCacheEnabled() && $cache = $this->getCache($url)) {
            return $cache;
        }

        if ($this->proxyRotator->getProxiesCount()) {
            $response = $this->rotateDownload($url);
        } else {
            $this->client = $this->generateClient();
            $response = $this->motor->download($url, $this->client);
        }

        if ($this->isCacheEnabled()) {
            $this->addCache($url, $response);
        }

        return $response;
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

    public function setBanRotatesCount(int $count): void
    {
        $this->banRotatesCount = $count;
    }

    public function setNoFoundRotatesCount(int $count): void
    {
        $this->notFoundRotatesCount = $count;
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

    /**
     * @return int
     */
    private function getNotFoundRotatesCount(): int
    {
        return $this->notFoundRotatesCount;
    }

    /**
     * @return int
     */
    private function getBanRotatesCount(): int
    {
        return $this->banRotatesCount;
    }

    private function rotateDownload(string $url, int $notFoundCounter = 0, int $banCounter = 0)
    {
        if ($notFoundCounter == $this->getNotFoundRotatesCount()) {
            throw new NotFoundException(sprintf('After %d attempts', $notFoundCounter));
        }

        if ($banCounter == $this->getBanRotatesCount()) {
            throw new BanException('After %d attempts', $banCounter);
        }

        try {
            $proxy = $this->proxyRotator->getLiveProxy();
            $this->client = $this->generateClient($proxy);

            return $this->motor->download($url, $this->client);
        } catch (NotFoundException $e) {
            $this->proxyRotator->blockProxy();
            $this->userAgentRotator->blockUserAgent();

            return $this->rotateDownload($url, $notFoundCounter + 1, $banCounter);
        } catch (BanException $e) {
            $this->proxyRotator->blockProxy();
            $this->userAgentRotator->blockUserAgent();

            return $this->rotateDownload($url, $notFoundCounter, $banCounter + 1);
        }
    }

    private function generateClient(Proxy $proxy = null): \GuzzleHttp\Client
    {
        return new \GuzzleHttp\Client([
            'curl' => [
                CURLOPT_SSLVERSION => CURL_SSLVERSION_TLSv1_2,
                CURLOPT_PROXY => $proxy ? $proxy->getAddress() : ''
            ],
            'timeout' => self::TIMEOUT,
            'headers' => ['User-Agent' => $this->userAgentRotator->getLiveUserAgent()]
        ]);
    }
}
