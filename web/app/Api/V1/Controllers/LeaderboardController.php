<?php

namespace App\Api\V1\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Total;
use App\Models\User;
use App\Services\RemoteService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LeaderboardController extends Controller
{
    /**
     *  A list of leaders in the invitation referrals
     *
     * @OA\Get(
     *     path="/v1/referrals/leaderboard",
     *     description="A list of leaders in the invitation referrals",
     *     tags={"Leaderboard"},
     *
     *     security={{
     *          "default" :{
     *              "ManagerRead",
     *              "User",
     *              "ManagerWrite"
     *          },
     *     }},
     *
     *     x={
     *          "auth-type": "Applecation & Application Use",
     *          "throttling-tier": "Unlimited",
     *          "wso2-appliocation-security": {
     *              "security-types": {"oauth2"},
     *              "optional": "false"
     *           },
     *     },
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
     *                  @OA\Property(
     *                      property="amount",
     *                      type="integer",
     *                      description="Number of invited users",
     *                      example=100,
     *                  ),
     *                 @OA\Property(
     *                      property="reward",
     *                      type="double",
     *                      description="Amount of remuneration",
     *                      example=50.50,
     *                  )
     *                 @OA\Property(
     *                      property="is_current",
     *                      type="boolean",
     *                      description="Determine the user who made the request",
     *                  )
     *             )
     *             @OA\Property(
     *                 property="informer",
     *                 type="object",
     *                 @OA\Property(
     *                      property="rank",
     *                      type="integer",
     *                      description="User rating place",
     *                      example=1000000000,
     *                  )
     *                 @OA\Property(
     *                      property="reward",
     *                      type="integer",
     *                      description="How much user earned",
     *                      example=,
     *                  )
     *                 @OA\Property(
     *                      property="grow_this_month",
     *                      type="integer",
     *                      description="",
     *                      example="",
     *                  )
     *              )
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
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(
     *                  property="id",
     *                  type="string",
     *                  description="ID not found"
     *              ),
     *              @OA\Property(
     *                  property="username",
     *                  type="string",
     *                  description="username not found"
     *              ),
     *              @OA\Property(
     *                  property="amount",
     *                  type="string",
     *                  description="amount not found"
     *              ),
     *              @OA\Property(
     *                  property="reward",
     *                  type="string",
     *                  description="reward not found"
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string",
     *                  description="Error message"
     *              )
     *          )
     *     ),
     *
     *     @OA\Response(
     *         response="500",
     *         description="Unknown error"
     *     )
     * )
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $user_id = Auth::user()->getAuthIdentifier();

        try {
            $informer = Total::getInformer($user_id);

            $users = Total::orderBy('amount', 'DESC')
                ->paginate($request->get('limit', config('settings.pagination_limit')));

            $users->map(function ($object){
                $isCurrent = false;
                global $user_id;
                if($object->user_id == $user_id){
                    $isCurrent = true;
                }
                $object->setAttribute('is_current', $isCurrent);
            });

            return response()->jsonApi([
                'type' => 'success',
                'title' => "Updating success",
                'message' => 'The referral code (link) has been successfully updated',
                $users->toArray(),
                'informer' => $informer
            ], 200);

        } catch (ModelNotFoundException $e) {
            return response()->jsonApi([
                'type' => 'danger',
                'title' => "Not operation",
                'message' => "Error showing all users",
                'data' => null
            ], 404);
        }
    }

    public function checkRemoteServices($input_data)
    {
        // Igor, this is demo data for the test. By connecting them, you don't need a remote microservice.
        // $input_data = \App\Services\TestService::showDataFromRemoteMembership();
        return RemoteService::accrualRemuneration($input_data);
    }
}
