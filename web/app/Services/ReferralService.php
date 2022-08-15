<?php

namespace App\Services;

use App\Models\Total;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Log;

class ReferralService
{
    /**
     * Find user in referral program
     * if not exist, then register a new user
     *
     * @param string $user_id
     *
     * @return mixed
     * @throws Exception
     */
    public static function getUser(string $user_id): mixed
    {
        // Try find / create user by id
        try {
            // Checking a user in the referral program
            $user = User::find($user_id);

            // If not exist, then create a new user
            if (!$user) {
                $user = User::create([
                    'id' => $user_id,
                ]);
            }

            return $user;
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Updating the referral tree.
     * Adding an inviter to a new user
     *
     * @param User $user
     * @param string|null $parent_user_id
     *
     * @return User
     * @throws Exception
     */
    public static function setInviter(User $user, string $parent_user_id = null): User
    {
        // Checking the presence of a user in the referral program
        try {
            // Checking if the user has an inviter / sponsor
            // If not, then set the inviter / sponsor
            if ($user->referrer_id === null) {
                $country = null;
                $ip = request()->ip();

                if ($position = json_decode(file_get_contents("http://ipinfo.io/{$ip}/json"))) {
                    // Successfully retrieved position.
                    $country = $position->country ?? null;
                }

                $user->referrer_id = $parent_user_id;
                $user->country = $country ?? 'Nigeria';
                $user->save();

                // Updating the statistics for the invitee.
                // Adding an Inviter to the Leaderboard
                Total::where('user_id', $parent_user_id)->first()->increment('amount');
            }

            // We send data to the membership microservice for information
            // about the tariff plan and reward for the inviting user
            //\PubSub::publish('getDataAboutPlanAndReward', $user->id, config('pubsub.queue.memberships'));

            return $user;
        } catch (Exception $e) {
            throw $e;
        }
    }
}
