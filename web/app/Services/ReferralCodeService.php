<?php

namespace App\Services;

use App\Exceptions\ReferralCodeLimitException;
use App\Models\ReferralCode;
use App\Models\User;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

class ReferralCodeService
{
    /**
     * Create a referral code
     *
     * @param Request $request
     * @param User $user
     * @return mixed
     * @throws ReferralCodeLimitException
     * @throws Exception
     */
    public static function createReferralCode(Request $request, User $user): mixed
    {
        // Check amount generated codes for current user
        $codesList = ReferralCode::byOwner()->byApplication()->get();
        $codesTotal = $codesList->count();

        if ($codesTotal >= config('settings.referral_code.limit')) {
            throw new ReferralCodeLimitException(sprintf("You can generate up to %s codes for the current service", config('settings.referral_code.limit')));
        }

        try {
            // Detect code is_default
            $is_default = false;
            if($request->has('is_default')){
                $is_default = $request->boolean('is_default', false);
            }

            // Correcting is_default if $codesTotal === 0
            if($codesTotal === 0){
                $is_default = true;
            }

            // Check if code is set as default, then reset all previous code
            if ($is_default && $codesTotal > 0) {
                self::defaultReset($user->id, null, $codesList);
            }

            // Create new referral code
            $rc = ReferralCode::query()->create([
                'user_id' => $user->id,
                'application_id' => $request->get('application_id'),
                'link' => 'link' . random_int(1, 1000),
                'is_default' => $is_default,
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
     * @param null $list
     */
    public static function defaultReset(string $user_id, string $application_id = null, Collection $list = null): void
    {
        if(!$list){
            $list = ReferralCode::byApplication($application_id)->byOwner($user_id)->get();
        }

        $list->each->update(['is_default' => false]);
    }
}
