<?php
namespace Pylesos;

use League\CLImate\CLImate;

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

    public function __toString(): string
    {
        $debug = $this->debug;
        $banWords = $this->request->getBanWords();

        return var_export([
            'title' => $this->parseTitle($this->response),
            'http_code' => $this->code,
            'error' => $this->error,
            'ban_words' => $banWords,
            'is_ban' => $this->isBan(),
            'body' => mb_substr($this->parseBody($this->response), 0, 1000),
            'Debug' => $debug ? [array_merge($this->debug, ['Response' => $this->response])] : false,
        ], true);
    }

    public function isBan(): bool
    {
        return in_array($this->code, $this->request->getBanCodes())
            || $this->findBanWords($this->request->getBanWords(), $this->response) > 0;
    }

    public function colorize(): void
    {
        $climate = new CLImate();
        $climate->cyan()->inline('Body: ');
        $climate->yellow()->out(' ' . mb_substr($this->parseBody($this->response), 0, 1200));
        $mainFields = [
            'Title: ' . $this->parseTitle($this->response),
            'Http Code: ' . $this->code,
            'Error: ' . $this->error,
            'Is Ban: ' . intval($this->isBan())
        ];
        $climate->cyan()->columns($mainFields, 1);
        $climate->cyan()->out('');
        if ($this->debug) {
            $key = 0;
            foreach ($this->debug as $name => $debugValues) {
                $debugColumns = [];
                foreach ($debugValues as $field => $value) {
                    if ($value) {
                        $debugColumns[] = $field . ': ' . $value;
                    }
                }

                if (!($key++ % 2)) {
                    $climate->yellow()->columns($debugColumns, 1);
                }  else {
                    $climate->cyan()->columns($debugColumns, 1);
                }

                $climate->yellow()->out('');
            }
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
        $count = 0;
        foreach ($stopWords as $stopWord) {
            if (mb_strpos($response, $stopWord) !== false) {
                $count++;
            }
        }

        return $count;
    }
}
