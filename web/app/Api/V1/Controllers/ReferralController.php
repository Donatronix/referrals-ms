<?php

namespace App\Api\V1\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ReferralCode;
use App\Models\User;
use App\Services\ReferralCodeService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PubSub;
use Sumra\JsonApi\JsonApiResponse;

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
     *     tags={"Referrals"},
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
        $currentUserId = Auth::user()->getAuthIdentifier();

        // Get list all referrals by user id
        $list = User::where('referrer_id', $currentUserId)->get();

        // Return response
        return response()->jsonApi($list, 200);
    }

    /**
     * Get user referrer invite code
     *
     * @OA\Post(
     *     path="/v1/referrals/inviting",
     *     summary="Create user invite code",
     *     description="Get user referrer invite code",
     *     tags={"Referrals"},
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
     *                  maximum=50,
     *                  description="ID of the service whose link the user clicked on",
     *                  example="net.sumra.chat"
     *              ),
     *              @OA\Property(
     *                  property="referral_code",
     *                  type="string",
     *                  minimum=8,
     *                  maximum=8,
     *                  description="Referral code of the inviting user",
     *                  example="1827oGRL"
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
        $this->validate($request, [
            'application_id' => 'required|string',
            'code' => 'string|max:8|min:8'
        ]);

        try {
            // if the user is invited, then we are looking for the referrer by the referral code
            if ($request->code) {

                $referral_info = ReferralCode::where('application_id', $request->get('application_id'))
                    ->where('code', $request->code)
                    ->first();

                if ($referral_info) {
                    return $this->createUser($request->get('application_id'), $referral_info->user_id);
                }
            }

            return $this->createUser($request->get('application_id'));
        } catch (Exception $e) {
            return response()->jsonApi([
                'type' => 'error',
                'title' => 'Referrals link not found',
                'message' => $e
            ], 404);
        }
    }

    /**
     * @param       $application_id
     * @param null  $parent_user_id
     *
     * @return mixed
     */
    private function createUser($application_id, $parent_user_id = null)
    {
        $currentUserId = Auth::user()->getAuthIdentifier();

        User::create([
            'id' => $currentUserId,
            'referrer_id' => $parent_user_id
        ]);

        $user_info = ReferralCodeService::createReferralCode([
            'user_id' => $currentUserId,
            'application_id' => $application_id,
            'is_default' => true
        ]);

        $array = [
            'user_id' => $user_info['user_id'],
            'application_id' => $user_info['application_id'],
            'referral_code' => $user_info['referral_code']
        ];

        PubSub::publish('invitedReferral', $array, 'contactsBook');

        return response()->jsonApi('User created', 200);
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
PubSub::transaction(function() {
})->publish('ReferralBonus', $array, 'referral');
*/
######################### EXAMPLE CODE ##################################
