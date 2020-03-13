<?php

use App\Library\Motor;
use App\Library\Motor\BanException;
use App\Library\Motor\NotFoundException;
use App\Library\Proxy;
use App\Library\ProxyRotator;
use App\Library\Pylesos;
use App\Library\UserAgentRotator;
use Laravel\Lumen\Testing\DatabaseMigrations;

class PylesosTest extends TestCase
{
    use DatabaseMigrations;

    const URL = 'https://google.com';

    const SUCCESS_RESPONSE = 'Success';

    public function testProxyRotation()
    {
        $motorMock = $this->createMock(Motor::class);
        $motorMock->method('download')
            ->will($this->onConsecutiveCalls(
                $this->throwException(new NotFoundException()),
                $this->throwException(new BanException()),
                self::SUCCESS_RESPONSE,
            ));
        $pylesos = new Pylesos(
            $motorMock, new ProxyRotator([
            new Proxy(),
            new Proxy('1.1.1.1:80'),
            new Proxy('2.2.2.2:8080'),
        ]), new UserAgentRotator([
                '',
                'UserAgent1',
                'UserAgent2',
            ])
        );
        $response = $pylesos->download(self::URL, false, 3);
        $this->assertEquals($response, self::SUCCESS_RESPONSE);
        $this->assertEquals(
            $pylesos->getClient()->getConfig()['curl'][CURLOPT_PROXY],
            '2.2.2.2:8080'
        );
        $this->assertEquals(
            $pylesos->getClient()->getConfig()['headers']['User-Agent'],
            'UserAgent2'
        );
    }

    public function testCache()
    {
        $motorMock = $this->createMock(Motor::class);
        $motorMock->method('download')
            ->will($this->onConsecutiveCalls(
                self::SUCCESS_RESPONSE,
                new NotFoundException(),
                '',
            ));
        $pylesos = new Pylesos(
            $motorMock, new ProxyRotator([new Proxy()]), new UserAgentRotator(['UserAgent1'])
        );
        for ($attemptId = 0; $attemptId < 3; $attemptId++) {
            $this->assertEquals(
                $pylesos->download(self::URL, true),
                self::SUCCESS_RESPONSE
            );
        }
    }
}
