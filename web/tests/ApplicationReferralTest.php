<?php

namespace Tests;

use Laravel\Lumen\Testing\WithoutMiddleware;

class ApplicationReferralTest extends TestCase
{
    use WithoutMiddleware;

    /**
     * Test user.
     *
     * @return void
     */
    public function testReferralIndex()
    {
        $response = $this->get('v1/referrals', [
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
    public function testReferralStore()
    {
        $response = $this->post('v1/referrals', [
            'application_id' => 'whatsapp',
            'referral_code' => 'M47rsWvB',
        ], [
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
    public function testReferralGetWalletTotal()
    {
        $response = $this->get('v1/admin/wallets/total-earnings?user_id=20000000-2000-2000-2000-000000000002',
            [
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
    public function testReferralTotals()
    {
        $response = $this->get('v1/webhooks/total-earnings?user_id=20000000-2000-2000-2000-000000000002',
            [
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
    public function testReferralPlatformEarnings()
    {
        $response = $this->get('v1/webhooks/leaderboard/overview-earnings/20000000-2000-2000-2000-000000000002',
            [
                'user-id' => '20000000-2000-2000-2000-000000000002',
            ])
            ->seeStatusCode(200)
            ->seeJson(['type' => 'success']);

//        dd($response->response->getContent());
    }
}
