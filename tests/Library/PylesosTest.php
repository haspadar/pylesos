<?php

use Laravel\Lumen\Testing\DatabaseMigrations;

class PylesosTest extends TestCase
{
    use DatabaseMigrations;

    const URL = 'https://google.com';

    const DOMAIN = 'google.com';

    const SUCCESS_RESPONSE = 'Success';

    public function setUp(): void
    {
        parent::setUp();
        DB::insert('insert into sites (id, url, domain) values (?, ?, ?)', [1, self::URL, self::DOMAIN]);
        DB::insert('insert into proxies (id, address, protocol) values (?, ?, ?)', [1, ProxyRotatorTest::PROXIES[0][0], ProxyRotatorTest::PROXIES[0][1]]);
        DB::insert('insert into sites_proxies (site_id, proxy_id) values (?, ?)', [1, 1]);
        DB::insert('insert into proxies (id, address, protocol) values (?, ?, ?)', [2, ProxyRotatorTest::PROXIES[1][0], ProxyRotatorTest::PROXIES[1][1]]);
        DB::insert('insert into sites_proxies (site_id, proxy_id) values (?, ?)', [1, 2]);

        DB::insert('insert into users_agents (id, user_agent) values (?, ?)', [1, UserAgentRotatorTest::USERS_AGENTS[0]]);
        DB::insert('insert into sites_users_agents (site_id, user_agent_id) values (?, ?)', [1, 1]);
        DB::insert('insert into users_agents (id, user_agent) values (?, ?)', [2, UserAgentRotatorTest::USERS_AGENTS[1]]);
        DB::insert('insert into sites_users_agents (site_id, user_agent_id) values (?, ?)', [1, 2]);
    }

    public function testProxyRotation()
    {
        $motorMock = $this->createMock(\App\Library\Motor::class);
        $motorMock->method('download')
            ->will($this->onConsecutiveCalls(
                $this->throwException(new \App\Library\Motor\NotFoundException()),
                self::SUCCESS_RESPONSE,
            ));
        $site = new \App\Library\Site(self::URL);
        $pylesos = new \App\Library\Pylesos($site, $motorMock);
        $pylesos->setNoFoundRotatesCount(2);
        $pylesos->disableCache();
        $this->assertTrue(true);
        $response = $pylesos->download(self::URL);
        $this->assertEquals($response, self::SUCCESS_RESPONSE);
        $this->assertEquals(
            $pylesos->getClient()->getConfig()['curl'][CURLOPT_PROXY],
            ProxyRotatorTest::PROXIES[1][0]
        );
        $this->assertEquals(
            $pylesos->getClient()->getConfig()['headers']['User-Agent'],
            UserAgentRotatorTest::USERS_AGENTS[1]
        );
    }

    public function testCache()
    {
        $motorMock = $this->createMock(\App\Library\Motor::class);
        $motorMock->method('download')
            ->will($this->onConsecutiveCalls(
                self::SUCCESS_RESPONSE,
                new \App\Library\Motor\NotFoundException(),
                '',
            ));
        $pylesos = new \App\Library\Pylesos(new \App\Library\Site(self::URL), $motorMock);
        for ($attemptId = 0; $attemptId < 3; $attemptId++) {
            $this->assertEquals(
                $pylesos->download(self::URL),
                self::SUCCESS_RESPONSE
            );
        }
    }
}
