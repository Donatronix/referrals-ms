<?php

namespace Tests;

use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function test_that_base_endpoint_returns_a_successful_response()
    {
        $user_id = '00000000-1000-1000-1000-000000000000';
        //        $user_id = AUth::id() ?? Auth::user()->getAuthIdentifier();

        try {
            // we get data for the informer
            $informer = Total::getInformer($user_id);

            dd($informer);

            // collecting an array with data for the graph
            $graph_data = Transaction::getDataForDate($user_id, request()->get('graph_filtr', 'week'));

            $users = Total::orderBy('amount', 'DESC')
                ->orderBy('reward', 'DESC')
                ->paginate(request()->get('limit', config('settings.pagination_limit')));

            $users->map(function ($object) use ($user_id) {
                $isCurrent = false;
                if ($object->user_id == $user_id) {
                    $isCurrent = true;
                }
                $object->setAttribute('is_current', $isCurrent);
                $object->save();
            });

            dd(
                array_merge([
                    'type' => 'success',
                    'title' => 'Updating success',
                    'message' => 'The referral code (link) has been successfully updated',
                    'informer' => $informer,
                    'graph' => $graph_data,
                ], $users->toArray())
            );
        } catch (ModelNotFoundException $e) {
            dd([
                'type' => 'danger',
                'title' => "Not operation",
                'message' => "Error showing all users",
                'data' => null,
            ]);
        }
    }
}
