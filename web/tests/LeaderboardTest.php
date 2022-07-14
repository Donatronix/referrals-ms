<?php

namespace Tests;

use Laravel\Lumen\Testing\WithoutMiddleware;

class LeaderboardTest extends TestCase
{
    use WithoutMiddleware;

    /**
     * A basic test example.
     *
     * @return void
     */
    public function testLeaderboard(): void
    {
        $this->get('v1/subscribers/leaderboard', [
            'user-id' => '20000000-2000-2000-2000-000000000002',
        ])
            ->seeStatusCode(200)
            ->seeJson(['success' => true]);
    }
}
