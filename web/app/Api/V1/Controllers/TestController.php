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
        $data = Transaction::getDataForDate('80000008-8008-8008-8008-800000000008', 'week');
        dd($data);
    }
}
