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
use Illuminate\Support\Facades\Auth;
use Throwable;

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

            return response()->jsonApi([
                'type' => 'success',
                'title' => 'Retrieval success',
                'message' => 'Leaderboard successfully generated',
                'informer' => $informer,
                'graph' => $graph_data,
                'data' => $users->toArray(),
                'leaderboard' => $this->getLeaderboard($request),
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
     *  A list of invited users by the current user in invitation referrals
     *
     * @OA\Get(
     *     path="/invited-users/{id}",
     *     description="A list of leaders in the invitation referrals",
     *     tags={"Invited Users"},
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
     *         description="invited referrals",
     *
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                      property="fullname",
     *                      type="string",
     *                      description="User fullname",
     *                      example="Vsaya",
     *                 ),
     *                  @OA\Property(
     *                      property="username",
     *                      type="string",
     *                      description="Username",
     *                      example="Lonzo",
     *                 ),
     *                 @OA\Property(
     *                      property="Platform",
     *                      type="string",
     *                      description="Platform through which user was referred",
     *                      example="WhatsApp",
     *                 ),
     *                 @OA\Property(
     *                      property="RegistrationDate",
     *                      type="string",
     *                      description="Date user was registered",
     *                      example="2022-05-17",
     *                 ),
     *                 @OA\Property(
     *                      property="CodeUsed",
     *                      type="string",
     *                      description="Referral code used by invitee",
     *                      example="qawdnasfkm",
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
    public function show(Request $request): mixed
    {
        try {
            $referrerId = $request->user()->id ?? Auth::user()->getAuthIdentifier();
            $filter = strtolower($request->key('filter'));
            $query = User::where('referrer_id', $referrerId);
            $users = $this->getFilterQuery($query, $filter)->get();

            $retVal = [];
            foreach ($users as $user) {
                $referralCode = ReferralCode::where('user_id', $user->id)->first();
                $retVal[] = [
                    'Full name' => $user->name,
                    'Username' => $user->username,
                    'Platform' => $referralCode->application_id,
                    'Registration date' => $user->created_at,
                    'Code used' => $referralCode->code,
                ];
            }

            return response()->jsonApi(
                array_merge([
                    'type' => 'success',
                    'title' => 'Retrieval success',
                    'message' => 'The referral code (link) has been successfully updated',
                ], [
                    'data' => $retVal,
                ]),
                200);
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
     * @param string      $country
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
        return [];
    }

    /**
     * @param $query
     * @param $filter
     *
     * @return mixed
     */
    protected function getFilterQuery($query, $filter): mixed
    {
        $en = CarbonImmutable::now()->locale('en_US');
        return match ($filter) {
            'today' => $query->whereDate('created_at', Carbon::now()->toDateString()),
            'this week' => $query->whereBetween('created_at', [$en->startOfWeek(), $en->endOfWeek()]),
            'this month' => $query->whereBetween('created_at', [$en->startOfMonth(), $en->endOfMonth()]),
            'this year' => $query->whereBetween('created_at', [$en->startOfYear(), $en->endOfYear()]),
            'country' => $query->whereIn('country', request()->country ?? null),
//            'country_and_city' => $query->whereIn('user_id', $this->getUserIDByCountryCity(request()->country, request()->city)),
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

        $referrers = User::get(['referrer_id'])
            ->map(function ($item) {
                return $item->referrer_id;
            })
            ->toArray();

        $query = $this->getFilterQuery(User::whereIn('referrer_id', $referrers), $filter);

        $invitedUsersFilter = match ($filter) {
            'this week' => 'current_week_count',
            'this month' => 'current_month_count',
            default => 'current_year_count',
        };

        foreach ($referrers as $referrer) {
            $user = $this->getUserProfile($referrer);
            $leaderboard[] = [
                'name' => $user['name'] ?? null,
                'channels' => $this->getChannels($referrer),
                'country' => $user['country'] ?? null,
                'invitees' => $query->where('referrer_id', $referrer)->count(),
                'reward' => $this->getTotalReward($referrer, $filter),
                'growth_this_month' => Total::getInvitedUsersByDate($referrer, 'current_month_count'),
            ];
        }

        $columns = array_column($leaderboard, 'reward');
        array_multisort($columns, SORT_DESC, SORT_NUMERIC, $leaderboard);

        $retVal = [];
        $rank = 1;
        foreach ($leaderboard as $board) {
            $retVal[] = array_merge($board, ['rank' => $rank]);
            $rank++;
        }

        return $retVal;
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

        $retVal = ReferralCode::whereIn('user_id', $users)->get(['application_id']);
        return $retVal->map(function ($item) {
            return $item->application_id;
        })->toArray();
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
        $invitees = $this->getFilterQuery(User::where('referrer_id', $referrer_id), $filter)->get();

        foreach ($invitees as $invited) {
            $reward += $this->getUserPlatformReward($invited->id);
        }
        return $reward;
    }
}
