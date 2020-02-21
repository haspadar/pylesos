<?php

use Laravel\Lumen\Testing\DatabaseMigrations;

class UpdateUserAgentsTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * A basic test example.
     *
     * @return void
     */
    public function testCommand()
    {
        $mock = $this->createMock(\App\Library\Services\SiteWithUserAgents::class);
        $userAgents = [
            'Mozilla/5.0 (iPhone; CPU iPhone OS 12_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Mobile/15E148' => true,
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/74.0.3729.169 Safari/537.36' => false
        ];
        $mock->method('downloadUserAgents')
            ->willReturn(array_keys($userAgents));
        $this->app->instance(\App\Library\Services\SiteWithUserAgents::class, $mock);
        $returnCode = $this->artisan('users_agents:download');
        $this->assertEquals(0, $returnCode);
        foreach ($userAgents as $userAgent => $isMobile) {
            $this->seeInDatabase('users_agents', [
                'user_agent' => $userAgent,
                'is_mobile' => $isMobile
            ]);
        }
    }
}
