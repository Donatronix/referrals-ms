<?php

namespace App\Api\V1\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Link;
use App\Models\User;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\Request;
use App\Services\Crypt;
use Kreait\Firebase\DynamicLink\CreateDynamicLink\FailedToCreateDynamicLink;
use PubSub;
use Validator;

/**
 * Class MainController
 * @package App\Api\V1\Controllers
 */
class MainController extends Controller
{
    /**
     * List all referrals for user
     *
     * @OA\Get(
     *     path="/api/v1/referral",
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
     */

    /**
     * Get user referrer invite code
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

        if($currentUser !== $username){
            $currentUser->user_name = $username;
            $currentUser->save();
        }

        // Get list all referrals by user id
        $list = User::where('referrer_id', $userId)->get();

        // Return response
        return response()->jsonApi($list, 200);
    }

    /**
     * Save data
     *
     * @OA\Post(
     *     path="/api/v1/referral",
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
     */

    /**
     * Get user referrer invite code
     *
     * @param Request $request
     *
     * @return \Sumra\JsonApi\
     * @throws \Illuminate\Validation\ValidationException
     */
    public function create(Request $request)
    {
        $data = $request->get('data', null);

        if ($data === null) {
            abort(401, 'Required data');
        }

        try {
            $inputData = Crypt::decrypt($data, $request);
        } catch (DecryptException $e) {
            // Return error
            return response()->jsonApi($e, 200);
        }

        $validator = Validator::make($inputData, [
            'referrer_code' => 'nullable|string',
            'package_name' => [
                'required',
                'string',
                'min:10',
                'regex:/[a-z0-9.]/'
            ],
            'device_id' => 'required|string',
            'device_name' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->jsonApi([
                'errors' => $validator->errors(),
            ], 422);
        }

        // If exist user id in header then join user
        if($request->headers->has('user-id')){
            $appUserId = $request->header('user-id');

            if ($appUserId === null) {
                abort(401, 'Unauthorized');
            }

            $inputData['user_id'] = $appUserId;
            $inputData['user_name'] = $request->header('username', null);

            return $this->join($inputData);
        }else{
            $inputData['ip'] = $request->ip();
            $inputData['metadata'] = $request->headers->all();

            return $this->stats($inputData);
        }
    }

    /**
     * Get user referrer invite code
     *
     * @OA\Get(
     *     path="/api/v1/referral/invite",
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
     */

    /**
     * Get user referrer invite code
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

        if(!$user){
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

        if(!$link){
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
                'referral_link' => (string) $shortLink
            ]);
        }

        // Return dynamic link
        return response()->jsonApi([
            'referral_code' => $user->referral_code,
            'referral_link' => $link->referral_link
        ], 200);
    }

    /**
     * Save stats of downloads and first run application after install
     *
     * @param array $input
     * @return mixed
     */
    private function stats(Array $input){
        $info = Application::create([
            'referrer_code' => $input['referrer_code'] ?? null,
            'package_name' => $input['package_name'],
            'device_id' => $input['device_id'],
            'device_name' => $input['device_name'],
            'ip' => $input['ip'],
            'metadata' => $input['metadata']
        ])->toArray();

        /**
         * Push notification to pubsub about downloading and installing app
         */
        $array = [
            'info' => $info,
            'note' => 'Add stats about install app'
        ];
        PubSub::transaction(function() {
        })->publish('UserInstallApp', $array, 'walletIP');

        // Return response
        return response()->jsonApi($info, 200);
    }

    /**
     * Join new user to referrer
     *
     * @param array $input
     * @return mixed
     */
    private function join(Array $input){
        // Find app user object and create if not exist
        $user = User::where('user_id', $input['user_id'])->first();
        if(!$user){
            $user = User::create([
                'user_id' => $input['user_id'],
                'user_name' => $input['user_name']
            ]);

            // Check downloaded app
//            $app = Application::where('device_id', $input['device_id'])->where('is_registered', false)->first();
//            if($app){
//                $app->is_registered = true;
//                $app->save();
//            }

            $app = Application::where('device_id', $input['device_id'])->first();
            if($app){
                $app->installed_status = Application::INSTALLED_OK;
                $app->save();
            }
        }

        // Check if exist referrer_code
        $referrerCode = $input['referrer_code'] ?? null;
        if($referrerCode !== null){
            // Check if new user has referrer already
            $newUser = User::where('user_id', $input['user_id'])->where('referrer_id', '!=', 0)->first();

            if($newUser){
                abort(400, 'You already have an referrer');
            }

            // Get referrer object
            $referrer = User::where('referral_code', $referrerCode)->first();

            if ($referrer === null) {
                abort(404, 'Referrer by code not found');
            }

            /**
             * Linking users and accruing referral bonus
             */
            $array = [
                'id' => $referrer->user_id,
                'points' => User::REFERRER_POINTS,
                'event' => 'referral_bonus',
                'note' => 'Accrual of referral bonus for the user ***'
            ];
            PubSub::transaction(function() use ($referrer, $user) {
                // Add referrer to new user
                $user->referrer_id = $referrer->user_id;
                $user->save();
            })->publish('ReferralBonus', $array, 'walletIP');
        }

        // Return response
        return response()->jsonApi('Process success', 200);
    }
}

######################### EXAMPLE CODE ##################################
/**
 * For wallet microservice
 */
/*
$array = [
    'sumra_user_id' => $appUserId,
    'status' => $items = Arr::random([
        User::STATUS_APPROVED,
        User::STATUS_NOT_APPROVED,
        User::STATUS_BLOCKED
    ])
];
PubSub::transaction(function() {})->publish('ReferralBonus', $array, 'referral');
*/
######################### EXAMPLE CODE ##################################
