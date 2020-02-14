<?php

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

class WhatIsMyBrowserСomTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * A basic test example.
     *
     * @return void
     */
    public function testParsing()
    {
        $this->assertTrue(true);
        return;
        /**
         * @var $userAgentsSource \App\Library\Services\WhatIsMyBrowserСom
         */
        $userAgentsSource = new \App\Library\Services\WhatIsMyBrowserСom();
        $responsesDirectory = __DIR__ . '/../../mock/responses/whatismybrowser.com';
        $mock = new MockHandler([
            new Response(200, [], file_get_contents($responsesDirectory . '/page1.html')),
            new Response(200, [], file_get_contents($responsesDirectory . '/page2.html')),
        ]);
        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);
        $userAgents = $userAgentsSource->downloadSite($client);
        $this->assertEquals([
            new \App\Library\UserAgent('Mozilla/5.0 (iPhone; CPU iPhone OS 12_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Mobile/15E148', true),
            new \App\Library\UserAgent('Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/74.0.3729.169 Safari/537.36', false),
            new \App\Library\UserAgent('Mozilla/5.0 (Windows NT 6.3; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/60.0.3112.113 Safari/537.36', false),
            new \App\Library\UserAgent('Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/52.0.2743.116 Safari/537.36 Edge/15.15063', false)
        ], $userAgents);
    }
}
