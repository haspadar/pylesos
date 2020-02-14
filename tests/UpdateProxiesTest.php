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
        $mock = $this->createMock(\App\Library\Services\SiteWithProxies::class);
        $proxies = [
            new \App\Library\Proxy('1.1.1.1:8080', 'https'),
            new \App\Library\Proxy('1.1.1.2:3128', 'https'),
        ];
        $mock->method('downloadSite')
            ->willReturn($proxies);
        $this->app->instance(\App\Library\Services\SiteWithProxies::class, $mock);
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
