<?php

namespace App\Api\V1\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ReferralCode;
use App\Models\User;
use App\Services\Firebase;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use PubSub;
use Sumra\JsonApi\JsonApiResponse;
use function Psy\debug;

/**
 * Class MainController
 *
 * @package App\Api\V1\Controllers
 */
class MainController extends Controller
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
        $list = User::where('id', $user->id)->get();

        // Return response
        return response()->jsonApi($list, 200);
    }

    /**
     * Get user referrer invite code
     *
     * @OA\Get(
     *     path="/v1/referrals/invite",
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
     *     @OA\Parameter(
     *         name="package_name",
     *         description="Package Name",
     *         in="query",
     *         example="net.sumra.chat",
     *         required=false,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
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
     *         description="not found"
     *     )
     * )
     *
     * @param Request $request
     *
     * @return \Sumra\JsonApi\
     * @throws \Illuminate\Validation\ValidationException
     */
    public function invite(Request $request)
    {
        $user = $this->getUser();
        $link_cnt = config('app.link_limit');

        // Check Package Name
        $application_id = $request->get('application_id');

        if(!$application_id){
            return 'Your request is missing a required parameter - Application ID';
        }

        // Get link by user id and package name
        $link = ReferralCode::where('user_id', $user->id)->where('application_id', $application_id)->limit($link_cnt);

        if ($link <= $link_cnt)
        {
            // Create dynamic link from google firebase service
            $shortLink = Firebase::linkGenerate($user->referral_code, $application_id);

            // Add
            $link = ReferralCode::create([
                'user_id' => $user->id,
                'application_id' => $application_id,
                'referral_link' => (string)$shortLink
            ]);
        }

        // Return dynamic link
        return response()->jsonApi([
            'referral_code' => $user->referral_code,
            'referral_link' => $link->referral_link
        ], 200);
    }

    /**
     * @return mixed
     */
    private function getUser()
    {
        $currentUserId = Auth::user()->getAuthIdentifier();
        $currentUserIdn = 2;


        // Find user and if not exist, then create a new user
        $user = User::find($currentUserId);

        if (!$user) {
            $user = User::create([
                'id' => $currentUserId,
                'username' => Auth::user()->username
            ]);
        } else {
            // Update username, if not exist
            $username = Auth::user()->username;
            if ($user->username !== $username) {
                $user->username = $username;
                $user->save();
            }
        }

        return $user;
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
