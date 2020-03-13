<?php

use App\Library\Proxy\Adapters\ProxySaleCom;
use Laravel\Lumen\Testing\DatabaseMigrations;

class ProxySaleComTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * A basic test example.
     *
     * @return void
     */
    public function testConfig()
    {
        $proxies = ProxySaleCom::getProxies([
            'PROXY_SALE_COM_PROXY_LOGIN' => 'login',
            'PROXY_SALE_COM_PROXY_PASSWORD' => 'password',
            'PROXY_SALE_COM_PROXY_PORT' => '3128',
            'PROXY_SALE_COM_PROXY_PROTOCOL' => 'SOCKS5',
            'PROXY_SALE_COM_PROXY_IPS' => '1.1.1.1,2.2.2.2, '
        ]);
        $this->assertCount(2, $proxies);
        $this->assertEquals(CURLPROXY_SOCKS5, $proxies[1]->getCurlProxyType());
        $this->assertEquals('2.2.2.2:3128', $proxies[1]->getAddress());
        $this->assertEquals('login:password', $proxies[1]->getCurlProxyPassword());
    }
}
