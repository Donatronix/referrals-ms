<?php

namespace App\Api\V1\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Device;
use App\Models\Stats;
use App\Models\User;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\Request;
use App\Services\Crypt;
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
     *     tags={"Referral"},
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
        $currentUser = User::where('app_user_id', $userId)->get();
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
     * @OA\Post(
     *     path="/api/v1/referral",
     *     summary="Join new user to referrer",
     *     description="Send encryption data: username - New User Name, * package_name (required) - Package Name, referrer_code - Referrer code, * device_id (required) - User Device ID, * device_name (required) - User Device Name",
     *     tags={"Referral"},
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

            $inputData['app_user_id'] = $appUserId;
            $inputData['user_name'] = $request->header('username', null);

            return $this->join($inputData);
        }else{
            $inputData['ip'] = $request->ip();
            $inputData['metadata'] = $request->headers->all();

            return $this->stats($inputData);
        }
    }

    /**
     * Save stats of downloads and first run application after install
     *
     * @param array $input
     * @return mixed
     */
    private function stats(Array $input){
        //dd($input);

        $info = Stats::create([
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
        $user = User::where('app_user_id', $input['app_user_id'])->first();
        if(!$user){
            $user = User::create([
                'app_user_id' => $input['app_user_id'],
                'user_name' => $input['user_name']
            ]);

            // Check downloaded app
            $app = Stats::where('device_id', $input['device_id'])->where('is_registered', false)->first();
            if($app){
                $app->is_registered = true;
                $app->save();

                /**
                 * Add Bonus for downloading the application and registration
                 */
                $array = [
                    'new_user_id' => $input['app_user_id'],
                    'points' => User::INSTALL_POINTS,
                    'subject' => "Bonus for downloading the application {$input['package_name']} and registration"
                ];
                PubSub::transaction(function() use ($input, $user) {
                    // Add device to user
                    $device = Device::create([
                        'name' => $input['device_name'],
                        'device_id' => $input['device_id'],
                        'user_id' => $user->id
                    ]);
                })->publish('sendRewardForInstallEmail', $array, 'mailReferral');
            }
        }

        // Check if exist referrer_code
        $referrerCode = $input['referrer_code'] ?? null;
        if($referrerCode !== null){
            // Check if new user has referrer already
            $newUser = User::where('app_user_id', $input['app_user_id'])->where('referrer_id', '!=', 0)->first();

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
                'id' => $referrer->app_user_id,
                'points' => User::REFERRER_POINTS,
                'event' => 'referral_bonus',
                'note' => 'Accrual of referral bonus for the user ***'
            ];
            PubSub::transaction(function() use ($referrer, $user) {
                // Add referrer to new user
                $user->referrer_id = $referrer->app_user_id;
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
    'new_user_id' => $appUserId,
    'status' => $items = Arr::random([
        User::STATUS_APPROVED,
        User::STATUS_NOT_APPROVED,
        User::STATUS_BLOCKED
    ])
];
PubSub::transaction(function() {})->publish('ReferralBonus', $array, 'referral');
*/
######################### EXAMPLE CODE ##################################
