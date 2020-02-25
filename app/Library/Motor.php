<?php

namespace App\Library;

use App\Library\Motor\BanException;
use App\Library\Motor\Exception;
use App\Library\Motor\NotFoundException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;
use Psr\Http\Message\ResponseInterface;

class Motor
{
    /**
     * @var Client
     */
    private Client $client;

    /**
     * @var ResponseInterface
     */
    private ResponseInterface $response;

    public function download(string $url, Client $client): string
    {
        $this->client = $client;

        return $this->checkForExceptionsResponse($url);
    }

    public function getResponse(): ?ResponseInterface
    {
        return $this->response;
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
                throw new NotFoundException(get_class($e) . ': ' . $e->getMessage(), $e->getCode());
            }

            if (intval($e->getCode() / 100) == 4) {
                throw new BanException(get_class($e) . ': ' . $e->getMessage(), $e->getCode());
            }

            throw new NotFoundException(get_class($e) . ': ' . $e->getMessage(), $e->getCode());
        } catch (\Exception $e) {
            throw new NotFoundException(get_class($e) . ': ' . $e->getMessage(), $e->getCode());
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
}
