<?php

namespace App\Api\V1\Controllers\Webhooks;

use App\Api\V1\Controllers\Controller;
use App\Models\Total;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Class ReferralController
 *
 * @package App\Api\V1\Controllers
 */
class ReferralController extends Controller
{
    /**
     * Get the total earnings
     *
     * @OA\Get(
     *     path="/webhooks/total-earnings",
     *     summary="Get total earnings",
     *     description="Get total earnings",
     *     tags={"Webhooks"},
     *
     *     security={{
     *         "default": {
     *             "ManagerRead",
     *             "Reward",
     *             "ManagerWrite"
     *         }
     *     }},
     *
     *     @OA\Response(
     *         response="200",
     *         description="Total reward successfully retrieved"
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
    public function getReferralTotals(Request $request)
    {
        try {
            if ($request->header('user_id')) {
                $total = Total::where('user_id', $request->header('user_id'))->get()->sum('reward');
            } else {
                $total = Total::all()->sum('reward');
            }

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
                'data' => null,
            ], 404);
        }
    }

}
