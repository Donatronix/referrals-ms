<?php

namespace App\Api\V1\Controllers;

use App\Models\ReferralCode;
use App\Models\Total;
use App\Models\Transaction;
use App\Models\User;
use App\Services\RemoteService;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class LeaderboardController extends Controller
{
    /**
     *  A list of leaders in the invitation referrals
     *
     * @OA\Get(
     *     path="/leaderboard",
     *     description="A list of leaders in the invitation referrals",
     *     tags={"Leaderboard"},
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
     *         description="Limit liderboard of page",
     *         @OA\Schema(
     *             type="number"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Count leaderboard of page",
     *         @OA\Schema(
     *             type="number"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="graph_filtr",
     *         in="query",
     *         description="Sort option for the graph. Possible values: week, month, year",
     *         @OA\Schema(
     *             type="string",
     *         )
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
     *                 )
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
     *                      property="grow_this_month",
     *                      type="integer",
     *                      description="",
     *                      example=100000,
     *                 )
     *             )
     *             @OA\Property(
     *                 property="leaderboard",
     *                 type="array",
     *                 @OA\Property(
     *                      property="rank",
     *                      type="integer",
     *                      description="User rating place",
     *                      example=1000000000,
     *                 ),
     *                  @OA\Property(
     *                      property="amount",
     *                      type="integer",
     *                      description="Number of invitees",
     *                      example=100000,
     *                 )
     *                 @OA\Property(
     *                      property="reward",
     *                      type="integer",
     *                      description="How much user earned",
     *                      example=7,
     *                 ),
     *                 @OA\Property(
     *                      property="grow_this_month",
     *                      type="integer",
     *                      description="",
     *                      example=100000,
     *                 )
     *             )
     *         )
     *     ),
     *
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
     *
     *     @OA\Response(
     *         response="500",
     *         description="Unknown error"
     *     )
     * )
     *
     * @param Request $request
     *
     * @return Response
     */
    public function index(Request $request): Response
    {
        $user_id = Auth::user()->getAuthIdentifier();

        try {
            // we get data for the informer
            $informer = Total::getInformer($user_id);

            // collecting an array with data for the graph
            $graph_data = Transaction::getDataForDate($user_id, $request->get('graph_filtr'));

            $users = Total::orderBy('amount', 'DESC')
                ->orderBy('reward', 'DESC')
                ->paginate($request->get('limit', config('settings.pagination_limit')));

            $users->map(function ($object) use ($user_id) {
                $isCurrent = false;
                if ($object->user_id == $user_id) {
                    $isCurrent = true;
                }
                $object->setAttribute('is_current', $isCurrent);
                $object->save();

            });

            return response()->jsonApi(
                array_merge([
                    'type' => 'success',
                    'title' => 'Updating success',
                    'message' => 'The referral code (link) has been successfully updated',
                    'informer' => $informer,
                    'graph' => $graph_data,
                ], [
                    'data' => $users->toArray(),
                    'leaderboard' => $this->getLeaderboard(),
                ]),
                200);

        } catch (ModelNotFoundException $e) {
            return response()->jsonApi([
                'type' => 'danger',
                'title' => "Not operation",
                'message' => "Error showing all users",
                'data' => null,
            ], 404);
        }
    }

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
     * @param string      $country
     * @param string|null $city
     *
     * @return array
     */
    protected function getUserIDByCountryCity(string $country, string $city = null)
    {
        if ($country and $city != null) {
            //TODO get user id by country and city from identity ms
            return [];
        }
        //TODO get user id by country from identity ms
        return [];
    }

    /**
     * @param $query
     * @param $filter
     *
     * @return mixed
     */
    protected function getFilterQuery($query, $filter)
    {
        $en = CarbonImmutable::now()->locale('en_US');
        return match ($filter) {
            'this week' => $query->whereBetween('created_at', [$en->startOfWeek(), $en->endOfWeek()]),
            'this month' => $query->whereMonth('created_at', Carbon::now()->month),
            'this year' => $query->whereYear('created_at', Carbon::now()->year),
            'country' => $query->whereIn('referrer_id', $this->getUserIDByCountryCity(request()->country)),
            'country_and_city' => $query->whereIn('user_id', $this->getUserIDByCountryCity(request()->country, request()->city)),
            default => $query,
        };
    }

    /**
     * @param Request $request
     *
     * @return array
     */
    protected function getLeaderboard(Request $request): array
    {
        $leaderboard = [];

        $filter = strtolower($request->filter);

        $referrers = User::distinct('referrer_id')->get(['referrer_id'])->toArray();

        $query = $this->getFilterQuery(User::whereIn('referrer_id', $referrers), $filter);

        $invitedUsersFilter = match ($filter) {
            'this week' => 'current_week_count',
            'this month' => 'current_month_count',
            default => 'current_year_count',
        };

        $rank = 1;
        foreach ($referrers as $referrer) {
            $user = $this->getUserProfile($referrer);
            $leaderboard[] = [
                'rank' => $rank,
                'name' => $user['name'] ?? null,
                'country' => $user['country'] ?? null,
                'invitees' => $query->where('referrer_id', $referrer)->count(),
                'reward' => $this->getTotalReward($referrer, $filter),
                'grow_this_month' => Total::getInvitedUsersByDate($referrer, $invitedUsersFilter),
            ];
            $rank++;
        }

        return collect($leaderboard)->sortByDesc('reward')->values()->all();
    }

    /**
     * @param $user_id
     *
     * @return array
     */
    protected function getUserProfile($user_id): array
    {
        //TODO get user profile from identity ms API
        return [];
    }

    /**
     * @param $user_id
     *
     * @return int|float
     */
    protected function getUserPlatformReward($user_id): float|int
    {
        $platforms = [
            'Ultainfinity Wealth LaunchPad',
            'Ultainfinity Wallet',
            'Ultainfinity Exchange',
        ];
        $value = 1;
        $application_id = ReferralCode::where('user_id', $user_id)->first()->application_id;
        //TODO API to retrieve the platform name using application id
        //TODO transform platform name to uppercase words
        //TODO check if platform matches those that have rewards and multiply by three
        //TODO Convert to user currency or default currency
        //TODO Return value
        return $value;
    }

    /**
     * @param $referrer_id
     * @param $filter
     *
     * @return int|float
     */
    protected function getTotalReward($referrer_id, $filter): int|float
    {
        $reward = 0;

        $invitees = $this->getFilterQuery(User::where('referrer_id', $referrer_id), $filter)->get(['user_id']);
        foreach ($invitees as $invited) {
            $reward += $this->getUserPlatformReward($invited->user_id);
        }
        return $reward;
    }
}
