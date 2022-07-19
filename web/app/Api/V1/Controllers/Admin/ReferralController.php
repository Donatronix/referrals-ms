<?php

namespace App\Api\V1\Controllers\Admin;

use App\Api\V1\Controllers\Controller;
use App\Models\Total;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * Class ReferralController
 *
 * @package App\Api\V1\Controllers\Application
 */
class ReferralController extends Controller
{

    /**
     * Get the total earnings
     *
     * @OA\Get(
     *     path="/wallets/total-earnings",
     *     summary="Get total earnings",
     *     description="Get total earnings",
     *     tags={"Referrals"},
     *
     *     security={{
     *         "default": {
     *             "ManagerRead",
     *             "Reward",
     *             "ManagerWrite"
     *         }
     *     }},
     *
     *     @OA\Parameter(
     *         name="user_id",
     *         in="query",
     *         description="User Id",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Total reward successfully retrieved"
     *     ),
     *     @OA\Response(
     *         response=401,
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
     * @return mixed
     */
    public function getWalletTotal(Request $request): mixed
    {
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|string|exists:referral_codes,user_id',
            ]);

            if ($validator->fails()) {
                throw new Exception($validator->errors()->first());
            }

            $user_id = $validator->validated()['user_id'];
            $total = Total::where('user_id', $user_id)->get()->sum('reward');


            // Return response
            return response()->json([
                'type' => 'success',
                'title' => "Total Reward",
                'message' => 'Total reward successfully retrieved',
                'data' => $total,
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'type' => 'danger',
                'title' => 'Total reward',
                'message' => "Error retrieving total reward: " . $e->getMessage(),
                'data' => [],
            ], 404);
        }
    }

}
