<?php

use App\Library\Motor;
use App\Library\Motor\BanException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Laravel\Lumen\Testing\DatabaseMigrations;

class MotorTest extends TestCase
{
    use DatabaseMigrations;

    const URL = 'https://google.com';

    const SUCCESS_RESPONSE = 'Success';

    public function testSuccessDownload()
    {
        $handlerStack = HandlerStack::create(
            new MockHandler([
                new Response(200, [], self::SUCCESS_RESPONSE),
            ])
        );
        $motor = new Motor();
        $this->assertEquals(
            $motor->download(self::URL, $this->getClient($handlerStack)),
            self::SUCCESS_RESPONSE
        );
    }

    public function testNotFoundCode()
    {
        $handlerStack = HandlerStack::create(
            new MockHandler([
                new Response(404, [], 'Not Found'),
            ])
        );
        $motor = new Motor();
        $this->expectException(ClientException::class);
        $motor->download(self::URL, $this->getClient($handlerStack));
    }

    public function testBanCode()
    {
        $handlerStack = HandlerStack::create(
            new MockHandler([
                new Response(200, [], 'Your IP is banned'),
            ])
        );
        $motor = new Motor();
        $this->expectException(BanException::class);
        $motor->download(self::URL, $this->getClient($handlerStack));
    }

    public function testBanContent()
    {
        $mock = new MockHandler([
            new Response(200, [], 'Vash ip ohuel'),
        ]);
        $handlerStack = HandlerStack::create($mock);
        $motor = new Motor();
        $this->expectException(BanException::class);
        $motor->download(self::URL, $this->getClient($handlerStack));
    }

    public function testServerError()
    {
        $handlerStack = HandlerStack::create(
            new MockHandler([
                new Response(504, [])
            ])
        );
        $motor = new Motor();
        $this->expectException(ServerException::class);
        $motor->download(self::URL, $this->getClient($handlerStack));
    }

    private function getClient(HandlerStack $handlerStack)
    {
        return new Client(['handler' => $handlerStack]);
    }
}
