<?php

namespace App\Library\Services;

use App\Library\Proxy;
use GuzzleHttp\Client;

trait SiteParserTrait
{
    protected int $connectTimeout = 5;

    protected string $domain;

    protected int $pagesCount;

    protected string $firstPageUrl;

    protected string $nextPageUrlPattern;

    /**
     * @var Client
     */
    private Client $client;

    public function __construct($pagesCount)
    {
        $this->pagesCount = $pagesCount;
    }

    public function getPagesCount(): int
    {
        return $this->pagesCount;
    }

    /**
     * @param Client $client
     * @return Proxy[]
     */
    protected function downloadSite(Client $client): array
    {
        $this->client = $client;
        $rows = [];
        for ($pageNumber = 1; $pageNumber <= $this->pagesCount; $pageNumber++) {
            $pageUrl = $this->getPageUrl($pageNumber);
            $page = $this->downloadPage($pageUrl);
            $pageRows = $this->parsePage($page);
            $rows = array_merge($pageRows, $rows);
        }

        return $rows;
    }

    protected function downloadPage(string $url): string
    {
        return $this->client
            ->request('get', $this->domain . $url, [
                'connect_timeout' => $this->connectTimeout
            ])
            ->getBody()
            ->getContents();
    }

    protected function getPageUrl(int $pageNumber): string
    {
        if ($pageNumber == 1) {
            $url = $this->firstPageUrl;
        } else {
            $url = sprintf($this->nextPageUrlPattern, $pageNumber);
        }

        return $url;
    }

    abstract protected function parsePage(string $page): array;
}
