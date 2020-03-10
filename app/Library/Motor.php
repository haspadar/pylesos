<?php

namespace App\Library;

use App\Library\Motor\BanException;
use App\Library\Motor\ConnectionException;
use App\Library\Motor\NotFoundException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Psr\Http\Message\ResponseInterface;

class Motor
{
    /**
     * @var ResponseInterface
     */
    private ResponseInterface $response;

    /**
     * @param string $url
     * @param Client $client
     * @return string
     * @throws BanException
     * @throws ConnectionException
     * @throws NotFoundException
     */
    public function download(string $url, Client $client): string
    {
        try {
            $this->response = $client
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
            throw new ConnectionException(get_class($e) . ': ' . $e->getMessage(), $e->getCode());
        }

        $content = $this->response
            ->getBody()
            ->getContents();
        if ($this->hasBannedText($content)) {
            throw new BanException('Content has banned text: ' . $content, $this->response->getStatusCode());
        }

        if (!$content) {
            throw new ConnectionException('No content: ' . $content, $this->response->getStatusCode());
        }

        return $content;
    }

    public function getResponse(): ?ResponseInterface
    {
        return $this->response;
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
}
