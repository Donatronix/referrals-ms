<?php

namespace Tests;

use App\Api\V1\Controllers\Admin\SummaryController;

class LeaderboardControllerTest extends TestCase
{

    /**
     * A basic test example.
     *
     * @return void
     */
    public function testSummaryListing()
    {
        $ummary = new SummaryController();

        $listing = $ummary->listing();
        dd($listing);


    }
}
