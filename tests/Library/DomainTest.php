<?php

use Laravel\Lumen\Testing\DatabaseMigrations;

class DomainTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * A basic test example.
     *
     * @return void
     */
    public function testDomains()
    {
        $urls = [
            'https://google.com/',
            'http://www.google.com',
            'google.com',
            'www.google.com',
            'google.com/',
            'google.com?query=123',
        ];
        $domains = [];
        foreach ($urls as $url) {
            $domains[] = (new \App\Library\Domain($url));
        }

        $this->assertEquals(count(array_unique($domains)), 1);
    }
}
