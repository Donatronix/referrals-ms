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
     *     tags={"Webhooks"},
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
            $query = User::query()->where('referrer_id', $id);
            $userTotalInvitees = $query->count();
            $invitees = $query->select('id')
                ->get(['id'])
                ->pluck('id')
                ->toArray();

            $userPlatforms = DB::table('application_user')
                ->whereIn('user_id', $invitees)->select('application_id')
                ->get(['application_id'])->unique('application_id');

            $overviewEarnings = [];

            //get subtotal of number of platform users
            $totalPlatformInvitedUsers = 0;

            foreach ($userPlatforms as $platform) {
                $platform = $platform->platform;
                $userCount = DB::table('application_user')
                    ->where('application_id', $platform)
                    ->whereIn('user_id', $invitees)
                    ->count();

                $totalPlatformInvitedUsers += $userCount;

                $overviewEarnings[] = [
                    'platform' => $platform,
                    'users_count' => $userCount,
                ];
            }

            return response()->jsonApi([
                'title' => 'Retrieval success',
                'message' => 'The platform earnings were successfully retrieved',
                'data' => [
                    'overview_earnings' => $overviewEarnings,
                    'subTotalPlatformInvitedUsers' => $totalPlatformInvitedUsers,
                    'subTotalEarnings' => $totalPlatformInvitedUsers,
                ],
            ]);
        } catch (Throwable $e) {
            return response()->jsonApi([
                'title' => "Not operation",
                'message' => $e->getMessage(),
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
