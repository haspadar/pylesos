<?php

use App\Library\Motor;
use App\Library\Motor\BanException;
use App\Library\Motor\ConnectionException;
use App\Library\Motor\NotFoundException;
use App\Library\Proxy;
use App\Library\ProxyRotator;
use App\Library\Pylesos;
use App\Library\Site;
use App\Library\UserAgentRotator;
use Laravel\Lumen\Testing\DatabaseMigrations;

class PylesosTest extends TestCase
{
    use DatabaseMigrations;

    const URL = 'https://google.com';

    const DOMAIN = 'google.com';

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
            $motorMock,
            new ProxyRotator([
                new Proxy(),
                new Proxy('1.1.1.1:80'),
                new Proxy('2.2.2.2:8080'),
            ]),
            new UserAgentRotator([
                '',
                'UserAgent1',
                'UserAgent2',
            ]),
            false
        );
        $pylesos->setAttemptsLimit(3);
        $response = $pylesos->download(self::URL);
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
            $motorMock,
            new ProxyRotator([new Proxy()]),
            new UserAgentRotator(['UserAgent1']),
            true
        );
        for ($attemptId = 0; $attemptId < 3; $attemptId++) {
            $this->assertEquals(
                $pylesos->download(self::URL),
                self::SUCCESS_RESPONSE
            );
        }
    }

    public function testReportProxies()
    {
        $motorMock = $this->createMock(Motor::class);
        $motorMock->method('download')
            ->will($this->onConsecutiveCalls(
                $this->throwException(new NotFoundException()),
                self::SUCCESS_RESPONSE,
            ));
        $pylesos = new Pylesos(
            $motorMock,
            new ProxyRotator([
                new Proxy('1.1.1.1:80'),
                new Proxy('2.2.2.2:80')
            ]),
            new UserAgentRotator(['UserAgent1', 'UserAgent2']),
            false
        );
        $pylesos->download(self::URL);
        $report = $pylesos->getReport();
        $this->assertEquals($report->getProxiesCount(), 2);
        $this->assertEquals($report->getGoodProxiesCount(self::URL), 1);
        $this->assertEquals($report->getBadProxiesCount(self::URL), 1);
    }

    public function testReportException()
    {
        $motorMock = $this->createMock(Motor::class);
        $motorMock->method('download')
            ->will($this->onConsecutiveCalls(
                $this->throwException(new NotFoundException()),
                $this->throwException(new ConnectionException()),
            ));
        $site = new Site(self::URL);
        $pylesos = new Pylesos(
            $motorMock,
            new ProxyRotator([]),
            new UserAgentRotator([]),
            false
        );
        $pylesos->setAttemptsLimit(2);
        $pylesos->download(self::URL);
        $report = $pylesos->getReport();
        $this->assertEquals(
            get_class($report->getException()),
            get_class(new App\Library\Pylesos\Exception())
        );
    }
}
