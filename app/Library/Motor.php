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
    private ?ResponseInterface $response = null;

    /**
     * @param string $url
     * @param Client $client
     * @return string
     * @throws BanException
     * @throws NotFoundException
     */
    public function download(string $url, Client $client): string
    {
        try {
            $this->response = $client
                ->request('get', $url, [
                    'connect_timeout' => 5
                ]);
            $content = $this->response
                ->getBody()
                ->getContents();
        } catch (ClientException $e) {
            $this->response = $e->getResponse();

            throw $e;
        }

        if ($this->hasBannedText($content)) {
            throw new BanException('Content has banned text: ' . $content, $this->response->getStatusCode());
        }

        if (!$content) {
            throw new NotFoundException('No content: ' . $content, $this->response->getStatusCode());
        }

        return $content;
    }

    public function getResponse(): ?ResponseInterface
    {
        return $this->response ?? null;
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
