<?php

use App\Library\Site;
use App\Library\UserAgentRotator;
use Laravel\Lumen\Testing\DatabaseMigrations;

class UserAgentRotatorTest extends TestCase
{
    use DatabaseMigrations;

    const URL = 'http://google.com';

    const USERS_AGENTS = [
        'UserAgent1',
        'UserAgent2',
    ];

    private string $domain = 'google.com';

    public function setUp(): void
    {
        parent::setUp();
        foreach (self::USERS_AGENTS as $userAgent) {
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
    public function testRotation()
    {
        $site = new Site(self::URL);
        $rotator = new UserAgentRotator(
            UserAgentRotator::findLiveUsersAgents($site->getId())
        );
        $this->assertEquals($rotator->getLiveUserAgent(), self::USERS_AGENTS[0]);
        $this->assertEquals($rotator->getRowsCount(), count(self::USERS_AGENTS));
        $rotator->skip();
        $this->assertEquals($rotator->getLiveUserAgent(), self::USERS_AGENTS[1]);
        $this->assertEquals($rotator->getRowsCount(), count(self::USERS_AGENTS) - 1);
        $rotator->skip();
        $this->assertEquals($rotator->getLiveUserAgent(), '');
    }
}
