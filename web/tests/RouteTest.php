<?php

use Tests\TestCase;

class RouteTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function test_admin_routes_are_protected()
    {
        $this->get('/admin/dashboard')
            ->assertResponseStatus(401);
    }

}
