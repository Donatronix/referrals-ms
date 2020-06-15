<?php

namespace App\Api\V1\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Device;
use App\Models\User;
use Illuminate\Http\Request;
use Kreait\Firebase\DynamicLink\GetStatisticsForDynamicLink\FailedToGetStatisticsForDynamicLink;

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
     *     summary="Validate user install app",
     *     description="Validate user install app",
     *     tags={"Management"},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="user_id",
     *                 type="integer",
     *                 description="Sumra User ID",
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
     *
     * @throws FailedToGetStatisticsForDynamicLink
     */
    public function validateUser(Request $request)
    {
        $input = $this->validate($request, [
            'user_id' => 'required|integer',
            'application_id' => 'required|integer',
            'status' => 'required|integer'
        ]);

        $app = Application::find($input['application_id']);
        $app->user_id = $input['application_id'];
        $app->installed_status = $input['status'];
        $app->save();

        /**
         * Add Bonus for downloading the application and registration
         */
        $array = [
            'sumra_user_id' => $input['user_id'],
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

        // Return response
        return response()->jsonApi($info, 200);
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
     */
    public function validateReferrer(Request $request){

        Application::update();


        // Return response
        return response()->jsonApi($list, 200);
    }
}
