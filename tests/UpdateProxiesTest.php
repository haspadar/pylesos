<?php

use App\Library\Proxy;
use App\Library\Services\ProxiesSitesList;
use App\Library\Services\SiteWithParseProxies;
use Laravel\Lumen\Testing\DatabaseMigrations;

class UpdateProxiesTest extends TestCase
{
    use DatabaseMigrations;

    public function testCommand()
    {
        $sitesListMock = $this->createMock(ProxiesSitesList::class);
        $proxies = [
            new Proxy('1.1.1.1:8080', 'https'),
            new Proxy('1.1.1.2:3128', 'https'),
        ];
        $siteMock = $this->createMock(SiteWithParseProxies::class);
        $siteMock->method('downloadProxies')
            ->willReturn($proxies);
        $siteMock->method('getDomain')
            ->willReturn('google.com');
        $sitesListMock->method('getSites')
            ->willReturn([$siteMock]);
        $this->app->instance(ProxiesSitesList::class, $sitesListMock);
        $returnCode = $this->artisan('proxies:download');
        $this->assertEquals(0, $returnCode);
        foreach ($proxies as $proxy) {
            $this->seeInDatabase('proxies', [
                'address' => $proxy->getAddress(),
                'protocol' => $proxy->getProtocol(),
            ]);
        }
    }
}
