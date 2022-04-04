<?php

namespace App\Api\V1\Controllers;

use App\Models\ReferralCode;
use App\Services\ReferralCodeService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

/**
 * Referral code Controller
 *
 * @package App\Api\V1\Controllers
 */
class ReferralCodeController extends Controller
{
    /**
     * Get referral codes and links
     *
     * @OA\Get(
     *     path="/referral-codes",
     *     description="Get all user's referral codes and links",
     *     tags={"Referral Code"},
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
     *         name="application_id",
     *         description="Application ID",
     *         in="query",
     *         required=false,
     *         @OA\Schema(
     *              type="string",
     *              default="app.sumra.wallet"
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response="200",
     *         description="The list of referral codes has been displayed successfully"
     *     ),
     *     @OA\Response(
     *         response="401",
     *         description="Unauthorized"
     *     )
     * )
     *
     * @return mixed
     * @throws ValidationException
     */
    public function index(Request $request)
    {
        try {
            $codes = ReferralCode::byOwner()
                ->when($request->has('application_id'), function ($q) {
                    return $q->byApplication();
                })
                ->get();

            return response()->jsonApi([
                'type' => 'success',
                'title' => "List referral",
                'message' => 'list referral successfully received',
                'data' => $codes->toArray(),
            ], 200);
        } catch (Exception $e) {
            $currentUserId = Auth::user()->getAuthIdentifier();

            return response()->jsonApi([
                'type' => 'danger',
                'title' => "Not received list",
                'message' => "Data #{$currentUserId} not found",
                'data' => null,
            ], 404);
        }
    }

    /**
     *  Create link and code for an existing user
     *
     * @OA\Post(
     *     path="/referral-codes",
     *     summary="Create link and code for an existing user",
     *     description="Create link and code for an existing user",
     *     tags={"Referral Code"},
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
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="application_id",
     *                 type="string",
     *                 maximum=36,
     *                 description="Application ID",
     *                 example="app.sumra.chat"
     *             ),
     *             @OA\Property(
     *                 property="is_default",
     *                 type="boolean",
     *                 description="Is Defailt referral code / link. Accept 1, 0, true, false",
     *                 example="false"
     *             ),
     *             @OA\Property(
     *                 property="note",
     *                 type="string",
     *                 description="Note about referral code",
     *                 example="Code for facebook"
     *             )
     *         ),
     *     ),
     *
     *     @OA\Response(
     *         response="200",
     *         description="Success create link and code",
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
     *         description="Not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="code",
     *                 type="string",
     *                 description="Your request requires the valid parameters"
     *             ),
     *         ),
     *     ),
     *     @OA\Response(
     *         response="500",
     *         description="Unknown error"
     *     ),
     * )
     *
     * @param Request $request
     *
     * @return mixed
     * @throws ValidationException
     */
    public function store(Request $request)
    {
        // Validate input data
        $this->validate(
            $request,
            array_merge(['application_id' => 'required|string|max:36'], ReferralCode::$rules)
        );

        // Check amount generated codes for current user
        $codesTotal = ReferralCode::byOwner()->byApplication()->get()->count();

        if ($codesTotal >= config('settings.referral_code.limit')) {
            return response()->jsonApi([
                'status' => 'warning',
                'title' => "Exceeded the limit",
                'message' => 'You have exceeded the limit on the number of links to this service',
            ], 200);
        }

        // Try to create new code with link
        try {
            $code = ReferralCodeService::createReferralCode($request);

            return response()->jsonApi([
                'type' => 'success',
                'title' => "Referral code generate",
                'message' => 'The creation of the referral link was successful',
                'data' => $code->toArray(),
            ], 200);
        } catch (Exception $e) {
            return response()->jsonApi([
                'type' => 'danger',
                'title' => 'Referral code generate',
                'message' => "There was an error while creating a referral code: " . $e->getMessage(),
                'data' => null,
            ], 404);
        }
    }

    /**
     * Show one code and link
     *
     * @OA\Get(
     *     path="/referral-codes/{id}",
     *     description="Show referral code and link",
     *     tags={"Referral Code"},
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
     *          name="id",
     *          in="path",
     *          required=true,
     *          description="ID referral code",
     *          example="1",
     *          @OA\Schema (
     *              type="string"
     *          ),
     *     ),
     *
     *     @OA\Response(
     *          response="200",
     *          description="Success"
     *     ),
     *     @OA\Response(
     *          response="401",
     *          description="Unauthorized"
     *     )
     * )
     *
     * @param $id
     *
     * @return mixed
     */
    public function show($id)
    {
        try {
            $code = ReferralCode::find($id);

            return response()->jsonApi([
                'type' => 'success',
                'title' => "Get referral code info",
                'message' => 'Get referral code info with link',
                'data' => $code,
            ], 200);
        } catch (Exception $e) {
            return response()->jsonApi([
                'type' => 'danger',
                'title' => "Get referral code info",
                'message' => "Referral code #{$id} not found",
                'data' => null,
            ], 404);
        }
    }

