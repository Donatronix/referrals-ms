<?php

namespace App\Api\V1\Controllers;

use App\Models\PromoCode;
use App\Traits\PromoCodeGeneratorTrait;
use Illuminate\Http\Request;
use Throwable;

class PromoCodeController extends Controller
{
    use PromoCodeGeneratorTrait;

    /**
     * Generate promo code
     *
     * @OA\Get(
     *     path="/promo-codes/generate",
     *     description="Show promo code",
     *     tags={"Promo Code"},
     *
     *     security={{
     *          "default":{
     *              "ManagerRead",
     *              "User",
     *              "ManagerWrite"
     *           }
     *     }},
     *
     *     x={
     *          "auth-type": "Application & Application User",
     *          "throttling-tier": "Unlimited",
     *          "wso2-application-security": {
     *              "security-types": {"oauth2"},
     *              "optional": "false"
     *          }
     *     },
     *
     *     @OA\Parameter(
     *          name="user_id",
     *          in="query",
     *          required=true,
     *          description="Partner user Id",
     *          example="00000000-1000-1000-1000-000000000000",
     *          @OA\Schema (
     *              type="string"
     *          ),
     *     ),
     *
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *
     *             @OA\Property(
     *                 property="code",
     *                 type="string",
     *                 maximum=5,
     *                 description="Promo code",
     *                 example="10AD5"
     *             ),
     *         ),
     *     ),
     *
     *     @OA\Response(
     *          response="200",
     *          description="Success"
     *     ),
     *     @OA\Response(
     *          response="401",
     *          description="Unauthorized"
     *     ),
     *     @OA\Response(
     *          response="404",
     *          description="Error"
     *     ),
     * )
     *
     * @param Request $request
     *
     * @return mixed
     */
    public function getPromoCode(Request $request): mixed
    {
        try {
            $this->validate($request, [
                'user_id' => 'required|string',
            ]);

            $code = $this->getCode();


            PromoCode::query()->create([
                'user_id' => $request->user_id,
                'code' => $code,
            ]);

            return response()->jsonApi([
                'type' => 'success',
                'title' => "Get promo code",
                'message' => 'Get promo code',
                'data' => $code,
            ], 200);
        } catch (Throwable $th) {
            return response()->jsonApi([
                'type' => 'danger',
                'title' => "Get promo code",
                'message' => "There was an error while creating a promo code: " . $th->getMessage(),
                'data' => null,
            ], 404);
        }
    }

    /**
     * Validate promo code
     *
     * @OA\Post(
     *     path="/promo-codes/validate",
     *     description="Validate promo code",
     *     tags={"Promo Code"},
     *
     *     security={{
     *          "default":{
     *              "ManagerRead",
     *              "User",
     *              "ManagerWrite"
     *           }
     *     }},
     *
     *     x={
     *          "auth-type": "Application & Application User",
     *          "throttling-tier": "Unlimited",
     *          "wso2-application-security": {
     *              "security-types": {"oauth2"},
     *              "optional": "false"
     *          }
     *     },
     *
     *     @OA\Parameter(
     *          name="code",
     *          in="query",
     *          required=true,
     *          description="Promo code to be validated",
     *          example="00000000",
     *          @OA\Schema (
     *              type="string"
     *          ),
     *     ),
     *
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *
     *             @OA\Property(
     *                 property="code",
     *                 type="string",
     *                 maximum=5,
     *                 description="Promo code",
     *                 example="10AD5"
     *             ),
     *         ),
     *     ),
     *
     *     @OA\Response(
     *          response="200",
     *          description="Success"
     *     ),
     *     @OA\Response(
     *          response="401",
     *          description="Unauthorized"
     *     ),
     *     @OA\Response(
     *          response="404",
     *          description="Error"
     *     ),
     * )
     *
     * @param Request $request
     *
     * @return mixed
     */
    public function validatePromoCode(Request $request): mixed
    {
        try {

            $this->validate($request, [
                'code' => 'required|string|exists:promo_codes,code',
            ]);

            $code = PromoCode::query()->where('code', $request->code)->first();

            return response()->jsonApi([
                'type' => 'success',
                'title' => "Get promo code info",
                'message' => 'Get promo code info',
                'data' => $code,
            ], 200);
        } catch (Throwable $e) {
            return response()->jsonApi([
                'type' => 'danger',
                'title' => "Get promo code info",
                'message' => $e->getMessage(),
                'data' => null,
            ], 404);
        }
    }
}
