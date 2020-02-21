<?php

use Laravel\Lumen\Testing\DatabaseMigrations;

class UserAgentRotatorTest extends TestCase
{
    use DatabaseMigrations;

    private $usersAgents = [
        'Mozilla/5.0 (iPhone; CPU iPhone OS 12_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Mobile/15E148',
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/74.0.3729.169 Safari/537.36',
    ];

    private $domain = 'google.com';

    public function setUp(): void
    {
        parent::setUp();
        foreach ($this->usersAgents as $userAgent) {
            DB::table('users_agents')->insert([
                'user_agent' => $userAgent
            ]);
        }
    }

    /**
     * A basic test example.
     *
     * @return void
     */
    public function testNext()
    {
        $rotator = new \App\Library\UserAgentRotator('http://google.com');
        $this->assertEquals($rotator->getLiveUserAgent(), $this->usersAgents[0]);
        $this->assertEquals($rotator->getUsersAgentsCount(), count($this->usersAgents));
        $rotator->blockUserAgent($rotator->getLiveUserAgent());
        $this->assertEquals($rotator->getLiveUserAgent(), $this->usersAgents[1]);
        $this->assertEquals($rotator->getUsersAgentsCount(), count($this->usersAgents) - 1);
    }

    public function testCircle()
    {
        $rotator = new \App\Library\UserAgentRotator('http://google.com');
        $rotator->blockUserAgent($rotator->getLiveUserAgent());
        $rotator->blockUserAgent($rotator->getLiveUserAgent());
        $this->assertEquals($rotator->getLiveUserAgent(), $this->usersAgents[0]);
        $this->assertEquals($rotator->getUsersAgentsCount(), count($this->usersAgents));
    }
}
