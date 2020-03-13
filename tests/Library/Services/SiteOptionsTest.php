<?php

use App\Library\Proxy\Adapters\FreeProxySites;
use App\Library\Proxy\Adapters\ProxySaleCom;
use App\Library\Site;
use Laravel\Lumen\Testing\DatabaseMigrations;

class SiteOptionsTest extends TestCase
{
    use DatabaseMigrations;

    public function testConfig()
    {
        $site = new Site('https://google.com', [
            'SITES' => ['google_com' => [
                'PROXY_ADAPTERS' => 'FreeProxySites,ProxySaleCom'
            ]]
        ]);
        $this->assertInstanceOf(FreeProxySites::class, $site->getProxiesAdapters()[0]);
        $this->assertInstanceOf(ProxySaleCom::class, $site->getProxiesAdapters()[1]);
    }
}
