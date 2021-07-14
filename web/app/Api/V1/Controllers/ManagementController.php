<?php

namespace App\Api\V1\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * Class ManagementController
 *
 * @package App\Api\V1\Controllers
 */
class ManagementController extends Controller
{
    /**
     * Validate referrer
     *
     * @OA\Post(
     *     path="/v1/referrals/manager/validate/referrer",
     *     summary="Validate referrer",
     *     description="Validate referrer",
     *     tags={"Management"},
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
     *
     * @return mixed
     * @throws ValidationException
     */
    public function validateReferrer(Request $request)
    {
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
        } catch (Exception $e) {
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
