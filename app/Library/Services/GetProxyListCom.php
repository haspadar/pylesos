<?php
namespace App\Library\Services;

use App\Library\Proxy;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use Illuminate\Validation\Validator;
use Symfony\Component\DomCrawler\Crawler;

class GetProxyListCom extends SiteWithProxies
{
    protected string $domain = 'https://api.getproxylist.com';

    protected string $firstPageUrl = '/proxy?anonymity=high%20anonymity&protocol=http';

    protected string $nextPageUrlPattern = '';

    public function __construct($pagesCount = 100)
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
        $json = json_decode($page);

        return [new Proxy($json->ip . ':' . $json->port, $json->protocol)];
    }
}
