<?php

namespace App\Api\V1\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Services\ReferralCodeService;
use Carbon\Carbon;

class TestController extends Controller
{
    public function test()
    {
//        $today = getdate();

        $date = Carbon::now();
        dd($date->toArray());
        dd(Carbon::now());
        dd($today->format('Y-m-d'));

        $uploads = Transaction::where('created_at', '>=', \Carbon\Carbon::now()->subWeek())->get();

        foreach($uploads as $date)
        {
            echo $date . '<br>';
        }
//        dd($data);
    }
}
