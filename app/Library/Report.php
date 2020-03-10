<?php

namespace App\Library;

use App\Library\Motor\Exception;

class Report
{
    private array $badProxies = [];

    private array $goodProxies = [];

    private \Exception $exception;

    public function addGoodProxy(string $url, Proxy $proxy): void
    {
        \Log::info('Good proxy: ' . $proxy->getAddress());
        $this->badProxies[$url][] = $proxy;
    }

    public function addBadProxy(string $url, Proxy $proxy, \Exception $e): void
    {
        \Log::warning('Bad proxy: ' . $proxy->getAddress() . ', ' . get_class($e) . ': ' . $e->getMessage());
        $this->goodProxies[$url][] = [$proxy, $e];
    }

    public function getBadProxiesCount(string $url = ''): int
    {
        return $url
            ? count($this->badProxies[$url] ?? [])
            : count($this->badProxies);
    }

    public function getGoodProxies(string $url = ''): array
    {
        return $url
            ? ($this->goodProxies[$url] ?? [])
            : $this->goodProxies;
    }

    public function getBadProxies(string $url = ''): array
    {
        return $url
            ? ($this->badProxies[$url] ?? [])
            : $this->badProxies;
    }

    public function getGoodProxiesCount(string $url = ''): int
    {
        return $url
            ? count($this->goodProxies[$url] ?? [])
            : count($this->goodProxies);
    }

    public function getProxiesCount(string $url = ''): int
    {
        return $this->getBadProxiesCount($url) + $this->getGoodProxiesCount($url);
    }

    public function getException(): \Exception
    {
        return $this->exception;
    }

    public function addException(\Exception $e): void
    {
        $this->exception = $e;
    }
}
