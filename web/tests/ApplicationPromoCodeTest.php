<?php

namespace Tests;

use Laravel\Lumen\Testing\WithoutMiddleware;

class ApplicationPromoCodeTest extends TestCase
{
    use WithoutMiddleware;

    /**
     * A basic test example.
     *
     * @return void
     */
    public function testGenerate()
    {
        $id = '20000000-2000-2000-2000-000000000002';
        $response = $this->get('v1/promo-codes/generate?user_id=' . $id, [
            'user-id' => '20000000-2000-2000-2000-000000000002',
        ])
            ->seeStatusCode(200)
            ->seeJson(['type' => 'success']);

//        dd($response->response->getContent());
    }

    /**
     * Validate promo code.
     *
     * @return void
     */
    public function testValidate()
    {
        $id = '20000000-2000-2000-2000-000000000002';
        $response = $this->post('v1/promo-codes/validate', [
            'code' => '21918',
        ], [
            'user-id' => '20000000-2000-2000-2000-000000000002',
        ])
            ->seeStatusCode(200)
            ->seeJson(['type' => 'success']);

        dd($response->response->getContent());
    }
}
