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

                Log::info('New user added successfully in referral program');
            } else {
                Log::info('The current user is already a member of the referral program');
            }

            return $user;
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * Updating the referral tree.
     * Adding an inviter to a new user
     *
     * @param User $newUser
     * @param string|null $parent_user_id
     *
     * @return User
     * @throws Exception
     */
    public static function setInviter(User $newUser, string $parent_user_id = null): User
    {
        // Checking the presence of a user in the referral program
        try {
            // Checking if the user has an inviter / sponsor
            // If not, then set the inviter / sponsor
            if ($newUser->referrer_id === null) {
                $country = null;
                $ip = request()->ip();

                if ($position = json_decode(file_get_contents("http://ipinfo.io/{$ip}/json"))) {
                    // Successfully retrieved position.
                    $country = $position->country ?? null;
                }

                $newUser->referrer_id = $parent_user_id;
                $newUser->country = $country ?? 'Nigeria';
                $newUser->save();

                Total::where('user_id', $parent_user_id)->first()->increment('amount');

                Log::info('The user was successfully added to their inviter');
            } else {
                Log::info('The user already has an inviter');
            }

            // Updating the statistics for the invitee.
            // Adding an Inviter to the Leaderboard


            // We send data to the membership microservice for information
            // about the tariff plan and reward for the inviting user
//            RemoteService::sendData('getDataAboutPlanAndReward', $newUser->id, 'Membership');

            return $newUser;
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
}


################## EXAMPLE CODE #######################
/**
 * For wallet microservice
 */
/*
$array = [
    'user_id' => $appUserId,
    'status' => Arr::random([
        User::STATUS_APPROVED,
        User::STATUS_NOT_APPROVED,
        User::STATUS_BLOCKED
    ])
];
PubSub::transaction(function() {})->publish('ReferralBonus', $array, 'referral');
*/
################## EXAMPLE CODE #######################
