<?php
namespace App\Library\Services;

use App\Library\Proxy;
use Illuminate\Validation\Validator;
use Symfony\Component\DomCrawler\Crawler;

class WhatIsMyBrowserСom extends SiteWithUserAgents
{
    protected string $domain = 'https://developers.whatismybrowser.com';

    protected int $pagesCount = 5;

    protected string $firstPageUrl = '/useragents/explore/software_type_specific/web-browser/1';

    protected string $nextPageUrlPattern = '/useragents/explore/software_type_specific/web-browser/%s';

    /**
     * @param string $page
     * @return Proxy[]
     */
    protected function parsePage(string $page): array
    {
        $crawler = new Crawler($page);

        return $crawler->filter('.table-useragents tbody tr')->each(function (Crawler $node, $i) {
            $userAgent = $node->filter('td:nth-child(1) a')->text();
            $software = $node->filter('td:nth-child(2)')->text();
            $os = $node->filter('td:nth-child(3)')->text();
            $layoutEngine = $node->filter('td:nth-child(4)')->text();

            return [
                'user_agent' => $userAgent,
                'software' => $software,
                'os' => $os,
                'is_mobile' => mb_strpos('Mobile', $userAgent) !== false,
                'layout_engine' => $layoutEngine
            ];
        });
    }
}
