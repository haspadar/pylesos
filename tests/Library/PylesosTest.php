<?php

use Laravel\Lumen\Testing\DatabaseMigrations;

class DownloadPageTest extends TestCase
{
    use DatabaseMigrations;

    const URL = 'https://google.com';

    const SUCCESS_RESPONSE = 'Success';

    public function testSuccessDownload()
    {
        $handlerStack = \GuzzleHttp\HandlerStack::create(
            new \GuzzleHttp\Handler\MockHandler([
                new \GuzzleHttp\Psr7\Response(200, [], self::SUCCESS_RESPONSE),
            ])
        );
        $pylesos = new \App\Library\Pylesos();
        $this->assertEquals(
            $pylesos->download(self::URL, $handlerStack),
            self::SUCCESS_RESPONSE
        );
    }

    public function testNotFoundCode()
    {
        $handlerStack = \GuzzleHttp\HandlerStack::create(
            new \GuzzleHttp\Handler\MockHandler([
                new \GuzzleHttp\Psr7\Response(404, [], 'Not Found'),
            ])
        );
        $pylesos = new \App\Library\Pylesos();
        $this->expectException(\App\Library\Pylesos\NotFoundException::class);
        $pylesos->download(self::URL, $handlerStack);
    }

    public function testNoContent()
    {
        $handlerStack = \GuzzleHttp\HandlerStack::create(
            new \GuzzleHttp\Handler\MockHandler([
                new \GuzzleHttp\Psr7\Response(200, ['Content-Length' => 0]),
            ])
        );
        $pylesos = new \App\Library\Pylesos();
        $this->expectException(\App\Library\Pylesos\NotFoundException::class);
        $pylesos->download(self::URL, $handlerStack);
    }

    public function testBanCode()
    {
        $handlerStack = \GuzzleHttp\HandlerStack::create(
            new \GuzzleHttp\Handler\MockHandler([
                new \GuzzleHttp\Psr7\Response(451, [], 'Unavailable For Legal Reasons'),
            ])
        );
        $pylesos = new \App\Library\Pylesos();
        $this->expectException(\App\Library\Pylesos\BanException::class);
        $pylesos->download(self::URL, $handlerStack);
    }

    public function testBanContent()
    {
        $mock = new \GuzzleHttp\Handler\MockHandler([
            new \GuzzleHttp\Psr7\Response(200, [], 'Vash ip ohuel'),
        ]);
        $handlerStack = \GuzzleHttp\HandlerStack::create($mock);
        $pylesos = new \App\Library\Pylesos();
        $this->expectException(\App\Library\Pylesos\BanException::class);
        $pylesos->download(self::URL, $handlerStack);
    }

    public function testServerError()
    {
        $handlerStack = \GuzzleHttp\HandlerStack::create(
            new \GuzzleHttp\Handler\MockHandler([
                new \GuzzleHttp\Psr7\Response(504, [])
            ])
        );
        $pylesos = new \App\Library\Pylesos();
        $this->expectException(\App\Library\Pylesos\Exception::class);
        $pylesos->download(self::URL, $handlerStack);
    }

    public function testRequestExceptionRequest()
    {
        $mock = new \GuzzleHttp\Handler\MockHandler([
            new \GuzzleHttp\Exception\RequestException(
                'Error Communicating with Server',
                new \GuzzleHttp\Psr7\Request('GET', self::URL)
            ),
        ]);
        $handlerStack = \GuzzleHttp\HandlerStack::create($mock);
        $pylesos = new \App\Library\Pylesos();
        $this->expectException(\App\Library\Pylesos\Exception::class);
        $pylesos->download(self::URL, $handlerStack);
    }

    public function testCache()
    {
        $handlerStack = \GuzzleHttp\HandlerStack::create(
            new \GuzzleHttp\Handler\MockHandler([
                new \GuzzleHttp\Psr7\Response(200, [], self::SUCCESS_RESPONSE),
                new \GuzzleHttp\Psr7\Response(404, [], 'Not Found'),
            ])
        );
        $pylesos = new \App\Library\Pylesos();
        $this->assertEquals(
            $pylesos->download(self::URL, $handlerStack),
            self::SUCCESS_RESPONSE
        );
        $this->assertEquals(
            $pylesos->download(self::URL, $handlerStack),
            self::SUCCESS_RESPONSE
        );
    }
}
