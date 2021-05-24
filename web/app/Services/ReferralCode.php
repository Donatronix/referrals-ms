<?php


namespace App\Services;

use App\Services\Firebase;
use App\Models\ReferralCode;


class ReferralCodeService
{
    public static function createReferralCode($referral_info = [])
    {
        $is_default = $referral_info->is_default ? true : false;
        $data = new ReferralCode();

        ReferralCode::create([
            'user_id' => $referral_info->user_id,
            'application_id' => $referral_info->application_id,
            'referral_link' => (string)Firebase::linkGenerate($data->referral_code, $referral_info->application_id),
            'code' => $data->referral_code,
            'is_default' => $is_default
        ]);
    }
}

/*
 * // Get link by user id and package name
        $link = ReferralCode::where('user_id', $user->id)->where('application_id', $application_id)->limit($link_cnt);


        if (count($link) <= $link_cnt)
        {
            // if count($link) return 0 then this link is default
            if(count($link) == 0) $is_default = true;

            // Create dynamic link from google firebase service
            $shortLink = Firebase::linkGenerate($user->referral_code, $application_id);

            // Add
            $link = ReferralCode::create([
                'user_id' => $user->id,
                'application_id' => $application_id,
                'referral_link' => (string)$shortLink,
                'code' => $user->referral_code,
                'is_default' => $is_default
            ]);
        }
 *
 *
 */
