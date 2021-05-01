<?php
namespace Pylesos;

use League\CLImate\CLImate;
use Monolog\Logger;

class Response
{
    private string $response;

    private int $code;

    private string $error;

    private array $debug = [];

    private ?Proxy $proxy;

    private Request $request;

    public function __construct(string $response, int $code, string $error, ?Proxy $proxy, Request $request)
    {
        $this->response = $response;
        $this->code = $code;
        $this->error = $error;
        $this->proxy = $proxy;
        $this->request = $request;
    }

    public function setDebug(array $debug): void
    {
        $this->debug = $debug;
    }
    
    public function getCode(): int 
    {
        return $this->code;
    }
    
    public function getError(): string 
    {
        return $this->error;
    }
    
    public function getProxy(): Proxy
    {
        return $this->proxy;
    }

    public function getResponse(): string
    {
        return $this->response;
    }

    public function __toString(): string
    {
        $debug = $this->debug;
        $banWords = $this->request->getBanWords();

        return var_export([
            'title' => $this->parseTitle($this->response),
            'http_code' => $this->code,
            'proxy_address' => $this->proxy ? $this->proxy->getAddress() : '',
            'proxy_auth' => $this->proxy ? $this->proxy->getAuth() : '',
            'error' => $this->error,
            'ban_words' => $banWords,
            'is_ban' => $this->isBan(),
            'body' => mb_substr($this->parseBody($this->response), 0, 1000),
            'Debug' => $debug ? [array_merge($this->debug, ['Response' => $this->response])] : false,
        ], true);
    }

    public function isBan(?Logger $logger = null): bool
    {
        $isBanCode = in_array($this->code, $this->request->getBanCodes());
        $banWords = $this->findBanWords($this->request->getBanWords(), strip_tags($this->response));
        $isBan = $isBanCode || $banWords;
        if ($isBanCode && $logger) {
            $logger->warning('Is ban code: ' . $this->code);
        } elseif ($banWords && $logger) {
            $logger->warning(
                'Has ban words: ' . implode(', ', $banWords),
                ['strip_tags_response' => strip_tags($this->response)]
            );
        }

        return $isBan;
    }

    public function colorize(): void
    {
        $climate = new CLImate();
        $climate->cyan()->inline(PHP_EOL . 'Body: ');
        if (mb_strpos($this->response, '<title>') !== false) {
            $climate->yellow()->out(' ' . mb_substr($this->parseBody($this->response), 0, 1200) . PHP_EOL);
        } else {
            $climate->yellow()->out(' ' . mb_substr($this->response, 0, 1200) . PHP_EOL);
        }

        $mainFields = [
            'Title: ' . $this->parseTitle($this->response),
            'Http Code: ' . $this->code,
            'Error: ' . $this->error,
            'Is Ban: ' . intval($this->isBan()),
            'Proxy Address: ' . ($this->proxy ? $this->proxy->getAddress() : ''),
            'Proxy Auth: ' . ($this->proxy ? $this->proxy->getAuth() : ''),
            'Motor: ' . $this->getRequest()->getMotor()
        ];
        $climate->cyan()->columns($mainFields, 1);
        $climate->cyan()->out('');
        if ($this->debug) {
            $climate->dump($this->debug);
        }
    }

    private function parseBody(string $response): string
    {
        $body = $this->parseTag('body', $response);

        return $this->removeDoubleEmptyLines(
            trim(
                strip_tags(
                    str_replace(
                        PHP_EOL,
                        "\t"
                        , $this->removeJs($body)
                    )
                )
            )
        );
    }

    private function removeDoubleEmptyLines(string $response): string
    {
        return  preg_replace('/^\h*\v+/m', '', $response);
    }

    private function parseTitle(string $response): string
    {
//        preg_match("/<title>(.+)<\/title>/i", $response, $matches);
//
//        return $matches[1] ?? '';
//
        return $this->parseTag('title', $response);
    }

    private function parseTag(string $tag, string $response): string
    {
        preg_match("/<$tag" . "[^>]*>(.+)<\/$tag>/is", $response, $matches);

        return trim($matches[1] ?? '');
    }

    private function removeJs(string $response): string
    {
        return preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', "", $response);
    }

    private function findBanWords(array $stopWords, string $response)
    {
        $words = [];
        foreach ($stopWords as $stopWord) {
            if (mb_strpos($response, $stopWord) !== false) {
                $words[] = $stopWord;
            }
        }

        return $words;
    }

    /**
     * @return Request
     */
    public function getRequest(): Request
    {
        return $this->request;
    }
}
