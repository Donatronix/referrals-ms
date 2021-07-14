<?php


namespace App\Services;

use App\Services\Firebase;
use App\Models\ReferralCode;


class ReferralCodeService
{
    public static function createReferralCode($referral_info)
    {
        $rc = ReferralCode::create([
            'user_id' => (integer)$referral_info['user_id'],
            'application_id' => $referral_info['application_id'],
            'referral_link' => 'link' . rand(1, 1000),
            'is_default' => $referral_info['is_default']
        ]);

        $generate_link = (string)Firebase::linkGenerate($rc->code, $referral_info['application_id']);
        $rc->update(['referral_link' => $generate_link]);

        $user_info = [
            'user_id' => (integer)$referral_info['user_id'],
            'application_id' => $referral_info['application_id'],
            'referral_code' => $rc->code
        ];

        return $user_info;
    }
}
