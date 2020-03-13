<?php
namespace App\Library\Services;

use App\Library\Proxy;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use Illuminate\Validation\Validator;
use Symfony\Component\DomCrawler\Crawler;

class ProxyListDownload extends SiteWithParseProxies
{
    protected string $domain = 'https://www.proxy-list.download';

    protected string $firstPageUrl = '/api/v1/get?type=http&anon=elite';

    protected string $nextPageUrlPattern = '';

    public function __construct($pagesCount = 1)
    {
        parent::__construct($pagesCount);
    }

    /**
     * @param HandlerStack $handlerStack
     * @return Proxy[]
     */
    public function downloadProxies(HandlerStack $handlerStack = null): array
    {
        $client = new Client(['handler' => $handlerStack]);

        return $this->downloadSite($client);
    }

    /**
     * @param string $page
     * @return Proxy[]
     */
    protected function parsePage(string $page): array
    {
        $rows = explode(PHP_EOL, $page);
        $proxies = [];
        foreach ($rows as $row) {
            if ($row) {
                $proxies[] = new Proxy(trim($row), 'http');
            }
        }

        return array_reverse($proxies);
    }
}
