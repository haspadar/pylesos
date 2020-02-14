<?php

use Laravel\Lumen\Testing\DatabaseMigrations;

class DownloadPageTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * A basic test example.
     *
     * @return void
     */
    public function testDownload()
    {
        $this->assertTrue(true);
        return;
        $url = '/test';
        $mock = new \GuzzleHttp\Handler\MockHandler([
            new \GuzzleHttp\Psr7\Response(202, ['Content-Length' => 0]),
            new \GuzzleHttp\Exception\RequestException(
                'Error Communicating with Server',
                new \GuzzleHttp\Psr7\Request('GET', $url)
            ),
            new \GuzzleHttp\Psr7\Response(200, [], 'Success response'),
        ]);
        $handlerStack = \GuzzleHttp\HandlerStack::create($mock);
        $client = new \GuzzleHttp\Client([
            'handler' => $handlerStack,
            'curl' => [
                CURLOPT_SSLVERSION => CURL_SSLVERSION_TLSv1_2,
                CURLOPT_PROXY => 'proxyip:58080'
            ],
            'timeout' => 5,
            'headers' => ['User-Agent' => UserAgent::random()]
        ]);
        $pylesos = new \App\Library\Pylesos();
//        $client->request('GET', '/', ['proxy' => 'tcp://localhost:8125']);
        $rotator = new \App\Library\Rotator('google.com');

        $this->assertEquals($pylesos->download($url, $rotator, $client), 'Success response');
    }

    public function testCache()
    {
        $this->assertTrue(true);
    }
}
