<?php

namespace App\Api\V1\Controllers;

use App\Models\ReferralCode;
use App\Services\ReferralCodeService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
     *     path="/v1/referrals/referral-codes",
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
                ->when($request->has('application_id'), function ($q) use ($request) {
                    return $q->byApplication($request->get('application_id'));
                })
                ->get();

            return response()->jsonApi([
                'status' => 'success',
                'title' => "List referral",
                'message' => 'list referral successfully received',
                'data' => $codes->toArray()
            ], 200);
        } catch (Exception $e) {
            $currentUserId = Auth::user()->getAuthIdentifier();

            return response()->jsonApi([
                'status' => 'danger',
                'title' => "Not received list",
                'message' => "Data #{$currentUserId} not found"
            ], 404);
        }
    }

    /**
     *  Create link and code for an existing user
     *
     * @OA\Post(
     *     path="/v1/referrals/referral-codes",
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
     *                 type="string",
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
     * @param \Illuminate\Http\Request $request
     *
     * @return mixed
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request)
    {
        // Validate input data
        $this->validate(
            $request,
            array_merge(['application_id' => 'required|string|max:36'], ReferralCode::$rules)
        );

        // Check amount generated codes for current user
        $codesTotal = ReferralCode::byOwner()
            ->byApplication($request->get('application_id'))
            ->get()
            ->count();
        if ($codesTotal >= config('app.code_limit')) {
            return response()->jsonApi([
                'status' => 'warning',
                'title' => "Exceeded the limit",
                'message' => 'You have exceeded the limit on the number of links to this service'
            ], 200);
        }

        // Try create new code with link
        try {
            $code = ReferralCodeService::createReferralCode([
                'application_id' => $request->get('application_id'),
                'is_default' => $request->boolean('is_default'),
                'note' => $request->get('note', null)
            ]);

            return response()->jsonApi([
                'status' => 'success',
                'title' => "Referral code generate",
                'message' => 'The creation of the referral link was successful',
                'data' => $code->toArray()
            ], 200);
        } catch (Exception $e) {
            return response()->jsonApi([
                'status' => 'danger',
                'title' => 'Referral code generate',
                'message' => "There was an error while creating a referral code: " . $e->getMessage()
            ], 404);
        }
    }

    /**
     * Show one code and link
     *
     * @OA\Get(
     *     path="/v1/referrals/referral-codes/{id}",
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
     *              "optimal": "false"
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
                'status' => 'success',
                'title' => "Get referral code info",
                'message' => 'Get referral code info with link',
                'data' => $code,
            ], 200);
        } catch (Exception $e) {
            return response()->jsonApi([
                'status' => 'danger',
                'title' => "Get referral code info",
                'message' => "Referral code #{$id} not found"
            ], 404);
        }
    }

    /**
     * Update referral link and code.
     *
     * @OA\Put(
     *     path="/v1/referrals/referral-codes/{id}",
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
     *                  type="string",
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
     * @param \Illuminate\Http\Request $request
     * @param                          $id
     *
     * @return mixed
     * @throws \Illuminate\Validation\ValidationException
     */
    public function update(Request $request, $id)
    {
        // Validate input data
        $this->validate($request, ReferralCode::$rules);

        // Try find referral code and update it
        try {
            $data = ReferralCode::find($id);

            // Check if has is_default parameter, then reset all previous code
            if ($request->has('is_default')) {
                ReferralCodeService::defaultReset($data->application_id, $data->user_id);

                $data->is_default = $request->boolean('is_default');
            }

            $data->note = $request->get('note', null);
            $data->save();

            // Send response
            return response()->jsonApi([
                'status' => 'success',
                'title' => "Updating success",
                'message' => 'The referral code (link) has been successfully updated'
            ], 200);
        } catch (Exception $e) {
            return response()->jsonApi([
                'status' => 'danger',
                'title' => 'Referrals link not found',
                'message' => "Referral code #{$id} updated error: " . $e->getMessage()
            ], 404);
        }
    }

    /**
     * Remove referral code
     *
     * @OA\Delete(
     *     path="/v1/referrals/referral-codes/{id}",
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
                'status' => 'success',
                'title' => "Deleting success",
                'message' => 'The referral link field has been successfully deleted'
            ], 200);
        } catch (Exception $e) {
            return response()->jsonApi([
                'status' => 'danger',
                'title' => 'Not found',
                'message' => "Referrals link #{$id} for deleted not found"
            ], 404);
        }
    }

    /**
     * Change the default link
     *
     * @OA\Put(
     *     path="/v1/referrals/referral-codes/{id}/default",
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
     *              "optimal": "false"
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
     * @return \Illuminate\Http\Response
     */
    public function setDefault($id): \Illuminate\Http\Response
    {
        dd('fff');

        try {
            $code = ReferralCode::find($id);

            // Reset defaults
            self::defaultReset($code->application_id, $code->user_id);

            // Set new default code
            $code->update(['is_default' => true]);

            return response()->jsonApi([
                'status' => 'success',
                'title' => "Update was success",
                'message' => 'Changing the default а referral link was successful.'
            ], 200);
        } catch (Exception $e) {
            return response()->jsonApi([
                'status' => 'danger',
                'title' => 'Operation not successful',
                'message' => "Changing the default a referral link was not successful."
            ], 404);
        }
    }
}
