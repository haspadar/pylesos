<?php
namespace App\Library\Services;

use App\Library\Proxy;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use Illuminate\Validation\Validator;
use Symfony\Component\DomCrawler\Crawler;

class FreeProxyCz extends SiteWithProxies
{
    protected string $domain = 'http://free-proxy.cz';

    protected string $firstPageUrl = '/en/proxylist/country/all/all/ping/level1';

    protected string $nextPageUrlPattern = '/en/proxylist/country/all/all/ping/level1/%s';

    public function __construct($pagesCount = 5)
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
        $proxies = $this->downloadSite($client);
        usort($proxies, function (Proxy $previous, Proxy $next) {
            if ($previous->getAddress() > $next->getAddress()) {
                return 1;
            }

            if ($previous->getAddress() < $next->getAddress()) {
                return -1;
            }

            return 0;
        });

        return $proxies;
    }

    /**
     * @param string $page
     * @return Proxy[]
     */
    protected function parsePage(string $page): array
    {
        $crawler = new Crawler($page);

        return array_filter($crawler->filter('#proxy_list tbody tr')->each(function (Crawler $node, $i) {
            $encodedIp = $node->filter('td:nth-child(1)')->text();
            $base64Pattern = 'document.write(Base64.decode(';
            if (mb_substr($encodedIp, 0, mb_strlen($base64Pattern)) == $base64Pattern) {
                $ip = base64_decode(strtr($encodedIp, [
                    'document.write(Base64.decode("' => '',
                    '"))' => ''
                ]));
                $port = $node->filter('td:nth-child(2)')->text();
                $protocol = strtolower($node->filter('td:nth-child(3)')->text());

                return new Proxy($ip . ':' . $port, $protocol);
            } else {
                return null;
            }
        }));
    }
}
