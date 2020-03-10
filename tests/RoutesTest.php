<?php

use Laravel\Lumen\Testing\DatabaseMigrations;

class RoutesTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * A basic test example.
     *
     * @return void
     */
    public function testMainPage()
    {
        $this->json('GET', '/download', ['page' => 'google.com'])
            ->seeJson([
                'page' => ['The page format is invalid.'],
            ]);
    }
}
