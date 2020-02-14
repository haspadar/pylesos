<?php
namespace App\Library\Services;

use App\Library\Proxy;
use GuzzleHttp\Client;
use Illuminate\Validation\Validator;
use Symfony\Component\DomCrawler\Crawler;

class FreeProxyCz extends SiteWithProxies
{
    protected string $domain = 'http://free-proxy.cz';

    protected string $firstPageUrl = '/en/proxylist/country/all/https/ping/level1';

    protected string $nextPageUrlPattern = '/en/proxylist/country/all/https/ping/level1/%s';

    public function __construct($pagesCount = 5)
    {
        parent::__construct($pagesCount);
    }

    /**
     * @param Client $client
     * @return Proxy[]
     */
    public function downloadProxies(Client $client): array
    {
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

//    /**
//     * @param array $items
//     * @return Item[] array
//     */
//    public static function getAddresses(array $items): array
//    {
//        $ips = [];
//        $ports = [];
//        $addresses = [];
//        $protocols = [];
//        foreach ($items as $item) {
//            foreach ($item->getValues() as $title => $value) {
//                if (mb_strtolower($title) == 'ip') {
//                    foreach ($value as $cryptedIp) {
//                        $ips[] = base64_decode(self::getStringBetweenQuotes($cryptedIp));
//                    }
//                } elseif (mb_strtolower($title) == 'port') {
//                    foreach ($value as $port) {
//                        $ports[] = $port;
//                    }
//                } elseif (mb_strtolower($title) == 'protocol') {
//                    foreach ($value as $upperProtocol) {
//                        $protocols[] = mb_strtolower($upperProtocol);
//                    }
//                }
//            }
//
//            foreach ($ips as $key => $ip) {
//                $address = $ip . ':' . $ports[$key];
//                $protocol = $protocols[$key] ?? '';
//                $addresses[$address . $protocol] = [
//                    'address' => $ip . ':' . $ports[$key],
//                    'protocol' => $protocol
//                ];
//            }
//        }
//
//        return array_values($addresses);
//    }

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

//    private static function getStringBetweenQuotes(string $string): string
//    {
//        $firstPosition = mb_strpos($string, '"');
//        $secondPosition = mb_strpos($string, '"', $firstPosition + 1);
//
//        return mb_substr($string, $firstPosition, $secondPosition - $firstPosition);
//    }
}
