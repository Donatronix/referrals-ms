<?php

namespace App\Api\V1\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Crypt;
use App\Traits\AdminUserCheckTrait;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\Request;

/**
 * Class ToolsController
 *
 * @package App\Api\V1\Controllers
 */
class ToolsController extends Controller
{
    use AdminUserCheckTrait;

    /**
     * Save data for first start
     *
     * @OA\Post(
     *     path="/v1/tools/data-encrypt",
     *     summary="Join new user to referrer",
     *     description="Send encryption data",
     *     tags={"Tools"},
     *
     *     security={
     *         "default": {
     *             "ManagerRead",
     *             "User",
     *             "ManagerWrite"
     *         }
     *     },
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
    public function dataEncrypt(Request $request)
    {

        $data = json_encode([
            'androidId' => 'ee4d70c80cdac614',
            'applicationID' => 'net.sumra.wallet',
            'deviceBootloader' => 'unknown',
            'deviceBrand' => 'google',
            'deviceManufactured' => 'Google',
            'deviceModel' => 'Android SDK built for x86',
            'deviceSerialNumber' => 'EMULATOR30X0X12X0',
            'packageName' => 'net.sumra.wallet',
            'versionCode' => '1',
            'versionName' => '0.0.245'
        ]);

        $data = Crypt::encrypt($data);

        return response()->jsonApi($data, 200);
    }

    /**
     * Save data for first start
     *
     * @OA\Post(
     *     path="/v1/tools/data-decrypt",
     *     summary="Join new user to referrer",
     *     description="Send encryption data",
     *     tags={"Tools"},
     *
     *     security={
     *         "default": {
     *             "ManagerRead",
     *             "User",
     *             "ManagerWrite"
     *         }
     *     },
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
    public function dataDecrypt(Request $request)
    {
        $data = $request->get('data', null);

        if ($data === null) {
            abort(401, 'Required data');
        }

        try {
            $versionKey = $request->get('version_key', 'null');

            $data = Crypt::decrypt($data, $versionKey);

            return response()->jsonApi(json_decode($data), 200);
        } catch (DecryptException $e) {
            // Return error
            return response()->jsonApi($e, 200);
        }
    }
}
