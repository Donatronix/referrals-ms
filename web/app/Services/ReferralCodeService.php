<?php

namespace App\Services;

use App\Models\ReferralCode;

class ReferralCodeService
{
    public static function createReferralCode($data)
    {
        $rc = ReferralCode::create([
            'user_id' => (string)$data['user_id'],
            'application_id' => $data['application_id'],
            'referral_link' => 'link' . rand(1, 1000),
            'is_default' => $data['is_default']
        ]);

        $generate_link = (string)Firebase::linkGenerate($rc->code, $data['application_id']);
        $rc->update(['referral_link' => $generate_link]);

        $user_info = [
            'user_id' => (integer)$data['user_id'],
            'application_id' => $data['application_id'],
            'referral_code' => $rc->code
        ];

        return $user_info;
    }
}
