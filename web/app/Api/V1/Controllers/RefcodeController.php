<?php


namespace App\Api\V1\Controllers;


use App\Http\Controllers\Controller;
use App\Models\Landingpage;
use App\Models\ReferralCode;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

/**
 * Referral code Controller
 *
 * @package App\Api\V1\Controllers
 */
class RefcodeController extends Controller
{
    /**
     * Get referral code
     *
     * @OA\Get(
     *     path="/v1/referrals/refcode",
     *     description="Get all user's referral codes",
     *     tags={"Refcode"},
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
     *     @OA\Response(
     *         response="200",
     *         description="List of all refcodes"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     *
     * @param Request $request
     *
     * @return mixed
     * @throws ValidationException
     */
    public function index() : JsonResponse
    {
        $user_id = (int) Auth::user()->getAuthIdentifier();
        try {
            $refcodes = ReferralCode::where('user_id', $user_id);
            $codes = [];
            foreach($refcodes as $p) {
                $codes[] = [
                    'id'=>$p->id,
                    'code'=>$p->code,
                    'created_at'=>$p->created_at,
                    'updated_at' => $p->updated_at
                ];
            }
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
        // Return response
        return response()->json([
            'success' => true,
            'data' => $codes
        ], 200);
    }

    /**
     * Landingpage Controller
     *
     * @OA\Post(
     *     path="/v1/referrals/refcode",
     *     description="Generate new code",
     *     tags={"Refcode"},
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
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\Property (
     *              property="user_id",
     *              type="integer",
     *              description="",
     *              example="100"
     *          ),
     *          @OA\Property (
     *              property="code",
     *              type="integeer",
     *              description="",
     *              example=""
     *          ),
     *      ),
     *
     *     @OA\Response(
     *         response="200",
     *         description="Save successfull"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     ),
     * )
     *
     * @param Request $request
     *
     * @return mixed
     * @throws ValidationException
     */
    public function save() : JsonResponse
    {
        $user_id = (int)Auth::user()->getAuthIdentifier();
        try {
            $code = new ReferralCode();
            $code->generate($user_id);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }

        // Return response
        return response()->json([
            'success' => true,
            'data' => $code->code
        ], 200);
    }
}
