<?php

namespace App\Api\V1\Controllers\Webhooks;

use App\Api\V1\Controllers\Controller;
use App\Models\ReferralCode;
use App\Models\Total;
use App\Models\User;
use App\Services\RemoteService;
use App\Traits\LeaderboardTrait;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class LeaderboardController extends Controller
{
    use LeaderboardTrait;

    /**
     *  A list of leaders in the invitation referrals
     *
     * @OA\Get(
     *     path="/leaderboard",
     *     description="A list of leaders in the invitation referrals",
     *     tags={"Application Leaderboard"},
     *
     *     security={{
     *         "default" :{
     *             "ManagerRead",
     *             "User",
     *             "ManagerWrite"
     *         }
     *     }},
     *
     *     x={
     *         "auth-type": "Application & Application User",
     *         "throttling-tier": "Unlimited",
     *         "wso2-application-security": {
     *             "security-types": {"oauth2"},
     *             "optional": "false"
     *         }
     *     },
     *
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         description="Limit leaderboard of page",
     *         @OA\Schema(
     *             type="number"
     *         ),
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Count leaderboard of page",
     *         @OA\Schema(
     *             type="number"
     *         ),
     *     ),
     *     @OA\Parameter(
     *         name="graph_filtr",
     *         in="query",
     *         description="Sort option for the graph. Possible values: week, month, year",
     *         @OA\Schema(
     *             type="string",
     *         ),
     *     ),
     *
     *     @OA\Response(
     *         response="200",
     *         description="TOP 1000 of leaders in the invitation referrals",
     *
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="links",
     *                 type="object",
     *                 @OA\Property(
     *                     property="id",
     *                     type="string",
     *                     description="user uuid",
     *                     example="fd069ebe-cdea-3fec-b1e2-ca5a73c661fc",
     *                 ),
     *                 @OA\Property(
     *                     property="user_id",
     *                     type="string",
     *                     description="user id",
     *                     example="edd72fdb-c83c-3e27-9047-d840e8745c61",
     *                 ),
     *                 @OA\Property(
     *                     property="username",
     *                     type="string",
     *                     description="username",
     *                     example="Lonzo",
     *                 ),
     *                 @OA\Property(
     *                      property="amount",
     *                      type="integer",
     *                      description="Number of invited users",
     *                      example=100,
     *                 ),
     *                 @OA\Property(
     *                      property="reward",
     *                      type="double",
     *                      description="Amount of remuneration",
     *                      example=50.50,
     *                 ),
     *                 @OA\Property(
     *                      property="is_current",
     *                      type="boolean",
     *                      description="Determine the user who made the request",
     *                 ),
     *             ),
     *             @OA\Property(
     *                 property="informer",
     *                 type="object",
     *                 @OA\Property(
     *                      property="rank",
     *                      type="integer",
     *                      description="User rating place",
     *                      example=1000000000,
     *                 ),
     *                 @OA\Property(
     *                      property="reward",
     *                      type="integer",
     *                      description="How much user earned",
     *                      example=7,
     *                 ),
     *                 @OA\Property(
     *                      property="growth_this_month",
     *                      type="integer",
     *                      description="",
     *                      example=100000,
     *                 ),
     *             ),
     *             @OA\Property(
     *                 property="leaderboard",
     *                 type="object",
     *                 @OA\Property(
     *                      property="rank",
     *                      type="integer",
     *                      description="User ranking place",
     *                      example=1,
     *                 ),
     *                  @OA\Property(
     *                      property="name",
     *                      type="string",
     *                      description="User name",
     *                      example="Vsaya",
     *                 ),
     *                  @OA\Property(
     *                      property="channels",
     *                      type="string",
     *                      description="Platform used for referral",
     *                      example="WhatsApp",
     *                 ),
     *                 @OA\Property(
     *                      property="invitees",
     *                      type="integer",
     *                      description="Number of invited users",
     *                      example=10,
     *                 ),
     *                 @OA\Property(
     *                      property="Country",
     *                      type="string",
     *                      description="User country",
     *                      example="Ukraine",
     *                 ),
     *                 @OA\Property(
     *                      property="growth_this_month",
     *                      type="integer",
     *                      description="",
     *                      example=100000,
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
     * @param Request $request
     *
     * @return mixed
     */
    public function index(Request $request): mixed
    {
        try {
            return response()->jsonApi([
                'type' => 'success',
                'title' => 'Retrieval success',
                'message' => 'Leaderboard successfully generated',
                'data' => $this->leaderboard($request),

            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->jsonApi([
                'type' => 'danger',
                'title' => "Not operation",
                'message' => "Error showing all users",
                'data' => null,
            ], 404);
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
     *  Get platform earnings for user
     *
     * @OA\Get(
     *     path="/invited-users/{id}",
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
     *     x={
     *         "auth-type": "Application & Application User",
     *         "throttling-tier": "Unlimited",
     *         "wso2-application-security": {
     *             "security-types": {"oauth2"},
     *             "optional": "false"
     *         }
     *     },
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
                ->select('application_id', DB::raw('COUNT(user_id) as totalPlatformInvitedUsers'))
                ->groupBy('application_id');

            $totalInvitees = DB::table('users')
                ->select(DB::raw('COUNT(id) as totalUsers'))
                ->groupBy('application_id')
                ->get();

            //get platforms
            $query = DB::table('referral_codes')->where('user_id', $id)
                ->select(
                    'application_id',
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
     * @param $input_data
     *
     * @return bool
     */
    public function checkRemoteServices($input_data): bool
    {
        // This is demo data for the test. By connecting them, you don't need a remote microservice.
        // $input_data = return [
        //            "id" => "2561dbee-2207-30ff-9241-b1b5ee79a03d",
        //            "program_type_id" => 1,
        //            "enabled" => 0,
        //            "level_id" => "a39b4c05-ed3f-39e5-91da-53cdbcb98a75",
        //            "created_at" => "2021-09-02T10:02:35.000000Z",
        //            "updated_at" => "2021-09-02T10:02:35.000000Z",
        //            "program_type" => [
        //                "id" => 1,
        //                "name" => "pioneer",
        //                "key" => "pioneer",
        //                "created_at" => "2021-09-02T10:02:35.000000Z",
        //                "updated_at" => "2021-09-02T10:02:35.000000Z",
        //            ],
        //            "level" => [
        //                "id" => "a39b4c05-ed3f-39e5-91da-53cdbcb98a75",
        //                "name" => "bronze",
        //                "price" => 99.0,
        //                "currency" => "BDT",
        //                "period" => "month",
        //                "program_type_id" => 1,
        //            ],
        //            "key" => "pioneer.get_give",
        //            "title" => "For each Referral you get $8. Your referred contacts give $5. Earn Unlimited",
        //            "value" => [
        //                0 => 8,
        //                1 => 5,
        //            ],
        //            "format" => "$",
        //        ];

        return RemoteService::accrualRemuneration($input_data);
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
