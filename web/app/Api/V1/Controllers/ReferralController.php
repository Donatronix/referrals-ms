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
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="application_id",
     *                 type="string",
     *                 maximum=50,
     *                 description="ID of the service whose link the user clicked on",
     *                 example="net.sumra.chat"
     *             ),
     *             @OA\Property(
     *                 property="referral_code",
     *                 type="string",
     *                 minimum=8,
     *                 maximum=8,
     *                 description="Referral code of the inviting user",
     *                 example="1827oGRL"
     *             ),
     *         ),
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
     * @param \Illuminate\Http\Request $request
     *
     * @return mixed
     * @throws \Illuminate\Validation\ValidationException
     */
    public function inviting(Request $request): JsonApiResponse
    {
        // Validate input data
        $this->validate($request, [
            'application_id' => 'required|string',
            'referral_code' => 'string|max:8|min:8'
        ]);

        // Check if the user is invited, then we are looking for the referrer by the referral code
        $parent_user_id = null;
        if ($request->has('referral_code')) {
            $referralInfo = ReferralCode::where('code', $request->get('referral_code'))
                ->byApplication($request->get('application_id'))
                ->first();

            if ($referralInfo) {
                $parent_user_id = $referralInfo->user_id;
            }
        }

        // Try to create new user with referrer link
        try {
            User::create([
                'id' => Auth::user()->getAuthIdentifier(),
                'referrer_id' => $parent_user_id
            ]);
        } catch (Exception $e) {
            return response()->jsonApi([
                'status' => 'danger',
                'title' => 'User inviting',
                'message' => "Cannot inviting new user: " . $e->getMessage()
            ], 404);
        }

        // Try to create new code with link
        try {
            $codeInfo = ReferralCodeService::createReferralCode([
                'application_id' => $request->get('application_id'),
                'is_default' => true
            ]);

            // Send notification to contacts book
            $array = [
                'user_id' => $codeInfo['user_id'],
                'application_id' => $codeInfo['application_id'],
                'referral_code' => $codeInfo['referral_code']
            ];

            PubSub::publish('invitedReferral', $array, config('settings.exchange_queue.contacts_book'));

            // Return response
            return response()->jsonApi([
                'status' => 'success',
                'title' => "Referral code generate",
                'message' => 'The creation of the referral link was successful',
                'data' => $codeInfo->toArray()
            ], 200);
        } catch (Exception $e) {
            return response()->jsonApi([
                'status' => 'danger',
                'title' => 'Referral code generate',
                'message' => "There was an error while creating a referral code: " . $e->getMessage()
            ], 404);
        }
    }
}
