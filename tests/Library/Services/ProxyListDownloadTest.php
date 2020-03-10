<?php

use App\Library\Proxy;
use App\Library\Services\ProxyListDownload;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Laravel\Lumen\Testing\DatabaseMigrations;

class ProxyListDownloadTest extends TestCase
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
         * @var $proxiesSource ProxyListDownload
         */
        $proxiesSource = new ProxyListDownload(1);
        $responsesDirectory = __DIR__ . '/../../mock/responses/proxy-list.download';
        $mock = new MockHandler([
            new Response(200, [], file_get_contents($responsesDirectory . '/page1.txt'))
        ]);
        $handlerStack = HandlerStack::create($mock);
        $proxies = $proxiesSource->downloadProxies($handlerStack);
        $this->assertEquals([
            new Proxy('1.1.1.1:8080'),
            new Proxy('2.2.2.2:3128'),
        ], $proxies);
    }
}
