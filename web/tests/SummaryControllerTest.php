<?php

namespace Tests;

class SummaryControllerTest extends TestCase
{

    /**
     * A basic test example.
     *
     * @return void
     */
    public function testSummaryListing()
    {
        $this->withoutMiddleware();

        $summary = $this->get('/v1/admin/summary-listing', [
            'user-id' => 1,
        ]);

        $summary->seeStatusCode(200)
            ->seeJson(['success' => true])
            ->response
            ->getContent();


    }
}
