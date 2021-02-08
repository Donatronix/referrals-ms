<?php

namespace App\Http\Controllers;

use App\Services\Crypt;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\Request;

/**
 * Class ToolsController
 *
 * @package App\Api\V1\Controllers
 */
class ToolsController extends Controller
{
    /**
     * Save data for first start
     *
     * @OA\Post(
     *     path="/tests/tools/data-encrypt",
     *     summary="Join new user to referrer",
     *     description="Send encryption data",
     *     tags={"Tools"},
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
            'referrer_code' => 'OvwW7Gtq83',
            'device_id' => 'ee4d70c80cdac614',
            'device_name' => 'Android SDK built for x86',
            'package_name' => 'net.sumra.wallet'
        ]);

        $data = Crypt::encrypt($data);

        return response()->jsonApi($data, 200);
    }

    /**
     * Save data for first start
     *
     * @OA\Post(
     *     path="/tests/tools/data-decrypt",
     *     summary="Join new user to referrer",
     *     description="Send encryption data",
     *     tags={"Tools"},
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
            return response()->jsonApi([
                'type' => 'error',
                'title' => 'Invalid request',
                'message' => 'Required data'
            ], 400);
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
