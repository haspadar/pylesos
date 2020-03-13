<?php

use App\Console\Commands\DownloadProxies;
use App\Library\ProxyRotator;
use App\Library\Site;
use Laravel\Lumen\Testing\DatabaseMigrations;

class ProxyRotatorTest extends TestCase
{
    use DatabaseMigrations;

    const URL = 'http://google.com';

    const PROXIES = [
        '1.1.1.1:80',
        '2.2.2.2:8080',
    ];

    private $domain = 'google.com';

    public function setUp(): void
    {
        parent::setUp();
        foreach (self::PROXIES as $proxy) {
            DB::insert('insert into proxies (address, protocol) values (?, ?)', [$proxy, 'http']);
        }
    }

    /**
     * A basic test example.
     *
     * @return void
     */
    public function testRotation()
    {
        $site = new Site(self::URL);
        $rotator = new ProxyRotator(DownloadProxies::findLiveProxies($site));
        $this->assertEquals($rotator->getRow()->getAddress(), '');
        $this->assertEquals($rotator->getRowsCount(), count(self::PROXIES) + 1);
        $rotator->skip();
        $randomProxies = self::PROXIES;
        $this->assertEquals($rotator->getRowsCount(), count($randomProxies));
        $rotator->skip();
        $this->assertTrue(in_array($rotator->getRow()->getAddress(), $randomProxies));
        unset($randomProxies[array_search($rotator->getRow()->getAddress(), $randomProxies)]);
        $this->assertEquals($rotator->getRowsCount(), count($randomProxies));
        $rotator->skip();
        $this->assertNull($rotator->getRow());
    }
}
