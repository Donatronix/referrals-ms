<?php

namespace Tests;

use Laravel\Lumen\Testing\WithoutMiddleware;

class ApplicationReferralCodeTest extends TestCase
{
    use WithoutMiddleware;

    /**
     * Test index.
     *
     * @return void
     */
    public function testReferralCodeIndex()
    {
        $response = $this->get('v1/referral-codes', [
            'user-id' => '20000000-2000-2000-2000-000000000002',
        ])
            ->seeStatusCode(200)
            ->seeJson(['type' => 'success']);

//        dd($response->response->getContent());
    }

    /**
     * Test user.
     *
     * @return void
     */
    public function testGetDataByUser()
    {
        $response = $this->get('v1/referral-codes', [
            'user-id' => '20000000-2000-2000-2000-000000000002',
        ])
            ->seeStatusCode(200)
            ->seeJson(['type' => 'success']);

//        dd($response->response->getContent());
    }

    /**
     * Test user.
     *
     * @return void
     */
    public function testReferralCodeStore()
    {
        $response = $this->post('v1/referral-codes', [
            'application_id' => 'whatsapp',
            'is_default' => 1,
            'note' => 'Created during test',
        ], [
            'user-id' => '20000000-2000-2000-2000-000000000002',
        ])
            ->seeStatusCode(200)
            ->seeJson(['type' => 'success']);

//        dd($response->response->getContent());
    }
}
