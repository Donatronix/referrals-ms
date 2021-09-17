<?php

namespace App\Api\V1\Controllers;

use App\Http\Controllers\Controller;
use App\Services\ReferralCodeService;

class TestController extends Controller
{
    public function test()
    {
        $data = [
            "user1" => "1000",
            "user2" => "942a6c6f-ef44-4f98-9264-1f03f5f664dc"
        ];
        ReferralCodeService::addUniqueUser($data);
    }
}
