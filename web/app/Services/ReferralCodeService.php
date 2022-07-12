<?php

namespace App\Services;

use App\Models\ReferralCode;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReferralCodeService
{
    /**
     * Create a referral code
     *
     * @param Request $request
     * @param User|null $user
     * @param bool $is_default
     *
     * @return mixed
     * @throws Exception
     */
    public static function createReferralCode(Request $request, User $user = null, bool $is_default = false): mixed
    {
        try {

            // Check user object
            if (!$user) {
                $user = Auth::user() ?? User::find(Auth::user()->getAuthIdentifier() ?? $request->get('user_id'));
            }

            // Check if code is set as default, then reset all previous code
            if ($is_default) {
                self::defaultReset($user->id);
            }

            // Create new referral code
            $rc = ReferralCode::create([
                'user_id' => Auth::user()->getAuthIdentifier(),
                'application_id' => $request->get('application_id'),
                'link' => 'link' . rand(1, 1000),
                'is_default' => $request->boolean('is_default', $is_default),
                'note' => $request->get('note', null),
            ]);

            // $generate_link = (string)Firebase::linkGenerate($rc->code, $request->get('application_id'));
            // $rc->update(['link' => $generate_link]);

            return $rc;
        } catch (Exception $e) {
            throw new Exception("There was an error while creating a referral code: " . $e->getMessage());
        }
    }

    /**
     * Reset all default codes by user and application
     *
     * @param             $user_id
     * @param string|null $application_id
     *
     * @return null
     */
    public static function defaultReset($user_id, string $application_id = null)
    {
        $list = ReferralCode::byApplication($application_id)->byOwner($user_id)->get();
        $list->each->update(['is_default' => false]);

        return null;
    }
}
