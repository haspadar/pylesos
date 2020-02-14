<?php
namespace App\Library\Services;

use App\Library\Proxy;
use App\Library\UserAgent;
use GuzzleHttp\Client;
use Illuminate\Validation\Validator;
use Symfony\Component\DomCrawler\Crawler;

class WhatIsMyBrowserCom extends SiteWithUserAgents
{
    protected string $domain = 'https://developers.whatismybrowser.com';

    protected int $pagesCount = 5;

    protected string $firstPageUrl = '/useragents/explore/software_type_specific/web-browser/1';

    protected string $nextPageUrlPattern = '/useragents/explore/software_type_specific/web-browser/%s';

    public function __construct($pagesCount = 11)
    {
        parent::__construct($pagesCount);
    }

    public function downloadUserAgents(Client $client): array
    {
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
