<?php

namespace App\Services;

use App\Models\ReferralCode;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class ReferralCodeService
{
    public static function createReferralCode($data)
    {
        $userId = Auth::user()->getAuthIdentifier();

        // Check if code is set as default, then reset all previous code
        if ($data['is_default']) {
            self::defaultReset($data['application_id'], $userId);
        }

        // Create new referral code
        $rc = ReferralCode::create([
            'application_id' => $data['application_id'],
            'user_id' => $userId,
            'link' => 'link' . rand(1, 1000),
            'is_default' => $data['is_default'] ?? false,
            'note' => $data['note'] ?? null
        ]);

        $generate_link = (string)Firebase::linkGenerate($rc->code, $data['application_id']);
        $rc->update(['link' => $generate_link]);

        return $rc;
    }

    /**
     * Reset all default codes by user and application
     *
     * @param $application_id
     * @param $user_id
     *
     * @return null
     */
    public static function defaultReset($application_id, $user_id){
        $list = ReferralCode::byApplication($application_id)
            ->byOwner($user_id)
            ->get();
        $list->each->update(['is_default' => false]);

        return null;
    }

    /**
     *  Check the invited user for uniqueness
     *
     * @param array | $data
     * @return false | object $output_data
     */
    public static function checkUser($data)
    {
        // trying to search for the invited user in the microservice structure
        $user_info = User::find($data['user2']);

        if($user_info === null)
        {
            $user_info = User::where('referrer_id', $data['user2'])->first();

            if($user_info !== null)
            {
                $output_data = User::create([
                    'id' => $data['user1'],
                    'referrer_id' => $data['user2'],
                ]);

                return $output_data;
            }

            $output_data = User::where('id', $data['user1'])->update([
                'referrer_id' => $data['user2'],
            ]);

            return $output_data;
        }

        return false;
    }
}
