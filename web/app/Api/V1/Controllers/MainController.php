<?php

namespace App\Api\V1\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Link;
use App\Models\User;
use App\Services\Crypt;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\Request;
use Kreait\Firebase\DynamicLink\CreateDynamicLink\FailedToCreateDynamicLink;
use PubSub;
use Validator;

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
     *     @OA\Parameter(
     *         name="user-id",
     *         description="User ID",
     *         in="header",
     *         required=true,
     *         @OA\Schema(
     *             type="number"
     *         )
     *     ),
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
     * @param Request $request
     *
     * @return \Sumra\JsonApi\
     */
    public function index(Request $request)
    {
        $userId = $request->header('user-id');

        if ($userId === null) {
            abort(401, 'Unauthorized');
        }

        // Check & update username
        $currentUser = User::where('user_id', $userId)->get();
        $username = $request->header('username', null);

        if ($currentUser !== $username) {
            $currentUser->user_name = $username;
            $currentUser->save();
        }

        // Get list all referrals by user id
        $list = User::where('referrer_id', $userId)->get();

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
     *     @OA\Parameter(
     *         name="user-id",
     *         description="New User ID",
     *         in="header",
     *         required=false,
     *         @OA\Schema(
     *             type="number"
     *         )
     *     ),
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
            abort(401, 'Required data');
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

            return response()->json([
                'type' => 'danger',
                'title' => 'Validation Error',
                'message' => $messages
            ], 422);
        }

        // If exist user id in header then join user
        if ($request->headers->has('user-id')) {
            $appUserId = $request->header('user-id');

            if ($appUserId === null) {
                abort(401, 'Unauthorized');
            }

            $inputData['user_id'] = $appUserId;
            $inputData['user_name'] = $request->header('username', null);

            return $this->registerReferrer($inputData);
        } else {
            // Remember user application
            $inputData['ip'] = $request->ip();
            $inputData['metadata'] = $request->headers->all();

            return $this->registerApplication($inputData);
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
            $app->user_id = $input['user_id'];
            $app->save();
        } else {
            abort(400, 'Not found installed application');
        }

        // Check if exist referrer_code, then join referrer to new user
        $referrerCode = $input['referrer_code'] ?? null;
        if ($referrerCode !== null) {
            // Check if new user has referrer already
            if ($app->referrer_id !== 0) {
                abort(400, 'You already have an referrer');
            }

            // Get referrer object
            $referrer = User::where('referral_code', $referrerCode)->first();

            if ($referrer === null) {
                abort(404, 'Referrer by code not found');
            }

            // Save referrer to user app
            $app->referrer_id = $referrer->user_id;
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
     *     @OA\Parameter(
     *         name="user-id",
     *         description="User ID",
     *         in="header",
     *         required=true,
     *         @OA\Schema(
     *             type="number"
     *         )
     *     ),
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
        $userId = $request->header('user-id');

        if ($userId === null) {
            abort(401, 'Unauthorized');
        }

        // Find user
        $user = User::where('user_id', $userId)->first();

        if (!$user) {
            // create a new invite record
            $user = User::create([
                'user_id' => $userId,
                'user_name' => $request->header('username', null)
            ]);
        }

        // Check Package Name
        $packageName = $request->get('package_name', Link::ANDROID_PACKAGE_NAME);

        // Get link by user id and package name
        $link = Link::where('user_id', $user->user_id)->where('package_name', $packageName)->first();

        if (!$link) {
            $referrerData = [
                //'code' => $user->referral_code,
                'utm_source' => $user->referral_code,
                'utm_medium' => Link::MEDIUM,
                'utm_campaign' => Link::CAMPAIGN,
                'utm_content' => $packageName
            ];

            // Create dynamic link from google firebase service
            $websiteUrl = 'https://sumra.net/discover?referrer=' . $user->referral_code;
            $googlePlayUrl = 'https://play.google.com/store/apps/details?id=' . $packageName . '&referrer=' . urlencode(http_build_query($referrerData));

            $parameters = [
                'dynamicLinkInfo' => [
                    'domainUriPrefix' => config('dynamic_links.default_domain'),
                    'link' => $websiteUrl,
                    'socialMetaTagInfo' => [
                        'socialTitle' => 'Sumra Net',
                        'socialDescription' => 'Follow me on Sumra network',
                        'socialImageLink' => 'https://sumra.net/css/logo.svg'
                    ],
                    'navigationInfo' => [
                        'enableForcedRedirect' => true,
                    ],
                    'analyticsInfo' => [
                        'googlePlayAnalytics' => [
                            'utmSource' => $user->referral_code,
                            'utmMedium' => Link::MEDIUM,
                            'utmCampaign' => Link::CAMPAIGN,
                            'utmContent' => $packageName,
                            /*
                            'utmTerm' => 'utmTerm',
                            'gclid' => 'gclid'
                            */
                        ],
                        /*
                          'itunesConnectAnalytics' => [
                            'at' => 'affiliateToken',
                            'ct' => 'campaignToken',
                            'mt' => '8',
                            'pt' => 'providerToken'
                          ]
                        */
                    ],
                    'androidInfo' => [
                        'androidPackageName' => $packageName,
                        'androidFallbackLink' => $googlePlayUrl,
                        //'androidMinPackageVersionCode' => Link::DEFAULT_ANDROID_MIN_PACKAGE_VERSION
                    ],
                    /*
                    'iosInfo' => [
                      'iosBundleId' => 'net.sumra.ios',
                      'iosFallbackLink' => 'https://fallback.domain.tld',
                      'iosCustomScheme' => 'customScheme',
                      'iosIpadFallbackLink' => 'https://ipad-fallback.domain.tld',
                      'iosIpadBundleId' => 'iPadBundleId',
                      'iosAppStoreId' => 'appStoreId'
                    ],
                    */
                ],
                'suffix' => [
                    'option' => 'SHORT'
                ]
            ];

            $dynamicLinks = app('firebase.dynamic_links');

            try {
                $shortLink = $dynamicLinks->createDynamicLink($parameters);
            } catch (FailedToCreateDynamicLink $e) {
                return response()->jsonApi($e->getMessage());
            }

            // Add
            $link = Link::create([
                'user_id' => $user->user_id,
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
