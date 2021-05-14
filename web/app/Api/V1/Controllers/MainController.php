<?php

namespace App\Api\V1\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\ReferalCode;
use App\Models\User;
use App\Services\Crypt;
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
     * Save data for first start
     *
     * @OA\Post(
     *     path="/v1/referrals",
     *     summary="Join new user to referrer",
     *     description="Send encryption data: username - New User Name, * package_name (required) - Package Name, referrer_code - Referrer code, * device_id (required) - User Device ID, * device_name (required) - User Device Name",
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
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="version_key",
     *                 type="string",
     *                 description="Version Key",
     *                 example="66981685"
     *             ),
     *             @OA\Property(
     *                 property="data",
     *                 type="text",
     *                 description="Encrypt data",
     *                 example=""
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Success send data"
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
     * @return \Illuminate\Http\JsonResponse|mixed
     */
    public function create(Request $request)
    {
        $data = $request->get('data', null);

        if ($data === null) {
            return response()->jsonApi([
                'type' => 'error',
                'title' => 'Invalid request',
                'message' => 'Required data'
            ], 400);
        }

        try {
            $versionKey = $request->get('version_key', 'null');

            $inputData = Crypt::decrypt($data, $versionKey);
        } catch (DecryptException $e) {
            // Return error
            return response()->jsonApi($e, 200);
        }

        $validator = Validator::make($inputData, [
            'referrer_code' => 'string|nullable',
            'package_name' => [
                'required',
                'string',
                'min:10',
                'regex:/[a-z0-9.]/'
            ],
            'device_id' => 'string|required',
            'device_name' => 'string|required'
        ]);

        // If has errors, then return
        if ($validator->fails()) {
            $messages = [];

            $errors = $validator->errors();
            foreach ($errors->all() as $field) {
                $messages[] = $field;
            }

            return response()->jsonApi([
                'type' => 'danger',
                'title' => 'Validation Error',
                'message' => $messages
            ], 422);
        }

        // Remember user application
        $inputData['ip'] = $request->ip();
        $inputData['metadata'] = $request->headers->all();
        $this->registerApplication($inputData);

        // If exist user id in header then join user
        // @todo 04/11/2020 Need review logic and replace user-id !!!
        if ($request->headers->has('user-id')) {
            $this->registerReferrer($inputData);
        }
    }

    /**
     * Add referrer to new user by application
     *
     * @param array $input
     *
     * @return mixed
     */
    private function registerReferrer(array $input)
    {
        // Check downloaded app
        $app = Application::where('device_id', $input['device_id'])
            ->where('package_name', $input['package_name'])
            ->first();

        if ($app) {
            $app->user_status = Application::INSTALLED_OK;
            $app->user_id = Auth::user()->getAuthIdentifier();
            $app->save();
        } else {
            return response()->jsonApi([
                'type' => 'error',
                'title' => 'Not found data',
                'message' => 'Not found installed application'
            ], 400);
        }

        // Check if exist referrer_code, then join referrer to new user
        $referrerCode = $input['referrer_code'] ?? null;
        if ($referrerCode !== null) {
            // Check if new user has referrer already
            if ($app->referrer_id !== 0) {
                return response()->jsonApi([
                    'type' => 'error',
                    'title' => 'Referrer exist',
                    'message' => 'You already have an referrer'
                ], 400);
            }

            // Get referrer object
            $referrer = User::where('referral_code', $referrerCode)->first();

            if ($referrer === null) {
                return response()->jsonApi([
                    'type' => 'error',
                    'title' => 'Not found data',
                    'message' => 'Referrer by code not found'
                ], 400);
            }

            // Save referrer to user app
            $app->referrer_id = $referrer->id;
            $app->referrer_status = Application::REFERRER_OK;
            $app->save();

            /**
             * Push notification to pubsub, what Referrer has been added to user by installed application
             */
            $array = [
                'application' => $app,
                'referrer_code' => $referrerCode,
                'note' => 'Referrer has been added to user by installed application'
            ];
            PubSub::publish('userReferrerSet', $array, 'admin');
        }

        // Return response
        return response()->jsonApi('Process success', 200);
    }

    /**
     * Save info about application after downloads and first run
     *
     * @param array $input
     *
     * @return mixed
     */
    private function registerApplication(array $input)
    {
        $app = Application::create([
            'package_name' => $input['package_name'],
            'device_id' => $input['device_id'],
            'device_name' => $input['device_name'],
            'ip' => $input['ip'],
            'metadata' => $input['metadata'],
            'referrer_code' => $input['referrer_code'] ?? null,
        ])->toArray();

        /**
         * Push notification to pubsub about downloading and installing app
         */
        $array = [
            'application' => $app,
            'note' => 'User has been installed application'
        ];
        PubSub::publish('userInstalledApp', $array, 'admin');

        // Return response
        return response()->jsonApi('Application saved', 200);
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

        // Check Package Name
        $packageName = $request->get('package_name', ReferalCode::ANDROID_PACKAGE_NAME);

        // Get link by user id and package name
        $link = ReferalCode::where('user_id', $user->id)
            ->where('package_name', $packageName)
            ->first();

        if (!$link) {
            // Create dynamic link from google firebase service
            $shortLink = Firebase::linkGenerate($user->referral_code, $packageName);

            // Add
            $link = ReferalCode::create([
                'user_id' => $user->id,
                'package_name' => $packageName,
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