    /**
     * Update referral link and code.
     *
     * @OA\Put(
     *     path="/referral-codes/{id}",
     *     description="Update referral code and link",
     *     tags={"Referral Code"},
     *
     *     security={{
     *          "default":{
     *              "ManagerRead",
     *              "User",
     *              "ManagerWrite"
     *          },
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
     *          name="id",
     *          in="path",
     *          required=true,
     *          description="Referral code ID",
     *          example="93f49909-a6ba-4812-b507-e5eb08a3cb9d",
     *          @OA\Schema (
     *              type="string"
     *          ),
     *     ),
     *      @OA\RequestBody(
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(
     *                  property="is_default",
     *                  type="boolean",
     *                  description="Is Defailt referral code / link. Accept 1, 0, true, false",
     *                  example="false"
     *              ),
     *              @OA\Property(
     *                  property="note",
     *                  type="string",
     *                  description="Note about referral code",
     *                  example=""
     *              ),
     *          ),
     *      ),
     *
     *     @OA\Response(
     *         response="200",
     *         description="Save successfull"
     *     ),
     *     @OA\Response(
     *         response="401",
     *         description="Unauthorized"
     *     ),
     *
     *     @OA\Response(
     *         response="500",
     *         description="Unknown error"
     *     ),
     * )
     *
     * @param Request                  $request
     * @param                          $id
     *
     * @return mixed
     * @throws ValidationException
     */
    public function update(Request $request, $id)
    {
        // Validate input data
        $this->validate($request, ReferralCode::$rules);

        // Try to find referral code and update it
        try {
            $data = ReferralCode::find($id);

            // Check if has is_default parameter, then reset all previous code
            if ($request->has('is_default')) {
                ReferralCodeService::defaultReset($data->user_id, $data->application_id);

                $data->is_default = $request->boolean('is_default');
            }

            $data->note = $request->get('note', null);
            $data->save();

            // Send response
            return response()->jsonApi([
                'type' => 'success',
                'title' => "Updating success",
                'message' => 'The referral code (link) has been successfully updated',
            ], 200);
        } catch (Exception $e) {
            return response()->jsonApi([
                'type' => 'danger',
                'title' => 'Referrals link not found',
                'message' => "Referral code #{$id} updated error: " . $e->getMessage(),
            ], 404);
        }
    }

    /**
     * Remove referral code
     *
     * @OA\Delete(
     *     path="/referral-codes/{id}",
     *     description="Delete referral code",
     *     tags={"Referral Code"},
     *
     *     security={{
     *         "default": {
     *             "ManagerRead",
     *             "User",
     *             "ManagerWrite"
     *         },
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
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Delete referral code by ID",
     *         example="93f49909-a6ba-4812-b507-e5eb08a3cb9d",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *
     *     @OA\Response(
     *          response="200",
     *          description="Success"
     *     ),
     *
     *     @OA\Response(
     *          response="404",
     *          description="Referral code not found",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(
     *                  property="code",
     *                  type="string",
     *                  description="Code of error"
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string",
     *                  description="Error message"
     *              ),
     *          ),
     *     ),
     * )
     *
     * @param $id
     *
     * @return mixed
     */
    public function destroy($id)
    {
        try {
            ReferralCode::destroy($id);

            return response()->jsonApi([
                'type' => 'success',
                'title' => "Deleting success",
                'message' => 'The referral link field has been successfully deleted',
            ], 200);
        } catch (Exception $e) {
            return response()->jsonApi([
                'type' => 'danger',
                'title' => 'Not found',
                'message' => "Referrals link #{$id} for deleted not found",
            ], 404);
        }
    }

    /**
     * Change the default link
     *
     * @OA\Put(
     *     path="/referral-codes/{id}/default",
     *     description="Set new referral code and link",
     *     tags={"Referral Code"},
     *
     *     security={{
     *          "default":{
     *              "ManagerRead",
     *              "User",
     *              "ManagerWrite"
     *           }
     *     }},
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
     *          name="id",
     *          in="path",
     *          required=true,
     *          description="ID of referral code",
     *          example="93f49909-a6ba-4812-b507-e5eb08a3cb9d",
     *          @OA\Schema (
     *              type="string"
     *          ),
     *     ),
     *     @OA\Response(
     *          response="200",
     *          description="Success"
     *     ),
     *     @OA\Response(
     *          response="401",
     *          description="Unauthorized"
     *     )
     * )
     *
     * @param $id
     *
     * @return Response
     */
    public function setDefault($id): Response
    {
        try {
            $code = ReferralCode::find($id);

            // Reset defaults
            ReferralCodeService::defaultReset($code->user_id, $code->application_id);

            // Set new default code
            $code->update(['is_default' => true]);

            return response()->jsonApi([
                'type' => 'success',
                'title' => "Update was success",
                'message' => 'Changing the default а referral link was successful.',
            ], 200);
        } catch (Exception $e) {
            return response()->jsonApi([
                'type' => 'danger',
                'title' => 'Operation not successful',
                'message' => "Changing the default a referral link was not successful.",
            ], 404);
        }
    }

    /**
     *  Get information on the referral link for a specific user
     *
     * @param Request $request
     *
     * @return mixed
     */
    public function getDataByUserId(Request $request)
    {
        $user_id = $request->get('user_id');

        try {
            // Get default referral code by user_id and application
            $referral_data = ReferralCode::where('user_id', $user_id)
                ->byApplication()
                ->where('is_default', 1)
                ->first();

            return response()->jsonApi([
                'type' => 'success',
                'title' => "Update was success",
                'message' => 'Changing the default а referral link was successful.',
                'data' => $referral_data,
            ], 200);
        } catch (Exception $e) {
            return response()->jsonApi([
                'type' => 'danger',
                'title' => 'Not received list',
                'message' => "Data of referral code not found",
                'data' => null,
            ], 404);
        }
    }
}
