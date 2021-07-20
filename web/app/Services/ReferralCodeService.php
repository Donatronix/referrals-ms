<?php

namespace App\Services;

use App\Models\ReferralCode;

class ReferralCodeService
{
    public static function createReferralCode($data)
    {
        $rc = ReferralCode::create([
            'application_id' => $data['application_id'],
            'user_id' => (string)$data['user_id'],
            'referral_link' => 'link' . rand(1, 1000),
            'is_default' => $data['is_default']
        ]);

        $generate_link = (string)Firebase::linkGenerate($rc->code, $data['application_id']);
        $rc->update(['referral_link' => $generate_link]);

        return $rc;
    }
}
