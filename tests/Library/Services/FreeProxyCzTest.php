<?php

use App\Library\Proxy;
use App\Library\Services\FreeProxyCz;
use App\Library\Services\SiteWithProxies;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

class FreeProxyCzTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * A basic test example.
     *
     * @return void
     */
    public function testParsing()
    {
        /**
         * @var $proxiesSource FreeProxyCz
         */
        $proxiesSource = new FreeProxyCz(2);
        $responsesDirectory = __DIR__ . '/../../mock/responses/free-proxy.cz';
        $mock = new MockHandler([
            new Response(200, [], file_get_contents($responsesDirectory . '/page1.html')),
            new Response(200, [], file_get_contents($responsesDirectory . '/page2.html')),
        ]);
        $handlerStack = HandlerStack::create($mock);
        $proxies = $proxiesSource->downloadProxies($handlerStack);
        $this->assertEquals([
            new Proxy('1.1.1.1:8080', 'https'),
            new Proxy('1.1.1.2:3128', 'https'),
            new Proxy('2.2.2.1:8080', 'https'),
            new Proxy('2.2.2.2:3128', 'https')
        ], $proxies);
    }
}
