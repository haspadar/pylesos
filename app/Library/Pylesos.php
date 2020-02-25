<?php
namespace App\Library;

use App\Library\Pylesos\BanException;
use App\Library\Pylesos\Exception;
use App\Library\Pylesos\NotFoundException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\HandlerStack;
use Psr\Http\Message\ResponseInterface;

class Pylesos
{
    /**
     * @var ProxyRotator
     */
    private ProxyRotator $proxyRotator;

    /**
     * @var UserAgentRotator
     */
    private UserAgentRotator $userAgentRotator;

    /**
     * @var Client
     */
    private Client $client;

    /**
     * @var ResponseInterface
     */
    private ResponseInterface $response;

    public function download(string $url, HandlerStack $handlerStack = null): string
    {
        if ($cache = $this->getCache($url)) {
           return $cache;
        }

        $this->proxyRotator = new ProxyRotator($url);
        $this->userAgentRotator = new UserAgentRotator($url);
        $this->client = new \GuzzleHttp\Client([
            'handler' => $handlerStack,
            'curl' => [
                CURLOPT_SSLVERSION => CURL_SSLVERSION_TLSv1_2,
                CURLOPT_PROXY => $this->proxyRotator->getLiveProxy()
            ],
            'timeout' => 5,
            'headers' => ['User-Agent' => $this->userAgentRotator->getLiveUserAgent()]
        ]);

        $response = $this->checkForExceptionsResponse($url);
        $this->addCache($url, $response);

        return $response;
    }

    public function getResponse(): ?ResponseInterface
    {
        return $this->response;
    }

    public function getUserAgentRotator(): ?UserAgentRotator
    {
        return $this->userAgentRotator;
    }

    public function getProxyRotator(): ?ProxyRotator
    {
        return $this->proxyRotator;
    }

    public function getClient(): ?Client
    {
        return $this->client;
    }

    private function hasBannedText(string $content): bool
    {
        $patterns = ['IP', 'Banned'];
        $lowerWords = explode(' ', strtolower($content));
        foreach ($patterns as $pattern) {
            if (in_array(strtolower($pattern), $lowerWords)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string $url
     * @return string
     * @throws BanException
     * @throws Exception
     * @throws NotFoundException
     */
    private function checkForExceptionsResponse(string $url): string
    {
        try {
            $this->response = $this->client
                ->request('get', $url, [
                    'connect_timeout' => 5
                ]);
        } catch (ClientException $e) {
            if ($e->getCode() == 404) {
                throw new NotFoundException($e->getMessage(), $e->getCode());
            }

            if (intval($e->getCode() / 100) == 4) {
                throw new BanException($e->getMessage(), $e->getCode());
            }

            throw new Exception($e->getMessage(), $e->getCode());
        } catch (\Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }

        $content = $this->response
            ->getBody()
            ->getContents();
        if ($this->hasBannedText($content)) {
            throw new BanException('Content has banned text: ' . $content, $this->response->getStatusCode());
        }

        if (!$content) {
            throw new NotFoundException('No content: ' . $content, $this->response->getStatusCode());
        }

        return $content;
    }

    private function addCache(string $url, string $response): void
    {
        \DB::table('cache')->insert([
            'url' => $url,
            'domain' => new Domain($url),
            'response' => $response
        ]);
    }

    private function getCache(string $url): string
    {
        $found = \DB::select('SELECT * FROM cache WHERE url = :url', [
            'url' => $url
        ]);

        return $found ? $found[0]->response : '';
    }
}
