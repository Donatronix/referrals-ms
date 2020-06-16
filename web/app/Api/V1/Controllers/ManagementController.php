<?php

namespace App\Api\V1\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Device;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * Class ManagementController
 * @package App\Api\V1\Controllers
 */
class ManagementController extends Controller
{
    /**
     * Validate user install app
     *
     * @OA\Post(
     *     path="/api/v1/referral/manager/validate/user",
     *     summary="Validate user installed app",
     *     description="Validate user installed app",
     *     tags={"Management"},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="user_id",
     *                 type="integer",
     *                 description="User ID",
     *                 example="669"
     *             ),
     *             @OA\Property(
     *                 property="application_id",
     *                 type="integer",
     *                 description="Application ID",
     *                 example="2"
     *             ),
     *             @OA\Property(
     *                 property="status",
     *                 type="integer",
     *                 description="Status (Approve, Reject, etc)",
     *                 example="1"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="User successfull validated"
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
     * @return mixed
     * @throws ValidationException
     */
    public function validateUser(Request $request)
    {
        $input = $this->validate($request, [
            'user_id' => 'required|integer',
            'application_id' => 'required|integer',
            'status' => 'required|integer'
        ]);

        // Change user status
        try {
            $app = Application::where('id', $input['application_id'])
                ->where('user_id', $input['user_id'])
                ->first();
            $app->user_status = $input['status'];
            $app->save();
        } catch(\Exception $e){
            // Return error
            return response()->jsonApi($e, 200);
        }

        /**
         * Add Bonus for downloading the application and registration
         */
        if($input['status'] === Application::INSTALLED_APPROVE){
            $array = [
                'user_id' => $input['user_id'],
                'points' => User::INSTALL_POINTS,
                'subject' => "Bonus for downloading the application {$input['package_name']} and registration"
            ];
            PubSub::transaction(function() use ($app) {
                // Add device to user
                Device::create([
                    'name' => $app->device_name,
                    'device_id' => $app->device_id,
                    'user_id' => $app->id
                ]);
            })->publish('sendRewardForInstallEmail', $array, 'mailReferral');
        }

        // Return response
        return response()->jsonApi('Operation successful', 200);
    }

    /**
     * Validate referrer
     *
     * @OA\Post(
     *     path="/api/v1/referral/manager/validate/referrer",
     *     summary="Validate referrer",
     *     description="Validate referrer",
     *     tags={"Management"},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="referrer_id",
     *                 type="integer",
     *                 description="Referrer ID",
     *                 example="69"
     *             ),
     *             @OA\Property(
     *                 property="application_id",
     *                 type="integer",
     *                 description="Application ID",
     *                 example="2"
     *             ),
     *             @OA\Property(
     *                 property="status",
     *                 type="integer",
     *                 description="Status (Approve, Reject, etc)",
     *                 example="1"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Successfull validated"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid request"
     *     ),
     * )
     *
     * @param Request $request
     * @return mixed
     * @throws ValidationException
     */
    public function validateReferrer(Request $request){
        $input = $this->validate($request, [
            'referrer_id' => 'required|integer',
            'application_id' => 'required|integer',
            'status' => 'required|integer'
        ]);

        // Change referrer status
        try {
            $app = Application::where('id', $input['application_id'])
                ->where('referrer_id', $input['referrer_id'])
                ->first();
            $app->referrer_status = $input['status'];
            $app->save();
        } catch(\Exception $e){
            // Return error
            return response()->jsonApi($e, 200);
        }

        /**
         * Linking users and accruing referral bonus
         */
        $array = [
            'id' => $input['referrer_id'],
            'points' => User::REFERRER_POINTS,
            'event' => 'referral_bonus',
            'note' => 'Accrual of referral bonus for the user ***'
        ];
        PubSub::publish('sendRewardForReferralEmail', $array, 'mailReferral');

        // Return response
        return response()->jsonApi('Operation successful', 200);
    }
}
