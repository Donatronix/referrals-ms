<?php

namespace App\Api\V1\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ReferralCode;
use App\Models\User;
use App\Services\Firebase;
use App\Services\ReferralCodeService;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use PubSub;
use Sumra\JsonApi\JsonApiResponse;
use function Psy\debug;
use function Symfony\Component\Translation\t;
use Illuminate\Support\Facades\DB;

/**
 * Class ReferralController
 *
 * @package App\Api\V1\Controllers
 */
class ReferralController extends Controller
{
    /**
     * List all referrals for user
     *
     * @OA\Get(
     *     path="/v1/referrals",
     *     summary="List all referrals for user",
     *     description="List all referrals for user",
     *     tags={"Main"},
     *
     *     security={{
     *         "default": {
     *             "ManagerRead",
     *             "User",
     *             "ManagerWrite"
     *         }
     *     }},
     *     x={
     *         "auth-type": "Application & Application User",
     *         "throttling-tier": "Unlimited",
     *         "wso2-application-security": {
     *             "security-types": {"oauth2"},
     *             "optional": "false"
     *         }
     *     },
     *
     *     @OA\Response(
     *         response="200",
     *         description="Success join new user to referrer user"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid request"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="not found"
     *     )
     * )
     *
     * @return \Sumra\JsonApi\JsonApiResponse
     */
    public function index(): JsonApiResponse
    {
        $user = $this->getUser();

        // Get list all referrals by user id
        $list = User::where('referrer_id', $user->id)->get();

        // Return response
        return response()->jsonApi($list, 200);
    }

    /**
     * Get user referrer invite code
     *
     * @OA\Post(
     *     path="/v1/referrals/inviting",
     *     summary="Get user invite code",
     *     description="Get user referrer invite code",
     *     tags={"Main"},
     *
     *     security={{
     *         "default": {
     *             "ManagerRead",
     *             "User",
     *             "ManagerWrite"
     *         }
     *     }},
     *     x={
     *         "auth-type": "Application & Application User",
     *         "throttling-tier": "Unlimited",
     *         "wso2-application-security": {
     *             "security-types": {"oauth2"},
     *             "optional": "false"
     *         }
     *     },
     *
     *     @OA\RequestBody(
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(
     *                  property="application_id",
     *                  type="string",
     *                  maximum="50",
     *                  description="ID of the service whose link the user clicked on",
     *                  example="net.sumra.chat"
     *              ),
     *              @OA\Property(
     *                  property="referral_code",
     *                  type="string",
     *                  minimum="8",
     *                  maximum="8",
     *                  description="Referral code of the inviting user",
     *                  example="1827oGRL",
     *              ),
     *          ),
     *     ),
     *
     *     @OA\Response(
     *         response="200",
     *         description="Success get or generate referrer invite code"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid request"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="not found",
     *         @OA\JsonContent(
     *              type="object",
     *              @OA\Property(
     *                  property="code",
     *                  type="string",
     *                  description="Your request is missing a required parameter - Code"
     *              ),
     *         )
     *     )
     * )
     *
     * @param Request $request
     *
     * @return \Sumra\JsonApi\
     * @throws \Illuminate\Validation\ValidationException
     */
    public function inviting(Request $request)
    {
        // POST application_id, code
        // id=net.sumra.chat
        // code=1827oGRL

        $rules = [
            'application_id' => 'required|string',
            'code' => 'string|max:8|min:8'
        ];
        $this->validate($request, $rules);
        try
        {
            // if the user is invited, then we are looking for the referrer by the referral code
            if($request->code){
                $referral_info = ReferralCode::getUserByReferralCode($request->code, $request->application_id);
                if($referral_info){
                    return $this->createUser($request->application_id, $referral_info->user_id);
                }
            }

            return $this->createUser($request->application_id);
        }
        catch (\Exception $e){
            return  response()->jsonApi([
                'type' => 'error',
                'title' => 'Referrals link not found',
                'message' => $e
            ], 404);
        }
    }

    /**
     * @return mixed
     */
    private function getUser($parrent_user_id = false)
    {
        $currentUserId = Auth::user()->getAuthIdentifier();

        // Find user and if not exist, then create a new user
        /*$user = User::find($currentUserId);*/

        /*if (!$user) {*/
            $user = User::create([
                'id' => $currentUserId,
                'referrer_id' => $parrent_user_id
            ]);
//        }
//        else {
//            // Update username, if not exist
//            $username = Auth::user()->username;
//            if ($user->username !== $username) {
//                $user->username = $username;
//                $user->save();
//            }
//        }

        return $user;
    }

    public function createUser($application_id, $parrent_user_id = false)
    {
        $currentUserId = Auth::user()->getAuthIdentifier();
         User::create([
            'id' => $currentUserId,
            'referrer_id' => $parrent_user_id
        ]);

         $referral_info = [
             'user_id' => $currentUserId,
             'application_id' => $application_id,
             'is_default' => true
         ];


        ReferralCodeService::createReferralCode($referral_info);

        return true;
    }

}

######################### EXAMPLE CODE ##################################
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
######################### EXAMPLE CODE ##################################
