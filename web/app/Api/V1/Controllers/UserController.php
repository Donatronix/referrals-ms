<?php


namespace App\Api\V1\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Helpers\Vcards;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    /**
     * User's contact list
     *
     * @OA\Get(
     *     path="/v1/referrals/contacts",
     *     summary="Load user's contact list",
     *     description="Load user's contact list",
     *     tags={"UserContacts"},
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
     * @return \Illuminate\Http\JsonResponse|mixed
     */
    public function contacts()
    {
        $user_id = intval(Auth::user()->getAuthIdentifier());
        $user = User::find($user_id);
        try {
            $contacts = $user->contacts();
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
        // Return response
        return response()->json([
            'success' => true,
            'data' => $contacts
        ], 200);

    }

    /**
     * User's contact list: add contacts from vCard
     *
     * @OA\Post(
     *     path="/v1/referrals/contacts/vcard",
     *     summary="Add contacts from vCard",
     *     description="Add contacts from vCard",
     *     tags={"UserContacts"},
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
     *     @OA\Parameter(
     *         name="vcard",
     *         description="vCard text",
     *         required=true,
     *         in="query",
     *          @OA\Schema (
     *              type="integer",
     *              default = 0
     *          )
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
    public function addvcard(Request $request)
    {
        $user_id = intval(Auth::user()->getAuthIdentifier());
        $user = User::find($user_id);
        $vcards = new Vcard();
        $vcard->fromString($request->vcard);
        try {
            $contacts = $user->contacts();
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
        // Return response
        return response()->json([
            'success' => true,
            'data' => $contacts
        ], 200);

    }

    /**
     * User's contact list: add contacts from Google export
     *
     * @OA\Post(
     *     path="/v1/referrals/contacts/google",
     *     summary="Add contacts from Google export",
     *     description="Add contacts from Google export",
     *     tags={"UserContacts"},
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
     *     @OA\Parameter(
     *         name="googleexport",
     *         description="Google export text",
     *         required=true,
     *         in="query",
     *          @OA\Schema (
     *              type="integer",
     *              default = 0
     *          )
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
    public function addgoogle(Request $request)
    {
        $user_id = intval(Auth::user()->getAuthIdentifier());
        $user = User::find($user_id);
        $vcards = new Vcard();
        $vcard->fromString($request->vcard);
        try {
            $contacts = $user->contacts();
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
        // Return response
        return response()->json([
            'success' => true,
            'data' => $contacts
        ], 200);

    }

}
