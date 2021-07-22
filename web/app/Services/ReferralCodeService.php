<?php

namespace App\Services;

use App\Models\ReferralCode;

class ReferralCodeService
{
    public static function createReferralCode($data)
    {
        // Check if link is default, reset all previous link
        if ($data['is_default']) {
            $list = ReferralCode::where('application_id', $data['application_id'])
                ->where('user_id', $data['user_id'])
                ->get();
            $list->each->update(['is_default' => false]);
        }

        // Create new referral code
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
