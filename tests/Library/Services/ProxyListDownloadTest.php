<?php

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
         * @var $proxiesSource \App\Library\Services\ProxyListDownload
         */
        $proxiesSource = new \App\Library\Services\ProxyListDownload(1);
        $responsesDirectory = __DIR__ . '/../../mock/responses/proxy-list.download';
        $mock = new MockHandler([
            new Response(200, [], file_get_contents($responsesDirectory . '/page1.txt'))
        ]);
        $handlerStack = HandlerStack::create($mock);
        $proxies = $proxiesSource->downloadProxies($handlerStack);
        $this->assertEquals([
            new \App\Library\Proxy('1.1.1.1:8080', 'http'),
            new \App\Library\Proxy('2.2.2.2:3128', 'http'),
        ], $proxies);
    }
}
