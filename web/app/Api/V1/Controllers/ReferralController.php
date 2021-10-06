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
     *     summary="List all referrals for current user",
     *     description="List all referrals for current user",
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
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         description="Limit referrals of page",
     *         @OA\Schema(
     *             type="number"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Count referrals of page",
     *         @OA\Schema(
     *             type="number"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search keywords",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response="200",
     *         description="Success getting list of referrals"
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
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            $currentUserId = Auth::user()->getAuthIdentifier();

            // Get list all referrals by user id
            $list = User::where('referrer_id', $currentUserId)
                ->paginate($request->get('limit', config('settings.pagination_limit')));

            // Return response
            return response()->json(array_merge(
                [
                    'type' => 'success',
                    'title' => "Get referrals list",
                    'message' => 'Contacts list received',
                ],
                $list->toArray()
            ), 200);
        } catch (Exception $e) {
            return response()->jsonApi([
                'type' => 'danger',
                'title' => "Get referrals list",
                'message' => $e->getMessage(),
                'data' => null
            ], 400);
        }
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
     *             )
     *         )
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
     *              )
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
        $newUser = User::find(Auth::user()->getAuthIdentifier());

        if ($newUser) {
            return response()->jsonApi([
                'status' => 'warning',
                'title' => 'User inviting',
                'message' => "User already exist"
            ], 204);
        }

        // Validate input data
        $this->validate($request, [
            'application_id' => 'required|string',
            'referral_code' => 'string|max:8|min:8'
        ]);

        // Check if the user is invited, then we are looking for the referrer by the referral code
        $parent_user_id = null;
        if ($request->has('referral_code')) {
            $parent_user_id = ReferralCode::select('user_id')
                ->where('code', $request->get('referral_code'))
                ->byApplication($request->get('application_id'))
                ->pluck('user_id')
                ->first();
        }

        // Try to create new user with referrer link
        try {
            $newUser = User::create([
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
            ], $newUser);

            // Send notification to contacts book
            PubSub::publish('invitedReferral', $codeInfo->toArray(), config('settings.exchange_queue.contacts_book'));

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
