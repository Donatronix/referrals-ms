<?php

namespace Tests;

use Laravel\Lumen\Testing\WithoutMiddleware;

class AdminSummaryControllerTest extends TestCase
{
    use WithoutMiddleware;

    /**
     * A basic test example.
     *
     * @return void
     */
    public function testAdminSummaryListing()
    {

        $summary = $this->get('/v1/admin/summary-listing', [
            'user-id' => '20000000-2000-2000-2000-000000000002',
        ]);

        $summary->seeStatusCode(200)
            ->seeJson(['success' => true])
            ->response
            ->getContent();

    }

}
