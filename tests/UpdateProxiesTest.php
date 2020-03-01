<?php

use Laravel\Lumen\Testing\DatabaseMigrations;

class UpdateProxiesTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * A basic test example.
     *
     * @return void
     */
    public function testCommand()
    {
        $sitesListMock = $this->createMock(\App\Library\Services\ProxiesSitesList::class);
        $proxies = [
            new \App\Library\Proxy('1.1.1.1:8080', 'https'),
            new \App\Library\Proxy('1.1.1.2:3128', 'https'),
        ];
        $siteMock = $this->createMock(\App\Library\Services\SiteWithProxies::class);
        $siteMock->method('downloadProxies')
            ->willReturn($proxies);
        $siteMock->method('getDomain')
            ->willReturn('google.com');
        $sitesListMock->method('getSites')
            ->willReturn([$siteMock]);
        $this->app->instance(\App\Library\Services\ProxiesSitesList::class, $sitesListMock);
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
