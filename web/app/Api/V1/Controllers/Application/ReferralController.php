<?php

namespace App\Api\V1\Controllers\Application;

use App\Api\V1\Controllers\Controller;
use App\Models\ReferralCode;
use App\Models\User;
use App\Services\ReferralCodeService;
use App\Services\ReferralService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use PubSub;

/**
 * Class ReferralController
 *
 * @package App\Api\V1\Controllers\Application
 */
class ReferralController extends Controller
{
    /**
     * List all referrals for user
     *
     * @OA\Get(
     *     path="/referrals",
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
     *         response="401",
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Invalid request"
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description="Not Found"
     *     )
     * )
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $currentUserId = Auth::user()->getAuthIdentifier();

            // Get list all referrals by user id
            $users = User::query()->where('referrer_id', $currentUserId)
                ->paginate($request->get('limit', config('settings.pagination_limit')));

            // Return response
            return response()->json([
                'type' => 'success',
                'title' => "Get referrals list",
                'message' => 'Referrals list received',
                'data' => $users->toArray(),
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'type' => 'danger',
                'title' => "Get referrals list",
                'message' => $e->getMessage(),

            ], 400);
        }
    }

    /**
     * Joining a new user to the referral program in the presence of the referral code of the inviter
     * Save data for first start, after registration
     *
     * @OA\Post(
     *     path="/referrals",
     *     summary="Joining a new user to the referral program in the presence of the referral code of the inviter",
     *     description="Joining a new user to the referral program in the presence of the referral code of the inviter",
     *     tags={"Referrals"},
     *
     *     security={{
     *         "default": {
     *             "ManagerRead",
     *             "User",
     *             "ManagerWrite"
     *         }
     *     }},
     *
     *     @OA\RequestBody(
     *         required=true,
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
     *         response="401",
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Invalid request"
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description="Not Found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="code",
     *                 type="string",
     *                 description="Your request is missing a required parameter - Code"
     *             )
     *         )
     *     )
     * )
     *
     * @param Request $request
     *
     * @return mixed
     * @throws ValidationException
     */
    public function store(Request $request): mixed
    {
        // We are trying to register a new user to the referral program
        try {
            // Validate input data
            $validator = Validator::make($request->all(), [
                'application_id' => [
                    'required',
                    'string',
                    'min:10',
                    'max:50',
                    'regex:/[a-z0-9.]/',
                ],
                'referral_code' => 'string|nullable|max:8|min:8',
            ]);

            if ($validator->fails()) {
                return response()->jsonApi([
                    'title' => 'Joining user to the referral program',
                    'message' => 'Input data validation error',
                    'data' => $validator->errors()
                ], 422);
            }

            // Find Referrer ID by its referral code and application ID
            $parent_user_id = null;
            if ($request->has('referral_code')) {
                $parent_user_id = ReferralCode::query()->select('user_id')
                    ->byReferralCode()
                    ->byApplication()
                    ->pluck('user_id')
                    ->first();
            }

            // Register new inviting user in the referral program
            $newUser = ReferralService::getUser(Auth::user()->getAuthIdentifier());

//            // Adding an inviter to a new user
            ReferralService::setInviter($newUser, $parent_user_id);

            // Try to create new referral code with link
            $userInfo = ReferralCodeService::createReferralCode($request, $newUser, true);

            // Send notification to contacts book
            PubSub::publish('invitedReferral', $userInfo->toArray(), config('settings.pubsub_receiver.contacts_books'));

            // Return response
            return response()->json([
                'type' => 'success',
                'title' => "Joining user to the referral program",
                'message' => 'User added successfully and referral code created',
                'data' => $userInfo->toArray(),
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'type' => 'danger',
                'title' => 'Joining user to the referral program',
                'message' => "Cannot joining user to the referral program: " . $e->getMessage(),
            ], 404);
        }
    }

}
