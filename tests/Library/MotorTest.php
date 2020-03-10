<?php

use Laravel\Lumen\Testing\DatabaseMigrations;

class MotorTest extends TestCase
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
        $motor = new \App\Library\Motor();
        $this->assertEquals(
            $motor->download(self::URL, $this->getClient($handlerStack)),
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
        $motor = new \App\Library\Motor();
        $this->expectException(\App\Library\Motor\NotFoundException::class);
        $motor->download(self::URL, $this->getClient($handlerStack));
    }

    public function testNoContent()
    {
        $handlerStack = \GuzzleHttp\HandlerStack::create(
            new \GuzzleHttp\Handler\MockHandler([
                new \GuzzleHttp\Psr7\Response(200, ['Content-Length' => 0]),
            ])
        );
        $motor = new \App\Library\Motor();
        $this->expectException(\App\Library\Motor\ConnectionException::class);
        $motor->download(self::URL, $this->getClient($handlerStack));
    }

    public function testBanCode()
    {
        $handlerStack = \GuzzleHttp\HandlerStack::create(
            new \GuzzleHttp\Handler\MockHandler([
                new \GuzzleHttp\Psr7\Response(451, [], 'Unavailable For Legal Reasons'),
            ])
        );
        $motor = new \App\Library\Motor();
        $this->expectException(\App\Library\Motor\BanException::class);
        $motor->download(self::URL, $this->getClient($handlerStack));
    }

    public function testBanContent()
    {
        $mock = new \GuzzleHttp\Handler\MockHandler([
            new \GuzzleHttp\Psr7\Response(200, [], 'Vash ip ohuel'),
        ]);
        $handlerStack = \GuzzleHttp\HandlerStack::create($mock);
        $motor = new \App\Library\Motor();
        $this->expectException(\App\Library\Motor\BanException::class);
        $motor->download(self::URL, $this->getClient($handlerStack));
    }

    public function testServerError()
    {
        $handlerStack = \GuzzleHttp\HandlerStack::create(
            new \GuzzleHttp\Handler\MockHandler([
                new \GuzzleHttp\Psr7\Response(504, [])
            ])
        );
        $motor = new \App\Library\Motor();
        $this->expectException(\App\Library\Motor\Exception::class);
        $motor->download(self::URL, $this->getClient($handlerStack));
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
        $motor = new \App\Library\Motor();
        $this->expectException(\App\Library\Motor\Exception::class);
        $motor->download(self::URL, $this->getClient($handlerStack));
    }

    private function getClient(\GuzzleHttp\HandlerStack $handlerStack)
    {
        return new \GuzzleHttp\Client(['handler' => $handlerStack]);
    }
}
