<?php


namespace App\Services;

use Illuminate\Http\Request;
use App\Models\ReferralCode;

class RemoteService
{
    public function getReferralLinkForRemoteServices (Request $request)
    {
        try{
            $referral_data = ReferralCode::where('id');
        }
        catch (\Exception $e){
            return response()->jsonApi([
                'type' => 'danger',
                'title' => 'Not received list',
                'message' => "Data of referral code not found",
                'data' => null
            ], 404);
        }
    }
}
