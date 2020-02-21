<?php

use Laravel\Lumen\Testing\DatabaseMigrations;

class ProxyRotatorTest extends TestCase
{
    use DatabaseMigrations;

    private $proxies = [
        ['1.1.1.1:80', 'https'],
        ['2.2.2.2:8080', 'https'],
        ['3.3.3.3:3128', 'https']
    ];

    private $domain = 'google.com';

    public function setUp(): void
    {
        parent::setUp();
        foreach ($this->proxies as list($proxy, $protocol)) {
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
        $rotator = new \App\Library\ProxyRotator('http://google.com');
        $this->assertEquals($rotator->getLiveProxy()->getAddress(), $this->proxies[0][0]);
        $this->assertEquals($rotator->getProxiesCount(), count($this->proxies));
        $rotator->blockProxy($rotator->getLiveProxy());
        $this->assertEquals($rotator->getLiveProxy()->getAddress(), $this->proxies[1][0]);
        $this->assertEquals($rotator->getProxiesCount(), count($this->proxies) - 1);
    }

    public function testCircle()
    {
        $rotator = new \App\Library\ProxyRotator('http://google.com');
        $rotator->blockProxy($rotator->getLiveProxy());
        $rotator->blockProxy($rotator->getLiveProxy());
        $rotator->blockProxy($rotator->getLiveProxy());
        $this->assertEquals($rotator->getLiveProxy()->getAddress(), $this->proxies[0][0]);
        $this->assertEquals($rotator->getProxiesCount(), count($this->proxies));
    }
}
