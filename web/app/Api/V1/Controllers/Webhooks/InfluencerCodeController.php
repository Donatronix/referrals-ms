<?php

namespace App\Api\V1\Controllers\Webhooks;

use App\Api\V1\Controllers\Controller;
use App\Services\ReferralCodeService;
use App\Services\ReferralService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Throwable;

class InfluencerCodeController extends Controller
{
    public function getInflunecerCode(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|string',
                'application_id' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->jsonApi([
                    'title' => 'Influencer code',
                    'message' => 'Input data validation error',
                    'data' => $validator->errors()
                ], 422);
            }

            $newUser = ReferralService::getUser($request->user_id);
            // Try to create new referral code with link
            $influencerCode = ReferralCodeService::createReferralCode($request, $newUser);

            return response()->jsonApi([
                'title' => 'Influencer code',
                'message' => 'Influencer code successfully generated',
                'data' => $influencerCode,
            ]);
        } catch (Throwable $e) {
            return response()->jsonApi([
                'title' => "Not operation",
                'message' => $e->getMessage(),
            ], 404);
        }
    }
}
