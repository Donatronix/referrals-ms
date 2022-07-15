<?php

namespace App\Api\V1\Controllers\Webhooks;

use App\Api\V1\Controllers\Controller;
use App\Models\ReferralCode;
use App\Models\Total;
use App\Models\User;
use App\Traits\LeaderboardTrait;
use Illuminate\Support\Facades\DB;
use Throwable;

class LeaderboardController extends Controller
{
    use LeaderboardTrait;

    /**
     *  Get platform earnings for user
     *
     * @OA\Get(
     *     path="/webhooks/leaderboard/overview-earnings/{id}",
     *     description="A list of leaders in the invitation referrals",
     *     tags={"Platform earnings"},
     *
     *     security={{
     *         "default" :{
     *             "ManagerRead",
     *             "User",
     *             "ManagerWrite"
     *         }
     *     }},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="User id",
     *         @OA\Schema(
     *             type="string"
     *         ),
     *     ),
     *
     *     @OA\Response(
     *         response="200",
     *         description="invited referrals",
     *
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                      property="overview_earnings",
     *                      type="string",
     *                      description="total earnings per platform and number of users",
     *                      example=450000,
     *                 ),
     *                  @OA\Property(
     *                      property="subTotalPlatformInvitedUsers",
     *                      type="integer",
     *                      description="Subtotal of number of platform users",
     *                      example="300",
     *                 ),
     *                 @OA\Property(
     *                      property="subTotalEarnings",
     *                      type="string",
     *                      description="Total earnings on all platforms",
     *                      example="WhatsApp",
     *                 ),
     *             ),
     *         ),
     *     ),
     *     @OA\Response(
     *          response="401",
     *          description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid request"
     *     ),
     *     @OA\Response(
     *          response="404",
     *          description="User not found",
     *     ),
     *     @OA\Response(
     *         response="500",
     *         description="Unknown error"
     *     ),
     * )
     *
     * @param $id
     * @return mixed
     */
    public function getPlatformEarnings($id): mixed
    {
        try {

            //get invited users
            $invitedUsers = DB::table('application_user')
                ->select('application_id', 'user_id', DB::raw('COUNT(user_id) as totalPlatformInvitedUsers'))
                ->groupBy('application_id');

            $totalInvitees = DB::table('users')
                ->select('referrer_id', DB::raw('COUNT(id) as totalInvitees'))
                ->groupBy('referrer_id');

            //get platforms
            $query = DB::table('referral_codes')->where('user_id', $id)
                ->select(
                    'referral_codes.application_id as application_id',
                    'invitedUsers.totalPlatformInvitedUsers as totalPlatformInvitedUsers',
                    'totalInvitees.totalInvitees as totalInvitedUsers',
                )
                ->joinSub($totalInvitees, 'totalInvitees', function ($join) {
                    $join->on('referral_codes.user_id', '=', 'totalInvitees.referrer_id');
                })
                ->joinSub($invitedUsers, 'invitedUsers', function ($join) {
                    $join->on('referral_codes.user_id', '=', 'invitedUsers.user_id');
                })
                ->groupBy(
                    'referral_codes.application_id',
                    'invitedUsers.totalPlatformInvitedUsers',
                    'totalInvitees.totalInvitees'
                );
            $overviewEarnings = $query->get();
            $subTotalPlatformInvitedUsers = $query->sum('totalPlatformInvitedUsers');
            $subTotalEarnings = $query->sum('totalPlatformInvitedUsers');


            return response()->jsonApi([
                'type' => 'success',
                'title' => 'Retrieval success',
                'message' => 'The platform earnings were successfully retrieved',
                'data' => [
                    'overview_earnings' => $overviewEarnings,
                    'subTotalPlatformInvitedUsers' => $subTotalPlatformInvitedUsers,
                    'subTotalEarnings' => $subTotalEarnings,
                ],
            ], 200);
        } catch (Throwable $e) {
            return response()->jsonApi([
                'type' => 'danger',
                'title' => "Not operation",
                'message' => $e->getMessage(),
                'data' => null,
            ], 404);
        }
    }

    /**
     * @param string $country
     * @param string|null $city
     *
     * @return array
     */
    protected function getUserIDByCountryCity(string $country, string $city = null): array
    {
        if ($country and $city != null) {
            //TODO get user id by country and city from identity ms
            return [];
        }
        //TODO get user id by country from identity ms
        return User::whereCountry($country)->get();
    }


    /**
     * @param $user_id
     *
     * @return array|null
     */
    protected function getUserProfile($user_id): array|null
    {
        return User::where('referrer_id', $user_id)->first()->toArray();
    }

    /**
     * @param $referrer
     *
     * @return mixed
     */
    protected function getChannels($referrer)
    {
        $users = User::where('referrer_id', $referrer)->get();
        $users = $users->map(function ($user) {
            return $user->id;
        })->toArray();

        $retVal = ReferralCode::distinct('application_id')->whereIn('user_id', $users)->get(['application_id']);
        return $retVal->map(function ($item) {
            return $item->application_id;
        })->toArray();
    }


    /**
     * @param $referrer_id
     * @param $filter
     *
     * @return int|float
     */
    protected function getTotalReward($referrer_id, $filter): int|float
    {
        return $this->getFilterQuery(Total::where('user_id', $referrer_id), $filter)->sum('reward');
    }
}
