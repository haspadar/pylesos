<?php

use App\Library\Motor;
use App\Library\Motor\NotFoundException;
use App\Library\Proxy;
use App\Library\ProxyRotator;
use App\Library\Pylesos;
use App\Library\Report;
use App\Library\UserAgentRotator;
use Laravel\Lumen\Testing\DatabaseMigrations;

class ReportTest extends TestCase
{
    use DatabaseMigrations;

    const URL = 'https://google.com';

    const DOMAIN = 'google.com';

    const SUCCESS_RESPONSE = 'Success';

    public function testConnections()
    {
        $motorMock = $this->createMock(Motor::class);
        $exception = new NotFoundException();
        $motorMock->method('download')
            ->will($this->onConsecutiveCalls(
                $this->throwException($exception),
                self::SUCCESS_RESPONSE
            ));
        $pylesos = new Pylesos(
            $motorMock,
            new ProxyRotator([
                new Proxy('1.1.1.1:80'),
                new Proxy('2.2.2.2:80')
            ]),
            new UserAgentRotator(['UserAgent1', 'UserAgent2']),
            new Report(self::URL)
        );
        $pylesos->download(self::URL, false);
        $report = $pylesos->getReport();
        $this->assertEquals(count($report->getConnections()), 2);
        $this->assertEquals(
            $report->getConnections()[0]->getException(),
            $exception
        );
    }
}
