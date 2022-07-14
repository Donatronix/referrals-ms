<?php

namespace Tests;

use Laravel\Lumen\Testing\WithoutMiddleware;

class ApplicationSummaryControllerTest extends TestCase
{
    use WithoutMiddleware;

    /**
     * Test summary controller in Application namespace
     *
     * @return void
     */
    public function testApplicationSummaryIndex()
    {
        $myIndex = $this->get('/v1/summary', [
            'user-id' => '40000004-4004-4004-4004-400000000004',
        ]);

        $myIndex->seeStatusCode(200)
            ->seeJson(['type' => 'success'])
            ->response
            ->getContent();

//        dd($summary->response->getContent());
    }
}
