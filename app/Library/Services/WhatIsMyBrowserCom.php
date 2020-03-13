<?php
namespace App\Library\Services;

use App\Library\Proxy;
use App\Library\UserAgent;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use Illuminate\Validation\Validator;
use Symfony\Component\DomCrawler\Crawler;

class WhatIsMyBrowserCom extends SiteWithParseUserAgents
{
    protected string $domain = 'https://developers.whatismybrowser.com';

    protected int $pagesCount = 5;

    protected string $firstPageUrl = '/useragents/explore/software_type_specific/web-browser/1';

    protected string $nextPageUrlPattern = '/useragents/explore/software_type_specific/web-browser/%s';

    public function __construct($pagesCount = 11)
    {
        parent::__construct($pagesCount);
    }

    public function downloadUserAgents(HandlerStack $handlerStack = null): array
    {
        $client = new \GuzzleHttp\Client([
            'handler' => $handlerStack,
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_3) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/80.0.3987.116 Safari/537.36'
            ]
        ]);

        return $this->downloadSite($client);
    }

    /**
     * @param string $page
     * @return Proxy[]
     */
    protected function parsePage(string $page): array
    {
        $crawler = new Crawler($page);

        return $crawler->filter('.table-useragents tbody tr')->each(function (Crawler $node, $i) {
            return $node->filter('td:nth-child(1) a')->text();
        });
    }
}
