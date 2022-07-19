<?php

namespace Tests;

use App\Models\Transaction;
use Laravel\Lumen\Testing\WithoutMiddleware;

class AdminTransactionTest extends TestCase
{
    use WithoutMiddleware;

    /**
     * A basic test example.
     *
     * @return void
     */
    public function testIndex()
    {
        $this->get('v1/admin/transactions', [
            'user-id' => '20000000-2000-2000-2000-000000000002',
        ])
            ->seeStatusCode(200)
            ->seeJson(['type' => 'success']);
    }

    /**
     * A test of the show method.
     *
     * @return void
     */
    public function testShow()
    {
        $id = '00cc9c5e-3901-3bd3-ac6d-43dcebcbe802';
        $response = $this->get('v1/admin/transactions/' . $id, [
            'user-id' => '20000000-2000-2000-2000-000000000002',
        ]);
        $response->seeStatusCode(200)
            ->seeJson(['type' => 'success']);
    }

    /**
     * A test of the store method.
     *
     * @return void
     */
    public function testStore()
    {
        $id = '00cc9c5e-3901-3bd3-ac6d-43dcebcbe802';
        $response = $this->post('v1/admin/transactions',
            [
                'user_id' => '96a64f66-39e3-4d1f-8a9b-5a9c9cf3024e',
                'user_plan' => 'gold',
                'reward' => 4,
                'currency' => 'USD',
                'operation_name' => 'store transaction',
            ],
            [
                'user-id' => '20000000-2000-2000-2000-000000000002',
            ]);
        $response->seeStatusCode(200)
            ->seeJson(['type' => 'success']);

//        dd($response->response->getContent());
    }

    /**
     * A test of the update method.
     *
     * @return void
     */
    public function testUpdate()
    {
        $id = '06b4375d-35f7-38a9-8b96-2fc89de44642';
        $response = $this->put('v1/admin/transactions/' . $id,
            [
                'user_id' => '96a64f66-39e3-4d1f-8a9b-5a9c9cf3024e',
                'user_plan' => 'gold',
                'reward' => 4,
                'currency' => 'USD',
                'operation_name' => 'update the transaction',
            ],
            [
                'user-id' => '20000000-2000-2000-2000-000000000002',
            ]);
        $response->seeStatusCode(200)
            ->seeJson(['type' => 'success']);

//        dd($response->response->getContent());
    }

    /**
     * A test of the update method.
     *
     * @return void
     */
    public function testManualUpdate()
    {
        $id = '055a8679-9a21-3d7c-b1bb-a569a78bbf1e';
        $validated = [
            'user_id' => '96a64f66-39e3-4d1f-8a9b-5a9c9cf3024e',
            'user_plan' => 'basic',
            'reward' => 4,
            'currency' => 'USD',
            'operation_name' => 'update transaction',
        ];

        $transaction = Transaction::findOrFail($id);

        $transaction->update($validated);

//        dd($transaction->toArray());
    }

    /**
     * A test of the delete method.
     *
     * @return void
     */
    public function testDelete()
    {
        $id = '00cc9c5e-3901-3bd3-ac6d-43dcebcbe802';
        $response = $this->delete('v1/admin/transactions/' . $id,
            [
                'user-id' => '20000000-2000-2000-2000-000000000002',
            ]);
        $response->seeStatusCode(200)
            ->seeJson(['type' => 'success'])
            ->response
            ->getContent();

        $response->assertResponseOk();

//        dd($response->response->getContent());
    }
}
