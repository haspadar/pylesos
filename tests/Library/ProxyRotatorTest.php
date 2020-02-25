<?php

use Laravel\Lumen\Testing\DatabaseMigrations;

class ProxyRotatorTest extends TestCase
{
    use DatabaseMigrations;

    const URL = 'http://google.com';

    const PROXIES = [
        ['1.1.1.1:80', 'https'],
        ['2.2.2.2:8080', 'https'],
        ['3.3.3.3:3128', 'https']
    ];

    private $domain = 'google.com';

    public function setUp(): void
    {
        parent::setUp();
        foreach (self::PROXIES as list($proxy, $protocol)) {
            DB::insert('insert into proxies (address, protocol) values (?, ?)', [$proxy, $protocol]);
        }
    }

    /**
     * A basic test example.
     *
     * @return void
     */
    public function testNext()
    {
        $rotator = new \App\Library\ProxyRotator(new \App\Library\Site(self::URL));
        $this->assertEquals($rotator->getLiveProxy()->getAddress(), self::PROXIES[0][0]);
        $this->assertEquals($rotator->getProxiesCount(), count(self::PROXIES));
        $rotator->blockProxy();
        $this->assertEquals($rotator->getLiveProxy()->getAddress(), self::PROXIES[1][0]);
        $this->assertEquals($rotator->getProxiesCount(), count(self::PROXIES) - 1);
    }

    public function testCircle()
    {
        $rotator = new \App\Library\ProxyRotator(new \App\Library\Site(self::URL));
        $rotator->blockProxy();
        $rotator->blockProxy();
        $rotator->blockProxy();
        $this->assertEquals($rotator->getLiveProxy()->getAddress(), self::PROXIES[0][0]);
        $this->assertEquals($rotator->getProxiesCount(), count(self::PROXIES));
    }
}
